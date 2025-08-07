<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        UserRole::create([
            'user_id' => 1,
            'role_id' => 1,
            'is_allowed' => true
        ]);
        UserRole::create([
            'user_id' => 2,
            'role_id' => 2,
            'is_allowed' => true
        ]);
        UserRole::create([
            'user_id' => 3,
            'role_id' => 3,
            'is_allowed' => true
        ]);

        // Mitra
        UserRole::create([
            'user_id' => 7,
            'role_id' => 4,
            'is_allowed' => true
        ]);
    }
}
