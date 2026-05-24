@php
    // Profile image resolution - support both old public path and new Storage disk
    // Service duration
    $hireDate    = $employee->date_hired;
    $svcYears    = 0; $svcMonths = 0;
    if ($hireDate) {
        try {
            $interval  = (new DateTime($hireDate))->diff(new DateTime);
            $svcYears  = $interval->y;
            $svcMonths = $interval->m;
        } catch (\Exception $e) {}
    }

    // Employee ID card settings
    $settings = \App\Models\Setting::singleton();
    $idTemplate = in_array($settings->id_card_template, ['classic', 'bold', 'minimal'], true) ? $settings->id_card_template : 'classic';
    $idPrimary = $settings->id_card_primary_color ?: ($settings->primary_color ?: '#C9407A');
    $idAccent = $settings->id_card_accent_color ?: ($settings->accent_color ?: '#fce7f3');
    $idLogoUrl = $settings->id_card_logo && file_exists(public_path('Uploads/Settings/' . $settings->id_card_logo))
        ? asset('Uploads/Settings/' . $settings->id_card_logo)
        : null;
    $idOrgName = $settings->org_name ?: 'Employee Identification';
    $idSystemName = $settings->system_name ?: 'EAJ HRMS';

    // Encrypted emp ID for QR
    $shortEncrypted = shortEncrypt($employee->emp_ID);

    $encryptedEmployeeId = shortEncrypt((string) $employee->id);
    $displayName = trim(collect([
        $employee->fname ? ucwords(strtolower($employee->fname)) : null,
        $employee->mname ? ucwords(strtolower(substr($employee->mname, 0, 1))) . '.' : null,
        $employee->lname ? ucwords(strtolower($employee->lname)) : null,
        $employee->suffix ?: null,
    ])->filter()->implode(' '));
    $profileFile = trim((string) $employee->profile);
    $placeholderFiles = ['default.png', 'default-male.png', 'default-female.png'];
    $hasProfileImage = $profileFile
        && !in_array(strtolower($profileFile), $placeholderFiles, true)
        && file_exists(public_path('Profile/Employee/' . $profileFile));
    $profileUrl = $hasProfileImage ? asset('Profile/Employee/' . $profileFile) : null;
    $i1 = strtoupper(substr($employee->fname ?? '', 0, 1));
    $i2 = strtoupper(substr($employee->lname ?? '', 0, 1));
    $profileInitials = ($i1 . $i2) ?: '?';
    $profilePalette = ['#C9407A','#7C3AED','#2563EB','#059669','#D97706','#DC2626','#0891B2','#0D9488'];
    $profileInitialColor = $i1 ? $profilePalette[ord($i1) % count($profilePalette)] : '#C9407A';

    // PDS nav items: [route_name, route_param_key, icon, label, request_path, completion_key]
    $pdsNav = [
        ['PDS',           $encryptedEmployeeId, 'user-round',          'Personal Information',        ['pds/personal-info/*','pds'], 'personal',  true],
        ['familybg',      $encryptedEmployeeId, 'users-round',         'Family Background',           ['pds/family-bg','pds/family-bg/*'], 'colfamstat',  false],
        ['educbg',        $encryptedEmployeeId, 'graduation-cap',      'Educational Background',      ['pds/educ-bg','pds/educ-bg/*'], 'coleducstat', false],
        ['eligibility',   $encryptedEmployeeId, 'badge-check',         'Eligibility',                 ['pds/eligibility','pds/eligibility/*'], 'eligibility', null],
        ['work-experience',$encryptedEmployeeId,'briefcase-business',  'Work Experience',             ['pds/work-experience','pds/work-experience/*'], 'workexperience', null],
        ['voluntary-work',$encryptedEmployeeId, 'heart-handshake',     'Voluntary Work',              ['pds/voluntary-work','pds/voluntary-work/*'], 'voluntaryworks', null],
        ['learning-dev',  $encryptedEmployeeId, 'book-open-check',     'Learning & Development',      ['pds/learning-dev','pds/learning-dev/*'], 'learningdev', null],
        ['otherInfo',     $encryptedEmployeeId, 'info',                'Other Information',           ['pds/other-info','pds/other-info/*'], 'colotherinfo', false],
        ['infoQuestion',  $encryptedEmployeeId, 'circle-help',         'Other Info Questions',        ['pds/info-question','pds/info-question/*'], 'colinfoquestion', false],
        ['references',    $encryptedEmployeeId, 'contact-round',       'References',                  ['pds/references','pds/references/*'], 'colreferences', false],
        ['govids',        $encryptedEmployeeId, 'id-card',             'Government Issued ID',        ['pds/government-id','pds/government-id/*'], 'colgovids', false],
        ['signature',     $encryptedEmployeeId, 'signature',           'E-Signature',                 ['pds/signature','pds/signature/*'], null, false],
    ];
@endphp

<div class="flex flex-col gap-4 lg:w-64 xl:w-72 shrink-0">

    {{-- Profile Card --}}
    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">

        <div class="relative h-24 bg-primary/10">
            <div class="absolute inset-x-0 bottom-0 h-px bg-border/50"></div>
            <button type="button" onclick="openEmployeeIdModal()" title="Show employee ID"
                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 bg-card/90 text-primary shadow-sm backdrop-blur transition hover:bg-muted">
                <i data-lucide="id-card" class="h-4 w-4"></i>
            </button>
        </div>

        <div class="-mt-12 px-4 pb-4 text-center">
            <div id="profilePictureTrigger" class="relative mx-auto mb-3 h-24 w-24 cursor-pointer" title="Click to change photo">
                <x-avatar :profile="$employee->profile"
                          :fname="$employee->fname"
                          :lname="$employee->lname"
                          id="changeProfilePicture"
                          class="h-24 w-24 rounded-full object-cover text-2xl ring-4 ring-card shadow-lg cursor-pointer transition hover:opacity-90"
                          title="Click to change photo" />
                <input type="file" id="profilePictureInput" class="hidden" accept="image/*">
                <div class="absolute bottom-1 right-1 flex h-7 w-7 items-center justify-center rounded-full bg-primary text-primary-foreground shadow ring-2 ring-card">
                    <i data-lucide="camera" class="h-3.5 w-3.5"></i>
                </div>
            </div>

            <h3 class="mx-auto max-w-[14rem] text-base font-bold leading-tight text-foreground">
                {{ $displayName ?: 'Employee Profile' }}
            </h3>
            <p class="mx-auto mt-1 max-w-[14rem] text-xs leading-snug text-muted-foreground">
                {{ $employee->position ?: 'No position set' }}
            </p>

            <div class="mt-3 flex justify-center">
                @if($employee->stat_1 == 1)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-800/50">
                        <i data-lucide="check-circle-2" class="h-3 w-3"></i> Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-[11px] font-semibold text-red-700 ring-1 ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-800/50">
                        <i data-lucide="circle-slash" class="h-3 w-3"></i> Suspended
                    </span>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl border border-border/60 bg-muted/30 px-2 py-2">
                    <i data-lucide="badge" class="mx-auto h-3.5 w-3.5 text-primary"></i>
                    <p class="mt-1 text-[9px] font-medium uppercase text-muted-foreground">Emp ID</p>
                    <p class="mt-0.5 truncate font-mono text-[11px] font-bold text-foreground">{{ $employee->emp_ID }}</p>
                </div>
                <div class="rounded-xl border border-border/60 bg-muted/30 px-2 py-2">
                    <i data-lucide="calendar-days" class="mx-auto h-3.5 w-3.5 text-primary"></i>
                    <p class="mt-1 text-[9px] font-medium uppercase text-muted-foreground">Service</p>
                    <p class="mt-0.5 text-[11px] font-bold text-foreground">{{ $svcYears }}y {{ $svcMonths }}m</p>
                </div>
                <div class="rounded-xl border border-border/60 bg-muted/30 px-2 py-2">
                    <i data-lucide="hash" class="mx-auto h-3.5 w-3.5 text-primary"></i>
                    <p class="mt-1 text-[9px] font-medium uppercase text-muted-foreground">Item No</p>
                    <p class="mt-0.5 truncate text-[11px] font-bold text-foreground">{{ $employee->item_no ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- PDS Navigation --}}
    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
        <div class="flex items-center gap-2 border-b border-border/60 px-4 py-3">
            <i data-lucide="id-card" class="h-3.5 w-3.5 text-primary/70"></i>
            <span class="text-[11px] font-bold uppercase tracking-wider text-foreground">Personal Data Sheet</span>
        </div>

        <nav class="p-1.5 space-y-0.5">
            <div class="mb-1.5 rounded-xl border border-primary/15 bg-primary/5 px-3 py-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <i data-lucide="fingerprint" class="h-3.5 w-3.5"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-primary">Personal Identity</p>
                        <p class="truncate text-[10px] text-muted-foreground">Employee profile and PDS records</p>
                    </div>
                </div>
            </div>

            @foreach($pdsNav as [$routeName, $routeParam, $icon, $label, $paths, $completionKey, $isSimpleFlag])
            @php
                $isActive = collect($paths)->contains(fn($p) => request()->is($p));

                // Compute completion
                $isDone = false;
                if ($completionKey === 'personal') {
                    $isDone = true;
                } elseif ($completionKey !== null && isset($columnstatus)) {
                    $completionValue = $columnstatus[$completionKey] ?? null;
                    if ($completionValue instanceof \Illuminate\Support\Collection || is_array($completionValue)) {
                        $isDone = count($completionValue) > 0;
                    } else {
                        $isDone = (int) ($completionValue ?? 0) === 1;
                    }
                }

                $href = ($guard == 'web')
                    ? route($routeName, $routeParam)
                    : route($routeName);
            @endphp
            <a href="{{ $href }}"
               class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs transition-colors
                      {{ $isActive
                            ? 'bg-primary/10 text-primary font-semibold'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}">
                <i data-lucide="{{ $icon }}" class="h-3.5 w-3.5 shrink-0
                           {{ $isActive ? 'text-primary' : 'text-muted-foreground/60 group-hover:text-foreground' }}"></i>
                <span class="flex-1 leading-snug">{{ $label }}</span>
                @if($completionKey !== null || $completionKey === 'personal')
                    @if($isDone)
                        <i data-lucide="check-circle-2" class="h-3 w-3 shrink-0 text-emerald-500"></i>
                    @else
                        <i data-lucide="circle" class="h-2.5 w-2.5 shrink-0 text-muted-foreground/25"></i>
                    @endif
                @endif
            </a>
            @endforeach

            <div class="my-2 rounded-xl border border-border/60 bg-muted/30 px-3 py-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-background text-primary ring-1 ring-border/70">
                        <i data-lucide="folder-open" class="h-3.5 w-3.5"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-foreground">Other Menu</p>
                        <p class="truncate text-[10px] text-muted-foreground">Preview and printable files</p>
                    </div>
                </div>
            </div>

            {{-- Preview / Print links --}}
            <a href="{{ ($guard == 'web') ? route('generatepds', $encryptedEmployeeId) : route('generatepds') }}" target="_blank"
               class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                <i data-lucide="eye" class="h-3.5 w-3.5 shrink-0 text-muted-foreground/60 group-hover:text-foreground"></i>
                <span class="flex-1">Preview PDS</span>
                <i data-lucide="external-link" class="h-3 w-3 opacity-40"></i>
            </a>
            <a href="{{ ($guard == 'web') ? route('genpdsAtthachment', $encryptedEmployeeId) : route('genpdsAtthachment') }}" target="_blank"
               class="group flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                <i data-lucide="paperclip" class="h-3.5 w-3.5 shrink-0 text-muted-foreground/60 group-hover:text-foreground"></i>
                <span class="flex-1">CS Form Attachment</span>
                <i data-lucide="external-link" class="h-3 w-3 opacity-40"></i>
            </a>
        </nav>
    </div>
</div>

{{-- Employee ID Card Modal --}}
<div id="employee-id-modal-backdrop"
     class="fixed inset-0 z-[60] hidden items-center justify-center p-4"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEmployeeIdModal()"></div>

    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-border/60 bg-card shadow-2xl">
        <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="id-card" class="h-5 w-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Employee ID Card</h3>
                    <p class="text-xs text-muted-foreground">Design: {{ ucfirst($idTemplate) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" id="employee-id-download-btn"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition hover:bg-primary/90"
                    title="Download">
                    <i data-lucide="download" class="h-3.5 w-3.5"></i>
                    Download
                </button>
                <button type="button" onclick="closeEmployeeIdModal()"
                    class="rounded-xl p-2 text-muted-foreground transition hover:bg-muted">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
        </div>

        <div class="p-5">
            <div class="flex justify-center overflow-x-auto rounded-2xl bg-muted/30 p-4">
                <div id="employee-id-card"
                     class="relative aspect-[2.125/3.375] w-[340px] shrink-0 overflow-hidden rounded-[24px] border border-black/10 bg-white text-slate-950 shadow-2xl"
                     style="--id-primary: {{ $idPrimary }}; --id-accent: {{ $idAccent }};">
                    @if($idTemplate === 'bold')
                        <div class="absolute inset-0" style="background:linear-gradient(155deg, var(--id-primary) 0%, var(--id-primary) 58%, #ffffff 58.2%, #ffffff 100%);"></div>
                        <div class="relative flex h-full flex-col p-5 text-white">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-sm font-black" style="color:var(--id-primary);">
                                    @if($idLogoUrl)
                                        <img src="{{ $idLogoUrl }}" alt="" class="h-full w-full object-contain p-1.5">
                                    @else
                                        {{ strtoupper(substr($idSystemName, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black leading-tight">{{ $idSystemName }}</p>
                                    <p class="truncate text-[10px] font-medium opacity-80">{{ $idOrgName }}</p>
                                </div>
                            </div>
                            <div class="mt-7 flex justify-center">
                                @if($hasProfileImage)
                                    <img src="{{ $profileUrl }}" alt="{{ $displayName }}" class="h-36 w-36 rounded-[28px] border-4 border-white object-cover shadow-xl">
                                @else
                                    <span class="flex h-36 w-36 items-center justify-center rounded-[28px] border-4 border-white text-5xl font-black text-white shadow-xl" style="background-color: {{ $profileInitialColor }};">{{ $profileInitials }}</span>
                                @endif
                            </div>
                            <div class="mt-5 text-center">
                                <p class="text-[22px] font-black uppercase leading-tight tracking-normal">{{ $displayName ?: 'Employee Profile' }}</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-white/75">{{ $employee->position ?: 'No position set' }}</p>
                            </div>
                            <div class="mt-auto grid grid-cols-[1fr_82px] items-end gap-3 text-slate-950">
                                <div class="rounded-2xl bg-white/95 p-3 shadow-lg">
                                    <p class="text-[9px] font-bold uppercase text-slate-500">Employee ID</p>
                                    <p class="font-mono text-lg font-black leading-tight">{{ $employee->emp_ID }}</p>
                                    <p class="mt-1 text-[9px] font-semibold uppercase text-slate-500">Portrait ID</p>
                                </div>
                                <div class="rounded-2xl bg-white p-2 shadow-lg">
                                    <div id="id-card-qrcode" class="flex items-center justify-center"></div>
                                </div>
                            </div>
                        </div>
                    @elseif($idTemplate === 'minimal')
                        <div class="absolute inset-y-0 left-0 w-5" style="background:var(--id-primary);"></div>
                        <div class="relative flex h-full flex-col py-6 pl-10 pr-6">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-black uppercase tracking-wider" style="color:var(--id-primary);">{{ $idSystemName }}</p>
                                    <p class="truncate text-[9px] font-semibold text-slate-500">{{ $idOrgName }}</p>
                                </div>
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-black" style="color:var(--id-primary);">
                                    @if($idLogoUrl)
                                        <img src="{{ $idLogoUrl }}" alt="" class="h-full w-full object-contain p-1.5">
                                    @else
                                        {{ strtoupper(substr($idSystemName, 0, 1)) }}
                                    @endif
                                </div>
                            </div>
                            <div class="mt-8 flex justify-center">
                                @if($hasProfileImage)
                                    <img src="{{ $profileUrl }}" alt="{{ $displayName }}" class="h-40 w-32 rounded-[22px] border border-slate-200 object-cover shadow-lg">
                                @else
                                    <span class="flex h-40 w-32 items-center justify-center rounded-[22px] border border-slate-200 text-5xl font-black text-white shadow-lg" style="background-color: {{ $profileInitialColor }};">{{ $profileInitials }}</span>
                                @endif
                            </div>
                            <div class="mt-6 text-center">
                                <p class="text-[20px] font-black uppercase leading-tight tracking-normal">{{ $displayName ?: 'Employee Profile' }}</p>
                                <p class="mx-auto mt-2 max-w-[230px] text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $employee->position ?: 'No position set' }}</p>
                            </div>
                            <div class="mt-auto grid grid-cols-[1fr_78px] items-end gap-3">
                                <div class="rounded-2xl border border-slate-200 p-3" style="background:var(--id-accent);">
                                    <p class="text-[9px] font-bold uppercase text-slate-500">Employee ID</p>
                                    <p class="font-mono text-lg font-black leading-tight">{{ $employee->emp_ID }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-2">
                                    <div id="id-card-qrcode" class="flex items-center justify-center"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="h-24 px-5 py-4 text-white" style="background:linear-gradient(135deg, var(--id-primary), #111827);">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-sm font-black shadow" style="color:var(--id-primary);">
                                    @if($idLogoUrl)
                                        <img src="{{ $idLogoUrl }}" alt="" class="h-full w-full object-contain p-1.5">
                                    @else
                                        {{ strtoupper(substr($idSystemName, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black leading-tight">{{ $idSystemName }}</p>
                                    <p class="truncate text-[10px] font-medium opacity-80">{{ $idOrgName }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="-mt-8 flex flex-col items-center px-6 pb-6 text-center">
                            @if($hasProfileImage)
                                <img src="{{ $profileUrl }}" alt="{{ $displayName }}" class="h-36 w-36 rounded-[28px] border-4 border-white object-cover shadow-xl">
                            @else
                                <span class="flex h-36 w-36 items-center justify-center rounded-[28px] border-4 border-white text-5xl font-black text-white shadow-xl" style="background-color: {{ $profileInitialColor }};">{{ $profileInitials }}</span>
                            @endif
                            <p class="mt-5 text-[22px] font-black uppercase leading-tight tracking-normal">{{ $displayName ?: 'Employee Profile' }}</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $employee->position ?: 'No position set' }}</p>
                            <div class="mt-6 grid w-full grid-cols-[1fr_86px] gap-3">
                                <div class="rounded-2xl p-3 text-left" style="background:var(--id-accent);">
                                    <p class="text-[9px] font-bold uppercase text-slate-500">Employee ID</p>
                                    <p class="font-mono text-lg font-black leading-tight">{{ $employee->emp_ID }}</p>
                                    <p class="mt-2 text-[9px] font-bold uppercase text-slate-500">Status</p>
                                    <p class="text-xs font-black">{{ $employee->stat_1 == 1 ? 'Active' : 'Suspended' }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                                    <div id="id-card-qrcode" class="flex items-center justify-center"></div>
                                </div>
                            </div>
                            <div class="mt-auto pt-6 text-[9px] font-semibold uppercase tracking-wider text-slate-400">Official Employee Portrait ID</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function openEmployeeIdModal() {
    const el = document.getElementById('id-card-qrcode');
    if (el && !el.hasChildNodes()) {
        new QRCode(el, {
            text: '{{ $shortEncrypted }}',
            width: 68, height: 68,
            colorDark: '#000000', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }
    document.getElementById('employee-id-modal-backdrop').classList.replace('hidden', 'flex');
}
function closeEmployeeIdModal() {
    document.getElementById('employee-id-modal-backdrop').classList.replace('flex', 'hidden');
}

document.getElementById('employee-id-download-btn').addEventListener('click', function() {
    openEmployeeIdModal();
    html2canvas(document.getElementById('employee-id-card'), {
        useCORS: true,
        backgroundColor: null,
        scale: 3
    }).then(canvas => {
        const a = document.createElement('a');
        a.download = '{{ $employee->emp_ID }}-employee-id.png';
        a.href = canvas.toDataURL();
        a.click();
    }).catch(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'Download failed', text: 'Employee ID card could not be exported.', icon: 'error' });
        }
    });
});

// Profile picture upload
document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('profilePictureTrigger');
    const input = document.getElementById('profilePictureInput');
    if (!trigger || !input) return;

    const originalAvatarHtml = document.getElementById('changeProfilePicture')?.outerHTML || '';
    const uploadUrl = @json(route('updateProfilePicture', $employee->id));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    const avatarAlt = @json($displayName ?: 'Employee Profile');
    const avatarClass = 'h-24 w-24 rounded-full object-cover text-2xl ring-4 ring-card shadow-lg cursor-pointer transition hover:opacity-90';

    const notify = (type, title, message = '') => {
        if (window.Swal) {
            window.Swal.fire({
                title,
                text: message,
                icon: type,
                timer: type === 'success' ? 2000 : undefined,
                showConfirmButton: type !== 'success',
            });
            return;
        }

        if (window.safeToast?.[type]) {
            window.safeToast[type](title, message);
            return;
        }

        if (message) alert(`${title}\n${message}`);
    };

    const cacheSafe = (src) => `${src}${src.includes('?') ? '&' : '?'}preview=${Date.now()}`;

    const setProfileAvatar = (src) => {
        const avatar = document.getElementById('changeProfilePicture');
        if (!avatar) return;

        if (avatar.tagName.toLowerCase() === 'img') {
            avatar.src = cacheSafe(src);
            return;
        }

        const replacement = document.createElement('img');
        replacement.id = 'changeProfilePicture';
        replacement.src = cacheSafe(src);
        replacement.alt = avatar.getAttribute('alt') || avatarAlt;
        replacement.title = 'Click to change photo';
        replacement.className = avatar.getAttribute('class') || avatarClass;
        avatar.replaceWith(replacement);
    };

    trigger.addEventListener('click', (event) => {
        if (event.target === input) return;
        input.click();
    });

    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            input.value = '';
            notify('error', 'Invalid file', 'Please choose an image file.');
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => setProfileAvatar(event.target.result);
        reader.readAsDataURL(file);

        const formData = new FormData();
        formData.append('profileImage', file);
        trigger.classList.add('pointer-events-none', 'opacity-70');

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.success === false) {
                throw new Error(data.message || data.error || 'Profile picture could not be updated.');
            }

            if (data.profile) setProfileAvatar(data.profile);
            notify('success', 'Profile Updated!');
        } catch (error) {
            const avatar = document.getElementById('changeProfilePicture');
            if (avatar && originalAvatarHtml) {
                avatar.outerHTML = originalAvatarHtml;
            }
            notify('error', 'Upload failed', error.message || 'Profile picture could not be updated.');
        } finally {
            trigger.classList.remove('pointer-events-none', 'opacity-70');
            input.value = '';
            window.refreshUi?.(trigger);
        }
    });
});
</script>
