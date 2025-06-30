<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionTableSeeder extends Seeder
{
    public function run(): void
    {
        $salesOrders = DB::table('sales_orders')->get();

        // Ambil semua delivery_orders duluan (1 query)
        $deliveryOrders = DB::table('delivery_orders')->get()->groupBy('sales_order_id');

        $transactions = [];
        $counter = 1;

        foreach ($salesOrders as $order) {
            $salesOrderId = $order->sales_order_id;
            $deliveryStatuses = [];

            if ($deliveryOrders->has($salesOrderId)) {
                $deliveryStatuses = $deliveryOrders[$salesOrderId]->pluck('status')
                    ->map(fn($s) => strtoupper($s))
                    ->toArray();
            }

            $orderStatus = strtoupper($order->status);
            $transactionStatus = 'UNPAID'; // default

            // Rule PAID
            if ($orderStatus === 'DELIVERED' && in_array('DELIVERED', $deliveryStatuses)) {
                $transactionStatus = 'PAID';
            } elseif ($orderStatus === 'CONFIRMED' && (
                in_array('READY', $deliveryStatuses) || in_array('CANCELLED', $deliveryStatuses)
            )) {
                $transactionStatus = 'PAID';
            }

            $invoiceDate = Carbon::now()->subDays(rand(0, 30));
            $dueDate = (clone $invoiceDate)->addDays(rand(7, 30));
            $invoiceCode = 'INV-' . $invoiceDate->format('Ymd') . '-' . str_pad($counter++, 4, '0', STR_PAD_LEFT);

            $transactions[] = [
                'invoice_id'      => $invoiceCode,
                'sales_order_id'  => $salesOrderId,
                'invoice_date'    => $invoiceDate->toDateString(),
                'due_date'        => $dueDate->toDateString(),
                'status'          => $transactionStatus,
                'total_amount'    => $order->total_amount,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        // Batch insert untuk performa (bisa dipecah kalau >1000)
        foreach (array_chunk($transactions, 1000) as $batch) {
            DB::table('transaction')->insert($batch);
        }

        $this->command->info(count($transactions) . ' transaksi berhasil dibuat.');
    }
}
