<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\Inventory;
use App\Models\ProductReturn;
use App\Models\SubPart;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AfterSalesKpiWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1; // Highest priority on the dashboard

    protected function getStats(): array
    {
        // KPI 1: Critical Stock
        $criticalStockCount = Inventory::whereRaw('quantity_available <= minimum_stock')->count();

        // KPI 2: Total Returned Items (Last 30 Days)
        $totalReturnsLast30Days = ProductReturn::where('return_date', '>=', Carbon::now()->subDays(30))->count();

        // KPI 3: Total Damaged Stock
        $totalDamagedStock = Inventory::sum('quantity_damaged');

        // FIX 1: Get the 'inventory' table name dynamically
        $inventoryTable = (new Inventory())->getTable(); 

        // KPI 4: Value of Damaged Stock
        $damagedValue = Inventory::query()
            ->where('quantity_damaged', '>', 0)
            // FIX 2: Use the correct table name in the join
            ->join('sub_parts', "{$inventoryTable}.product_id", '=', 'sub_parts.sub_part_number')
            // FIX 3: Use the correct table name in the SUM
            ->sum(DB::raw("{$inventoryTable}.quantity_damaged * sub_parts.cost"));

        return [
            Stat::make('Critical Stock', $criticalStockCount)
                ->description('Items below the minimum threshold')
                ->color($criticalStockCount > 0 ? 'danger' : 'success'),
                // URL temporarily removed to prevent errors. You can add it back
                // if you have an InventoryResource by running:
                // php artisan make:filament-resource Inventory --generate
                // ->url(route('filament.admin.resources.inventories.index')),

            Stat::make('Total Returns (30 Days)', $totalReturnsLast30Days)
                ->description('Returns received this month')
                ->color('warning'),

            Stat::make('Total Damaged Stock', $totalDamagedStock)
                ->description('Total units recorded as damaged')
                ->color('danger'),

            Stat::make('Damaged Stock Value', 'IDR ' . number_format($damagedValue, 2))
                ->description('Total loss from damaged stock')
                ->color('danger'),
        ];
    }
}