<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create([
            'Nama' => 'Unit 11',
        ]);
        Unit::create([
            'Nama' => 'Unit 12',
        ]);
        Unit::create([
            'Nama' => 'Unit 13',
        ]);
    }
}
