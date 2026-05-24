@extends('layouts.master')
@section('pageTitle', 'Settings')

@section('body')
@php
    $themePresets = [
        'ea'      => ['label' => 'EA Pink',  'hex' => '#C9407A'],
        'indigo'  => ['label' => 'Indigo',   'hex' => '#4f46e5'],
        'emerald' => ['label' => 'Emerald',  'hex' => '#059669'],
        'amber'   => ['label' => 'Amber',    'hex' => '#d97706'],
        'rose'    => ['label' => 'Rose',     'hex' => '#e11d48'],
        'violet'  => ['label' => 'Violet',   'hex' => '#7c3aed'],
        'cyan'    => ['label' => 'Cyan',     'hex' => '#0891b2'],
        'teal'    => ['label' => 'Teal',     'hex' => '#0d9488'],
        'orange'  => ['label' => 'Orange',   'hex' => '#ea580c'],
        'slate'   => ['label' => 'Slate',    'hex' => '#475569'],
    ];
    $activeTheme = array_key_exists($setting->theme ?? '', $themePresets) ? $setting->theme : 'ea';
    $idCardTemplates = [
        'classic' => ['label' => 'Classic', 'icon' => 'badge', 'desc' => 'Formal portrait ID with a clean brand header.'],
        'bold'    => ['label' => 'Bold',    'icon' => 'scan-face', 'desc' => 'High-contrast card with strong color blocking.'],
        'minimal' => ['label' => 'Minimal', 'icon' => 'panel-left', 'desc' => 'Quiet executive layout with a slim color rail.'],
    ];
    $activeIdTemplate = array_key_exists($setting->id_card_template ?? '', $idCardTemplates) ? $setting->id_card_template : 'classic';
    $idPrimary = $setting->id_card_primary_color ?: ($setting->primary_color ?: '#C9407A');
    $idAccent = $setting->id_card_accent_color ?: ($setting->accent_color ?: '#fce7f3');
    $idLogoUrl = $setting->id_card_logo ? asset('Uploads/Settings/' . $setting->id_card_logo) : null;
    $dtrHeaderUrl = $setting->dtrHeaderUrl();
    $leaveFormHeaderUrl = $setting->leaveFormHeaderUrl();
    $sectorMeta = [
        'government' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
        'education'  => ['bg' => '#d1fae5', 'color' => '#065f46'],
        'private'    => ['bg' => '#ede9fe', 'color' => '#5b21b6'],
        'industry'   => ['bg' => '#ffedd5', 'color' => '#9a3412'],
    ];
    $navItems = [
        'org'         => ['icon' => 'landmark',          'label' => 'Organization', 'desc' => 'Sector & org type'],
        'general'     => ['icon' => 'sliders-horizontal', 'label' => 'General',      'desc' => 'System & emails'],
        'appearance'  => ['icon' => 'palette',            'label' => 'Appearance',   'desc' => 'Theme & colors'],
        'menus'       => ['icon' => 'layers',             'label' => 'Menus',        'desc' => 'Sidebar visibility'],
        'permissions' => ['icon' => 'shield-half',        'label' => 'Permissions',  'desc' => 'User access control'],
    ];

    // Per-org-type labels for the four leadership positions
    $leadershipLabels = [
        // â”€â”€ Government â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        'suc'  => ['suc_pres' => 'University President',   'vpaa' => 'VP Academic Affairs',      'vpaf' => 'VP Admin & Finance', 'hr' => 'HR Director / HRMO'],
        'nga'  => ['suc_pres' => 'Secretary / Agency Head','vpaa' => 'Undersecretary',            'vpaf' => 'Finance Director / CFO',      'hr' => 'HR Director / HRMO'],
        'lgu'  => ['suc_pres' => 'Mayor / Governor',       'vpaa' => 'Vice Mayor / Governor','vpaf' => 'Mun. / Prov. Treasurer','hr' => 'HRMO / HR Officer'],
        'gocc' => ['suc_pres' => 'President / CEO',        'vpaa' => 'Chief Operating Officer',  'vpaf' => 'Chief Finance Officer',       'hr' => 'CHRO / HR Manager'],
        // â”€â”€ Education â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        'private_school' => ['suc_pres' => 'School President',  'vpaa' => 'Academic Dean',       'vpaf' => 'Finance Director',            'hr' => 'HR Head'],
        'training'       => ['suc_pres' => 'Director',          'vpaa' => 'Deputy Director',     'vpaf' => 'Finance Officer',             'hr' => 'HR Officer'],
        // â”€â”€ Private â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        'sme'          => ['suc_pres' => 'Owner / CEO',         'vpaa' => 'Operations Manager',  'vpaf' => 'Finance Manager',             'hr' => 'HR Manager'],
        'medium_large' => ['suc_pres' => 'CEO / President',     'vpaa' => 'Chief Operating Officer','vpaf' => 'Chief Finance Officer',     'hr' => 'HR Director / CHRO'],
        'enterprise'   => ['suc_pres' => 'Group CEO',           'vpaa' => 'Group COO',           'vpaf' => 'Group CFO',                   'hr' => 'Group CHRO'],
        // â”€â”€ Industry â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        'bpo'           => ['suc_pres' => 'Country Manager',    'vpaa' => 'Operations Director', 'vpaf' => 'Finance Director',            'hr' => 'HR Director'],
        'manufacturing' => ['suc_pres' => 'Plant Manager / CEO','vpaa' => 'Operations Manager',  'vpaf' => 'Finance Controller',          'hr' => 'HR Manager'],
        'healthcare'    => ['suc_pres' => 'Hospital Director',  'vpaa' => 'Medical Director',    'vpaf' => 'Finance Director',            'hr' => 'HR Director'],
        'retail'        => ['suc_pres' => 'CEO / General Manager','vpaa' => 'Operations Director','vpaf' => 'Finance Director',           'hr' => 'HR Director'],
        // â”€â”€ Fallback â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        '_default' => ['suc_pres' => 'President / Agency Head', 'vpaa' => 'VPAA / Deputy Head',  'vpaf' => 'VPAF / Finance Head',         'hr' => 'HR Head'],
    ];

    // Resolve labels for the currently saved org type
    $curOrgType = $setting->org_type ?? 'suc';
    $curLabels  = $leadershipLabels[$curOrgType] ?? $leadershipLabels['_default'];
@endphp

{{-- â”€â”€ Toast â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
@if(session('success'))
<div id="sg-toast" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl border border-border bg-card px-5 py-4 shadow-xl shadow-black/8 animate-[toastIn_.25s_ease-out]">
    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40">
        <i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
    </div>
    <div class="min-w-0">
        <p class="text-sm font-semibold text-foreground">Saved successfully</p>
        <p class="truncate text-xs text-muted-foreground max-w-xs">{{ session('success') }}</p>
    </div>
    <button onclick="document.getElementById('sg-toast').remove()"
            class="ml-2 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted transition-colors">
        <i data-lucide="x" class="w-3.5 h-3.5"></i>
    </button>
</div>
@endif

<div class="mx-auto max-w-[1100px] pb-24">

    {{-- â”€â”€ Page header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="mb-8">
        <nav class="mb-3 flex items-center gap-1.5 text-xs text-muted-foreground">
            <i data-lucide="home" class="w-3 h-3 inline-block"></i>
            <a href="{{ route('dashboard') }}" class="hover:text-foreground transition-colors">Dashboard</a>
            <i data-lucide="chevron-right" class="w-3 h-3 opacity-40 inline-block"></i>
            <span class="text-foreground font-medium">Settings</span>
        </nav>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">System Settings</h1>
        <p class="mt-1.5 text-sm text-muted-foreground max-w-lg">
            Configure your organization profile, user permissions, appearance, and system preferences.
        </p>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8">

        {{-- â”€â”€ Left nav â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <aside class="lg:w-56 lg:shrink-0 lg:sticky lg:top-20 lg:self-start">

            {{-- Mobile: horizontal pills --}}
            <div class="flex gap-1.5 overflow-x-auto pb-2 lg:hidden">
                @foreach($navItems as $key => $item)
                    <button type="button" data-tab="{{ $key }}"
                            class="sg-mob-tab whitespace-nowrap flex items-center gap-1.5 rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-medium text-muted-foreground transition-all hover:text-foreground {{ $loop->first ? 'sg-mob-active' : '' }}"
                            style="{{ $loop->first ? 'border-color:var(--color-primary);color:var(--color-primary);' : '' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-3 h-3 shrink-0"></i>{{ $item['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Desktop: vertical nav inside card --}}
            <div class="hidden lg:block">
                <div class="rounded-2xl border border-border/60 bg-card p-2 shadow-sm">
                    <p class="mb-2 px-3 pt-1 text-[9px] font-bold uppercase tracking-[.12em] text-muted-foreground/50">Configuration</p>
                    @foreach($navItems as $key => $item)
                        <button type="button" data-tab="{{ $key }}"
                                class="sg-tab relative flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left transition hover:bg-muted/60 data-[active=true]:bg-primary/10 {{ $loop->first ? 'active' : '' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"><i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i></span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium leading-none text-foreground">{{ $item['label'] }}</p>
                                <p class="mt-1 text-[11px] leading-none text-muted-foreground truncate">{{ $item['desc'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- â”€â”€ Content panels â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="flex-1 min-w-0 space-y-5">

            {{-- â•â• ORGANIZATION â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="panel-org" class="sg-panel space-y-5">
                <form id="form-org" method="POST" action="{{ route('settings.saveOrg') }}" class="space-y-5">
                    @csrf

                    {{-- Org name --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Organization Identity</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Official name used across documents and reports.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Organization Name</label>
                            <input type="text" name="org_name"
                                   value="{{ old('org_name', $setting->org_name) }}"
                                   placeholder="e.g. Central Philippine State University"
                                   class="block h-10 w-full rounded-xl border border-border bg-background px-3 text-sm text-foreground outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20 disabled:opacity-60">
                        </div>
                    </div>

                    {{-- Sector --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="landmark" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Sector</p>
                                <p class="text-xs text-muted-foreground mt-0.5">The industry sector your organization belongs to.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <input type="hidden" name="sector" id="inp-sector"
                                   value="{{ old('sector', $setting->sector ?? 'government') }}">
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach($orgConfig as $sKey => $sector)
                                    @php
                                        $sm  = $sectorMeta[$sKey];
                                        $cur = ($setting->sector ?? 'government') === $sKey;
                                    @endphp
                                    <button type="button" data-sector="{{ $sKey }}"
                                            onclick="selectSector('{{ $sKey }}')"
                                            class="relative flex cursor-pointer flex-col items-center gap-3 rounded-2xl border-2 border-border p-5 text-center transition hover:border-primary/40 relative flex flex-col items-center gap-3 rounded-2xl border-2 border-border p-5 text-center {{ $cur ? 'is-selected' : '' }}">
                                        <span class="sc-icon flex h-12 w-12 items-center justify-center rounded-2xl transition-all"
                                              style="background:{{ $cur ? 'var(--color-primary)' : $sm['bg'] }}; color:{{ $cur ? '#fff' : $sm['color'] }}">
                                            <i data-lucide="{{ $sector['icon'] }}" class="w-6 h-6"></i>
                                        </span>
                                        <p class="text-xs font-bold text-foreground leading-snug">{{ $sector['label'] }}</p>
                                        <span class="sc-check absolute right-2.5 top-2.5 {{ $cur ? 'flex' : 'hidden' }} h-5 w-5 items-center justify-center rounded-full bg-primary text-white">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Org type --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="network" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Organization Type</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Specific classification within your selected sector.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            @foreach($orgConfig as $sKey => $sector)
                                <div id="types-{{ $sKey }}"
                                     class="org-types grid grid-cols-1 gap-3 sm:grid-cols-2
                                            {{ ($setting->sector ?? 'government') === $sKey ? '' : 'hidden' }}">
                                    @foreach($sector['types'] as $tKey => $type)
                                        <label class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 border-border p-4 transition hover:border-primary/40 has-[:checked]:border-primary has-[:checked]:bg-primary/5 flex cursor-pointer items-start gap-4 rounded-2xl border-2 border-border p-4 hover:border-primary/30">
                                            <div class="mt-0.5 shrink-0">
                                                        <input type="radio" name="org_type" value="{{ $tKey }}" class="peer sr-only"
                                                       {{ ($setting->org_type ?? 'suc') === $tKey ? 'checked' : '' }}>
                                                <div class="flex h-4 w-4 items-center justify-center rounded-full border-2 border-border bg-background transition peer-checked:border-primary flex h-4 w-4 items-center justify-center rounded-full border-2 border-border bg-background">
                                                    <div class="h-2 w-2 scale-0 rounded-full bg-primary transition peer-checked:scale-100 h-1.5 w-1.5 rounded-full"></div>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <i data-lucide="{{ $type['icon'] }}" class="w-3.5 h-3.5 text-primary shrink-0"></i>
                                                    <span class="text-sm font-semibold text-foreground">{{ $type['label'] }}</span>
                                                </div>
                                                <p class="text-xs text-muted-foreground leading-relaxed">{{ $type['desc'] }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Employment types --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <i data-lucide="badge" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-foreground">Employment Types</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Enable appointment types available in your organization.</p>
                                </div>
                            </div>
                            <button type="button" onclick="applySuggested()" class="inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground transition hover:bg-muted shrink-0">
                                <i data-lucide="wand-sparkles" class="w-3.5 h-3.5 text-primary"></i>
                                <span class="text-primary font-semibold">Auto-suggest</span>
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-wrap gap-2">
                                @foreach($empTypes as $eKey => $empType)
                                    <div class="emp-chip">
                                        <label class="cursor-pointer select-none">
                                            <input type="checkbox" name="emp_types[]" value="{{ $eKey }}" class="peer sr-only emp-type-input"
                                                   {{ in_array($eKey, $enabledTypes) ? 'checked' : '' }}>
                                            <div class="chip-bd flex items-center gap-2 rounded-xl border-2 border-border px-3.5 py-2.5 transition-all hover:border-primary/40 peer-checked:border-primary peer-checked:bg-primary/5">
                                                <span class="chip-dot h-1.5 w-1.5 shrink-0 rounded-full bg-muted-foreground/30 transition-all peer-checked:bg-primary"></span>
                                                <i data-lucide="{{ $empType['icon'] }}" class="chip-icon w-3.5 h-3.5 text-muted-foreground transition-colors shrink-0 peer-checked:text-primary"></i>
                                                <span class="text-sm font-medium text-foreground">{{ $empType['label'] }}</span>
                                                @if($empType['govt'])
                                                    <span class="rounded bg-blue-100 dark:bg-blue-900/40 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400">GOV</span>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Save Organization
                        </button>
                    </div>
                </form>
            </div>

            {{-- â•â• GENERAL â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="panel-general" class="sg-panel hidden space-y-5">
                <form id="form-general" method="POST" action="{{ route('settings.saveGeneral') }}" class="space-y-5">
                    @csrf

                    {{-- System --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="server" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">System Configuration</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Global system name and operational mode.</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">System Name</label>
                                <input type="text" name="system_name"
                                       value="{{ old('system_name', $setting->system_name) }}"
                                       placeholder="{{ $sysName ?? 'EAJ HRMS' }}" class="block h-10 w-full rounded-xl border border-border bg-background px-3 text-sm text-foreground outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20 disabled:opacity-60">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Employee ID Start Code</label>
                                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_140px]">
                                    <input type="text" name="employee_id_prefix"
                                           value="{{ old('employee_id_prefix', $setting->employeeIdPrefix()) }}"
                                           maxlength="20"
                                           pattern="[A-Za-z0-9]*"
                                           placeholder="EMP"
                                           class="block h-10 w-full rounded-xl border border-border bg-background px-3 text-sm font-semibold uppercase text-foreground outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20 disabled:opacity-60"
                                           oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase(); updateEmployeeIdPreview(this.value);">
                                    <div class="flex h-10 items-center rounded-xl border border-border bg-muted/30 px-3 font-mono text-sm font-bold text-foreground">
                                        <span id="employee-id-prefix-preview">{{ $setting->employeeIdPrefix() }}</span>0001
                                    </div>
                                </div>
                                <p class="mt-1.5 text-xs text-muted-foreground">New employee records will use this prefix, for example <span class="font-mono">{{ $setting->employeeIdPrefix() }}0001</span>.</p>
                            </div>
                            <div class="flex items-center justify-between gap-6 rounded-2xl border border-border bg-muted/20 px-5 py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-foreground">Maintenance Mode</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Non-admin users see a maintenance page while enabled.</p>
                                </div>
                                <div class="inline-flex h-6 w-11 cursor-pointer items-center rounded-full p-0.5 transition {{ $setting->maintenance ? 'bg-primary' : 'bg-muted' }} shrink-0"
                                     onclick="toggleSw(this,'cb-maintenance')">
                                    <div class="h-5 w-5 rounded-full bg-white shadow transition-transform {{ $setting->maintenance ? 'translate-x-5' : '' }}"></div>
                                </div>
                                <input type="hidden" name="maintenance" value="0">
                                <input type="checkbox" id="cb-maintenance" name="maintenance" value="1"
                                       class="sr-only" {{ $setting->maintenance ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Emails --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Email Addresses</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Used for notifications, job postings, and official communications.</p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                            @foreach([
                                ['records_office_email', 'Records Office', 'file-text'],
                                ['job_portal_email',     'Job Portal',     'briefcase'],
                                ['hr_head_email',        'HR Head',        'user-round'],
                            ] as [$field, $lbl, $ico])
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                        <i data-lucide="{{ $ico }}" class="mr-1 w-3.5 h-3.5 opacity-50 inline-block align-middle"></i>{{ $lbl }}
                                    </label>
                                    <input type="email" name="{{ $field }}"
                                           value="{{ old($field, $setting->$field) }}"
                                           placeholder="email@domain.gov.ph" class="block h-10 w-full rounded-xl border border-border bg-background px-3 text-sm text-foreground outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20 disabled:opacity-60">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Leadership --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Leadership Positions</p>
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    Assigned employees appear as signatories on official documents and PDFs.
                                    <span id="leadership-org-hint" class="ml-1 inline-flex items-center gap-1 rounded-md bg-primary/8 px-1.5 py-0.5 text-[10px] font-semibold text-primary">
                                        {{ $curLabels['suc_pres'] !== 'President / Agency Head' ? ($orgConfig[$setting->sector ?? 'government']['types'][$curOrgType]['label'] ?? '') : '' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach([
                                ['suc_pres', 'crown'],
                                ['vpaa',     'graduation-cap'],
                                ['vpaf',     'coins'],
                                ['hr',       'user-round'],
                            ] as [$field, $ico])
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                        <i data-lucide="{{ $ico }}" class="mr-1 w-3.5 h-3.5 opacity-50 inline-block align-middle"></i>
                                        <span id="ldr-lbl-{{ $field }}">{{ $curLabels[$field] }}</span>
                                    </label>
                                    <select name="{{ $field }}" data-placeholder="Search employee" class="settings-search-select absolute h-px w-px opacity-0">
                                        <option value="">- Not assigned -</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ (int)($setting->$field) === $emp->id ? 'selected' : '' }}>
                                                {{ $emp->lname }}, {{ $emp->fname }}{{ $emp->position ? ' - '.$emp->position : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Save General Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- â•â• APPEARANCE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="panel-appearance" class="sg-panel hidden space-y-5">
                <form id="form-appearance" method="POST" action="{{ route('settings.saveTheme') }}" class="space-y-5" enctype="multipart/form-data">
                    @csrf

                    {{-- Theme presets --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="swatch-book" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Color Theme</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Choose a preset that defines the primary brand color system-wide.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-5 gap-2.5 sm:grid-cols-10">
                                @foreach($themePresets as $tKey => $preset)
                                    <label class="rounded-2xl border-2 border-border cursor-pointer transition hover:border-primary/40 has-[:checked]:border-primary has-[:checked]:bg-primary/5 group flex cursor-pointer flex-col items-center gap-2 p-3 transition-all
                                                  has-[:checked]:border-primary has-[:checked]:bg-primary/5"
                                           onclick="previewTheme('{{ $tKey }}')">
                                        <input type="radio" name="theme" value="{{ $tKey }}" class="sr-only"
                                               {{ $activeTheme === $tKey ? 'checked' : '' }}>
                                        <span class="h-9 w-9 rounded-xl shadow-md transition-transform group-hover:scale-105 group-has-[:checked]:scale-110 group-has-[:checked]:shadow-lg"
                                              style="background:{{ $preset['hex'] }}; box-shadow:0 3px 10px {{ $preset['hex'] }}55;"></span>
                                        <span class="text-[9px] font-semibold text-muted-foreground group-has-[:checked]:text-primary leading-none">{{ $preset['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-3 flex items-center gap-1.5 text-xs text-muted-foreground">
                                <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                                Changes preview instantly. Click <strong class="text-foreground">Save Appearance</strong> to persist across all pages.
                            </p>
                        </div>
                    </div>

                    {{-- Custom colors --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="pipette" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Custom Brand Colors</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Override the preset theme with exact brand colors. Leave blank to use preset defaults.</p>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            @foreach([
                                ['primary_color', 'Primary Color', '#C9407A', 'Main brand color for buttons, links, and highlights.'],
                                ['accent_color',  'Accent Color',  '#fce7f3', 'Subtle background tint for selected / active states.'],
                            ] as [$field, $lbl, $default, $hint])
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{{ $lbl }}</label>
                                    <div class="flex items-center gap-2.5">
                                        <div class="relative h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-border cursor-pointer">
                                            <input type="color" id="{{ $field }}_picker"
                                                   value="{{ $setting->$field ?: $default }}"
                                                   class="absolute inset-0 h-full w-full opacity-0 z-10 cursor-pointer"
                                                   oninput="syncColor('{{ $field }}', this.value)">
                                            <span id="{{ $field }}_swatch" class="absolute inset-0"
                                                  style="background:{{ $setting->$field ?: $default }}"></span>
                                        </div>
                                        <input type="text" name="{{ $field }}" id="{{ $field }}_text"
                                               value="{{ $setting->$field ?? '' }}"
                                               placeholder="{{ $default }}"
                                               class="block h-10 w-full rounded-xl border border-border bg-background px-3 text-sm text-foreground outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20 disabled:opacity-60 font-mono flex-1"
                                               oninput="syncColor('{{ $field }}', this.value)">
                                    </div>
                                    <p class="mt-1.5 text-xs text-muted-foreground">{{ $hint }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Employee ID cards --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="id-card" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Employee ID Card</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Customize the portrait ID card shown from employee profiles.</p>
                            </div>
                        </div>

                        <div class="grid gap-6 p-6 xl:grid-cols-[minmax(0,1fr)_280px]">
                            <div class="space-y-6">
                                <div>
                                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">ID Design</label>
                                    <div class="grid gap-3 md:grid-cols-3">
                                        @foreach($idCardTemplates as $key => $template)
                                            <label class="id-card-template-card group cursor-pointer rounded-2xl border-2 border-border bg-background p-4 transition hover:border-primary/40">
                                                <input type="radio" name="id_card_template" value="{{ $key }}" class="sr-only" {{ $activeIdTemplate === $key ? 'checked' : '' }}>
                                                <span class="flex items-center gap-3">
                                                    <span class="id-card-template-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-muted-foreground transition">
                                                        <i data-lucide="{{ $template['icon'] }}" class="h-4 w-4"></i>
                                                    </span>
                                                    <span class="min-w-0">
                                                        <span class="block text-sm font-semibold text-foreground">{{ $template['label'] }}</span>
                                                        <span class="mt-0.5 block text-xs leading-snug text-muted-foreground">{{ $template['desc'] }}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    @foreach([
                                        ['id_card_primary_color', 'ID Primary Color', $idPrimary, 'Header, QR badge, and main accents.'],
                                        ['id_card_accent_color',  'ID Accent Color',  $idAccent,  'Soft panels and supporting backgrounds.'],
                                    ] as [$field, $lbl, $default, $hint])
                                        <div>
                                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{{ $lbl }}</label>
                                            <div class="flex items-center gap-2.5">
                                                <div class="relative h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-border cursor-pointer">
                                                    <input type="color" id="{{ $field }}_picker"
                                                           value="{{ $setting->$field ?: $default }}"
                                                           class="absolute inset-0 h-full w-full opacity-0 z-10 cursor-pointer"
                                                           oninput="syncIdCardColor('{{ $field }}', this.value)">
                                                    <span id="{{ $field }}_swatch" class="absolute inset-0"
                                                          style="background:{{ $setting->$field ?: $default }}"></span>
                                                </div>
                                                <input type="text" name="{{ $field }}" id="{{ $field }}_text"
                                                       value="{{ $setting->$field ?? '' }}"
                                                       placeholder="{{ $default }}"
                                                       class="block h-10 w-full rounded-xl border border-border bg-background px-3 text-sm text-foreground outline-none transition focus:border-primary/50 focus:ring-2 focus:ring-primary/20 font-mono flex-1"
                                                       oninput="syncIdCardColor('{{ $field }}', this.value)">
                                            </div>
                                            <p class="mt-1.5 text-xs text-muted-foreground">{{ $hint }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">ID Logo</label>
                                    <div class="flex items-center gap-3 rounded-2xl border border-border bg-background p-3">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-muted">
                                            @if($idLogoUrl)
                                                <img id="id-card-logo-preview" src="{{ $idLogoUrl }}" alt="ID logo" class="h-full w-full object-contain p-1.5">
                                            @else
                                                <span id="id-card-logo-preview" class="text-lg font-bold text-primary">{{ strtoupper(substr($setting->system_name ?: 'H', 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <input type="file" name="id_card_logo" id="id_card_logo" accept="image/*"
                                               class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-xl file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-foreground hover:file:bg-primary/90"
                                               onchange="previewIdCardLogo(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-border bg-background p-4">
                                <div class="mx-auto aspect-[2.125/3.375] w-full max-w-[220px] overflow-hidden rounded-[18px] border border-border shadow-lg" style="background:linear-gradient(180deg, {{ $idAccent }} 0%, #ffffff 42%, #ffffff 100%);">
                                    <div class="h-16 px-4 py-3 text-white" style="background:{{ $idPrimary }};">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-xs font-bold" style="color:{{ $idPrimary }};">
                                                @if($idLogoUrl)
                                                    <img src="{{ $idLogoUrl }}" alt="" class="h-full w-full object-contain p-1">
                                                @else
                                                    {{ strtoupper(substr($setting->system_name ?: 'H', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-[10px] font-bold leading-tight">{{ $setting->system_name ?: 'EAJ HRMS' }}</p>
                                                <p class="truncate text-[8px] opacity-80">{{ $setting->org_name ?: 'Employee Identification' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="-mt-5 flex flex-col items-center px-4 text-center">
                                        <div class="h-20 w-20 rounded-2xl border-4 border-white bg-muted shadow"></div>
                                        <p class="mt-3 text-[13px] font-black text-foreground">Complete Name</p>
                                        <p class="mt-1 text-[9px] font-semibold uppercase tracking-wider text-muted-foreground">Position</p>
                                        <div class="mt-4 grid w-full grid-cols-[1fr_54px] gap-2">
                                            <div class="rounded-xl p-2 text-left" style="background:{{ $idAccent }};">
                                                <p class="text-[7px] font-bold uppercase text-muted-foreground">Employee ID</p>
                                                <p class="mt-0.5 text-[11px] font-black text-foreground">EMP-0000</p>
                                            </div>
                                            <div class="rounded-xl bg-white p-1.5 shadow-sm"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Document headers --}}
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <i data-lucide="image-up" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Document Headers</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Upload the header images used in generated DTR and leave application PDFs.</p>
                            </div>
                        </div>

                        <div class="grid gap-6 p-6 lg:grid-cols-2">
                            <div class="rounded-2xl border border-border bg-background p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-foreground">DTR Header</p>
                                        <p class="mt-0.5 text-xs text-muted-foreground">Used in Daily Time Record PDFs.</p>
                                    </div>
                                    <i data-lucide="calendar-clock" class="h-4 w-4 text-muted-foreground"></i>
                                </div>
                                <div class="mb-3 overflow-hidden rounded-xl border border-border bg-white">
                                    <img id="dtr-header-preview" src="{{ $dtrHeaderUrl }}" alt="DTR header preview" class="h-24 w-full object-contain">
                                </div>
                                <input type="file" name="dtr_header" id="dtr_header" accept="image/*"
                                       class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-xl file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-foreground hover:file:bg-primary/90"
                                       onchange="previewDocumentHeader(this, 'dtr-header-preview')">
                            </div>

                            <div class="rounded-2xl border border-border bg-background p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-foreground">Leave Form Header</p>
                                        <p class="mt-0.5 text-xs text-muted-foreground">Used in generated leave application forms.</p>
                                    </div>
                                    <i data-lucide="file-text" class="h-4 w-4 text-muted-foreground"></i>
                                </div>
                                <div class="mb-3 overflow-hidden rounded-xl border border-border bg-white">
                                    <img id="leave-form-header-preview" src="{{ $leaveFormHeaderUrl }}" alt="Leave form header preview" class="h-24 w-full object-contain">
                                </div>
                                <input type="file" name="leave_form_header" id="leave_form_header" accept="image/*"
                                       class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-xl file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-foreground hover:file:bg-primary/90"
                                       onchange="previewDocumentHeader(this, 'leave-form-header-preview')">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0">
                            <i data-lucide="check" class="w-4 h-4"></i> Save Appearance
                        </button>
                    </div>
                </form>
            </div>

            {{-- â•â• MENUS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="panel-menus" class="sg-panel hidden">
                <form id="form-menus" method="POST" action="{{ route('settings.saveMenu') }}">
                    @csrf
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                        <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <i data-lucide="layers" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-foreground">Sidebar Menu Visibility</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Hidden menus are removed from all user sidebars globally.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" onclick="toggleAllMenus(true)" class="inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Show all
                                </button>
                                <button type="button" onclick="toggleAllMenus(false)" class="inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                                    <i data-lucide="eye-off" class="w-3.5 h-3.5"></i> Hide all
                                </button>
                            </div>
                        </div>

                        <div class="divide-y divide-border/40">
                            @foreach($menuSettings as $group => $items)
                                <div class="px-6 py-5">
                                    <div class="mb-4 flex items-center gap-3">
                                        <span class="h-px flex-1 bg-border/50"></span>
                                        <span class="text-[10px] font-bold uppercase tracking-[.1em] text-muted-foreground/60">{{ $group }}</span>
                                        <span class="h-px flex-1 bg-border/50"></span>
                                    </div>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($items as $item)
                                            <label class="flex cursor-pointer items-center justify-between rounded-xl border border-border/60 bg-background px-4 py-3 transition-all hover:border-primary/30 has-[:checked]:border-primary/40 has-[:checked]:bg-primary/5">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-muted-foreground/25 transition-colors [label:has(:checked)>&]:bg-primary"></span>
                                                    <span class="text-sm font-medium text-foreground truncate">{{ $item->label }}</span>
                                                </div>
                                                <div class="inline-flex h-6 w-11 cursor-pointer items-center rounded-full p-0.5 transition {{ $item->is_visible ? 'bg-primary' : 'bg-muted' }} ml-3 shrink-0"
                                                     onclick="toggleSw(this,'mcb-{{ $item->menu_key }}');event.preventDefault()">
                                                    <div class="h-5 w-5 rounded-full bg-white shadow transition-transform {{ $item->is_visible ? 'translate-x-5' : '' }}"></div>
                                                </div>
                                                <input type="checkbox" name="visible[]" value="{{ $item->menu_key }}"
                                                       id="mcb-{{ $item->menu_key }}"
                                                       class="menu-toggle sr-only" {{ $item->is_visible ? 'checked' : '' }}>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end border-t border-border/50 px-6 py-4">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0">
                                <i data-lucide="check" class="w-4 h-4"></i> Save Menu Visibility
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- â•â• PERMISSIONS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="panel-permissions" class="sg-panel hidden space-y-4">
                <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                    <div class="border-b border-border/60 bg-muted/20 px-6 py-5 flex items-center gap-3.5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <i data-lucide="shield-half" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">User Access Control</p>
                            <p class="text-xs text-muted-foreground mt-0.5">Grant or restrict module access per user. Administrators always have full access.</p>
                        </div>
                    </div>
                </div>

                @forelse($users as $user)
                    @php
                        $userPerms = $user->menuPermission?->menu_keys ?? [];
                        $isAdmin   = $user->role === 'Administrator';
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-muted/20 border-b border-border/50">
                            <div class="flex items-center gap-3.5">
                                <x-avatar :profile="null"
                                          :fname="$user->fname ?? ''"
                                          :lname="$user->lname ?? ''"
                                          class="h-10 w-10 rounded-full text-xs font-bold shrink-0" />
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-bold text-foreground">{{ $user->fname }} {{ $user->lname }}</p>
                                        @if($isAdmin)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">
                                                <i data-lucide="crown" class="w-3 h-3"></i> Admin
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full border border-border bg-card px-2.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                                                {{ $user->role }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $user->email }}</p>
                                </div>
                            </div>
                            @if(!$isAdmin)
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="grantAll({{ $user->id }})" class="inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-500"></i> Grant All
                                    </button>
                                    <button type="button" onclick="revokeAll({{ $user->id }})" class="inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                                        <i data-lucide="ban" class="w-3.5 h-3.5 text-destructive"></i> Revoke All
                                    </button>
                                </div>
                            @endif
                        </div>

                        @if($isAdmin)
                            <div class="flex items-center gap-3 px-5 py-4 text-xs text-muted-foreground">
                                <i data-lucide="infinity" class="w-4 h-4 text-primary shrink-0"></i>
                                Administrators have unrestricted access to all modules and settings.
                            </div>
                        @else
                            <form method="POST" action="{{ route('settings.savePermissions') }}" class="perm-form" data-user="{{ $user->id }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="px-5 py-5">
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                        @foreach($menuSettings->flatten() as $item)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-3 transition-all hover:border-primary/30 has-[:checked]:border-primary/40 has-[:checked]:bg-primary/5">
                                                <div class="relative flex h-4 w-4 shrink-0">
                                                    <input type="checkbox" name="menu_keys[]"
                                                           value="{{ $item->menu_key }}"
                                                           class="peer absolute inset-0 h-full w-full opacity-0 cursor-pointer perm-check-{{ $user->id }}"
                                                           {{ in_array($item->menu_key, $userPerms) ? 'checked' : '' }}>
                                                    <span class="flex h-4 w-4 items-center justify-center rounded-md border-2 border-border bg-background transition peer-checked:border-primary peer-checked:bg-primary flex h-4 w-4 items-center justify-center rounded-md border-2 border-border bg-background peer-checked:border-primary peer-checked:bg-primary">
                                                        <i data-lucide="check" class="ci-tick w-2.5 h-2.5 text-white"></i>
                                                    </span>
                                                </div>
                                                <span class="text-sm font-medium text-foreground leading-tight min-w-0 truncate">{{ $item->label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex justify-end border-t border-border/50 px-5 py-4">
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0">
                                        <i data-lucide="check" class="w-4 h-4"></i> Save Permissions
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm flex flex-col items-center gap-4 py-16 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted">
                            <i data-lucide="users" class="w-8 h-8 text-muted-foreground/30"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">No users found</p>
                            <p class="mt-1 text-xs text-muted-foreground">Create user accounts in User Management first.</p>
                        </div>
                        <a href="{{ route('ulist') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0 mt-1">
                            <i data-lucide="plus" class="w-4 h-4"></i> Go to User Management
                        </a>
                    </div>
                @endforelse
            </div>

        </div>{{-- /content --}}
    </div>{{-- /layout --}}
</div>
<div id="ajax-toast-container" class="fixed bottom-6 right-6 z-50 flex max-w-[calc(100vw-2rem)] flex-col gap-3"></div>
@endsection

@push('scripts')
<script>
// ========== CONFIGURATION ==========
const orgConfig        = @json($orgConfig);
const sectorMeta       = @json($sectorMeta);
const leadershipLabels = @json($leadershipLabels);
const themePresetHex   = @json(collect($themePresets)->mapWithKeys(fn($preset, $key) => [$key => $preset['hex']]));

// ========== UTILITIES ==========
function showToast(message, type = 'success') {
    if (window.Toast?.show) {
        window.Toast.show({
            title: type === 'success' ? 'Saved successfully' : 'Error',
            description: message,
            variant: type,
            duration: 5000,
            position: 'bottom-right',
        });
        return;
    }

    const container = document.getElementById('ajax-toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-main">
            <div class="toast-icon">
                <i data-lucide="${type === 'success' ? 'check' : 'alert-circle'}" class="w-4 h-4"></i>
            </div>
            <div class="toast-content">
                <p class="toast-title">${type === 'success' ? 'Saved successfully' : 'Error'}</p>
                <p class="toast-description">${message}</p>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()" aria-label="Close">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        <div class="toast-progress-bar">
            <div class="progress-bar-fill" style="animation: progressShrink 5000ms linear forwards"></div>
        </div>
    `;
    container.appendChild(toast);
    if (typeof lucide !== 'undefined') lucide.createIcons();
    setTimeout(() => {
        toast.style.transform = 'translateX(12px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function ajaxSubmit(form, url, method = 'POST') {
    const formData = new FormData(form);
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const errors = data.errors ? Object.values(data.errors).flat().join(' ') : '';
            throw new Error(errors || data.message || 'Unable to save changes.');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            if (data.appearance) {
                applyAppearance(data.appearance);
            }
            if (data.system_name) {
                applySystemName(data.system_name);
            }
            if (data.employee_id_prefix) {
                updateEmployeeIdPreview(data.employee_id_prefix);
            }
            showToast(data.message || 'Saved successfully');
        } else {
            showToast(data.message || 'An error occurred', 'error');
        }
    })
    .catch(err => showToast('Network error: ' + err.message, 'error'));
}

function applySystemName(systemName) {
    const name = String(systemName || '').trim();
    if (!name) return;

    document.querySelectorAll('[data-system-name]').forEach((el) => {
        el.textContent = name;
    });

    const pageTitle = @json(trim($__env->yieldContent('pageTitle')));
    document.title = pageTitle ? `${name} - ${pageTitle}` : name;
}

function updateEmployeeIdPreview(prefix) {
    const cleanPrefix = String(prefix || 'EMP').replace(/[^A-Za-z0-9]/g, '').toUpperCase() || 'EMP';
    document.getElementById('employee-id-prefix-preview')?.replaceChildren(document.createTextNode(cleanPrefix));
}

function previewDocumentHeader(input, previewId) {
    const file = input.files?.[0];
    const preview = document.getElementById(previewId);
    if (!file || !preview) return;

    preview.src = URL.createObjectURL(file);
}

// ========== TAB SWITCHING ==========
function activateTab(tab) {
    document.querySelectorAll('.sg-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.sg-tab, .sg-mob-tab').forEach(btn => {
        const on = btn.dataset.tab === tab;
        btn.classList?.toggle('active', on);
        btn.classList.toggle('bg-primary/10', on);
        btn.classList.toggle('text-primary', on);
        btn.classList.toggle('border-primary', on);
    });
    const panel = document.getElementById('panel-' + tab);
    if (panel) panel.classList.remove('hidden');
    try { localStorage.setItem('hrms_settings_tab', tab); } catch(e) {}
}
document.querySelectorAll('.sg-tab, .sg-mob-tab').forEach(btn =>
    btn.addEventListener('click', () => activateTab(btn.dataset.tab))
);
(function() {
    let saved = '';
    try { saved = localStorage.getItem('hrms_settings_tab') || ''; } catch(e) {}
    const hashTab = (window.location.hash || '').replace('#', '');
    activateTab(hashTab || saved || 'org');
    updateOrgTypeCards();
    updateEmpTypeChips();
    updateIdCardTemplateCards();
})();

// ========== TOGGLE SWITCH ==========
function toggleSw(el, cbId) {
    const thumb = el.querySelector('div');
    const on = !el.classList.contains('bg-primary');
    el.classList.toggle('bg-primary', on);
    el.classList.toggle('bg-muted', !on);
    thumb?.classList.toggle('translate-x-5', on);
    const cb = document.getElementById(cbId);
    if (cb) cb.checked = on;
}

function updateLeadershipLabels(orgType) {
    const labels = leadershipLabels[orgType] || leadershipLabels['_default'];
    ['suc_pres','vpaa','vpaf','hr'].forEach(f => {
        const el = document.getElementById('ldr-lbl-' + f);
        if (el) el.textContent = labels[f] || '';
    });
    const hint = document.getElementById('leadership-org-hint');
    const sector = document.getElementById('inp-sector')?.value || '';
    if (hint) {
        const typeName = orgConfig[sector]?.types?.[orgType]?.label || '';
        hint.textContent = typeName;
        hint.style.display = typeName ? '' : 'none';
    }
}
document.querySelectorAll('input[name="org_type"]').forEach(radio =>
    radio.addEventListener('change', () => {
        updateOrgTypeCards();
        updateLeadershipLabels(radio.value);
    })
);

function updateOrgTypeCards() {
    document.querySelectorAll('input[name="org_type"]').forEach(radio => {
        const label = radio.closest('label');
        if (!label) return;
        label.classList.toggle('border-primary', !!(radio && radio.checked));
        label.classList.toggle('bg-primary/5', !!(radio && radio.checked));
        const marker = label.querySelector('.rounded-full .rounded-full');
        if (marker) {
            marker.classList.toggle('scale-100', !!(radio && radio.checked));
            marker.classList.toggle('scale-0', !(radio && radio.checked));
        }
    });
}

function updateEmpTypeChips() {
    document.querySelectorAll('input[name="emp_types[]"]').forEach(cb => {
        const chip = cb.closest('.emp-chip')?.querySelector('.chip-bd');
        const dot = cb.closest('.emp-chip')?.querySelector('.chip-dot');
        const icon = cb.closest('.emp-chip')?.querySelector('.chip-icon');
        if (!chip) return;
        chip.classList.toggle('border-primary', cb.checked);
        chip.classList.toggle('bg-primary/5', cb.checked);
        dot?.classList.toggle('bg-primary', cb.checked);
        icon?.classList.toggle('text-primary', cb.checked);
    });
}

document.querySelectorAll('input[name="emp_types[]"]').forEach(cb =>
    cb.addEventListener('change', updateEmpTypeChips)
);

function updateIdCardTemplateCards() {
    document.querySelectorAll('input[name="id_card_template"]').forEach((radio) => {
        const card = radio.closest('.id-card-template-card');
        const icon = card?.querySelector('.id-card-template-icon');
        if (!card) return;

        card.classList.toggle('border-primary', radio.checked);
        card.classList.toggle('bg-primary/5', radio.checked);
        icon?.classList.toggle('bg-primary', radio.checked);
        icon?.classList.toggle('text-primary-foreground', radio.checked);
        icon?.classList.toggle('bg-muted', !radio.checked);
        icon?.classList.toggle('text-muted-foreground', !radio.checked);
    });
}

document.querySelectorAll('input[name="id_card_template"]').forEach((radio) => {
    radio.addEventListener('change', updateIdCardTemplateCards);
    radio.closest('.id-card-template-card')?.addEventListener('click', () => {
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

function selectSector(sector) {
    document.getElementById('inp-sector').value = sector;
    document.querySelectorAll('[data-sector]').forEach(card => {
        const on = card.dataset.sector === sector;
        const cardSm = sectorMeta[card.dataset.sector] || { bg: '#f3f4f6', color: '#6b7280' };
        card.classList.toggle('border-primary', on);
        card.classList.toggle('bg-primary/5', on);
        card.querySelector('.sc-check')?.classList.toggle('hidden', !on);
        card.querySelector('.sc-check')?.classList.toggle('flex', on);
        const ico = card.querySelector('.sc-icon');
        if (ico) {
            ico.style.background = on ? 'var(--color-primary)' : cardSm.bg;
            ico.style.color = on ? '#fff' : cardSm.color;
        }
    });
    document.querySelectorAll('.org-types').forEach(el => el.classList.add('hidden'));
    const types = document.getElementById('types-' + sector);
    if (!types) return;
    types.classList.remove('hidden');
    let checkedType = types.querySelector('input[name="org_type"]:checked');
    if (!checkedType) {
        const first = types.querySelector('input[name="org_type"]');
        if (first) { first.checked = true; checkedType = first; }
    }
    updateOrgTypeCards();
    if (checkedType) updateLeadershipLabels(checkedType.value);
}

function applySuggested() {
    const sector = document.getElementById('inp-sector').value;
    const suggested = orgConfig[sector]?.suggested ?? [];
    document.querySelectorAll('input[name="emp_types[]"]').forEach(cb => {
        cb.checked = suggested.includes(cb.value);
    });
    updateEmpTypeChips();
}

function previewTheme(theme) {
    ['primary_color', 'accent_color'].forEach((field) => {
        const text = document.getElementById(field + '_text');
        const swatch = document.getElementById(field + '_swatch');
        if (text) text.value = '';
        if (swatch) swatch.style.background = themePresetHex[theme] || '#C9407A';
    });

    applyAppearance({ theme, primary_color: null, accent_color: null });

    document.querySelectorAll('input[name="theme"]').forEach(input => {
        input.checked = input.value === theme;
    });
}

function syncColor(field, value) {
    const picker = document.getElementById(field + '_picker');
    const text = document.getElementById(field + '_text');
    const swatch = document.getElementById(field + '_swatch');
    const normalized = (value || '').trim();
    const isHex = /^#[0-9a-fA-F]{6}$/.test(normalized);

    if (text && text.value !== value) text.value = value;
    if (picker && isHex) picker.value = normalized;
    if (swatch) {
        swatch.style.background = isHex ? normalized : themePresetHex[window.__currentTheme || 'ea'] || '#C9407A';
    }

    applyAppearance({
        theme: window.__currentTheme || document.documentElement.getAttribute('data-theme') || 'ea',
        primary_color: document.getElementById('primary_color_text')?.value || null,
        accent_color: document.getElementById('accent_color_text')?.value || null,
    });
}

function syncIdCardColor(field, value) {
    const picker = document.getElementById(field + '_picker');
    const text = document.getElementById(field + '_text');
    const swatch = document.getElementById(field + '_swatch');
    const normalized = (value || '').trim();
    const isHex = /^#[0-9a-fA-F]{6}$/.test(normalized);

    if (text && text.value !== value) text.value = value;
    if (picker && isHex) picker.value = normalized;
    if (swatch) swatch.style.background = isHex ? normalized : (field.includes('primary') ? '#C9407A' : '#fce7f3');
}

function previewIdCardLogo(input) {
    const file = input.files?.[0];
    if (!file || !file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const preview = document.getElementById('id-card-logo-preview');
        if (!preview) return;

        if (preview.tagName === 'IMG') {
            preview.src = event.target.result;
            return;
        }

        const image = document.createElement('img');
        image.id = 'id-card-logo-preview';
        image.src = event.target.result;
        image.alt = 'ID logo';
        image.className = 'h-full w-full object-contain p-1.5';
        preview.replaceWith(image);
    };
    reader.readAsDataURL(file);
}

function hexToHsl(hex) {
    const match = /^#?([0-9a-fA-F]{6})$/.exec(hex || '');
    if (!match) return null;

    const raw = match[1];
    let r = parseInt(raw.slice(0, 2), 16) / 255;
    let g = parseInt(raw.slice(2, 4), 16) / 255;
    let b = parseInt(raw.slice(4, 6), 16) / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    const l = (max + min) / 2;

    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
            case g: h = (b - r) / d + 2; break;
            default: h = (r - g) / d + 4;
        }
        h /= 6;
    }

    return `${Math.round(h * 360)} ${Math.round(s * 100)}% ${Math.round(l * 100)}%`;
}

function setThemeVar(name, hsl) {
    const root = document.documentElement;
    if (hsl) root.style.setProperty(name, hsl, 'important');
    else root.style.removeProperty(name);
}

function applyAppearance(appearance) {
    const theme = appearance.theme || 'ea';
    const primaryHsl = hexToHsl(appearance.primary_color);
    const accentHsl = hexToHsl(appearance.accent_color);
    const serverOverrides = document.getElementById('system-theme-overrides');

    if (serverOverrides) serverOverrides.remove();

    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.dataset.serverTheme = theme;
    window.__currentTheme = theme;
    window.applyHrmsTheme?.(theme);

    setThemeVar('--primary', primaryHsl);
    setThemeVar('--ring', primaryHsl);
    setThemeVar('--sidebar-primary', primaryHsl);
    setThemeVar('--accent', accentHsl);
    setThemeVar('--sidebar-accent', accentHsl);

    try { localStorage.setItem('theme', theme); } catch(e) {}
    window.refreshUi?.();
}

function toggleAllMenus(state) {
    document.querySelectorAll('.menu-toggle').forEach(cb => cb.checked = state);
    document.querySelectorAll('.menu-toggle').forEach(cb => {
        const sw = cb.closest('label')?.querySelector('.inline-flex.h-6.w-11');
        const thumb = sw?.querySelector('div');
        sw?.classList.toggle('bg-primary', state);
        sw?.classList.toggle('bg-muted', !state);
        thumb?.classList.toggle('translate-x-5', state);
    });
}

function grantAll(uid)  { document.querySelectorAll('.perm-check-' + uid).forEach(c => c.checked = true); }
function revokeAll(uid) { document.querySelectorAll('.perm-check-' + uid).forEach(c => c.checked = false); }

function initSettingsSearchSelects() {
    document.querySelectorAll('select.settings-search-select').forEach((select) => {
        if (select.dataset.searchReady === 'true') return;

        const wrapper = document.createElement('div');
        wrapper.className = 'settings-search-combobox relative w-full';
        wrapper.innerHTML = `
            <button type="button" class="settings-search-trigger flex h-10 w-full items-center justify-between gap-2 rounded-xl border border-border bg-background px-3 text-left text-sm text-foreground outline-none transition hover:border-primary/50 focus:ring-2 focus:ring-primary/20" aria-haspopup="listbox" aria-expanded="false">
                <span class="settings-search-value min-w-0 flex-1 truncate"></span>
                <i data-lucide="chevrons-up-down" class="h-3.5 w-3.5 shrink-0 opacity-60"></i>
            </button>
            <div class="settings-search-panel absolute left-0 right-0 top-full z-[80] mt-1 hidden overflow-hidden rounded-xl border border-border bg-popover text-popover-foreground shadow-xl">
                <div class="flex items-center gap-2 border-b border-border/60 bg-background px-3 py-2">
                    <i data-lucide="search" class="h-3.5 w-3.5 text-muted-foreground"></i>
                    <input type="text" class="settings-search-input h-8 min-w-0 flex-1 bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" autocomplete="off">
                </div>
                <div class="settings-search-options max-h-64 overflow-y-auto p-1" role="listbox"></div>
            </div>
        `;

        select.insertAdjacentElement('afterend', wrapper);
        select.dataset.searchReady = 'true';
        wrapper.querySelector('.settings-search-trigger').addEventListener('click', () => openSettingsSearchSelect(select));
        wrapper.querySelector('.settings-search-input').addEventListener('input', () => renderSettingsSearchOptions(select));
        select.addEventListener('change', () => syncSettingsSearchSelect(select));
        syncSettingsSearchSelect(select);
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function settingsSearchWrapper(select) {
    return select.nextElementSibling?.classList.contains('settings-search-combobox') ? select.nextElementSibling : null;
}

function syncSettingsSearchSelect(select) {
    const wrapper = settingsSearchWrapper(select);
    if (!wrapper) return;

    const selected = select.options[select.selectedIndex];
    const hasValue = !!selected?.value;
    const value = wrapper.querySelector('.settings-search-value');
    value.textContent = hasValue ? selected.text.trim() : (select.dataset.placeholder || 'Search and select');
    value.classList.toggle('text-muted-foreground', !hasValue);
}

function openSettingsSearchSelect(select) {
    document.querySelectorAll('.settings-search-panel').forEach((panel) => panel.classList.add('hidden'));
    document.querySelectorAll('.settings-search-trigger').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));

    const wrapper = settingsSearchWrapper(select);
    if (!wrapper) return;

    const input = wrapper.querySelector('.settings-search-input');
    input.placeholder = select.dataset.placeholder || 'Search and select';
    input.value = '';
    wrapper.querySelector('.settings-search-panel').classList.remove('hidden');
    wrapper.querySelector('.settings-search-trigger').setAttribute('aria-expanded', 'true');
    renderSettingsSearchOptions(select);
    requestAnimationFrame(() => input.focus());
}

function renderSettingsSearchOptions(select) {
    const wrapper = settingsSearchWrapper(select);
    if (!wrapper) return;

    const query = wrapper.querySelector('.settings-search-input').value.trim().toLowerCase();
    const optionsBox = wrapper.querySelector('.settings-search-options');
    const options = Array.from(select.options).filter((option) => option.text.toLowerCase().includes(query));

    optionsBox.innerHTML = options.length
        ? options.map((option) => `
            <button type="button" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-accent hover:text-accent-foreground ${option.value === select.value ? 'bg-primary/10 font-semibold text-primary' : 'text-foreground'}" data-value="${option.value}" role="option">
                <span class="min-w-0 flex-1 truncate">${escapeSettingsSearchText(option.text.trim())}</span>
                ${option.value === select.value ? '<i data-lucide="check" class="h-3.5 w-3.5 shrink-0"></i>' : ''}
            </button>
        `).join('')
        : '<div class="px-3 py-6 text-center text-sm text-muted-foreground">No employees found</div>';

    optionsBox.querySelectorAll('button[data-value]').forEach((button) => {
        button.addEventListener('click', () => {
            select.value = button.dataset.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            settingsSearchWrapper(select)?.querySelector('.settings-search-panel')?.classList.add('hidden');
        });
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function escapeSettingsSearchText(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

initSettingsSearchSelects();
document.addEventListener('click', (event) => {
    if (!event.target.closest('.settings-search-combobox')) {
        document.querySelectorAll('.settings-search-panel').forEach((panel) => panel.classList.add('hidden'));
        document.querySelectorAll('.settings-search-trigger').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
    }
});

// ========== FORM SUBMISSIONS (AJAX) - using your exact route names ==========
document.getElementById('form-org')?.addEventListener('submit', (e) => {
    e.preventDefault();
    ajaxSubmit(e.target, '{{ route("settings.saveOrg") }}');
});
document.getElementById('form-general')?.addEventListener('submit', (e) => {
    e.preventDefault();
    ajaxSubmit(e.target, '{{ route("settings.saveGeneral") }}');
});
document.getElementById('form-appearance')?.addEventListener('submit', (e) => {
    e.preventDefault();
    ajaxSubmit(e.target, '{{ route("settings.saveTheme") }}');
});
document.getElementById('form-menus')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const visible = formData.getAll('visible[]');
    fetch('{{ route("settings.saveMenu") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ visible: visible }),
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Unable to save menu visibility.');
        return data;
    })
    .then(data => showToast(data.message || 'Menu visibility saved.'))
    .catch(err => showToast('Error: ' + err.message, 'error'));
});
document.querySelectorAll('.perm-form').forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const userId = form.dataset.user;
        const formData = new FormData(form);
        const menuKeys = formData.getAll('menu_keys[]');
        fetch('{{ route("settings.savePermissions") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId, menu_keys: menuKeys }),
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Unable to save permissions.');
            return data;
        })
        .then(data => showToast(data.message || 'Permissions saved.'))
        .catch(err => showToast('Error: ' + err.message, 'error'));
    });
});

const observer = new MutationObserver(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
const toastContainer = document.getElementById('ajax-toast-container');
if (toastContainer) {
    observer.observe(toastContainer, { childList: true, subtree: true });
}
</script>
@endpush
