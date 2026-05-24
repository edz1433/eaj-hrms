@extends('layouts.master')

@section('body')
@php
    $canChooseEmployee = ($guard === 'web') || (($acctstat ?? 0) == 1);
    $selectedEmployeeId = isset($employee) && $employee ? $employee->emp_ID : request('employee');
    $selectedPeriod = $period ?? 1;
    $selectedDate = $date ?? now()->format('Y-m');
    $selectedOvertime = (int) ($overtime ?? 0);
    $previewUrl = isset($employee, $period, $date)
        ? route('dtr-pdf', ['employee' => $employee->emp_ID, 'period' => $period, 'date' => $date, 'overtime' => $overtime])
        : null;
    $employeeCount = method_exists($employeeall, 'count') ? $employeeall->count() : count($employeeall ?? []);
    $selectedEmployeeLabel = '';
    foreach ($employeeall as $emp) {
        if ($selectedEmployeeId == $emp->emp_ID) {
            $selectedEmployeeLabel = trim($emp->lname . ' ' . $emp->prefix . ' ' . $emp->fname . ' ' . (isset($emp->mname) ? substr($emp->mname, 0, 1) . '.' : ''));
            break;
        }
    }
@endphp

<div class="flex flex-col gap-5 p-4 lg:flex-row lg:items-start lg:p-6">
    <div class="lg:w-64 xl:w-72 shrink-0">
        @include('dtr.submenu')
    </div>

    <div class="min-w-0 flex-1 space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i data-lucide="calendar-clock" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-foreground">Daily Time Record</h1>
                        <p class="mt-0.5 text-xs text-muted-foreground">Generate printable attendance summaries by employee and period.</p>
                    </div>
                </div>
            </div>

            <span class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-card px-3 py-1.5 text-xs font-medium text-muted-foreground shadow-sm">
                <i data-lucide="users" class="h-3.5 w-3.5"></i>
                {{ number_format($employeeCount) }} available
            </span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <form class="border-b border-border/60 bg-muted/10 p-4" action="{{ route('dtrSearch') }}" method="POST">
                @csrf
                <input type="hidden" name="acctstat" value="{{ $acctstat }}">

                <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(220px,1.4fr)_180px_180px_120px_150px] xl:items-end">
                    @if($canChooseEmployee)
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Employee</label>
                            <input type="hidden" name="employee" id="employee" value="{{ $selectedEmployeeId }}" required>
                            <div class="relative" data-employee-combobox>
                                <input type="text" id="employee-search"
                                    value="{{ $selectedEmployeeLabel }}"
                                    placeholder="Search employee"
                                    autocomplete="off"
                                    class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 pr-9 text-sm text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    data-employee-search>
                                <i data-lucide="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                                <div class="absolute z-40 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-xl border border-border/60 bg-card p-1 shadow-xl" data-employee-options>
                                    @foreach($employeeall as $emp)
                                        @php
                                            $empLabel = trim($emp->lname . ' ' . $emp->prefix . ' ' . $emp->fname . ' ' . (isset($emp->mname) ? substr($emp->mname, 0, 1) . '.' : ''));
                                        @endphp
                                        <button type="button"
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-foreground hover:bg-muted"
                                            data-employee-option
                                            data-value="{{ $emp->emp_ID }}"
                                            data-label="{{ $empLabel }}">
                                            <span class="truncate">{{ $empLabel }}</span>
                                            <span class="ml-2 shrink-0 font-mono text-[11px] text-muted-foreground">{{ $emp->emp_ID }}</span>
                                        </button>
                                    @endforeach
                                    <div class="hidden px-3 py-2 text-xs text-muted-foreground" data-employee-empty>No employees found</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Period</label>
                        <select class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                            name="period" required>
                            <option value="1" @selected($selectedPeriod == 1)>1st half</option>
                            <option value="2" @selected($selectedPeriod == 2)>2nd half</option>
                            <option value="3" @selected($selectedPeriod == 3)>Whole Month</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Month</label>
                        <input type="month" name="date" id="date" value="{{ $selectedDate }}"
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
                            <i data-lucide="file-text" class="h-6 w-6"></i>
                        </div>
                        <p class="mt-4 text-sm font-semibold text-foreground">No DTR preview yet</p>
                        <p class="mt-1 max-w-sm text-xs text-muted-foreground">Choose an employee, month, and period, then generate the printable Daily Time Record.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-employee-combobox]');
    if (!root) return;

    const search = root.querySelector('[data-employee-search]');
    const hidden = document.getElementById('employee');
    const menu = root.querySelector('[data-employee-options]');
    const options = Array.from(root.querySelectorAll('[data-employee-option]'));
    const empty = root.querySelector('[data-employee-empty]');

    function openMenu() {
        menu.classList.remove('hidden');
    }

    function closeMenu() {
        menu.classList.add('hidden');
    }

    function filterOptions() {
        const term = search.value.trim().toLowerCase();
        let visible = 0;

        options.forEach(option => {
            const haystack = `${option.dataset.label || ''} ${option.dataset.value || ''}`.toLowerCase();
            const matches = !term || haystack.includes(term);
            option.classList.toggle('hidden', !matches);
            if (matches) visible++;
        });

        empty.classList.toggle('hidden', visible > 0);
        openMenu();
    }

    search.addEventListener('focus', filterOptions);
    search.addEventListener('input', () => {
        hidden.value = '';
        filterOptions();
    });

    options.forEach(option => {
        option.addEventListener('click', () => {
            hidden.value = option.dataset.value || '';
            search.value = option.dataset.label || '';
            closeMenu();
        });
    });

    document.addEventListener('click', event => {
        if (!root.contains(event.target)) {
            closeMenu();
        }
    });

    search.closest('form')?.addEventListener('submit', event => {
        if (hidden.value) return;

        const firstVisible = options.find(option => !option.classList.contains('hidden'));
        if (firstVisible && search.value.trim()) {
            hidden.value = firstVisible.dataset.value || '';
            search.value = firstVisible.dataset.label || '';
            return;
        }

        event.preventDefault();
        openMenu();
        search.focus();
    });
});
</script>
@endpush
