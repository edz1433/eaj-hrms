<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $employeeIds = Employee::whereIn('emp_ID', ['EMP0001', 'EMP0002', 'EMP0003', 'EMP0004'])
            ->pluck('id', 'emp_ID');

        Setting::updateOrCreate(
            ['id' => 1],
            [
                'org_name' => 'Central Philippines State University',
                'sector' => 'government',
                'org_type' => 'suc',
                'emp_types' => ['permanent', 'casual', 'job_order', 'part_time'],
                'suc_pres' => $employeeIds['EMP0001'] ?? null,
                'vpaa' => $employeeIds['EMP0002'] ?? null,
                'vpaf' => $employeeIds['EMP0003'] ?? null,
                'hr' => $employeeIds['EMP0004'] ?? null,
                'records_office_email' => 'records@cpsu.edu.ph',
                'job_portal_email' => 'careers@cpsu.edu.ph',
                'hr_head_email' => 'hrmo@cpsu.edu.ph',
                'maintenance' => false,
                'system_name' => 'EAJ HRMS',
                'employee_id_prefix' => 'EMP',
                'theme' => 'ea',
                'primary_color' => null,
                'accent_color' => null,
                'login_bg' => null,
            ]
        );
    }
}
