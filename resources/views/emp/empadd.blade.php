@extends('layouts.master')

@section('body')
@php
    $usesLocation = ($locCtx->enabled ?? true) && $locations->isNotEmpty();
@endphp

{{-- Full-screen dialog overlay --}}
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 backdrop-blur-sm p-4 sm:p-6 lg:p-8">

<div class="relative w-full max-w-3xl my-4">

    {{-- Dialog shell --}}
    <div class="rounded-2xl border border-border/60 bg-card shadow-2xl overflow-hidden">

        {{-- â”€â”€ Dialog Header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="flex items-center justify-between border-b border-border/60 bg-muted/20 px-6 py-4">
            <div>
                <h2 class="text-sm font-bold text-foreground">Add New Employee</h2>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    <i class="fas {{ $locCtx->icon }} mr-1 opacity-60"></i>{{ $orgName }}
                    &nbsp;-&nbsp; Fill in the employee record
                </p>
            </div>
            <a href="{{ route('employees') }}"
               class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground">
                <i class="fas fa-times text-sm"></i>
            </a>
        </div>

        {{-- â”€â”€ Step indicator â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="border-b border-border/60 bg-muted/10 px-6 py-3">
            <div class="flex items-center gap-0">
                @php
                $steps = [
                    ['icon' => 'fa-user', 'label' => 'Identity'],
                    ['icon' => 'fa-briefcase', 'label' => 'Employment'],
                    ['icon' => 'fa-heartbeat', 'label' => 'Physical & IDs'],
                    ['icon' => 'fa-phone', 'label' => 'Contact'],
                    ['icon' => 'fa-map-marker-alt', 'label' => 'Address'],
                ];
                @endphp
                @foreach($steps as $i => $step)
                    <div class="flex items-center {{ $i < count($steps)-1 ? 'flex-1' : '' }}">
                        <div class="step-indicator flex flex-col items-center"
                             data-step="{{ $i+1 }}">
                            <div id="step-dot-{{ $i+1 }}"
                                 class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all
                                        {{ $i === 0 ? 'border-primary bg-primary text-white shadow-sm shadow-primary/30' : 'border-border/60 bg-background text-muted-foreground' }}">
                                <i class="fas {{ $step['icon'] }} text-[10px]"></i>
                            </div>
                            <span id="step-lbl-{{ $i+1 }}"
                                  class="mt-1 text-[9px] font-medium leading-none hidden sm:block
                                         {{ $i === 0 ? 'text-primary' : 'text-muted-foreground' }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                        @if($i < count($steps)-1)
                            <div id="step-line-{{ $i+1 }}"
                                 class="flex-1 mx-1 h-0.5 rounded-full transition-all {{ $i === 0 ? 'bg-primary/30' : 'bg-border/60' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- â”€â”€ Form â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <form action="{{ route('empCreate') }}" method="POST" id="add-emp-form">
            @csrf

            {{-- validation errors --}}
            @if($errors->any())
            <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800/40 dark:bg-red-950/30">
                <p class="text-xs font-semibold text-red-700 dark:text-red-400">Please fix the following errors:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs text-red-600 dark:text-red-400">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- â•â•â• STEP 1 - Personal Identity â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="step-panel-1" class="p-6 space-y-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <i class="fas fa-user mr-1.5 text-primary/70"></i>Personal Identity
                </p>

                {{-- Name row --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="field-label">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="lname" value="{{ old('lname') }}" required
                               oninput="this.value=this.value.toUpperCase()" placeholder="DELA CRUZ"
                               class="field-input">
                    </div>
                    <div>
                        <label class="field-label">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="fname" value="{{ old('fname') }}" required
                               oninput="this.value=this.value.toUpperCase()" placeholder="JUAN"
                               class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Middle Name <span class="text-red-500">*</span></label>
                        <input type="text" name="mname" value="{{ old('mname') }}" required
                               oninput="this.value=this.value.toUpperCase()" placeholder="SANTOS"
                               class="field-input">
                    </div>
                </div>

                {{-- Suffix / Prefix --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="field-label">Suffix</label>
                        <select name="suffix" class="field-select">
                            <option value="">None</option>
                            @foreach(['Jr.','Sr.','I','II','III','IV','V'] as $s)
                                <option value="{{ $s }}" {{ old('suffix') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Prefix</label>
                        <select name="prefix" class="field-select">
                            <option value="">None</option>
                            @foreach(['Atty.','Dr.','Engr.','RChE.','J.D.','M.S.W.','C.P.A.','Mr.','Mrs.','Ms.'] as $p)
                                <option value="{{ $p }}" {{ old('prefix') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Title/Credential</label>
                        <select name="title_prefix" class="field-select">
                            <option value="">None</option>
                            @foreach(['MBA','DPA','MPA','MD','RN','LLM','MSW','CPA','DIT','CNA','CHRP'] as $t)
                                <option value="{{ $t }}" {{ old('title_prefix') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Sex <span class="text-red-500">*</span></label>
                        <select name="sex" required class="field-select">
                            <option value="">- Select -</option>
                            <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                </div>

                {{-- Birth info --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="field-label">Birth Date</label>
                        <input type="date" name="bdate" id="bday" value="{{ old('bdate') }}"
                               onchange="calculateAge()" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Age</label>
                        <input type="text" name="age" id="age" readonly value="{{ old('age') }}"
                               class="field-input opacity-70 cursor-not-allowed">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Birth Place</label>
                        <input type="text" name="b_place" value="{{ old('b_place') }}"
                               placeholder="Municipality / Province" class="field-input">
                    </div>
                </div>

                {{-- Civil Status / Citizenship --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="field-label">Civil Status</label>
                        <select name="civil_status" class="field-select">
                            <option value="">- Select -</option>
                            @foreach(['Single','Married','Separated','Widowed','Other'] as $cs)
                                <option value="{{ $cs }}" {{ old('civil_status') == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Citizenship</label>
                        <select name="citizenship" id="add-citizenship" class="field-select">
                            <option value="">- Select -</option>
                            <option value="1" {{ old('citizenship') == 1 ? 'selected' : '' }}>Filipino</option>
                            <option value="2" {{ old('citizenship') == 2 ? 'selected' : '' }}>Dual Citizenship</option>
                        </select>
                    </div>
                    <div id="dual-category-box" class="{{ old('citizenship') == 2 ? '' : 'opacity-40 pointer-events-none' }}">
                        <label class="field-label">Citizenship By</label>
                        <div class="flex items-center gap-4 mt-2">
                            <label class="flex items-center gap-1.5 text-xs text-foreground cursor-pointer">
                                <input type="radio" name="c_category" value="1" class="accent-primary"
                                       {{ old('c_category') == 1 ? 'checked' : '' }}>
                                By Birth
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-foreground cursor-pointer">
                                <input type="radio" name="c_category" value="2" class="accent-primary"
                                       {{ old('c_category') == 2 ? 'checked' : '' }}>
                                By Naturalization
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Country (only when dual citizenship) --}}
                <div id="add-country-box" class="{{ old('citizenship') == 2 ? '' : 'hidden' }}">
                    <label class="field-label">Country (Dual Citizenship)</label>
                    <select name="country" class="field-select">
                        <option value="">- Select Country -</option>
                        @foreach(['Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia','Austria','Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi','Cabo Verde','Cambodia','Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia','Comoros','Congo','Costa Rica','Croatia','Cuba','Cyprus','Czech Republic','Democratic Republic of the Congo','Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea','Estonia','Eswatini','Ethiopia','Fiji','Finland','France','Gabon','Gambia','Georgia','Germany','Ghana','Greece','Grenada','Guatemala','Guinea','Guinea-Bissau','Guyana','Haiti','Honduras','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Ivory Coast','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati','Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Mauritania','Mauritius','Mexico','Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco','Mozambique','Myanmar','Namibia','Nauru','Nepal','Netherlands','New Zealand','Nicaragua','Niger','Nigeria','North Korea','North Macedonia','Norway','Oman','Pakistan','Palau','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal','Qatar','Romania','Russia','Rwanda','Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines','Samoa','San Marino','Sao Tome and Principe','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Korea','South Sudan','Spain','Sri Lanka','Sudan','Suriname','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tonga','Trinidad and Tobago','Tunisia','Turkey','Turkmenistan','Tuvalu','Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Uzbekistan','Vanuatu','Vatican City','Venezuela','Vietnam','Yemen','Zambia','Zimbabwe'] as $country)
                            <option value="{{ $country }}" {{ old('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- â•â•â• STEP 2 - Employment â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="step-panel-2" class="hidden p-6 space-y-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <i class="fas fa-briefcase mr-1.5 text-primary/70"></i>Employment Details
                </p>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="field-label">Position / Designation <span class="text-red-500">*</span></label>
                        <input type="text" name="position" value="{{ old('position') }}" required
                               placeholder="e.g. Administrative Officer" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Employee ID</label>
                        <input type="text" value="{{ $nextEmpID ?? 'Auto-generated' }}" readonly
                               class="field-input cursor-not-allowed bg-muted/40 font-mono">
                    </div>
                    <div>
                        <label class="field-label">{{ $locCtx->item_label ?? 'Item / Plantilla No.' }}</label>
                        <input type="text" name="item_no" value="{{ old('item_no') }}" placeholder="N/A" class="field-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Date Hired</label>
                        <input type="date" name="date_hired" value="{{ old('date_hired') }}" class="field-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="field-label">Employment Status <span class="text-red-500">*</span></label>
	                        <select name="emp_status" required data-placeholder="Search employment status" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="">- Select -</option>
                            @foreach ($stat as $st)
                                <option value="{{ $st->id }}" {{ old('emp_status') == $st->id ? 'selected' : '' }}>
                                    {{ $st->status_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">{{ $locCtx->office_label ?? 'Department / Office' }} <span class="text-red-500">*</span></label>
                        <select name="emp_dept" required data-placeholder="Search office or department" class="employee-office-select absolute h-px w-px opacity-0">
                            <option value="">- Select Office -</option>
                            @foreach ($offices as $q)
                                <option value="{{ $q->id }}" {{ old('emp_dept') == $q->id ? 'selected' : '' }}>
                                    {{ $q->office_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @if($usesLocation)
                    <div>
                        <label class="field-label">
                            <i class="fas {{ $locCtx->icon }} mr-1 opacity-60 text-[10px]"></i>{{ $locCtx->label }}
                        </label>
	                        <select name="camp_id" data-placeholder="Search {{ strtolower($locCtx->label) }}" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="">- Select {{ $locCtx->label }} -</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('camp_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="field-label">Immediate Supervisor</label>
	                        <select name="supervisor" data-placeholder="Search supervisor" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="0">- None -</option>
                            @foreach ($supervisor as $sup)
                                <option value="{{ $sup->id }}" {{ old('supervisor') == $sup->id ? 'selected' : '' }}>
                                    {{ strtoupper($sup->lname) }}, {{ strtoupper($sup->fname) }}
                                    {{ $sup->mname ? strtoupper(substr($sup->mname,0,1)).'.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- â•â•â• STEP 3 - Physical Info & Government IDs â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="step-panel-3" class="hidden p-6 space-y-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <i class="fas fa-heartbeat mr-1.5 text-primary/70"></i>Physical Information
                </p>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                    <div>
                        <label class="field-label">Height (cm)</label>
                        <input type="number" name="height_cm" id="height_cm_add" value="{{ old('height_cm') }}"
                               placeholder="e.g. 165" step="0.01" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Height (ft)</label>
                        <input type="number" name="height_ft" id="height_ft_add" value="{{ old('height_ft') }}"
                               placeholder="e.g. 5.4" step="0.01" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Weight (kg)</label>
                        <input type="number" name="weight_kg" id="weight_kg_add" value="{{ old('weight_kg') }}"
                               placeholder="e.g. 60" step="0.01" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Weight (lb)</label>
                        <input type="number" name="weight_lb" id="weight_lb_add" value="{{ old('weight_lb') }}"
                               placeholder="e.g. 132" step="0.01" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Blood Type</label>
                        <select name="b_type" class="field-select">
                            <option value="">-</option>
                            @foreach(['A+','A-','AB+','AB-','B+','B-','O+','O-'] as $bt)
                                <option value="{{ $bt }}" {{ old('b_type') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="border-t border-border/40 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">
                        <i class="fas fa-id-card mr-1.5 text-primary/70"></i>Government IDs
                    </p>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="field-label">GSIS No.</label>
                            <input type="text" name="gsis" value="{{ old('gsis') }}" placeholder="N/A" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">Pag-IBIG No.</label>
                            <input type="text" name="pagibig" value="{{ old('pagibig') }}" placeholder="N/A" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">PhilHealth No.</label>
                            <input type="text" name="philhealth" value="{{ old('philhealth') }}" placeholder="N/A" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">UMID / SSS No.</label>
                            <input type="text" name="sss" value="{{ old('sss') }}" placeholder="N/A" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">TIN No.</label>
                            <input type="text" name="tin" value="{{ old('tin') }}" placeholder="N/A" class="field-input font-mono">
                        </div>
                    </div>
                </div>
            </div>

            {{-- â•â•â• STEP 4 - Contact Information â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="step-panel-4" class="hidden p-6 space-y-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <i class="fas fa-phone mr-1.5 text-primary/70"></i>Contact Information
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="field-label">Email Address</label>
                        <input type="email" name="org_email" value="{{ old('org_email') }}"
                               placeholder="email@domain.com" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Mobile Number</label>
                        <input type="text" name="mobile" id="add-mobile" value="{{ old('mobile') }}"
                               placeholder="09XX-XXX-XXXX" maxlength="13" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Telephone Number</label>
                        <input type="text" name="telephone" value="{{ old('telephone') }}"
                               placeholder="(0XX) XXX-XXXX" class="field-input">
                    </div>
                </div>

                <div class="rounded-xl border border-amber-200/60 bg-amber-50/60 dark:border-amber-800/30 dark:bg-amber-950/20 px-4 py-3">
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <i class="fas fa-info-circle mr-1.5"></i>
                        The employee's default login password will be <strong>employee123</strong>. They can change it after first login.
                    </p>
                </div>
            </div>

            {{-- â•â•â• STEP 5 - Addresses â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            <div id="step-panel-5" class="hidden p-6 space-y-6">

                {{-- Residential --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">
                        <i class="fas fa-home mr-1.5 text-primary/70"></i>Residential Address
                    </p>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="field-label">Region</label>
                            <select name="add_region" class="field-select">
                                <option value="">Select</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_id }}" {{ old('add_region') == $region->region_id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Province</label>
                            <input type="text" name="add_prov" value="{{ old('add_prov') }}" placeholder="Province" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">City / Municipality</label>
                            <input type="text" name="add_city" value="{{ old('add_city') }}" placeholder="City / Municipality" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Barangay</label>
                            <input type="text" name="add_brgy" value="{{ old('add_brgy') }}" placeholder="Barangay" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">House / Block / Lot</label>
                            <input type="text" name="add_block" value="{{ old('add_block') }}" placeholder="N/A" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Street</label>
                            <input type="text" name="add_street" value="{{ old('add_street') }}" placeholder="N/A" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Subdivision / Village</label>
                            <input type="text" name="add_village" value="{{ old('add_village') }}" placeholder="N/A" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">ZIP Code</label>
                            <input type="number" name="add_zcode" value="{{ old('add_zcode') }}" placeholder="0000" class="field-input">
                        </div>
                    </div>
                </div>

                {{-- Permanent --}}
                <div class="border-t border-border/40 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            <i class="fas fa-map-pin mr-1.5 text-primary/70"></i>Permanent Address
                        </p>
                        <label class="flex items-center gap-1.5 text-xs text-muted-foreground cursor-pointer select-none">
                            <input type="checkbox" id="same-as-residential" class="accent-primary rounded" onchange="copyResidential(this)">
                            Same as Residential
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="field-label">Region</label>
                            <select name="padd_region" id="padd_region" class="field-select">
                                <option value="">Select</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_id }}" {{ old('padd_region') == $region->region_id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Province</label>
                            <input type="text" name="padd_prov" id="padd_prov" value="{{ old('padd_prov') }}" placeholder="Province" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">City / Municipality</label>
                            <input type="text" name="padd_city" id="padd_city" value="{{ old('padd_city') }}" placeholder="City / Municipality" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Barangay</label>
                            <input type="text" name="padd_brgy" id="padd_brgy" value="{{ old('padd_brgy') }}" placeholder="Barangay" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">House / Block / Lot</label>
                            <input type="text" name="padd_block" id="padd_block" value="{{ old('padd_block') }}" placeholder="N/A" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Street</label>
                            <input type="text" name="padd_street" id="padd_street" value="{{ old('padd_street') }}" placeholder="N/A" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Subdivision / Village</label>
                            <input type="text" name="padd_village" id="padd_village" value="{{ old('padd_village') }}" placeholder="N/A" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">ZIP Code</label>
                            <input type="number" name="padd_zcode" id="padd_zcode" value="{{ old('padd_zcode') }}" placeholder="0000" class="field-input">
                        </div>
                    </div>
                </div>
            </div>

            {{-- â”€â”€ Footer Navigation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
            <div class="flex items-center justify-between border-t border-border/60 bg-muted/10 px-6 py-4">
                <button type="button" id="btn-back" onclick="prevStep()"
                    class="hidden inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-background px-4 py-2 text-xs font-medium text-foreground transition hover:bg-muted">
                    <i class="fas fa-arrow-left text-[10px]"></i> Back
                </button>
                <span id="step-counter" class="text-xs text-muted-foreground">Step 1 of 5</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('employees') }}"
                       class="rounded-xl border border-border/60 px-4 py-2 text-xs font-medium text-foreground transition hover:bg-muted">
                        Cancel
                    </a>
                    <button type="button" id="btn-next" onclick="nextStep()"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90">
                        Next <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                    <button type="submit" id="btn-submit"
                        class="hidden inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-save text-[10px]"></i> Save Employee
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

@push('scripts')
<style>
.field-label { @apply mb-1 block text-xs font-medium text-foreground; }
.field-input { @apply w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground/50 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 transition; }
.field-select { @apply w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 transition cursor-pointer; }
</style>
<script>
// â”€â”€ Step navigation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
var currentStep = 1;
const totalSteps = 5;

function goToStep(n) {
    document.getElementById('step-panel-' + currentStep).classList.add('hidden');
    document.getElementById('step-panel-' + n).classList.remove('hidden');

    // Update dots
    for (let i = 1; i <= totalSteps; i++) {
        const dot = document.getElementById('step-dot-' + i);
        const lbl = document.getElementById('step-lbl-' + i);
        if (i < n) {
            dot.className = 'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all border-emerald-500 bg-emerald-500 text-white shadow-sm shadow-emerald-500/30';
            if (lbl) lbl.className = 'mt-1 text-[9px] font-medium leading-none hidden sm:block text-emerald-600 dark:text-emerald-400';
            dot.innerHTML = '<i class="fas fa-check text-[10px]"></i>';
        } else if (i === n) {
            dot.className = 'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all border-primary bg-primary text-white shadow-sm shadow-primary/30';
            if (lbl) lbl.className = 'mt-1 text-[9px] font-medium leading-none hidden sm:block text-primary';
            dot.innerHTML = '<i class="fas {{ ["fa-user","fa-briefcase","fa-heartbeat","fa-phone","fa-map-marker-alt"][$i-1] }} text-[10px]"></i>';
        } else {
            dot.className = 'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all border-border/60 bg-background text-muted-foreground';
            if (lbl) lbl.className = 'mt-1 text-[9px] font-medium leading-none hidden sm:block text-muted-foreground';
            dot.innerHTML = '<i class="fas {{ ["fa-user","fa-briefcase","fa-heartbeat","fa-phone","fa-map-marker-alt"][$i-1] }} text-[10px]"></i>';
        }
        if (i < totalSteps) {
            const line = document.getElementById('step-line-' + i);
            if (line) line.className = 'flex-1 mx-1 h-0.5 rounded-full transition-all ' + (i < n ? 'bg-emerald-500' : (i === n ? 'bg-primary/30' : 'bg-border/60'));
        }
    }

    document.getElementById('step-counter').textContent = 'Step ' + n + ' of ' + totalSteps;
    document.getElementById('btn-back').classList.toggle('hidden', n === 1);
    document.getElementById('btn-next').classList.toggle('hidden', n === totalSteps);
    document.getElementById('btn-submit').classList.toggle('hidden', n !== totalSteps);
    currentStep = n;
}

// Re-render icon for each step after JS rebuilds innerHTML
const stepIcons = ['fa-user','fa-briefcase','fa-heartbeat','fa-phone','fa-map-marker-alt'];
function goToStepClean(n) {
    document.getElementById('step-panel-' + currentStep).classList.add('hidden');
    document.getElementById('step-panel-' + n).classList.remove('hidden');
    for (let i = 1; i <= totalSteps; i++) {
        const dot = document.getElementById('step-dot-' + i);
        const lbl = document.getElementById('step-lbl-' + i);
        const labels = ['Identity','Employment','Physical & IDs','Contact','Address'];
        if (i < n) {
            dot.className = 'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all border-emerald-500 bg-emerald-500 text-white';
            dot.innerHTML = '<i class="fas fa-check text-[10px]"></i>';
            if (lbl) { lbl.className = 'mt-1 text-[9px] font-medium leading-none hidden sm:block text-emerald-600 dark:text-emerald-400'; lbl.textContent = labels[i-1]; }
        } else if (i === n) {
            dot.className = 'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all border-primary bg-primary text-white shadow-sm shadow-primary/30';
            dot.innerHTML = '<i class="fas ' + stepIcons[i-1] + ' text-[10px]"></i>';
            if (lbl) { lbl.className = 'mt-1 text-[9px] font-medium leading-none hidden sm:block text-primary'; lbl.textContent = labels[i-1]; }
        } else {
            dot.className = 'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all border-border/60 bg-background text-muted-foreground';
            dot.innerHTML = '<i class="fas ' + stepIcons[i-1] + ' text-[10px]"></i>';
            if (lbl) { lbl.className = 'mt-1 text-[9px] font-medium leading-none hidden sm:block text-muted-foreground'; lbl.textContent = labels[i-1]; }
        }
        if (i < totalSteps) {
            const line = document.getElementById('step-line-' + i);
            if (line) line.className = 'flex-1 mx-1 h-0.5 rounded-full transition-all ' + (i < n ? 'bg-emerald-500' : 'bg-border/60');
        }
    }
    document.getElementById('step-counter').textContent = 'Step ' + n + ' of ' + totalSteps;
    document.getElementById('btn-back').classList.toggle('hidden', n === 1);
    document.getElementById('btn-next').classList.toggle('hidden', n === totalSteps);
    document.getElementById('btn-submit').classList.toggle('hidden', n !== totalSteps);
    currentStep = n;
}

function nextStep() { if (currentStep < totalSteps) goToStepClean(currentStep + 1); }
function prevStep() { if (currentStep > 1) goToStepClean(currentStep - 1); }

// â”€â”€ Age calculation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function calculateAge() {
    const bday = document.getElementById('bday').value;
    if (!bday) return;
    const today = new Date(), birth = new Date(bday);
    let age = today.getFullYear() - birth.getFullYear();
    if (today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) age--;
    document.getElementById('age').value = age;
}

// â”€â”€ Citizenship toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.getElementById('add-citizenship').addEventListener('change', function() {
    const isDual = this.value === '2';
    document.getElementById('dual-category-box').classList.toggle('opacity-40', !isDual);
    document.getElementById('dual-category-box').classList.toggle('pointer-events-none', !isDual);
    document.getElementById('add-country-box').classList.toggle('hidden', !isDual);
});

// â”€â”€ Mobile format â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.getElementById('add-mobile').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g,'').substring(0,11);
    let f = v.substring(0,4);
    if (v.length > 4) f += '-' + v.substring(4,7);
    if (v.length > 7) f += '-' + v.substring(7,11);
    e.target.value = f;
});

// â”€â”€ Height/weight conversion â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const hCm = document.getElementById('height_cm_add');
const hFt = document.getElementById('height_ft_add');
const wKg = document.getElementById('weight_kg_add');
const wLb = document.getElementById('weight_lb_add');
if (hCm) hCm.addEventListener('input', () => { const v = parseFloat(hCm.value); if (!isNaN(v)) hFt.value = (v / 30.48).toFixed(2); });
if (hFt) hFt.addEventListener('input', () => { const v = parseFloat(hFt.value); if (!isNaN(v)) hCm.value = Math.round(v * 30.48); });
if (wKg) wKg.addEventListener('input', () => { const v = parseFloat(wKg.value); if (!isNaN(v)) wLb.value = Math.round(v * 2.20462); });
if (wLb) wLb.addEventListener('input', () => { const v = parseFloat(wLb.value); if (!isNaN(v)) wKg.value = Math.round(v / 2.20462); });

// â”€â”€ Same as residential â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initEmployeeOfficeSelects() {
	    document.querySelectorAll('.employee-office-select, .employee-search-select').forEach((select) => {
        if (select.dataset.comboboxReady === 'true') {
            syncEmployeeOfficeCombobox(select);
            return;
        }

        const combobox = document.createElement('div');
        combobox.className = 'employee-combobox relative w-full';
        combobox.innerHTML = `
            <button type="button" class="employee-combobox-trigger flex min-h-10 w-full items-center justify-between gap-2 rounded-xl border border-border/60 bg-background px-3 py-2 text-left text-sm text-foreground shadow-sm transition hover:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20" aria-haspopup="listbox" aria-expanded="false">
                <span class="employee-combobox-value min-w-0 flex-1 truncate"></span>
                <i data-lucide="chevrons-up-down" class="h-3.5 w-3.5 shrink-0 opacity-60"></i>
            </button>
            <div class="employee-combobox-panel absolute left-0 right-0 top-full z-[80] mt-1 hidden overflow-hidden rounded-xl border border-border/70 bg-popover text-popover-foreground shadow-xl backdrop-blur">
                <div class="flex items-center gap-2 border-b border-border/60 bg-background px-3 py-2">
                    <i data-lucide="search" class="h-3.5 w-3.5 text-muted-foreground"></i>
                    <input type="text" class="employee-combobox-search h-8 min-w-0 flex-1 bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" placeholder="Search office or department" autocomplete="off" role="searchbox">
                </div>
                <div class="employee-combobox-options max-h-64 overflow-y-auto p-1" role="listbox"></div>
            </div>
        `;

        select.insertAdjacentElement('afterend', combobox);
        select.dataset.comboboxReady = 'true';
        combobox.querySelector('.employee-combobox-trigger').addEventListener('click', () => openEmployeeOfficeCombobox(select));
        combobox.querySelector('.employee-combobox-search').addEventListener('input', () => renderEmployeeOfficeOptions(select));
        combobox.querySelector('.employee-combobox-search').addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeEmployeeOfficeCombobox(select);
        });
        select.addEventListener('change', () => syncEmployeeOfficeCombobox(select));
        select.addEventListener('invalid', () => openEmployeeOfficeCombobox(select));
        syncEmployeeOfficeCombobox(select);
    });

    window.refreshIcons?.();
}

function getEmployeeOfficeCombobox(select) {
    return select.nextElementSibling?.classList.contains('employee-combobox') ? select.nextElementSibling : null;
}

function syncEmployeeOfficeCombobox(select) {
    const combobox = getEmployeeOfficeCombobox(select);
    if (!combobox) return;
    const selected = select.options[select.selectedIndex];
    const value = combobox.querySelector('.employee-combobox-value');
    value.textContent = selected?.value ? selected.text : (select.dataset.placeholder || 'Search office or department');
    value.classList.toggle('text-muted-foreground', !selected?.value);
}

function openEmployeeOfficeCombobox(select) {
    document.querySelectorAll('.employee-combobox-panel').forEach((panel) => panel.classList.add('hidden'));
    const combobox = getEmployeeOfficeCombobox(select);
    if (!combobox) return;
	    const panel = combobox.querySelector('.employee-combobox-panel');
	    const search = combobox.querySelector('.employee-combobox-search');
	    combobox.querySelector('.employee-combobox-trigger').setAttribute('aria-expanded', 'true');
	    panel.classList.remove('hidden');
	    search.placeholder = select.dataset.placeholder || 'Search and select';
	    search.value = '';
    renderEmployeeOfficeOptions(select);
    requestAnimationFrame(() => search.focus());
}

function closeEmployeeOfficeCombobox(select) {
    const combobox = getEmployeeOfficeCombobox(select);
    if (!combobox) return;
    combobox.querySelector('.employee-combobox-panel').classList.add('hidden');
    combobox.querySelector('.employee-combobox-trigger').setAttribute('aria-expanded', 'false');
}

function renderEmployeeOfficeOptions(select) {
    const combobox = getEmployeeOfficeCombobox(select);
    if (!combobox) return;
    const query = combobox.querySelector('.employee-combobox-search').value.trim().toLowerCase();
    const optionsBox = combobox.querySelector('.employee-combobox-options');
    const options = Array.from(select.options).filter((option) => option.value).filter((option) => option.text.toLowerCase().includes(query));

    optionsBox.innerHTML = options.length
        ? options.map((option) => `
            <button type="button" class="employee-combobox-option flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-accent hover:text-accent-foreground ${option.value === select.value ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground'}" data-value="${option.value}" role="option">
                <span>${escapeEmployeeComboboxText(option.text)}</span>
                ${option.value === select.value ? '<i data-lucide="check" class="h-3.5 w-3.5"></i>' : ''}
            </button>
        `).join('')
	        : '<div class="px-3 py-6 text-center text-sm text-muted-foreground">No results found</div>';

    optionsBox.querySelectorAll('.employee-combobox-option').forEach((button) => {
        button.addEventListener('click', () => {
            select.value = button.dataset.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            closeEmployeeOfficeCombobox(select);
        });
    });

    window.refreshIcons?.();
}

function escapeEmployeeComboboxText(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    initEmployeeOfficeSelects();
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.employee-combobox')) {
	            document.querySelectorAll('.employee-office-select, .employee-search-select').forEach(closeEmployeeOfficeCombobox);
        }
    });
});
function copyResidential(cb) {
    if (!cb.checked) return;
    const map = {
        add_prov: 'padd_prov', add_city: 'padd_city', add_brgy: 'padd_brgy',
        add_block: 'padd_block', add_street: 'padd_street', add_village: 'padd_village',
        add_zcode: 'padd_zcode'
    };
    Object.entries(map).forEach(([src, dst]) => {
        const s = document.querySelector('[name="' + src + '"]');
        const d = document.getElementById(dst);
        if (s && d) d.value = s.value;
    });
    // Region select
    const srcR = document.querySelector('[name="add_region"]');
    const dstR = document.getElementById('padd_region');
    if (srcR && dstR) dstR.value = srcR.value;
}

// If form had validation errors, jump to the first step that has an error
@if($errors->any())
    // Just stay on step 1 to show errors
@endif
</script>
@endpush

@endsection

