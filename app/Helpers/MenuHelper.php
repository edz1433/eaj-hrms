<?php

namespace App\Helpers;

class MenuHelper
{
    /**
     * All HRMS menu items (menu_key => label).
     * Keys match the menu_key column in menu_settings table.
     */
    public static function all(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'icon'  => 'layout-dashboard',   // clean, modern dashboard icon
            ],

            'employees' => [
                'label' => 'Employee Management',
                'icon'  => 'users',              // classic "team" icon
                'children' => [
                    'employee_records' => [
                        'label' => 'Employee Records',
                        'icon'  => 'id-card',    // personal identification
                    ],
                    'pds' => [
                        'label' => 'Personal Data Sheet',
                        'icon'  => 'file-text',  // document with text
                    ],
                    'dtr' => [
                        'label' => 'Daily Time Records',
                        'icon'  => 'clock',      // time tracking
                    ],
                    'official_time' => [
                        'label' => 'Official Time',
                        'icon'  => 'timer',      // stopwatch / timer
                    ],
                    'tardiness' => [
                        'label' => 'Tardiness/Absences',
                        'icon'  => 'alert-triangle', // warning sign
                    ],
                ],
            ],

            'leave' => [
                'label' => 'Leave Management',
                'icon'  => 'calendar-check',    // calendar with checkmark
                'children' => [
                    'leave_applications' => [
                        'label' => 'Leave Applications',
                        'icon'  => 'file-plus', // add document
                    ],
                    'leave_credits' => [
                        'label' => 'Leave Credits',
                        'icon'  => 'wallet',    // credit balance
                    ],
                ],
            ],

            'payroll' => [
                'label' => 'Payroll',
                'icon'  => 'credit-card',
            ],

            'recruitment' => [
                'label' => 'Recruitment',
                'icon'  => 'briefcase',         // hiring / job
                'children' => [
                    'job_postings' => [
                        'label' => 'Job Postings',
                        'icon'  => 'clipboard-list', // list on clipboard
                    ],
                    'job_applications' => [
                        'label' => 'Job Applications',
                        'icon'  => 'file-check',   // approved document
                    ],
                ],
            ],

            'administration' => [
                'label' => 'Administration',
                'icon'  => 'settings',          // system settings
                'children' => [
                    'user_management' => [
                        'label' => 'User Management',
                        'icon'  => 'user-cog',  // user with cog
                    ],
                    'office_management' => [
                        'label' => 'Office Management',
                        'icon'  => 'building',  // office building
                    ],
                    'deans_list' => [
                        'label' => 'Deans List',
                        'icon'  => 'graduation-cap',
                    ],
                    'system_settings' => [
                        'label' => 'System Settings',
                        'icon'  => 'sliders',   // settings sliders
                    ],
                ],
            ],

            'reports' => [
                'label' => 'Reports & Analytics',
                'icon'  => 'bar-chart-3',      // modern bar chart
            ],

            'events' => [
                'label' => 'Events & Calendar',
                'icon'  => 'calendar-days',    // calendar with days
            ],
        ];
    }

    /**
     * Valid menu keys for validation rules.
     */
    public static function keys(): array
    {
        return collect(self::grouped())
            ->flatMap(fn ($items) => array_keys($items))
            ->values()
            ->all();
    }

    /**
     * Get label for a given menu key.
     */
    public static function label(string $key): ?string
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Menus grouped by section — used in sidebar and permission panels.
     */
    public static function grouped(): array
    {
        return [
            'HR Management' => [
                'dashboard'          => 'Dashboard',
                'employee_records'   => 'Employee Records',
                'pds'                => 'Personal Data Sheet',
            ],
            'Attendance' => [
                'dtr'                => 'Daily Time Records',
                'official_time'      => 'Official Time',
                'tardiness'          => 'Tardiness & Absences',
            ],
            'Leave' => [
                'leave_applications' => 'Leave Applications',
                'leave_credits'      => 'Leave Credits',
            ],
            'Payroll' => [
                'payroll'            => 'Payroll',
            ],
            'Recruitment' => [
                'job_postings'       => 'Job Postings',
                'job_applications'   => 'Job Applications',
            ],
            'Administration' => [
                'user_management'    => 'User Management',
                'office_management'  => 'Office Management',
                'deans_list'         => 'Deans List',
                'system_settings'    => 'System Settings',
            ],
            'Reports' => [
                'reports'            => 'Reports & Analytics',
                'events'             => 'Events & Calendar',
            ],
        ];
    }

    /**
     * Route name mapped to each menu key — used to highlight active sidebar item.
     */
    public static function routeMap(): array
    {
        return [
            'dashboard'          => 'dashboard',
            'employee_records'   => 'employees',
            'pds'                => 'empPDS',
            'dtr'                => 'dtr-read',
            'tardiness'          => 'readTiredness',
            'leave_applications' => 'leaveStatus',
            'leave_credits'      => 'leavesRead',
            'payroll'            => 'payroll',
            'job_postings'       => 'jlist',
            'job_applications'   => 'appList',
            'user_management'    => 'ulist',
            'office_management'  => 'officeList',
            'deans_list'         => 'deanlist',
            'system_settings'    => 'settings',
            'reports'            => 'systemPerformance',
            'events'             => 'eventIndex',
        ];
    }

    public static function activeRouteMap(): array
    {
        return [
            'dashboard'          => ['dashboard', 'myaccount', 'readPending'],
            'employee_records'   => ['employees', 'empCreate', 'empEdit', 'empUpdate', 'empDelete'],
            'pds'                => ['empPDS', 'PDS', 'familybg', 'educbg', 'eligibility*', 'work-experience*', 'voluntary-work*', 'learning-dev*', 'otherInfo', 'infoQuestion', 'references', 'govids', 'signature'],
            'dtr'                => ['dtr-read'],
            'official_time'      => ['OfficialTimeRead', 'OfficialTimeCreate'],
            'tardiness'          => ['readTiredness', 'tirednessSearch'],
            'leave_applications' => ['leaveStatus', 'leavesReadEmp', 'leaveCreate', 'leaveUpdate', 'leaveDelete*'],
            'leave_credits'      => ['leavesRead', 'leaveCredits*'],
            'payroll'            => ['payroll'],
            'job_postings'       => ['jlist', 'jCreate', 'jEdit', 'jUpdate', 'jDelete'],
            'job_applications'   => ['appList', 'updateStatus', 'setCtrlNo'],
            'user_management'    => ['ulist', 'uCreate', 'uEdit', 'uUpdate', 'uDelete'],
            'office_management'  => ['officeList', 'officeCreate', 'officeEdit', 'officeUpdate', 'officeDelete'],
            'deans_list'         => ['deanlist', 'deanCreate', 'deanEdit', 'deanUpdate', 'deanDelete'],
            'system_settings'    => ['settings', 'settings.*'],
            'reports'            => ['systemPerformance'],
            'events'             => ['eventIndex', 'event*'],
        ];
    }

    /**
     * Menus that only Administrators can ever access (hidden from permission panels for other roles).
     */
    public static function adminOnly(): array
    {
        return [
            'user_management',
            'office_management',
            'deans_list',
            'system_settings',
        ];
    }

    /**
     * Check if a user (web guard) has access to a given menu key.
     * Administrator always has access. Others are checked against their UserMenuPermission.
     */
    public static function userCanAccess(string $menuKey): bool
    {
        $user = auth()->guard('web')->user();

        if (!$user) {
            return false;
        }

        if ($user->role === 'Administrator') {
            return true;
        }

        $permission = \App\Models\UserMenuPermission::where('user_id', $user->id)->first();

        if (!$permission || !$permission->menu_keys) {
            return false;
        }

        return in_array($menuKey, $permission->menu_keys);
    }

    /**
     * Check if a menu is globally visible (from menu_settings table).
     */
    public static function isVisible(string $menuKey): bool
    {
        static $cache = null;

        if ($cache === null) {
            $cache = \App\Models\MenuSetting::pluck('is_visible', 'menu_key')->toArray();
        }

        return (bool) ($cache[$menuKey] ?? true);
    }
}
