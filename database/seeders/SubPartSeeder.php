<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubPartSeeder extends Seeder
{
    public function run(): void
    {
        $jokes = [
            'mirip air', 'anti macet', 'gak bisa move on', 'level dewa', 'kilat banget',
            'bisa buat masak', 'licin parah', 'aromanya wangi', 'ngacir', 'super irit',
            'spesial tanggal tua', 'hemat energi', 'kaya akan cinta', 'bikin adem',
            'ga bikin nangis', 'manja banget', 'buat balapan', 'edisi mantan',
            'ngebut edition', 'nyetrum dikit', 'no debat', 'limited edition',
            'gak dijual bebas', 'khusus sultan', 'versi bocil'
        ];

        $subParts = [];
        $totalSubParts = 0;

        $masterParts = DB::table('master_part')->get();

        foreach ($masterParts as $part) {
            $jumlahSub = rand(1, 5);

            for ($i = 0; $i < $jumlahSub && $totalSubParts < 500; $i++) {
                $kataLucu = fake()->randomElement($jokes);
                $subParts[] = [
                    'part_number' => $part->part_number,
                    'sub_part_name' => strtoupper($part->part_name) . '_' . $kataLucu,
                    'sub_part_number' => 'subPART-' . strtoupper(Str::random(8)),
                    'price' => fake()->numberBetween(5000, 300000),
                ];
                $totalSubParts++;
            }

            if ($totalSubParts >= 500) {
                break;
            }
        }

        DB::table('sub_parts')->insert($subParts);
    }
}
