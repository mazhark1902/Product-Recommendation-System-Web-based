<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $counter = 1;
        $now = Carbon::now();

        // 1. Ambil 54.350 SO yg status 'Delivered'
        $deliveredSO = DB::table('sales_orders')
            ->where('status', 'delivered')
            ->limit(54350)
            ->get();

        if ($deliveredSO->isEmpty()) {
            $this->command->warn('Tidak ada sales order dengan status Delivered.');
            return;
        }

        // Bagi jadi tiga bagian: 54.000 + 200 + 150
        $doDelivered = $deliveredSO->slice(0, 54000);
        $doReady = $deliveredSO->slice(54000, 200);
        $doRejected = $deliveredSO->slice(54200, 150);

        $insertData = [];

        foreach ($doDelivered as $so) {
            $insertData[] = $this->buildDO($counter++, $so->sales_order_id, 'delivered');
        }

        foreach ($doReady as $so) {
            $insertData[] = $this->buildDO($counter++, $so->sales_order_id, 'ready');
        }

        foreach ($doRejected as $so) {
            $insertData[] = $this->buildDO($counter++, $so->sales_order_id, 'cancelled');
        }

        // 2. Ambil 50 SO draft + 100 confirmed
        $pendingSO = DB::table('sales_orders')
            ->whereIn('status', ['Draft', 'Confirmed'])
            ->limit(150)
            ->get();

        foreach ($pendingSO as $so) {
            $insertData[] = $this->buildDO($counter++, $so->sales_order_id, 'pending');
        }

        // Batch insert (lebih cepat & aman)
        foreach (array_chunk($insertData, 1000) as $chunk) {
            DB::table('delivery_orders')->insert($chunk);
        }

        $this->command->info(count($insertData) . ' delivery orders berhasil dibuat.');
    }

    private function buildDO(int $counter, string $salesOrderId, string $status): array
    {
        return [
            'delivery_order_id' => 'DO' . str_pad($counter, 5, '0', STR_PAD_LEFT),
            'sales_order_id'    => $salesOrderId,
            'delivery_date'     => Carbon::now()->subDays(rand(1, 5))->toDateString(),
            'status'            => $status,
            'notes'             => collect([
                'Handle with care',
                'Customer requested morning delivery',
                'Leave at front door if unavailable',
                'Contact before delivery',
                null,
            ])->random(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
