<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
class TopCustomerBarChart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Top 10 Dealers by Revenue';

    protected function getData(): array
    {
        $data = DB::table('sales_orders')
            ->join('outlet_dealers', 'sales_orders.customer_id', '=', 'outlet_dealers.outlet_code')
            ->select('outlet_dealers.outlet_name', DB::raw('SUM(sales_orders.total_amount) as total_revenue'))
            ->groupBy('outlet_dealers.outlet_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue',
                    'data' => $data->pluck('total_revenue'),
                    'backgroundColor' => ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#3B82F6'],
                ],
            ],
            'labels' => $data->pluck('outlet_name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): ?array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}