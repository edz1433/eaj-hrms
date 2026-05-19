<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Office;
use App\Models\Pmt;
use App\Models\User;
use App\Models\DocuFolder;
use App\Models\Dtr;
use App\Models\LeaveApplication;
use App\Models\Eligibility;
use App\Models\WorkExperience;
use App\Models\LearningDev; 
use App\Models\VoluntaryWork;
use App\Models\Application;
use App\Models\SpmsPersonnel;
use App\Models\Setting;
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
        
        if (\Auth::guard('web')->check()) {
            $today = Carbon::now('Asia/Manila');
            
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
            ));
        }
        
        if (\Auth::guard('employee')->check()) {
            // Employee dashboard data
            $authEmp = \Auth::guard('employee')->user();
            $today = Carbon::now('Asia/Manila');
            
            // Get employee's leave applications
            $employeeLeaves = LeaveApplication::where('empid', $authEmp->emp_ID)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Get pending leave count for this employee
            $pendingLeaveCount = LeaveApplication::where('empid', $authEmp->emp_ID)
                ->where('emp_esign', 0)
                ->where('status', 1)
                ->count();
            
            // Get upcoming birthdays (for employee's department)
            $upcomingBirthdays = Employee::whereNotNull('bdate')
                ->where('stat_1', 1)
                ->where('emp_dept', $authEmp->emp_dept)
                ->whereRaw("
                    DATE_FORMAT(bdate, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
                    AND DATE_FORMAT(bdate, '%m-%d') <= DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
                ")
                ->orderByRaw("DATE_FORMAT(bdate, '%m-%d') ASC")
                ->take(5)
                ->get()
                ->each(function ($employee) {
                    $employee->bdate = Carbon::parse($employee->bdate);
                });
            
            return view("home.dashboard", compact(
                'authEmp',
                'employeeLeaves',
                'pendingLeaveCount',
                'upcomingBirthdays',
                'today'
            ));
        }
        
        // Fallback for unauthenticated
        return redirect()->route('login');
    }

    public function drive()
    {
        $guard = $this->getGuard();

        $docFolder = DocuFolder::where('folder_category', 'mainfolder')->get();
        $offices   = Office::all();

        $category = null;

        if ($guard === 'employee') {
            $userid = auth()->guard('employee')->user()->id;

            // ✅ returns int or null
            $category = SpmsPersonnel::where('empid', $userid)
                ->value('category');
        }

        $office = null;
        if (\Auth::guard('employee')->check()) {
            $uid = auth()->guard('employee')->user()->id;
            $office = Office::where('office_head_id', $uid)->first();
        }

        return view('drive.drive', compact(
            'docFolder',
            'category',
            'office',
            'offices',
            'guard'
        ));
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
