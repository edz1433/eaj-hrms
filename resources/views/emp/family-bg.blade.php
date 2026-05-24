@extends('layouts.master')
@section('body')
@php
    $inputCls = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls  = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $names = explode(',', $familyBg->name_child);
    $dates = explode(',', $familyBg->date_birth);
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/30">
                <i data-lucide="users-round" class="h-4 w-4 text-rose-600 dark:text-rose-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Family Background</h1>
                <p class="text-[11px] text-muted-foreground">Spouse, children, and parents information</p>
            </div>
        </div>

        {{-- Spouse Info --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="gem" class="h-3.5 w-3.5 text-rose-500 opacity-70"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">Spouse Information</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Surname</label>
                    <input type="text" value="{{ $familyBg->spouse_sname }}" name="spouse_sname"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">First Name</label>
                    <input type="text" value="{{ $familyBg->spouse_fname }}" name="spouse_fname"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Middle Name</label>
                    <input type="text" value="{{ $familyBg->spouse_mname }}" name="spouse_mname"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Extension</label>
                    <input type="text" value="{{ $familyBg->spouse_ext }}" name="spouse_ext"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
            </div>
            <div class="border-t border-border/40 px-5 pb-5 pt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Occupation</label>
                    <input type="text" value="{{ $familyBg->occupation }}" name="occupation"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Business Name</label>
                    <input type="text" value="{{ $familyBg->bus_name }}" name="bus_name"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Business Address</label>
                    <input type="text" value="{{ $familyBg->bus_address }}" name="bus_address"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Telephone</label>
                    <input type="text" value="{{ $familyBg->telephone }}" name="telephone"
                        data-column-id="{{ $empid }}" class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
            </div>
        </div>

        {{-- Children --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="baby" class="h-3.5 w-3.5 text-sky-500 opacity-70"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">Children</span>
                </div>
                <button id="add-row-familybg" type="button"
                    class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-[10px] font-semibold text-primary hover:bg-primary/20 transition-colors">
                    <i data-lucide="plus" class="h-3 w-3"></i> Add Row
                </button>
            </div>
            <div class="p-5">
                <div class="mb-2 hidden sm:grid sm:grid-cols-[1fr_1fr_32px] gap-3">
                    <span class="{{ $labelCls }}">Child's Name</span>
                    <span class="{{ $labelCls }}">Date of Birth</span>
                    <span></span>
                </div>
                <div id="form-container" class="space-y-2">
                    @foreach($names as $index => $name)
                        @if(isset($dates[$index]))
                            <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_32px] gap-2 items-center" data-index="{{ $index }}">
                                <input type="text" value="{{ trim($name) }}" name="name_child[]"
                                    class="{{ $inputCls }} update-child update-field-array"
                                    data-index="{{ $index }}" placeholder="N/A">
                                <input type="date" value="{{ trim($dates[$index]) }}" name="date_birth[]"
                                    class="{{ $inputCls }} update-child update-field-array"
                                    data-index="{{ $index }}">
                                @if($index > 0)
                                    <button type="button"
                                        class="btn-delete flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                                        <i data-lucide="x" class="h-3 w-3"></i>
                                    </button>
                                @else
                                    <div class="h-8 w-8"></div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Father --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="user-round" class="h-3.5 w-3.5 text-slate-500 opacity-70"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">Father's Name</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Surname</label>
                    <input type="text" value="{{ $familyBg->father_sname }}" name="father_sname"
                        data-column-id="{{ $empid }}" data-column-name="father_sname"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">First Name</label>
                    <input type="text" value="{{ $familyBg->father_fname }}" name="father_fname"
                        data-column-id="{{ $empid }}" data-column-name="father_fname"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Middle Name</label>
                    <input type="text" value="{{ $familyBg->father_mname }}" name="father_mname"
                        data-column-id="{{ $empid }}" data-column-name="father_mname"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Extension</label>
                    <input type="text" value="{{ $familyBg->father_ext }}" name="father_ext"
                        data-column-id="{{ $empid }}" data-column-name="father_ext"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
            </div>
        </div>

        {{-- Mother --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="user-round" class="h-3.5 w-3.5 text-pink-500 opacity-70"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">Mother's Maiden Name</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Surname</label>
                    <input type="text" value="{{ $familyBg->mother_sname }}" name="mother_sname"
                        data-column-id="{{ $empid }}" data-column-name="mother_sname"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">First Name</label>
                    <input type="text" value="{{ $familyBg->mother_fname }}" name="mother_fname"
                        data-column-id="{{ $empid }}" data-column-name="mother_fname"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Middle Name</label>
                    <input type="text" value="{{ $familyBg->mother_mname }}" name="mother_mname"
                        data-column-id="{{ $empid }}" data-column-name="mother_mname"
                        class="{{ $inputCls }} update-field" placeholder="N/A">
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const empId = @json($empid);
    const csrf = @json(csrf_token());
    const fieldUrl = @json(route('familyBgUpdate'));
    const childUrl = @json(route('update-child'));
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
            column: field.dataset.columnName || field.name,
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

    function childRows() {
        return Array.from(document.querySelectorAll('#form-container > div'));
    }

    function saveChildren(sourceField = null) {
        const rows = childRows();
        const names = rows.map((row) => row.querySelector('[name="name_child[]"]')?.value ?? '');
        const dates = rows.map((row) => row.querySelector('[name="date_birth[]"]')?.value ?? '');

        postJson(childUrl, {
            empid: empId,
            name_child: names,
            date_birth: dates,
        })
            .then(() => {
                rows.forEach((row) => {
                    row.querySelectorAll('.update-child').forEach((field) => field.dataset.lastSavedValue = field.value ?? '');
                });
                if (sourceField) markField(sourceField, 'saved');
            })
            .catch((error) => {
                console.error(error.message);
                if (sourceField) markField(sourceField, 'error');
            });
    }

    document.querySelectorAll('.update-field').forEach((field) => {
        field.dataset.lastSavedValue = field.value ?? '';
        field.addEventListener(field.tagName === 'SELECT' ? 'change' : 'blur', () => saveField(field));
    });

    document.getElementById('form-container')?.addEventListener('focusout', (event) => {
        if (event.target.matches('.update-child')) saveChildren(event.target);
    });

    document.getElementById('form-container')?.addEventListener('change', (event) => {
        if (event.target.matches('input[type="date"].update-child')) saveChildren(event.target);
    });

    document.getElementById('form-container')?.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-delete');
        if (!button) return;
        button.closest('[data-index]')?.remove();
        saveChildren();
    });

    document.getElementById('add-row-familybg')?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 sm:grid-cols-[1fr_1fr_32px] gap-2 items-center';
        row.dataset.index = String(childRows().length);
        row.innerHTML = `
            <input type="text" name="name_child[]" class="${inputClass} update-child" placeholder="N/A">
            <input type="date" name="date_birth[]" class="${inputClass} update-child">
            <button type="button" class="btn-delete flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                <i data-lucide="x" class="h-3.5 w-3.5"></i>
            </button>
        `;
        document.getElementById('form-container')?.appendChild(row);
        refreshRowIcons(row);
        row.querySelector('input')?.focus();
        saveChildren();
    });
});
</script>
@endpush
