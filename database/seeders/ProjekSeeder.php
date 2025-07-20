<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projek;

class ProjekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Projek::create([
            'name' => 'Hoonian',
        ]);
        Projek::create([
            'name' => 'Roomah',
        ]);
        Projek::create([
            'name' => 'Residence',
        ]);
    }
}
