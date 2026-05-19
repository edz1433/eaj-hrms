<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampBranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['code' => 'MAIN', 'abbr' => 'MAIN', 'name' => 'Main Campus'],
            ['code' => 'EXT-HIN', 'abbr' => 'HIN', 'name' => 'Hinoba-an Campus'],
            ['code' => 'EXT-ILOG', 'abbr' => 'ILOG', 'name' => 'Ilog Campus'],
            ['code' => 'EXT-CAN', 'abbr' => 'CAN', 'name' => 'Candoni Campus'],
        ];

        foreach ($branches as $branch) {
            DB::table('camp_branches')->updateOrInsert(
                ['code' => $branch['code']],
                array_merge($branch, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
