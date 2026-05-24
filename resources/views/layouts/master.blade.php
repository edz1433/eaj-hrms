@php
    $resolvedTheme = $sysTheme ?? 'ea';
    $hexToHsl = function (?string $hex): ?string {
        if (!$hex || !preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $m)) {
            return null;
        }

        $hex = $m[1];
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            $h = match ($max) {
                $r => (($g - $b) / $d + ($g < $b ? 6 : 0)),
                $g => (($b - $r) / $d + 2),
                default => (($r - $g) / $d + 4),
            } / 6;
        }

        return round($h * 360) . ' ' . round($s * 100) . '% ' . round($l * 100) . '%';
    };
    $primaryHsl = $hexToHsl($sysPrimaryColor ?? null);
    $accentHsl = $hexToHsl($sysAccentColor ?? null);
    $appTitle = $sysName ?? 'EAJ HRMS';
    $pageTitle = trim($__env->yieldContent('pageTitle'));
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $resolvedTheme }}" x-data="sidebar()" x-init="init()" x-cloak>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appTitle }}{{ $pageTitle ? ' - ' . $pageTitle : '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        (function () {
            var savedTheme = '{{ $resolvedTheme }}';
            var uiVersion = 'tailwind-cdn-theme-v3';
            if (localStorage.getItem('hrmsUiVersion') !== uiVersion) {
                localStorage.setItem('darkMode', 'false');
                localStorage.setItem('hrmsUiVersion', uiVersion);
            }
            localStorage.setItem('theme', savedTheme);
            document.documentElement.setAttribute('data-theme', savedTheme);
            var darkMode = localStorage.getItem('darkMode');
            if (darkMode === null) {
                darkMode = false;
                localStorage.setItem('darkMode', 'false');
            } else {
                darkMode = darkMode === 'true';
            }
            if (darkMode) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            window.__sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            window.__darkMode = darkMode;
            window.__currentTheme = savedTheme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/custom.js'])
    <style id="theme-token-safety">
        html:not(.dark) {
            --background: 266 100% 98%;
            --foreground: 263 42% 15%;
            --card: 0 0% 100%;
            --card-foreground: 263 42% 15%;
            --border: 268 45% 88%;
            --input: 268 45% 88%;
            --muted: 268 100% 96%;
            --muted-foreground: 263 18% 44%;
            --sidebar: var(--background);
            --sidebar-foreground: var(--foreground);
            --sidebar-border: var(--border);
            --sidebar-accent: var(--accent);
            --sidebar-accent-foreground: var(--accent-foreground);
        }
        body, .bg-background { background-color: hsl(var(--background)) !important; color: hsl(var(--foreground)) !important; }
        .bg-sidebar { background-color: hsl(var(--sidebar)) !important; }
        .text-foreground { color: hsl(var(--foreground)) !important; }
        .text-sidebar-foreground, .text-sidebar-foreground\/80, .text-sidebar-foreground\/70, .text-sidebar-foreground\/60 { color: hsl(var(--sidebar-foreground)) !important; }
        .bg-sidebar-primary { background-color: hsl(var(--sidebar-primary)) !important; }
        .text-sidebar-primary-foreground { color: hsl(var(--sidebar-primary-foreground)) !important; }
        .bg-sidebar-accent, .hover\:bg-sidebar-accent:hover { background-color: hsl(var(--sidebar-accent)) !important; }
        .text-sidebar-accent-foreground, .hover\:text-sidebar-accent-foreground:hover { color: hsl(var(--sidebar-accent-foreground)) !important; }
        .border-border { border-color: hsl(var(--border)) !important; }
    </style>
    @if($primaryHsl || $accentHsl)
        <style id="system-theme-overrides">
            html {
                @if($primaryHsl)
                    --primary: {{ $primaryHsl }} !important;
                    --ring: {{ $primaryHsl }} !important;
                    --sidebar-primary: {{ $primaryHsl }} !important;
                @endif
                @if($accentHsl)
                    --accent: {{ $accentHsl }} !important;
                    --sidebar-accent: {{ $accentHsl }} !important;
                @endif
            }
        </style>
    @endif
    <link rel="shortcut icon" href="{{ asset('Uploads/ease-icon.png') }}">
    @stack('styles')
</head>

<body class="min-h-screen overflow-x-hidden bg-background font-sans text-foreground antialiased">

@php $authUser = auth()->guard($guard)->user(); @endphp

{{-- LOADER --}}
<div id="globalLoader" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-background/80 backdrop-blur">
    <div class="relative flex items-center justify-center">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></div>
        <div class="absolute flex h-10 w-10 items-center justify-center rounded-xl bg-card shadow"><img src="{{ asset('Uploads/ease-icon.png') }}" alt="EAJ"></div>
    </div>
</div>

{{-- MOBILE OVERLAY --}}
<div x-show="isMobile && sidebarOpen"
     x-transition.opacity.duration.300
     @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
     :class="{ 'open': sidebarOpen }"
     hidden>
</div>

{{-- SIDEBAR CONTAINER --}}
<div class="fixed left-0 top-0 z-30 flex h-screen flex-col overflow-hidden bg-sidebar shadow-sm transition-all duration-300 ease-out max-lg:w-[260px] max-lg:-translate-x-full"
     :class="{
         'w-[260px]': !sidebarCollapsed || isMobile,
         'w-[70px]': sidebarCollapsed && !isMobile,
         'translate-x-0': isMobile && sidebarOpen
     }">
    <aside class="flex h-full w-full flex-col overflow-hidden border-r border-sidebar-border bg-inherit">
        {{-- Sidebar Header with Logo --}}
        <div class="flex h-14 shrink-0 items-center justify-between border-b border-sidebar-border px-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground shadow-sm">
                    <img src="{{ asset('Uploads/ease-icon.png') }}" alt="EAJ" class="h-5 w-5 object-contain">
                </div>
                <span x-show="!sidebarCollapsed || isMobile"
                      x-transition:enter="transition-all duration-300 ease-out"
                      x-transition:enter-start="opacity-0 w-0"
                      x-transition:enter-end="opacity-100 w-auto"
                      x-transition:leave="transition-all duration-250 ease-in"
                      x-transition:leave-start="opacity-100 w-auto"
                      x-transition:leave-end="opacity-0 w-0"
                      class="truncate text-sm font-semibold text-sidebar-foreground" data-system-name>{{ $appTitle }}</span>
                <span x-show="sidebarCollapsed && !isMobile" class="text-sm font-bold text-sidebar-foreground">E</span>
            </div>
            {{-- Collapse toggle button (desktop only) --}}
            <button @click="toggleSidebar" class="hidden h-8 w-8 items-center justify-center rounded-lg text-sidebar-foreground/70 transition hover:bg-sidebar-accent hover:text-sidebar-accent-foreground lg:flex" x-show="!isMobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     :class="{ 'rotate-180': sidebarCollapsed }">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </button>
        </div>

        {{-- Navigation Menu --}}
        <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-4" data-auto-animate>
            @php
                use App\Helpers\MenuHelper;
                $can = fn($key) => $guard === 'web' ? MenuHelper::userCanAccess($key) && MenuHelper::isVisible($key) : MenuHelper::isVisible($key);
                $routeMap = MenuHelper::routeMap();
                $mkUrl = fn($key) => ($routeMap[$key] ?? null) ? route($routeMap[$key]) : '#';
                $activeRouteMap = MenuHelper::activeRouteMap();
                $isActive = fn($key) => request()->routeIs(...($activeRouteMap[$key] ?? array_filter([$routeMap[$key] ?? null])));
                $hasActiveChild = fn($children) => collect($children)->contains(fn($_, $ck) => $isActive($ck));
            @endphp

            @if($guard === 'employee')
                @php
                    $employeeStatusName = strtolower((string) optional(\App\Models\Status::find($authUser->emp_status ?? null))->status_name);
                    $isRegularEmployee = $employeeStatusName === 'regular';
                    $employeeNavItems = [
                        [
                            'label' => 'Dashboard',
                            'icon' => 'layout-dashboard',
                            'url' => route('dashboard'),
                            'active' => request()->routeIs('dashboard'),
                        ],
                        [
                            'label' => 'DTR',
                            'icon' => 'clock',
                            'url' => route('dtr-read'),
                            'active' => request()->routeIs('dtr-read', 'dtrSearch', 'dtr-pdf'),
                        ],
                        [
                            'label' => 'PDS',
                            'icon' => 'file-text',
                            'url' => route('empPDS'),
                            'active' => request()->routeIs('empPDS', 'PDS', 'familybg', 'educbg', 'eligibility*', 'work-experience*', 'voluntary-work*', 'learning-dev*', 'otherInfo', 'infoQuestion', 'references', 'govids', 'signature'),
                        ],
                        [
                            'label' => 'Payslip',
                            'icon' => 'receipt',
                            'url' => route('payroll'),
                            'active' => request()->routeIs('payroll'),
                        ],
                    ];

                    if ($isRegularEmployee) {
                        array_splice($employeeNavItems, 2, 0, [[
                            'label' => 'Leave Management',
                            'icon' => 'file-pen-line',
                            'url' => route('leavesReadEmp'),
                            'active' => request()->routeIs('leavesReadEmp', 'leaveStatus', 'historyRead', 'previewLeave'),
                        ]]);
                    }
                @endphp

                @foreach($employeeNavItems as $item)
                    <a href="{{ $item['url'] }}" class="group flex min-h-10 items-center justify-between gap-3 rounded-xl px-3 text-sm font-medium text-sidebar-foreground/80 no-underline transition-all duration-200 ease-out hover:translate-x-1 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-primary/40 active:scale-[0.99] {{ $item['active'] ? 'bg-sidebar-accent text-sidebar-accent-foreground shadow-sm' : '' }}">
                        <div class="flex items-center gap-3" :class="{ 'justify-center w-full': sidebarCollapsed && !isMobile }">
                            <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0 transition-transform duration-200 ease-out group-hover:scale-110 group-hover:rotate-3"></i>
                            <span x-show="!sidebarCollapsed || isMobile" x-transition class="flex-1 truncate text-left transition-transform duration-200 group-hover:translate-x-0.5">{{ $item['label'] }}</span>
                        </div>
                    </a>
                @endforeach
            @else
            @foreach(MenuHelper::all() as $key => $item)
                @php
                    $children = $item['children'] ?? [];
                    $hasChildren = !empty($children);
                    if ($hasChildren) {
                        $visibleChildren = [];
                        foreach ($children as $ck => $ci) if ($can($ck)) $visibleChildren[$ck] = $ci;
                        $showItem = !empty($visibleChildren);
                        $groupOpen = $hasActiveChild($visibleChildren);
                    } else {
                        $showItem = $can($key);
                        $visibleChildren = [];
                        $groupOpen = false;
                    }
                @endphp

                @if($showItem)
                    @if($hasChildren)
                        <div x-data="{ open: {{ $groupOpen ? 'true' : 'false' }} }" class="flex flex-col gap-1" data-auto-animate>
                            <button @click="open = !open" class="group flex min-h-10 w-full items-center justify-between gap-3 rounded-xl px-3 text-sm font-medium text-sidebar-foreground/80 transition-all duration-200 ease-out hover:translate-x-1 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-primary/40 active:scale-[0.99] {{ $groupOpen ? 'bg-sidebar-accent text-sidebar-accent-foreground shadow-sm' : '' }}">
                                <div class="flex items-center gap-3 min-w-0" :class="{ 'justify-center w-full': sidebarCollapsed && !isMobile }">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0 transition-transform duration-200 ease-out group-hover:scale-110 group-hover:rotate-3"></i>
                                    <span x-show="!sidebarCollapsed || isMobile" x-transition class="flex-1 truncate text-left transition-transform duration-200 group-hover:translate-x-0.5">{{ $item['label'] }}</span>
                                </div>
                                <svg x-show="!sidebarCollapsed || isMobile" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="transition-transform duration-200" :class="{ 'rotate-180': open }"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-show="open && (!sidebarCollapsed || isMobile)" x-collapse.duration.300 class="ml-5 mt-1 flex flex-col gap-1 border-l border-sidebar-border pl-3">
                                @foreach($visibleChildren as $childKey => $childItem)
                                    <a href="{{ $mkUrl($childKey) }}" class="group flex min-h-9 items-center gap-2 rounded-lg px-3 text-sm text-sidebar-foreground/70 no-underline transition-all duration-200 ease-out hover:translate-x-1 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground {{ $isActive($childKey) ? 'bg-sidebar-accent/80 text-sidebar-accent-foreground' : '' }}">
                                        <i data-lucide="{{ $childItem['icon'] }}" class="h-4 w-4 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                                        <span>{{ $childItem['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $mkUrl($key) }}" class="group flex min-h-10 items-center justify-between gap-3 rounded-xl px-3 text-sm font-medium text-sidebar-foreground/80 no-underline transition-all duration-200 ease-out hover:translate-x-1 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-primary/40 active:scale-[0.99] {{ $isActive($key) ? 'bg-sidebar-accent text-sidebar-accent-foreground shadow-sm' : '' }}">
                            <div class="flex items-center gap-3" :class="{ 'justify-center w-full': sidebarCollapsed && !isMobile }">
                                <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0 transition-transform duration-200 ease-out group-hover:scale-110 group-hover:rotate-3"></i>
                                <span x-show="!sidebarCollapsed || isMobile" x-transition class="flex-1 truncate text-left transition-transform duration-200 group-hover:translate-x-0.5">{{ $item['label'] }}</span>
                            </div>
                        </a>
                    @endif
                @endif
            @endforeach
            @endif
        </nav>

        {{-- User Profile Footer (Static at bottom) --}}
        <div class="shrink-0 border-t border-sidebar-border p-3">
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button @click="open = !open"
                        class="group w-full rounded-xl border border-transparent p-2 text-left transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-sidebar-border hover:bg-sidebar-accent hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-sidebar-primary"
                        :class="{ 'is-open': open }"
                        :aria-expanded="open.toString()">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0">
                            <x-avatar :profile="$authUser->profile ?? null" 
                                      :fname="$authUser->fname ?? ''" 
                                      :lname="$authUser->lname ?? ''" 
                                      size="md" 
                                      class="h-8 w-8 rounded-full" />
                        </div>
                        <div x-show="!sidebarCollapsed || isMobile" x-cloak class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-sidebar-foreground">
                                {{ ucwords(strtolower($authUser->fname)) }} {{ ucwords(strtolower($authUser->lname ?? '')) }}
                            </p>
                            <p class="truncate text-xs text-sidebar-foreground/60">
                                {{ $authUser->email ?? '' }}
                            </p>
                        </div>
                        <svg x-show="!sidebarCollapsed || isMobile" x-cloak class="shrink-0 text-sidebar-foreground/50 transition-transform" :class="{ 'rotate-180': open }" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </div>
                </button>

                <!-- Dropdown Menu (positioned above footer) -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="absolute bottom-full left-0 right-0 mb-2 rounded-xl border border-sidebar-border bg-popover p-1 text-popover-foreground shadow-xl">
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form-sidebar" class="hidden">@csrf</form>
                        <button onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-destructive transition hover:bg-destructive/10">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

{{-- MAIN CONTENT WRAPPER --}}
<div class="flex min-h-screen flex-col bg-background transition-all duration-300 ease-in-out"
     :class="{
         'lg:ml-[260px]': !sidebarCollapsed || isMobile,
         'lg:ml-[70px]': sidebarCollapsed && !isMobile
     }">

    {{-- TOPBAR (shadcn style) --}}
    <header class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-border bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/70">
        <div class="flex items-center gap-2">
            <button @click="toggleSidebar" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-foreground transition hover:bg-accent hover:text-accent-foreground">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                <span class="sr-only">Toggle sidebar</span>
            </button>
            @hasSection('pageTitle')
                <div class="hidden items-center gap-2 text-sm text-muted-foreground sm:flex">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7l-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                    <span class="font-medium text-foreground">@yield('pageTitle')</span>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2">
            {{-- Dark Mode Toggle --}}
            <div x-data="{
                    darkMode: document.documentElement.classList.contains('dark'),
                    toggleDarkMode() {
                        this.darkMode = !this.darkMode;
                        document.documentElement.classList.toggle('dark', this.darkMode);
                        localStorage.setItem('darkMode', this.darkMode ? 'true' : 'false');
                        window.__darkMode = this.darkMode;
                        window.refreshUi?.();
                    }
                }">
                <button @click="toggleDarkMode()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-foreground transition hover:bg-accent hover:text-accent-foreground" aria-label="Toggle theme">
                    <svg x-show="!darkMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    <svg x-show="darkMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                    <span class="sr-only">Toggle theme</span>
                </button>
            </div>

            {{-- NOTIFICATIONS DROPDOWN --}}
            @php
                $notificationItems = $guard === 'employee'
                    ? collect($notifications1 ?? [])
                    : collect($notifications ?? []);
                $unreadCountValue = $guard === 'employee'
                    ? ($notificationsCount1 ?? 0)
                    : ($notificationsCount ?? 0);
                $unreadCount = is_countable($unreadCountValue)
                    ? count($unreadCountValue)
                    : (int) $unreadCountValue;

                $notificationTitle = function ($notif) {
                    if (($notif->module ?? '') === 'leave') {
                        $leaveType = $notif->leave_type ? ' leave' : '';
                        return ($notif->category == 1 ? 'Leave application submitted' : 'Leave application update') . $leaveType;
                    }

                    if (($notif->module ?? '') === 'pds') {
                        return match ((string) $notif->category) {
                            '1' => 'New eligibility submitted',
                            '2' => 'New work experience submitted',
                            '3' => 'New voluntary work submitted',
                            '4' => 'New learning development submitted',
                            default => 'PDS notification',
                        };
                    }

                    return ucwords(str_replace(['_', '-'], ' ', (string) ($notif->module ?? 'Notification')));
                };

                $notificationName = function ($notif) use ($guard) {
                    if ($guard === 'employee') {
                        return 'HR Office';
                    }

                    return $notif->leave_emp_fullname
                        ?? $notif->pds_emp_eligi_fullname
                        ?? $notif->pds_emp_workexp_fullname
                        ?? $notif->pds_emp_volworks_fullname
                        ?? $notif->pds_emp_learndev_fullname
                        ?? 'System';
                };

                $notificationHref = function ($notif) use ($guard) {
                    if (($notif->module ?? '') === 'leave') {
                        return isset($notif->leave_emp_id) && $guard !== 'employee'
                            ? route('leaveStatus', $notif->leave_emp_id)
                            : route('leaveStatus');
                    }

                    if (($notif->module ?? '') === 'pds') {
                        if ($guard === 'employee') {
                            return route('empPDS');
                        }

                        $menu = match ((string) $notif->category) {
                            '1' => 'eligibility',
                            '2' => 'work-experience',
                            '3' => 'voluntary-work',
                            '4' => 'learning-dev',
                            default => 'empPDS',
                        };
                        $menid = $notif->pds_emp_eligi_id
                            ?? $notif->pds_emp_workexp_id
                            ?? $notif->pds_emp_volworks_id
                            ?? $notif->pds_emp_learndev_id
                            ?? 0;

                        return $menu === 'empPDS'
                            ? route('empPDS')
                            : route('updateNotif', ['menid' => $menid, 'lappid' => $notif->lapp_id ?? 0, 'menu' => $menu]);
                    }

                    return '#';
                };
            @endphp
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button @click="open = !open" class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-foreground transition hover:bg-accent hover:text-accent-foreground">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    @if($unreadCount > 0)<span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px] font-bold text-destructive-foreground">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif
                </button>
                <div x-show="open" x-transition class="absolute right-0 top-full mt-2 w-80 overflow-hidden rounded-xl border border-border bg-popover text-popover-foreground shadow-xl">
                    <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Notifications</h3>
                            @if($unreadCount > 0)<p class="mt-0.5 text-xs text-muted-foreground">{{ $unreadCount }} unread</p>@endif
                        </div>
                        @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                @csrf
                                <button type="submit" class="rounded-lg px-2 py-1 text-xs font-medium text-primary transition hover:bg-primary/10">Mark all as read</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        @forelse($notificationItems as $notif)
                            @php
                                $notifStatus = (int) ($notif->notifstat ?? $notif->status ?? 1);
                                $timeDifference = $notif->notif_created_at ?? $notif->created_at
                                    ? \Carbon\Carbon::parse($notif->notif_created_at ?? $notif->created_at)->timezone('Asia/Manila')->diffForHumans()
                                    : '';
                            @endphp
                            <a href="{{ $notificationHref($notif) }}" class="flex gap-3 border-b border-border/70 px-4 py-3 text-sm no-underline transition hover:bg-muted/40">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $notifStatus === 0 ? 'bg-primary' : 'bg-muted-foreground/30' }}"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-foreground">{{ ucwords(strtolower($notificationName($notif))) }}</span>
                                    <span class="mt-0.5 block text-xs text-muted-foreground">{{ $notificationTitle($notif) }}</span>
                                    @if($timeDifference)<span class="mt-1 block text-[11px] text-muted-foreground/80">{{ $timeDifference }}</span>@endif
                                </span>
                            </a>
                        @empty
                            <div class="flex flex-col items-center gap-2 p-8 text-center text-muted-foreground">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><line x1="4" y1="4" x2="20" y2="20"/></svg>
                                <div><p class="text-sm font-medium text-foreground">No notifications</p><p class="text-xs text-muted-foreground">You're all caught up!</p></div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT AREA --}}
    <main class="flex-1 p-4 sm:p-6">
        @yield('body')
    </main>

    {{-- FOOTER (static, matching design tokens) --}}
    <footer class="border-t border-border bg-background/95 py-4 backdrop-blur">
        <div class="flex flex-col items-center justify-between gap-3 px-6 text-xs text-muted-foreground sm:flex-row">
            <div class="flex flex-wrap items-center gap-2">
                <span>&copy; {{ date('Y') }} <span data-system-name>{{ $appTitle }}</span></span>
                <span class="h-3 w-px bg-border"></span>
                <button type="button" onclick="ModalManager.open('dpViewModal')" class="text-muted-foreground transition hover:text-foreground">Data Privacy Policy</button>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span>Maintained by</span>
                <a href="https://www.facebook.com/cpsumiso.main" target="_blank" class="text-muted-foreground transition hover:text-foreground">MIS Department</a>
            </div>
        </div>
    </footer>
</div>

{{-- DATA PRIVACY MODAL --}}
<div id="dpViewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm" onclick="if(event.target===this)ModalManager.close('dpViewModal')">
    <div class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-border bg-card text-card-foreground shadow-2xl">
        <div class="flex items-center justify-between border-b border-border px-5 py-4"><h2 class="text-lg font-semibold text-foreground">Data Privacy Policy</h2><button onclick="ModalManager.close('dpViewModal')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-accent hover:text-foreground"><i data-lucide="x" class="w-4 h-4"></i></button></div>
        <div class="max-h-[70vh] overflow-y-auto p-5">@include('data-privacy')</div>
        <div class="flex items-center justify-end gap-3 border-t border-border bg-muted/20 px-5 py-4"><span class="text-xs text-muted-foreground">&copy; {{ now()->year }} <span data-system-name>{{ $appTitle }}</span></span></div>
    </div>
</div>

{{-- DATA PRIVACY NOTICE (if needed) --}}
@if($guard === 'employee' && $authUser->dpn == 0)
<div id="dpnModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-2xl border border-border bg-card text-card-foreground shadow-2xl">
        <div class="flex items-center justify-between border-b border-border px-5 py-4"><div><h2 class="text-lg font-semibold text-foreground">Data Privacy Notice</h2><p class="mt-1 text-sm text-muted-foreground">Review how your information is collected, used, and protected before continuing.</p></div></div>
        <div class="max-h-[70vh] overflow-y-auto p-5">@include('data-privacy')</div>
        <div class="flex items-center justify-between gap-3 border-t border-border bg-muted/20 px-5 py-4">
            <span class="text-xs text-muted-foreground">&copy; {{ now()->year }} <span data-system-name>{{ $appTitle }}</span></span>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('dataPrivacyNotice') }}">@csrf<button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">I Accept</button></form>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="inline-flex items-center justify-center rounded-xl border border-destructive/40 px-4 py-2 text-sm font-semibold text-destructive transition hover:bg-destructive/10">Decline</button></form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
function sidebar() {
    return {
        sidebarOpen: true,
        sidebarCollapsed: false,
        isMobile: window.innerWidth < 1024,
        init() {
            this.isMobile = window.innerWidth < 1024;
            this.sidebarOpen = !this.isMobile;
            if (!this.isMobile) {
                this.sidebarCollapsed = window.__sidebarCollapsed || false;
                this.sidebarOpen = true;
            }
            window.addEventListener('resize', () => {
                const wasMobile = this.isMobile;
                this.isMobile = window.innerWidth < 1024;
                if (!wasMobile && this.isMobile) { this.sidebarOpen = false; this.sidebarCollapsed = false; }
                else if (wasMobile && !this.isMobile) { this.sidebarOpen = true; }
            });
            this.$watch('sidebarCollapsed', value => { if (!this.isMobile) localStorage.setItem('sidebarCollapsed', value); });
            const observer = new MutationObserver(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme', 'class'] });
        },
        toggleSidebar() {
            if (this.isMobile) this.sidebarOpen = !this.sidebarOpen;
            else { this.sidebarCollapsed = !this.sidebarCollapsed; this.sidebarOpen = true; }
        }
    };
}
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    // Add loaded class to enable transitions after initial render
    document.documentElement.classList.add('js-loaded');
    document.documentElement.classList.remove('js-loading');
});
</script>

@stack('scripts')
</body>
</html>







