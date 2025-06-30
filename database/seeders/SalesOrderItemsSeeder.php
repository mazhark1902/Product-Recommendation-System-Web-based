<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesOrderItemsSeeder extends Seeder
{
    public function run(): void
    {
        $salesOrders = DB::table('sales_orders')->get();

        if ($salesOrders->isEmpty()) {
            $this->command->warn('Tidak ada sales order yang ditemukan.');
            return;
        }

        $batchInsert = [];

        foreach ($salesOrders as $so) {
            $quotationItems = DB::table('quotation_items')
                ->where('quotation_id', $so->quotation_id)
                ->get();

            if ($quotationItems->isEmpty()) {
                $this->command->warn("Quotation item kosong untuk quotation_id: {$so->quotation_id}");
                continue;
            }

            foreach ($quotationItems as $item) {
                if (!$item->part_number) {
                    $this->command->warn("Part number tidak ditemukan di quotation_item ID: {$item->id}");
                    continue;
                }

                $batchInsert[] = [
                    'sales_order_id' => $so->sales_order_id,
                    'part_number'    => $item->part_number,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'subtotal'       => $item->subtotal,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];

                // Insert per 1000 data
                if (count($batchInsert) >= 1000) {
                    DB::table('sales_order_items')->insert($batchInsert);
                    $batchInsert = [];
                }
            }
        }

        // Sisa data terakhir
        if (!empty($batchInsert)) {
            DB::table('sales_order_items')->insert($batchInsert);
        }

        $this->command->info('Sales order items berhasil dibuat dari quotation items.');
    }
}
