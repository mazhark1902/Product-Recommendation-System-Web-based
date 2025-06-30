<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Quotation Seeder...');

        // Ambil semua outlet dari table outlet_dealers
        $outlets = DB::table('outlet_dealers')->pluck('outlet_code')->toArray();

        if (count($outlets) < 5) {
            $this->command->warn('Outlet dealers harus punya minimal 5 outlet.');
            return;
        }

        // Ambil 5 dealer yang mau dibanyakin
        $priorityOutlets = array_slice($outlets, 0, 5);
        $otherOutlets = array_diff($outlets, $priorityOutlets);

        $quotations = [];

        // 1. Approved - 55,000
        $approvedCount = 55000;
        $priorityApprovedPerOutlet = intdiv($approvedCount, 2); // Bagi 50% untuk 5 outlet prioritas
        $priorityPerOutlet = intdiv($priorityApprovedPerOutlet, count($priorityOutlets)); // Tiap outlet

        $id = 1;

        // ➤ Insert untuk 5 outlet prioritas
        foreach ($priorityOutlets as $outletCode) {
            for ($i = 0; $i < $priorityPerOutlet; $i++) {
                $quotations[] = $this->generateQuotationRow($id++, $outletCode, 'approved');
            }
        }

        // ➤ Sisa approved untuk outlet lainnya
        $remainingApproved = $approvedCount - count($quotations);
        for ($i = 0; $i < $remainingApproved; $i++) {
            $outletCode = $otherOutlets[array_rand($otherOutlets)];
            $quotations[] = $this->generateQuotationRow($id++, $outletCode, 'Approved');
        }

        // 2. Rejected - 4,000
        for ($i = 0; $i < 4000; $i++) {
            $outletCode = $outlets[array_rand($outlets)];
            $quotations[] = $this->generateQuotationRow($id++, $outletCode, 'Rejected');
        }

        // 3. Pending - 1,000
        for ($i = 0; $i < 1000; $i++) {
            $outletCode = $outlets[array_rand($outlets)];
            $quotations[] = $this->generateQuotationRow($id++, $outletCode, 'Pending');
        }

        // Chunk insert untuk performa
        $chunks = array_chunk($quotations, 1000);
        foreach ($chunks as $chunk) {
            DB::table('quotations')->insert($chunk);
        }

        $this->command->info('✅ 60,000 quotations berhasil di-generate!');
    }

    private function generateQuotationRow($id, $outletCode, $status)
    {
        $quotation_date = now()->subDays(rand(1, 180));
        $valid_until = Carbon::parse($quotation_date)->addDays(rand(7, 30));

        return [
            'quotation_id' => 'QUO' . str_pad($id, 6, '0', STR_PAD_LEFT),
            'outlet_code' => $outletCode,
            'quotation_date' => $quotation_date,
            'status' => $status,
            'total_amount' => 0,
            'valid_until' => $valid_until,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
