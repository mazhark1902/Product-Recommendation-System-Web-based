<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $model = ['RUSH',
        'INNOVA',
        'AVANZA',
        'FORTUNER',
        'OTHER',
        'Kijang',
        'SIENTA',
        'CALYA',
        'AGYA',
        'YARIS',
        'VIOS',
        'ETIOS',
        'HI ACE',
        'NAV1',
        'ALPHARD',
        'HILUX',
        'COROLLA',
        'CAMRY',
        'VOXY',
        'COROLLA CROSS',
        'LEXUS',
        'DYNA',
        'LAND CRUISER'];

        $type = ['Heavy SVC','Light_SVC'];

        
        for ($i = 1; $i <= 10000; $i++) {
            
            DB::table('_service_')->insert([
                // 'outlet_code' => $outlet[array_rand($outlet)],
                // 'service_code' => rand(100000, 999999), // must be integer as per DB
                // 'service_date' => 'INV' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'kilometer' => rand(10000,100000),
                'service_type' => $type[array_rand($type)],
                'car_id' => 'ID'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'car_model' => $model[array_rand($model)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
