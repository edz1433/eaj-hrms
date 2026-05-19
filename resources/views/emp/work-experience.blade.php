@extends('layouts.master')
@section('body')
@php
    $inputCls  = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $selectCls = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls  = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $fileCls   = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-0.5 file:text-[10px] file:font-semibold file:text-primary hover:file:bg-primary/20 transition-colors';
    $listaccom = isset($workexperienceedit->list_accom) ? explode(';', $workexperienceedit->list_accom) : [];
    $formOpen  = !(count($workexperience) > 0 && !isset($workexperienceedit));
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
                <i class="fas fa-briefcase text-sky-600 dark:text-sky-400 text-sm"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Work Experience</h1>
                <p class="text-[11px] text-muted-foreground">Previous and current employment records</p>
            </div>
        </div>

        {{-- Form card --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-plus-circle text-sky-500 text-xs opacity-80"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">
                        {{ isset($workexperienceedit) ? 'Edit Entry' : 'Add Work Experience' }}
                    </span>
                </div>
                <button type="button" onclick="toggleForm()"
                    class="inline-flex items-center gap-1 rounded-lg border border-border/60 bg-background px-2.5 py-1 text-[10px] font-medium text-muted-foreground hover:bg-muted transition-colors">
                    <i id="form-toggle-icon" class="fas fa-chevron-{{ $formOpen ? 'up' : 'down' }} text-[9px] transition-transform"></i>
                    <span id="form-toggle-label">{{ $formOpen ? 'Hide Form' : 'Show Form' }}</span>
                </button>
            </div>
            <div id="form-section" class="{{ $formOpen ? '' : 'hidden' }}">
                <form class="p-5" action="{{ isset($workexperienceedit) ? route('workexperienceUpdate', $workexperienceedit->id) : route('workexperienceCreate') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($workexperienceedit))
                        <input type="hidden" name="id" value="{{ $workexperienceedit->id }}">
                    @endif
                    <input type="hidden" name="empid" value="{{ $employee->emp_ID }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="{{ $labelCls }}">Inclusive Dates (From — To)</label>
                            <div class="flex gap-2">
                                <input type="date" id="inc_date1" name="inc_date1" class="{{ $inputCls }}"
                                    value="{{ isset($workexperienceedit) ? $workexperienceedit->inc_date1 : '' }}" required>
                                <input type="date" id="inc_date2" name="inc_date2" class="{{ $inputCls }}"
                                    value="{{ isset($workexperienceedit) ? $workexperienceedit->inc_date2 : '' }}"
                                    placeholder="Leave blank if present">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Immediate Supervisor</label>
                            <input type="text" name="supervisor" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($workexperienceedit) ? $workexperienceedit->supervisor : '' }}" autocomplete="off">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelCls }}">Position Title (write in full, do not abbreviate)</label>
                            <input type="text" name="position" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($workexperienceedit) ? $workexperienceedit->position : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Monthly Salary</label>
                            <input type="text" name="salary" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($workexperienceedit) ? $workexperienceedit->salary : '' }}" autocomplete="off"
                                oninput="autoFormatNumber(this)" required>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="{{ $labelCls }}">Department / Agency / Office / Company (write in full)</label>
                            <input type="text" name="department" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($workexperienceedit) ? $workexperienceedit->department : '' }}" autocomplete="off" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelCls }}">Salary / Job / Pay Grade (Format "00-0") / Step / Increment</label>
                            <input type="text" name="sg_grade" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($workexperienceedit) ? $workexperienceedit->sg_grade : '' }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Status of Appointment</label>
                            <input type="text" name="stat_app" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($workexperienceedit) ? $workexperienceedit->stat_app : '' }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Government Service (Y/N)</label>
                            <select name="service" class="{{ $selectCls }}" required>
                                <option value="" {{ old('service', isset($workexperienceedit) && $workexperienceedit->service === '' ? 'selected' : '') }}>N/A</option>
                                <option value="N" {{ old('service', isset($workexperienceedit) && $workexperienceedit->service === 'N' ? 'selected' : '') }}>No</option>
                                <option value="Y" {{ old('service', isset($workexperienceedit) && $workexperienceedit->service === 'Y' ? 'selected' : '') }}>Yes</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Attachment (PDF)</label>
                            <input type="file" name="attachment" class="{{ $fileCls }}" accept="application/pdf">
                        </div>
                    </div>

                    {{-- Accomplishments --}}
                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelCls }} mb-2">List of Accomplishments & Contributions</label>
                            <div class="space-y-1.5">
                                @for($i = 0; $i < 8; $i++)
                                <input type="text" name="list_accom[{{ $i }}]" class="{{ $inputCls }}"
                                    placeholder="Accomplishment {{ $i + 1 }}"
                                    value="{{ isset($listaccom[$i]) ? trim($listaccom[$i]) : '' }}" autocomplete="off">
                                @endfor
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }} mb-2">Actual Summary</label>
                            <textarea name="actual_summary" id="actual_summary" rows="13"
                                class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-xs text-foreground focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors resize-none"
                                style="white-space: pre-wrap;">{{ isset($workexperienceedit) ? str_replace(['<br>', '<br/>', '<br />'], "\n", $workexperienceedit->actual_summary) : '' }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" name="btn-submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-primary/90 transition-colors">
                            <i class="fas fa-save"></i>
                            {{ isset($workexperienceedit) ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Records --}}
        @if(count($workexperience) > 0)
        <div class="space-y-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground px-1">
                {{ count($workexperience) }} {{ Str::plural('Record', count($workexperience)) }}
            </p>
            @foreach($workexperience as $work)
            <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm workexperience-row row-{{ $work->id }}">
                <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-border/40">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-foreground">{{ $work->position }}</p>
                        <p class="text-[11px] text-muted-foreground">{{ $work->department }}</p>
                        <div class="mt-1 text-[10px] text-muted-foreground">
                            <span class="font-medium text-foreground">
                                @if($work->inc_date2 != null)
                                    {{ \Carbon\Carbon::parse($work->inc_date1)->format('m/d/Y') }} —
                                    {{ \Carbon\Carbon::parse($work->inc_date2)->format('m/d/Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($work->inc_date1)->format('m/d/Y') }} — Present
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        @if($work->status == 0)
                            <span id="status-{{ $work->id }}"
                                class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-amber-200 dark:ring-amber-800/40">
                                To be Reviewed
                            </span>
                        @elseif($work->status == 1)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800/40">
                                Reviewed
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800/40">
                                Canceled
                            </span>
                        @endif
                    </div>
                </div>
                <div class="px-5 py-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-muted-foreground">
                    @if($work->sg_grade)<span>SG/Step: <span class="font-semibold text-foreground">{{ $work->sg_grade }}</span></span>@endif
                    @if($work->salary)<span>Salary: <span class="font-semibold text-foreground">{{ $work->salary }}</span></span>@endif
                    @if($work->stat_app)<span>Status: <span class="font-semibold text-foreground">{{ $work->stat_app }}</span></span>@endif
                    <span>Gov't Service: <span class="font-semibold text-foreground">{{ $work->service == 'Y' ? 'Yes' : 'No' }}</span></span>
                    @if(!empty($work->attachment))
                        <a href="#" data-label="{{ $work->position }}"
                           data-pdf="{{ asset('storage/' . $work->attachment) }}"
                           onclick="showPdfModal(this); return false;"
                           class="inline-flex items-center gap-1 text-primary hover:underline font-semibold">
                            <i class="fas fa-eye text-[9px]"></i> Preview PDF
                        </a>
                    @endif
                    @if($work->status == 2 && $work->remarks)
                        <span class="text-red-500">Remarks: {{ $work->remarks }}</span>
                    @endif

                    <div class="ml-auto flex items-center gap-1.5">
                        @if($guard == 'web')
                            <a href="{{ route('workexperienceEdit', ['id' => $empid, 'eid' => $work->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            <button class="workexperience_approve flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200/60 dark:bg-emerald-950/30 transition-colors"
                                value="{{ $work->id }}" title="Approve">
                                <i class="fas fa-check text-[10px]"></i>
                            </button>
                            @if($work->status == 0)
                                <button onclick="openCancelModal({{ $work->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Cancel">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            @endif
                            <button class="workexperience_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 transition-colors"
                                value="{{ $work->id }}" title="Delete">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        @elseif($guard == 'employee')
                            @if($work->status == 0)
                            <a href="{{ route('workexperienceEdit', ['id' => $empid, 'eid' => $work->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            <button class="workexperience_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 transition-colors"
                                value="{{ $work->id }}" title="Delete">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                            @else
                            <a href="{{ route('workexperienceEdit', ['id' => $empid, 'eid' => $work->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 py-10 text-center">
            <i class="fas fa-briefcase text-3xl text-muted-foreground/30 mb-2"></i>
            <p class="text-xs text-muted-foreground">No work experience records yet.</p>
        </div>
        @endif

    </div>
</div>

{{-- PDF Modal --}}
<div id="pdf-modal-backdrop" class="fixed inset-0 z-[60] hidden items-center justify-center p-4" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePdfModal()"></div>
    <div class="relative w-full max-w-3xl rounded-2xl border border-border/60 bg-card shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
            <h3 class="text-sm font-semibold text-foreground" id="pdfModalLabel"></h3>
            <button onclick="closePdfModal()" class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="p-4">
            <iframe id="modalPdf" src="" width="100%" height="560px" class="rounded-lg border border-border/60"></iframe>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancel-modal-backdrop" class="fixed inset-0 z-[60] hidden items-center justify-center p-4" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
    <div class="relative w-full max-w-sm rounded-2xl border border-border/60 bg-card shadow-2xl overflow-hidden">
        <form method="POST" action="{{ route('workexperienceCancel') }}">
            @csrf
            <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
                <h3 class="text-sm font-semibold text-foreground">Cancel Entry</h3>
                <button type="button" onclick="closeCancelModal()" class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <input type="hidden" name="id" id="workexp-id">
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-1">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="3" required
                        class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-xs text-foreground focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors resize-none"
                        placeholder="Enter reason for cancellation"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-5 pb-5">
                <button type="button" onclick="closeCancelModal()"
                    class="rounded-xl border border-border/60 bg-background px-4 py-2 text-xs font-medium text-foreground hover:bg-muted transition-colors">
                    Close
                </button>
                <button type="submit"
                    class="rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700 transition-colors">
                    Cancel Entry
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleForm() {
    const s = document.getElementById('form-section');
    const i = document.getElementById('form-toggle-icon');
    const l = document.getElementById('form-toggle-label');
    s.classList.toggle('hidden');
    i.classList.toggle('rotate-180');
    l.textContent = s.classList.contains('hidden') ? 'Show Form' : 'Hide Form';
}
function showPdfModal(link) {
    document.getElementById('pdfModalLabel').innerText = link.getAttribute('data-label') || '';
    document.getElementById('modalPdf').src = link.getAttribute('data-pdf');
    document.getElementById('pdf-modal-backdrop').classList.replace('hidden', 'flex');
}
function closePdfModal() {
    document.getElementById('modalPdf').src = '';
    document.getElementById('pdf-modal-backdrop').classList.replace('flex', 'hidden');
}
function openCancelModal(id) {
    document.getElementById('workexp-id').value = id;
    document.getElementById('remarks').value = '';
    document.getElementById('cancel-modal-backdrop').classList.replace('hidden', 'flex');
}
function closeCancelModal() {
    document.getElementById('cancel-modal-backdrop').classList.replace('flex', 'hidden');
}
function autoFormatNumber(input) {
    let v = input.value.replace(/,/g, '').replace(/\D/g, '');
    input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
@endpush
@endsection
