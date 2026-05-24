@extends('layouts.master')
@section('pageTitle', 'Dashboard')

@section('body')
@php
    $displayName = trim(collect([
        $authEmp->fname ? ucwords(strtolower($authEmp->fname)) : null,
        $authEmp->lname ? ucwords(strtolower($authEmp->lname)) : null,
    ])->filter()->implode(' '));
    $statusName = strtolower((string) optional($employeeStatus)->status_name);
    $isRegularEmployee = $statusName === 'regular';
    $calendarMonth = $today->copy()->startOfMonth();
    $calendarStart = $calendarMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
    $eventsByDate = collect($events)->groupBy(fn ($event) => \Carbon\Carbon::parse($event['start'])->toDateString());
@endphp

<div class="space-y-4">
    @if($canToggleHrDashboard ?? false)
        <div class="flex justify-end">
            <div class="inline-flex rounded-lg border border-border/60 bg-card p-1 text-xs font-semibold shadow-sm">
                <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-1.5 text-muted-foreground no-underline transition hover:bg-muted hover:text-foreground">HR Dashboard</a>
                <a href="{{ route('dashboard', ['dashboard' => 'personal']) }}" class="rounded-md bg-primary px-3 py-1.5 text-primary-foreground no-underline shadow-sm">Personal Dashboard</a>
            </div>
        </div>
    @endif

    <section class="overflow-hidden rounded-lg border border-border/60 bg-gradient-to-r from-emerald-50 via-sky-50 to-amber-50 p-6 shadow-sm dark:from-emerald-950/30 dark:via-sky-950/20 dark:to-amber-950/20">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <x-avatar :profile="$authEmp->profile ?? null"
                      :fname="$authEmp->fname ?? ''"
                      :lname="$authEmp->lname ?? ''"
                      class="h-20 w-20 rounded-full object-cover text-2xl ring-4 ring-white shadow" />
            <div class="min-w-0">
                <p class="text-xs font-medium text-muted-foreground">{{ $today->format('F d, Y') }}</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Welcome, {{ $displayName ?: 'Employee' }}</h1>
                <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                    <span>{{ $authEmp->position ?: 'Employee' }}</span>
                    <span class="h-4 w-px bg-border"></span>
                    <span>{{ $authEmp->emp_ID }}</span>
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-border/60 bg-card p-4 shadow-sm">
        <p class="text-sm font-semibold text-foreground">Date Range</p>
        <div class="mt-2 flex min-h-10 items-center gap-3 rounded-lg border border-border/70 bg-background px-3 text-sm text-foreground">
            <i data-lucide="calendar-days" class="h-4 w-4 text-primary"></i>
            <span>{{ $weekStart->format('M d, Y') }} - {{ $weekEnd->format('M d, Y') }}</span>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted-foreground">Leave Records</p>
                    <p class="mt-1 text-2xl font-semibold text-foreground">{{ number_format($leaveRecords) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Total applications filed</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i data-lucide="calendar-check" class="h-4 w-4"></i>
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted-foreground">Total Late</p>
                    <p class="mt-1 text-2xl font-semibold text-foreground">{{ $attendanceSummary['late_label'] }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">For selected range</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i data-lucide="clock-3" class="h-4 w-4"></i>
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted-foreground">Total Undertime</p>
                    <p class="mt-1 text-2xl font-semibold text-foreground">{{ $attendanceSummary['undertime_label'] }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">For selected range</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i data-lucide="hourglass" class="h-4 w-4"></i>
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted-foreground">Service</p>
                    <p class="mt-1 text-2xl font-semibold text-foreground">{{ $serviceYears }} {{ \Illuminate\Support\Str::plural('yr', $serviceYears) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $serviceSince ? 'Since ' . $serviceSince : 'Date hired not set' }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i data-lucide="badge-info" class="h-4 w-4"></i>
                </span>
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_36rem]">
        <div class="overflow-hidden rounded-lg border border-border/60 bg-card shadow-sm">
            <div class="border-b border-border/60 px-5 py-3">
                <h2 class="text-base font-semibold text-foreground">Campus Events</h2>
            </div>
            <div class="p-5">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground"><i data-lucide="chevron-left" class="h-4 w-4"></i></button>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground"><i data-lucide="chevron-right" class="h-4 w-4"></i></button>
                        <span class="inline-flex h-9 items-center rounded-lg bg-primary/90 px-4 text-sm font-semibold text-primary-foreground">today</span>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground">{{ $calendarMonth->format('F Y') }}</h3>
                    <div class="inline-flex overflow-hidden rounded-lg border border-primary/30 bg-primary text-xs font-semibold text-primary-foreground">
                        <span class="px-4 py-2">month</span>
                        <span class="border-l border-white/20 px-4 py-2">week</span>
                        <span class="border-l border-white/20 px-4 py-2">day</span>
                    </div>
                </div>

                <div class="grid grid-cols-7 border-l border-t border-border/70 text-xs">
                    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                        <div class="border-b border-r border-border/70 bg-muted/30 px-2 py-2 text-center font-semibold text-foreground">{{ $day }}</div>
                    @endforeach
                    @for($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay())
                        @php
                            $dateKey = $date->toDateString();
                            $dayEvents = $eventsByDate->get($dateKey, collect());
                            $inMonth = $date->month === $calendarMonth->month;
                        @endphp
                        <div class="min-h-28 border-b border-r border-border/70 bg-background p-2 {{ $inMonth ? '' : 'text-muted-foreground/40' }}">
                            <div class="text-right text-xs">{{ $date->day }}</div>
                            <div class="mt-2 space-y-1">
                                @foreach($dayEvents->take(2) as $event)
                                    <div class="truncate rounded px-2 py-1 text-[11px] font-medium text-white" style="background-color: {{ $event['color'] }}">
                                        {{ $event['title'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="overflow-hidden rounded-lg border border-border/60 bg-card shadow-sm">
                <div class="border-b border-border/60 px-5 py-3">
                    <h2 class="text-base font-semibold text-foreground">Recent DTR</h2>
                </div>
                <div class="divide-y divide-border/60">
                    @forelse($recentDtr as $record)
                        <div class="grid gap-3 px-5 py-3 sm:grid-cols-[9rem_1fr]">
                            <div>
                                <p class="text-sm font-semibold text-foreground">{{ $record['date'] }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $record['schedule_am'] }}</p>
                                <p class="text-xs leading-relaxed text-muted-foreground">{{ $record['schedule_pm'] }}</p>
                            </div>
                            @php
                                $dtrItems = ['am_in' => 'AM IN', 'am_out' => 'AM OUT', 'pm_in' => 'PM IN', 'pm_out' => 'PM OUT'];

                                if (!empty($record['overtime'])) {
                                    $dtrItems['overtime'] = 'OT';
                                }
                            @endphp
                            <div class="grid gap-2" style="grid-template-columns: repeat(auto-fit, minmax(4rem, 1fr));">
                                @foreach($dtrItems as $key => $label)
                                    <div class="rounded-lg border border-border/70 bg-background px-2 py-2 text-center">
                                        <p class="text-[10px] text-muted-foreground">{{ $label }}</p>
                                        <p class="mt-1 text-xs font-bold text-foreground">{{ $record[$key] ?: '--' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-muted-foreground">No DTR records for this date range.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-border/60 bg-card p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground">Quick Actions</h2>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('empPDS') }}" class="flex items-center gap-3 rounded-lg border border-border/60 bg-background px-4 py-3 text-sm font-medium text-foreground no-underline transition hover:bg-muted">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700"><i data-lucide="clipboard-list" class="h-4 w-4"></i></span>
                        Open PDS
                    </a>
                    <a href="{{ route('dtr-read') }}" class="flex items-center gap-3 rounded-lg border border-border/60 bg-background px-4 py-3 text-sm font-medium text-foreground no-underline transition hover:bg-muted">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-700"><i data-lucide="clock" class="h-4 w-4"></i></span>
                        View DTR
                    </a>
                    @if($isRegularEmployee)
                        <a href="{{ route('leavesReadEmp') }}" class="flex items-center gap-3 rounded-lg border border-border/60 bg-background px-4 py-3 text-sm font-medium text-foreground no-underline transition hover:bg-muted">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><i data-lucide="file-pen-line" class="h-4 w-4"></i></span>
                            File Leave Application
                        </a>
                    @endif
                    <a href="{{ route('payroll') }}" class="flex items-center gap-3 rounded-lg border border-border/60 bg-background px-4 py-3 text-sm font-medium text-foreground no-underline transition hover:bg-muted">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-700"><i data-lucide="receipt" class="h-4 w-4"></i></span>
                        Open Payslip
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
