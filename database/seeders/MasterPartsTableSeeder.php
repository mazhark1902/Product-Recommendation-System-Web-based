<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterPartSTableSeeder extends Seeder
{
    public function run(): void
    {
        $partNames = [
            'Oli', 'Kampas Rem', 'Lampu Sen', 'Klakson', 'Filter Udara', 'Filter Oli',
            'Radiator', 'Aki', 'Ban Depan', 'Ban Belakang', 'Velg', 'Busi', 'CDI',
            'Kabel Gas', 'Karburator', 'Gear Set', 'Shockbreaker', 'Knalpot',
            'Spion', 'Lampu Depan', 'Lampu Belakang', 'Stang', 'Jok', 'Cover Body',
            'Tutup Tangki', 'Handle Rem', 'Handle Kopling', 'Master Rem', 'ECU',
            'Sensor Oksigen', 'Throttle Body', 'Pompa Bensin', 'Injektor', 'Karet Footstep',
            'Kick Starter', 'Piston', 'Ring Seher', 'Blok Mesin', 'Klep', 'Rantai Mesin',
            'CVT', 'Roller CVT', 'V-Belt', 'Clutch Housing', 'Bearing', 'Fan Radiator',
            'Thermostat', 'Timer Chain', 'Kopling Ganda', 'CDI Racing', 'Oli Gardan',
            'Kipas Pendingin', 'Baut Roda', 'Seal Oli', 'Seal CVT', 'Kampas Kopling',
            'Speedometer', 'Sensor Kecepatan', 'Switch Rem', 'Relay Starter', 'Regulator',
            'Lampu Plat', 'Bracket Plat', 'Fuse', 'Relay', 'Cover Radiator', 'Ignition Coil'
        ];

        $insertData = [];

        for ($i = 0; $i < 150; $i++) {
            $partName = fake()->randomElement($partNames);
            $insertData[] = [
                'part_number' => 'PART-' . strtoupper(Str::random(6)),
                'part_name' => $partName,
                'part_price' => fake()->numberBetween(10000, 5000000),
            ];
        }

        DB::table('master_part')->insert($insertData);
    }
}
