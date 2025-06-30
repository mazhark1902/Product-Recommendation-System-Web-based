<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditMemosSeeder extends Seeder
{
    public function run()
    {
        $statusMap = [
            'PENDING_REFUND' => ['REFUND', 5],
            'REFUNDED'       => ['REFUND', 95],
            'DRAFT'          => ['CREDIT_MEMO', 20],
            'ISSUED'         => ['CREDIT_MEMO', 20],
            'APPLIED'        => ['CREDIT_MEMO', 30],
            'REJECTED'       => ['CREDIT_MEMO', 20],
            'EXPIRED'        => ['CREDIT_MEMO', 10],
        ];

        $counter = 1;

        foreach ($statusMap as $status => [$refundAction, $count]) {
            $returns = DB::table('product_returns')
                ->where('refund_action', $refundAction)
                ->limit($count)
                ->get();

            foreach ($returns as $return) {
                // Ambil price dari tabel sub_parts berdasarkan part_number (foreign key ke sub_part_number)
                $price = DB::table('sub_parts')
                    ->where('sub_part_number', $return->part_number)
                    ->value('price');

                if (!$price) {
                    echo "❌ Price tidak ditemukan untuk sub_part_number: {$return->part_number}\n";
                    continue;
                }

                $amount = $return->quantity * $price;
                $issuedDate = Carbon::parse($return->return_date);

                // Khusus status EXPIRED, buat due date lebih duluan dari issued date
                if ($status === 'EXPIRED') {
                    $issuedDate = $issuedDate->copy()->addYears(2);
                    $dueDate = $issuedDate->copy()->subYear();
                } else {
                    $dueDate = $issuedDate->copy()->addYear();
                }

                DB::table('credit_memos')->insert([
                    'credit_memos_id' => 'CM-' . str_pad($counter++, 5, '0', STR_PAD_LEFT),
                    'return_id'       => $return->return_id,
                    'amount'          => $amount,
                    'issued_date'     => $issuedDate->toDateString(),
                    'due_date'        => $dueDate->toDateString(),
                    'status'          => $status,
                ]);
            }
        }

        echo "✅ Seeder credit_memos selesai dibuat.\n";
        echo "🔍 Checking {$refundAction} for {$status} = {$returns->count()} records\n";

    }
}
