<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Menu::create([
            'id' => 1,
            'name' => 'Dashboard',
            'code' => 'Dashboard'
        ]);
        Menu::create([
            'id' => 2,
            'name' => 'Properti',
            'code' => 'Property'
        ]);
        Menu::create([
            'id' => 3,
            'name' => 'MasterData',
            'code' => 'Data'
        ]);
        Menu::create([
            'id' => 4,
            'name' => 'Transaksi',
            'code' => 'Transaction'
        ]);
        Menu::create([
            'id' => 5,
            'name' => 'Analisa',
            'code' => 'Analyst'
        ]);
        Menu::create([
            'id' => 6,
            'name' => 'Konsumen',
            'code' => 'Consument'
        ]);
        Menu::create([
            'id' => 7,
            'name' => 'Pesan',
            'code' => 'Chat'
        ]);
        Menu::create([
            'id' => 8,
            'name' => 'Pengaturan',
            'code' => 'Setting'
        ]);
    }
}
