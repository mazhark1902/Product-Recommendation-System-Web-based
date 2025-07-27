<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SparePartsStats extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        // 1. Total Distinct Parts Sold
        $distinctParts = DB::table('sales_order_items')
            ->select('part_number')
            ->distinct()
            ->count();

        // 2. Most Sold Part (by quantity)
        $mostSoldPart = DB::table('sales_order_items')
            ->join('sub_parts', 'sales_order_items.part_number', '=', 'sub_parts.sub_part_number')
            ->join('master_part', 'sub_parts.part_number', '=', 'master_part.part_number')
            ->select('master_part.part_name', DB::raw('SUM(sales_order_items.quantity) as total_qty'))
            ->groupBy('master_part.part_name')
            ->orderByDesc('total_qty')
            ->first();

        // 3. Top Revenue Part (by subtotal)
        $topRevenuePart = DB::table('sales_order_items')
            ->join('sub_parts', 'sales_order_items.part_number', '=', 'sub_parts.sub_part_number')
            ->join('master_part', 'sub_parts.part_number', '=', 'master_part.part_number')
            ->select('master_part.part_name', DB::raw('SUM(sales_order_items.subtotal) as total_revenue'))
            ->groupBy('master_part.part_name')
            ->orderByDesc('total_revenue')
            ->first();

        // 4. Total Revenue from All Parts
        $totalRevenue = DB::table('sales_order_items')
            ->sum('subtotal');

        return [
            Stat::make('Distinct Parts Sold', number_format($distinctParts))
                ->description('Jumlah part berbeda yang pernah dijual')
                ->color('success'),

            Stat::make('Most Sold Part', $mostSoldPart?->part_name ?? 'N/A')
                ->description('Berdasarkan total kuantitas')
                ->color('warning'),

            Stat::make('Top Revenue Part', $topRevenuePart?->part_name ?? 'N/A')
                ->description('Berdasarkan total subtotal')
                ->color('info'),

            Stat::make('Total Revenue from Parts', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Akumulasi subtotal dari semua part')
                ->color('primary'),
        ];
    }
}
