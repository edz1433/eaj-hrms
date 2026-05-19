<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'edzavril1@gmail.com'],
            [
                'fname'    => 'Edzavril',
                'mname'    => null,
                'lname'    => 'Admin',
                'gender'   => 'Male',
                'email'    => 'edzavril1@gmail.com',
                'password' => Hash::make('password'),
                'role'     => 'Administrator',
            ]
        );
    }
}
