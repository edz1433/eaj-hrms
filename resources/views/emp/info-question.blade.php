@extends('layouts.master')
@section('body')
@php
    $inputCls  = 'rounded-lg border border-border/60 bg-background px-2.5 py-1 text-xs text-foreground placeholder:text-muted-foreground/50 focus:border-primary/60 focus:outline-none focus:ring-1 focus:ring-primary/30 transition-colors';
    $question  = explode(',', $infoquestion->question);
    $qdetails  = explode(',', $infoquestion->qdetails);
@endphp
<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">
    @include('emp.submenu-side')

    <div class="flex-1 min-w-0 space-y-4">

        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                <i data-lucide="circle-help" class="h-4 w-4 text-amber-600 dark:text-amber-400"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-foreground">Other Info Questions</h1>
                <p class="text-[11px] text-muted-foreground">CS Form 212 — answer all questions honestly</p>
            </div>
        </div>

        @php
        $sectionA = [
            [0, 'Within the third degree?', 'text', null],
            [1, 'Within the fourth degree (for Local Government Unit - Career Employees)?', 'text', 'If Yes, give details'],
            [2, 'Have you ever been found guilty of any administrative offense?', 'text', 'If Yes, give details'],
            [3, 'Have you been criminally charged before any court?', 'mixed', null],
            [4, 'Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?', 'text', 'If Yes, give details'],
            [5, 'Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?', 'text', 'If Yes, give details'],
            [6, 'Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?', 'text', 'If Yes, give details'],
            [7, 'Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?', 'text', 'If Yes, give details'],
            [8, 'Have you acquired the status of an immigrant or permanent resident of another country?', 'text', 'If Yes, give details (country)'],
        ];
        $sectionB = [
            [9,  'Are you a member of any indigenous group?', 'text', 'If Yes, please specify'],
            [10, 'Are you a person with disability?', 'text', 'If Yes, please specify'],
            [11, 'Are you a solo parent?', 'text', 'If Yes, please specify'],
        ];
        @endphp

        {{-- Section A --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary">A</span>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground">
                    Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you?
                </span>
            </div>
            <div class="divide-y divide-border/40">
                @foreach($sectionA as [$idx, $qtext, $dtype, $detailLabel])
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-foreground mb-2">
                        <span class="mr-1 text-muted-foreground font-normal">{{ $loop->iteration }}.</span>{{ $qtext }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input class="updated-data h-3.5 w-3.5 accent-primary" type="radio"
                                name="question_{{ $idx }}" data-array="{{ $idx }}"
                                id="no-{{ $idx }}" value="0"
                                {{ ($question[$idx] ?? '') == 0 ? 'checked' : '' }}>
                            <span class="text-xs text-foreground">No</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input class="updated-data h-3.5 w-3.5 accent-primary" type="radio"
                                name="question_{{ $idx }}" data-array="{{ $idx }}"
                                id="yes-{{ $idx }}" value="1"
                                {{ ($question[$idx] ?? '') == 1 ? 'checked' : '' }}>
                            <span class="text-xs text-foreground">Yes</span>
                        </label>
                        @if($idx == 3)
                            <div data-detail-for="{{ $idx }}" class="{{ ($question[$idx] ?? '') == 1 ? 'flex' : 'hidden' }} flex-wrap items-center gap-2 ml-2">
                                <span class="text-[10px] text-muted-foreground">Date Filed:</span>
                                <input class="{{ $inputCls }} input-details updated-data w-36"
                                    type="date" name="qdetails_3_date" data-array="12"
                                    value="{{ $qdetails[12] ?? '' }}" id="details-date-3" @disabled(($question[$idx] ?? '') != 1)>
                                <span class="text-[10px] text-muted-foreground">Status of Case/s:</span>
                                <input class="{{ $inputCls }} input-details updated-data w-48"
                                    type="text" name="qdetails_{{ $idx }}" data-array="{{ $idx }}"
                                    value="{{ $qdetails[$idx] ?? '' }}" id="details-{{ $idx }}"
                                    placeholder="Status" @disabled(($question[$idx] ?? '') != 1)>
                            </div>
                        @elseif($detailLabel)
                            <div data-detail-for="{{ $idx }}" class="{{ ($question[$idx] ?? '') == 1 ? 'flex' : 'hidden' }} items-center gap-2 ml-2">
                                <span class="text-[10px] text-muted-foreground">{{ $detailLabel }}:</span>
                                <input class="{{ $inputCls }} input-details updated-data w-64"
                                    type="text" name="qdetails_{{ $idx }}" data-array="{{ $idx }}"
                                    value="{{ $qdetails[$idx] ?? '' }}" id="details-{{ $idx }}"
                                    placeholder="Details" @disabled(($question[$idx] ?? '') != 1)>
                            </div>
                        @else
                            <input class="hidden input-details updated-data" type="hidden"
                                name="qdetails_{{ $idx }}" data-array="{{ $idx }}"
                                value="{{ $qdetails[$idx] ?? '' }}" id="details-{{ $idx }}">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Section B --}}
        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-[10px] font-bold text-primary">B</span>
                <span class="text-xs font-bold uppercase tracking-wide text-foreground leading-tight">
                    Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277); and (c) Solo Parents Welfare Act of 2000 (RA 8972)
                </span>
            </div>
            <div class="divide-y divide-border/40">
                @foreach($sectionB as [$idx, $qtext, $dtype, $detailLabel])
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-foreground mb-2">
                        <span class="mr-1 text-muted-foreground font-normal">{{ $loop->iteration }}.</span>{{ $qtext }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input class="updated-data h-3.5 w-3.5 accent-primary" type="radio"
                                name="question_{{ $idx }}" data-array="{{ $idx }}"
                                id="no-{{ $idx }}" value="0"
                                {{ ($question[$idx] ?? '') == 0 ? 'checked' : '' }}>
                            <span class="text-xs text-foreground">No</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input class="updated-data h-3.5 w-3.5 accent-primary" type="radio"
                                name="question_{{ $idx }}" data-array="{{ $idx }}"
                                id="yes-{{ $idx }}" value="1"
                                {{ ($question[$idx] ?? '') == 1 ? 'checked' : '' }}>
                            <span class="text-xs text-foreground">Yes</span>
                        </label>
                        <div data-detail-for="{{ $idx }}" class="{{ ($question[$idx] ?? '') == 1 ? 'flex' : 'hidden' }} items-center gap-2 ml-2">
                            <span class="text-[10px] text-muted-foreground">{{ $detailLabel }}:</span>
                            <input class="{{ $inputCls }} input-details updated-data w-64"
                                type="text" name="qdetails_{{ $idx }}" data-array="{{ $idx }}"
                                value="{{ $qdetails[$idx] ?? '' }}" id="details-{{ $idx }}"
                                placeholder="Details" @disabled(($question[$idx] ?? '') != 1)>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    @include('script.infoquestionScript')
@endpush
