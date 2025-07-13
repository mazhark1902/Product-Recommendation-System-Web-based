<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubPart;
use App\Models\InventoryMovement;
use App\Models\DemandForecast;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastDemand extends Command
{
    protected $signature = 'app:forecast-demand';
    protected $description = 'Analyze historical sales data to forecast future demand.';

    public function handle()
    {
        $this->info('Starting demand forecasting...');

        // Analisis data 3 bulan terakhir
        $monthsToAnalyze = 3;
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths($monthsToAnalyze);

        // Ambil semua sub-part
        $subParts = SubPart::all();

        foreach ($subParts as $part) {
            // Hitung total penjualan
            $totalSales = InventoryMovement::where('product_id', $part->sub_part_number)
                ->where('movement_type', 'OUT')
                ->whereBetween('movement_date', [$startDate, $endDate])
                ->sum(DB::raw('ABS(quantity)')); // Gunakan quantity yang ada di inventory_movements

            // Rata-rata penjualan bulanan
            $averageMonthlySales = $totalSales / $monthsToAnalyze;

            // Ambil stok saat ini
            $currentStock = Inventory::where('product_id', $part->sub_part_number)->sum('quantity_available');

            // Logika sederhana: Rekomendasi stok adalah 1.5x dari rata-rata penjualan bulanan
            $safetyFactor = 1.5;
            $recommendedStock = ceil($averageMonthlySales * $safetyFactor);

            // Simpan hasil prediksi
            DemandForecast::updateOrCreate(
                ['sub_part_number' => $part->sub_part_number],
                [
                    'sub_part_name' => $part->sub_part_name,
                    'average_monthly_sales' => $averageMonthlySales,
                    'recommended_stock_level' => $recommendedStock,
                    'current_stock' => $currentStock,
                    'forecast_date' => Carbon::now()
                ]
            );
        }

        $this->info('Demand forecasting completed successfully!');
    }
}