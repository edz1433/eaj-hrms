@php
    $fieldClass = 'w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground/50 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-70';
    $labelClass = 'mb-1 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground';
    $primaryButton = 'inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90';
    $secondaryButton = 'rounded-xl border border-border/60 px-4 py-2 text-xs font-medium text-foreground transition hover:bg-muted';
    $specialCredits = [
        ['Special Privilege Leave', 'special_pl', 'special-pl', $employee->special_pl ?? 0],
        ['Solo Parent Leave', 'solo_pl', 'solo-pl', $employee->solo_pl ?? 0],
        ['Study Leave', 'study_leave', 'study-leave', $employee->study_leave ?? 0],
        ['10-Day VAWC Leave', 'vawc_leave', 'vawc-leave', $employee->vawc_leave ?? 0],
        ['Rehabilitation Privilege', 'rehab_leave', 'rehab-leave', $employee->rehab_leave ?? 0],
        ['Special Leave Benefits for Women', 'benefits_leave', 'benefits-leave', $employee->benefits_leave ?? 0],
        ['Special Emergency (Calamity) Leave', 'calamity_leave', 'calamity-leave', $employee->calamity_leave ?? 0],
        ['Adoption Leave', 'adopt_leave', 'adopt-leave', $employee->adopt_leave ?? 0],
        ['Vacation Service Credit', 'servcred_leave', 'servcred-leave', $employee->servcred_leave ?? 0],
        ['Wellness Leave', 'well_leave', 'wellness-leave', $employee->well_leave ?? 0],
    ];
@endphp

<div class="modal fade" id="leaveModal" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content overflow-hidden rounded-2xl border-0 bg-card shadow-2xl">
            <form action="{{ route('leavesCreate') }}" method="POST">
                @csrf
                <div class="flex items-start justify-between border-b border-border/60 px-5 py-4">
                    <div>
                        <h5 class="text-sm font-semibold text-foreground" id="leaveModalLabel">Add Leave Credit</h5>
                        <p class="mt-0.5 text-xs text-muted-foreground">Add earned leave credits for a selected month.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="{{ $labelClass }}" for="date">Date</label>
                        <input class="{{ $fieldClass }}" type="month" id="date" name="date" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="days">Days</label>
                        <input type="hidden" name="empid" value="{{ $employee->id }}">
                        <input class="{{ $fieldClass }}" type="number" id="days" name="days" min="1" max="30" oninput="updateEquivalent()" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="sl">Sick Leave</label>
                        <input class="{{ $fieldClass }}" type="number" id="sl" name="sl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required readonly>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="vl">Vacation Leave</label>
                        <input class="{{ $fieldClass }}" type="number" id="vl" name="vl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required readonly>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="{{ $labelClass }}" for="remarks-add">Remarks</label>
                        <textarea class="{{ $fieldClass }}" id="remarks-add" name="remarks" rows="3" placeholder="Optional remarks"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                    <button type="button" class="{{ $secondaryButton }}" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="btn-submit" class="{{ $primaryButton }}">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSettingLeave" tabindex="-1" role="dialog" aria-labelledby="modalSettingLeaveLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content overflow-hidden rounded-2xl border-0 bg-card shadow-2xl">
            <div class="flex items-start justify-between border-b border-border/60 px-5 py-4">
                <div>
                    <h5 class="text-sm font-semibold text-foreground" id="modalSettingLeaveLabel">Special Leave Balances</h5>
                    <p class="mt-0.5 text-xs text-muted-foreground">Changes are saved automatically.</p>
                </div>
                <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted" data-dismiss="modal" aria-label="Close">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto p-5">
                <div class="divide-y divide-border/60 rounded-2xl border border-border/60 bg-background">
                    @foreach($specialCredits as [$label, $name, $balanceId, $value])
                    <div class="grid grid-cols-[minmax(0,1fr)_7rem] items-center gap-3 px-3 py-2.5">
                        <label class="text-xs font-medium leading-snug text-foreground" for="setting-{{ $name }}">{{ $label }}</label>
                        <input id="setting-{{ $name }}" class="{{ $fieldClass }} update-field text-center" type="number" name="{{ $name }}" value="{{ number_format((float) $value, 3, '.', '') }}" data-column-id="{{ $empid ?? $employee->id }}" data-column-name="{{ $name }}" data-balance-target="{{ $balanceId }}" step="0.001" min="0" max="999.999" placeholder="0.000" autocomplete="off">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveModalDeduct" tabindex="-1" role="dialog" aria-labelledby="leaveModalDeductLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content overflow-hidden rounded-2xl border-0 bg-card shadow-2xl">
            <form action="{{ route('leavescreditDeduct') }}" method="POST">
                @csrf
                <div class="flex items-start justify-between border-b border-border/60 px-5 py-4">
                    <div>
                        <h5 class="text-sm font-semibold text-foreground" id="leaveModalDeductLabel">Deduct Leave Credit</h5>
                        <p class="mt-0.5 text-xs text-muted-foreground">Deduct sick and vacation leave balances.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}" for="deduct-sl">Sick Leave</label>
                        <input type="hidden" name="empid" value="{{ $employee->id }}">
                        <input type="hidden" name="date" value="{{ \Carbon\Carbon::now()->format('Y-m') }}" required>
                        <input class="{{ $fieldClass }}" type="number" id="deduct-sl" name="sl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="deduct-vl">Vacation Leave</label>
                        <input class="{{ $fieldClass }}" type="number" id="deduct-vl" name="vl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="deduct-remarks">Remarks</label>
                        <textarea class="{{ $fieldClass }}" id="deduct-remarks" name="remarks" rows="3" placeholder="Optional remarks"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                    <button type="button" class="{{ $secondaryButton }}" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="btn-submit" class="{{ $primaryButton }}">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveEditModal" tabindex="-1" role="dialog" aria-labelledby="leaveEditModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content overflow-hidden rounded-2xl border-0 bg-card shadow-2xl">
            <form action="{{ route('leavesUpdate') }}" method="POST">
                @csrf
                <div class="flex items-start justify-between border-b border-border/60 px-5 py-4">
                    <div>
                        <h5 class="text-sm font-semibold text-foreground" id="leaveEditModalLabel">Edit Leave Credit</h5>
                        <p class="mt-0.5 text-xs text-muted-foreground">Update an existing credit entry.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="{{ $labelClass }}" for="date1">Date</label>
                        <input class="{{ $fieldClass }}" type="month" id="date1" name="date" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="days1">Days</label>
                        <input type="hidden" id="lcid" name="lcid">
                        <input class="{{ $fieldClass }}" type="number" id="days1" name="days" min="1" max="30" oninput="updateEquivalent1()" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="sl1">Sick Leave</label>
                        <input type="hidden" name="empid" value="{{ $employee->id }}">
                        <input class="{{ $fieldClass }}" type="number" id="sl1" name="sl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required readonly>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="vl1">Vacation Leave</label>
                        <input class="{{ $fieldClass }}" type="number" id="vl1" name="vl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required readonly>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="{{ $labelClass }}" for="remarks1">Remarks</label>
                        <textarea class="{{ $fieldClass }}" id="remarks1" name="remarks" rows="3" placeholder="Optional remarks"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                    <button type="button" class="{{ $secondaryButton }}" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="btn-submit" class="{{ $primaryButton }}">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="leaveModalDeductEdit" tabindex="-1" role="dialog" aria-labelledby="leaveModalDeductEditLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content overflow-hidden rounded-2xl border-0 bg-card shadow-2xl">
            <form action="{{ route('leavescreditDeductUpdate') }}" method="POST">
                @csrf
                <div class="flex items-start justify-between border-b border-border/60 px-5 py-4">
                    <div>
                        <h5 class="text-sm font-semibold text-foreground" id="leaveModalDeductEditLabel">Edit Deducted Credit</h5>
                        <p class="mt-0.5 text-xs text-muted-foreground">Update a deduction entry.</p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted" data-dismiss="modal" aria-label="Close">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}" for="sl1-ded">Sick Leave</label>
                        <input type="hidden" name="empid" value="{{ $employee->id }}">
                        <input type="hidden" id="lcid-ded" name="lcid">
                        <input type="hidden" id="date-ded" name="date" value="{{ \Carbon\Carbon::now()->format('Y-m') }}" required>
                        <input class="{{ $fieldClass }}" type="number" id="sl1-ded" name="sl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}" for="vl1-ded">Vacation Leave</label>
                        <input class="{{ $fieldClass }}" type="number" id="vl1-ded" name="vl" step="0.001" min="0" max="30" placeholder="0.000" autocomplete="off" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}" for="remarks1-ded">Remarks</label>
                        <textarea class="{{ $fieldClass }}" id="remarks1-ded" name="remarks" rows="3" placeholder="Optional remarks"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                    <button type="button" class="{{ $secondaryButton }}" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="btn-submit" class="{{ $primaryButton }}">
                        <i data-lucide="save" class="h-3.5 w-3.5"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
