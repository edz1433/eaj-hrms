@extends('layouts.master')

@section('body')
@php
    $canChooseEmployee = ($guard === 'web') || (($acctstat ?? 0) == 1);
    $selectedEmployeeId = $data['employeeId'] ?? request('employee');
    $dateFrom = $data['dateFrom'] ?? now()->startOfMonth()->format('Y-m-d');
    $dateTo = $data['dateTo'] ?? now()->format('Y-m-d');
    $selectedOvertime = (int) ($data['overtime'] ?? 0);
    $logCount = isset($data['logs']) ? count($data['logs']) : 0;
    $employeeCount = method_exists($employeeall, 'count') ? $employeeall->count() : count($employeeall ?? []);
    $previewUrl = isset($data) && $data
        ? route('logDtrView', ['employeeId' => $data['employeeId'] ?? 0, 'dateFrom' => $data['dateFrom'] ?? null, 'dateTo' => $data['dateTo'] ?? null, 'overtime' => $data['overtime'] ?? null])
        : null;
@endphp

<div class="flex flex-col gap-5 p-4 lg:flex-row lg:items-start lg:p-6">
    <div class="lg:w-64 xl:w-72 shrink-0">
        @include('dtr.submenu')
    </div>

    <div class="min-w-0 flex-1 space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="file-clock" class="h-4 w-4"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-foreground">DTR Logs</h1>
                    <p class="mt-0.5 text-xs text-muted-foreground">Generate printable raw time entries for a selected date range.</p>
                </div>
            </div>

            <span class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-card px-3 py-1.5 text-xs font-medium text-muted-foreground shadow-sm">
                <i data-lucide="list-checks" class="h-3.5 w-3.5"></i>
                {{ number_format($logCount) }} logs
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Available Employees</p>
                <p class="mt-1 text-lg font-bold text-foreground">{{ number_format($employeeCount) }}</p>
            </div>
            <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Range</p>
                <p class="mt-1 text-lg font-bold text-foreground">{{ \Carbon\Carbon::parse($dateFrom)->format('M j') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }}</p>
            </div>
            <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Mode</p>
                <p class="mt-1 text-lg font-bold text-foreground">{{ $selectedOvertime ? 'Overtime' : 'Regular Logs' }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <form class="border-b border-border/60 bg-muted/10 p-4" action="{{ route('dtrLogspost') }}" method="POST">
                @csrf
                <input type="hidden" name="acctstat" value="{{ $acctstat }}">

                <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(220px,1.4fr)_180px_180px_120px_150px] xl:items-end">
                    @if($canChooseEmployee)
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Employee</label>
                            <select class="select2 w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                name="employee" id="employee" required>
                                <option disabled {{ $selectedEmployeeId ? '' : 'selected' }}>Select employee</option>
                                @foreach($employeeall as $emp)
                                    <option value="{{ $emp->emp_ID }}" @selected($selectedEmployeeId == $emp->emp_ID)>
                                        {{ $emp->lname }} {{ $emp->prefix }} {{ $emp->fname }} {{ isset($emp->mname) ? substr($emp->mname, 0, 1) . '.' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">From</label>
                        <input type="date" name="date_from" id="inc_date1" value="{{ $dateFrom }}"
                            class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                            required>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">To</label>
                        <input type="date" name="date_to" id="inc_date2" value="{{ $dateTo }}"
                            class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                            required>
                    </div>

                    <label class="flex min-h-[40px] items-center gap-2 rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground">
                        <input type="checkbox" value="1" name="overtime" class="h-4 w-4 rounded border-border accent-primary" @checked($selectedOvertime == 1)>
                        <span class="text-xs font-medium">Overtime</span>
                    </label>

                    <button type="submit"
                        class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                        <i data-lucide="file-down" class="h-4 w-4"></i>
                        Generate
                    </button>
                </div>
            </form>

            <div class="bg-background p-4">
                @if($previewUrl)
                    <iframe src="{{ $previewUrl }}" class="h-[72vh] min-h-[620px] w-full rounded-xl border border-border/60 bg-white"></iframe>
                @else
                    <div class="flex min-h-[420px] flex-col items-center justify-center rounded-xl border border-dashed border-border/70 bg-muted/20 p-8 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <i data-lucide="file-clock" class="h-6 w-6"></i>
                        </div>
                        <p class="mt-4 text-sm font-semibold text-foreground">No logs preview yet</p>
                        <p class="mt-1 max-w-sm text-xs text-muted-foreground">Choose an employee and date range, then generate the printable DTR logs.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
