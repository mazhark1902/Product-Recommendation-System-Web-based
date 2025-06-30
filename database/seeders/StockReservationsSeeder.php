<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockReservationsSeeder extends Seeder
{
    public function run()
    {
        $deliveredOrders = DB::table('sales_orders')
            ->where('status', 'DELIVERED')
            ->get();

        $confirmedOrders = DB::table('sales_orders')
            ->where('status', 'CONFIRMED')
            ->get();

        $reservations = [];

        // Proses untuk DELIVERED (status stock reservation: RELEASED)
        foreach ($deliveredOrders as $order) {
            $items = DB::table('sales_order_items')
                ->where('sales_order_id', $order->sales_order_id)
                ->get();

            foreach ($items as $item) {
                $reservations[] = [
                    'part_number'       => $item->part_number,
                    'sales_order_id'    => $order->sales_order_id,
                    'reserved_quantity' => $item->quantity,
                    'reservation_date'  => Carbon::now()->subDays(rand(1, 15)),
                    'status'            => 'RELEASED',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
        }

        // Proses untuk CONFIRMED (status stock reservation: ACTIVE)
        foreach ($confirmedOrders as $order) {
            $items = DB::table('sales_order_items')
                ->where('sales_order_id', $order->sales_order_id)
                ->get();

            foreach ($items as $item) {
                $reservations[] = [
                    'part_number'       => $item->part_number,
                    'sales_order_id'    => $order->sales_order_id,
                    'reserved_quantity' => $item->quantity,
                    'reservation_date'  => Carbon::now()->subDays(rand(1, 15)),
                    'status'            => 'ACTIVE',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
        }

        foreach (array_chunk($reservations, 1000) as $batch) {
            DB::table('stock_reservations')->insert($batch);
        }

        $this->command->info(count($reservations) . ' stock reservations berhasil dibuat dari sales_orders.');
    }
}
