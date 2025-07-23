<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tipe;

class TipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tipe::create([
            'project_id' => 2,
            'name' => 'Rumah',
        ]);
        Tipe::create([
            'project_id' => 1,
            'name' => 'Hotel',
        ]);
        Tipe::create([
            'project_id' => 3,
            'name' => 'Apartemen',
        ]);
    }
}
