<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryMovementsSeeder extends Seeder
{
    public function run()
    {
        echo "🔄 Starting OUT movement seeding...\n";

        // Ambil data delivery yang statusnya DELIVERED
        $outItems = DB::table('delivery_orders')
            ->join('delivery_items', 'delivery_orders.delivery_order_id', '=', 'delivery_items.delivery_order_id')
            ->join('sub_parts', 'delivery_items.part_number', '=', 'sub_parts.sub_part_number')
            ->where('delivery_orders.status', 'DELIVERED')
            ->select(
                'delivery_orders.id as delivery_order_id',
                'delivery_orders.delivery_date',
                'delivery_items.id as delivery_item_id',
                'delivery_items.quantity',
                'delivery_items.part_number as product_id'
            )
            ->get();

        // Cek apakah ada sub_parts yang hilang (prevent FK error)
        $productParts = $outItems->pluck('product_id')->unique();

        foreach ($productParts as $partNumber) {
            $exists = DB::table('sub_parts')->where('sub_part_number', $partNumber)->exists();

            if (!$exists) {
                DB::table('sub_parts')->insert([
                    'sub_part_number' => $partNumber,
                    'description' => 'Auto-generated part from OUT seeder',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                echo "✅ Inserted missing sub_part: $partNumber\n";
            }
        }

        // Insert OUT movement
        $insertedOut = 0;

        foreach ($outItems as $item) {
            DB::table('inventory_movements')->insert([
                'inventory_movement_id' => 'OUTMOV' . str_pad($item->delivery_item_id, 5, '0', STR_PAD_LEFT),
                'product_id' => $item->product_id,
                'movement_type' => 'OUT',
                'quantity' => $item->quantity ?? rand(1, 10),
                'movement_date' => $item->delivery_date ?? now()->subDays(rand(1, 10)),
                'reference_type' => 'delivery_orders',
                'reference_id' => $item->delivery_order_id,
                'notes' => 'Auto-generated OUT movement',
                'batch_number' => 'OUT_' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            ]);
            $insertedOut++;
        }

        echo "✅ OUT movement done. Inserted: $insertedOut\n\n";

        // IN Movement
        echo "🔄 Starting IN movement seeding...\n";

        $inventoryData = DB::table('inventory')->get();
        $insertedIn = 0;

        foreach ($inventoryData as $inv) {
            DB::table('inventory_movements')->insert([
                'inventory_movement_id' => 'INMOV' . str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                'product_id' => $inv->product_id,
                'movement_type' => 'IN',
                'quantity' => $inv->quantity_available + $inv->quantity_reserved + $inv->quantity_damaged,
                'movement_date' => now()->subDays(rand(5, 30)),
                'reference_type' => 'purchase_order',
                'reference_id' => rand(1000, 9999),
                'notes' => 'Auto-generated IN movement',
                'batch_number' => 'IN_' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            ]);
            $insertedIn++;
        }

        echo "✅ IN movement done. Inserted: $insertedIn\n";
    }
}
