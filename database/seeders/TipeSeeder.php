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
            'name' => 'Rumah',
        ]);
        Tipe::create([
            'name' => 'Hotel',
        ]);
        Tipe::create([
            'name' => 'Apartemen',
        ]);
    }
}
