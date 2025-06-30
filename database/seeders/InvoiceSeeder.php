<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        $salesOrders = DB::table('sales_orders')->select('sales_order_id')->get();

        foreach ($salesOrders as $so) {
            DB::table('transaction')->insert([
                'sales_order_id' => $so->sales_order_id,
                'invoice_date' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                'due_date' => Carbon::now()->addDays(rand(10, 30))->toDateString(),
                'status' => ['DRAFT', 'UNPAID', 'PAID', 'CANCELLED'][rand(0, 2)],
                'total_amount' => rand(500000, 5000000) / 100,
            ]);
        }
    }
}
