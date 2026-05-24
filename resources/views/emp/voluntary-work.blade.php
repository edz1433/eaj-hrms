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
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                <i data-lucide="hand-heart" class="h-4 w-4 text-green-600 dark:text-green-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Voluntary Work</h1>
                <p class="text-[11px] text-muted-foreground">Community service and volunteer activities</p>
            </div>
        </div>

        {{-- Form card --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="circle-plus" class="h-3.5 w-3.5 text-green-500 opacity-80"></i>
                    <span class="text-xs font-bold uppercase tracking-wide text-foreground">
                        {{ isset($voluntaryworksedit) ? 'Edit Entry' : 'Add Voluntary Work' }}
                    </span>
                </div>
                <button type="button" onclick="toggleForm()"
                    class="inline-flex items-center gap-1 rounded-lg border border-border/60 bg-background px-2.5 py-1 text-[10px] font-medium text-muted-foreground hover:bg-muted transition-colors">
                    <i id="form-toggle-icon" data-lucide="chevron-up" class="h-3 w-3 transition-transform"></i>
                    <span id="form-toggle-label">Hide Form</span>
                </button>
            </div>
            <div id="form-section">
                <form class="p-5" action="{{ isset($voluntaryworksedit) ? route('voluntaryworksUpdate', $voluntaryworksedit->id) : route('voluntaryworksCreate') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($voluntaryworksedit))
                        <input type="hidden" name="id" value="{{ $voluntaryworksedit->id }}">
                    @endif
                    <input type="hidden" name="empid" value="{{ $employee->emp_ID }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label class="{{ $labelCls }}">Name & Address of Organization (write in full)</label>
                            <input type="text" name="org_name" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($voluntaryworksedit) ? $voluntaryworksedit->org_name : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Inclusive Dates (From — To)</label>
                            <div class="flex gap-2">
                                <input type="date" id="inc_date1" name="inc_date1" class="{{ $inputCls }}"
                                    value="{{ isset($voluntaryworksedit) ? $voluntaryworksedit->inc_date1 : '' }}" required>
                                <input type="date" id="inc_date2" name="inc_date2" class="{{ $inputCls }}"
                                    value="{{ isset($voluntaryworksedit) ? $voluntaryworksedit->inc_date2 : '' }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Number of Hours</label>
                            <input type="number" name="num_hours" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($voluntaryworksedit) ? $voluntaryworksedit->num_hours : '' }}" autocomplete="off" required>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Position / Nature of Work</label>
                            <input type="text" name="position" class="{{ $inputCls }}" placeholder="N/A"
                                value="{{ isset($voluntaryworksedit) ? $voluntaryworksedit->position : '' }}" autocomplete="off" required>
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
                            {{ isset($voluntaryworksedit) ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Records --}}
        @if(count($voluntaryworks) > 0)
        <div class="space-y-3">
            <p data-pds-record-count class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground px-1">
                {{ count($voluntaryworks) }} {{ Str::plural('Record', count($voluntaryworks)) }}
            </p>
            @foreach($voluntaryworks as $vwork)
            @php
                $volTitle = trim((string) $vwork->position) ?: 'Untitled Volunteer Role';
                $volOrg = trim((string) $vwork->org_name) ?: 'Organization not specified';
                $volHours = is_numeric($vwork->num_hours) ? number_format($vwork->num_hours) : ($vwork->num_hours ?: '0');
                $volFrom = $vwork->inc_date1 ? \Carbon\Carbon::parse($vwork->inc_date1)->format('M d, Y') : 'No start date';
                $volTo = $vwork->inc_date2 ? \Carbon\Carbon::parse($vwork->inc_date2)->format('M d, Y') : 'No end date';
            @endphp
            <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm transition hover:border-primary/25 hover:shadow-md voluntaryworks-row row-{{ $vwork->id }}">
                <div class="flex flex-col gap-4 px-5 py-4 border-b border-border/40 md:flex-row md:items-start md:justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300">
                                <i data-lucide="hand-heart" class="h-4 w-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold leading-snug text-foreground">{{ $volTitle }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-[11px] text-muted-foreground">
                                    <i data-lucide="map-pin" class="h-3 w-3 shrink-0"></i>
                                    <span class="truncate">{{ $volOrg }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="rounded-xl border border-border/60 bg-muted/20 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"><i data-lucide="calendar-days" class="h-3 w-3"></i> Inclusive Dates</p>
                                <p class="mt-1 text-xs font-semibold text-foreground">{{ $volFrom }} - {{ $volTo }}</p>
                            </div>
                            <div class="rounded-xl border border-border/60 bg-muted/20 px-3 py-2">
                                <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"><i data-lucide="clock-3" class="h-3 w-3"></i> Hours Rendered</p>
                                <p class="mt-1 text-xs font-semibold text-foreground">{{ $volHours }} hrs</p>
                            </div>
                        </div>
                        <div class="hidden">
                        <p class="text-xs font-bold text-foreground">{{ $vwork->position }}</p>
                        <p class="text-[11px] text-muted-foreground">{{ $vwork->org_name }}</p>
                        <p class="mt-0.5 text-[10px] text-muted-foreground">
                            {{ \Carbon\Carbon::parse($vwork->inc_date1)->format('m/d/Y') }} —
                            {{ \Carbon\Carbon::parse($vwork->inc_date2)->format('m/d/Y') }}
                            · <span class="font-semibold text-foreground">{{ number_format($vwork->num_hours) }} hrs</span>
                        </p>
                        </div>
                    </div>
                    <div class="shrink-0 md:pt-1">
                        @if($vwork->status == 0)
                            <span id="status-{{ $vwork->id }}"
                                class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-amber-200 dark:ring-amber-800/40">
                                To be Reviewed
                            </span>
                        @elseif($vwork->status == 1)
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
                    @if(!empty($vwork->attachment))
                        <a href="#" data-label="{{ $vwork->position }}"
                           data-pdf="{{ asset('storage/' . $vwork->attachment) }}"
                           onclick="showPdfModal(this); return false;"
                           class="inline-flex items-center gap-1 text-primary hover:underline font-semibold">
                            <i data-lucide="eye" class="h-3 w-3"></i> Preview PDF
                        </a>
                    @endif
                    @if($vwork->status == 2 && $vwork->remarks)
                        <span class="text-red-500">Remarks: {{ $vwork->remarks }}</span>
                    @endif
                    <div data-pds-actions class="ml-auto flex items-center gap-1.5">
                        @if($guard == 'web')
                            <a href="{{ route('voluntaryworksEdit', ['id' => shortEncrypt((string) $employee->id), 'eid' => $vwork->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i data-lucide="pencil" class="h-3 w-3"></i>
                            </a>
                            <button data-pds-hide-on-approve class="voluntaryworks_approve flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200/60 dark:bg-emerald-950/30 transition-colors"
                                value="{{ $vwork->id }}" title="Approve">
                                <i data-lucide="check" class="h-3 w-3"></i>
                            </button>
                            @if($vwork->status == 0)
                                <button data-pds-hide-on-approve onclick="openCancelModal({{ $vwork->id }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Cancel">
                                    <i data-lucide="x" class="h-3 w-3"></i>
                                </button>
                            @endif
                            <button class="voluntaryworks_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 transition-colors"
                                value="{{ $vwork->id }}" title="Delete">
                                <i data-lucide="trash-2" class="h-3 w-3"></i>
                            </button>
                        @elseif($guard == 'employee' && $vwork->status == 0)
                            <a href="{{ route('voluntaryworksEdit', ['id' => shortEncrypt((string) $employee->id), 'eid' => $vwork->id]) }}"
                               class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200/60 dark:bg-amber-950/30 transition-colors" title="Edit">
                                <i data-lucide="pencil" class="h-3 w-3"></i>
                            </a>
                            <button class="voluntaryworks_delete flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-200/60 dark:bg-red-950/30 transition-colors"
                                value="{{ $vwork->id }}" title="Delete">
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
            <i data-lucide="hand-heart" class="mx-auto mb-2 h-8 w-8 text-muted-foreground/30"></i>
            <p class="text-xs text-muted-foreground">No voluntary work records yet.</p>
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

{{-- Cancel Modal --}}
<div id="cancel-modal-backdrop" class="fixed inset-0 z-[60] hidden items-center justify-center p-4" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
    <div class="relative w-full max-w-sm rounded-2xl border border-border/60 bg-card shadow-2xl overflow-hidden">
        <form method="POST" action="{{ route('voluntaryworksCancel') }}" data-pds-cancel-form>
            @csrf
            <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
                <h3 class="text-sm font-semibold text-foreground">Cancel Entry</h3>
                <button type="button" onclick="closeCancelModal()" class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors">
                    <i data-lucide="x" class="h-3.5 w-3.5"></i>
                </button>
            </div>
            <div class="p-5">
                <input type="hidden" name="id" id="vwork-id">
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
@include('script.pdsRecordActions', ['config' => [
    'label' => 'Voluntary Work',
    'deleteSelector' => '.voluntaryworks_delete',
    'approveSelector' => '.voluntaryworks_approve',
    'rowSelector' => '.voluntaryworks-row',
    'countSelector' => '[data-pds-record-count]',
    'deleteRoute' => route('voluntaryworkDelete', ['id' => '__ID__']),
    'approveRoute' => route('voluntaryworksApprove', ['id' => '__ID__']),
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
    document.getElementById('pdfModalLabel').innerText = link.getAttribute('data-label') || '';
    document.getElementById('modalPdf').src = link.getAttribute('data-pdf');
    document.getElementById('pdf-modal-backdrop').classList.replace('hidden', 'flex');
}
function closePdfModal() {
    document.getElementById('modalPdf').src = '';
    document.getElementById('pdf-modal-backdrop').classList.replace('flex', 'hidden');
}
function openCancelModal(id) {
    document.getElementById('vwork-id').value = id;
    document.getElementById('remarks').value = '';
    document.getElementById('cancel-modal-backdrop').classList.replace('hidden', 'flex');
}
function closeCancelModal() {
    document.getElementById('cancel-modal-backdrop').classList.replace('flex', 'hidden');
}
</script>
@endpush
@endsection
