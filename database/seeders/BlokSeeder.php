<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Blok;


class BlokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Blok::create([
            'name' => 'Blok A',
        ]);
        Blok::create([
            'name' => 'Blok B',
        ]);
        Blok::create([
            'name' => 'Blok C',
        ]);
    }
}
