{{--
    Employee add / edit modals.
    Requires: $statuses, $offices, $locations, $locCtx (all globally or controller-passed).
    Include with: @include('emp.modals')
--}}
@php
    $usesLocation = ($locCtx->enabled ?? true) && $locations->isNotEmpty();
    $locationIcon = $locationIcon ?? match($locCtx->type ?? 'office') {
        'campus' => 'school',
        'site' => 'map-pinned',
        'plant' => 'factory',
        'facility' => 'building-2',
        default => 'building-2',
    };
@endphp

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     ADD EMPLOYEE MODAL  #modal-employee-backdrop
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="modal-employee-backdrop"
     class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0"
     aria-modal="true" role="dialog">

    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"
         onclick="document.getElementById('modal-employee-backdrop').classList.add('hidden');document.getElementById('modal-employee-backdrop').classList.remove('flex');"></div>

    <div class="relative h-screen max-h-screen overflow-y-auto rounded-none border-l border-border bg-card shadow-2xl w-full max-w-xl rounded-2xl border border-border/60 bg-card shadow-2xl">

        <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
            <h3 class="inline-flex items-center gap-2 font-semibold text-foreground">
                <i data-lucide="user-plus" class="h-4 w-4 text-primary"></i>Add Employee
            </h3>
            <button onclick="document.getElementById('modal-employee-backdrop').classList.add('hidden');document.getElementById('modal-employee-backdrop').classList.remove('flex');"
                class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <form action="{{ route('empCreate') }}" method="POST" id="employee-create-form">
            @csrf
            <div class="p-5 space-y-4">

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="fname" value="{{ old('fname') }}" required oninput="this.value=this.value.toUpperCase()" placeholder="JUAN"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Middle Name <span class="text-red-500">*</span></label>
                        <input type="text" name="mname" value="{{ old('mname') }}" required oninput="this.value=this.value.toUpperCase()" placeholder="SANTOS"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="lname" value="{{ old('lname') }}" required oninput="this.value=this.value.toUpperCase()" placeholder="DELA CRUZ"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                </div>

                <div class="border-t border-border/40"></div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @if($usesLocation)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">
                            <i data-lucide="{{ $locationIcon ?? 'building-2' }}" class="mr-1 inline h-3 w-3 opacity-60"></i>{{ $locCtx->label }}
                        </label>
                        <select name="camp_id" data-placeholder="Search {{ strtolower($locCtx->label) }}" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="">- Select {{ $locCtx->label }} -</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('camp_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">{{ $locCtx->office_label ?? 'Department / Office' }} <span class="text-red-500">*</span></label>
                        <select name="emp_dept" required data-placeholder="Search office or department"
                                class="employee-office-select absolute h-px w-px opacity-0">
                            <option value="">- Select Office -</option>
                            @foreach ($offices as $q)
                                <option value="{{ $q->id }}" {{ old('emp_dept') == $q->id ? 'selected' : '' }}>{{ $q->office_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Position <span class="text-red-500">*</span></label>
                        <input type="text" name="position" value="{{ old('position') }}" required placeholder="e.g. HR Officer"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Employee ID</label>
                        <input type="text" value="{{ $nextEmpID ?? 'Auto-generated' }}" readonly data-next-emp-id
                               class="w-full cursor-not-allowed rounded-lg border border-border/60 bg-muted/40 px-3 py-2 font-mono text-sm text-foreground focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">{{ $locCtx->item_label ?? 'Item No.' }}</label>
                        <input type="text" name="item_no" value="{{ old('item_no') }}" placeholder="N/A"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Birth Date</label>
                        <input type="date" name="bdate" id="employee-add-bdate" value="{{ old('bdate') }}" onchange="updateEmployeeAddAge()"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Age</label>
                        <input type="number" name="age" id="employee-add-age" value="{{ old('age') }}" readonly
                               class="w-full cursor-not-allowed rounded-lg border border-border/60 bg-muted/40 px-3 py-2 text-sm text-muted-foreground focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Employment Status <span class="text-red-500">*</span></label>
                        <select name="emp_status" required data-placeholder="Search employment status" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="">- Select -</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st->id }}" {{ old('emp_status') == $st->id ? 'selected' : '' }}>{{ $st->status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Sex <span class="text-red-500">*</span></label>
                        <select name="sex" required class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option value="">- Select -</option>
                            <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Civil Status</label>
                        <select name="civil_status" class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option value="">Select Civil Status</option>
                            @foreach(['Single','Married','Separated','Widowed','Other'] as $cs)
                                <option value="{{ $cs }}" {{ old('civil_status') == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Organization Email <span class="text-red-500">*</span></label></label>
                        <input type="email" name="org_email" value="{{ old('org_email') }}" placeholder="employee@organization.gov.ph"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                <button type="button"
                    onclick="document.getElementById('modal-employee-backdrop').classList.add('hidden');document.getElementById('modal-employee-backdrop').classList.remove('flex');"
                    class="rounded-lg border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                    Cancel
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90">
                    <i data-lucide="save" class="h-3.5 w-3.5"></i> Save Employee
                </button>
            </div>
        </form>
    </div>
</div>


{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     EDIT EMPLOYEE MODAL  #modal-employee-edit-backdrop
     Open with: openEditModal({id, lname, fname, mname, camp_id, emp_dept, emp_status, position})
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="modal-employee-edit-backdrop"
     class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0"
     aria-modal="true" role="dialog">

    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeEditModal()"></div>

    <div class="relative h-screen max-h-screen overflow-y-auto rounded-none border-l border-border bg-card shadow-2xl w-full max-w-xl rounded-2xl border border-border/60 bg-card shadow-2xl">

        <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
            <h3 class="inline-flex items-center gap-2 font-semibold text-foreground">
                <i data-lucide="square-pen" class="h-4 w-4 text-sky-500"></i>Edit Employee
            </h3>
            <button onclick="closeEditModal()"
                class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <form action="{{ route('empUpdate') }}" method="POST" id="employee-edit-form">
            @csrf
            <input type="hidden" name="id" id="edit-emp-id">

            <div class="p-5 space-y-4">

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="lname" id="edit-lname" required oninput="this.value=this.value.toUpperCase()"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="fname" id="edit-fname" required oninput="this.value=this.value.toUpperCase()"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Middle Name</label>
                        <input type="text" name="mname" id="edit-mname" oninput="this.value=this.value.toUpperCase()"
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                </div>

                <div class="border-t border-border/40"></div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @if($usesLocation)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">
                            <i data-lucide="{{ $locationIcon ?? 'building-2' }}" class="mr-1 inline h-3 w-3 opacity-60"></i>{{ $locCtx->label }}
                        </label>
                        <select name="camp_id" id="edit-camp-id" data-placeholder="Search {{ strtolower($locCtx->label) }}" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="">- Select {{ $locCtx->label }} -</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">{{ $locCtx->office_label ?? 'Department / Office' }} <span class="text-red-500">*</span></label>
                        <select name="emp_dept" id="edit-emp-dept" required data-placeholder="Search office or department"
                                class="employee-office-select absolute h-px w-px opacity-0">
                            <option value="">- Select Office -</option>
                            @foreach ($offices as $q)
                                <option value="{{ $q->id }}">{{ $q->office_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Position <span class="text-red-500">*</span></label>
                        <input type="text" name="position" id="edit-position" required
                               class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Employment Status <span class="text-red-500">*</span></label>
                        <select name="emp_status" id="edit-emp-status" required data-placeholder="Search employment status" class="employee-search-select absolute h-px w-px opacity-0">
                            <option value="">- Select -</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st->id }}">{{ $st->status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                <button type="button" onclick="closeEditModal()"
                    class="rounded-lg border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                    Cancel
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <i data-lucide="save" class="h-3.5 w-3.5"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window.employeeRowData = window.employeeRowData || {};

const employeeAjaxConfig = {
    csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',
    pdsBaseUrl: @json(url('/pds/personal-info')),
    leavesBaseUrl: @json(url('/leaves')),
    deleteBaseUrl: @json(url('/employees/delete')),
    editBaseUrl: @json(url('/employees/edit')),
};

function employeeToast(type, title, description = '') {
    if (window.Toast?.[type]) {
        window.Toast[type](title, description);
        return;
    }

    alert(description || title);
}

function employeeInitials(emp) {
    return `${emp.fname || ''}`.charAt(0).toUpperCase() + `${emp.lname || ''}`.charAt(0).toUpperCase();
}

function employeeFullName(emp) {
    const middle = emp.mname ? `${emp.mname.charAt(0).toUpperCase()}. ` : '';
    return `${emp.fname || ''} ${middle}${emp.lname || ''}`.trim();
}

function employeeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function employeeRowHtml(emp, rowLabel = 'New') {
    window.employeeRowData[emp.id] = emp;

    const statusLabel = employeeHtml(emp.status_name || '-');
    const officeName = employeeHtml(emp.office_name || '');
    const location = employeeHtml(emp.location_abbr || '');
    const email = employeeHtml(emp.email || '-');
    const position = employeeHtml(emp.position || '');
    const empId = employeeHtml(emp.emp_ID || '');
    const lastName = employeeHtml(emp.lname || '');
    const firstName = employeeHtml(emp.fname || '');
    const middleInitial = emp.mname ? ` ${employeeHtml(emp.mname.charAt(0).toUpperCase())}.` : '';
    const canLeave = Number(emp.emp_status) === 1;
    const name = employeeFullName(emp).replace(/'/g, "\\'");

    return `
        <tr class="group transition-colors hover:bg-muted/20" id="tr-${emp.id}">
            <td class="px-4 py-3 text-xs tabular-nums text-muted-foreground/60">${rowLabel}</td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-[11px] font-bold text-primary ring-2 ring-primary/30 select-none">
                        ${employeeInitials(emp)}
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-sm font-semibold text-foreground">${lastName}, ${firstName}${middleInitial}</p>
                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">${position}</p>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3">
                <span class="rounded-md bg-muted/70 px-2 py-1 font-mono text-[11px] text-foreground/60">${empId}</span>
            </td>
            <td class="px-4 py-3">
                <div class="space-y-1">
                    ${location ? `<span class="inline-flex items-center gap-1 rounded-md bg-muted/60 px-1.5 py-0.5 text-[11px] font-medium text-foreground/70">${location}</span>` : ''}
                    ${officeName ? `<p class="truncate text-[11px] text-muted-foreground max-w-[140px]">${officeName}</p>` : ''}
                </div>
            </td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-[11px] font-semibold text-primary ring-1 ring-primary/20">${statusLabel}</span>
            </td>
            <td class="hidden px-4 py-3 lg:table-cell"><span class="text-xs text-muted-foreground/40">-</span></td>
            <td class="hidden px-4 py-3 xl:table-cell"><span class="max-w-[160px] truncate text-[11px] text-muted-foreground block">${email}</span></td>
            <td class="px-4 py-3 text-center">
                <button type="button" id="toggle-${emp.id}" data-state="${emp.stat_1 || 0}" onclick="openToggleDialog(this, '${name}', ${emp.id})"
                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-primary/30 ${emp.stat_1 ? 'bg-primary' : 'bg-muted-foreground/25'}">
                    <span class="pointer-events-none inline-block h-3.5 w-3.5 rounded-full bg-white shadow-sm transition-transform ${emp.stat_1 ? 'translate-x-4' : 'translate-x-0'}"></span>
                </button>
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-0.5">
                    <a href="${employeeAjaxConfig.pdsBaseUrl}/${emp.pds_token || emp.id}" title="Edit Personal Information" class="rounded-lg p-2 text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-950/40"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></a>
                    ${canLeave ? `<a href="${employeeAjaxConfig.leavesBaseUrl}/${emp.id}" title="Leave Credits" class="rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50 dark:hover:bg-emerald-950/40"><i data-lucide="calendar-check" class="h-3.5 w-3.5"></i></a>` : `<span title="Leave Credits (N/A)" class="rounded-lg p-2 text-muted-foreground/25 cursor-not-allowed"><i data-lucide="calendar-check" class="h-3.5 w-3.5"></i></span>`}
                    <a href="${employeeAjaxConfig.pdsBaseUrl}/${emp.pds_token || emp.id}" title="Personal Data Sheet" class="rounded-lg p-2 text-sky-600 transition hover:bg-sky-50 dark:hover:bg-sky-950/40"><i data-lucide="id-card" class="h-3.5 w-3.5"></i></a>
                    <button type="button" title="Working Hours" onclick="openOfficialTime('${emp.emp_ID}')" class="rounded-lg p-2 text-violet-600 transition hover:bg-violet-50 dark:hover:bg-violet-950/40"><i data-lucide="clock-3" class="h-3.5 w-3.5"></i></button>
                    <button type="button" title="Delete Employee" onclick="openDeleteDialog(${emp.id}, '${name}')" class="rounded-lg p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                </div>
            </td>
        </tr>
    `;
}

function upsertEmployeeRow(emp, prepend = false) {
    const tbody = document.getElementById('employees-table-body');
    if (!tbody) return;

    tbody.querySelector('td[colspan="9"]')?.closest('tr')?.remove();
    const current = document.getElementById(`tr-${emp.id}`);
    const rowLabel = current?.children?.[0]?.textContent?.trim() || 'New';
    const wrapper = document.createElement('tbody');
    wrapper.innerHTML = employeeRowHtml(emp, rowLabel).trim();
    const row = wrapper.firstElementChild;

    if (current) {
        current.replaceWith(row);
    } else if (prepend) {
        tbody.prepend(row);
    } else {
        tbody.append(row);
    }

    window.refreshIcons?.();
}

async function submitEmployeeAjax(form, { prepend = false, close = null } = {}) {
    const submitButton = form.querySelector('[type="submit"]');
    submitButton?.setAttribute('disabled', 'disabled');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': employeeAjaxConfig.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            const firstError = data.errors ? Object.values(data.errors).flat()[0] : data.message;
            throw new Error(firstError || 'Please check the employee details.');
        }

        upsertEmployeeRow(data.employee, prepend);
        employeeToast('success', data.message || 'Employee saved.');
        close?.();

        if (prepend) {
            form.reset();
            initEmployeeOfficeSelects();
            document.querySelectorAll('[data-next-emp-id]').forEach((el) => {
                el.value = data.next_emp_id || 'Auto-generated';
            });
        }
    } catch (error) {
        employeeToast('error', 'Employee not saved', error.message);
    } finally {
        submitButton?.removeAttribute('disabled');
    }
}

function initEmployeeOfficeSelects() {
    document.querySelectorAll('.employee-office-select, .employee-search-select').forEach((select) => {
        if (select.dataset.comboboxReady === 'true') {
            syncEmployeeOfficeCombobox(select);
            return;
        }

        const combobox = document.createElement('div');
        combobox.className = 'employee-combobox relative w-full';
        combobox.innerHTML = `
            <button type="button" class="employee-combobox-trigger flex min-h-10 w-full items-center justify-between gap-2 rounded-lg border border-border/60 bg-background px-3 py-2 text-left text-sm text-foreground shadow-sm transition hover:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/30" aria-haspopup="listbox" aria-expanded="false">
                <span class="employee-combobox-value min-w-0 flex-1 truncate"></span>
                <i data-lucide="chevrons-up-down" class="h-3.5 w-3.5 shrink-0 opacity-60"></i>
            </button>
            <div class="employee-combobox-panel absolute left-0 right-0 top-full z-[80] mt-1 hidden overflow-hidden rounded-xl border border-border/70 bg-popover text-popover-foreground shadow-xl backdrop-blur">
                <div class="employee-combobox-search-wrap flex items-center gap-2 border-b border-border/60 bg-background px-3 py-2">
                    <i data-lucide="search" class="h-3.5 w-3.5 text-muted-foreground"></i>
                    <input type="text" class="employee-combobox-search h-8 min-w-0 flex-1 bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" placeholder="Search office or department" autocomplete="off" role="searchbox">
                </div>
                <div class="employee-combobox-options max-h-64 overflow-y-auto p-1" role="listbox"></div>
            </div>
        `;

        select.insertAdjacentElement('afterend', combobox);
        select.dataset.comboboxReady = 'true';

        const trigger = combobox.querySelector('.employee-combobox-trigger');
        const search = combobox.querySelector('.employee-combobox-search');

        trigger.addEventListener('click', () => openEmployeeOfficeCombobox(select));
        search.addEventListener('input', () => renderEmployeeOfficeOptions(select));
        search.addEventListener('keydown', (event) => {
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
    combobox.querySelector('.employee-combobox-value').textContent = selected?.value ? selected.text : (select.dataset.placeholder || 'Search and select');
    combobox.querySelector('.employee-combobox-value').classList.toggle('text-muted-foreground', !selected?.value);
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
    const options = Array.from(select.options)
        .filter((option) => option.value)
        .filter((option) => option.text.toLowerCase().includes(query));

    optionsBox.innerHTML = options.length
        ? options.map((option) => `
            <button type="button" class="employee-combobox-option flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-accent hover:text-accent-foreground ${option.value === select.value ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground'}" data-value="${option.value}" role="option">
                <span>${escapeEmployeeComboboxText(option.text)}</span>
                ${option.value === select.value ? '<i data-lucide="check" class="h-3.5 w-3.5"></i>' : ''}
            </button>
        `).join('')
        : '<div class="employee-combobox-empty px-3 py-6 text-center text-sm text-muted-foreground">No results found</div>';

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

function updateEmployeeAddAge() {
    const birthdateInput = document.getElementById('employee-add-bdate');
    const ageInput = document.getElementById('employee-add-age');
    if (!birthdateInput || !ageInput || !birthdateInput.value) {
        if (ageInput) ageInput.value = '';
        return;
    }

    const birthdate = new Date(birthdateInput.value);
    const today = new Date();
    let age = today.getFullYear() - birthdate.getFullYear();
    const monthDiff = today.getMonth() - birthdate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
        age--;
    }

    ageInput.value = age >= 0 ? age : '';
}

document.addEventListener('DOMContentLoaded', () => {
    updateEmployeeAddAge();
    initEmployeeOfficeSelects();
    document.getElementById('employee-create-form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitEmployeeAjax(event.currentTarget, {
            prepend: true,
            close: () => {
                document.getElementById('modal-employee-backdrop').classList.add('hidden');
                document.getElementById('modal-employee-backdrop').classList.remove('flex');
            },
        });
    });
    document.getElementById('employee-edit-form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitEmployeeAjax(event.currentTarget, { close: closeEditModal });
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.employee-combobox')) {
            document.querySelectorAll('.employee-office-select, .employee-search-select').forEach(closeEmployeeOfficeCombobox);
        }
    });
});

function closeEditModal() {
    document.getElementById('modal-employee-edit-backdrop').classList.add('hidden');
    document.getElementById('modal-employee-edit-backdrop').classList.remove('flex');
}

async function fetchEmployeeForEdit(empId) {
    const response = await fetch(`${employeeAjaxConfig.editBaseUrl}/${encodeURIComponent(empId)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    const data = await response.json();

    if (!response.ok || !data.success) {
        throw new Error(data.message || 'Employee details could not be loaded.');
    }

    window.employeeRowData[data.employee.id] = data.employee;
    return data.employee;
}

async function openEditModal(employeeRef) {
    let emp = typeof employeeRef === 'object' ? employeeRef : window.employeeRowData[employeeRef];

    if (!emp && employeeRef) {
        try {
            emp = await fetchEmployeeForEdit(employeeRef);
        } catch (error) {
            employeeToast('error', 'Edit failed', error.message);
            return;
        }
    }

    if (!emp) return;

    initEmployeeOfficeSelects();

    document.getElementById('edit-emp-id').value     = emp.id         ?? '';
    document.getElementById('edit-lname').value      = emp.lname      ?? '';
    document.getElementById('edit-fname').value      = emp.fname      ?? '';
    document.getElementById('edit-mname').value      = emp.mname      ?? '';
    document.getElementById('edit-position').value   = emp.position   ?? '';
	const campSelect = document.getElementById('edit-camp-id');
	if (campSelect) {
	    campSelect.value = emp.camp_id ?? '';
	    campSelect.dispatchEvent(new Event('change', { bubbles: true }));
	}
    const deptSelect = document.getElementById('edit-emp-dept');
    deptSelect.value = emp.emp_dept ?? '';
    deptSelect.dispatchEvent(new Event('change', { bubbles: true }));
	const statusSelect = document.getElementById('edit-emp-status');
	statusSelect.value = emp.emp_status ?? '';
	statusSelect.dispatchEvent(new Event('change', { bubbles: true }));

    document.getElementById('modal-employee-edit-backdrop').classList.remove('hidden');
    document.getElementById('modal-employee-edit-backdrop').classList.add('flex');
}
</script>


