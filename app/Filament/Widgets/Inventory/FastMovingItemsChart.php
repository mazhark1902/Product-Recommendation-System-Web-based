<?php

namespace App\Filament\Widgets\Inventory;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FastMovingItemsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Fast-Moving Items (Last 30 Days)';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';


    protected function getData(): array
    {
        $data = DB::table('inventory_movements')
            // PERBAIKAN 1: Join menggunakan kolom `product_id` sesuai skema database Anda
            ->join('sub_parts', 'inventory_movements.product_id', '=', 'sub_parts.sub_part_number')
            ->select(
                'sub_parts.sub_part_name',
                // PERBAIKAN 2: Menggunakan kolom `quantity` yang benar
                DB::raw('SUM(ABS(inventory_movements.quantity)) as total_moved')
            )
            ->where('inventory_movements.movement_type', 'out')
            ->where('inventory_movements.movement_date', '>=', Carbon::now()->subDays(30))
            ->groupBy('sub_parts.sub_part_name')
            ->orderBy('total_moved', 'desc')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $data->pluck('total_moved')->all(),
                    'backgroundColor' => '#4BC0C0',
                ],
            ],
            'labels' => $data->pluck('sub_part_name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}