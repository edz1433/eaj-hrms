@extends('layouts.master')
@section('body')
@php
    $inputCls = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';

    $schools     = explode(',', $educBg->coll_school);
    $courses     = explode(',', $educBg->coll_course);
    $periods     = explode(',', $educBg->coll_period);
    $levels      = explode(',', $educBg->coll_level);
    $years       = explode(',', $educBg->coll_grad);
    $honors      = explode(',', $educBg->coll_honor);

    $gradSchools = explode(',', $educBg->grad_school);
    $gradCourses = explode(',', $educBg->grad_course);
    $gradPeriods = explode(',', $educBg->grad_period);
    $gradLevels  = explode(',', $educBg->grad_level);
    $gradYears   = explode(',', $educBg->grad_grad);
    $gradHonors  = explode(',', $educBg->grad_honor);
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                <i data-lucide="graduation-cap" class="h-4 w-4 text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Educational Background</h1>
                <p class="text-[11px] text-muted-foreground">Schools attended and academic achievements</p>
            </div>
        </div>

        @php
        $eduLevels = [
            ['Elementary', 'school', 'blue',
                [['elem_school','School Name (write in full)','text'],['elem_period','Period of Attendance','text'],['elem_level','Highest Level / Units Earned','text'],['elem_grad','Year Graduated','number'],['elem_honor','Scholarship / Academic Honors','text']]
            ],
            ['Secondary', 'presentation', 'violet',
                [['sec_school','School Name (write in full)','text'],['sec_period','Period of Attendance','text'],['sec_level','Highest Level / Units Earned','text'],['sec_grad','Year Graduated','number'],['sec_honor','Scholarship / Academic Honors','text']]
            ],
            ['Vocational / Trade Course', 'wrench', 'amber',
                [['voc_school','School Name (write in full)','text'],['voc_course','Basic Education / Degree / Course','text'],['voc_period','Period of Attendance','text'],['voc_level','Highest Level / Units Earned','text'],['voc_grad','Year Graduated','number'],['voc_honor','Scholarship / Academic Honors','text']]
            ],
        ];
        $colorMap = ['blue'=>'text-blue-500','violet'=>'text-violet-500','amber'=>'text-amber-500'];
        @endphp

        @foreach($eduLevels as [$title, $icon, $color, $fields])
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="{{ $icon }}" class="h-3.5 w-3.5 {{ $colorMap[$color] }} opacity-80"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">{{ $title }}</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($fields as [$fname, $flabel, $ftype])
                <div>
                    <label class="{{ $labelCls }}">{{ $flabel }}</label>
                    <input type="{{ $ftype }}" value="{{ $educBg->$fname }}" name="{{ $fname }}"
                        data-column-id="{{ $empid }}"
                        class="{{ $inputCls }} update-field"
                        @if($fname === 'elem_period' || $fname === 'sec_period' || $fname === 'voc_period')
                            placeholder="ex: 2020-2024"
                            oninput="validateDateRange(this)" onkeyup="restrictInput(this)"
                        @else
                            placeholder="N/A"
                        @endif>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- College --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="university" class="h-3.5 w-3.5 text-indigo-500 opacity-80"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">College</span>
                </div>
                <button id="add-row-college" type="button"
                    class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-[10px] font-semibold text-primary hover:bg-primary/20 transition-colors">
                    <i data-lucide="plus" class="h-3 w-3"></i> Add Entry
                </button>
            </div>
            <div id="college-container" class="divide-y divide-border/40">
                @foreach($schools as $index => $school)
                <div class="college-div p-5 relative" data-index="{{ $index }}">
                    @if($index > 0)
                        <button type="button"
                            class="btn-delete absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                            <i data-lucide="x" class="h-3 w-3"></i>
                        </button>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="lg:col-span-2">
                            <label class="{{ $labelCls }}">School Name (write in full)</label>
                            <input type="text" value="{{ trim($school) }}" name="coll_school[]"
                                class="{{ $inputCls }} update-child" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $index == 0 ? 'Basic Education / Degree / Course' : 'Degree / Course' }}</label>
                            <input type="text" value="{{ trim($courses[$index] ?? '') }}" name="coll_course[]"
                                class="{{ $inputCls }} update-child" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Period of Attendance</label>
                            <input type="text" value="{{ trim($periods[$index] ?? '') }}" name="coll_period[]"
                                class="{{ $inputCls }} update-child" placeholder="ex: 2020-2024" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Highest Level / Units Earned</label>
                            <input type="text" value="{{ trim($levels[$index] ?? '') }}" name="coll_level[]"
                                class="{{ $inputCls }} update-child" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Year Graduated</label>
                            <input type="number" value="{{ trim($years[$index] ?? '') }}" name="coll_grad[]"
                                class="{{ $inputCls }} update-child" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="{{ $labelCls }}">Scholarship / Academic Honors Received</label>
                            <input type="text" value="{{ trim($honors[$index] ?? '') }}" name="coll_honor[]"
                                class="{{ $inputCls }} update-child" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Graduate Studies --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="book-open" class="h-3.5 w-3.5 text-rose-500 opacity-80"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">Graduate Studies</span>
                </div>
                <button id="add-row-graduate" type="button"
                    class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-[10px] font-semibold text-primary hover:bg-primary/20 transition-colors">
                    <i data-lucide="plus" class="h-3 w-3"></i> Add Entry
                </button>
            </div>
            <div id="graduate-container" class="divide-y divide-border/40">
                @foreach($gradSchools as $index => $school)
                <div class="graduate-div p-5 relative" data-index="{{ $index }}">
                    @if($index > 0)
                        <button type="button"
                            class="btn-delete-grad absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                            <i data-lucide="x" class="h-3 w-3"></i>
                        </button>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="lg:col-span-2">
                            <label class="{{ $labelCls }}">School Name (write in full)</label>
                            <input type="text" value="{{ trim($school) }}" name="grad_school[]"
                                class="{{ $inputCls }} update-grad" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Basic Education / Degree / Course</label>
                            <input type="text" value="{{ trim($gradCourses[$index] ?? '') }}" name="grad_course[]"
                                class="{{ $inputCls }} update-grad" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Period of Attendance</label>
                            <input type="text" value="{{ trim($gradPeriods[$index] ?? '') }}" name="grad_period[]"
                                class="{{ $inputCls }} update-grad" placeholder="ex: 2020-2024" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Highest Level / Units Earned</label>
                            <input type="text" value="{{ trim($gradLevels[$index] ?? '') }}" name="grad_level[]"
                                class="{{ $inputCls }} update-grad" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Year Graduated</label>
                            <input type="number" value="{{ trim($gradYears[$index] ?? '') }}" name="grad_grad[]"
                                class="{{ $inputCls }} update-grad" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="{{ $labelCls }}">Scholarship / Academic Honors Received</label>
                            <input type="text" value="{{ trim($gradHonors[$index] ?? '') }}" name="grad_honor[]"
                                class="{{ $inputCls }} update-grad" placeholder="N/A" data-index="{{ $index }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const empId = @json($empid);
    const csrf = @json(csrf_token());
    const fieldUrl = @json(route('educBgUpdate'));
    const collegeUrl = @json(route('educBgUpdateArray'));
    const graduateUrl = @json(route('educBgUpdateGraduateArray'));
    const inputClass = @json($inputCls);

    function refreshRowIcons(row) {
        requestAnimationFrame(() => {
            window.refreshUi?.(row);
            window.refreshIcons?.();
        });
    }

    function markField(field, state) {
        field.classList.remove('border-emerald-400', 'border-red-400');
        field.classList.add(state === 'saved' ? 'border-emerald-400' : 'border-red-400');
        setTimeout(() => field.classList.remove('border-emerald-400', 'border-red-400'), 1200);
    }

    function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        }).then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Auto-save failed.');
            }
            return data;
        });
    }

    function saveField(field) {
        const value = field.value ?? '';
        if (field.dataset.lastSavedValue === value) return;

        postJson(fieldUrl, {
            id: field.dataset.columnId || empId,
            column: field.name,
            value,
        })
            .then(() => {
                field.dataset.lastSavedValue = value;
                markField(field, 'saved');
            })
            .catch((error) => {
                console.error(error.message);
                markField(field, 'error');
            });
    }

    function containerRows(containerId, rowSelector) {
        return Array.from(document.querySelectorAll(`#${containerId} > ${rowSelector}`));
    }

    function values(rows, name) {
        return rows.map((row) => row.querySelector(`[name="${name}"]`)?.value ?? '');
    }

    function saveCollege(sourceField = null) {
        const rows = containerRows('college-container', '.college-div');
        postJson(collegeUrl, {
            empid: empId,
            schools: values(rows, 'coll_school[]'),
            degrees: values(rows, 'coll_course[]'),
            periods: values(rows, 'coll_period[]'),
            levels: values(rows, 'coll_level[]'),
            years: values(rows, 'coll_grad[]'),
            honors: values(rows, 'coll_honor[]'),
        })
            .then(() => {
                rows.forEach((row) => row.querySelectorAll('.update-child').forEach((field) => field.dataset.lastSavedValue = field.value ?? ''));
                if (sourceField) markField(sourceField, 'saved');
            })
            .catch((error) => {
                console.error(error.message);
                if (sourceField) markField(sourceField, 'error');
            });
    }

    function saveGraduate(sourceField = null) {
        const rows = containerRows('graduate-container', '.graduate-div');
        postJson(graduateUrl, {
            empid: empId,
            grad_schools: values(rows, 'grad_school[]'),
            grad_courses: values(rows, 'grad_course[]'),
            grad_periods: values(rows, 'grad_period[]'),
            grad_levels: values(rows, 'grad_level[]'),
            grad_years: values(rows, 'grad_grad[]'),
            grad_honors: values(rows, 'grad_honor[]'),
        })
            .then(() => {
                rows.forEach((row) => row.querySelectorAll('.update-grad').forEach((field) => field.dataset.lastSavedValue = field.value ?? ''));
                if (sourceField) markField(sourceField, 'saved');
            })
            .catch((error) => {
                console.error(error.message);
                if (sourceField) markField(sourceField, 'error');
            });
    }

    document.querySelectorAll('.update-field').forEach((field) => {
        field.dataset.lastSavedValue = field.value ?? '';
        field.addEventListener('blur', () => saveField(field));
    });

    document.getElementById('college-container')?.addEventListener('focusout', (event) => {
        if (event.target.matches('.update-child')) saveCollege(event.target);
    });

    document.getElementById('graduate-container')?.addEventListener('focusout', (event) => {
        if (event.target.matches('.update-grad')) saveGraduate(event.target);
    });

    document.getElementById('college-container')?.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-delete');
        if (!button) return;
        button.closest('.college-div')?.remove();
        saveCollege();
    });

    document.getElementById('graduate-container')?.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-delete-grad');
        if (!button) return;
        button.closest('.graduate-div')?.remove();
        saveGraduate();
    });

    document.getElementById('add-row-college')?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'college-div p-5 relative';
        row.dataset.index = String(containerRows('college-container', '.college-div').length);
        row.innerHTML = `
            <button type="button" class="btn-delete absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                <i data-lucide="x" class="h-3.5 w-3.5"></i>
            </button>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-2"><label class="{{ $labelCls }}">School Name (write in full)</label><input type="text" name="coll_school[]" class="${inputClass} update-child" placeholder="N/A"></div>
                <div><label class="{{ $labelCls }}">Degree / Course</label><input type="text" name="coll_course[]" class="${inputClass} update-child" placeholder="N/A"></div>
                <div><label class="{{ $labelCls }}">Period of Attendance</label><input type="text" name="coll_period[]" class="${inputClass} update-child" placeholder="ex: 2020-2024"></div>
                <div><label class="{{ $labelCls }}">Highest Level / Units Earned</label><input type="text" name="coll_level[]" class="${inputClass} update-child" placeholder="N/A"></div>
                <div><label class="{{ $labelCls }}">Year Graduated</label><input type="number" name="coll_grad[]" class="${inputClass} update-child" placeholder="N/A"></div>
                <div class="sm:col-span-2 lg:col-span-3"><label class="{{ $labelCls }}">Scholarship / Academic Honors Received</label><input type="text" name="coll_honor[]" class="${inputClass} update-child" placeholder="N/A"></div>
            </div>
        `;
        document.getElementById('college-container')?.appendChild(row);
        refreshRowIcons(row);
        row.querySelector('input')?.focus();
        saveCollege();
    });

    document.getElementById('add-row-graduate')?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'graduate-div p-5 relative';
        row.dataset.index = String(containerRows('graduate-container', '.graduate-div').length);
        row.innerHTML = `
            <button type="button" class="btn-delete-grad absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                <i data-lucide="x" class="h-3.5 w-3.5"></i>
            </button>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-2"><label class="{{ $labelCls }}">School Name (write in full)</label><input type="text" name="grad_school[]" class="${inputClass} update-grad" placeholder="N/A"></div>
                <div><label class="{{ $labelCls }}">Basic Education / Degree / Course</label><input type="text" name="grad_course[]" class="${inputClass} update-grad" placeholder="N/A"></div>
                <div><label class="{{ $labelCls }}">Period of Attendance</label><input type="text" name="grad_period[]" class="${inputClass} update-grad" placeholder="ex: 2020-2024"></div>
                <div><label class="{{ $labelCls }}">Highest Level / Units Earned</label><input type="text" name="grad_level[]" class="${inputClass} update-grad" placeholder="N/A"></div>
                <div><label class="{{ $labelCls }}">Year Graduated</label><input type="number" name="grad_grad[]" class="${inputClass} update-grad" placeholder="N/A"></div>
                <div class="sm:col-span-2 lg:col-span-3"><label class="{{ $labelCls }}">Scholarship / Academic Honors Received</label><input type="text" name="grad_honor[]" class="${inputClass} update-grad" placeholder="N/A"></div>
            </div>
        `;
        document.getElementById('graduate-container')?.appendChild(row);
        refreshRowIcons(row);
        row.querySelector('input')?.focus();
        saveGraduate();
    });
});

function validateDateRange(input) {
    const value = input.value;
    if (/^\d{4}-\d{4}$/.test(value)) {
        const [s, e] = value.split('-').map(Number);
        if (s < 1900 || e > 2099 || s > e) {
            input.setCustomValidity('Please enter a valid year range (YYYY-YYYY).');
            input.reportValidity();
        } else {
            input.setCustomValidity('');
        }
    } else {
        input.setCustomValidity('Please enter the date range in YYYY-YYYY format.');
        input.reportValidity();
    }
}
function restrictInput(input) {
    input.value = input.value.replace(/[^0-9-]/g, '');
}
</script>
@endpush
@endsection
