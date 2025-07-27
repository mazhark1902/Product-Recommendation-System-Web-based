<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class TopSpendingCustomersChart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Top 5 Customers by Total Spend';

    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $data = DB::table('transaction')
            ->join('sales_orders', 'transaction.sales_order_id', '=', 'sales_orders.sales_order_id')
            ->select('sales_orders.customer_id', DB::raw('SUM(transaction.total_amount) as total_spend'))
            ->groupBy('sales_orders.customer_id')
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();

        $labels = $data->pluck('customer_id')->toArray();
        $values = $data->pluck('total_spend')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Spend (IDR)',
                    'data' => $values,
                    'backgroundColor' => '#3b82f6', // blue-500
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
