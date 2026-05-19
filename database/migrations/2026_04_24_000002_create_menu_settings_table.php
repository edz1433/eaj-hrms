<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key')->unique();
            $table->string('label');
            $table->string('group')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        // Seed all HRMS menu items with default visibility = true
        $now = now();
        $menus = [
            // HR Management
            ['menu_key' => 'dashboard',        'label' => 'Dashboard',              'group' => 'HR Management',    'sort_order' => 1],
            ['menu_key' => 'employee_records',  'label' => 'Employee Records',       'group' => 'HR Management',    'sort_order' => 2],
            ['menu_key' => 'pds',               'label' => 'Personal Data Sheet',    'group' => 'HR Management',    'sort_order' => 3],
            // Attendance
            ['menu_key' => 'dtr',               'label' => 'Daily Time Records',     'group' => 'Attendance',       'sort_order' => 4],
            ['menu_key' => 'official_time',     'label' => 'Official Time',          'group' => 'Attendance',       'sort_order' => 5],
            ['menu_key' => 'tardiness',         'label' => 'Tardiness & Absences',   'group' => 'Attendance',       'sort_order' => 6],
            // Leave
            ['menu_key' => 'leave_applications','label' => 'Leave Applications',     'group' => 'Leave',            'sort_order' => 7],
            ['menu_key' => 'leave_credits',     'label' => 'Leave Credits',          'group' => 'Leave',            'sort_order' => 8],
            // Payroll
            ['menu_key' => 'payroll',           'label' => 'Payroll',                'group' => 'Payroll',          'sort_order' => 9],
            // Recruitment
            ['menu_key' => 'job_postings',      'label' => 'Job Postings',           'group' => 'Recruitment',      'sort_order' => 10],
            ['menu_key' => 'job_applications',  'label' => 'Job Applications',       'group' => 'Recruitment',      'sort_order' => 11],
            // Administration
            ['menu_key' => 'user_management',   'label' => 'User Management',        'group' => 'Administration',   'sort_order' => 12],
            ['menu_key' => 'office_management', 'label' => 'Office Management',      'group' => 'Administration',   'sort_order' => 13],
            ['menu_key' => 'deans_list',        'label' => 'Deans List',             'group' => 'Administration',   'sort_order' => 14],
            // Reports
            ['menu_key' => 'reports',           'label' => 'Reports & Analytics',    'group' => 'Reports',          'sort_order' => 15],
            ['menu_key' => 'events',            'label' => 'Events & Calendar',      'group' => 'Reports',          'sort_order' => 16],
        ];

        foreach ($menus as &$menu) {
            $menu['is_visible']   = true;
            $menu['created_at']   = $now;
            $menu['updated_at']   = $now;
        }

        DB::table('menu_settings')->insert($menus);
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_settings');
    }
};
