@extends('layouts.master')

@section('body')
@include('leaves.style')
@php
    $isLeaveManagement = ($leaveMode ?? ($guard == 'web' ? 'management' : 'personal')) === 'management';
@endphp
<div class="p-4 sm:p-6">
    @if($isLeaveManagement)
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-foreground">Leave Management</h1>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="calendar-days" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>
                Maintain credit balances and review employee leave applications.
            </p>
        </div>
    </div>
    @endif
    <div class="grid gap-4 lg:grid-cols-[24rem_minmax(0,1fr)]">
        @include("leaves.side-menu")
        <div class="min-w-0">
            <div class="overflow-hidden rounded-lg border border-border/60 border-t-4 border-t-primary bg-card shadow-sm">
                <div class="border-b border-border/60 px-5 py-4">
                    @include("leaves.top-menu")
                </div>
                <div class="p-5">
                @if($isLeaveManagement)
                    @if(($creditStats['all'] ?? count($leaves)) == 0)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 dark:border-amber-900/60 dark:bg-amber-950/20">
                        <div class="mx-auto max-w-2xl text-center">
                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                <i data-lucide="wallet-cards" class="h-5 w-5"></i>
                            </div>
                            <h2 class="mt-3 text-base font-semibold text-foreground">Input Leave Credit Balance to Start</h2>
                            <p class="mt-1 text-sm text-muted-foreground">Please enter employee leave credit balance below to proceed.</p>
                                        <form class="form-horizontal" action="{{ route('leavesCreate') }}" method="POST">
                                            @csrf
                                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                        
                                                <div>
                                                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Sick Leave</label>
                                                        <input type="hidden" name="empid" value="{{ $employee->id }}">
                                                        <input class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20" type="number" name="sl" step="0.001" min="0" max="{{ (($creditStats['all'] ?? count($leaves)) == 0) ? '' : 30 }}" placeholder="0.00" required>
                                                </div>
                                                
                                                <div>
                                                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Vacation Leave</label>
                                                        <input class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20" type="number" name="vl" step="0.001" min="0" required>
                                                </div>

                                                <div class="sm:col-span-2">
                                                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Remarks</label>
                                                        <textarea class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20" type="text" name="remarks" step="0.001" rows="3"></textarea>
                                                </div>

                                                <div class="sm:col-span-2 text-right">
                                                    <button type="submit" name="btn-submit" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90">
                                                        <i data-lucide="save" class="h-3.5 w-3.5"></i> Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                        </div>
                    </div>    
                    @else
                    @php
                        $ledgerTotal = method_exists($leaves, 'total') ? $leaves->total() : count($leaves);
                        $hasLedgerFilters = request()->hasAny(['search', 'type']);
                    @endphp
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-foreground">Credit Ledger</h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">{{ $ledgerTotal }} recorded {{ \Illuminate\Support\Str::plural('transaction', $ledgerTotal) }}</p>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-2 text-xs font-semibold text-primary-foreground shadow-sm transition hover:bg-primary/90" data-toggle="modal" data-target="#leaveModal">
                                <i data-lucide="plus" class="h-3.5 w-3.5"></i> Add Credit
                            </button>
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-destructive/20 bg-destructive/10 px-3.5 py-2 text-xs font-semibold text-destructive transition hover:bg-destructive/15" data-toggle="modal" data-target="#leaveModalDeduct">
                                <i data-lucide="minus" class="h-3.5 w-3.5"></i> Deduct
                            </button>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <form method="GET" action="{{ route('leavesRead', $employee->id) }}">
                            @if(request('type'))
                                <input type="hidden" name="type" value="{{ request('type') }}">
                            @endif
                            <div class="flex flex-col gap-3 border-b border-border/60 px-4 py-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <div class="relative min-w-[180px] flex-1 sm:max-w-xs">
                                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"></i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search remarks, month, encoder..." class="w-full rounded-xl border border-border/60 bg-background py-2 pl-8 pr-3 text-xs text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
                                </div>

                                <div class="flex flex-wrap items-center gap-1">
                                    <a href="{{ route('leavesRead', array_merge(['id' => $employee->id], request()->except(['type', 'page']))) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ !request('type') ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                                        All <span class="ml-1 opacity-70">{{ $creditStats['all'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('leavesRead', array_merge(['id' => $employee->id], request()->except('page'), ['type' => 'starting'])) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('type') === 'starting' ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                                        Starting <span class="ml-1 opacity-70">{{ $creditStats['starting'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('leavesRead', array_merge(['id' => $employee->id], request()->except('page'), ['type' => 'added'])) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('type') === 'added' ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                                        Added <span class="ml-1 opacity-70">{{ $creditStats['added'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('leavesRead', array_merge(['id' => $employee->id], request()->except('page'), ['type' => 'deducted'])) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('type') === 'deducted' ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                                        Deducted <span class="ml-1 opacity-70">{{ $creditStats['deducted'] ?? 0 }}</span>
                                    </a>
                                </div>

                                <div class="ml-auto flex items-center gap-2">
                                    <select name="per_page" onchange="this.form.submit()" class="rounded-xl border border-border/60 bg-background px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20">
                                        @foreach([10, 15, 25, 50] as $size)
                                            <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }} / page</option>
                                        @endforeach
                                    </select>
                                    @if($hasLedgerFilters)
                                        <a href="{{ route('leavesRead', $employee->id) }}" class="inline-flex items-center gap-1 rounded-xl border border-destructive/20 bg-destructive/10 px-3 py-2 text-xs font-medium text-destructive transition hover:bg-destructive/15">
                                            <i data-lucide="x" class="h-3 w-3"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border/60 bg-muted/25 text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                    <th class="w-10 px-4 py-3">#</th>
                                    <th class="px-4 py-3">Credits</th>
                                    <th class="px-4 py-3">For the Month of</th>
                                    <th class="px-4 py-3">Remarks</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Encoded</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead> 
                            <tbody class="divide-y divide-border/60">
                                @forelse($leaves as $leave)
                                @php $date = ($leave->created_at) ? \Carbon\Carbon::parse($leave->created_at)->format('F d, Y') : '' @endphp
                                    @php
                                        $rowNum = method_exists($leaves, 'currentPage') ? (($leaves->currentPage() - 1) * $leaves->perPage() + $loop->iteration) : $loop->iteration;
                                        $typeClass = $leave->stat == 0
                                            ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300'
                                            : (($leave->stat == 1 && $leave->days == 0)
                                                ? 'border-destructive/20 bg-destructive/10 text-destructive'
                                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300');
                                        $typeLabel = $leave->stat == 0 ? 'Starting Balance' : (($leave->stat == 1 && $leave->days == 0) ? 'Deducted' : 'Added');
                                    @endphp
                                    <tr id="tr-{{ $leave->id }}" class="transition hover:bg-muted/30">
                                        <td class="px-4 py-3 text-xs tabular-nums text-muted-foreground/60">{{ $rowNum }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-border/60 bg-background px-2.5 py-1 text-xs font-semibold text-foreground">
                                                    <span class="text-muted-foreground">SL</span>
                                                    <span class="font-mono tabular-nums">{{ number_format((float) $leave->earn_sl, 3) }}</span>
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-border/60 bg-background px-2.5 py-1 text-xs font-semibold text-foreground">
                                                    <span class="text-muted-foreground">VL</span>
                                                    <span class="font-mono tabular-nums">{{ number_format((float) $leave->earn_vl, 3) }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-foreground">{{ \Carbon\Carbon::parse($leave->date)->format('F Y') }}</div>
                                            <div class="mt-0.5 text-[11px] text-muted-foreground">{{ number_format((float) $leave->days, 3) }} days</div>
                                        </td>
                                        <td class="max-w-[280px] px-4 py-3 text-xs leading-relaxed text-muted-foreground">{{ $leave->remarks ?: 'No remarks' }}</td>
                                        <td class="px-4 py-3"><span class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $typeClass }}">{{ $typeLabel }}</span></td>
                                        <td class="px-4 py-3 text-xs text-muted-foreground">{{ $date }}</td>
                                        <td width="100" class="px-4 py-3">
                                            <div class="flex justify-end gap-1">
                                            <a href="#" class="leaves_edit inline-flex h-8 w-8 items-center justify-center rounded-lg text-primary transition hover:bg-primary/10" data-id="{{ $leave->id }}" title="Edit" data-toggle="modal" data-target="{{ ($leave->stat == 1 && $leave->days == 0) ?  '#leaveModalDeductEdit ' : '#leaveEditModal' }}  ">
                                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                            </a>
                                            <button class="{{ ($leave->stat == 0) ? 'cursor-not-allowed text-muted-foreground/35' : 'text-destructive leaves_delete hover:bg-destructive/10' }} inline-flex h-8 w-8 items-center justify-center rounded-lg transition" value="{{ $leave->id }}" title="Delete">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            </button>
                                            </div>
                                        </td>
                                    </tr> 
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-muted-foreground">
                                                    <i data-lucide="wallet-cards" class="h-5 w-5"></i>
                                                </div>
                                                <p class="mt-3 text-sm font-semibold text-foreground">{{ $hasLedgerFilters ? 'No transactions match your filters' : 'No credit transactions yet' }}</p>
                                                <p class="mt-1 text-xs text-muted-foreground">{{ $hasLedgerFilters ? 'Try changing the search or selected type.' : 'Add a credit transaction to start the ledger.' }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>                    
                        </div>
                        @if(method_exists($leaves, 'links') && ($leaves->hasPages() || $ledgerTotal > 0))
                            <div class="border-t border-border/60 px-4 py-3">
                                {{ $leaves->links() }}
                            </div>
                        @endif
                    </div>
                    @endif
                @else
                    @include('leaves.employee-application-form')
                @endif
            </div>                        
        </div>
    </div>
</div>
@if($isLeaveManagement)
    @include("leaves.modal")
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initializeSpecialLeaveSettings();
        initializeLeaveCreditModals();
    });

    function initializeLeaveCreditModals() {
        if (window.__leaveCreditModalsReady) {
            return;
        }

        window.__leaveCreditModalsReady = true;

        function openModal(modal) {
            if (!modal) {
                return;
            }

            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            modal.classList.add('show');
            document.body.classList.add('modal-open');

            if (!document.querySelector('.modal-backdrop.leave-credit-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show leave-credit-backdrop';
                document.body.appendChild(backdrop);
            }

            window.refreshIcons?.(modal);
        }

        function closeModal(modal) {
            if (!modal) {
                return;
            }

            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop.leave-credit-backdrop').forEach((backdrop) => backdrop.remove());
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-toggle="modal"][data-target]');
            if (trigger) {
                const modal = document.querySelector(trigger.dataset.target.trim());
                if (modal) {
                    event.preventDefault();
                    openModal(modal);
                }
                return;
            }

            const dismiss = event.target.closest('[data-dismiss="modal"]');
            if (dismiss) {
                event.preventDefault();
                closeModal(dismiss.closest('.modal'));
                return;
            }

            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            closeModal(document.querySelector('.modal.show'));
        });
    }

    function initializeSpecialLeaveSettings() {
        document.querySelectorAll('#modalSettingLeave .update-field').forEach((field) => {
            if (field.dataset.leaveSettingsReady === '1') {
                return;
            }

            field.dataset.leaveSettingsReady = '1';
            field.addEventListener('change', function () {
                saveSpecialLeaveBalance(field);
            });
        });
    }

    function saveSpecialLeaveBalance(field) {
        const employeeId = field.dataset.columnId;
        const column = field.dataset.columnName;
        const value = Number.parseFloat(field.value || 0);

        if (!employeeId || !column || Number.isNaN(value) || value < 0) {
            field.classList.add('border-destructive');
            return;
        }

        field.disabled = true;
        field.classList.remove('border-destructive');

        fetch('{{ route("employeeUpdate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: employeeId,
                column: column,
                value: value,
            }),
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to save balance.');
            }
            return data;
        })
        .then((data) => {
            const formatted = data.value || value.toFixed(3);
            const targetId = field.dataset.balanceTarget;
            field.value = formatted;

            if (targetId) {
                const balance = document.getElementById(targetId);
                if (balance) {
                    balance.textContent = formatted;
                }
            }

            window.safeToast?.success?.('Saved', 'Special leave balance updated.');
        })
        .catch((error) => {
            field.classList.add('border-destructive');
            window.safeToast?.error?.('Save failed', error.message || 'Unable to save balance.');
        })
        .finally(() => {
            field.disabled = false;
        });
    }
</script>
@endpush
