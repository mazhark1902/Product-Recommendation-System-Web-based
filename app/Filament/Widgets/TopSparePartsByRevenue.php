<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopSparePartsByRevenue extends ChartWidget
{
    protected static ?string $heading = 'Top Spare Parts by Revenue';

    protected function getData(): array
    {
            $data = DB::table('sales_order_items')
            ->join('sub_parts', 'sales_order_items.part_number', '=', 'sub_parts.sub_part_number')
            ->join('master_part', 'sub_parts.part_number', '=', 'master_part.part_number')
            ->select('master_part.part_name', DB::raw('SUM(sales_order_items.subtotal) as total_revenue'))
            ->groupBy('master_part.part_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
        
       return [
            'datasets' => [
                [
                    'label' => 'Total Revenue (IDR)',
                    'data' => $data->pluck('total_revenue'),
                    'backgroundColor' => '#34D399', // Green
                ],
            ],
            'labels' => $data->pluck('part_name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
