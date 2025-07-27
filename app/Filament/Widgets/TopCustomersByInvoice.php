<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class TopCustomersByInvoice extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Top Dealers by Invoice Amount';

    protected function getData(): array
    {
        $data = DB::table('transaction')
            ->join('sales_orders', 'transaction.sales_order_id', '=', 'sales_orders.sales_order_id')
            ->join('outlet_dealers', 'sales_orders.customer_id', '=', 'outlet_dealers.outlet_code')
            ->select('outlet_dealers.outlet_name', DB::raw('SUM(transaction.total_amount) as total'))
            ->groupBy('outlet_dealers.outlet_name')
            ->orderByDesc('total') 
            ->limit(5)
            ->get();

        $labels = $data->pluck('outlet_name')->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Invoice Amount',
                    'data' => $values,
                    'backgroundColor' => '#4ade80', // green
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // or 'horizontalBar' if you prefer (v2 Chart.js only)
    }

    protected static ?int $sort = 5;
}
