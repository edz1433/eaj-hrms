@php
    $leaveOptionsLeft = [
        [1, 'Vacation Leave', '(Sec. 51, Rule XVI, Omnibus Rules Implementing E.O No. 292)', false, 'vacation-leave'],
        [2, 'Mandatory/Forced Leave', '(Sec. 51, Rule XVI, Omnibus Rules Implementing E.O No. 292)', false, null],
        [3, 'Sick Leave', '(Sec. 51, Rule XVI, Omnibus Rules Implementing E.O No. 292)', false, 'sick-leave'],
        [4, 'Maternity Leave', '(R.A No. 11210/IRR issued by CSC, DOLE and SSS)', true, null],
        [5, 'Paternity Leave', '(R.A No. 8187/CSC MC No. 71,s. 1998, as amended)', true, null],
        [6, 'Special Privilege Leave', '(Sec. 21, Rule XVI, Omnibus Rules Implementing E.O No. 292)', false, null],
        [7, 'Solo Parent Leave', '(R.A. No. 8972/CSC MC No. 8, s. 2004)', true, null],
        [15, 'Wellness Leave', '', false, null],
    ];
    $leaveOptionsRight = [
        [8, 'Study Leave', '(Sec. 68, Rule XVI, Omnibus Rules Implementing E.O No. 292)', true, 'study-leave'],
        [9, '10-Day VAWC Leave', '(R.A No. 9262/CSC MO No. 15,s. 2005)', true, null],
        [10, 'Rehabilitation Privilege', '(Sec. 55, Rule XVI, omnibus Rules Implementing E.O No. 292)', true, null],
        [11, 'Special Leave Benefits for Women', '(R.A No. 9710/CSC MC No. 25,s. 2010)', true, null],
        [12, 'Special Emergency (Calamity) Leave', '(CSC MC No. 2,s. 2012, as amended)', true, null],
        [13, 'Adoption Leave', '(R.A. No. 8552)', true, null],
        [14, 'Vacation Service Credit', '(R.A. No. 4670)', false, null],
    ];
@endphp

<form class="form-horizontal add-form" action="{{ route('LeaveAppCreate') }}" method="POST">
    @csrf
    <input type="hidden" name="empid" value="{{ $employee->emp_ID }}">

    <div class="grid gap-5 xl:grid-cols-2">
        <div>
            <span class="inline-flex rounded bg-slate-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Type of Leave to Availed Of</span>
            <div class="mt-2 space-y-1">
                @foreach($leaveOptionsLeft as [$value, $label, $note, $disabled, $id])
                    <label class="flex items-start gap-2 text-xs leading-snug {{ $disabled ? 'text-muted-foreground/70' : 'text-foreground' }}">
                        <input class="leave-type mt-0.5 h-3.5 w-3.5 rounded-full border-border text-primary focus:ring-primary/30" type="radio" value="{{ $value }}" name="leave_type" {{ $id ? 'id='.$id : '' }} {{ $disabled ? 'disabled' : '' }} required>
                        <span>
                            <b>{{ $label }}</b>
                            @if($note)<span class="ft text-muted-foreground">{{ $note }}</span>@endif
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="xl:pt-6">
            <div class="space-y-1">
                @foreach($leaveOptionsRight as [$value, $label, $note, $disabled, $id])
                    <label class="flex items-start gap-2 text-xs leading-snug {{ $disabled ? 'text-muted-foreground/70' : 'text-foreground' }}">
                        <input class="leave-type mt-0.5 h-3.5 w-3.5 rounded-full border-border text-primary focus:ring-primary/30" type="radio" value="{{ $value }}" name="leave_type" {{ $id ? 'id='.$id : '' }} {{ $disabled ? 'disabled' : '' }} required>
                        <span>
                            <b>{{ $label }}</b>
                            @if($note)<span class="ft text-muted-foreground">{{ $note }}</span>@endif
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <span class="inline-flex rounded bg-slate-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Details of Leave</span>
            <div class="mt-2 space-y-2 text-xs text-foreground">
                <div>
                    <p class="italic">In case of Vacation/Special Privilege Leave</p>
                    <label class="mt-1 flex items-center gap-2">
                        <input class="vacation-check h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="1" name="leave_purpose" required disabled>
                        <b>Within the Philippines</b>
                    </label>
                    <label class="mt-1 flex flex-wrap items-center gap-2">
                        <input class="vacation-check h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="2" name="leave_purpose" id="abroad" required disabled>
                        <b>Abroad (Specify)</b>
                        <input class="input-details vacation-leave flex-1" type="text" id="leaves_1" name="leave_detail[]" autocomplete="off" readonly>
                    </label>
                </div>

                <div>
                    <p class="italic">In case of Sick Leave</p>
                    <label class="mt-1 flex items-center gap-2">
                        <input class="sick-leave-detail h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="3" name="leave_purpose" id="in-hospital" required disabled>
                        <b>In Hospital (Specify Illness)</b>
                    </label>
                    <label class="mt-1 flex flex-wrap items-center gap-2">
                        <input class="sick-leave-detail h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="4" name="leave_purpose" id="out-patient" required disabled>
                        <b>Out Patient (Specify Illness)</b>
                        <input class="input-details sick-leave flex-1" type="text" id="leaves_2" name="leave_detail[]" readonly>
                    </label>
                </div>
            </div>
        </div>

        <div class="xl:pt-7">
            <div class="space-y-2 text-xs text-foreground">
                <div>
                    <p class="italic">In case of Study Leave</p>
                    <label class="mt-1 flex items-center gap-2">
                        <input class="leave-check h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="5" name="leave_purpose" required disabled>
                        <b>Completion of Master's Degree</b>
                    </label>
                    <label class="mt-1 flex flex-wrap items-center gap-2">
                        <input class="leave-check h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="6" name="leave_purpose" required disabled>
                        <b>BAR/Board Examination Review</b>
                        <input class="input-details study-leave flex-1" type="text" id="leaves_3" name="leave_detail[]" autocomplete="off" readonly>
                    </label>
                </div>

                <div>
                    <p class="italic">Other Purpose</p>
                    <input type="radio" value="" name="leave_purpose" class="hidden" checked id="monetizationdefault">
                    <label class="purpose-detail mt-1 flex items-center gap-2">
                        <input class="h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="7" name="leave_purpose" id="monetization" disabled>
                        <b>Monetization of Leave Credits</b>
                    </label>
                    <label class="purpose-detail mt-1 flex flex-wrap items-center gap-2">
                        <input class="h-3.5 w-3.5 rounded-full border-border text-primary" type="radio" value="8" name="leave_purpose" id="terminal-leave" disabled>
                        <b>Terminal Leave</b>
                        <input class="input-details other-leave flex-1" type="text" id="leaves_4" name="leave_detail[]" autocomplete="off" readonly>
                    </label>
                </div>
            </div>
        </div>

        <div>
            <label class="inline-flex rounded bg-slate-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white" for="date_range">Inclusive Dates</label>
            <input type="text" id="date_range" name="date_range" class="mt-1 h-9 w-full rounded border border-border/70 bg-background px-3 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Select Date Range" required autocomplete="off">
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="inline-flex rounded bg-slate-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white" for="day">Days Applied</label>
                <input type="text" id="day" name="days" class="mt-1 h-9 w-full rounded border border-border/70 bg-background px-3 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20" autocomplete="off" required readonly>
            </div>
            <div>
                <label class="inline-flex rounded bg-slate-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white" for="date_filing">Date of Filing</label>
                <input type="date" id="date_filing" name="date_filing" class="mt-1 h-9 w-full rounded border border-border/70 bg-background px-3 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20" value="{{ \Carbon\Carbon::now()->toDateString() }}" readonly>
            </div>
        </div>
    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit" class="inline-flex h-8 items-center justify-center rounded bg-emerald-600 px-4 text-xs font-semibold text-white transition hover:bg-emerald-700">Submit</button>
    </div>
</form>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script>
    (function () {
        let flatpickrFallbackRequested = false;

        function requestFlatpickrFallback() {
            if (flatpickrFallbackRequested || window.flatpickr) {
                return;
            }

            flatpickrFallbackRequested = true;
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
            script.defer = true;
            document.head.appendChild(script);
        }

        function setGroupEnabled(selector, enabled) {
            document.querySelectorAll(selector).forEach(function (field) {
                field.disabled = !enabled;

                if (!enabled) {
                    field.checked = false;
                }
            });
        }

        function setDetailEnabled(selector, enabled) {
            document.querySelectorAll(selector).forEach(function (field) {
                field.readOnly = !enabled;

                if (!enabled) {
                    field.value = '';
                }
            });
        }

        function resetLeaveDetails() {
            setGroupEnabled('.vacation-check', false);
            setGroupEnabled('.sick-leave-detail', false);
            setGroupEnabled('.leave-check', false);
            setGroupEnabled('.purpose-detail input[type="radio"]', false);
            setDetailEnabled('.vacation-leave', false);
            setDetailEnabled('.sick-leave', false);
            setDetailEnabled('.study-leave', false);
            setDetailEnabled('.other-leave', false);

            const defaultPurpose = document.getElementById('monetizationdefault');
            if (defaultPurpose) {
                defaultPurpose.checked = true;
            }
        }

        function updateLeaveDetailFields(leaveType) {
            resetLeaveDetails();

            if (leaveType === '1' || leaveType === '6') {
                setGroupEnabled('.vacation-check', true);
                setDetailEnabled('.vacation-leave', true);
                return;
            }

            if (leaveType === '3') {
                setGroupEnabled('.sick-leave-detail', true);
                setDetailEnabled('.sick-leave', true);
                return;
            }

            if (leaveType === '8') {
                setGroupEnabled('.leave-check', true);
                setDetailEnabled('.study-leave', true);
                return;
            }

            if (leaveType) {
                setGroupEnabled('.purpose-detail input[type="radio"]', true);
                setDetailEnabled('.other-leave', true);
            }
        }

        function countWeekdays(startDate, endDate) {
            let count = 0;
            const currentDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
            const lastDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

            while (currentDate <= lastDate) {
                const day = currentDate.getDay();
                if (day !== 0 && day !== 6) {
                    count++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            return count;
        }

        function updateLeaveDays(selectedDates) {
            const daysField = document.getElementById('day');
            if (!daysField) {
                return;
            }

            if (selectedDates.length === 1) {
                daysField.value = countWeekdays(selectedDates[0], selectedDates[0]);
                return;
            }

            if (selectedDates.length === 2) {
                daysField.value = countWeekdays(selectedDates[0], selectedDates[1]);
                return;
            }

            daysField.value = '';
        }

        function initializeInclusiveDatesPicker(attemptsLeft) {
            const dateRangeField = document.getElementById('date_range');
            if (!dateRangeField) {
                return;
            }

            if (!window.flatpickr) {
                if (attemptsLeft === 120) {
                    requestFlatpickrFallback();
                }

                if (attemptsLeft > 0) {
                    window.setTimeout(function () {
                        initializeInclusiveDatesPicker(attemptsLeft - 1);
                    }, 150);
                }
                return;
            }

            if (dateRangeField._flatpickr) {
                return;
            }

            const picker = window.flatpickr(dateRangeField, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: updateLeaveDays,
                onValueUpdate: updateLeaveDays,
                onClose: updateLeaveDays,
            });

            document.querySelectorAll('input[name="leave_type"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    picker.set('minDate', null);
                    picker.clear();
                    updateLeaveDetailFields(input.value);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            resetLeaveDetails();
            document.querySelectorAll('input[name="leave_type"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    updateLeaveDetailFields(input.value);
                });
            });
            document.querySelectorAll('input[name="leave_detail[]"]').forEach(function (input) {
                input.addEventListener('input', function () {
                    document.querySelectorAll('input[name="leave_detail[]"]').forEach(function (otherInput) {
                        if (otherInput !== input) {
                            otherInput.value = '';
                        }
                    });
                });
            });

            initializeInclusiveDatesPicker(150);
        });
    })();
</script>
@endpush
