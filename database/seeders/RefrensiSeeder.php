<?php

namespace Database\Seeders;

use App\Models\Refrensi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RefrensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Refrensi::create([
            'name' => 'Instagram',
            'code' => 'ig'
        ]);
        Refrensi::create([
            'name' => 'Whatsapp',
            'code' => 'wa'
        ]);
        Refrensi::create([
            'name' => 'Internet',
            'code' => 'inet'
        ]);
        Refrensi::create([
            'name' => 'Majalah/Koran',
            'code' => 'mk'
        ]);
        Refrensi::create([
            'name' => 'Lainnya',
            'code' => 'lain'
        ]);
    }
}
