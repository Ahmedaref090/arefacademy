<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin (teacher) account — change the password after first login.
        User::updateOrCreate(
            ['phone' => '01000000000'],
            [
                'name' => 'Aref (Admin)',
                'email' => 'admin@aref.academy',
                'role' => UserRole::Admin,
                'password' => 'password',
            ]
        );
    }
}
