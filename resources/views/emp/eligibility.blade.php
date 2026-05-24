@extends('layouts.master')
@section('body')
@php
    $inputCls  = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls  = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $fileCls   = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-0.5 file:text-[10px] file:font-semibold file:text-primary hover:file:bg-primary/20 transition-colors';
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-yellow-100 dark:bg-yellow-900/30">
                <i data-lucide="badge-check" class="h-4 w-4 text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Eligibility</h1>
                <p class="text-[11px] text-muted-foreground">Career service, board/bar, and special eligibilities</p>
            </div>
        </div>

        {{-- Form card --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="circle-plus" class="h-3.5 w-3.5 text-yellow-500 opacity-80"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">
                        {{ isset($eligibilityedit) ? 'Edit Entry' : 'Add Eligibility' }}
                    </span>
                </div>
                <button type="button" onclick="toggleForm()"
                    class="inline-flex items-center gap-1 rounded-lg border border-border/60 bg-background px-2.5 py-1 text-[10px] font-medium text-muted-foreground hover:bg-muted transition-colors">
                    <i id="form-toggle-icon" data-lucide="chevron-up" class="h-3 w-3 transition-transform"></i>
                    <span id="form-toggle-label">Hide Form</span>
                </button>
            </div>
            <div id="form-section">
                <form class="p-5" action="{{ isset($eligibilityedit) ? route('eligibilityUpdate', $eligibilityedit->id) : route('eligibilityCreate') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($eligibilityedit))
                        <input type="hidden" name="id" value="{{ $eligibilityedit->id }}">
                    @endif
                    <input type="hidden" name="empid" value="{{ $employee->emp_ID }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="{{ $labelCls }}">Career Service / RA 1080 / Board-Bar / Special Laws / CES / CSEE / Barangay Eligibility</label>
                            <input type="text" name="careereligible" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ old('careereligible', isset($eligibilityedit) ? $eligibilityedit->careereligible : '') }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Rating (if applicable)</label>
                            <input type="number" name="rating" step="0.01" min="0" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ old('rating', isset($eligibilityedit) ? $eligibilityedit->rating : '') }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Date of Examination / Conferment</label>
                            <input type="date" name="date_exam" class="{{ $inputCls }}"
                                value="{{ old('date_exam', isset($eligibilityedit) ? $eligibilityedit->date_exam : '') }}" autocomplete="off">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelCls }}">Place of Examination / Conferment</label>
                            <input type="text" name="place_exam" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ old('place_exam', isset($eligibilityedit) ? $eligibilityedit->place_exam : '') }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">License Number</label>
                            <input type="number" name="number" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ old('number', isset($eligibilityedit) ? $eligibilityedit->number : '') }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Date of Validity</label>
                            <input type="date" name="date_valid" class="{{ $inputCls }}"
                                value="{{ old('date_valid', isset($eligibilityedit) ? $eligibilityedit->date_valid : '') }}" autocomplete="off">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Attachment (PDF)</label>
                            <input type="file" name="attachment" class="{{ $fileCls }}" accept="application/pdf">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" name="btn-submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-primary/90 transition-colors">
                            <i data-lucide="save" class="h-3.5 w-3.5"></i>
                            {{ isset($eligibilityedit) ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Records --}}
        @if(count($eligibility) > 0)
        <div class="space-y-3">
            <p data-pds-record-count class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground px-1">
                {{ count($eligibility) }} {{ Str::plural('Record', count($eligibility)) }}
            </p>
            @foreach($eligibility as $eli)
            @php
                $eliTitle = trim((string) $eli->careereligible) ?: 'Untitled Eligibility';
                $eliRating = trim((string) $eli->rating) ?: 'Not applicable';
                $eliExamDate = $eli->date_exam ? \Carbon\Carbon::parse($eli->date_exam)->format('M d, Y') : 'No exam date';
                $eliPlace = trim((string) $eli->place_exam) ?: 'Place not specified';
                $eliLicense = trim((string) $eli->number) ?: 'Not specified';
                $eliValid = $eli->date_valid ? \Carbon\Carbon::parse($eli->date_valid)->format('M d, Y') : 'No expiry date';
            @endphp
            <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm transition hover:border-primary/25 hover:shadow-md eligibility-row row-{{ $eli->id }}">
                <div class="flex flex-col gap-4 px-5 py-4 border-b border-border/40 md:flex-row md:items-start md:justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">
                                <i data-lucide="badge-check" class="h-4 w-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold leading-snug text-foreground">{{ $eliTitle }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-[11px] text-muted-foreground">
                                    <i data-lucide="map-pin" class="h-3 w-3 shrink-0"></i>
                                    <span class="truncate">{{ $eliPlace }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-border/60 bg-muted/20 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"><i data-lucide="star" class="h-3 w-3"></i> Rating</p>
                                <p class="mt-1 truncate text-xs font-semibold text-foreground">{{ $eliRating }}</p>
                            </div>
                            <div class="rounded-xl border border-border/60 bg-muted/20 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"><i data-lucide="calendar-days" class="h-3 w-3"></i> Exam / Conferment</p>
                                <p class="mt-1 text-xs font-semibold text-foreground">{{ $eliExamDate }}</p>
                            </div>
                            <div class="rounded-xl border border-border/60 bg-muted/20 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"><i data-lucide="hash" class="h-3 w-3"></i> License No.</p>
                                <p class="mt-1 truncate text-xs font-semibold text-foreground">{{ $eliLicense }}</p>
                            </div>
                            <div class="rounded-xl border border-border/60 bg-muted/20 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"><i data-lucide="calendar-check" class="h-3 w-3"></i> Valid Until</p>
                                <p class="mt-1 text-xs font-semibold text-foreground">{{ $eliValid }}</p>
                            </div>
                        </div>
                        <div class="hidden">
                        <p class="text-xs font-bold text-foreground leading-snug">{{ $eli->careereligible }}</p>
                        <div class="mt-1 flex flex-wrap gap-2 text-[10px] text-muted-foreground">
                            @if($eli->rating)<span class="font-medium text-foreground">Rating: {{ $eli->rating }}</span>@endif
                            @if($eli->date_exam)<span>· {{ $eli->date_exam }}</span>@endif
                            @if($eli->place_exam)<span>· {{ $eli->place_exam }}</span>@endif
                        </div>
                        </div>
                    </div>
                    {{-- Status badge --}}
                    <div class="shrink-0 md:pt-1">
                        @if($eli->status == 0)
                            <span id="status-{{ $eli->id }}"
                                class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-amber-200 dark:ring-amber-800/40">
                                To be Reviewed
                            </span>
                        @elseif($eli->status == 1)
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
                <div class="px-5 py-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] text-muted-foreground">
                    @if($eli->attachment)
                        <a href="#" data-label="{{ $eli->careereligible }}"
                           data-pdf="{{ asset('storage/' . $eli->attachment) }}"
                           onclick="showPdfModal(this); return false;"
                           class="inline-flex items-center gap-1 text-primary hover:underline font-semibold">
                            <i data-lucide="eye" class="h-3 w-3"></i> Preview PDF
                        </a>
                    @endif
                    @if($eli->status == 2 && $eli->remarks)
                        <span class="text-red-500">Remarks: {{ $eli->remarks }}</span>
                    @endif

                    {{-- Action buttons --}}
                    <div data-pds-actions class="ml-auto flex items-center gap-1.5">
                        @if($guard == 'web')
                            <a href="{{ route('eligibilityEdit', ['id' => $employeeRouteId ?? shortEncrypt((string) $employee->id), 'eid' => $eli->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 dark:border-amber-800/40 transition-colors" title="Edit">
                                <i data-lucide="pencil" class="h-3 w-3"></i>
                            </a>
                            <button data-pds-hide-on-approve class="eligible_approve flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200/60 dark:bg-emerald-950/30 dark:border-emerald-800/40 transition-colors"
                                value="{{ $eli->id }}" title="Approve">
                                <i data-lucide="check" class="h-3 w-3"></i>
                            </button>
                            @if($eli->status == 0)
                                <button data-pds-hide-on-approve onclick="openCancelModal({{ $eli->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 dark:border-amber-800/40 transition-colors" title="Cancel">
                                    <i data-lucide="x" class="h-3 w-3"></i>
                                </button>
                            @endif
                            <button class="eligible_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 dark:border-red-800/40 transition-colors"
                                value="{{ $eli->id }}" title="Delete">
                                <i data-lucide="trash-2" class="h-3 w-3"></i>
                            </button>
                        @elseif($guard == 'employee' && $eli->status !== 1)
                            <a href="{{ route('eligibilityEdit', ['id' => $employeeRouteId ?? shortEncrypt((string) $employee->id), 'eid' => $eli->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 dark:border-amber-800/40 transition-colors" title="Edit">
                                <i data-lucide="pencil" class="h-3 w-3"></i>
                            </a>
                            <button class="eligible_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 dark:border-red-800/40 transition-colors"
                                value="{{ $eli->id }}" title="Delete">
                                <i data-lucide="trash-2" class="h-3 w-3"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 py-10 text-center">
            <i data-lucide="badge-check" class="mx-auto mb-2 h-8 w-8 text-muted-foreground/30"></i>
            <p class="text-xs text-muted-foreground">No eligibility records yet. Add one using the form above.</p>
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
                <i data-lucide="x" class="h-3.5 w-3.5"></i>
            </button>
        </div>
        <div class="p-4">
            <iframe id="modalPdf" src="" width="100%" height="560px" class="rounded-lg border border-border/60"></iframe>
        </div>
    </div>
</div>

{{-- Cancel / Remarks Modal --}}
<div id="cancel-modal-backdrop" class="fixed inset-0 z-[60] hidden items-center justify-center p-4" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
    <div class="relative w-full max-w-sm rounded-2xl border border-border/60 bg-card shadow-2xl overflow-hidden">
        <form method="POST" action="{{ route('eliCancel') }}" data-pds-cancel-form>
            @csrf
            <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
                <h3 class="text-sm font-semibold text-foreground">Cancel Entry</h3>
                <button type="button" onclick="closeCancelModal()" class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors">
                    <i data-lucide="x" class="h-3.5 w-3.5"></i>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <input type="hidden" name="id" id="eli-id">
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
@include('script.pdsRecordActions', ['config' => [
    'label' => 'Eligibility',
    'deleteSelector' => '.eligible_delete',
    'approveSelector' => '.eligible_approve',
    'rowSelector' => '.eligibility-row',
    'countSelector' => '[data-pds-record-count]',
    'deleteRoute' => route('eliDelete', ['id' => '__ID__']),
    'approveRoute' => route('eliApprove', ['id' => '__ID__']),
]])
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
    document.getElementById('pdfModalLabel').innerText = link.getAttribute('data-label');
    document.getElementById('modalPdf').src = link.getAttribute('data-pdf');
    document.getElementById('pdf-modal-backdrop').classList.replace('hidden', 'flex');
}
function closePdfModal() {
    document.getElementById('modalPdf').src = '';
    document.getElementById('pdf-modal-backdrop').classList.replace('flex', 'hidden');
}
function openCancelModal(id) {
    document.getElementById('eli-id').value = id;
    document.getElementById('remarks').value = '';
    document.getElementById('cancel-modal-backdrop').classList.replace('hidden', 'flex');
}
function closeCancelModal() {
    document.getElementById('cancel-modal-backdrop').classList.replace('flex', 'hidden');
}
</script>
@endpush
@endsection
