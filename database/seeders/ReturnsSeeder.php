<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturnsSeeder extends Seeder
{
    public function run()
    {
        $deliveredOrders = DB::table('sales_orders')
            ->where('status', 'DELIVERED')
            ->pluck('sales_order_id')
            ->toArray();

        $products = DB::table('sub_parts')->pluck('sub_part_number')->toArray();

        // Ambil enum values dari definisi kolom reason
        $enumResult = DB::selectOne("SHOW COLUMNS FROM product_returns WHERE Field = 'reason'");
        preg_match("/^enum\((.*)\)$/", $enumResult->Type, $matches);
        $reasons = array_map(function ($value) {
            return trim($value, "'");
        }, explode(',', $matches[1]));

        if (empty($reasons)) {
            echo "⚠️ Tidak bisa ambil enum reason dari schema. Seeder dibatalkan.\n";
            return;
        }

        $datePrefix = Carbon::now()->format('Ymd');
        $counter = 1;

        // 100 GOOD
        for ($i = 0; $i < 100; $i++) {
            DB::table('product_returns')->insert([
                'return_id' => 'RTN-' . $datePrefix . '-' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                'sales_order_id' => $deliveredOrders[array_rand($deliveredOrders)],
                'part_number' => $products[array_rand($products)],
                'quantity' => rand(1, 5),
                'return_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                'reason' => $reasons[array_rand($reasons)],
                'condition' => 'GOOD',
            ]);
        }

        // 100 DAMAGED
        for ($i = 0; $i < 100; $i++) {
            DB::table('product_returns')->insert([
                'return_id' => 'RTN-' . $datePrefix . '-' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                'sales_order_id' => $deliveredOrders[array_rand($deliveredOrders)],
                'part_number' => $products[array_rand($products)],
                'quantity' => rand(1, 5),
                'return_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                'reason' => $reasons[array_rand($reasons)],
                'condition' => 'DAMAGED',
            ]);
        }

        echo "✅ Seeder selesai. 100 GOOD + 100 DAMAGED inserted.\n";
    }
}
