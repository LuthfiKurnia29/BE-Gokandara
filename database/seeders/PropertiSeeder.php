<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Properti;

class PropertiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Properti::create([
            'project_id' => 1,
            'blok_id' => 1,
            'unit_id' => 1,
            'tipe_id' => 1,
            'luas_bangunan' => '5x6',
            'luas_tanah' => '6x10',
            'kelebihan' => 'Dekat dengan fasilitas umum',
            'lokasi' => 'Jalan Mawar Merah No. 10, Bandung',
            'harga' => 500000000,
        ]);

        Properti::create([
            'project_id' => 2,
            'blok_id' => 2,
            'unit_id' => 2,
            'tipe_id' => 2,
            'luas_bangunan' => '6x7',
            'luas_tanah' => '7x12',
            'kelebihan' => 'Akses mudah ke jalan raya',
            'lokasi' => 'Jalan Melati Putih No. 20, Jakarta',
            'harga' => 750000000,
        ]);

        Properti::create([
            'project_id' => 3,
            'blok_id' => 3,
            'unit_id' => 3,
            'tipe_id' => 3,
            'luas_bangunan' => '7x8',
            'luas_tanah' => '8x14',
            'kelebihan' => 'Lingkungan asri dan tenang',
            'lokasi' => 'Jalan Anggrek Biru No. 30, Surabaya',
            'harga' => 1000000000,
        ]);
    }
}
