@extends('layouts.master')
@section('body')
@php
    $inputCls = 'w-full rounded-lg border border-border/60 bg-background px-3 py-1.5 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $labelCls = 'block text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-0.5';
    $refname  = explode(';', $references->refname);
    $refadd   = explode(';', $references->refadd);
    $reftelno = explode(';', $references->reftelno);
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/30">
                <i data-lucide="contact-round" class="h-4 w-4 text-teal-600 dark:text-teal-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">References</h1>
                <p class="text-[11px] text-muted-foreground">Persons not related by consanguinity or affinity</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <i data-lucide="users" class="h-3.5 w-3.5 text-teal-500 opacity-70"></i>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">Character References</span>
            </div>
            <div class="p-5 space-y-4">
                {{-- Column headers --}}
                <div class="hidden sm:grid sm:grid-cols-3 gap-4">
                    <span class="{{ $labelCls }}">Name</span>
                    <span class="{{ $labelCls }}">Address</span>
                    <span class="{{ $labelCls }}">Telephone No.</span>
                </div>

                @foreach([0,1,2] as $i)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="sm:hidden {{ $labelCls }}">Name</label>
                        <input class="{{ $inputCls }} input-details updated-data" type="text"
                            name="refname_{{ $i }}" data-array="{{ $i }}"
                            value="{{ $refname[$i] ?? '' }}" id="refname-{{ $i }}"
                            placeholder="Full name">
                    </div>
                    <div>
                        <label class="sm:hidden {{ $labelCls }}">Address</label>
                        <input class="{{ $inputCls }} input-details updated-data" type="text"
                            name="refadd_{{ $i }}" data-array="{{ $i }}"
                            value="{{ $refadd[$i] ?? '' }}" id="refadd-{{ $i }}"
                            placeholder="Address">
                    </div>
                    <div>
                        <label class="sm:hidden {{ $labelCls }}">Telephone No.</label>
                        <input class="{{ $inputCls }} input-details updated-data" type="text"
                            name="reftelno_{{ $i }}" data-array="{{ $i }}"
                            value="{{ $reftelno[$i] ?? '' }}" id="reftelno-{{ $i }}"
                            placeholder="Telephone number">
                    </div>
                </div>
                @if($i < 2)
                    <div class="border-t border-border/40"></div>
                @endif
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    @include('script.referenceScript')
@endpush
