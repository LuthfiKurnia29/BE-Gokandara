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
            'name' => 'Facebook',
            'code' => 'fb'
        ]);
        Refrensi::create([
            'name' => 'Website',
            'code' => 'web'
        ]);
        Refrensi::create([
            'name' => 'Referensi',
            'code' => 'ref'
        ]);
        Refrensi::create([
            'name' => 'Expo',
            'code' => 'ex'
        ]);
        Refrensi::create([
            'name' => 'Telemarketing',
            'code' => 'tm'
        ]);
        Refrensi::create([
            'name' => 'Canvasing',
            'code' => 'canvas'
        ]);
        Refrensi::create([
            'name' => 'Agent',
            'code' => 'agent'
        ]);
        Refrensi::create([
            'name' => 'Lainnya',
            'code' => 'lain'
        ]);
    }
}
