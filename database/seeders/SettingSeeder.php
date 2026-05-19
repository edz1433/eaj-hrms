<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $employeeIds = Employee::whereIn('emp_ID', ['0001', '0002', '0003', '0004'])
            ->pluck('id', 'emp_ID');

        Setting::updateOrCreate(
            ['id' => 1],
            [
                'org_name' => 'Central Philippines State University',
                'sector' => 'government',
                'org_type' => 'suc',
                'emp_types' => ['permanent', 'casual', 'job_order', 'part_time'],
                'suc_pres' => $employeeIds['0001'] ?? null,
                'vpaa' => $employeeIds['0002'] ?? null,
                'vpaf' => $employeeIds['0003'] ?? null,
                'hr' => $employeeIds['0004'] ?? null,
                'records_office_email' => 'records@cpsu.edu.ph',
                'job_portal_email' => 'careers@cpsu.edu.ph',
                'hr_head_email' => 'hrmo@cpsu.edu.ph',
                'maintenance' => false,
                'system_name' => 'EAJ HRMS',
                'theme' => 'ea',
                'primary_color' => null,
                'accent_color' => null,
                'login_bg' => null,
            ]
        );
    }
}
