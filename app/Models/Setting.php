<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        // Organization
        'org_name',
        'sector',
        'org_type',
        'emp_types',
        // Leadership positions
        'hr',
        'suc_pres',
        'vpaa',
        'vpaf',
        // Attendance / kiosk
        'dtr_acct',
        'hr_kiosk',
        'hrk_pw',
        'sync_backups',
        'te_rstrct_lvl',
        // Emails
        'records_office_email',
        'job_portal_email',
        'hr_head_email',
        // System controls
        'maintenance',
        'system_name',
        // Theme
        'theme',
        'primary_color',
        'accent_color',
        'login_bg',
        'id_card_template',
        'id_card_primary_color',
        'id_card_accent_color',
        'id_card_logo',
    ];

    protected $casts = [
        'maintenance'  => 'boolean',
        'sync_backups' => 'boolean',
        'emp_types'    => 'array',
    ];

    /**
     * Always return the singleton settings row, creating it if absent.
     */
    public static function singleton(): static
    {
        return static::firstOrCreate([], [
            'system_name' => 'CPSU HRIS',
            'theme'       => 'ea',
            'maintenance' => false,
            'org_name'    => 'EAJ HR Management System',
        ]);
    }

    /**
     * Get organization name with fallback.
     *
     * @return string
     */
    public static function getOrgName(): string
    {
        $setting = self::singleton();
        return $setting->org_name ?? 'EAJ HR Management System';
    }

    /**
     * Get system name with fallback.
     *
     * @return string
     */
    public static function getSystemName(): string
    {
        $setting = self::singleton();
        return $setting->system_name ?? 'EAJ HRMS';
    }

    /**
     * Get theme with fallback.
     *
     * @return string
     */
    public static function getTheme(): string
    {
        $setting = self::singleton();
        return $setting->theme ?? 'ea';
    }

    /**
     * Get primary color with fallback.
     *
     * @return string
     */
    public static function getPrimaryColor(): string
    {
        $setting = self::singleton();
        return $setting->primary_color ?? '#C9407A';
    }

    /**
     * Get accent color with fallback.
     *
     * @return string
     */
    public static function getAccentColor(): string
    {
        $setting = self::singleton();
        return $setting->accent_color ?? '#6366f1';
    }

    /**
     * Get maintenance mode status.
     *
     * @return bool
     */
    public static function isUnderMaintenance(): bool
    {
        $setting = self::singleton();
        return (bool) ($setting->maintenance ?? false);
    }

    /**
     * Get login background image.
     *
     * @return string|null
     */
    public static function getLoginBg(): ?string
    {
        $setting = self::singleton();
        return $setting->login_bg ?? null;
    }

    /**
     * Resolve the location context for the current org type.
     * Education orgs (suc, private_school, training) use "Campus";
     * all other sectors use "Branch".
     */
    public function locationContext(): object
    {
        $orgType = $this->org_type ?? '';

        $contexts = [
            // Government: only SUCs need a campus layer. LGU/NGA/GOCC employee records stay office-based.
            'suc' => [
                'enabled' => true,
                'type' => 'campus',
                'label' => 'Campus',
                'plural' => 'Campuses',
                'icon' => 'fa-university',
                'office_label' => 'College / Office',
                'item_label' => 'Plantilla / Item No.',
                'directory_label' => 'Campus-based directory',
            ],
            'lgu' => [
                'enabled' => false,
                'type' => 'office',
                'label' => 'Office',
                'plural' => 'Offices',
                'icon' => 'fa-building',
                'office_label' => 'Department / Office',
                'item_label' => 'Plantilla / Item No.',
                'directory_label' => 'Department-based directory',
            ],
            'nga' => [
                'enabled' => false,
                'type' => 'office',
                'label' => 'Office',
                'plural' => 'Offices',
                'icon' => 'fa-building-columns',
                'office_label' => 'Division / Office',
                'item_label' => 'Plantilla / Item No.',
                'directory_label' => 'Division-based directory',
            ],
            'gocc' => [
                'enabled' => false,
                'type' => 'office',
                'label' => 'Office',
                'plural' => 'Offices',
                'icon' => 'fa-building',
                'office_label' => 'Department / Office',
                'item_label' => 'Position Item No.',
                'directory_label' => 'Department-based directory',
            ],

            // Education.
            'private_school' => [
                'enabled' => true,
                'type' => 'campus',
                'label' => 'Campus',
                'plural' => 'Campuses',
                'icon' => 'fa-school',
                'office_label' => 'Department / Office',
                'item_label' => 'Employee No. / Item No.',
                'directory_label' => 'Campus-based directory',
            ],
            'training' => [
                'enabled' => true,
                'type' => 'site',
                'label' => 'Training Site',
                'plural' => 'Training Sites',
                'icon' => 'fa-chalkboard-user',
                'office_label' => 'Program / Office',
                'item_label' => 'Employee No. / Item No.',
                'directory_label' => 'Site-based directory',
            ],

            // Private sector.
            'sme' => [
                'enabled' => false,
                'type' => 'office',
                'label' => 'Team',
                'plural' => 'Teams',
                'icon' => 'fa-store',
                'office_label' => 'Team / Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Team-based directory',
            ],
            'medium_large' => [
                'enabled' => true,
                'type' => 'branch',
                'label' => 'Branch',
                'plural' => 'Branches',
                'icon' => 'fa-building',
                'office_label' => 'Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Branch-based directory',
            ],
            'enterprise' => [
                'enabled' => true,
                'type' => 'branch',
                'label' => 'Branch / Site',
                'plural' => 'Branches / Sites',
                'icon' => 'fa-building',
                'office_label' => 'Business Unit / Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Branch/site-based directory',
            ],

            // Industry.
            'bpo' => [
                'enabled' => true,
                'type' => 'site',
                'label' => 'Site',
                'plural' => 'Sites',
                'icon' => 'fa-headset',
                'office_label' => 'Account / Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Site-based directory',
            ],
            'manufacturing' => [
                'enabled' => true,
                'type' => 'plant',
                'label' => 'Plant',
                'plural' => 'Plants',
                'icon' => 'fa-industry',
                'office_label' => 'Production Unit / Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Plant-based directory',
            ],
            'healthcare' => [
                'enabled' => true,
                'type' => 'facility',
                'label' => 'Facility',
                'plural' => 'Facilities',
                'icon' => 'fa-hospital',
                'office_label' => 'Unit / Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Facility-based directory',
            ],
            'retail' => [
                'enabled' => true,
                'type' => 'branch',
                'label' => 'Store / Branch',
                'plural' => 'Stores / Branches',
                'icon' => 'fa-store',
                'office_label' => 'Department',
                'item_label' => 'Employee Code',
                'directory_label' => 'Store/branch-based directory',
            ],
        ];

        return (object) ($contexts[$orgType] ?? [
            'enabled' => false,
            'type' => 'office',
            'label' => 'Office',
            'plural' => 'Offices',
            'icon' => 'fa-building',
            'office_label' => 'Department / Office',
            'item_label' => 'Employee No. / Item No.',
            'directory_label' => 'Office-based directory',
        ]);
    }

    /**
     * Get employment types array.
     *
     * @return array
     */
    public static function getEmpTypes(): array
    {
        $setting = self::singleton();
        return $setting->emp_types ?? ['Permanent', 'Casual', 'Job Order', 'Contractual'];
    }

    /**
     * Get HR email.
     *
     * @return string|null
     */
    public static function getHrEmail(): ?string
    {
        $setting = self::singleton();
        return $setting->hr_head_email ?? null;
    }

    /**
     * Get records office email.
     *
     * @return string|null
     */
    public static function getRecordsOfficeEmail(): ?string
    {
        $setting = self::singleton();
        return $setting->records_office_email ?? null;
    }
}
