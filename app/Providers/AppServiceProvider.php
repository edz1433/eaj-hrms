<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Notification;
use App\Models\Setting;
use App\Helpers\MenuHelper; // Add this import

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');

        View::composer('*', function ($view) {
            $cachedSetting = Setting::singleton();
            $view->with('sysTheme', $cachedSetting->theme ?? 'ea');
            $view->with('sysPrimaryColor', $cachedSetting->primary_color);
            $view->with('sysAccentColor', $cachedSetting->accent_color);
            $view->with('locCtx',  $cachedSetting->locationContext());
            $view->with('orgName', $cachedSetting->org_name ?? 'HRMS');
            $view->with('sysName', $cachedSetting->system_name ?: 'EAJ HRMS');
            $view->with('orgType', $cachedSetting->org_type ?? '');

            // Resolve active guard and share globally
            $guard = Auth::guard('web')->check() ? 'web'
                   : (Auth::guard('employee')->check() ? 'employee' : null);
            $view->with('guard', $guard);

            // Share MenuHelper instance globally
            $menuHelper = new MenuHelper();
            $view->with('menuHelper', $menuHelper);
            
            // Create helper function for menu access
            $canAccess = function($key) use ($guard, $menuHelper) {
                if ($guard === 'web') {
                    return $menuHelper->userCanAccess($key) && $menuHelper->isVisible($key);
                }
                return $menuHelper->isVisible($key);
            };
            $view->with('canAccess', $canAccess);

            // Admin (web guard) notifications
            if (Auth::guard('web')->check()) {
                $notifications = Notification::query()
                    ->select(
                        'notifications.id',
                        'notifications.lapp_id',
                        'notifications.empid',
                        'notifications.module',
                        'notifications.category',
                        'notifications.status as notifstat',
                        'notifications.created_at as notif_created_at'
                    )
                    ->where('notifications.utype', 'hr')
                    ->leftJoin('leave_applications', function ($j) {
                        $j->on('notifications.lapp_id', '=', 'leave_applications.id')
                          ->where('notifications.module', 'leave');
                    })
                    ->leftJoin('employees as leave_emp', function ($j) {
                        $j->on('leave_emp.emp_ID', '=', 'leave_applications.empid')
                          ->where('notifications.module', 'leave');
                    })
                    ->addSelect(
                        'leave_applications.empid as leave_emp_empid',
                        'leave_applications.leave_type',
                        'leave_applications.transnum',
                        'leave_emp.id as leave_emp_id',
                        'leave_emp.profile as leave_emp_profile',
                        DB::raw("CONCAT(leave_emp.fname, ' ', leave_emp.lname) as leave_emp_fullname")
                    )
                    ->leftJoin('eligibilities', function ($j) {
                        $j->on('notifications.lapp_id', '=', 'eligibilities.id')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 1);
                    })
                    ->leftJoin('employees as pds_emp_eligi', function ($j) {
                        $j->on('pds_emp_eligi.emp_ID', '=', 'eligibilities.empid')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 1);
                    })
                    ->addSelect(
                        'pds_emp_eligi.id as pds_emp_eligi_id',
                        'pds_emp_eligi.profile as pds_emp_eligi_profile',
                        DB::raw("CONCAT(pds_emp_eligi.fname, ' ', pds_emp_eligi.lname) as pds_emp_eligi_fullname")
                    )
                    ->leftJoin('work_experiences', function ($j) {
                        $j->on('notifications.lapp_id', '=', 'work_experiences.id')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 2);
                    })
                    ->leftJoin('employees as pds_emp_workexp', function ($j) {
                        $j->on('pds_emp_workexp.emp_ID', '=', 'work_experiences.empid')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 2);
                    })
                    ->addSelect(
                        'pds_emp_workexp.id as pds_emp_workexp_id',
                        'pds_emp_workexp.profile as pds_emp_workexp_profile',
                        DB::raw("CONCAT(pds_emp_workexp.fname, ' ', pds_emp_workexp.lname) as pds_emp_workexp_fullname")
                    )
                    ->leftJoin('voluntary_works', function ($j) {
                        $j->on('notifications.lapp_id', '=', 'voluntary_works.id')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 3);
                    })
                    ->leftJoin('employees as pds_emp_volworks', function ($j) {
                        $j->on('pds_emp_volworks.emp_ID', '=', 'voluntary_works.empid')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 3);
                    })
                    ->addSelect(
                        'pds_emp_volworks.id as pds_emp_volworks_id',
                        'pds_emp_volworks.profile as pds_emp_volworks_profile',
                        DB::raw("CONCAT(pds_emp_volworks.fname, ' ', pds_emp_volworks.lname) as pds_emp_volworks_fullname")
                    )
                    ->leftJoin('learning_devs', function ($j) {
                        $j->on('notifications.lapp_id', '=', 'learning_devs.id')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 4);
                    })
                    ->leftJoin('employees as pds_emp_learndev', function ($j) {
                        $j->on('pds_emp_learndev.emp_ID', '=', 'learning_devs.empid')
                          ->where('notifications.module', 'pds')
                          ->where('notifications.category', 4);
                    })
                    ->addSelect(
                        'pds_emp_learndev.id as pds_emp_learndev_id',
                        'pds_emp_learndev.profile as pds_emp_learndev_profile',
                        DB::raw("CONCAT(pds_emp_learndev.fname, ' ', pds_emp_learndev.lname) as pds_emp_learndev_fullname")
                    )
                    ->orderBy('notifications.created_at', 'desc')
                    ->get();

                $notificationsCount = $notifications->where('notifstat', 0)->count();

                $view->with('notifications', $notifications)
                     ->with('notificationsCount', $notificationsCount);

                // Empty defaults so employee partials don't throw
                $view->with('notifications1', collect())
                     ->with('notificationsCount1', 0);

            } elseif (Auth::guard('employee')->check()) {
                // Employee notifications — raw collection, filtered in the blade partial
                $notifications1     = Notification::where('utype', 'employee')->get();
                $notificationsCount1 = $notifications1;   // blade partial counts by empid

                $view->with('notifications1', $notifications1)
                     ->with('notificationsCount1', $notificationsCount1);

                // Empty defaults so admin partials don't throw
                $view->with('notifications', collect())
                     ->with('notificationsCount', 0);

            } else {
                // Unauthenticated pages (login, verify, etc.)
                $view->with('notifications', collect())
                     ->with('notificationsCount', 0)
                     ->with('notifications1', collect())
                     ->with('notificationsCount1', 0);
            }
        });
    }
}
