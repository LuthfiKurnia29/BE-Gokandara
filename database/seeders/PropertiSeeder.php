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
            'Project_Id' => 1,
            'Blok_Id' => 1,
            'Unit_Id' => 1,
            'Tipe_Id' => 1,
            'Luas_Bangunan' => '5x6',
            'Luas_Tanah' => '6x10',
            'Kelebihan' => 'Dekat dengan fasilitas umum',
            'Lokasi' => 'Jalan Mawar Merah No. 10, Bandung',
            'Harga' => 500000000,
        ]);

        Properti::create([
            'Project_Id' => 2,
            'Blok_Id' => 2,
            'Unit_Id' => 2,
            'Tipe_Id' => 2,
            'Luas_Bangunan' => '6x7',
            'Luas_Tanah' => '7x12',
            'Kelebihan' => 'Akses mudah ke jalan raya',
            'Lokasi' => 'Jalan Melati Putih No. 20, Jakarta',
            'Harga' => 750000000,
        ]);

        Properti::create([
            'Project_Id' => 3,
            'Blok_Id' => 3,
            'Unit_Id' => 3,
            'Tipe_Id' => 3,
            'Luas_Bangunan' => '7x8',
            'Luas_Tanah' => '8x14',
            'Kelebihan' => 'Lingkungan asri dan tenang',
            'Lokasi' => 'Jalan Anggrek Biru No. 30, Surabaya',
            'Harga' => 1000000000,
        ]);
    }
}
