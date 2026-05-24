@extends('layouts.master')
@section('body')
@php
    $inputCls = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $govid    = explode(',', $govids->govid);
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                <i data-lucide="id-card" class="h-4 w-4 text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Government Issued ID</h1>
                <p class="text-[11px] text-muted-foreground">Passport, GSIS, SSS, PRC, Driver's License, etc.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="badge" class="h-3.5 w-3.5 text-blue-500 opacity-70"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">ID Details</span>
            </div>
            <div class="p-5">
                <p class="mb-4 text-xs text-muted-foreground">
                    Government Issued ID (i.e. Passport, GSIS, SSS, PRC, Driver's License, etc.)
                    — please indicate ID Number and Date of Issuance.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="{{ $labelCls }}">Government Issued ID</label>
                        <input class="{{ $inputCls }} input-details updated-data" type="text"
                            name="govid_0" data-array="0"
                            value="{{ $govid[0] ?? '' }}" id="govid-0"
                            placeholder="e.g. Passport, Driver's License">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">ID / License / Passport No.</label>
                        <input class="{{ $inputCls }} input-details updated-data" type="text"
                            name="govid_1" data-array="1"
                            value="{{ $govid[1] ?? '' }}" id="govid-1"
                            placeholder="ID number">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Date / Place of Issuance</label>
                        <input class="{{ $inputCls }} input-details updated-data" type="text"
                            name="govid_2" data-array="2"
                            value="{{ $govid[2] ?? '' }}" id="govid-2"
                            placeholder="e.g. Jan 1 2020 / Manila">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    @include('script.govidScript')
@endpush
