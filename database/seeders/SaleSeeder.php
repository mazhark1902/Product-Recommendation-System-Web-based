<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale; // ✅ This is the missing line

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        foreach ([2023, 2024] as $year) {
            foreach ($months as $month) {
                Sale::create([
                    'month' => $month,
                    'year' => $year,
                    'revenue' => rand(10000000, 50000000),
                    'sales' => rand(1000, 3500),
                    'orders' => rand(100, 500),
                    'returns' => rand(0, 30),
                ]);
            }
        }
    }
}
