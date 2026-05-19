@extends('layouts.master')
@section('body')
@php
    $inputCls  = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls  = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $skillshob   = explode(',', $otherinfo->skills_hob);
    $recognition = explode(',', $otherinfo->recognition);
    $memorg      = explode(',', $otherinfo->mem_org);
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30">
                <i class="fas fa-info-circle text-violet-600 dark:text-violet-400 text-sm"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Other Information</h1>
                <p class="text-[11px] text-muted-foreground">Skills, recognitions, and memberships</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-star text-violet-500 text-xs opacity-70"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">Skills, Distinctions & Memberships</span>
                </div>
                <button id="add-row-familybg" type="button"
                    class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-[10px] font-semibold text-primary hover:bg-primary/20 transition-colors">
                    <i class="fas fa-plus text-[9px]"></i> Add Row
                </button>
            </div>
            <div class="p-5">
                <div class="mb-2 hidden md:grid md:grid-cols-[1fr_1.5fr_1.5fr_32px] gap-3">
                    <span class="{{ $labelCls }}">Special Skills / Hobbies</span>
                    <span class="{{ $labelCls }}">Non-Academic Distinctions / Recognition</span>
                    <span class="{{ $labelCls }}">Membership in Association / Organization</span>
                    <span></span>
                </div>
                <div id="form-container" class="space-y-2">
                    @foreach($skillshob as $index => $name)
                        @if(isset($memorg[$index]))
                            <div class="grid grid-cols-1 md:grid-cols-[1fr_1.5fr_1.5fr_32px] gap-2 items-center" data-index="{{ $index }}">
                                <input type="text" value="{{ trim($name) }}" name="skills_hob[]"
                                    class="{{ $inputCls }} update-child update-field-array"
                                    data-index="{{ $index }}" placeholder="N/A">
                                <input type="text" value="{{ trim($recognition[$index] ?? '') }}" name="recognition[]"
                                    class="{{ $inputCls }} update-child update-field-array"
                                    data-index="{{ $index }}" placeholder="N/A">
                                <input type="text" value="{{ trim($memorg[$index]) }}" name="mem_org[]"
                                    class="{{ $inputCls }} update-child update-field-array"
                                    data-index="{{ $index }}" placeholder="N/A">
                                @if($index > 0)
                                    <button type="button"
                                        class="btn-delete flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                                        <i class="fas fa-trash text-[10px]"></i>
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

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const empId = @json($empid);
    const csrf = @json(csrf_token());
    const updateUrl = @json(route('update-child-oi'));
    const inputClass = @json($inputCls);

    function markField(field, state) {
        field.classList.remove('border-emerald-400', 'border-red-400');
        field.classList.add(state === 'saved' ? 'border-emerald-400' : 'border-red-400');
        setTimeout(() => field.classList.remove('border-emerald-400', 'border-red-400'), 1200);
    }

    function rows() {
        return Array.from(document.querySelectorAll('#form-container > div'));
    }

    function clean(value) {
        return (value ?? '').replace(/,/g, '').trim();
    }

    function postJson(payload) {
        return fetch(updateUrl, {
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

    function saveOtherInfo(sourceField = null) {
        const currentRows = rows();
        const skills = currentRows.map((row) => clean(row.querySelector('[name="skills_hob[]"]')?.value));
        const recognitions = currentRows.map((row) => clean(row.querySelector('[name="recognition[]"]')?.value));
        const memberships = currentRows.map((row) => clean(row.querySelector('[name="mem_org[]"]')?.value));

        postJson({
            empid: empId,
            skills_hob: skills,
            recognition: recognitions,
            mem_org: memberships,
        })
            .then(() => {
                currentRows.forEach((row) => {
                    row.querySelectorAll('.update-child').forEach((field) => {
                        field.value = clean(field.value);
                        field.dataset.lastSavedValue = field.value;
                    });
                });
                if (sourceField) markField(sourceField, 'saved');
            })
            .catch((error) => {
                console.error(error.message);
                if (sourceField) markField(sourceField, 'error');
            });
    }

    document.getElementById('form-container')?.addEventListener('focusout', (event) => {
        if (event.target.matches('.update-child')) saveOtherInfo(event.target);
    });

    document.getElementById('form-container')?.addEventListener('click', (event) => {
        const button = event.target.closest('.btn-delete');
        if (!button) return;
        button.closest('[data-index]')?.remove();
        saveOtherInfo();
    });

    document.getElementById('add-row-familybg')?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-[1fr_1.5fr_1.5fr_32px] gap-2 items-center';
        row.dataset.index = String(rows().length);
        row.innerHTML = `
            <input type="text" name="skills_hob[]" class="${inputClass} update-child update-field-array" placeholder="N/A">
            <input type="text" name="recognition[]" class="${inputClass} update-child update-field-array" placeholder="N/A">
            <input type="text" name="mem_org[]" class="${inputClass} update-child update-field-array" placeholder="N/A">
            <button type="button" class="btn-delete flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30 transition-colors">
                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
            </button>
        `;
        document.getElementById('form-container')?.appendChild(row);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        row.querySelector('input')?.focus();
        saveOtherInfo();
    });
});
</script>
@endpush
