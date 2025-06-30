<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DealerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dealers = [
            'DUB001196' => ['Name' => 'Dealer A', 'Province' => 'Aceh', 'email' => 'DealerA@gmail.com'],
            'GRT001049' => ['Name' => 'Dealer B', 'Province' => 'Sumatera Utara', 'email' => 'DealerB@gmail.com'],
            'WIM001070' => ['Name' => 'Dealer C', 'Province' => 'Sumatera Barat', 'email' => 'DealerC@gmail.com'],
            'HAA001076' => ['Name' => 'Dealer D', 'Province' => 'Jambi', 'email' => 'DealerD@gmail.com'],
            'PEP001039' => ['Name' => 'Dealer E', 'Province' => 'Sumatera Selatan', 'email' => 'DealerE@gmail.com'],
            'TUT001080' => ['Name' => 'Dealer F', 'Province' => 'Bengkulu', 'email' => 'DealerF@gmail.com'],
            'AGT001193' => ['Name' => 'Dealer G', 'Province' => 'Riau', 'email' => 'DealerG@gmail.com'],
            'SJA001259' => ['Name' => 'Dealer H', 'Province' => 'Lampung', 'email' => 'DealerH@gmail.com'],
            'TUT001168' => ['Name' => 'Dealer I', 'Province' => 'DKI Jakarta', 'email' => 'DealerI@gmail.com'],
            'NAC001269' => ['Name' => 'Dealer J', 'Province' => 'Jawa Barat', 'email' => 'DealerJ@gmail.com'],
            'DMS001308' => ['Name' => 'Dealer K', 'Province' => 'Jawa Tengah', 'email' => 'DealerK@gmail.com'],
            'HAK001288' => ['Name' => 'Dealer L', 'Province' => 'D.I. Yogyakarta', 'email' => 'DealerL@gmail.com'],
            'DAT001273' => ['Name' => 'Dealer M', 'Province' => 'Jawa Timur', 'email' => 'DealerM@gmail.com'],
            'TUT001109' => ['Name' => 'Dealer N', 'Province' => 'Bali', 'email' => 'DealerN@gmail.com'],
            'HAK001199' => ['Name' => 'Dealer O', 'Province' => 'NTB', 'email' => 'DealerO@gmail.com'],
            'TAG001258' => ['Name' => 'Dealer P', 'Province' => 'NTT', 'email' => 'DealerP@gmail.com'],
            'ANT001069' => ['Name' => 'Dealer Q', 'Province' => 'Kalimantan Barat', 'email' => 'DealerQ@gmail.com'],
            'NAC001156' => ['Name' => 'Dealer R', 'Province' => 'Kalimantan Tengah', 'email' => 'DealerR@gmail.com'],
            'DUB001287' => ['Name' => 'Dealer S', 'Province' => 'Kalimantan Selatan', 'email' => 'DealerS@gmail.com'],
            'AKA001052' => ['Name' => 'Dealer T', 'Province' => 'Kalimantan Timur', 'email' => 'DealerT@gmail.com'],
            'AST005087' => ['Name' => 'Dealer U', 'Province' => 'Sulawesi Utara', 'email' => 'DealerU@gmail.com'],
            'ANT001180' => ['Name' => 'Dealer V', 'Province' => 'Sulawesi Tengah', 'email' => 'DealerV@gmail.com'],
            'AST007139' => ['Name' => 'Dealer W', 'Province' => 'Sulawesi Selatan', 'email' => 'DealerW@gmail.com'],
            'NAC001225' => ['Name' => 'Dealer X', 'Province' => 'Sulawesi Tenggara', 'email' => 'DealerX@gmail.com'],
            'WIT001042' => ['Name' => 'Dealer Y', 'Province' => 'Maluku', 'email' => 'DealerY@gmail.com'],
            'DUC001038' => ['Name' => 'Dealer Z', 'Province' => 'Papua', 'email' => 'DealerZ@gmail.com'],
            'AST008208' => ['Name' => 'Dealer AA', 'Province' => 'Timor Timur', 'email' => 'DealerAA@gmail.com'],
            'ANT001351' => ['Name' => 'Dealer AB', 'Province' => 'Otorita Batam', 'email' => 'DealerAB@gmail.com'],
            'HAA001002' => ['Name' => 'Dealer AC', 'Province' => 'Banten', 'email' => 'DealerAC@gmail.com'],
            'AST008215' => ['Name' => 'Dealer AD', 'Province' => 'Bali - AAM', 'email' => 'DealerAD@gmail.com'],
            'NAC001029' => ['Name' => 'Dealer AF', 'Province' => 'Gorontalo', 'email' => 'DealerAF@gmail.com'],
            'NAC001098' => ['Name' => 'Dealer AG', 'Province' => 'Kepulauan Bangka Belitung', 'email' => 'DealerAG@gmail.com'],
            'SET001040' => ['Name' => 'Dealer AH', 'Province' => 'Kepulauan Riau', 'email' => 'DealerAH@gmail.com'],
            'NAC001050' => ['Name' => 'Dealer AI', 'Province' => 'Papua Barat', 'email' => 'DealerAI@gmail.com'],
            'ANT001200' => ['Name' => 'Dealer AJ', 'Province' => 'Maluku Utara', 'email' => 'DealerAJ@gmail.com'],
            'AST006093' => ['Name' => 'Dealer AK', 'Province' => 'Sulawesi Barat', 'email' => 'DealerAK@gmail.com'],
            'AUT001216' => ['Name' => 'Dealer AM', 'Province' => 'Kalimantan Utara', 'email' => 'DealerAM@gmail.com'],
        ];
        foreach ($dealers as $code => $data) {
            DB::table('dealers')->insert([
                'outlet_code' => $code,
                'dealer_name' => $data['Name'],
                'province' => $data['Province'],
                'email' => $data['email'],
            ]);
        
    }
}}
