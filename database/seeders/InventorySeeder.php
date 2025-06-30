<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // Ambil semua product_id dari sub_parts
        $productIds = DB::table('sub_parts')->pluck('sub_part_number')->toArray();

        foreach ($productIds as $productId) {
            // Hitung quantity_reserved dari sales_order_items (hanya yang sales_order status 'CONFIRMED')
            $quantityReserved = DB::table('sales_order_items')
                ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.sales_order_id')
                ->where('sales_orders.status', 'CONFIRMED')
                ->where('sales_order_items.part_number', $productId)
                ->sum('sales_order_items.quantity');

            DB::table('inventory')->insert([
                'product_id' => $productId,
                'location' => 'Main Warehouse',
                'batch_number' => null, // belum diisi sekarang
                'quantity_available' => rand(100, 200),
                'quantity_reserved' => $quantityReserved,
                'quantity_damaged' => 0,
                'minimum_stock' => rand(10, 15),
                'last_updated' => Carbon::now(),
            ]);
        }
    }
}
