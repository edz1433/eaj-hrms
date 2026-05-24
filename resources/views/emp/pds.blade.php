@extends('layouts.master')

@section('body')

<div class="flex flex-col gap-5 lg:flex-row lg:items-start p-4 lg:p-6">

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    @include('emp.submenu-side')

    {{-- ── Main content ────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-base font-bold text-foreground">Personal Information</h1>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    Changes auto-save when you leave each field.
                    @if($guard === 'employee')<span class="ml-1 text-amber-500"><i data-lucide="lock" class="inline h-3 w-3"></i> Some fields are read-only</span>@endif
                </p>
            </div>
            <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">
                <i data-lucide="refresh-cw" class="h-3 w-3"></i> Auto-save on
            </span>
        </div>

        @php
        $inputCls  = "w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground/40 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 transition update-field";
        $selectCls = "w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 transition cursor-pointer update-field";
        $rdCls     = "w-full rounded-xl border border-border/60 bg-background/50 px-3 py-2 text-sm text-foreground/60 cursor-not-allowed";
        $labelCls  = "mb-1 block text-xs font-medium text-muted-foreground";
        @endphp

        <form method="POST" action="#">
        @csrf

        {{-- ── Card: Personal Identity ──────────────────────────────────── --}}
        <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden mb-4">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i data-lucide="user-round" class="h-3.5 w-3.5"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-foreground">Personal Identity</span>
            </div>
            <div class="p-5 space-y-4">

                {{-- Name --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="{{ $labelCls }}">Last Name</label>
                        <input type="text" name="lname" value="{{ $employee->lname }}"
                               data-column-id="{{ $empid }}" data-column-name="lname"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">First Name</label>
                        <input type="text" name="fname" value="{{ $employee->fname }}"
                               data-column-id="{{ $empid }}" data-column-name="fname"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Middle Name</label>
                        <input type="text" name="mname" value="{{ $employee->mname }}"
                               data-column-id="{{ $empid }}" data-column-name="mname"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Suffix</label>
                        <select name="suffix" class="{{ $selectCls }}">
                            <option value="" data-column-id="{{ $empid }}" data-column-name="suffix"
                                    @if(!$employee->suffix) selected @endif>N/A</option>
                            @foreach(['Jr.','Sr.','I','II','III','IV','V'] as $s)
                                <option value="{{ $s }}" data-column-id="{{ $empid }}" data-column-name="suffix"
                                        @if($employee->suffix == $s) selected @endif>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Prefix / Title / Birth --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="{{ $labelCls }}">Prefix</label>
                        <select name="prefix" class="{{ $selectCls }}">
                            <option value="" data-column-id="{{ $empid }}" data-column-name="prefix" @if(!$employee->prefix) selected @endif>N/A</option>
                            @foreach(['Ph.D.','Atty.','Dr.','Engr.','RChE.','J.D.','M.S.W.','C.P.A.','C.L.E.A.','DIT.'] as $p)
                                <option value="{{ $p }}" data-column-id="{{ $empid }}" data-column-name="prefix" @if($employee->prefix==$p) selected @endif>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Title / Credential</label>
                        <select name="title_prefix" data-column-id="{{ $empid }}" data-column-name="title_prefix" class="{{ $selectCls }}">
                            <option value="" @if(!$employee->title_prefix) selected @endif>N/A</option>
                            @foreach(['MBA','DPA','MPA','MD','RN','LLM','MSW','CPA','DIT','CNA','CHRP'] as $t)
                                <option value="{{ $t }}" @if($employee->title_prefix==$t) selected @endif>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Birth Date</label>
                        <input type="date" name="bdate" value="{{ $employee->bdate }}"
                               data-column-id="{{ $empid }}" data-column-name="bdate"
                               id="bday" onchange="calculateAge()" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Age</label>
                        <input type="text" name="age" id="age"
                               value="{{ $employee->bdate ? \Carbon\Carbon::parse($employee->bdate)->age : '' }}"
                               class="{{ $rdCls }}" readonly>
                    </div>
                </div>

                {{-- Demographics --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelCls }}">Birth Place</label>
                        <input type="text" name="b_place" value="{{ $employee->b_place }}"
                               data-column-id="{{ $empid }}" data-column-name="b_place"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Sex</label>
                        <select name="sex" class="{{ $selectCls }}">
                            <option disabled>Select</option>
                            <option value="Male" data-column-id="{{ $empid }}" data-column-name="sex" @if($employee->sex=='Male') selected @endif>Male</option>
                            <option value="Female" data-column-id="{{ $empid }}" data-column-name="sex" @if($employee->sex=='Female') selected @endif>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Civil Status</label>
                        <select name="civil_status" class="{{ $selectCls }}">
                            <option disabled>Select</option>
                            @foreach(['Single','Married','Separated','Widowed','Other'] as $cs)
                                <option value="{{ $cs }}" data-column-id="{{ $empid }}" data-column-name="civil_status" @if($employee->civil_status==$cs) selected @endif>{{ $cs }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Citizenship --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="{{ $labelCls }}">Citizenship</label>
                        <select name="citizenship" id="pds-citizenship" class="{{ $selectCls }}">
                            <option disabled>Select</option>
                            <option value="1" data-column-id="{{ $empid }}" data-column-name="citizenship" @if($employee->citizenship==1) selected @endif>Filipino</option>
                            <option value="2" data-column-id="{{ $empid }}" data-column-name="citizenship" @if($employee->citizenship==2) selected @endif>Dual Citizenship</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Citizenship By</label>
                        <div class="flex items-center gap-4 mt-2">
                            <label class="flex items-center gap-1.5 text-xs text-foreground cursor-pointer">
                                <input type="radio" class="update-field accent-primary pds-citizenship-category" value="1" name="c_category"
                                       data-column-id="{{ $empid }}" data-column-name="c_category"
                                       @if($employee->c_category==1) checked @endif @if($employee->citizenship != 2) disabled @endif>
                                By Birth
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-foreground cursor-pointer">
                                <input type="radio" class="update-field accent-primary pds-citizenship-category" value="2" name="c_category"
                                       data-column-id="{{ $empid }}" data-column-name="c_category"
                                       @if($employee->c_category==2) checked @endif @if($employee->citizenship != 2) disabled @endif>
                                By Naturalization
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Country</label>
                        <select name="country" id="pds-country" data-placeholder="Search country" class="{{ $selectCls }} js-search-select"
                                @if($employee->citizenship != 2) disabled @endif>
                            <option value="" data-column-id="{{ $empid }}" data-column-name="country">Select</option>
                            @foreach(['Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia','Austria','Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi','Cabo Verde','Cambodia','Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia','Comoros','Congo','Costa Rica','Croatia','Cuba','Cyprus','Czech Republic','Denmark','Djibouti','Dominican Republic','Ecuador','Egypt','El Salvador','Eritrea','Estonia','Ethiopia','Fiji','Finland','France','Germany','Ghana','Greece','Guatemala','Haiti','Honduras','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Laos','Latvia','Lebanon','Libya','Lithuania','Luxembourg','Malaysia','Mexico','Moldova','Morocco','Myanmar','Nepal','Netherlands','New Zealand','Nigeria','Norway','Oman','Pakistan','Panama','Paraguay','Peru','Philippines','Poland','Portugal','Qatar','Romania','Russia','Rwanda','Saudi Arabia','Senegal','Singapore','South Africa','South Korea','Spain','Sri Lanka','Sweden','Switzerland','Syria','Taiwan','Tanzania','Thailand','Turkey','Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Venezuela','Vietnam','Zambia','Zimbabwe'] as $country)
                                <option value="{{ $country }}" data-column-id="{{ $empid }}" data-column-name="country"
                                        @if($employee->country==$country) selected @endif>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card: Employment ─────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden mb-4">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
                    <i data-lucide="briefcase-business" class="h-3.5 w-3.5"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-foreground">Employment Details</span>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="{{ $labelCls }}">Date Hired</label>
                        <input type="date" name="date_hired" value="{{ $employee->date_hired }}"
                               data-column-id="{{ $empid }}" data-column-name="date_hired"
                               class="{{ $inputCls }}">
                    </div>
                    @if($guard == 'web')
                    <div>
                        <label class="{{ $labelCls }}">Item / Plantilla No.</label>
                        <input type="text" name="item_no" value="{{ $employee->item_no }}"
                               data-column-id="{{ $empid }}" data-column-name="item_no"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label class="{{ $labelCls }}">Position / Designation</label>
                        <input type="text" name="position" value="{{ $employee->position }}"
                               data-column-id="{{ $empid }}" data-column-name="position"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @if($guard == 'web')
                    <div>
                        <label class="{{ $labelCls }}">Employment Status</label>
	                        <select name="emp_status" data-placeholder="Search employment status" class="{{ $selectCls }} js-search-select">
                            <option value="">Select</option>
                            @foreach($stat as $st)
                                <option value="{{ $st->id }}" data-column-id="{{ $empid }}" data-column-name="emp_status"
                                        @if($employee->emp_status==$st->id) selected @endif>{{ $st->status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="{{ $labelCls }}">{{ $locCtx->label }}</label>
	                        <select name="camp_id" data-placeholder="Search {{ strtolower($locCtx->label) }}" class="{{ $selectCls }} js-search-select">
                            <option disabled>Select</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" data-column-id="{{ $empid }}" data-column-name="camp_id"
                                        @if($employee->camp_id==$loc->id) selected @endif>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Department / Office</label>
	                        <select name="emp_dept" data-placeholder="Search department or office" class="{{ $selectCls }} js-search-select">
                            <option value="">Select</option>
                            @foreach($offices as $of)
                                <option value="{{ $of->id }}" data-column-id="{{ $empid }}" data-column-name="emp_dept"
                                        @if($employee->emp_dept==$of->id) selected @endif>{{ $of->office_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(isset($supervisor))
                    <div>
                        <label class="{{ $labelCls }}">Immediate Supervisor</label>
	                        <select name="supervisor" data-placeholder="Search supervisor" class="{{ $selectCls }} js-search-select">
                            <option value="0" data-column-id="{{ $empid }}" data-column-name="supervisor">— None —</option>
                            @foreach($supervisor as $sup)
                                <option value="{{ $sup->id }}" data-column-id="{{ $empid }}" data-column-name="supervisor"
                                        @if($employee->supervisor==$sup->id) selected @endif>
                                    {{ strtoupper($sup->lname) }}, {{ strtoupper($sup->fname) }}
                                    {{ $sup->mname ? strtoupper(substr($sup->mname,0,1)).'.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Card: Physical Info ───────────────────────────────────────── --}}
        <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden mb-4">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="heart-pulse" class="h-3.5 w-3.5"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-foreground">Physical Information</span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div>
                        <label class="{{ $labelCls }}">Height (cm)</label>
                        <input type="number" name="height_cm" id="height_cm" value="{{ $employee->height_cm }}"
                               data-column-id="{{ $empid }}" data-column-name="height_cm"
                               class="{{ $inputCls }}" placeholder="N/A" step="0.01">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Height (m)</label>
                        <input type="number" name="height_m" id="height_m" value="{{ $employee->height_m }}"
                               data-column-id="{{ $empid }}" data-column-name="height_m"
                               class="{{ $inputCls }}" placeholder="N/A" step="0.01">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Weight (kg)</label>
                        <input type="number" name="weight_kg" id="weight_kg" value="{{ $employee->weight_kg }}"
                               data-column-id="{{ $empid }}" data-column-name="weight_kg"
                               class="{{ $inputCls }}" placeholder="N/A" step="0.01">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Weight (lb)</label>
                        <input type="number" name="weight_lb" id="weight_lb" value="{{ $employee->weight_lb }}"
                               data-column-id="{{ $empid }}" data-column-name="weight_lb"
                               class="{{ $inputCls }}" placeholder="N/A" step="0.01">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Blood Type</label>
                        <select name="b_type" class="{{ $selectCls }}">
                            <option disabled>Select</option>
                            @foreach(['A+','A-','AB+','AB-','B+','B-','O+','O-'] as $bt)
                                <option value="{{ $bt }}" data-column-id="{{ $empid }}" data-column-name="b_type"
                                        @if($employee->b_type==$bt) selected @endif>{{ $bt }}</option>
                            @endforeach
                            <option value="" data-column-id="{{ $empid }}" data-column-name="b_type"
                                    @if(!$employee->b_type) selected @endif>N/A</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card: Government IDs & Contact ───────────────────────────── --}}
        <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden mb-4">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400">
                    <i data-lucide="id-card" class="h-3.5 w-3.5"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-foreground">Government IDs & Contact</span>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    <div>
                        <label class="{{ $labelCls }}">GSIS</label>
                        <input type="text" name="gsis" id="gsis" value="{{ $employee->gsis }}"
                               data-column-id="{{ $empid }}" data-column-name="gsis"
                               class="{{ $inputCls }} font-mono text-xs" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Pag-IBIG</label>
                        <input type="text" name="pagibig" id="pagibig" value="{{ $employee->pagibig }}"
                               data-column-id="{{ $empid }}" data-column-name="pagibig"
                               class="{{ $inputCls }} font-mono text-xs" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">PhilHealth</label>
                        <input type="text" name="philhealth" id="philhealth" value="{{ $employee->philhealth }}"
                               data-column-id="{{ $empid }}" data-column-name="philhealth"
                               class="{{ $inputCls }} font-mono text-xs" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">UMID / SSS</label>
                        <input type="text" name="sss" id="sss" value="{{ $employee->sss }}"
                               data-column-id="{{ $empid }}" data-column-name="sss"
                               class="{{ $inputCls }} font-mono text-xs" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">TIN</label>
                        <input type="text" name="tin" id="tin" value="{{ $employee->tin }}"
                               data-column-id="{{ $empid }}" data-column-name="tin"
                               class="{{ $inputCls }} font-mono text-xs" placeholder="N/A">
                    </div>
                </div>

                <div class="border-t border-border/40 pt-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground mb-3">Contact</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="{{ $labelCls }}">Email Address</label>
                            <input type="email" name="org_email" id="org_email" value="{{ $employee->email }}"
                                   data-column-id="{{ $empid }}" data-column-name="email"
                                   class="{{ $guard === 'employee' ? $rdCls : $inputCls }}"
                                   placeholder="N/A" @if($guard === 'employee') readonly @endif>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Mobile Number</label>
                            <input type="text" name="mobile" id="mobile" value="{{ $employee->mobile }}"
                                   data-column-id="{{ $empid }}" data-column-name="mobile"
                                   class="{{ $inputCls }}" placeholder="09XX-XXX-XXXX">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Telephone</label>
                            <input type="text" name="telephone" id="telephone" value="{{ $employee->telephone }}"
                                   data-column-id="{{ $empid }}" data-column-name="telephone"
                                   class="{{ $inputCls }}" placeholder="(0XX) XXX-XXXX">
                        </div>
                    </div>
                </div>

                @if($guard == 'web' && isset($payrollemp))
                <div class="border-t border-border/40 pt-4">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div>
                            <label class="{{ $labelCls }}">Monthly Salary</label>
                            <input type="text" name="emp_salary" id="emp_salary" value="{{ $payrollemp->emp_salary }}"
                                   data-column-id="{{ $empid }}" data-column-name="emp_salary"
                                   class="{{ $inputCls }}" placeholder="0.00">
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Card: Residential Address ────────────────────────────────── --}}
        <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden mb-4">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <i data-lucide="house" class="h-3.5 w-3.5"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-foreground">Residential Address</span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="{{ $labelCls }}">Region</label>
	                        <select id="region" name="add_region" data-placeholder="Search region" class="{{ $selectCls }} js-search-select">
                            <option value="">Select</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->region_id }}" data-column-id="{{ $empid }}" data-column-name="add_region"
                                        @if($employee->add_region == $region->region_id) selected @endif>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Province</label>
                        <input type="text" name="add_prov" id="add_prov" value="{{ $employee->add_prov }}"
                               data-column-id="{{ $empid }}" data-column-name="add_prov"
                               class="{{ $inputCls }}" placeholder="Province">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">City / Municipality</label>
                        <input type="text" name="add_city" id="add_city" value="{{ $employee->add_city }}"
                               data-column-id="{{ $empid }}" data-column-name="add_city"
                               class="{{ $inputCls }}" placeholder="City">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Barangay</label>
                        <input type="text" name="add_brgy" id="add_brgy" value="{{ $employee->add_brgy }}"
                               data-column-id="{{ $empid }}" data-column-name="add_brgy"
                               class="{{ $inputCls }}" placeholder="Barangay">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">House / Block / Lot</label>
                        <input type="text" name="add_block" id="add_block" value="{{ $employee->add_block }}"
                               data-column-id="{{ $empid }}" data-column-name="add_block"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Street</label>
                        <input type="text" name="add_street" id="add_street" value="{{ $employee->add_street }}"
                               data-column-id="{{ $empid }}" data-column-name="add_street"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Subdivision / Village</label>
                        <input type="text" name="add_village" id="add_village" value="{{ $employee->add_village }}"
                               data-column-id="{{ $empid }}" data-column-name="add_village"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">ZIP Code</label>
                        <input type="number" name="add_zcode" id="add_zcode" value="{{ $employee->add_zcode }}"
                               data-column-id="{{ $empid }}" data-column-name="add_zcode"
                               class="{{ $inputCls }}" placeholder="0000">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card: Permanent Address ──────────────────────────────────── --}}
        <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden mb-4">
            <div class="flex items-center gap-2 border-b border-border/60 bg-muted/20 px-5 py-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                    <i data-lucide="map-pin" class="h-3.5 w-3.5"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-foreground">Permanent Address</span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="{{ $labelCls }}">Region</label>
	                        <select id="region1" name="padd_region" data-placeholder="Search region" class="{{ $selectCls }} js-search-select">
                            <option value="">Select</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->region_id }}" data-column-id="{{ $empid }}" data-column-name="padd_region"
                                        @if($employee->padd_region == $region->region_id) selected @endif>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Province</label>
                        <input type="text" name="padd_prov" id="padd_prov" value="{{ $employee->padd_prov }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_prov"
                               class="{{ $inputCls }}" placeholder="Province">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">City / Municipality</label>
                        <input type="text" name="padd_city" id="padd_city" value="{{ $employee->padd_city }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_city"
                               class="{{ $inputCls }}" placeholder="City">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Barangay</label>
                        <input type="text" name="padd_brgy" id="padd_brgy" value="{{ $employee->padd_brgy }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_brgy"
                               class="{{ $inputCls }}" placeholder="Barangay">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">House / Block / Lot</label>
                        <input type="text" name="padd_block" id="padd_block" value="{{ $employee->padd_block }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_block"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Street</label>
                        <input type="text" name="padd_street" id="padd_street" value="{{ $employee->padd_street }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_street"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Subdivision / Village</label>
                        <input type="text" name="padd_village" id="padd_village" value="{{ $employee->padd_village }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_village"
                               class="{{ $inputCls }}" placeholder="N/A">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">ZIP Code</label>
                        <input type="number" name="padd_zcode" id="padd_zcode" value="{{ $employee->padd_zcode }}"
                               data-column-id="{{ $empid }}" data-column-name="padd_zcode"
                               class="{{ $inputCls }}" placeholder="0000">
                    </div>
                </div>
            </div>
        </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    initPdsAutosave();
    initPdsSearchSelects();
    initPdsCitizenshipToggle();

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.pds-search-select')) {
            document.querySelectorAll('.pds-search-select-panel').forEach((panel) => panel.classList.add('hidden'));
            document.querySelectorAll('.pds-search-select-trigger').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
        }
    });

    window.addEventListener('resize', positionOpenPdsSearchSelects);
    window.addEventListener('scroll', positionOpenPdsSearchSelects, true);
});

function initPdsCitizenshipToggle() {
    const citizenship = document.getElementById('pds-citizenship');
    const country = document.getElementById('pds-country');
    const categories = document.querySelectorAll('.pds-citizenship-category');
    if (!citizenship || !country) return;

    const syncCitizenshipFields = () => {
        const isDual = citizenship.value === '2';

        country.disabled = !isDual;
        if (!isDual) {
            country.value = '';
            categories.forEach((radio) => {
                radio.checked = false;
                radio.disabled = true;
            });
        } else {
            categories.forEach((radio) => {
                radio.disabled = false;
            });
        }

        syncPdsSearchSelect(country);
    };

    citizenship.addEventListener('change', syncCitizenshipFields);
    syncCitizenshipFields();
}

function initPdsAutosave() {
    const fields = document.querySelectorAll('.update-field');
    fields.forEach((field) => {
        if (field.dataset.autosaveReady === 'true') return;

        const eventName = ['SELECT', 'INPUT'].includes(field.tagName) && ['radio', 'checkbox'].includes(field.type)
            ? 'change'
            : (field.tagName === 'SELECT' ? 'change' : 'blur');

        field.dataset.autosaveReady = 'true';
        field.dataset.lastSavedValue = getPdsFieldValue(field) ?? '';
        field.addEventListener(eventName, () => savePdsField(field));
    });
}

function calculateAge() {
    const birthday = document.getElementById('bday')?.value;
    const ageInput = document.getElementById('age');
    if (!birthday || !ageInput) {
        if (ageInput) ageInput.value = '';
        return;
    }

    const birthDate = new Date(birthday);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    ageInput.value = age >= 0 ? age : '';
}

function getPdsFieldMeta(field) {
    if (field.tagName === 'SELECT') {
        const selected = field.options[field.selectedIndex];
        return {
            id: selected?.dataset.columnId || field.dataset.columnId,
            column: selected?.dataset.columnName || field.dataset.columnName,
        };
    }

    return {
        id: field.dataset.columnId,
        column: field.dataset.columnName,
    };
}

function getPdsFieldValue(field) {
    if (field.type === 'radio' || field.type === 'checkbox') {
        return field.checked ? field.value : null;
    }

    return field.value;
}

function setPdsAutosaveState(field, state) {
    field.classList.remove('border-emerald-400', 'border-red-400');
    if (state === 'saved') field.classList.add('border-emerald-400');
    if (state === 'error') field.classList.add('border-red-400');

    if (state === 'saved' || state === 'error') {
        setTimeout(() => field.classList.remove('border-emerald-400', 'border-red-400'), 1200);
    }
}

function savePdsField(field) {
    if (field.disabled || field.readOnly) return;
    if ((field.type === 'radio' || field.type === 'checkbox') && !field.checked) return;

    const value = getPdsFieldValue(field);
    const valueKey = value ?? '';
    if (field.dataset.lastSavedValue === valueKey) return;

    const meta = getPdsFieldMeta(field);
    if (!meta.id || !meta.column) {
        console.warn('Auto-save skipped: missing employee id or column.', field);
        return;
    }

    fetch('{{ route("employeeUpdate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            id: meta.id,
            column: meta.column,
            value: value,
        }),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const errors = data.errors ? Object.values(data.errors).flat().join(' ') : '';
            throw new Error(errors || data.message || 'Auto-save failed.');
        }
        field.dataset.lastSavedValue = valueKey;
        setPdsAutosaveState(field, 'saved');
    })
    .catch((error) => {
        setPdsAutosaveState(field, 'error');
        console.error(error.message);
    });
}

function initPdsSearchSelects() {
    document.querySelectorAll('select.js-search-select').forEach((select) => {
        if (select.dataset.searchReady === 'true') {
            syncPdsSearchSelect(select);
            return;
        }

        select.classList.add('absolute', 'h-px', 'w-px', 'opacity-0', 'pointer-events-none');

        const wrapper = document.createElement('div');
        wrapper.className = 'pds-search-select relative w-full';
        wrapper.innerHTML = `
            <button type="button" class="pds-search-select-trigger flex min-h-10 w-full items-center justify-between gap-2 rounded-xl border border-border/60 bg-background px-3 py-2 text-left text-sm text-foreground shadow-sm transition hover:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-60" aria-haspopup="listbox" aria-expanded="false">
                <span class="pds-search-select-value min-w-0 flex-1 truncate"></span>
                <i data-lucide="chevrons-up-down" class="h-3.5 w-3.5 shrink-0 opacity-60"></i>
            </button>
            <div class="pds-search-select-panel fixed z-[9999] hidden overflow-hidden rounded-xl border border-border/70 bg-popover text-popover-foreground shadow-xl">
                <div class="flex items-center gap-2 border-b border-border/60 bg-background px-3 py-2">
                    <i data-lucide="search" class="h-3.5 w-3.5 text-muted-foreground"></i>
                    <input type="text" class="pds-search-select-input h-8 min-w-0 flex-1 bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" autocomplete="off" role="searchbox">
                </div>
                <div class="pds-search-select-options max-h-64 overflow-y-auto p-1" role="listbox"></div>
            </div>
        `;

        select.insertAdjacentElement('afterend', wrapper);
        select.dataset.searchReady = 'true';

        wrapper.querySelector('.pds-search-select-trigger').addEventListener('click', () => openPdsSearchSelect(select));
        wrapper.querySelector('.pds-search-select-input').addEventListener('input', () => renderPdsSearchOptions(select));
        wrapper.querySelector('.pds-search-select-input').addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closePdsSearchSelect(select);
        });
        select.addEventListener('change', () => syncPdsSearchSelect(select));
        new MutationObserver(() => syncPdsSearchSelect(select))
            .observe(select, { attributes: true, attributeFilter: ['disabled'] });

        syncPdsSearchSelect(select);
    });

    window.refreshUi?.();
    window.refreshIcons?.();
}

function getPdsSearchSelect(select) {
    return select.nextElementSibling?.classList.contains('pds-search-select') ? select.nextElementSibling : null;
}

function syncPdsSearchSelect(select) {
    const wrapper = getPdsSearchSelect(select);
    if (!wrapper) return;

    const selected = select.options[select.selectedIndex];
    const value = wrapper.querySelector('.pds-search-select-value');
    const trigger = wrapper.querySelector('.pds-search-select-trigger');
    const hasValue = !!selected?.value;

    value.textContent = hasValue ? selected.text.trim() : (select.dataset.placeholder || 'Search and select');
    value.classList.toggle('text-muted-foreground', !hasValue);
    trigger.disabled = select.disabled;
}

function openPdsSearchSelect(select) {
    if (select.disabled) return;

    document.querySelectorAll('.pds-search-select-panel').forEach((panel) => panel.classList.add('hidden'));
    document.querySelectorAll('.pds-search-select-trigger').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));

    const wrapper = getPdsSearchSelect(select);
    if (!wrapper) return;

    const input = wrapper.querySelector('.pds-search-select-input');
    input.placeholder = select.dataset.placeholder || 'Search and select';
    input.value = '';
    wrapper.querySelector('.pds-search-select-panel').classList.remove('hidden');
    wrapper.querySelector('.pds-search-select-trigger').setAttribute('aria-expanded', 'true');
    renderPdsSearchOptions(select);
    positionPdsSearchSelect(select);
    requestAnimationFrame(() => input.focus());
}

function closePdsSearchSelect(select) {
    const wrapper = getPdsSearchSelect(select);
    if (!wrapper) return;
    wrapper.querySelector('.pds-search-select-panel').classList.add('hidden');
    wrapper.querySelector('.pds-search-select-trigger').setAttribute('aria-expanded', 'false');
}

function renderPdsSearchOptions(select) {
    const wrapper = getPdsSearchSelect(select);
    if (!wrapper) return;

    const query = wrapper.querySelector('.pds-search-select-input').value.trim().toLowerCase();
    const optionsBox = wrapper.querySelector('.pds-search-select-options');
    const options = Array.from(select.options)
        .filter((option) => option.value !== '')
        .filter((option) => option.text.toLowerCase().includes(query));

    optionsBox.innerHTML = options.length
        ? options.map((option) => `
            <button type="button" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-accent hover:text-accent-foreground ${option.value === select.value ? 'bg-primary/10 font-semibold text-primary' : 'text-foreground'}" data-value="${option.value}" role="option">
                <span class="min-w-0 flex-1 truncate">${escapePdsSearchText(option.text.trim())}</span>
                ${option.value === select.value ? '<i data-lucide="check" class="h-3.5 w-3.5 shrink-0"></i>' : ''}
            </button>
        `).join('')
        : '<div class="px-3 py-6 text-center text-sm text-muted-foreground">No results found</div>';

    optionsBox.querySelectorAll('button[data-value]').forEach((button) => {
        button.addEventListener('click', () => {
            select.value = button.dataset.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            closePdsSearchSelect(select);
        });
    });

    window.refreshUi?.(wrapper);
    window.refreshIcons?.();

    positionPdsSearchSelect(select);
}

function positionOpenPdsSearchSelects() {
    document.querySelectorAll('.pds-search-select-panel:not(.hidden)').forEach((panel) => {
        const wrapper = panel.closest('.pds-search-select');
        const select = wrapper?.previousElementSibling;
        if (select?.matches('select.js-search-select')) {
            positionPdsSearchSelect(select);
        }
    });
}

function positionPdsSearchSelect(select) {
    const wrapper = getPdsSearchSelect(select);
    if (!wrapper) return;

    const trigger = wrapper.querySelector('.pds-search-select-trigger');
    const panel = wrapper.querySelector('.pds-search-select-panel');
    if (!trigger || !panel || panel.classList.contains('hidden')) return;

    const rect = trigger.getBoundingClientRect();
    const gap = 6;
    const viewportPadding = 12;
    const belowSpace = window.innerHeight - rect.bottom - viewportPadding;
    const aboveSpace = rect.top - viewportPadding;
    const preferredHeight = 310;
    const openUp = belowSpace < 180 && aboveSpace > belowSpace;
    const maxHeight = Math.max(160, Math.min(preferredHeight, openUp ? aboveSpace - gap : belowSpace - gap));
    const panelTop = openUp ? Math.max(viewportPadding, rect.top - gap - maxHeight) : Math.min(window.innerHeight - viewportPadding - maxHeight, rect.bottom + gap);

    panel.style.left = `${Math.max(viewportPadding, rect.left)}px`;
    panel.style.top = `${panelTop}px`;
    panel.style.width = `${Math.min(rect.width, window.innerWidth - viewportPadding * 2)}px`;
    panel.style.maxHeight = `${maxHeight}px`;

    const options = panel.querySelector('.pds-search-select-options');
    if (options) {
        options.style.maxHeight = `${Math.max(96, maxHeight - 49)}px`;
    }
}

function escapePdsSearchText(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
