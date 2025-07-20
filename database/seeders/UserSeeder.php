<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => 'password123'
        ]);
        User::create([
            'id' => 2,
            'name' => 'Supervisor',
            'email' => 'spv@gmail.com',
            'password' => 'password123spv'
        ]);
        User::create([
            'id' => 3,
            'name' => 'Sales',
            'email' => 'sales@gmail.com',
            'password' => 'password123sales'
        ]);
    }
}
