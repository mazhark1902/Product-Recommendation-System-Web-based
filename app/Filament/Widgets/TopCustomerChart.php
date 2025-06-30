<?php

    namespace App\Filament\Widgets;

    use Filament\Widgets\ChartWidget;
    use Illuminate\Support\Facades\DB;

    class TopCustomersChart extends ChartWidget
    {
        protected static ?string $heading = 'Top 5 Customers by Revenue';

        protected function getData(): array
        {
            $data = DB::table('sales_orders')
                ->join('dealers', 'sales_orders.customer_id', '=', 'dealers.outlet_code')
                ->select('dealers.dealer_name', DB::raw('SUM(sales_orders.total_amount) as total_revenue'))
                ->groupBy('dealers.dealer_name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get();
                
                dd('masuk getData');

            return [
                'datasets' => [
                    [
                        'label' => 'Total Revenue',
                        'data' => $data->pluck('total_revenue'),
                        'backgroundColor' => ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#3B82F6'],
                    ],
                ],
                'labels' => $data->pluck('dealer_name'),
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
