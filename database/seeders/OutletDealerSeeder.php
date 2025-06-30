<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class OutletDealerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $dealers = DB::table('dealers')->get();

        foreach ($dealers as $dealer) {
            $dealerRegion = strtolower($dealer->province ?? '');

            if (str_contains($dealerRegion, 'Jawa Barat')) {
                $outletCount = 20;
            } elseif (str_contains($dealerRegion, 'Jakarta')) {
                $outletCount = 10;
            } else {
                $outletCount = 5;
            }

            for ($i = 0; $i < $outletCount; $i++) {
                $kecamatan = $faker->city; // Ganti subDistrict
                $outletName = 'Outlet_' . ucwords($kecamatan);
                $outletCode = Str::slug($dealer->dealer_name, '_') . '_' . rand(1000, 9999);
                $cleanedEmailName = Str::slug($outletName, '_');

                DB::table('outlet_dealers')->insert([
                    'outlet_name' => $outletName,
                    'outlet_code' => $outletCode,
                    'dealer_code' => $dealer->dealer_code,
                    'email' => strtolower($cleanedEmailName) . '@gmail.com',
                    'phone' => '+62' . $faker->numerify('8##########'),
                    'address' => 'Kec. ' . $kecamatan . ', ' . ucwords($dealerRegion) . ', Indonesia',
                    'credit_limit' => 0.00,
                ]);
            }
        }
    }
}
