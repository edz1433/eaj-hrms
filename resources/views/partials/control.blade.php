@php use App\Helpers\MenuHelper; @endphp

{{-- ─────────────────────────────────────────────────────────────────
     Base Tailwind class strings — kept in PHP so they stay DRY
────────────────────────────────────────────────────────────────── --}}
@php
    $base  = 'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-medium transition-colors duration-150 ';
    $on    = 'bg-sidebar-primary text-white';
    $off   = 'text-sidebar-foreground/60 hover:bg-sidebar-accent hover:text-sidebar-foreground';
    $ico   = 'w-4 shrink-0 text-center text-[13px]';
    $lbl   = 'mt-4 mb-1.5 px-3 text-[9px] font-semibold uppercase tracking-widest text-sidebar-foreground/30 select-none';
    $sub   = 'group flex items-center gap-3 rounded-lg py-2 pl-9 pr-3 text-xs font-medium transition-colors duration-150 ';
    $subOn = 'text-sidebar-primary font-semibold';
    $subOff= 'text-sidebar-foreground/40 hover:bg-sidebar-accent hover:text-sidebar-foreground';

    // Web guard: full MenuHelper check. Employee guard: global visibility only.
    $can = fn(string $key): bool => $guard === 'web'
        ? MenuHelper::userCanAccess($key) && MenuHelper::isVisible($key)
        : MenuHelper::isVisible($key);
@endphp

{{-- ══ Dashboard ════════════════════════════════════════════════════ --}}
<a href="{{ route('dashboard') }}"
   class="{{ $base }}{{ request()->is('dashboard','myaccount','pending/*') ? $on : $off }}">
    <i class="fas fa-tachometer-alt {{ $ico }}"></i>
    <span>Dashboard</span>
</a>

{{-- ══ HR Management — web guard only ═════════════════════════════ --}}
@if($guard === 'web')
    @php
        $showEmp  = $can('employee_records');   // route: employees
        $showTard = $can('tardiness');           // route: readTiredness  → /tardiness/data
        $showHR   = $showEmp || $showTard;
    @endphp
    @if($showHR)
        <p class="{{ $lbl }}">HR Management</p>

        @if($showEmp)
            <a href="{{ route('employees') }}"
               class="{{ $base }}{{ request()->is('employees','employees/*') ? $on : $off }}">
                <i class="fas fa-users {{ $ico }}"></i>
                <span>Employees</span>
            </a>
        @endif

        @if($showTard)
            <a href="{{ route('readTiredness') }}"
               class="{{ $base }}{{ request()->is('tardiness*') ? $on : $off }}">
                <i class="fas fa-hourglass-start {{ $ico }}"></i>
                <span>Tardiness</span>
            </a>
        @endif
    @endif
@endif

{{-- ══ My Records — employee guard only ══════════════════════════ --}}
@if($guard === 'employee' && $can('pds'))
    <p class="{{ $lbl }}">My Records</p>

    <a href="{{ route('empPDS') }}"
       class="{{ $base }}{{ request()->is('pds','pds/*') ? $on : $off }}">
        <i class="fas fa-clipboard-list {{ $ico }}"></i>
        <span>Personal Data Sheet</span>
    </a>
@endif

{{-- ══ Attendance ═══════════════════════════════════════════════════ --}}
@if($can('dtr'))
    <p class="{{ $lbl }}">Attendance</p>

    {{-- route: dtr-read → /dtr --}}
    <a href="{{ route('dtr-read') }}"
       class="{{ $base }}{{ request()->is('dtr','dtr/*') ? $on : $off }}">
        <i class="fas fa-clock {{ $ico }}"></i>
        <span>Daily Time Records</span>
    </a>
@endif

{{-- ══ Leave ════════════════════════════════════════════════════════ --}}
@php
    $showLeave = $guard === 'web'
        ? $can('leave_applications')
        : ($can('leave_applications') && auth()->guard('employee')->user()?->emp_status == 1);
@endphp
@if($showLeave)
    <p class="{{ $lbl }}">Leave</p>

    @if($guard === 'web')
        {{-- route: leavesRead → /leaves/{id?} --}}
        <a href="{{ route('leavesRead', 1) }}"
           class="{{ $base }}{{ request()->is('leave','leave/*','leaves*') ? $on : $off }}">
            <i class="fas fa-calendar-check {{ $ico }}"></i>
            <span>Leave Applications</span>
        </a>
    @else
        {{-- route: leavesReadEmp → /leave --}}
        <a href="{{ route('leavesReadEmp') }}"
           class="{{ $base }}{{ request()->is('leave','leave/*') ? $on : $off }}">
            <i class="fas fa-calendar-check {{ $ico }}"></i>
            <span>Leave</span>
        </a>
    @endif
@endif

{{-- ══ Documents ════════════════════════════════════════════════════ --}}
{{-- Payroll --}}
@if($guard === 'web' && $can('payroll'))
    <p class="{{ $lbl }}">Payroll</p>

    <a href="{{ route('payroll') }}"
       class="{{ $base }}{{ request()->is('payroll') ? $on : $off }}">
        <i class="fas fa-credit-card {{ $ico }}"></i>
        <span>Payroll</span>
    </a>
@endif
{{-- ══ Events ═══════════════════════════════════════════════════════ --}}
@if($can('events'))
    <p class="{{ $lbl }}">Events</p>

    {{-- route: eventIndex → /event --}}
    <a href="{{ route('eventIndex') }}"
       class="{{ $base }}{{ request()->is('event','event/*') ? $on : $off }}">
        <i class="fas fa-calendar-alt {{ $ico }}"></i>
        <span>Events & Calendar</span>
    </a>
@endif

{{-- ══ Reports — web guard only ════════════════════════════════════ --}}
@if($guard === 'web' && $can('reports'))
    <p class="{{ $lbl }}">Reports</p>

    {{-- route: systemPerformance → /system-performance --}}
    <a href="{{ route('systemPerformance') }}"
       class="{{ $base }}{{ request()->is('system-performance*') ? $on : $off }}">
        <i class="fas fa-chart-bar {{ $ico }}"></i>
        <span>Reports & Analytics</span>
    </a>
@endif

{{-- ══ Recruitment — web guard only ═══════════════════════════════ --}}
@if($guard === 'web')
    @php
        $showJobs  = $can('job_postings');    // route: jlist  → /career
        $showApps  = $can('job_applications');// route: appList → /career/applications
        $careersOn = request()->is('career','career/*');
    @endphp
    @if($showJobs || $showApps)
        <p class="{{ $lbl }}">Recruitment</p>

        <button type="button" onclick="toggleCareers()"
                class="{{ $base }} w-full {{ $careersOn ? $on : $off }}">
            <i class="fas fa-briefcase {{ $ico }}"></i>
            <span class="flex-1 text-left">Careers</span>
            <i id="careers-chevron"
               class="fas fa-chevron-right text-[10px] transition-transform duration-200
                      {{ $careersOn ? 'rotate-90' : '' }}"></i>
        </button>

        <div id="careers-sub" class="mt-0.5 space-y-0.5 {{ $careersOn ? '' : 'hidden' }}">
            @if($showJobs)
                {{-- route: jlist → GET /career --}}
                <a href="{{ route('jlist') }}"
                   class="{{ $sub }}{{ request()->is('career') && !request()->is('career/applications*') ? $subOn : $subOff }}">
                    <i class="fas fa-circle text-[5px] w-4 shrink-0 text-center opacity-50"></i>
                    <span>Job Openings</span>
                </a>
            @endif
            @if($showApps)
                {{-- route: appList → GET /career/applications --}}
                <a href="{{ route('appList') }}"
                   class="{{ $sub }}{{ request()->is('career/applications*') ? $subOn : $subOff }}">
                    <i class="fas fa-circle text-[5px] w-4 shrink-0 text-center opacity-50"></i>
                    <span>Applications</span>
                </a>
            @endif
        </div>
    @endif
@endif

{{-- ══ Administration — web guard only ════════════════════════════ --}}
@if($guard === 'web')
    @php
        $showUsers  = $can('user_management');  // route: ulist        → /user
        $showOff    = $can('office_management');// route: officeList   → /office
        $showDeans  = $can('deans_list');       // route: deanlist     → /deans
        $showAdmin  = $showUsers || $showOff || $showDeans;
    @endphp
    @if($showAdmin)
        <p class="{{ $lbl }}">Administration</p>

        @if($showUsers)
            <a href="{{ route('ulist') }}"
               class="{{ $base }}{{ request()->is('user','user/*') ? $on : $off }}">
                <i class="fas fa-user-cog {{ $ico }}"></i>
                <span>Users</span>
            </a>
        @endif

        @if($showOff)
            <a href="{{ route('officeList') }}"
               class="{{ $base }}{{ request()->is('office','office/*') ? $on : $off }}">
                <i class="fas fa-building {{ $ico }}"></i>
                <span>Offices</span>
            </a>
        @endif

        @if($showDeans)
            <a href="{{ route('deanlist') }}"
               class="{{ $base }}{{ request()->is('deans','deans/*') ? $on : $off }}">
                <i class="fas fa-graduation-cap {{ $ico }}"></i>
                <span>Deans List</span>
            </a>
        @endif

    @endif
@endif
