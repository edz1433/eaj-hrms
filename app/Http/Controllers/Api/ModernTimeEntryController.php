<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFaceProfile;
use App\Models\TimeEntryLog;
use App\Models\User;
use App\Services\TimeEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class ModernTimeEntryController extends Controller
{
    public function __construct(private TimeEntryService $timeEntry)
    {
    }

    public function validateQr(Request $request)
    {
        $request->validate(['qr_token' => ['required', 'string', 'max:5000']]);

        $result = $this->timeEntry->validateQr($request->qr_token);
        if (!$result['valid']) {
            $this->timeEntry->writeLog($request, [
                'qr_token' => $request->qr_token,
                'status' => 'failed',
                'reason' => $result['message'],
            ]);

            return response()->json($result, 422);
        }

        return response()->json([
            'valid' => true,
            'employee' => $this->timeEntry->employeePayload($result['employee']),
            'has_face_profile' => $result['has_face_profile'],
        ]);
    }

    public function verifyFace(Request $request)
    {
        $request->validate([
            'qr_token' => ['required', 'string', 'max:5000'],
            'embedding' => ['required', 'array'],
            'liveness' => ['required', 'array'],
        ]);

        $qr = $this->timeEntry->validateQr($request->qr_token);
        if (!$qr['valid']) {
            $this->timeEntry->writeLog($request, [
                'qr_token' => $request->qr_token,
                'status' => 'failed',
                'reason' => $qr['message'],
            ]);

            return response()->json(['match' => false, 'message' => $qr['message']], 422);
        }

        $employee = $qr['employee'];
        $liveness = $this->timeEntry->validateLiveness($request->liveness);
        if (!$liveness['valid']) {
            $this->timeEntry->writeLog($request, [
                'employee_id' => $employee->emp_ID,
                'qr_token' => $request->qr_token,
                'status' => 'failed',
                'reason' => $liveness['message'],
            ]);

            return response()->json(['match' => false, 'message' => $liveness['message']], 422);
        }

        $match = $this->timeEntry->compareFace($employee->emp_ID, $request->embedding);
        if (!$match['match']) {
            $this->timeEntry->writeLog($request, [
                'employee_id' => $employee->emp_ID,
                'qr_token' => $request->qr_token,
                'status' => 'failed',
                'reason' => $match['message'],
                'matched_score' => $match['score'] ?? null,
            ]);
        }

        $canRegisterFace = $this->timeEntry->canRegisterFaceForEmployee($employee);
        $this->loginMatchedIdentity($employee, $canRegisterFace);

        return response()->json([
            'match' => $match['match'],
            'message' => $match['message'],
            'matched_score' => $match['score'] ?? null,
            'employee' => $this->timeEntry->employeePayload($employee),
            'can_register_face' => $canRegisterFace,
            'face_registration_url' => $canRegisterFace ? route('time-entry.register') : null,
            'logs_url' => $canRegisterFace ? route('time-entry.logs') : null,
            'status' => $this->timeEntry->dtrStatus($employee->emp_ID),
            'csrf_token' => csrf_token(),
            'server_time' => now('Asia/Manila')->toIso8601String(),
        ], $match['match'] ? 200 : 422);
    }

    public function status(Request $request)
    {
        $request->validate(['employee_id' => ['required', 'string', 'exists:employees,emp_ID']]);

        return response()->json([
            'status' => $this->timeEntry->dtrStatus($request->employee_id),
            'server_time' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function logAttendance(Request $request)
    {
        $request->validate([
            'qr_token' => ['required', 'string', 'max:5000'],
            'embedding' => ['required', 'array'],
            'selected_action' => ['required', Rule::in(['time_in', 'time_out', 'overtime'])],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'liveness' => ['required', 'array'],
            'device_info' => ['nullable', 'array'],
        ]);

        $rateKey = 'time-entry-log:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 30)) {
            return response()->json(['success' => false, 'message' => 'Too many attempts.'], 429);
        }
        RateLimiter::hit($rateKey, 60);

        $qr = $this->timeEntry->validateQr($request->qr_token);
        if (!$qr['valid']) {
            return $this->failedLog($request, null, $qr['message']);
        }

        $employee = $qr['employee'];
        $liveness = $this->timeEntry->validateLiveness($request->liveness);
        if (!$liveness['valid']) {
            return $this->failedLog($request, $employee->emp_ID, $liveness['message']);
        }

        $face = $this->timeEntry->compareFace($employee->emp_ID, $request->embedding);
        if (!$face['match']) {
            return $this->failedLog($request, $employee->emp_ID, $face['message'], $face['score'] ?? null);
        }

        $geo = $this->timeEntry->validateGeo((float) $request->latitude, (float) $request->longitude, (float) $request->accuracy);
        if (!$geo['valid']) {
            return $this->failedLog($request, $employee->emp_ID, $geo['message'], $face['score'] ?? null);
        }

        $result = $this->timeEntry->logDtrAction($employee->emp_ID, $request->selected_action, [
            'device_id' => 'TIME_ENTRY',
        ]);

        $this->timeEntry->writeLog($request, [
            'employee_id' => $employee->emp_ID,
            'qr_token' => $request->qr_token,
            'selected_action' => $request->selected_action,
            'status' => $result['success'] ? 'success' : 'failed',
            'reason' => $result['message'],
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'matched_score' => $face['score'] ?? null,
            'device_info' => $request->device_info,
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'employee' => $this->timeEntry->employeePayload($employee),
            'time' => $result['time'] ?? null,
            'status' => $result['status'] ?? $this->timeEntry->dtrStatus($employee->emp_ID),
        ], $result['success'] ? 200 : 422);
    }

    public function logs()
    {
        $logs = TimeEntryLog::latest('logged_at')
            ->limit(100)
            ->get();

        return response()->json(['logs' => $logs]);
    }

    public function employees()
    {
        $employees = Employee::query()
            ->where('stat_1', 1)
            ->orderBy('lname')
            ->get(['emp_ID', 'fname', 'mname', 'lname', 'position'])
            ->map(fn ($employee) => $this->timeEntry->employeePayload($employee));

        return response()->json(['employees' => $employees]);
    }

    public function registerFace(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'string', 'exists:employees,emp_ID'],
            'qr_token' => ['nullable', 'string', 'max:5000'],
            'scans' => ['required', 'array', 'size:4'],
            'scans.*.pose' => ['required', Rule::in(['front', 'slight_left', 'slight_right', 'neutral'])],
            'scans.*.embedding' => ['required', 'array'],
            'liveness' => ['required', 'array'],
            'liveness.face_movement' => ['accepted'],
            'liveness.blink_detected' => ['accepted'],
        ]);

        $qrToken = trim((string) $request->input('qr_token', ''));
        if ($qrToken === '') {
            $qrToken = $this->timeEntry->encryptQrToken($request->employee_id);
        }

        $qr = $this->timeEntry->validateQr($qrToken);
        if (!$qr['valid'] || $qr['employee']->emp_ID !== $request->employee_id) {
            return response()->json(['success' => false, 'message' => 'QR token does not match the selected employee.'], 422);
        }

        if (EmployeeFaceProfile::where('employee_id', $request->employee_id)->where('is_active', true)->exists()) {
            return response()->json(['success' => false, 'message' => 'This employee already has an active face registration.'], 422);
        }

        $scans = [];
        foreach ($request->scans as $scan) {
            $embedding = $this->timeEntry->normalizeEmbedding($scan['embedding']);
            if (!$embedding) {
                return response()->json(['success' => false, 'message' => 'One or more face scans are invalid.'], 422);
            }
            $scans[] = ['pose' => $scan['pose'], 'embedding' => $embedding];
        }

        $centroid = $this->timeEntry->centroid(array_column($scans, 'embedding'));

        DB::transaction(function () use ($request, $scans, $centroid, $qrToken) {
            EmployeeFaceProfile::create([
                'employee_id' => $request->employee_id,
                'qr_token' => $qrToken,
                'face_embedding' => [
                    'version' => 1,
                    'model' => 'face-api.js',
                    'scans' => $scans,
                    'centroid' => $centroid,
                ],
                'scan_count' => count($scans),
                'registered_by' => auth()->guard('web')->id(),
                'registered_at' => now(),
                'is_active' => true,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Face registration saved.']);
    }

    private function failedLog(Request $request, ?string $employeeId, string $reason, ?float $score = null)
    {
        $this->timeEntry->writeLog($request, [
            'employee_id' => $employeeId,
            'qr_token' => $request->qr_token,
            'selected_action' => $request->selected_action,
            'status' => 'failed',
            'reason' => $reason,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'matched_score' => $score,
            'device_info' => $request->device_info,
        ]);

        return response()->json(['success' => false, 'message' => $reason], 422);
    }

    private function loginMatchedIdentity(Employee $employee, bool $canRegisterFace): void
    {
        Auth::guard('web')->logout();
        Auth::guard('employee')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        if ($canRegisterFace) {
            $user = User::where('emp_ID', $employee->emp_ID)->first();

            if ($user) {
                Auth::guard('web')->login($user);
                request()->session()->regenerate();
                return;
            }
        }

        Auth::guard('employee')->login($employee);
        request()->session()->regenerate();
    }
}
