<?php

namespace Database\Seeders;

use App\Models\Projek;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjekLogoSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $projeks = Projek::with('gambars')->get();

        foreach ($projeks as $projek) {
            $projek->update([
                'logo' => $projek->gambars->first()->gambar,
            ]);
        }
    }
}
