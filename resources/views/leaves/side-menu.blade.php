@php
    use Illuminate\Support\Facades\File;

    $profileFile = trim((string) $employee->profile);
    $placeholderProfiles = ['default.png', 'default.jpg', 'default.jpeg', 'avatar.png', ''];
    $profileImagePath = 'Profile/Employee/' . $profileFile;
    $hasProfileImage = !in_array(strtolower($profileFile), $placeholderProfiles, true)
        && File::exists(public_path($profileImagePath));
    $imagePath = $hasProfileImage ? $profileImagePath : null;
    $employeeFullName = trim(ucwords(strtolower($employee->fname . ' ' . $employee->lname)));
    $employeeFullNameUpper = strtoupper($employee->fname . ' ' . $employee->lname);
    $employeeInitials = strtoupper(substr(trim((string) $employee->fname), 0, 1) . substr(trim((string) $employee->lname), 0, 1)) ?: '?';
    $selectedEmployeeLabel = trim($employee->lname . ' ' . ($employee->prefix ?? '') . ' ' . $employee->fname . ' ' . (!empty($employee->mname) ? substr($employee->mname, 0, 1) . '.' : ''));
    $isLeaveManagement = ($leaveMode ?? ($guard == 'web' ? 'management' : 'personal')) === 'management';
    $creditRows = [
        ['Vacation Leave', 'b-vl', $employee->vl],
        ['Sick Leave', 'b-sl', $employee->sl],
        ['Special Privilege Leave', 'special-pl', $employee->special_pl ?? 0],
        ['Solo Parent Leave', 'solo-pl', $employee->solo_pl ?? 0],
        ['Study Leave', 'study-leave', $employee->study_leave ?? 0],
        ['10-Day VAWC Leave', 'vawc-leave', $employee->vawc_leave ?? 0],
        ['Rehabilitation Privilege', 'rehab-leave', $employee->rehab_leave ?? 0],
        ['Special Leave Benefits for Women', 'benefits-leave', $employee->benefits_leave ?? 0],
        ['Special Emergency Leave', 'calamity-leave', $employee->calamity_leave ?? 0],
        ['Adoption Leave', 'adopt-leave', $employee->adopt_leave ?? 0],
        ['Vacation Service Credit', 'servcred-leave', $employee->servcred_leave ?? 0],
        ['Wellness Leave', 'wellness-leave', $employee->well_leave ?? 0],
    ];
@endphp

<div class="min-w-0">
    <aside class="sticky top-4 overflow-hidden rounded-lg border border-border/60 border-t-4 border-t-primary bg-card shadow-sm">
        
        @if($guard == "web" && $isLeaveManagement)
        <div class="border-b border-border/60 p-5">
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground" for="leave-employee-search">Employee</label>
            <input type="hidden" id="employee" value="{{ $employee->id }}">
            <div class="relative" data-leave-employee-combobox>
                <input type="text"
                    id="leave-employee-search"
                    value="{{ $selectedEmployeeLabel }}"
                    placeholder="Search employee"
                    autocomplete="off"
                    class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 pr-9 text-sm text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    data-leave-employee-search>
                <i data-lucide="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <div class="absolute z-40 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-xl border border-border/60 bg-card p-1 shadow-xl" data-leave-employee-options>
                    @foreach ($emplalls as $emp)
                        @php
                            $empLabel = trim($emp->lname . ' ' . ($emp->prefix ?? '') . ' ' . $emp->fname . ' ' . (!empty($emp->mname) ? substr($emp->mname, 0, 1) . '.' : ''));
                        @endphp
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-foreground hover:bg-muted"
                            data-leave-employee-option
                            data-value="{{ $emp->id }}"
                            data-employee-id="{{ $emp->emp_ID }}"
                            data-label="{{ $empLabel }}">
                            <span class="truncate">{{ $empLabel }}</span>
                            <span class="ml-2 shrink-0 font-mono text-[11px] text-muted-foreground">{{ $emp->emp_ID }}</span>
                        </button>
                    @endforeach
                    <div class="hidden px-3 py-2 text-xs text-muted-foreground" data-leave-employee-empty>No employees found</div>
                </div>
            </div>
        </div>
        @endif

        <div class="p-5">
            <div class="flex flex-col items-center text-center">
                @if($imagePath)
                    <img src="{{ asset($imagePath) }}" alt="User Image" class="h-24 w-24 rounded-full object-cover ring-4 ring-primary/10" id="changeProfilePicture">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-primary/10 text-2xl font-bold text-primary ring-4 ring-primary/10" id="changeProfilePicture" title="{{ $employeeFullNameUpper }}">
                        {{ $employeeInitials }}
                    </div>
                @endif
                <input type="file" id="profilePictureInput" class="hidden" accept="image/*">
                <h3 class="mt-3 text-sm font-semibold text-foreground">{{ $employeeFullName }}</h3>
                <p class="mt-1 text-xs text-muted-foreground">{{ $employee->position }}</p>
            </div>

            <div class="mt-5 flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Credit Balances</p>
                @if($guard == "web" && $isLeaveManagement)
                    <button type="button" data-toggle="modal" data-target="#modalSettingLeave" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted" title="Leave settings">
                        <i data-lucide="settings" class="h-3.5 w-3.5"></i>
                    </button>
                @endif
            </div>

            <div class="mt-3 divide-y divide-border/60 bg-background">
                @foreach($creditRows as [$label, $id, $value])
                <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                    <span class="min-w-0 text-xs font-medium leading-snug text-foreground">{{ $label }}</span>
                    <span id="{{ $id }}" class="shrink-0 font-mono text-xs font-semibold tabular-nums text-primary">{{ number_format((float) $value, 3) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </aside>
</div>

@if($guard == "web" && $isLeaveManagement)
@push('scripts')
<script>
    function initializeLeaveEmployeeSearch() {
        const root = document.querySelector('[data-leave-employee-combobox]');
        if (!root || root.dataset.leaveEmployeeReady === '1') {
            return;
        }

        root.dataset.leaveEmployeeReady = '1';

        const hidden = document.getElementById('employee');
        const search = root.querySelector('[data-leave-employee-search]');
        const menu = root.querySelector('[data-leave-employee-options]');
        const options = Array.from(root.querySelectorAll('[data-leave-employee-option]'));
        const empty = root.querySelector('[data-leave-employee-empty]');

        function openMenu() {
            menu.classList.remove('hidden');
        }

        function closeMenu() {
            menu.classList.add('hidden');
        }

        function filterOptions() {
            const term = search.value.trim().toLowerCase();
            let visible = 0;

            options.forEach(function (option) {
                const haystack = `${option.dataset.label || ''} ${option.dataset.value || ''} ${option.dataset.employeeId || ''}`.toLowerCase();
                const matches = !term || haystack.includes(term);
                option.classList.toggle('hidden', !matches);
                if (matches) {
                    visible++;
                }
            });

            empty.classList.toggle('hidden', visible > 0);
            openMenu();
        }

        search.addEventListener('focus', filterOptions);
        search.addEventListener('input', filterOptions);

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                hidden.value = option.dataset.value || '';
                search.value = option.dataset.label || '';
                closeMenu();
                redirectToSelectedLeaveEmployee(hidden.value);
            });
        });

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                closeMenu();
            }
        });

        search.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            const firstVisible = options.find(function (option) {
                return !option.classList.contains('hidden');
            });

            if (firstVisible) {
                hidden.value = firstVisible.dataset.value || '';
                search.value = firstVisible.dataset.label || '';
                closeMenu();
                redirectToSelectedLeaveEmployee(hidden.value);
            }
        });
    }

    function redirectToSelectedLeaveEmployee(employeeId) {
        if (!employeeId || String(employeeId) === '{{ $employee->id }}') {
            return;
        }

        const currentPath = window.location.pathname;
        let targetUrl = '{{ route("leavesRead", ":id") }}'.replace(':id', employeeId);

        if (currentPath.includes('/status')) {
            targetUrl = '{{ route("leaveStatus", ":id") }}'.replace(':id', employeeId);
        } else if (currentPath.includes('/history')) {
            targetUrl = '{{ route("historyRead", ":id") }}'.replace(':id', employeeId);
        }

        window.location.href = targetUrl;
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeLeaveEmployeeSearch();
        window.setTimeout(initializeLeaveEmployeeSearch, 300);
    });
</script>
@endpush
@endif
