<?php

namespace Database\Seeders;

use App\Helpers\MenuHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSettingSeeder extends Seeder
{
    public function run(): void
    {
        $grouped = MenuHelper::grouped();
        $sortOrder = 1;

        foreach ($grouped as $group => $items) {
            foreach ($items as $key => $label) {
                DB::table('menu_settings')->updateOrInsert(
                    ['menu_key' => $key],
                    [
                        'label'      => $label,
                        'group'      => $group,
                        'sort_order' => $sortOrder++,
                        'is_visible' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
