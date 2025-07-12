<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopSparePartsByQuantity extends ChartWidget
{
    protected static ?string $heading = 'Top Spare Parts by Quantity Ordered';

    protected static ?int $sort = 0;

    protected function getData(): array
    {
        $data = DB::table('sales_order_items')
            ->join('sub_parts', 'sales_order_items.part_number', '=', 'sub_parts.sub_part_number')
            ->join('master_part', 'sub_parts.part_number', '=', 'master_part.part_number')
            ->select('master_part.part_name', DB::raw('SUM(sales_order_items.quantity) as total_quantity'))
            ->groupBy('master_part.part_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();
        
        $labels = $data->pluck('part_name')->toArray();
        $values = $data->pluck('total_quantity')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Quantity Ordered',
                    'data' => $values,
                    'backgroundColor' => '#facc15', // yellow
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
