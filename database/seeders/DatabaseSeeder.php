<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin (teacher) account.
        // Login is phone-based, so the admin's login identifier lives in the
        // "phone" field: log in at /login with  aref / ahmedaref
        User::updateOrCreate(
            ['phone' => '01068014651'],
            [
                'name' => 'Aref (Admin)',
                'email' => 'admin@aref.academy',
                'role' => UserRole::Admin,
                'password' => 'ahmedaref',
            ]
        );
    }
}
