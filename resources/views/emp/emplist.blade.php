@extends('layouts.master')

@section('body')
@php
    $usesLocation = ($locCtx->enabled ?? true) && $locations->isNotEmpty();
    $hasFilters = request()->hasAny($usesLocation ? ['search','status','location','dept'] : ['search','status','dept']);
    $total      = $stats['total'];
    $newPct     = $total > 0 ? round(($stats['new_this_year'] / $total) * 100) : 0;
    $locationIcon = match($locCtx->type ?? 'office') {
        'campus' => 'school',
        'site' => 'map-pinned',
        'plant' => 'factory',
        'facility' => 'building-2',
        default => 'building-2',
    };
@endphp

<div class="flex flex-col gap-5 p-4 sm:p-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-foreground">Employee Directory</h1>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                    {{ number_format($total) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="{{ $locationIcon }}" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>{{ $orgName }}
                &nbsp;·&nbsp; {{ $locCtx->directory_label ?? ($locCtx->plural . '-based directory') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
            <a href="{{ route('empQr') }}" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-card px-3.5 py-2 text-xs font-medium text-foreground shadow-sm transition-all hover:border-border hover:shadow-md">
                <i data-lucide="id-card" class="h-3.5 w-3.5 opacity-70"></i> All ID Cards
            </a>
            <a href="{{ route('genEmp') }}" target="_blank"
            class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-card px-3.5 py-2 text-xs font-medium text-foreground shadow-sm transition-all hover:border-border hover:shadow-md">
                <i data-lucide="file-down" class="h-3.5 w-3.5 text-red-500 opacity-80"></i> Export PDF
            </a>
            <button type="button"
                onclick="document.getElementById('modal-employee-backdrop').classList.replace('hidden','flex')"
                class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-primary/90 hover:shadow-md">
                <i data-lucide="user-plus" class="h-3.5 w-3.5"></i> Add Employee
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-6 lg:gap-4">

        {{-- Total --}}
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Total Active</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($total) }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="users" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width: 100%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">All active records</p>
        </div>

        {{-- Status breakdown (top 3) --}}
        @foreach($stats['by_status']->take(3) as $idx => $s)
        @php
            $pct   = $total > 0 ? round(($s->count / $total) * 100) : 0;
        @endphp
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                        {{ $s->status_name }}
                    </p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($s->count) }}</p>
                </div>
                <span class="mt-0.5 rounded-lg bg-primary/10 px-2 py-0.5 text-[11px] font-bold text-primary">
                    {{ $pct }}%
                </span>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary transition-all" style="width:{{ $pct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">{{ $pct }}% of workforce</p>
        </div>
        @endforeach

        {{-- New hires --}}
        {{-- <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">New This Year</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['new_this_year']) }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="user-plus" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:{{ $newPct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">Hired {{ now()->year }}</p>
        </div> --}}

        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{{ $locCtx->office_label ?? 'Offices' }}</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($offices->count()) }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="building-2" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:100%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">Available assignments</p>
        </div>

        @if($usesLocation)
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="absolute -right-3 -top-3 h-16 w-16m"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{{ $locCtx->plural }}</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($locations->count()) }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="{{ $locationIcon }}" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:100%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">Visible for this org type</p>
        </div>
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">

        {{-- â”€â”€ Filter bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <form method="GET" action="{{ route('employees') }}" id="filter-form">
            <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

            <div class="flex flex-col gap-3 border-b border-border/60 px-4 py-3 sm:flex-row sm:items-center sm:flex-wrap">

                {{-- Search --}}
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <i data-lucide="search" class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground pointer-events-none"></i>
                    <input type="text" name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, ID, position, office..."
                        class="w-full rounded-xl border border-border/60 bg-background py-2 pl-8 pr-3 text-xs text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                {{-- Status filter --}}
                <div class="flex items-center gap-1 flex-wrap">
                    <a href="{{ route('employees', array_merge(request()->except(['status','page']), [])) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition
                            {{ !request('status') ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                        All
                        <span class="ml-1 {{ !request('status') ? 'opacity-80' : 'opacity-60' }}">{{ $total }}</span>
                    </a>
                    @foreach($statuses as $st)
                        @php $cnt = $stats['by_status']->firstWhere('id', $st->id)?->count ?? 0; @endphp
                        @if($cnt > 0)
                        <a href="{{ route('employees', array_merge(request()->except(['status','page']), ['status' => $st->id])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition
                                {{ request('status') == $st->id ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                            {{ $st->status_name }}
                            <span class="ml-1 {{ request('status') == $st->id ? 'opacity-80' : 'opacity-60' }}">{{ $cnt }}</span>
                        </a>
                        @endif
                    @endforeach
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    {{-- Location filter --}}
                    @if($usesLocation)
                    <select name="location" onchange="this.form.submit()"
                            class="rounded-xl border border-border/60 bg-background px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer">
                        <option value="">All {{ $locCtx->plural }}</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    {{-- Department filter --}}
                    <select name="dept" onchange="this.form.submit()"
                            class="hidden sm:block rounded-xl border border-border/60 bg-background px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer">
                        <option value="">All {{ $locCtx->office_label ?? 'Departments' }}</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ request('dept') == $office->id ? 'selected' : '' }}>
                                {{ $office->office_abbr ?? $office->office_name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Active filters badge + reset --}}
                    @if($hasFilters)
                    <a href="{{ route('employees') }}"
                    class="inline-flex items-center gap-1 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 dark:text-red-400">
                        <i data-lucide="x" class="h-3 w-3"></i> Clear
                    </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- â”€â”€ Table â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border/60 bg-muted/25 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                        <th class="w-10 px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Employee</th>
                        <th class="px-4 py-3 text-left">Emp ID</th>
                        <th class="px-4 py-3 text-left">{{ $usesLocation ? $locCtx->label . ' · ' : '' }}{{ $locCtx->office_label ?? 'Office' }}</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="hidden px-4 py-3 text-left lg:table-cell">Since</th>
                        <th class="hidden px-4 py-3 text-left xl:table-cell">Contact</th>
                        <th class="w-16 px-4 py-3 text-center">Active</th>
                        <th class="w-28 px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
            <tbody id="employees-table-body" class="divide-y divide-border/40">

                @forelse ($employees as $emp)
                @php
                    $years  = 0; $months = 0;
                    $hireDate = $emp->date_hired;
                    if ($hireDate) {
                        try {
                            $d = (new DateTime($hireDate))->diff(new DateTime);
                            $years  = $d->y;
                            $months = $d->m;
                        } catch (\Exception $e) {}
                    }
                    $rowNum = ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration;
                @endphp
                <tr class="group transition-colors hover:bg-muted/20" id="tr-{{ $emp->id }}">

                    {{-- # --}}
                    <td class="px-4 py-3 text-xs tabular-nums text-muted-foreground/60">
                        {{ $rowNum }}
                    </td>

                    {{-- Employee --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <x-avatar :profile="$emp->profile"
                                      :fname="$emp->fname"
                                      :lname="$emp->lname"
                                      class="h-9 w-9 shrink-0 rounded-full object-cover text-[11px] ring-2 ring-primary/30 select-none" />
                            <div class="min-w-0 leading-tight">
                                <p class="truncate text-sm font-semibold text-foreground">
                                    {{ $emp->lname }},
                                    {{ $emp->fname }}{{ $emp->suffix ? ' '.$emp->suffix : '' }}{{ isset($emp->mname) ? ' '.strtoupper(substr($emp->mname,0,1)).'.' : '' }}
                                </p>
                                <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{{ $emp->position }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Emp ID --}}
                    <td class="px-4 py-3">
                        <span class="rounded-md bg-muted/70 px-2 py-1 font-mono text-[11px] text-foreground/60">
                            {{ $emp->emp_ID }}
                        </span>
                    </td>

                    {{-- Location - Office --}}
                    <td class="px-4 py-3">
                        <div class="space-y-1">
                            @if($emp->location_abbr && $emp->location_abbr !== '')
                                <span class="inline-flex items-center gap-1 rounded-md bg-muted/60 px-1.5 py-0.5 text-[11px] font-medium text-foreground/70">
                                    <i data-lucide="{{ $locationIcon }}" class="h-3 w-3 opacity-50"></i>
                                    {{ $emp->location_abbr }}
                                </span>
                            @endif
                            @if($emp->office_name)
                                <p class="truncate text-[11px] text-muted-foreground max-w-[140px]">
                                    {{ $emp->office_name }}
                                </p>
                            @endif
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3">
                        @if($emp->partime_rate > 0)
                            <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-[11px] font-semibold text-primary ring-1 ring-primary/20">
                                Part-time
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-[11px] font-semibold text-primary ring-1 ring-primary/20">
                                {{ $emp->status_name ?? '' }}
                            </span>
                        @endif
                    </td>

                    {{-- Since --}}
                    <td class="hidden px-4 py-3 lg:table-cell">
                        @if($hireDate)
                            <div class="leading-tight">
                                <p class="text-xs font-medium text-foreground tabular-nums">
                                    {{ $years > 0 ? $years.'y ' : '' }}{{ $months }}m
                                </p>
                                <p class="mt-0.5 text-[11px] text-muted-foreground">
                                    {{ \Carbon\Carbon::parse($hireDate)->format('M d, Y') }}
                                </p>
                            </div>
                        @else
                            <span class="text-xs text-muted-foreground/40"></span>
                        @endif
                    </td>

                    {{-- Contact --}}
                    <td class="hidden px-4 py-3 xl:table-cell">
                        <span class="max-w-[160px] truncate text-[11px] text-muted-foreground block">
                            {{ $emp->org_email ?: '' }}
                        </span>
                    </td>

                    {{-- Active toggle --}}
                    <td class="px-4 py-3 text-center">
                        <button type="button"
                            id="toggle-{{ $emp->id }}"
                            data-state="{{ $emp->stat_1 }}"
                            onclick="openToggleDialog(this, '{{ addslashes($emp->fname.' '.($emp->mname ? strtoupper(substr($emp->mname,0,1)).'. ' : '').$emp->lname) }}', {{ $emp->id }})"
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-primary/30 {{ $emp->stat_1 ? 'bg-primary' : 'bg-muted-foreground/25' }}">
                            <span class="pointer-events-none inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transition-transform {{ $emp->stat_1 ? 'translate-x-4' : 'translate-x-0' }}"></span>
                        </button>
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-0.5">
                            {{-- Edit through PDS --}}
                            <a href="{{ route('PDS', shortEncrypt((string) $emp->id)) }}" title="Edit Personal Information"
                                class="rounded-lg p-2 text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                            </a>

                            @if($emp->emp_status == 1)
                                <a href="{{ route('leavesRead', $emp->id) }}" title="Leave Credits"
                                class="rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50 dark:hover:bg-emerald-950/40">
                                    <i data-lucide="calendar-check" class="h-3.5 w-3.5"></i>
                                </a>
                            @else
                                <span title="Leave Credits (N/A)"
                                    class="rounded-lg p-2 text-muted-foreground/25 cursor-not-allowed">
                                    <i data-lucide="calendar-check" class="h-3.5 w-3.5"></i>
                                </span>
                            @endif

                            <a href="{{ route('PDS', shortEncrypt((string) $emp->id)) }}" title="Personal Data Sheet"
                            class="rounded-lg p-2 text-sky-600 transition hover:bg-sky-50 dark:hover:bg-sky-950/40">
                                <i data-lucide="id-card" class="h-3.5 w-3.5"></i>
                            </a>

                            <button type="button" title="Working Hours"
                                onclick="openOfficialTime('{{ $emp->emp_ID }}')"
                                class="rounded-lg p-2 text-violet-600 transition hover:bg-violet-50 dark:hover:bg-violet-950/40">
                                <i data-lucide="clock-3" class="h-3.5 w-3.5"></i>
                            </button>

                            {{-- Delete --}}
                            <button type="button" title="Delete Employee"
                                onclick="openDeleteDialog({{ $emp->id }}, '{{ addslashes($emp->fname.' '.($emp->mname ? strtoupper(substr($emp->mname,0,1)).'. ' : '').$emp->lname) }}')"
                                class="rounded-lg p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                            </button>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-16">
                        <div class="flex flex-col items-center justify-center gap-4 text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted">
                                @if($hasFilters)
                                    <i data-lucide="filter-x" class="h-6 w-6 text-muted-foreground/40"></i>
                                @else
                                    <i data-lucide="users" class="h-6 w-6 text-muted-foreground/40"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-foreground">
                                    {{ $hasFilters ? 'No results match your filters' : 'No employees yet' }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ $hasFilters ? 'Try adjusting your search or filters.' : 'Get started by adding your first employee.' }}
                                </p>
                            </div>
                            @if($hasFilters)
                                <a href="{{ route('employees') }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                                    <i data-lucide="x" class="h-3.5 w-3.5"></i> Clear Filters
                                </a>
                            @else
                                <a href="{{ route('empAdd') }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90 transition">
                                    <i data-lucide="user-plus" class="h-3.5 w-3.5"></i> Add Employee
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        {{-- â”€â”€ Pagination footer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        @if($employees->hasPages() || $employees->total() > 0)
        <div class="border-t border-border/60 bg-muted/10 px-4 py-3">
            {{ $employees->links() }}
        </div>
        @endif

    </div>

</div>

@include('emp.modals')

<div id="ot-backdrop" class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeOfficialTime()"></div>

    <div class="relative h-screen max-h-screen overflow-y-auto rounded-none border-l border-border bg-card shadow-2xl w-full max-w-md rounded-2xl border border-border/60 bg-card shadow-2xl">
        <div class="flex items-start justify-between border-b border-border/60 px-5 py-4">
            <div>
                <h3 class="font-semibold text-foreground">Official Working Hours</h3>
                <p class="mt-0.5 text-xs text-muted-foreground">AM In / AM Out / PM In / PM Out per day</p>
            </div>
            <button onclick="closeOfficialTime()"
                class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <form id="ot-form" action="{{ route('OfficialTimeCreate') }}" method="POST">
            @csrf
            <input type="hidden" name="empid" id="ot-empid">
            <div class="p-5 space-y-2.5">
                <div id="ot-message" class="hidden rounded-lg border px-3 py-2 text-xs"></div>
                <div class="flex items-center gap-3 pb-0.5">
                    <span class="w-10 shrink-0"></span>
                    <div class="grid flex-1 grid-cols-4 gap-2 text-center text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                        <span>AM In</span><span>AM Out</span><span>PM In</span><span>PM Out</span>
                    </div>
                </div>
                @foreach([['MON','mon'],['TUE','tue'],['WED','wed'],['THU','thu'],['FRI','fri']] as [$lbl,$key])
                <div class="flex items-center gap-3">
                    <span class="w-10 shrink-0 text-[11px] font-bold tracking-wider text-muted-foreground">{{ $lbl }}</span>
                    <div class="grid flex-1 grid-cols-4 gap-2">
                        @foreach(['mornin','mornout','noonin','noonout'] as $slot)
                        <input type="time" name="{{ $key }}_{{ $slot }}"
                               class="rounded-lg border border-border/60 bg-background px-2 py-1.5 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20">
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-3.5">
                <button type="button" onclick="closeOfficialTime()"
                    class="rounded-xl border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                    Cancel
                </button>
                <button type="submit"
                    id="ot-submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-60">
                    <i data-lucide="save" class="h-3.5 w-3.5"></i> Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

{{-- â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—
     â•‘  CONFIRM TOGGLE MODAL                                                â•‘
     â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="confirm-backdrop" class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
    <div class="relative h-screen max-h-screen overflow-y-auto rounded-none border-l border-border bg-card shadow-2xl w-full max-w-xs rounded-2xl border border-border/60 bg-card shadow-2xl">
        <div class="p-6">
            <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/30">
                <i data-lucide="triangle-alert" class="h-5 w-5 text-red-600 dark:text-red-400"></i>
            </div>
            <h3 class="font-semibold text-foreground">Confirm Account Change</h3>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground" id="confirmMessage"></p>
        </div>
        <div class="flex justify-end gap-2 border-t border-border/60 px-6 py-4">
            <button id="cancelToggleBtn"
                class="rounded-xl border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                Cancel
            </button>
            <button id="confirmToggleBtn"
                class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                Confirm
            </button>
        </div>
    </div>
</div>

{{-- â•”â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•—
     â•‘  DELETE CONFIRM MODAL                                                â•‘
     â•šâ•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="delete-backdrop" class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
    <div class="relative h-screen max-h-screen overflow-y-auto rounded-none border-l border-border bg-card shadow-2xl w-full max-w-xs rounded-2xl border border-border/60 bg-card shadow-2xl">
        <div class="p-6">
            <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/30">
                <i data-lucide="trash-2" class="h-5 w-5 text-red-600 dark:text-red-400"></i>
            </div>
            <h3 class="font-semibold text-foreground">Delete Employee</h3>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground" id="deleteMessage"></p>
            <p class="mt-1 text-xs text-red-500 font-medium">This action cannot be undone.</p>
        </div>
        <div class="flex justify-end gap-2 border-t border-border/60 px-6 py-4">
            <button id="cancelDeleteBtn"
                class="rounded-xl border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn"
                class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// â”€â”€ Official Time â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const officialTimeFields = [
    'mon_mornin', 'mon_mornout', 'mon_noonin', 'mon_noonout',
    'tue_mornin', 'tue_mornout', 'tue_noonin', 'tue_noonout',
    'wed_mornin', 'wed_mornout', 'wed_noonin', 'wed_noonout',
    'thu_mornin', 'thu_mornout', 'thu_noonin', 'thu_noonout',
    'fri_mornin', 'fri_mornout', 'fri_noonin', 'fri_noonout',
];

function setOfficialTimeMessage(message = '', type = 'error') {
    const box = document.getElementById('ot-message');
    if (!box) return;

    box.textContent = message;
    box.classList.toggle('hidden', !message);
    box.className = 'rounded-lg border px-3 py-2 text-xs ' + (message ? '' : 'hidden ') +
        (type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300'
            : 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300');
}

function officialTimeDefaultFor(name) {
    if (name.includes('noonin')) return '13:00';
    if (name.includes('noonout')) return '17:00';
    if (name.includes('mornout')) return '12:00';
    return '08:00';
}

function fillOfficialTime(schedule = {}) {
    officialTimeFields.forEach(name => {
        const el = document.querySelector(`#ot-backdrop [name="${name}"]`);
        if (!el) return;

        el.value = schedule[name] ? String(schedule[name]).substring(0, 5) : officialTimeDefaultFor(name);
    });
}

function openOfficialTime(empId) {
    document.getElementById('ot-empid').value = empId;
    setOfficialTimeMessage();
    fillOfficialTime();

    fetch('{{ url("/employees/official-time") }}/' + encodeURIComponent(empId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Unable to load official time.');
        fillOfficialTime(data.data || {});
    })
    .catch(() => {
        setOfficialTimeMessage('Using default working hours because the saved schedule could not be loaded.');
    });

    document.getElementById('ot-backdrop').classList.replace('hidden', 'flex');
}
function closeOfficialTime() {
    document.getElementById('ot-backdrop').classList.replace('flex', 'hidden');
}

document.getElementById('ot-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const form = event.currentTarget;
    const submit = document.getElementById('ot-submit');
    setOfficialTimeMessage();
    submit?.setAttribute('disabled', 'disabled');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: new FormData(form),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            const errors = data.errors ? Object.values(data.errors).flat() : [];
            throw new Error(errors[0] || data.message || 'Unable to save official time.');
        }

        fillOfficialTime(data.data || {});
        setOfficialTimeMessage(data.message || 'Official time saved successfully.', 'success');
        setTimeout(closeOfficialTime, 700);
    } catch (error) {
        setOfficialTimeMessage(error.message || 'Unable to save official time.');
    } finally {
        submit?.removeAttribute('disabled');
    }
});

// â”€â”€ Toggle confirm â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let _empId, _state, _btn;

function openToggleDialog(btn, name, empId) {
    _btn    = btn;
    _empId  = empId;
    _state  = btn.dataset.state === '1' ? 0 : 1;
    const action = _state ? 'enable' : 'disable';
    document.getElementById('confirmMessage').innerHTML =
        `Are you sure you want to <strong>${action}</strong> the account for <strong>${name}</strong>?`;
    document.getElementById('confirm-backdrop').classList.replace('hidden', 'flex');
}

// â”€â”€ Delete confirm â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function openDeleteDialog(empId, name) {
    document.getElementById('deleteMessage').innerHTML =
        `Are you sure you want to permanently delete <strong>${name}</strong>?`;
    document.getElementById('confirmDeleteBtn').dataset.id = empId;
    document.getElementById('delete-backdrop').classList.replace('hidden', 'flex');
}

document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
    document.getElementById('delete-backdrop').classList.replace('flex', 'hidden');
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async (event) => {
    const button = event.currentTarget;
    const empId = button.dataset.id;
    if (!empId) return;

    button.disabled = true;
    try {
        const response = await fetch(`{{ url('/employees/delete') }}/${encodeURIComponent(empId)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Delete failed.');
        }

        document.getElementById(`tr-${empId}`)?.remove();
        document.getElementById('delete-backdrop').classList.replace('flex', 'hidden');
        if (window.Toast) {
            window.Toast.success('Employee deleted', data.message || 'Employee deleted successfully.');
        }
    } catch (error) {
        if (window.Toast) {
            window.Toast.error('Delete failed', error.message);
        } else {
            alert(error.message);
        }
    } finally {
        button.disabled = false;
    }
});

document.getElementById('cancelToggleBtn').addEventListener('click', () => {
    document.getElementById('confirm-backdrop').classList.replace('flex', 'hidden');
});

document.getElementById('confirmToggleBtn').addEventListener('click', () => {
    document.getElementById('confirm-backdrop').classList.replace('flex', 'hidden');
    fetch('{{ route("toggleAcctStat") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: _empId, stat_1: _state })
    })
    .then(r => r.json())
    .then(d => {
        if (!d.success) return;
        const thumb = _btn.querySelector('span');
        _btn.dataset.state = String(_state);
        if (_state) {
            _btn.classList.replace('bg-muted-foreground/25', 'bg-primary');
            thumb.classList.replace('translate-x-0', 'translate-x-4');
        } else {
            _btn.classList.replace('bg-primary', 'bg-muted-foreground/25');
            thumb.classList.replace('translate-x-4', 'translate-x-0');
        }
    });
});
</script>
@endpush

@endsection
