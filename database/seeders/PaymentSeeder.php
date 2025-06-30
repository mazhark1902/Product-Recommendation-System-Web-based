<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua transaksi yang sudah PAID
        $paidInvoices = DB::table('transaction')
            ->where('status', 'PAID')
            ->get();

        $paymentMethods = ['Bank Transfer', 'Credit Note', 'e-Wallet', 'Cheque'];
        $payments = [];

        foreach ($paidInvoices as $index => $invoice) {
            $paymentId = 'PY-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);

            $payments[] = [
                'payment_id'      => $paymentId,
                'invoice_id'      => $invoice->invoice_id,
                'payment_date'    => Carbon::now()->subDays(rand(1, 20))->toDateString(),
                'amount_paid'     => $invoice->total_amount,
                'payment_method'  => $paymentMethods[array_rand($paymentMethods)],
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        // Insert all at once
        foreach (array_chunk($payments, 1000) as $batch) {
            DB::table('payments')->insert($batch);
        }

        $this->command->info(count($payments) . ' payments berhasil dibuat untuk invoice yang PAID.');
    }
}
