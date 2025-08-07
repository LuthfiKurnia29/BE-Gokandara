<?php

namespace Database\Seeders;

use App\Models\UserMenuAccess;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuAccessSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 3,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 8,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 1,
            'menu_id' => 9,
            'is_allowed' => true
        ]);

        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 3,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 2,
            'menu_id' => 7,
            'is_allowed' => true
        ]);

        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 3,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 3,
            'menu_id' => 7,
            'is_allowed' => true
        ]);

        UserMenuAccess::create([
            'user_role_id' => 4,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 4,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 4,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 4,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 4,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => 4,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
    }
}
