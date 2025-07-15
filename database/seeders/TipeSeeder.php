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
            'Nama' => 'Rumah',
        ]);
        Tipe::create([
            'Nama' => 'Hotel',
        ]);
        Tipe::create([
            'Nama' => 'Apartemen',
        ]);
    }
}
