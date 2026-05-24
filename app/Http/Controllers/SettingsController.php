<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;
use App\Models\Employee;
use App\Models\MenuSetting;
use App\Models\Setting;
use App\Models\Status;
use App\Models\User;
use App\Models\UserMenuPermission;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $this->ensureMenuSettings();

        $setting = Setting::singleton();
        $employees = Employee::select('id', 'emp_ID', 'fname', 'lname', 'position')
                              ->orderBy('lname')
                              ->get();

        $menuSettings = MenuSetting::orderBy('sort_order')->get()->groupBy('group');

        $users = User::select('id', 'fname', 'lname', 'role', 'emp_ID', 'email')
                     ->orderBy('lname')
                     ->get()
                     ->map(function ($u) {
                         $perm = UserMenuPermission::where('user_id', $u->id)->first();
                         $u->menuPermission = $perm;
                         return $u;
                     });

        $orgConfig    = config('organization', []);
        $empTypes     = $this->empTypeOptions();
        $enabledTypes = $setting->emp_types ?? [];
        $leadershipLabels = $this->leadershipLabels(); // ADDED

        return view('settings.index', compact(
            'setting', 'employees', 'menuSettings', 'users',
            'orgConfig', 'empTypes', 'enabledTypes', 'leadershipLabels'
        ));
    }

    /**
     * Save Organization Settings (AJAX)
     */
    public function saveOrg(Request $request)
    {
        $data = $request->validate([
            'org_name'   => 'nullable|string|max:150',
            'sector'     => 'nullable|string|in:government,education,private,industry',
            'org_type'   => 'nullable|string|max:50',
            'emp_types'  => 'nullable|array',
            'emp_types.*'=> 'string',
        ]);

        if (!isset($data['emp_types'])) {
            $data['emp_types'] = [];
        }

        Setting::singleton()->update($data);
        $this->ensureEmploymentStatuses($data['emp_types'], $data['sector'] ?? Setting::singleton()->sector);

        return $this->settingsResponse($request, 'Organization profile saved.');
    }

    /**
     * Save General Settings (AJAX)
     */
    public function saveGeneral(Request $request)
    {
        $data = $request->validate([
            'suc_pres'             => 'nullable|integer',
            'vpaa'                 => 'nullable|integer',
            'vpaf'                 => 'nullable|integer',
            'hr'                   => 'nullable|integer',
            'hr_head_email'        => 'nullable|email',
            'records_office_email' => 'nullable|email',
            'job_portal_email'     => 'nullable|email',
            'system_name'          => 'nullable|string|max:100',
            'employee_id_prefix'   => 'nullable|string|max:20|regex:/^[A-Za-z0-9]*$/',
            'maintenance'          => 'nullable|boolean',
        ]);

        $data['system_name'] = trim($data['system_name'] ?? '') ?: 'EAJ HRMS';
        $data['employee_id_prefix'] = strtoupper(trim($data['employee_id_prefix'] ?? '')) ?: 'EMP';

        $setting = Setting::singleton();
        $setting->update($data);

        return $this->settingsResponse($request, 'General settings saved.', [
            'system_name' => $setting->fresh()->system_name ?: 'EAJ HRMS',
            'employee_id_prefix' => $setting->fresh()->employeeIdPrefix(),
        ]);
    }

    /**
     * Save Theme & Appearance (AJAX)
     */
    public function saveTheme(Request $request)
    {
        $request->validate([
            'theme'                => 'required|string|in:ea,indigo,emerald,amber,rose,violet,cyan,orange,slate,teal',
            'primary_color'        => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'accent_color'         => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'id_card_template'     => 'nullable|string|in:classic,bold,minimal',
            'id_card_primary_color'=> 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'id_card_accent_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'id_card_logo'         => 'nullable|image|max:2048',
            'dtr_header'           => 'nullable|image|max:4096',
            'leave_form_header'    => 'nullable|image|max:4096',
        ]);

        $presetColors = [
            'ea'      => ['primary' => '#C9407A', 'accent' => '#fce7f3'],
            'indigo'  => ['primary' => '#4f46e5', 'accent' => '#e0e7ff'],
            'emerald' => ['primary' => '#059669', 'accent' => '#d1fae5'],
            'amber'   => ['primary' => '#d97706', 'accent' => '#fef3c7'],
            'rose'    => ['primary' => '#e11d48', 'accent' => '#ffe4e6'],
            'violet'  => ['primary' => '#7c3aed', 'accent' => '#ede9fe'],
            'cyan'    => ['primary' => '#0891b2', 'accent' => '#cffafe'],
            'orange'  => ['primary' => '#ea580c', 'accent' => '#ffedd5'],
            'slate'   => ['primary' => '#475569', 'accent' => '#f1f5f9'],
            'teal'    => ['primary' => '#0f766e', 'accent' => '#ccfbf1'],
        ];

        $normalizeHex = fn (?string $hex) => $hex ? strtoupper($hex) : null;
        $themePreset = $presetColors[$request->theme] ?? null;
        $primaryColor = $request->filled('primary_color') ? $normalizeHex($request->primary_color) : null;
        $accentColor = $request->filled('accent_color') ? $normalizeHex($request->accent_color) : null;

        if ($themePreset && $primaryColor === $normalizeHex($themePreset['primary'])) {
            $primaryColor = null;
        }

        if ($themePreset && $accentColor === $normalizeHex($themePreset['accent'])) {
            $accentColor = null;
        }

        $settings = Setting::singleton();
        $update = [
            'theme'         => $request->theme,
            'primary_color' => $primaryColor,
            'accent_color'  => $accentColor,
            'id_card_template' => $request->input('id_card_template', 'classic') ?: 'classic',
            'id_card_primary_color' => $request->filled('id_card_primary_color')
                ? $normalizeHex($request->id_card_primary_color)
                : null,
            'id_card_accent_color' => $request->filled('id_card_accent_color')
                ? $normalizeHex($request->id_card_accent_color)
                : null,
        ];

        if ($request->hasFile('id_card_logo')) {
            $update['id_card_logo'] = $this->storeSettingsImage($request->file('id_card_logo'), 'id-card-logo');
        }

        if ($request->hasFile('dtr_header')) {
            $update['dtr_header'] = $this->storeSettingsImage($request->file('dtr_header'), 'dtr-header');
        }

        if ($request->hasFile('leave_form_header')) {
            $update['leave_form_header'] = $this->storeSettingsImage($request->file('leave_form_header'), 'leave-form-header');
        }

        $settings->update($update);
        $settings->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme saved.',
                'appearance' => [
                    'theme' => $settings->theme,
                    'primary_color' => $settings->primary_color,
                    'accent_color' => $settings->accent_color,
                ],
                'id_card' => [
                    'template' => $settings->id_card_template ?: 'classic',
                    'primary_color' => $settings->id_card_primary_color,
                    'accent_color' => $settings->id_card_accent_color,
                    'logo' => $settings->id_card_logo ? asset('Uploads/Settings/' . $settings->id_card_logo) : null,
                ],
                'document_headers' => [
                    'dtr_header' => $settings->dtrHeaderUrl(),
                    'leave_form_header' => $settings->leaveFormHeaderUrl(),
                ],
            ]);
        }

        return back()->with('success', 'Theme saved.');
    }

    /**
     * Save Menu Visibility (AJAX)
     */
    public function saveMenuVisibility(Request $request)
    {
        $this->ensureMenuSettings();

        $request->validate([
            'visible'   => 'nullable|array',
            'visible.*' => 'string',
        ]);

        $visibleKeys = $request->input('visible', []);
        $allKeys = MenuSetting::pluck('menu_key')->all();

        foreach ($allKeys as $key) {
            MenuSetting::where('menu_key', $key)->update([
                'is_visible' => in_array($key, $visibleKeys, true),
            ]);
        }

        return $this->settingsResponse($request, 'Menu visibility saved.');
    }

    /**
     * Save User Menu Permissions (AJAX)
     */
    public function saveUserPermissions(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'menu_keys'  => 'nullable|array',
            'menu_keys.*'=> 'string',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->role === 'Administrator') {
            return response()->json(['success' => false, 'message' => 'Administrator always has full access.'], 422);
        }

        UserMenuPermission::updateOrCreate(
            ['user_id' => $request->user_id],
            ['menu_keys' => $request->menu_keys ?? []]
        );

        return $this->settingsResponse($request, "Permissions updated for {$user->fname} {$user->lname}.");
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Link a user account to an employee record (AJAX / normal POST).
     */
    public function linkEmployee(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'emp_ID'  => 'nullable|string|exists:employees,emp_ID',
        ]);

        $user = User::findOrFail($data['user_id']);
        $user->update(['emp_ID' => $data['emp_ID'] ?? null]);

        return $this->settingsResponse($request, "Employee link updated for {$user->fname} {$user->lname}.");
    }

    private function settingsResponse(Request $request, string $message, array $extra = [])
    {
        if ($request->expectsJson()) {
            return response()->json(array_merge(['success' => true, 'message' => $message], $extra));
        }

        return back()->with('success', $message);
    }

    private function storeSettingsImage($file, string $prefix): string
    {
        $directory = public_path('Uploads/Settings');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return $fileName;
    }

    private function ensureMenuSettings(): void
    {
        $sortOrder = 1;
        $validKeys = MenuHelper::keys();

        MenuSetting::whereNotIn('menu_key', $validKeys)->delete();

        foreach (MenuHelper::grouped() as $group => $items) {
            foreach ($items as $key => $label) {
                MenuSetting::updateOrCreate(
                    ['menu_key' => $key],
                    [
                        'label'      => $label,
                        'group'      => $group,
                        'sort_order' => $sortOrder++,
                    ]
                );
            }
        }
    }

    private function empTypeOptions(): array
    {
        return [
            'permanent'    => ['icon' => 'badge',          'label' => 'Permanent / Regular',   'govt' => true],
            'temporary'    => ['icon' => 'clock',          'label' => 'Temporary',              'govt' => true],
            'coterminous'  => ['icon' => 'link',           'label' => 'Coterminous',            'govt' => true],
            'casual'       => ['icon' => 'calendar-days',  'label' => 'Casual',                 'govt' => true],
            'contractual'  => ['icon' => 'file-pen',       'label' => 'Contractual / COS',      'govt' => false],
            'job_order'    => ['icon' => 'clipboard-list', 'label' => 'Job Order',              'govt' => false],
            'part_time'    => ['icon' => 'hourglass',      'label' => 'Part-time',              'govt' => false],
            'probationary' => ['icon' => 'user-check',     'label' => 'Probationary',           'govt' => false],
        ];
    }

    private function ensureEmploymentStatuses(array $enabledTypes, ?string $sector): void
    {
        $preferredSector = $sector === 'government' ? 'government' : 'private';
        $labelsByType = [
            'permanent'    => $preferredSector === 'government' ? ['Regular', 'Plantilla', 'Permanent'] : ['Regular'],
            'temporary'    => ['Temporary'],
            'coterminous'  => ['Coterminous'],
            'casual'       => ['Casual'],
            'contractual'  => ['Contractual', 'Project-based', 'Seasonal'],
            'job_order'    => ['Job Order'],
            'part_time'    => ['Part-time', 'Full-time'],
            'probationary' => ['Probationary'],
        ];

        $nextSort = (int) Status::where('sector', $preferredSector)->max('sort_order');

        collect($enabledTypes)
            ->flatMap(fn ($type) => $labelsByType[$type] ?? [])
            ->unique()
            ->each(function ($label) use ($preferredSector, &$nextSort) {
                Status::firstOrCreate(
                    ['status_name' => $label, 'sector' => $preferredSector],
                    ['sort_order' => ++$nextSort, 'active' => true]
                );
            });
    }

    private function leadershipLabels(): array
    {
        return [
            'suc'  => ['suc_pres' => 'University President',   'vpaa' => 'VP Academic Affairs',      'vpaf' => 'VP Administration & Finance', 'hr' => 'HR Director / HRMO'],
            'nga'  => ['suc_pres' => 'Secretary / Agency Head','vpaa' => 'Undersecretary',            'vpaf' => 'Finance Director / CFO',      'hr' => 'HR Director / HRMO'],
            'lgu'  => ['suc_pres' => 'Mayor / Governor',       'vpaa' => 'Vice Mayor / Governor',    'vpaf' => 'Mun. / Prov. Treasurer',      'hr' => 'HRMO / HR Officer'],
            'gocc' => ['suc_pres' => 'President / CEO',        'vpaa' => 'Chief Operating Officer',  'vpaf' => 'Chief Finance Officer',       'hr' => 'CHRO / HR Manager'],
            'private_school' => ['suc_pres' => 'School President','vpaa' => 'Academic Dean',       'vpaf' => 'Finance Director',            'hr' => 'HR Head'],
            'training'       => ['suc_pres' => 'Director',      'vpaa' => 'Deputy Director',         'vpaf' => 'Finance Officer',             'hr' => 'HR Officer'],
            'sme'          => ['suc_pres' => 'Owner / CEO',     'vpaa' => 'Operations Manager',      'vpaf' => 'Finance Manager',             'hr' => 'HR Manager'],
            'medium_large' => ['suc_pres' => 'CEO / President', 'vpaa' => 'Chief Operating Officer','vpaf' => 'Chief Finance Officer',       'hr' => 'HR Director / CHRO'],
            'enterprise'   => ['suc_pres' => 'Group CEO',       'vpaa' => 'Group COO',               'vpaf' => 'Group CFO',                   'hr' => 'Group CHRO'],
            'bpo'           => ['suc_pres' => 'Country Manager','vpaa' => 'Operations Director',     'vpaf' => 'Finance Director',            'hr' => 'HR Director'],
            'manufacturing' => ['suc_pres' => 'Plant Manager / CEO','vpaa' => 'Operations Manager','vpaf' => 'Finance Controller',          'hr' => 'HR Manager'],
            'healthcare'    => ['suc_pres' => 'Hospital Director','vpaa' => 'Medical Director',     'vpaf' => 'Finance Director',            'hr' => 'HR Director'],
            'retail'        => ['suc_pres' => 'CEO / General Manager','vpaa' => 'Operations Director','vpaf' => 'Finance Director',         'hr' => 'HR Director'],
            '_default' => ['suc_pres' => 'President / Agency Head', 'vpaa' => 'VPAA / Deputy Head','vpaf' => 'VPAF / Finance Head',         'hr' => 'HR Head'],
        ];
    }
}
