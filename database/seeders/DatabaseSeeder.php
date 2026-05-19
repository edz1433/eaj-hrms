<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            CampBranchSeeder::class,
            OfficeSeeder::class,
            EmployeeSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            MenuSettingSeeder::class,
        ]);
    }
}
