<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::create([
            'name' => 'Gudang Utama Cikarang',
            'code' => 'WH-UTAMA',
            'address' => 'Jl. Industri Utama Blok A1, Kawasan Industri Cikarang, Bekasi',
        ]);

        Warehouse::create([
            'name' => 'Gudang Transit Jakarta',
            'code' => 'WH-TRANSIT-JKT',
            'address' => 'Jl. Logistik No. 22, Cakung, Jakarta Timur',
        ]);

        Warehouse::create([
            'name' => 'Gudang Barang Retur',
            'code' => 'WH-RETUR',
            'address' => 'Jl. Industri Utama Blok A2, Kawasan Industri Cikarang, Bekasi',
        ]);
    }
}