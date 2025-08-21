<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //
        Role::create([
            'id' => 1,
            'name' => 'Admin',
            'code' => 'adm',
        ]);
        Role::create([
            'id' => 2,
            'name' => 'Supervisor',
            'code' => 'spv'
        ]);
        Role::create([
            'id' => 3,
            'name' => 'Sales',
            'code' => 'sls'
        ]);
        Role::create([
            'id' => 4,
            'name' => 'Mitra',
            'code' => 'mtr'
        ]);
        Role::create([
            'id' => 5,
            'name' => 'Telemarketing',
            'code' => 'tlm'
        ]);
    }
}
