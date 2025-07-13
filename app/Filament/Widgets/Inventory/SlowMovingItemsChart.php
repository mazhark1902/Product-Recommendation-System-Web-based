<?php

namespace App\Filament\Widgets\Inventory;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SlowMovingItemsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Slow-Moving Items (Last 90 Days)';
    protected static ?int $sort = 7; // Sesuaikan urutannya

    protected function getData(): array
    {
        $data = DB::table('inventory_movements')
            ->join('sub_parts', 'inventory_movements.product_id', '=', 'sub_parts.sub_part_number')
            ->select(
                'sub_parts.sub_part_name',
                DB::raw('SUM(ABS(inventory_movements.quantity)) as total_moved')
            )
            ->where('inventory_movements.movement_type', 'out')
            ->where('inventory_movements.movement_date', '>=', Carbon::now()->subDays(90))
            ->groupBy('sub_parts.sub_part_name')
            ->orderBy('total_moved', 'asc') // Urutkan dari yang terkecil
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $data->pluck('total_moved')->all(),
                    'backgroundColor' => '#F97316', // Orange color
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