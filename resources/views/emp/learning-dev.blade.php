@extends('layouts.master')
@section('body')
@php
    $inputCls = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $fileCls  = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground file:mr-2 file:rounded file:border-0 file:bg-primary/10 file:px-2 file:py-0.5 file:text-[10px] file:font-semibold file:text-primary hover:file:bg-primary/20 transition-colors';
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/30">
                <i class="fas fa-book text-orange-600 dark:text-orange-400 text-sm"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Learning & Development</h1>
                <p class="text-[11px] text-muted-foreground">Training programs and interventions attended</p>
            </div>
        </div>

        {{-- Form card --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="fas fa-plus-circle text-orange-500 text-xs opacity-80"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">
                        {{ isset($learningdevedit) ? 'Edit Entry' : 'Add L&D Entry' }}
                    </span>
                </div>
                <button type="button" onclick="toggleForm()"
                    class="inline-flex items-center gap-1 rounded-lg border border-border/60 bg-background px-2.5 py-1 text-[10px] font-medium text-muted-foreground hover:bg-muted transition-colors">
                    <i id="form-toggle-icon" class="fas fa-chevron-up text-[9px] transition-transform"></i>
                    <span id="form-toggle-label">Hide Form</span>
                </button>
            </div>
            <div id="form-section">
                <form class="p-5" action="{{ isset($learningdevedit) ? route('learningdevUpdate', $learningdevedit->id) : route('learningdevCreate') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($learningdevedit))
                        <input type="hidden" name="id" value="{{ $learningdevedit->id }}">
                    @endif
                    <input type="hidden" name="empid" value="{{ $employee->emp_ID }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="{{ $labelCls }}">Title of Learning & Development Interventions / Training Program</label>
                            <input type="text" name="learning_dev" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($learningdevedit) ? $learningdevedit->learning_dev : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Inclusive Dates (From — To)</label>
                            <div class="flex gap-2">
                                <input type="date" id="inc_date1" name="inc_date1" class="{{ $inputCls }}"
                                    value="{{ isset($learningdevedit) ? $learningdevedit->inc_date1 : '' }}" required>
                                <input type="date" id="inc_date2" name="inc_date2" class="{{ $inputCls }}"
                                    value="{{ isset($learningdevedit) ? $learningdevedit->inc_date2 : '' }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Number of Hours</label>
                            <input type="number" name="num_hours" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($learningdevedit) ? $learningdevedit->num_hours : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Type of LD (Managerial / Supervisory / Technical / etc.)</label>
                            <input type="text" name="types" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($learningdevedit) ? $learningdevedit->types : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Conducted / Sponsored By</label>
                            <input type="text" name="conducted" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($learningdevedit) ? $learningdevedit->conducted : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Attachment (PDF)</label>
                            <input type="file" name="attachment" class="{{ $fileCls }}" accept="application/pdf">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" name="btn-submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-primary/90 transition-colors">
                            <i class="fas fa-save"></i>
                            {{ isset($learningdevedit) ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Records --}}
        @if(count($learningdev) > 0)
        <div class="space-y-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground px-1">
                {{ count($learningdev) }} {{ Str::plural('Record', count($learningdev)) }}
            </p>
            @foreach($learningdev as $learning)
            <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm learningdev-row row-{{ $learning->id }}">
                <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-border/40">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-foreground leading-snug">{{ $learning->learning_dev }}</p>
                        <p class="mt-0.5 text-[10px] text-muted-foreground">
                            {{ \Carbon\Carbon::parse($learning->inc_date1)->format('m/d/Y') }} —
                            {{ \Carbon\Carbon::parse($learning->inc_date2)->format('m/d/Y') }}
                            · <span class="font-semibold text-foreground">{{ number_format($learning->num_hours) }} hrs</span>
                            · {{ $learning->types }}
                        </p>
                        <p class="text-[10px] text-muted-foreground">Sponsored by: <span class="font-medium text-foreground">{{ $learning->conducted }}</span></p>
                    </div>
                    <div class="shrink-0">
                        @if($learning->status == 0)
                            <span id="status-{{ $learning->id }}"
                                class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-amber-200 dark:ring-amber-800/40">
                                To be Reviewed
                            </span>
                        @elseif($learning->status == 1)
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
                    @if(!empty($learning->attachment))
                        <a href="#" data-label="{{ $learning->learning_dev }}"
                           data-pdf="{{ asset('storage/' . $learning->attachment) }}"
                           onclick="showPdfModal(this); return false;"
                           class="inline-flex items-center gap-1 text-primary hover:underline font-semibold">
                            <i class="fas fa-eye text-[9px]"></i> Preview PDF
                        </a>
                    @endif
                    @if($learning->status == 2 && $learning->remarks)
                        <span class="text-red-500">Remarks: {{ $learning->remarks }}</span>
                    @endif
                    <div class="ml-auto flex items-center gap-1.5">
                        @if($guard == 'web')
                            <a href="{{ route('learningdevEdit', ['id' => $empid, 'eid' => $learning->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            <button class="learningdev_approve flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200/60 dark:bg-emerald-950/30 transition-colors"
                                value="{{ $learning->id }}" title="Approve">
                                <i class="fas fa-check text-[10px]"></i>
                            </button>
                            @if($learning->status == 0)
                                <button onclick="openCancelModal({{ $learning->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Cancel">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            @endif
                            <button class="learningdev_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 transition-colors"
                                value="{{ $learning->id }}" title="Delete">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        @elseif($guard == 'employee' && in_array($learning->status, [0, 2]))
                            <a href="{{ route('learningdevEdit', ['id' => $empid, 'eid' => $learning->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            <button class="learningdev_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 transition-colors"
                                value="{{ $learning->id }}" title="Delete">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-dashed border-border/60 bg-muted/20 py-10 text-center">
            <i class="fas fa-book text-3xl text-muted-foreground/30 mb-2"></i>
            <p class="text-xs text-muted-foreground">No learning & development records yet.</p>
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
        <form method="POST" action="{{ route('learningdevCancel') }}">
            @csrf
            <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
                <h3 class="text-sm font-semibold text-foreground">Cancel Entry</h3>
                <button type="button" onclick="closeCancelModal()" class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="p-5">
                <input type="hidden" name="id" id="learndev-id">
                <label class="block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-1">Remarks</label>
                <textarea name="remarks" id="remarks" rows="3" required
                    class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-xs text-foreground focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none"
                    placeholder="Enter reason for cancellation"></textarea>
            </div>
            <div class="flex justify-end gap-2 px-5 pb-5">
                <button type="button" onclick="closeCancelModal()"
                    class="rounded-xl border border-border/60 bg-background px-4 py-2 text-xs font-medium text-foreground hover:bg-muted transition-colors">Close</button>
                <button type="submit"
                    class="rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700 transition-colors">Cancel Entry</button>
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
    document.getElementById('learndev-id').value = id;
    document.getElementById('remarks').value = '';
    document.getElementById('cancel-modal-backdrop').classList.replace('hidden', 'flex');
}
function closeCancelModal() {
    document.getElementById('cancel-modal-backdrop').classList.replace('flex', 'hidden');
}
</script>
@endpush
@endsection
