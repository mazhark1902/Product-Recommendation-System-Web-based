<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SparePartCustomerSpread extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Customer Spread per Spare Part';

    protected function getData(): array
    {
        $data = DB::table('sales_order_items')
            ->join('sub_parts', 'sales_order_items.part_number', '=', 'sub_parts.sub_part_number')
            ->join('master_part', 'sub_parts.part_number', '=', 'master_part.part_number')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.sales_order_id')
            ->select('master_part.part_name', DB::raw('COUNT(DISTINCT sales_orders.customer_id) as customer_count'))
            ->groupBy('master_part.part_name')
            ->orderByDesc('customer_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Unique Customers',
                    'data' => $data->pluck('customer_count'),
                    'backgroundColor' => '#F87171', // red
                ],
            ],
            'labels' => $data->pluck('part_name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Chart.js v4 uses 'bar' and sets horizontal via options (see below)
    }

    protected function getOptions(): ?array
    {
        return [
            'indexAxis' => 'y', // 👈 for horizontal bar chart
        ];
    }
}
