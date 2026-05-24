@extends('layouts.master')

@section('body')
@php
    $roleTone = [
        'Administrator' => 'text-rose-600 bg-rose-50 ring-rose-200 dark:bg-rose-950/30 dark:text-rose-300 dark:ring-rose-800/50',
        'HR Administrator' => 'text-sky-600 bg-sky-50 ring-sky-200 dark:bg-sky-950/30 dark:text-sky-300 dark:ring-sky-800/50',
        'Payroll Administrator' => 'text-emerald-600 bg-emerald-50 ring-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-800/50',
        'HR Staff' => 'text-violet-600 bg-violet-50 ring-violet-200 dark:bg-violet-950/30 dark:text-violet-300 dark:ring-violet-800/50',
        'Payroll Staff' => 'text-amber-600 bg-amber-50 ring-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:ring-amber-800/50',
    ];
@endphp

<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-foreground">User Management</h1>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                    {{ number_format($stats['total']) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="shield-check" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>
                Employee-linked access control for system modules
            </p>
        </div>
        <button type="button" onclick="openUserDrawer()"
            class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-primary/90 hover:shadow-md sm:shrink-0">
            <i data-lucide="user-plus" class="h-3.5 w-3.5"></i> Add User
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 {{ $canManageAdministrators ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
        <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Total Users</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="users" class="h-4 w-4"></i>
                </div>
            </div>
        </div>
        @if($canManageAdministrators)
        <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Administrators</p>
            <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['admins']) }}</p>
        </div>
        @endif
        <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">HR Users</p>
            <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['hr']) }}</p>
        </div>
        <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Payroll Users</p>
            <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['payroll']) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
        <div class="flex flex-col gap-3 border-b border-border/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative max-w-sm flex-1">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"></i>
                <input type="search" id="user-search" placeholder="Search name, role, email..."
                    class="w-full rounded-xl border border-border/60 bg-background py-2 pl-8 pr-3 text-xs text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="text-xs text-muted-foreground">
                {{ $canManageAdministrators ? 'Administrator has full access. Other roles follow checked permissions.' : 'Administrator accounts are hidden. Other roles follow checked permissions.' }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border/60 bg-muted/25 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                        <th class="w-10 px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Login Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Access</th>
                        <th class="w-24 px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="divide-y divide-border/40">
                    @forelse($users as $user)
                        @php
                            $menuKeysForUser = $user->role === 'Administrator' ? $menuKeys : ($user->menuPermission?->menu_keys ?? []);
                            $avatarProfile = $user->employee?->profile;
                        @endphp
                        <tr id="user-tr-{{ $user->id }}" class="group transition-colors hover:bg-muted/20"
                            data-user-search="{{ strtolower($user->fname.' '.$user->mname.' '.$user->lname.' '.$user->email.' '.$user->role.' '.$user->emp_ID) }}">
                            <td class="px-4 py-3 text-xs tabular-nums text-muted-foreground/60">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-avatar :profile="$avatarProfile" :fname="$user->fname" :lname="$user->lname"
                                        class="h-9 w-9 rounded-full object-cover text-[11px] ring-2 ring-primary/30" />
                                    <div class="min-w-0 leading-tight">
                                        <p class="truncate text-sm font-semibold text-foreground">{{ $user->lname }}, {{ $user->fname }}{{ $user->mname ? ' '.strtoupper(substr($user->mname, 0, 1)).'.' : '' }}</p>
                                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{{ $user->employee?->position ?: ($user->emp_ID ? 'Employee account' : 'System account') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="block max-w-[220px] truncate text-xs text-muted-foreground">{{ $user->email }}</span>
                                @if($user->emp_ID)
                                    <span class="mt-1 inline-flex rounded-md bg-muted/70 px-2 py-0.5 font-mono text-[10px] text-foreground/60">{{ $user->emp_ID }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $roleTone[$user->role] ?? 'bg-muted text-muted-foreground ring-border' }}">{{ $user->role }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($user->role === 'Administrator')
                                        <span class="rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Full Access</span>
                                    @elseif(count($menuKeysForUser))
                                        @foreach(array_slice($menuKeysForUser, 0, 4) as $key)
                                            <span class="rounded-md bg-muted/70 px-2 py-0.5 text-[10px] font-medium text-muted-foreground">{{ collect($menuGroups)->flatten()->get($key) ?? $key }}</span>
                                        @endforeach
                                        @if(count($menuKeysForUser) > 4)
                                            <span class="rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">+{{ count($menuKeysForUser) - 4 }}</span>
                                        @endif
                                    @else
                                        <span class="text-[11px] text-muted-foreground/60">No access selected</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-0.5">
                                    <button type="button" onclick="editUser({{ $user->id }})" title="Edit User"
                                        class="rounded-lg p-2 text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <button type="button" onclick="openDeleteUser({{ $user->id }}, '{{ addslashes($user->fname.' '.$user->lname) }}')" title="Delete User"
                                        class="rounded-lg p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-muted">
                                    <i data-lucide="users" class="h-6 w-6 text-muted-foreground/40"></i>
                                </div>
                                <p class="mt-4 font-semibold text-foreground">No users found</p>
                                <p class="mt-1 text-xs text-muted-foreground">Create your first system user.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="user-drawer" class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/30" onclick="closeUserDrawer()"></div>
    <div class="right-corner-modal relative flex h-full max-h-screen w-full max-w-2xl flex-col overflow-hidden rounded-none border-l border-border/60 bg-card shadow-2xl sm:rounded-l-2xl">
        <div class="shrink-0 flex items-start justify-between border-b border-border/60 px-5 py-4">
            <div>
                <h3 class="inline-flex items-center gap-2 font-semibold text-foreground">
                    <i data-lucide="user-cog" class="h-4 w-4 text-primary"></i>
                    <span id="drawer-title">Create User</span>
                </h3>
                <p class="mt-0.5 text-xs text-muted-foreground">Employee-linked users use the employee email as login.</p>
            </div>
            <button type="button" onclick="closeUserDrawer()" class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <form id="user-form" method="POST" action="{{ route('uCreate') }}" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <input type="hidden" name="uid" id="user-id">
            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-foreground">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="user-role" required onchange="handleRoleChange()"
                        class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Select role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="employee-picker-block">
                    <label class="mb-1.5 block text-xs font-medium text-foreground">Employee <span class="text-red-500">*</span></label>
                    <select name="emp_ID" id="user-employee" onchange="fillFromEmployee()"
                        class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Select employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee['emp_ID'] }}">{{ $employee['label'] }} - {{ $employee['position'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="fname" id="user-fname" required oninput="this.value=this.value.toUpperCase()"
                            class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Middle Name</label>
                        <input type="text" name="mname" id="user-mname" oninput="this.value=this.value.toUpperCase()"
                            class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" name="lname" id="user-lname" required oninput="this.value=this.value.toUpperCase()"
                            class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Gender <span class="text-red-500">*</span></label>
                        <select name="gender" id="user-gender" required
                            class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-foreground">Login Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="user-email" required
                            class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-foreground">Password <span id="password-required" class="text-red-500">*</span></label>
                    <input type="password" name="password" id="user-password" autocomplete="new-password"
                        class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <p class="mt-1 text-xs text-muted-foreground" id="password-help">
                        {{ $canManageAdministrators ? 'Required for Administrator. Employee-linked users default to their Employee ID when blank.' : 'Employee-linked users default to their Employee ID when blank.' }}
                    </p>
                </div>

                <div id="permission-block" class="rounded-2xl border border-border/60 bg-muted/10">
                    <div class="flex flex-col gap-3 border-b border-border/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-foreground">Page Access</p>
                            <p class="text-xs text-muted-foreground">Check all pages this user can access.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="setAllPermissions(true)" class="rounded-lg border border-border/60 px-3 py-1.5 text-xs font-medium text-foreground hover:bg-muted">Select All</button>
                            <button type="button" onclick="setAllPermissions(false)" class="rounded-lg border border-border/60 px-3 py-1.5 text-xs font-medium text-foreground hover:bg-muted">Clear</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                        @foreach($menuGroups as $group => $items)
                            <div class="rounded-xl border border-border/50 bg-background/60 p-3">
                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ $group }}</p>
                                <div class="space-y-2">
                                    @foreach($items as $key => $label)
                                        <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-foreground transition hover:bg-muted/60">
                                            <input type="checkbox" name="menu_keys[]" value="{{ $key }}"
                                                class="access-check h-4 w-4 rounded border-border text-primary focus:ring-primary/30">
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="shrink-0 flex justify-end gap-2 border-t border-border/60 bg-card px-5 py-4">
                <button type="button" onclick="closeUserDrawer()" class="rounded-lg border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90">
                    <i data-lucide="save" class="h-3.5 w-3.5"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

<div id="delete-user-drawer" class="fixed inset-0 z-50 hidden items-stretch justify-end overflow-hidden p-0" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/30" onclick="closeDeleteUser()"></div>
    <div class="right-corner-modal relative w-full max-w-xs rounded-2xl border border-border/60 bg-card shadow-2xl">
        <div class="p-6">
            <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-red-100 dark:bg-red-900/30">
                <i data-lucide="trash-2" class="h-5 w-5 text-red-600 dark:text-red-400"></i>
            </div>
            <h3 class="font-semibold text-foreground">Delete User</h3>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground" id="delete-user-message"></p>
        </div>
        <div class="flex justify-end gap-2 border-t border-border/60 px-6 py-4">
            <button type="button" onclick="closeDeleteUser()" class="rounded-xl border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">Cancel</button>
            <button type="button" id="confirm-delete-user" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<script>
const userConfig = {
    createUrl: @json(route('uCreate')),
    updateUrl: @json(route('uUpdate')),
    editBaseUrl: @json(url('/user/edit')),
    deleteUrl: @json(route('uDelete')),
    csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',
    rolesEmployee: @json($employeeRoles),
    canManageAdministrators: @json($canManageAdministrators),
    menuGroups: @json($menuGroups),
};
const employeesById = @json($employees->keyBy('emp_ID'));
let currentDeleteUserId = null;

function userToast(type, title, description = '') {
    if (window.Toast?.[type]) return window.Toast[type](title, description);
    alert(description || title);
}

function userHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function userInitials(user) {
    return `${user.fname || ''}`.charAt(0).toUpperCase() + `${user.lname || ''}`.charAt(0).toUpperCase();
}

function menuLabel(key) {
    for (const group of Object.values(userConfig.menuGroups)) {
        if (group[key]) return group[key];
    }
    return key;
}

function roleClass(role) {
    const tones = {
        'Administrator': 'text-rose-600 bg-rose-50 ring-rose-200 dark:bg-rose-950/30 dark:text-rose-300 dark:ring-rose-800/50',
        'HR Administrator': 'text-sky-600 bg-sky-50 ring-sky-200 dark:bg-sky-950/30 dark:text-sky-300 dark:ring-sky-800/50',
        'Payroll Administrator': 'text-emerald-600 bg-emerald-50 ring-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-800/50',
        'HR Staff': 'text-violet-600 bg-violet-50 ring-violet-200 dark:bg-violet-950/30 dark:text-violet-300 dark:ring-violet-800/50',
        'Payroll Staff': 'text-amber-600 bg-amber-50 ring-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:ring-amber-800/50',
    };
    return tones[role] || 'bg-muted text-muted-foreground ring-border';
}

function userAccessHtml(user) {
    if (user.role === 'Administrator') {
        return '<span class="rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Full Access</span>';
    }
    const keys = user.menu_keys || [];
    if (!keys.length) return '<span class="text-[11px] text-muted-foreground/60">No access selected</span>';
    const visible = keys.slice(0, 4).map((key) => `<span class="rounded-md bg-muted/70 px-2 py-0.5 text-[10px] font-medium text-muted-foreground">${userHtml(menuLabel(key))}</span>`).join('');
    return keys.length > 4 ? `${visible}<span class="rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">+${keys.length - 4}</span>` : visible;
}

function userRowHtml(user, rowNumber = 'New') {
    const fullName = `${user.fname || ''} ${user.lname || ''}`.trim().replace(/'/g, "\\'");
    const search = `${user.fname || ''} ${user.mname || ''} ${user.lname || ''} ${user.email || ''} ${user.role || ''} ${user.emp_ID || ''}`.toLowerCase();
    return `
        <tr id="user-tr-${user.id}" class="group transition-colors hover:bg-muted/20" data-user-search="${userHtml(search)}">
            <td class="px-4 py-3 text-xs tabular-nums text-muted-foreground/60">${rowNumber}</td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white ring-2 ring-primary/30">${userHtml(userInitials(user))}</span>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-sm font-semibold text-foreground">${userHtml(user.lname)}, ${userHtml(user.fname)}${user.mname ? ' ' + userHtml(user.mname.charAt(0).toUpperCase()) + '.' : ''}</p>
                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">${userHtml(user.employee_position || (user.emp_ID ? 'Employee account' : 'System account'))}</p>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3">
                <span class="block max-w-[220px] truncate text-xs text-muted-foreground">${userHtml(user.email)}</span>
                ${user.emp_ID ? `<span class="mt-1 inline-flex rounded-md bg-muted/70 px-2 py-0.5 font-mono text-[10px] text-foreground/60">${userHtml(user.emp_ID)}</span>` : ''}
            </td>
            <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ${roleClass(user.role)}">${userHtml(user.role)}</span></td>
            <td class="px-4 py-3"><div class="flex flex-wrap gap-1.5">${userAccessHtml(user)}</div></td>
            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-0.5">
                    <button type="button" onclick="editUser(${user.id})" title="Edit User" class="rounded-lg p-2 text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-950/40"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button>
                    <button type="button" onclick="openDeleteUser(${user.id}, '${fullName}')" title="Delete User" class="rounded-lg p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                </div>
            </td>
        </tr>
    `;
}

function upsertUserRow(user, prepend = false) {
    const tbody = document.getElementById('users-table-body');
    const current = document.getElementById(`user-tr-${user.id}`);
    const rowNumber = current?.children?.[0]?.textContent?.trim() || 'New';
    const wrapper = document.createElement('tbody');
    wrapper.innerHTML = userRowHtml(user, rowNumber).trim();
    const row = wrapper.firstElementChild;
    if (current) current.replaceWith(row);
    else if (prepend) tbody.prepend(row);
    else tbody.append(row);
    window.refreshIcons?.();
}

function setReadonlyForEmployeeLinked(isLinked) {
    ['user-fname', 'user-mname', 'user-lname', 'user-gender', 'user-email'].forEach((id) => {
        const el = document.getElementById(id);
        el.toggleAttribute('readonly', isLinked && el.tagName !== 'SELECT');
        el.toggleAttribute('disabled', isLinked && el.tagName === 'SELECT');
        el.classList.toggle('bg-muted/40', isLinked);
    });
}

function handleRoleChange() {
    const role = document.getElementById('user-role').value;
    const isAdmin = role === 'Administrator';
    document.getElementById('employee-picker-block').classList.toggle('hidden', isAdmin);
    document.getElementById('permission-block').classList.toggle('hidden', isAdmin);
    document.getElementById('password-required').classList.toggle('hidden', !isAdmin);
    document.getElementById('user-password').required = isAdmin && !document.getElementById('user-id').value;
    setReadonlyForEmployeeLinked(!isAdmin);
    if (isAdmin) {
        document.getElementById('user-employee').value = '';
    } else {
        fillFromEmployee();
    }
}

function fillFromEmployee() {
    const empId = document.getElementById('user-employee').value;
    const employee = employeesById[empId];
    if (!employee) return;
    document.getElementById('user-fname').value = employee.fname || '';
    document.getElementById('user-mname').value = employee.mname || '';
    document.getElementById('user-lname').value = employee.lname || '';
    document.getElementById('user-gender').value = employee.gender || '';
    document.getElementById('user-email').value = employee.email || '';
}

function setAllPermissions(state) {
    document.querySelectorAll('.access-check').forEach((checkbox) => checkbox.checked = state);
}

function setPermissions(keys = []) {
    document.querySelectorAll('.access-check').forEach((checkbox) => checkbox.checked = keys.includes(checkbox.value));
}

function resetUserForm() {
    const form = document.getElementById('user-form');
    form.reset();
    form.action = userConfig.createUrl;
    document.getElementById('user-id').value = '';
    document.getElementById('drawer-title').textContent = 'Create User';
    setPermissions([]);
    handleRoleChange();
}

function openUserDrawer(user = null) {
    resetUserForm();
    if (user) {
        document.getElementById('drawer-title').textContent = 'Edit User';
        document.getElementById('user-form').action = userConfig.updateUrl;
        document.getElementById('user-id').value = user.id;
        document.getElementById('user-role').value = user.role;
        document.getElementById('user-employee').value = user.emp_ID || '';
        document.getElementById('user-fname').value = user.fname || '';
        document.getElementById('user-mname').value = user.mname || '';
        document.getElementById('user-lname').value = user.lname || '';
        document.getElementById('user-gender').value = user.gender || '';
        document.getElementById('user-email').value = user.email || '';
        setPermissions(user.menu_keys || []);
        handleRoleChange();
        document.getElementById('user-password').required = false;
    }
    document.getElementById('user-drawer').classList.replace('hidden', 'flex');
}

function closeUserDrawer() {
    document.getElementById('user-drawer').classList.replace('flex', 'hidden');
}

async function editUser(id) {
    try {
        const response = await fetch(`${userConfig.editBaseUrl}/${encodeURIComponent(id)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Unable to load user.');
        openUserDrawer(data.user);
    } catch (error) {
        userToast('error', 'User not loaded', error.message);
    }
}

function openDeleteUser(id, name) {
    currentDeleteUserId = id;
    document.getElementById('delete-user-message').innerHTML = `Are you sure you want to delete <strong>${userHtml(name)}</strong>?`;
    document.getElementById('delete-user-drawer').classList.replace('hidden', 'flex');
}

function closeDeleteUser() {
    currentDeleteUserId = null;
    document.getElementById('delete-user-drawer').classList.replace('flex', 'hidden');
}

document.getElementById('user-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const disabled = Array.from(form.querySelectorAll(':disabled'));
    disabled.forEach((el) => el.disabled = false);
    const submitButton = form.querySelector('[type="submit"]');
    submitButton.disabled = true;
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': userConfig.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            const firstError = data.errors ? Object.values(data.errors).flat()[0] : data.message;
            throw new Error(firstError || 'Unable to save user.');
        }
        upsertUserRow(data.user, !document.getElementById('user-id').value);
        closeUserDrawer();
        userToast('success', data.message || 'User saved.');
    } catch (error) {
        userToast('error', 'User not saved', error.message);
    } finally {
        disabled.forEach((el) => el.disabled = true);
        submitButton.disabled = false;
    }
});

document.getElementById('confirm-delete-user')?.addEventListener('click', async () => {
    if (!currentDeleteUserId) return;
    try {
        const formData = new FormData();
        formData.append('id', currentDeleteUserId);
        const response = await fetch(userConfig.deleteUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': userConfig.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Unable to delete user.');
        document.getElementById(`user-tr-${data.id}`)?.remove();
        closeDeleteUser();
        userToast('success', data.message || 'User deleted.');
    } catch (error) {
        userToast('error', 'Delete failed', error.message);
    }
});

document.getElementById('user-search')?.addEventListener('input', (event) => {
    const query = event.target.value.trim().toLowerCase();
    document.querySelectorAll('#users-table-body tr[id^="user-tr-"]').forEach((row) => {
        row.classList.toggle('hidden', query && !row.dataset.userSearch.includes(query));
    });
});

handleRoleChange();
</script>
@endsection
