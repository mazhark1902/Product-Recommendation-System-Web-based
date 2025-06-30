<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotationItemSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '2048M'); // 2GB cukup
        set_time_limit(0); // Hindari timeout

        // Ambil semua quotation_id dari quotation yang Approved (target 55.000)
        $approvedQuotations = DB::table('quotations')
            ->where('status', 'Approved')
            ->pluck('quotation_id')
            ->toArray();

        // Ambil produk dari sub_parts
        $products = DB::table('sub_parts')->pluck('sub_part_number')->toArray();

        if (count($approvedQuotations) < 55000) {
            $this->command->warn('Jumlah quotation Approved kurang dari 55.000. Seeder tetap dijalankan dengan jumlah yang ada.');
        }

        if (count($products) < 10) {
            $this->command->error('Produk di sub_parts terlalu sedikit. Minimal 10 dibutuhkan.');
            return;
        }

        // Tentukan 5 produk favorit (paling sering dibeli)
        shuffle($products);
        $topProducts = array_slice($products, 0, 5);
        $otherProducts = array_slice($products, 5);

        // Distribusi jumlah quotation & item
        $distribution = [
            ['count' => 10000, 'items' => 1],
            ['count' => 15000, 'items' => 2],
            ['count' => 10000, 'items' => 3],
            ['count' => 10000, 'items' => 4],
            ['count' => 10000, 'items' => 5],
        ];

        $start = 0;
        $batchData = [];

        // === Approved Quotations ===
        foreach ($distribution as $group) {
            $count = $group['count'];
            $itemsPerQuotation = $group['items'];

            $quotationsBatch = array_slice($approvedQuotations, $start, $count);
            $start += $count;

            foreach ($quotationsBatch as $quotationId) {
                $selectedParts = [];

                // Sisipkan produk populer (1-2)
                $numTop = min(rand(1, min(2, $itemsPerQuotation)), count($topProducts));
                $topSelected = collect($topProducts)->random($numTop)->toArray();
                $selectedParts = array_merge($selectedParts, $topSelected);

                // Tambah produk lainnya
                $remaining = $itemsPerQuotation - count($selectedParts);
                if ($remaining > 0 && count($otherProducts) > 0) {
                    $otherSelected = collect($otherProducts)->random($remaining)->toArray();
                    $selectedParts = array_merge($selectedParts, $otherSelected);
                }

                foreach ($selectedParts as $partNumber) {
                    $qty = rand(1, 5);
                    $price = rand(10000, 100000);
                    $subtotal = $qty * $price;

                    $batchData[] = [
                        'quotation_id' => $quotationId,
                        'part_number'  => $partNumber,
                        'unit_price'   => $price,
                        'quantity'     => $qty,
                        'subtotal'     => $subtotal,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];

                    if (count($batchData) >= 1000) {
                        DB::table('quotation_items')->insert($batchData);
                        $batchData = [];
                    }
                }
            }
        }

        // === Pending & Rejected Quotations ===
        $extraStatuses = ['Pending', 'Rejected'];

        foreach ($extraStatuses as $status) {
            $extraQuotations = DB::table('quotations')
                ->where('status', $status)
                ->pluck('quotation_id')
                ->toArray();

            foreach ($extraQuotations as $quotationId) {
                // Random berapa banyak item (1–3)
                $itemCount = rand(1, 3);
                $selectedParts = collect($products)->random($itemCount)->toArray();

                foreach ($selectedParts as $partNumber) {
                    $qty = rand(1, 5);
                    $price = rand(10000, 100000);
                    $subtotal = $qty * $price;

                    $batchData[] = [
                        'quotation_id' => $quotationId,
                        'part_number'  => $partNumber,
                        'unit_price'   => $price,
                        'quantity'     => $qty,
                        'subtotal'     => $subtotal,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];

                    if (count($batchData) >= 1000) {
                        DB::table('quotation_items')->insert($batchData);
                        $batchData = [];
                    }
                }
            }
        }

        // Sisa batch terakhir
        if (!empty($batchData)) {
            DB::table('quotation_items')->insert($batchData);
        }

        $this->command->info('quotation_items berhasil dibuat untuk semua quotation (Approved, Pending, Rejected).');
    }
}
