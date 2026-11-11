<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'subadmin@test.com'],
            ['name' => 'Test Teacher', 'password' => bcrypt('password'), 'role' => 'sub_admin', 'is_active' => true]
        );

        User::firstOrCreate(
            ['email' => 'parent@test.com'],
            ['name' => 'Test Parent', 'password' => bcrypt('password'), 'role' => 'parent', 'is_active' => true]
        );
    }
}
