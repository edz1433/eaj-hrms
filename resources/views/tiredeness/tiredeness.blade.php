@extends('layouts.master')

@section('pageTitle', 'Tardiness & Undertime')

@section('body')
@php
    $canChooseEmployee = $guard === 'web';
    $selectedEmployeeId = (string) ($employeeId ?? 0);
    $selectedMonth = $month ?? now()->format('Y-m');
    $employeeCount = method_exists($employeeall, 'count') ? $employeeall->count() : count($employeeall ?? []);
    $selectedEmployeeLabel = 'ALL';

    foreach ($employeeall as $emp) {
        if ($selectedEmployeeId === (string) $emp->emp_ID) {
            $selectedEmployeeLabel = trim($emp->lname . ' ' . $emp->prefix . ' ' . $emp->fname . ' ' . (isset($emp->mname) ? substr($emp->mname, 0, 1) . '.' : ''));
            break;
        }
    }

    if ($guard === 'employee' && auth()->guard('employee')->check()) {
        $authEmployee = auth()->guard('employee')->user();
        $selectedEmployeeLabel = trim($authEmployee->lname . ' ' . $authEmployee->prefix . ' ' . $authEmployee->fname . ' ' . ($authEmployee->mname ? substr($authEmployee->mname, 0, 1) . '.' : ''));
    }

    $iframeSrc = $selectedEmployeeId !== ''
        ? route('pdfTirednes', ['employeeId' => $selectedEmployeeId, 'month' => $selectedMonth])
        : '';
@endphp

<div class="flex flex-col gap-5 p-4 lg:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <i data-lucide="clock-alert" class="h-4 w-4"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-foreground">Tardiness & Undertime</h1>
                <p class="mt-0.5 text-xs text-muted-foreground">Generate late and undertime summaries by employee and month.</p>
            </div>
        </div>

        <span class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-card px-3 py-1.5 text-xs font-medium text-muted-foreground shadow-sm">
            <i data-lucide="users" class="h-3.5 w-3.5"></i>
            {{ number_format($employeeCount) }} employees
        </span>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
        <form class="border-b border-border/60 bg-muted/10 p-4" action="{{ route('tirednessSearch') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(240px,1fr)_220px_150px] lg:items-end">
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Employee Name</label>
                    <input type="hidden" name="employee" id="employee" value="{{ $selectedEmployeeId }}" required>

                    @if($canChooseEmployee)
                        <div class="relative" data-employee-combobox>
                            <input type="text" id="employee-search"
                                value="{{ $selectedEmployeeLabel }}"
                                placeholder="Search employee"
                                autocomplete="off"
                                class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 pr-9 text-sm text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                data-employee-search>
                            <i data-lucide="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                            <div class="absolute z-40 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-xl border border-border/60 bg-card p-1 shadow-xl" data-employee-options>
                                <button type="button"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-foreground hover:bg-muted"
                                    data-employee-option
                                    data-value="0"
                                    data-label="ALL">
                                    <span class="truncate">ALL</span>
                                    <span class="ml-2 shrink-0 font-mono text-[11px] text-muted-foreground">All employees</span>
                                </button>
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
                    @else
                        <div class="rounded-xl border border-border/60 bg-muted/30 px-3 py-2 text-sm font-medium text-foreground">
                            {{ $selectedEmployeeLabel }}
                        </div>
                    @endif
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">To</label>
                    <input type="month" name="month" id="date" value="{{ $selectedMonth }}"
                        class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                        required>
                </div>

                <button type="submit"
                    class="inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90">
                    <i data-lucide="file-down" class="h-4 w-4"></i>
                    Generate
                </button>
            </div>
        </form>

        <div class="bg-background p-4">
            @if($iframeSrc)
                <iframe src="{{ $iframeSrc }}" class="h-[72vh] min-h-[620px] w-full rounded-xl border border-border/60 bg-white"></iframe>
            @else
                <div class="flex min-h-[420px] flex-col items-center justify-center rounded-xl border border-dashed border-border/70 bg-muted/20 p-8 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <i data-lucide="file-clock" class="h-6 w-6"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-foreground">No report preview yet</p>
                    <p class="mt-1 max-w-sm text-xs text-muted-foreground">Choose an employee and month, then generate the Tardiness & Undertime report.</p>
                </div>
            @endif
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
        if (hidden.value !== '') return;

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
