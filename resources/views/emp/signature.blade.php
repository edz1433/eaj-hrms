@extends('layouts.master')
@section('body')
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
                <i data-lucide="signature" class="h-4 w-4 text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">E-Signature</h1>
                <p class="text-[11px] text-muted-foreground">Click the signature area to upload a new signature image</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="pen-line" class="h-3.5 w-3.5 text-indigo-500 opacity-70"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">Signature Preview</span>
            </div>
            <div class="p-6 flex flex-col items-center gap-4">

                {{-- Instruction card --}}
                <div class="w-full max-w-sm overflow-hidden rounded-xl border border-border/60">
                    <img src="{{ asset('Uploads/esign-note.jpg') }}" alt="Signature instructions" class="w-full h-auto">
                </div>

                {{-- Signature display --}}
                <div class="w-full max-w-sm rounded-xl border-2 border-dashed border-border/60 bg-muted/20 p-4 text-center cursor-pointer hover:border-primary/40 hover:bg-primary/5 transition-colors group"
                     onclick="document.getElementById('signature-file').click()">
                    <img id="signature-preview" src="{{ $imageData }}" alt="E-SIGNATURE"
                         class="mx-auto max-h-32 w-auto object-contain">
                    <p class="mt-2 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground group-hover:text-primary transition-colors">
                        <i data-lucide="cloud-upload" class="mr-1 inline h-3 w-3"></i> Click to upload new signature
                    </p>
                </div>

                <input type="file" id="signature-file" accept="image/png" class="hidden">

                <p class="text-[10px] text-muted-foreground text-center">
                    Accepted format: <span class="font-semibold">PNG</span> only &middot; Recommended: transparent background
                </p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    @include('script.signatureScript')
@endpush
