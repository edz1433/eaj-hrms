<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['office_code' => 'OUP-001', 'office_abbr' => 'OUP', 'office_name' => 'Office of the University President'],
            ['office_code' => 'OVPAA-001', 'office_abbr' => 'OVPAA', 'office_name' => 'Office of the VP for Academic Affairs'],
            ['office_code' => 'OVPAF-001', 'office_abbr' => 'OVPAF', 'office_name' => 'Office of the VP for Administration and Finance'],
            ['office_code' => 'HRMO-001', 'office_abbr' => 'HRMO', 'office_name' => 'Human Resource Management Office'],
            ['office_code' => 'OUR-001', 'office_abbr' => 'OUR', 'office_name' => 'Office of the University Registrar'],
            ['office_code' => 'FO-001', 'office_abbr' => 'FO', 'office_name' => 'Finance Office'],
            ['office_code' => 'PDO-001', 'office_abbr' => 'PDO', 'office_name' => 'Planning and Development Office'],
            ['office_code' => 'ICTC-001', 'office_abbr' => 'ICTC', 'office_name' => 'Information and Communication Technology Center'],
            ['office_code' => 'CAF-001', 'office_abbr' => 'CAF', 'office_name' => 'College of Agriculture and Forestry'],
            ['office_code' => 'CAS-001', 'office_abbr' => 'CAS', 'office_name' => 'College of Arts and Sciences'],
            ['office_code' => 'CET-001', 'office_abbr' => 'CET', 'office_name' => 'College of Engineering and Technology'],
            ['office_code' => 'CN-001', 'office_abbr' => 'CN', 'office_name' => 'College of Nursing'],
            ['office_code' => 'CED-001', 'office_abbr' => 'CED', 'office_name' => 'College of Education'],
        ];

        foreach ($offices as $office) {
            DB::table('offices')->updateOrInsert(
                ['office_code' => $office['office_code']],
                array_merge($office, [
                    'office_head_id' => null,
                    'oic_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
