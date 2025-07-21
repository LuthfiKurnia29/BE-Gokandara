<?php

namespace Database\Seeders;

use App\Models\Prospek;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProspekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Prospek::create([
            'name' => 'Cold'
        ]);
        Prospek::create([
            'name' => 'Warm'
        ]);
        Prospek::create([
            'name' => 'Hot'
        ]);
        Prospek::create([
            'name' => 'Suspect'
        ]);
    }
}
