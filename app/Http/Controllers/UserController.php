<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private array $roles = [
        'Administrator',
        'HR Administrator',
        'Payroll Administrator',
        'HR Staff',
        'Payroll Staff',
    ];

    private function getGuard(): string
    {
        return auth()->guard('web')->check() ? 'web' : 'employee';
    }

    private function employeeRoles(): array
    {
        return array_values(array_filter($this->roles, fn ($role) => $role !== 'Administrator'));
    }

    private function menuKeys(Request $request): array
    {
        return collect($request->input('menu_keys', []))
            ->intersect(MenuHelper::keys())
            ->values()
            ->all();
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing(['employee', 'menuPermission']);
        $employee = $user->employee;
        $menuKeys = $user->role === 'Administrator'
            ? MenuHelper::keys()
            : ($user->menuPermission?->menu_keys ?? []);

        return [
            'id' => $user->id,
            'fname' => $user->fname,
            'mname' => $user->mname,
            'lname' => $user->lname,
            'gender' => $user->gender,
            'email' => $user->email,
            'role' => $user->role,
            'emp_ID' => $user->emp_ID,
            'employee_name' => $employee ? trim("{$employee->fname} {$employee->lname}") : null,
            'employee_position' => $employee?->position,
            'employee_profile' => $employee?->profile,
            'menu_keys' => $menuKeys,
            'access_count' => count($menuKeys),
            'created_at' => optional($user->created_at)->format('M d, Y'),
        ];
    }

    private function employeesForSelection()
    {
        return Employee::query()
            ->select('id', 'emp_ID', 'fname', 'mname', 'lname', 'sex', 'position', 'org_email', 'email', 'profile')
            ->orderBy('lname')
            ->orderBy('fname')
            ->get()
            ->map(fn ($employee) => [
                'id' => $employee->id,
                'emp_ID' => $employee->emp_ID,
                'fname' => $employee->fname,
                'mname' => $employee->mname,
                'lname' => $employee->lname,
                'gender' => $employee->sex,
                'position' => $employee->position,
                'email' => $employee->org_email ?: $employee->email,
                'profile' => $employee->profile,
                'label' => trim("{$employee->lname}, {$employee->fname}") . " ({$employee->emp_ID})",
            ]);
    }

    public function ulist()
    {
        $guard = $this->getGuard();
        $users = User::with(['employee', 'menuPermission'])
            ->orderByRaw("role = 'Administrator' desc")
            ->orderBy('lname')
            ->get();
        $employees = $this->employeesForSelection();
        $roles = $this->roles;
        $employeeRoles = $this->employeeRoles();
        $menuGroups = MenuHelper::grouped();
        $menuKeys = MenuHelper::keys();
        $stats = [
            'total' => $users->count(),
            'admins' => $users->where('role', 'Administrator')->count(),
            'hr' => $users->filter(fn ($user) => str_starts_with($user->role, 'HR'))->count(),
            'payroll' => $users->filter(fn ($user) => str_starts_with($user->role, 'Payroll'))->count(),
            'linked' => $users->whereNotNull('emp_ID')->count(),
        ];

        return view('users.user-list', compact(
            'guard',
            'users',
            'employees',
            'roles',
            'employeeRoles',
            'menuGroups',
            'menuKeys',
            'stats'
        ));
    }

    public function uCreate(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in($this->roles)],
            'emp_ID' => [
                Rule::requiredIf(fn () => in_array($request->role, $this->employeeRoles(), true)),
                'nullable',
                'exists:employees,emp_ID',
                'unique:users,emp_ID',
            ],
            'fname' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'string', 'max:80'],
            'mname' => ['nullable', 'string', 'max:80'],
            'lname' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'string', 'max:80'],
            'gender' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'in:Male,Female'],
            'email' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'email', 'unique:users,email'],
            'password' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'min:6'],
            'menu_keys' => ['nullable', 'array'],
            'menu_keys.*' => ['string', Rule::in(MenuHelper::keys())],
        ]);

        $employee = null;
        if ($validated['role'] !== 'Administrator') {
            $employee = Employee::where('emp_ID', $validated['emp_ID'])->firstOrFail();
            $email = $employee->org_email ?: $employee->email;

            if (!$email) {
                return response()->json([
                    'message' => 'Selected employee has no organization email to use for login.',
                    'errors' => ['emp_ID' => ['Selected employee has no organization email to use for login.']],
                ], 422);
            }

            if (User::where('email', $email)->exists()) {
                return response()->json([
                    'message' => 'Selected employee email is already used by another user.',
                    'errors' => ['emp_ID' => ['Selected employee email is already used by another user.']],
                ], 422);
            }
        }

        $user = DB::transaction(function () use ($request, $validated, $employee) {
            $user = User::create([
                'fname' => $employee ? strtoupper($employee->fname) : strtoupper($validated['fname']),
                'mname' => $employee ? ($employee->mname ? strtoupper($employee->mname) : null) : ($validated['mname'] ? strtoupper($validated['mname']) : null),
                'lname' => $employee ? strtoupper($employee->lname) : strtoupper($validated['lname']),
                'gender' => $employee ? $employee->sex : $validated['gender'],
                'email' => $employee ? ($employee->org_email ?: $employee->email) : $validated['email'],
                'password' => Hash::make($validated['password'] ?: $employee->emp_ID),
                'role' => $validated['role'],
                'emp_ID' => $employee?->emp_ID,
                'access' => null,
            ]);

            if ($user->role !== 'Administrator') {
                UserMenuPermission::updateOrCreate(
                    ['user_id' => $user->id],
                    ['menu_keys' => $this->menuKeys($request)]
                );
            }

            return $user->fresh(['employee', 'menuPermission']);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function uEdit(int $id)
    {
        $user = User::with(['employee', 'menuPermission'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),
        ]);
    }

    public function uUpdate(Request $request)
    {
        $user = User::findOrFail($request->input('uid'));

        $validated = $request->validate([
            'uid' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in($this->roles)],
            'emp_ID' => [
                Rule::requiredIf(fn () => in_array($request->role, $this->employeeRoles(), true)),
                'nullable',
                'exists:employees,emp_ID',
                Rule::unique('users', 'emp_ID')->ignore($user->id),
            ],
            'fname' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'string', 'max:80'],
            'mname' => ['nullable', 'string', 'max:80'],
            'lname' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'string', 'max:80'],
            'gender' => [Rule::requiredIf(fn () => $request->role === 'Administrator'), 'nullable', 'in:Male,Female'],
            'email' => [
                Rule::requiredIf(fn () => $request->role === 'Administrator'),
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'min:6'],
            'menu_keys' => ['nullable', 'array'],
            'menu_keys.*' => ['string', Rule::in(MenuHelper::keys())],
        ]);

        $employee = null;
        if ($validated['role'] !== 'Administrator') {
            $employee = Employee::where('emp_ID', $validated['emp_ID'])->firstOrFail();
            $email = $employee->org_email ?: $employee->email;

            if (!$email) {
                return response()->json([
                    'message' => 'Selected employee has no organization email to use for login.',
                    'errors' => ['emp_ID' => ['Selected employee has no organization email to use for login.']],
                ], 422);
            }

            $emailTaken = User::where('email', $email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailTaken) {
                return response()->json([
                    'message' => 'Selected employee email is already used by another user.',
                    'errors' => ['emp_ID' => ['Selected employee email is already used by another user.']],
                ], 422);
            }
        }

        DB::transaction(function () use ($request, $validated, $user, $employee) {
            $data = [
                'fname' => $employee ? strtoupper($employee->fname) : strtoupper($validated['fname']),
                'mname' => $employee ? ($employee->mname ? strtoupper($employee->mname) : null) : ($validated['mname'] ? strtoupper($validated['mname']) : null),
                'lname' => $employee ? strtoupper($employee->lname) : strtoupper($validated['lname']),
                'gender' => $employee ? $employee->sex : $validated['gender'],
                'email' => $employee ? ($employee->org_email ?: $employee->email) : $validated['email'],
                'role' => $validated['role'],
                'emp_ID' => $employee?->emp_ID,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($user->role === 'Administrator') {
                UserMenuPermission::where('user_id', $user->id)->delete();
            } else {
                UserMenuPermission::updateOrCreate(
                    ['user_id' => $user->id],
                    ['menu_keys' => $this->menuKeys($request)]
                );
            }
        });

        $user = $user->fresh(['employee', 'menuPermission']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function uDelete(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['success' => false, 'status' => 404, 'message' => 'User not found.'], 404);
        }

        if ($user->id === auth()->guard('web')->id()) {
            return response()->json(['success' => false, 'status' => 403, 'message' => 'Cannot delete your own account.'], 403);
        }

        DB::transaction(function () use ($user) {
            UserMenuPermission::where('user_id', $user->id)->delete();
            $user->delete();
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'User deleted successfully.',
            'id' => $user->id,
        ]);
    }

    public function myAccount()
    {
        $guard = $this->getGuard();
        $user  = auth()->guard('web')->user();
        return view('account.my-account', compact('user', 'guard'));
    }
}
