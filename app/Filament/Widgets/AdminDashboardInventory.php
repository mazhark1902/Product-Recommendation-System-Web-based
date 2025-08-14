<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\Inventory;


class AdminDashboardInventory extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
// Get the table name from the model to avoid hardcoding errors
        $inventoryTable = (new Inventory())->getTable();

        // Run a single query to get all SUM data for better efficiency
        $stockTotals = DB::table($inventoryTable)
            ->selectRaw('SUM(quantity_available) as total_available, SUM(quantity_reserved) as total_reserved')
            ->first();
        
        // Calculate the total value of the inventory
        $totalValue = Inventory::query()
            ->join('sub_parts', 'inventory.product_id', '=', 'sub_parts.sub_part_number')
            ->sum(DB::raw('inventory.quantity_available * sub_parts.cost'));

        // Get the values from the query result, defaulting to 0 if null
        $availableStock = $stockTotals->total_available ?? 0;
        $reservedStock = $stockTotals->total_reserved ?? 0;
        
        // Calculate the actual free stock
        $freeStock = $availableStock - $reservedStock;

        return [
            Stat::make('Total Inventory Value', 'Rp ' . number_format($totalValue, 2))
                ->description('The total value of all available stock')
                ->color('success'),

                Stat::make('Free Stock', number_format($freeStock))
                ->description('The amount of safe stock that can be promised to customers.')
                ->color('success'),
                
                Stat::make('Reserved Stock', number_format($reservedStock))
                ->description('Stock that has been allocated for active sales orders.')
                ->color('warning'),
                
                // Stat::make('Available Stock (On Hand)', number_format($availableStock))
                //     ->description('Total physical stock recorded across all warehouses.')
                //     ->color('primary'),
        ];
    }
}
