<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class stock_reservations extends Seeder
{
    public function run()
    {
        // Ambil semua product_id dari tabel products
        $productIds = DB::table('master_part')->pluck('part_number')->toArray();

        // Buat dummy sales orders jika belum ada
        if (DB::table('sales_order')->count() < 50) {
            foreach (range(1, 50) as $i) {
                DB::table('sales_order')->insert([
                    'order_number' => 'SO' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'customer_name' => 'Customer ' . $i,
                    'order_date' => Carbon::now()->subDays(rand(1, 30)),
                    'status' => 'OPEN',
                ]);
            }
        }

        // Ambil semua sales_order_id
        $salesOrderIds = DB::table('sales_orders')->pluck('sales_order_id')->toArray();

        // Insert 100 dummy stock reservations
        foreach (range(1, 100) as $i) {
            DB::table('stock_reservations')->insert([
                'product_id' => $productIds[array_rand($productIds)],
                'sales_order_id' => $salesOrderIds[array_rand($salesOrderIds)],
                'reserved_quantity' => rand(1, 20),
                'reservation_date' => Carbon::now()->subDays(rand(0, 15)),
                'status' => rand(0, 1) ? 'ACTIVE' : 'RELEASED',
            ]);
        }
    }
}
