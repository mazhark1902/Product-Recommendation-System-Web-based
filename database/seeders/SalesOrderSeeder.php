<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $approvedQuotations = DB::table('quotations')
            ->where('status', 'Approved')
            ->orderBy('quotation_id')
            ->get();

        if ($approvedQuotations->count() < 55000) {
            $this->command->warn('Jumlah quotation Approved kurang dari 55.000. Seeder tetap dijalankan dengan jumlah yang ada.');
        }

        // Tentukan distribusi status
        $statusDistribution = [
            'Delivered' => 54350,
            'Draft'     => 50,
            'Confirmed' => 100,
            'Rejected'  => 500,
        ];

        $data = [];
        $counter = 1;
        $start = 0;

        foreach ($statusDistribution as $status => $amount) {
            $chunk = $approvedQuotations->slice($start, $amount);
            foreach ($chunk as $quotation) {
                $data[] = [
                    'sales_order_id'   => 'SO' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                    'customer_id'      => $quotation->outlet_code,
                    'quotation_id'     => $quotation->quotation_id,
                    'order_date'       => $quotation->quotation_date,
                    'status'           => $status,
                    'total_amount'     => $quotation->total_amount,
                    'delivery_address' => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
                $counter++;
            }
            $start += $amount;
        }

        // Bagi batch insert biar aman
        $chunks = array_chunk($data, 1000);
        foreach ($chunks as $chunk) {
            DB::table('sales_orders')->insert($chunk);
        }

        $this->command->info(count($data) . ' sales orders berhasil dibuat dengan distribusi status yang sesuai.');
    }
}
