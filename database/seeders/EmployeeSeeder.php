<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $officeIds = DB::table('offices')->pluck('id', 'office_code');
        $branchIds = DB::table('camp_branches')->pluck('id', 'code');

        $employees = [
            [
                'emp_ID' => 'EMP0001',
                'fname' => 'MARIA',
                'mname' => 'SANTOS',
                'lname' => 'DELA CRUZ',
                'position' => 'University President',
                'item_no' => 'SUC-PRES-0001',
                'bdate' => '1978-02-14',
                'sex' => 'Female',
                'civil_status' => 'Married',
                'org_email' => 'president@cpsu.edu.ph',
                'emp_status' => 1,
                'emp_dept' => $officeIds['OUP-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2020-01-06',
            ],
            [
                'emp_ID' => 'EMP0002',
                'fname' => 'RICARDO',
                'mname' => 'LOPEZ',
                'lname' => 'VILLANUEVA',
                'position' => 'VP for Academic Affairs',
                'item_no' => 'SUC-VPAA-0001',
                'bdate' => '1975-07-22',
                'sex' => 'Male',
                'civil_status' => 'Married',
                'org_email' => 'vpaa@cpsu.edu.ph',
                'emp_status' => 1,
                'emp_dept' => $officeIds['OVPAA-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2020-02-03',
            ],
            [
                'emp_ID' => 'EMP0003',
                'fname' => 'CORAZON',
                'mname' => 'REYES',
                'lname' => 'MAGLAYA',
                'position' => 'VP for Administration and Finance',
                'item_no' => 'SUC-VPAF-0001',
                'bdate' => '1976-11-09',
                'sex' => 'Female',
                'civil_status' => 'Married',
                'org_email' => 'vpaf@cpsu.edu.ph',
                'emp_status' => 1,
                'emp_dept' => $officeIds['OVPAF-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2020-03-02',
            ],
            [
                'emp_ID' => 'EMP0004',
                'fname' => 'JOSE',
                'mname' => 'BERNARDO',
                'lname' => 'RAMOS',
                'position' => 'HR Management Officer',
                'item_no' => 'HRMO-PL-0001',
                'bdate' => '1985-04-18',
                'sex' => 'Male',
                'civil_status' => 'Single',
                'org_email' => 'hrmo@cpsu.edu.ph',
                'emp_status' => 1,
                'emp_dept' => $officeIds['HRMO-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2021-01-11',
            ],
            [
                'emp_ID' => 'EMP0005',
                'fname' => 'LOURDES',
                'mname' => 'GARCIA',
                'lname' => 'FERNANDEZ',
                'position' => 'University Registrar',
                'item_no' => 'OUR-PL-0001',
                'bdate' => '1982-09-30',
                'sex' => 'Female',
                'civil_status' => 'Married',
                'org_email' => 'registrar@cpsu.edu.ph',
                'emp_status' => 1,
                'emp_dept' => $officeIds['OUR-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2021-05-17',
            ],
            [
                'emp_ID' => 'EMP0006',
                'fname' => 'EDUARDO',
                'mname' => 'CRUZ',
                'lname' => 'NAVARRO',
                'position' => 'Finance Officer',
                'item_no' => 'FO-CS-0001',
                'bdate' => '1990-12-05',
                'sex' => 'Male',
                'civil_status' => 'Single',
                'org_email' => 'finance@cpsu.edu.ph',
                'emp_status' => 3,
                'emp_dept' => $officeIds['FO-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2022-02-14',
            ],
            [
                'emp_ID' => 'EMP0007',
                'fname' => 'ANA',
                'mname' => 'PEREZ',
                'lname' => 'SORIANO',
                'position' => 'Planning Officer',
                'item_no' => 'PDO-JO-0001',
                'bdate' => '1993-06-12',
                'sex' => 'Female',
                'civil_status' => 'Single',
                'org_email' => 'planning@cpsu.edu.ph',
                'emp_status' => 4,
                'emp_dept' => $officeIds['PDO-001'] ?? null,
                'camp_id' => $branchIds['EXT-HIN'] ?? null,
                'date_hired' => '2022-07-01',
            ],
            [
                'emp_ID' => 'EMP0008',
                'fname' => 'RAMON',
                'mname' => 'MENDOZA',
                'lname' => 'AQUINO',
                'position' => 'ICT Director',
                'item_no' => 'ICTC-PT-0001',
                'bdate' => '1988-03-27',
                'sex' => 'Male',
                'civil_status' => 'Married',
                'org_email' => 'ict@cpsu.edu.ph',
                'emp_status' => 6,
                'emp_dept' => $officeIds['ICTC-001'] ?? null,
                'camp_id' => $branchIds['MAIN'] ?? null,
                'date_hired' => '2022-09-19',
            ],
        ];

        foreach ($employees as $employee) {
            $birthday = Carbon::parse($employee['bdate']);

            DB::table('employees')->updateOrInsert(
                ['emp_ID' => $employee['emp_ID']],
                array_merge($employee, [
                    'age' => $birthday->age,
                    'email' => $employee['org_email'],
                    'profile' => null,
                    'stat_1' => 1,
                    'password' => Hash::make($employee['emp_ID']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            DB::table('official_times')->updateOrInsert(
                ['empid' => $employee['emp_ID']],
                [
                    'morn_mon' => '08:00:00-12:00:00',
                    'aft_mon' => '13:00:00-17:00:00',
                    'morn_tue' => '08:00:00-12:00:00',
                    'aft_tue' => '13:00:00-17:00:00',
                    'morn_wed' => '08:00:00-12:00:00',
                    'aft_wed' => '13:00:00-17:00:00',
                    'morn_thu' => '08:00:00-12:00:00',
                    'aft_thu' => '13:00:00-17:00:00',
                    'morn_fri' => '08:00:00-12:00:00',
                    'aft_fri' => '13:00:00-17:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $employeeIds = DB::table('employees')->pluck('id', 'emp_ID');

        $officeHeads = [
            'OUP-001' => 'EMP0001',
            'OVPAA-001' => 'EMP0002',
            'OVPAF-001' => 'EMP0003',
            'HRMO-001' => 'EMP0004',
            'OUR-001' => 'EMP0005',
            'FO-001' => 'EMP0006',
            'PDO-001' => 'EMP0007',
            'ICTC-001' => 'EMP0008',
        ];

        foreach ($officeHeads as $officeCode => $employeeNumber) {
            DB::table('offices')
                ->where('office_code', $officeCode)
                ->update([
                    'office_head_id' => $employeeIds[$employeeNumber] ?? null,
                    'updated_at' => now(),
                ]);
        }
    }
}
