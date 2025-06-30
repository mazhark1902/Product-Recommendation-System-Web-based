<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryItemsSeeder extends Seeder
{
    public function run()
    {
        $totalInserted = 0;

        // Ambil data delivery_orders secara bertahap (chunk) untuk performa
        DB::table('delivery_orders')->orderBy('id')->chunkById(500, function ($deliveryOrders) use (&$totalInserted) {
            $insertData = [];

            foreach ($deliveryOrders as $deliveryOrder) {
                // Ambil semua sales_order_items berdasarkan sales_order_id dari DO
                $salesOrderItems = DB::table('sales_order_items')
                    ->where('sales_order_id', $deliveryOrder->sales_order_id)
                    ->get();

                foreach ($salesOrderItems as $item) {
                    $insertData[] = [
                        'delivery_order_id' => $deliveryOrder->delivery_order_id,
                        'part_number'       => $item->part_number,
                        'quantity'          => $item->quantity,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }

            // Insert ke delivery_items secara batch biar performa optimal
            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 1000) as $chunk) {
                    DB::table('delivery_items')->insert($chunk);
                    $totalInserted += count($chunk);
                }
            }
        });

        $this->command->info("$totalInserted delivery items berhasil dibuat berdasarkan sales order items.");
    }
}
