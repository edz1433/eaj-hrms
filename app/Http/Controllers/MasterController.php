<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Office;
use App\Models\User;
use App\Models\Dtr;
use App\Models\LeaveApplication;
use App\Models\Eligibility;
use App\Models\WorkExperience;
use App\Models\LearningDev; 
use App\Models\VoluntaryWork;
use App\Models\Application;
use App\Models\Setting;
use App\Models\Event;
use App\Models\Status;
use App\Models\OfficialTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class MasterController extends Controller
{
    public function getGuard()
    {
        if(\Auth::guard('web')->check()) {
            return 'web';
        } elseif(\Auth::guard('employee')->check()) {
            return 'employee';
        }
    }

    public function dashboard()
    {
        $guard = $this->getGuard();
        
        if (\Auth::guard('web')->check() && $this->shouldShowPersonalDashboard()) {
            $authEmp = \Auth::guard('web')->user()->employee;

            if ($authEmp) {
                return $this->employeeDashboard($authEmp, true);
            }
        }

        if (\Auth::guard('web')->check()) {
            $today = Carbon::now('Asia/Manila');
            $canTogglePersonalDashboard = $this->canTogglePersonalDashboard();
            
            // ── Basic Counts ──────────────────────────────────────────────
            $totalEmployees = Employee::where('stat_1', 1)->count();
            $dtrCount = Dtr::whereDate('date', $today->toDateString())->count();
            
            // ── Leave Applications ────────────────────────────────────────
            $leaveappCount = LeaveApplication::where('emp_esign', 0)
                ->where('history', 1)
                ->where('status', 1)
                ->count();
            
            // Get leave breakdown (pending, approved, disapproved)
            $leaveBreakdown = [
                'pending' => LeaveApplication::where('emp_esign', 0)->where('history', 1)->where('status', 1)->count(),
                'approved' => LeaveApplication::where('emp_esign', 1)->where('history', 1)->where('status', 2)->count(),
                'disapproved' => LeaveApplication::where('emp_esign', 1)->where('history', 1)->where('status', 0)->count(),
            ];
            
            // ── PDS Queue Counts ─────────────────────────────────────────
            $eliCount = Eligibility::where('status', 0)->count();
            $workexpCount = WorkExperience::where('status', 0)->count();
            $learDevCount = LearningDev::where('status', 0)->count();
            $volWorkCount = VoluntaryWork::where('status', 0)->count();
            $pdsTotal = $eliCount + $workexpCount + $learDevCount + $volWorkCount;
            
            $pdsBreakdown = [
                'eligibility' => $eliCount,
                'work_experience' => $workexpCount,
                'learning_dev' => $learDevCount,
                'voluntary_work' => $volWorkCount,
            ];
            
            // ── Employment Status Breakdown ───────────────────────────────
            $chartEmployee = Employee::where('stat_1', 1)->get();
            $empStatuses = [1, 2, 3, 4];
            $empStatusPercentages = collect($empStatuses)->mapWithKeys(function ($status) use ($chartEmployee, $totalEmployees) {
                $count = $chartEmployee->where('emp_status', $status)->count();
                $percentage = $totalEmployees > 0 ? ($count / $totalEmployees) * 100 : 0;
                return [$status => ['count' => $count, 'percentage' => $percentage]];
            });
            
            // ── Gender Split ─────────────────────────────────────────────
            $genderSplit = [
                'male' => Employee::where('stat_1', 1)->where('sex', 'Male')->count(),
                'female' => Employee::where('stat_1', 1)->where('sex', 'Female')->count(),
            ];
            
            // ── Department Distribution ───────────────────────────────────
            $deptDist = Office::select('offices.id', 'offices.office_name', 'offices.office_abbr')
                ->withCount(['employees' => function($query) {
                    $query->where('stat_1', 1);
                }])
                ->having('employees_count', '>', 0)
                ->orderBy('employees_count', 'desc')
                ->take(8)
                ->get();
            
            // ── Monthly Leave Trend (Last 6 Months) ──────────────────────
            $monthlyLeaves = collect(range(0, 5))->map(function ($i) use ($today) {
                $month = $today->copy()->subMonths($i);
                $count = LeaveApplication::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                return [
                    'month' => $month->format('M'),
                    'total' => $count,
                ];
            })->reverse()->values();
            
            $leavesThisMonth = LeaveApplication::whereYear('created_at', $today->year)
                ->whereMonth('created_at', $today->month)
                ->count();
            
            // ── Pending Leave Applications with Employee Details ──────────
            $pendingLeaves = LeaveApplication::where('emp_esign', 0)
                ->where('history', 1)
                ->where('status', 1)
                ->with(['employee' => function($q) {
                    $q->select('id', 'emp_ID', 'fname', 'lname', 'profile', 'position');
                }])
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get()
                ->map(function($leave) {
                    return (object) [
                        'fname' => $leave->employee->fname ?? '',
                        'lname' => $leave->employee->lname ?? '',
                        'profile' => $leave->employee->profile ?? null,
                        'position' => $leave->employee->position ?? '',
                        'leave_type' => $leave->leave_type ?? '',
                        'days' => $leave->days ?? 1,
                        'created_at' => $leave->created_at,
                    ];
                });
            
            // ── Upcoming Birthdays (Next 30 Days) ────────────────────────
            $upcomingBirthdays = Employee::whereNotNull('bdate')
                ->where('stat_1', 1)
                ->whereRaw("
                    DATE_FORMAT(bdate, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
                    AND DATE_FORMAT(bdate, '%m-%d') <= DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
                ")
                ->orWhereRaw("
                    DATE_FORMAT(bdate, '%m-%d') <= DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
                    AND DATE_FORMAT(bdate, '%m-%d') < DATE_FORMAT(CURDATE(), '%m-%d')
                ")
                ->join('offices', 'employees.emp_dept', '=', 'offices.id')
                ->select('employees.id', 'employees.fname', 'employees.lname', 'employees.mname', 'employees.profile', 'employees.bdate', 'offices.office_abbr')
                ->orderByRaw("
                    CASE 
                        WHEN DATE_FORMAT(bdate, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d') THEN 0
                        ELSE 1
                    END,
                    DATE_FORMAT(bdate, '%m-%d') ASC
                ")
                ->take(10)
                ->get()
                ->each(function ($employee) {
                    $employee->bdate = Carbon::parse($employee->bdate);
                });
            
            // ── Office/Department Count ───────────────────────────────────
            $offCount = Office::all();
            
            return view("home.dashboard", compact(
                'totalEmployees',
                'dtrCount',
                'leaveappCount',
                'leaveBreakdown',
                'pdsTotal',
                'pdsBreakdown',
                'empStatusPercentages',
                'genderSplit',
                'deptDist',
                'monthlyLeaves',
                'leavesThisMonth',
                'pendingLeaves',
                'upcomingBirthdays',
                'offCount',
                'eliCount',
                'workexpCount',
                'learDevCount',
                'volWorkCount'
            ) + compact('canTogglePersonalDashboard'));
        }
        
        if (\Auth::guard('employee')->check()) {
            return $this->employeeDashboard(\Auth::guard('employee')->user());
        }
        
        // Fallback for unauthenticated
        return redirect()->route('login');
    }

    private function shouldShowPersonalDashboard(): bool
    {
        $user = \Auth::guard('web')->user();

        if (!$user || !$user->emp_ID || !$user->employee) {
            return false;
        }

        $hasHrDashboard = in_array($user->role, ['Administrator', 'HR Administrator', 'HR Staff'], true);

        return !$hasHrDashboard || request('dashboard') === 'personal';
    }

    private function canTogglePersonalDashboard(): bool
    {
        $user = \Auth::guard('web')->user();

        return (bool) ($user?->emp_ID && $user?->employee && in_array($user->role, ['HR Administrator', 'HR Staff'], true));
    }

    private function employeeDashboard(Employee $authEmp, bool $canToggleHrDashboard = false)
    {
        $today = Carbon::now('Asia/Manila');
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);

        $employeeStatus = Status::find($authEmp->emp_status);
        $leaveRecords = LeaveApplication::where('empid', $authEmp->emp_ID)->count();
        $dtrRecords = Dtr::where('emp_ID', $authEmp->emp_ID)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderByDesc('date')
            ->get();
        $officialTime = OfficialTime::forEmployee($authEmp->emp_ID);
        $recentDtr = $dtrRecords->take(4)->map(fn ($record) => $this->formatEmployeeDtrRecord($record, $officialTime))->values();
        $attendanceSummary = $this->summarizeEmployeeAttendance($dtrRecords, $officialTime);
        $serviceYears = $authEmp->date_hired ? (int) floor(Carbon::parse($authEmp->date_hired)->diffInYears($today)) : 0;
        $serviceSince = $authEmp->date_hired ? Carbon::parse($authEmp->date_hired)->format('M d, Y') : null;
        $events = Event::query()
            ->where(function ($query) {
                $query->whereNull('event_stat')->orWhere('event_stat', 1);
            })
            ->whereBetween('start', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()])
            ->orderBy('start')
            ->get(['title', 'start', 'end', 'bg_color'])
            ->map(fn ($event) => [
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'color' => $event->bg_color ?: '#0f766e',
            ])
            ->values();

        return view("home.employee-dashboard", compact(
            'authEmp',
            'employeeStatus',
            'today',
            'weekStart',
            'weekEnd',
            'leaveRecords',
            'recentDtr',
            'attendanceSummary',
            'serviceYears',
            'serviceSince',
            'events',
            'canToggleHrDashboard'
        ));
    }

    private function splitDtrTimes(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($time) => trim($time))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeDtrTime(?string $time): ?Carbon
    {
        $time = trim((string) $time);

        if ($time === '') {
            return null;
        }

        try {
            return Carbon::parse($time);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatDtrTime(?Carbon $time): ?string
    {
        return $time ? $time->format('h:i A') : null;
    }

    private function officialDtrSlots(Dtr $record, ?OfficialTime $officialTime = null): array
    {
        $officialTime ??= OfficialTime::forEmployee($record->emp_ID);
        $day = strtolower(Carbon::parse($record->date)->format('D'));
        $day = in_array($day, OfficialTime::DAYS, true) ? $day : 'mon';

        [$amIn, $amOut] = OfficialTime::splitRange(
            $officialTime->{"morn_{$day}"} ?? null,
            OfficialTime::DEFAULT_MORNING
        );
        [$pmIn, $pmOut] = OfficialTime::splitRange(
            $officialTime->{"aft_{$day}"} ?? null,
            OfficialTime::DEFAULT_AFTERNOON
        );

        return [
            'amIn' => Carbon::parse($amIn),
            'amOut' => Carbon::parse($amOut),
            'pmIn' => Carbon::parse($pmIn),
            'pmOut' => Carbon::parse($pmOut),
            'overtime' => Carbon::parse($pmOut),
            'schedule_am' => 'AM ' . Carbon::parse($amIn)->format('h:i A') . ' - ' . Carbon::parse($amOut)->format('h:i A'),
            'schedule_pm' => 'PM ' . Carbon::parse($pmIn)->format('h:i A') . ' - ' . Carbon::parse($pmOut)->format('h:i A'),
        ];
    }

    private function closestPunchForSlot($punches, Carbon $target, array $used = []): ?array
    {
        return collect($punches)
            ->reject(fn ($punch) => in_array($punch['key'], $used, true))
            ->sortBy(fn ($punch) => abs($target->diffInSeconds($punch['time'], false)))
            ->first();
    }

    private function parsedDtrPunches(Dtr $record, ?OfficialTime $officialTime = null): array
    {
        $slots = $this->officialDtrSlots($record, $officialTime);
        $timeIns = collect($this->splitDtrTimes($record->time_in))
            ->map(fn ($time, $index) => ['key' => "in:{$index}", 'time' => $this->normalizeDtrTime($time)])
            ->filter(fn ($punch) => $punch['time'])
            ->values();
        $timeOuts = collect($this->splitDtrTimes($record->time_out))
            ->map(fn ($time, $index) => ['key' => "out:{$index}", 'time' => $this->normalizeDtrTime($time)])
            ->filter(fn ($punch) => $punch['time'])
            ->values();
        $timeOvers = collect($this->splitDtrTimes($record->time_over))
            ->map(fn ($time, $index) => ['key' => "over:{$index}", 'time' => $this->normalizeDtrTime($time)])
            ->filter(fn ($punch) => $punch['time'])
            ->values();

        $usedIn = [];
        $amInPunch = $this->closestPunchForSlot($timeIns, $slots['amIn'], $usedIn);
        if ($amInPunch) {
            $usedIn[] = $amInPunch['key'];
        }

        $pmInPunch = $this->closestPunchForSlot($timeIns, $slots['pmIn'], $usedIn);

        $usedOut = [];
        $amOutPunch = $this->closestPunchForSlot($timeOuts, $slots['amOut'], $usedOut);
        if ($amOutPunch) {
            $usedOut[] = $amOutPunch['key'];
        }

        $pmOutPunch = $this->closestPunchForSlot($timeOuts, $slots['pmOut'], $usedOut);
        $overtimePunch = $this->closestPunchForSlot($timeOvers, $slots['overtime']);

        if ($timeOuts->count() === 1 && $amOutPunch && !$pmOutPunch) {
            $single = $timeOuts->first();
            $closerToPm = abs($slots['pmOut']->diffInSeconds($single['time'], false)) < abs($slots['amOut']->diffInSeconds($single['time'], false));
            if ($closerToPm) {
                $pmOutPunch = $single;
                $amOutPunch = null;
            }
        }

        return [
            'amIn' => $amInPunch['time'] ?? null,
            'amOut' => $amOutPunch['time'] ?? null,
            'pmIn' => $pmInPunch['time'] ?? null,
            'pmOut' => $pmOutPunch['time'] ?? null,
            'overtime' => $overtimePunch['time'] ?? null,
            'schedule_am' => $slots['schedule_am'],
            'schedule_pm' => $slots['schedule_pm'],
        ];
    }

    private function minutesAfter(?string $time, string $threshold): int
    {
        if (!$time) {
            return 0;
        }

        try {
            $actual = Carbon::parse($time);
            $target = Carbon::parse($threshold);

            return $actual->greaterThan($target) ? $target->diffInMinutes($actual) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function minutesBefore(?string $time, string $threshold): int
    {
        if (!$time) {
            return 0;
        }

        try {
            $actual = Carbon::parse($time);
            $target = Carbon::parse($threshold);

            return $actual->lessThan($target) ? $actual->diffInMinutes($target) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function summarizeEmployeeAttendance($records, ?OfficialTime $officialTime = null): array
    {
        $late = 0;
        $undertime = 0;

        foreach ($records as $record) {
            $punches = $this->parsedDtrPunches($record, $officialTime);
            $slots = $this->officialDtrSlots($record, $officialTime);

            $late += $this->minutesAfter($punches['amIn']?->format('H:i:s'), $slots['amIn']->format('H:i:s'));
            $late += $this->minutesAfter($punches['pmIn']?->format('H:i:s'), $slots['pmIn']->format('H:i:s'));
            $undertime += $this->minutesBefore($punches['amOut']?->format('H:i:s'), $slots['amOut']->format('H:i:s'));
            $undertime += $this->minutesBefore($punches['pmOut']?->format('H:i:s'), $slots['pmOut']->format('H:i:s'));
        }

        return [
            'late_minutes' => $late,
            'late_label' => $this->formatMinutes($late),
            'undertime_minutes' => $undertime,
            'undertime_label' => $this->formatMinutes($undertime),
        ];
    }

    private function formatEmployeeDtrRecord(Dtr $record, ?OfficialTime $officialTime = null): array
    {
        $punches = $this->parsedDtrPunches($record, $officialTime);

        return [
            'date' => Carbon::parse($record->date)->format('M d'),
            'schedule_am' => $punches['schedule_am'],
            'schedule_pm' => $punches['schedule_pm'],
            'am_in' => $this->formatDtrTime($punches['amIn']),
            'am_out' => $this->formatDtrTime($punches['amOut']),
            'pm_in' => $this->formatDtrTime($punches['pmIn']),
            'pm_out' => $this->formatDtrTime($punches['pmOut']),
            'overtime' => $this->formatDtrTime($punches['overtime']),
        ];
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        return floor($minutes / 60) . ' hrs ' . ($minutes % 60) . ' min';
    }

    public function logout()
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            return redirect()->route('getLogin')->with('success', 'You have been successfully logged out');
        }

        if (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
            return redirect()->route('getLogin')
                             ->with('success', 'You have been successfully logged out');
        }

        return redirect()->route('getLogin')
                         ->with('error', 'No authenticated user to log out');
    }

    public function dataPrivacy()
    {
        $guard = $this->getGuard();
        $customPaper = [0, 0, 684, 1050];
        $pdf = \PDF::loadView('data-privacy', compact('guard'))
            ->setPaper($customPaper, 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'margin-top' => 10,
                'margin-right' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
            ])
            ->setCallbacks([
                'before_render' => function ($domPdf) {
                    $domPdf->getCanvas()->page_text(10, 10, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]);
                },
            ]);

        return $pdf->stream(); // stream to iframe
    } 

    public function appList(){
        $guard = $this->getGuard();
        $applications = Application::join('job_hirings', 'applications.jid', '=', 'job_hirings.id')
            ->select('applications.*', 'job_hirings.title as position')
            ->get();
        return view('career.application', compact('applications', 'guard'));
    }

    public function systemSetting(){
         $setting = Setting::singleton();
        $employees = Employee::select('id', 'emp_ID', 'fname', 'lname', 'position')
                              ->orderBy('lname')
                              ->get();

        $menuSettings = MenuSetting::orderBy('sort_order')->get()->groupBy('group');

        $users = User::select('id', 'fname', 'lname', 'role', 'emp_ID', 'email')
                     ->orderBy('lname')
                     ->get()
                     ->map(function ($u) {
                         $perm = UserMenuPermission::where('user_id', $u->id)->first();
                         $u->menuPermission = $perm;
                         return $u;
                     });

        $orgConfig    = config('organization', []);
        $empTypes     = $this->empTypeOptions();
        $enabledTypes = $setting->emp_types ?? [];
        $leadershipLabels = $this->leadershipLabels();

        return view('settings.index', compact(
            'setting', 'employees', 'menuSettings', 'users',
            'orgConfig', 'empTypes', 'enabledTypes', 'leadershipLabels'
        ));
    }


    public function dataPrivacyNotice(Request $request)
    {
        $guard = $this->getGuard();
        $user = Employee::find(auth()->guard($guard)->user()->id);
        $user->dpn = 1; 
        $user->save();

        return redirect()->back();
    }

}
