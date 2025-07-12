<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\Inventory;
use App\Models\ProductReturn;
use App\Models\SubPart;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AfterSalesKpiWidget extends BaseWidget
{
    protected static ?int $sort = 1; // Prioritas tertinggi di dashboard

    protected function getStats(): array
    {
        // KPI 1: Stok Kritis
        $criticalStockCount = Inventory::whereRaw('quantity_available <= minimum_stock')->count();

        // KPI 2: Total Item Retur (30 Hari Terakhir)
        $totalReturnsLast30Days = ProductReturn::where('return_date', '>=', Carbon::now()->subDays(30))->count();

        // KPI 3: Jumlah Stok Rusak
        $totalDamagedStock = Inventory::sum('quantity_damaged');

        // PERBAIKAN 1: Dapatkan nama tabel 'inventory' secara dinamis
        $inventoryTable = (new Inventory())->getTable(); 

        // KPI 4: Nilai Stok Rusak
        $damagedValue = Inventory::query()
            ->where('quantity_damaged', '>', 0)
            // PERBAIKAN 2: Gunakan nama tabel yang benar dalam join
            ->join('sub_parts', "{$inventoryTable}.product_id", '=', 'sub_parts.sub_part_number')
            // PERBAIKAN 3: Gunakan nama tabel yang benar dalam SUM
            ->sum(DB::raw("{$inventoryTable}.quantity_damaged * sub_parts.price"));

        return [
            Stat::make('Stok Kritis', $criticalStockCount)
                ->description('Item di bawah batas minimum')
                ->color($criticalStockCount > 0 ? 'danger' : 'success'),
                // URL dihapus sementara untuk mencegah error. Anda bisa menambahkannya lagi
                // jika sudah memiliki InventoryResource dengan menjalankan:
                // php artisan make:filament-resource Inventory --generate
                // ->url(route('filament.admin.resources.inventories.index')),

            Stat::make('Total Retur (30 Hari)', $totalReturnsLast30Days)
                ->description('Retur yang masuk bulan ini')
                ->color('warning'),

            Stat::make('Jumlah Stok Rusak', $totalDamagedStock)
                ->description('Total unit yang tercatat rusak')
                ->color('danger'),

            Stat::make('Nilai Stok Rusak', 'Rp ' . number_format($damagedValue, 2))
                ->description('Total kerugian dari stok rusak')
                ->color('danger'),
        ];
    }
}