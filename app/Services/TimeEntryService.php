<?php

namespace App\Services;

use App\Models\Dtr;
use App\Models\Employee;
use App\Models\EmployeeFaceProfile;
use App\Models\TimeEntryLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TimeEntryService
{
    private int $embeddingSize = 128;
    private float $acceptDistance = 0.65;

    public function decryptQrToken(string $encrypted): ?string
    {
        $key = 'fA7xB93kL0pTzWmQ';
        $cipher = 'AES-128-ECB';
        $encrypted = strtr(trim($encrypted), '-_', '+/');
        $decrypted = openssl_decrypt(base64_decode($encrypted), $cipher, $key, 0);

        return is_string($decrypted) && trim($decrypted) !== '' ? trim($decrypted) : null;
    }

    public function encryptQrToken(string $employeeId): string
    {
        $key = 'fA7xB93kL0pTzWmQ';
        $cipher = 'AES-128-ECB';

        return rtrim(strtr(base64_encode(openssl_encrypt($employeeId, $cipher, $key, 0)), '+/', '-_'), '=');
    }

    public function validateQr(string $qrToken): array
    {
        $employeeId = $this->decryptQrToken($qrToken);

        if (!$employeeId) {
            return ['valid' => false, 'message' => 'Invalid QR token.'];
        }

        $employee = Employee::query()
            ->where('emp_ID', $employeeId)
            ->where('stat_1', 1)
            ->first();

        if (!$employee) {
            return ['valid' => false, 'message' => 'Employee not found or inactive.'];
        }

        return [
            'valid' => true,
            'employee' => $employee,
            'has_face_profile' => EmployeeFaceProfile::where('employee_id', $employeeId)->where('is_active', true)->exists(),
        ];
    }

    public function normalizeEmbedding(array $embedding): ?array
    {
        if (count($embedding) !== $this->embeddingSize) {
            return null;
        }

        $sum = 0.0;
        $out = [];
        foreach ($embedding as $value) {
            if (!is_numeric($value) || !is_finite((float) $value)) {
                return null;
            }
            $float = (float) $value;
            $out[] = $float;
            $sum += $float * $float;
        }

        $norm = sqrt(max($sum, 1e-12));
        return array_map(fn ($value) => $value / $norm, $out);
    }

    public function centroid(array $embeddings): ?array
    {
        $valid = array_values(array_filter(array_map(fn ($embedding) => is_array($embedding) ? $this->normalizeEmbedding($embedding) : null, $embeddings)));
        if (empty($valid)) {
            return null;
        }

        $accumulator = array_fill(0, $this->embeddingSize, 0.0);
        foreach ($valid as $embedding) {
            for ($i = 0; $i < $this->embeddingSize; $i++) {
                $accumulator[$i] += $embedding[$i];
            }
        }

        for ($i = 0; $i < $this->embeddingSize; $i++) {
            $accumulator[$i] /= count($valid);
        }

        return $this->normalizeEmbedding($accumulator);
    }

    public function compareFace(string $employeeId, array $embedding): array
    {
        $probe = $this->normalizeEmbedding($embedding);
        if (!$probe) {
            return ['match' => false, 'message' => 'Invalid face embedding.'];
        }

        $profile = EmployeeFaceProfile::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->latest('registered_at')
            ->first();

        if (!$profile) {
            return ['match' => false, 'message' => 'No active face profile is registered.'];
        }

        $payload = $profile->face_embedding ?: [];
        $candidates = [];
        if (isset($payload['centroid']) && is_array($payload['centroid'])) {
            $candidates[] = $payload['centroid'];
        }
        foreach (($payload['scans'] ?? []) as $scan) {
            if (isset($scan['embedding']) && is_array($scan['embedding'])) {
                $candidates[] = $scan['embedding'];
            }
        }

        $best = INF;
        foreach ($candidates as $candidate) {
            $candidate = $this->normalizeEmbedding($candidate);
            if (!$candidate) {
                continue;
            }

            $distance = 0.0;
            for ($i = 0; $i < $this->embeddingSize; $i++) {
                $diff = $probe[$i] - $candidate[$i];
                $distance += $diff * $diff;
            }
            $best = min($best, sqrt($distance));
        }

        if (!is_finite($best)) {
            return ['match' => false, 'message' => 'Face profile is incomplete.'];
        }

        return [
            'match' => $best <= $this->acceptDistance,
            'distance' => $best,
            'score' => max(0, min(1, 1 - ($best / 2))),
            'message' => $best <= $this->acceptDistance ? 'Face matched.' : 'Face did not match.',
        ];
    }

    public function validateGeo(?float $latitude, ?float $longitude, ?float $accuracy): array
    {
        if ($latitude === null || $longitude === null || $accuracy === null) {
            return ['valid' => false, 'message' => 'Geo location is required.'];
        }

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return ['valid' => false, 'message' => 'Geo coordinates are invalid.'];
        }

        if ($accuracy > 150) {
            return ['valid' => false, 'message' => 'Geo accuracy must be within 150 meters.'];
        }

        return ['valid' => true, 'message' => 'Geo location verified.'];
    }

    public function validateLiveness(array $liveness): array
    {
        $checks = [
            'face_movement' => (bool) ($liveness['face_movement'] ?? false),
            'blink_detected' => (bool) ($liveness['blink_detected'] ?? false),
            'challenge_passed' => (bool) ($liveness['challenge_passed'] ?? false),
            'single_face' => (bool) ($liveness['single_face'] ?? false),
        ];

        if (in_array(false, $checks, true)) {
            return ['valid' => false, 'message' => 'Live face challenge was not completed.'];
        }

        $samples = (int) ($liveness['samples_count'] ?? 0);
        $elapsedMs = (int) ($liveness['elapsed_ms'] ?? 0);
        $motionScore = (float) ($liveness['motion_score'] ?? 0);
        $faceSizeVariance = (float) ($liveness['face_size_variance'] ?? 0);
        $descriptorVariance = (float) ($liveness['descriptor_variance'] ?? 0);

        if ($samples < 6) {
            return ['valid' => false, 'message' => 'More live face samples are required.'];
        }

        if ($elapsedMs < 1800 || $elapsedMs > 15000) {
            return ['valid' => false, 'message' => 'Live face challenge timing is invalid.'];
        }

        if ($motionScore < 10) {
            return ['valid' => false, 'message' => 'Face movement was too low for liveness verification.'];
        }

        if ($faceSizeVariance < 2.5) {
            return ['valid' => false, 'message' => 'Move closer or farther during verification.'];
        }

        if ($descriptorVariance < 0.001) {
            return ['valid' => false, 'message' => 'Face scan appears static. Please use live video.'];
        }

        return ['valid' => true, 'message' => 'Liveness verified.'];
    }

    public function dtrStatus(string $employeeId, ?Carbon $date = null): array
    {
        $date = $date ?: now('Asia/Manila');
        $record = Dtr::where('emp_ID', $employeeId)->where('date', $date->toDateString())->first();

        $timeIn = $this->splitTimes($record?->time_in);
        $timeOut = $this->splitTimes($record?->time_out);
        $overtime = $this->splitTimes($record?->time_over);

        return [
            'date' => $date->toDateString(),
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'overtime' => $overtime,
            'actions' => [
                'time_in' => count($timeIn) === 0,
                'time_out' => count($timeIn) > 0 && count($timeOut) === 0,
                'overtime' => count($timeIn) > 0 && count($overtime) === 0,
            ],
        ];
    }

    public function logDtrAction(string $employeeId, string $action, array $context = []): array
    {
        $allowedActions = ['time_in', 'time_out', 'overtime'];
        if (!in_array($action, $allowedActions, true)) {
            return ['success' => false, 'message' => 'Invalid action selected.'];
        }

        return DB::transaction(function () use ($employeeId, $action, $context) {
            $now = now('Asia/Manila');
            $date = $now->toDateString();
            $time = $now->format('H:i:s');

            $record = Dtr::where('emp_ID', $employeeId)
                ->where('date', $date)
                ->lockForUpdate()
                ->first();

            $status = $this->dtrStatus($employeeId, $now);
            if (!($status['actions'][$action] ?? false)) {
                return ['success' => false, 'message' => $this->invalidActionMessage($action, $status)];
            }

            if (!$record) {
                $record = new Dtr([
                    'emp_ID' => $employeeId,
                    'date' => $date,
                ]);
            }

            $deviceId = (string) ($context['device_id'] ?? 'TIME_ENTRY');
            match ($action) {
                'time_in' => $this->setSingleDtrTime($record, 'time_in', 'device_id_in', $time, $deviceId),
                'time_out' => $this->setSingleDtrTime($record, 'time_out', 'device_id_out', $time, $deviceId),
                'overtime' => $this->setSingleDtrTime($record, 'time_over', 'device_id_over', $time, $deviceId),
            };

            $record->save();

            return [
                'success' => true,
                'message' => 'Attendance recorded.',
                'time' => $now->format('h:i:s A'),
                'date' => $date,
                'status' => $this->dtrStatus($employeeId, $now),
            ];
        });
    }

    public function writeLog(Request $request, array $payload): TimeEntryLog
    {
        return TimeEntryLog::create([
            'employee_id' => $payload['employee_id'] ?? null,
            'qr_token' => $payload['qr_token'] ?? null,
            'selected_action' => $payload['selected_action'] ?? null,
            'status' => $payload['status'] ?? 'failed',
            'reason' => $payload['reason'] ?? null,
            'latitude' => $payload['latitude'] ?? null,
            'longitude' => $payload['longitude'] ?? null,
            'accuracy' => $payload['accuracy'] ?? null,
            'matched_score' => $payload['matched_score'] ?? null,
            'device_info' => [
                'user_agent' => $request->userAgent(),
                'client' => $payload['device_info'] ?? null,
            ],
            'ip_address' => $request->ip(),
            'logged_at' => now(),
        ]);
    }

    public function canRegisterFace($user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->role, ['Administrator', 'HR', 'HR Administrator'], true)) {
            return true;
        }

        $access = $user->access;
        $decoded = is_string($access) ? json_decode($access, true) : null;
        $flags = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', (string) $access)));

        if (in_array('face_registration', $flags, true) || in_array('time_entry_face_registration', $flags, true)) {
            return true;
        }

        $permission = $user->menuPermission()->first();
        return $permission && in_array('face_registration', $permission->menu_keys ?: [], true);
    }

    public function canRegisterFaceForEmployee(Employee $employee): bool
    {
        if (in_array($employee->role, ['Administrator', 'HR', 'HR Administrator'], true)) {
            return true;
        }

        $user = \App\Models\User::query()
            ->where('emp_ID', $employee->emp_ID)
            ->first();

        return $this->canRegisterFace($user);
    }


    public function employeePayload(Employee $employee): array
    {
        return [
            'emp_ID' => $employee->emp_ID,
            'name' => trim(preg_replace('/\s+/', ' ', "{$employee->fname} {$employee->mname} {$employee->lname}")),
            'position' => $employee->position,
            'profile' => $employee->profile,
        ];
    }

    public function tableHasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    private function splitTimes($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    private function setSingleDtrTime(Dtr $record, string $timeField, string $deviceField, string $time, string $deviceId): void
    {
        $record->{$timeField} = $time;
        $record->{$deviceField} = $deviceId;
    }

    private function appendDtrTime(Dtr $record, string $timeField, string $deviceField, string $time, string $deviceId): void
    {
        $times = $this->splitTimes($record->{$timeField});
        $devices = $this->splitTimes($record->{$deviceField});
        $times[] = $time;
        $devices[] = $deviceId;
        $record->{$timeField} = implode(',', $times);
        $record->{$deviceField} = implode(',', $devices);
    }

    private function invalidActionMessage(string $action, array $status): string
    {
        return match ($action) {
            'time_in' => 'Time In has already been recorded.',
            'time_out' => empty($status['time_in']) ? 'Time Out requires Time In first.' : 'Time Out has already been recorded.',
            'overtime' => empty($status['time_in']) ? 'Overtime requires Time In first.' : 'Overtime has already been recorded.',
            default => 'Action is not available.',
        };
    }
}
