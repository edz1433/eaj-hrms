<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // ── Government (SUC / Public Sector) ─────────────────────────────
            ['id' => 1,  'status_name' => 'Regular',           'sector' => 'government', 'sort_order' => 1],
            ['id' => 2,  'status_name' => 'Plantilla',         'sector' => 'government', 'sort_order' => 2],
            ['id' => 3,  'status_name' => 'Casual',            'sector' => 'government', 'sort_order' => 3],
            ['id' => 4,  'status_name' => 'Job Order',         'sector' => 'government', 'sort_order' => 4],
            ['id' => 5,  'status_name' => 'Full-time',         'sector' => 'government', 'sort_order' => 5],
            ['id' => 6,  'status_name' => 'Part-time',         'sector' => 'government', 'sort_order' => 6],
            ['id' => 7,  'status_name' => 'Contractual',       'sector' => 'government', 'sort_order' => 7],

            // ── Private Sector ────────────────────────────────────────────────
            ['id' => 8,  'status_name' => 'Regular',           'sector' => 'private',    'sort_order' => 1],
            ['id' => 9,  'status_name' => 'Probationary',      'sector' => 'private',    'sort_order' => 2],
            ['id' => 10, 'status_name' => 'Full-time',         'sector' => 'private',    'sort_order' => 3],
            ['id' => 11, 'status_name' => 'Part-time',         'sector' => 'private',    'sort_order' => 4],
            ['id' => 12, 'status_name' => 'Contractual',       'sector' => 'private',    'sort_order' => 5],
            ['id' => 13, 'status_name' => 'Project-based',     'sector' => 'private',    'sort_order' => 6],
            ['id' => 14, 'status_name' => 'Seasonal',          'sector' => 'private',    'sort_order' => 7],
        ];

        foreach ($statuses as $status) {
            DB::table('statuses')->updateOrInsert(
                ['id' => $status['id']],
                array_merge($status, [
                    'active'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
