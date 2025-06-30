<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesOrderStatusPieChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Order Status Breakdown';
    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        // Ambil jumlah per status dari table sales_orders
        $data = DB::table('sales_orders')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $data->pluck('status'),
            'datasets' => [
                [
                    'label' => 'Total Orders',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        '#3B82F6', // blue
                        '#F59E0B', // yellow
                        '#10B981', // green
                        '#EF4444', // red
                        '#8B5CF6', // purple
                        '#6366F1', // indigo
                    ],
                ],
            ],
        ];
    }
}
