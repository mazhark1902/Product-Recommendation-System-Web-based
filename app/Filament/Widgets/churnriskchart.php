<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class churnriskchart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Customer Churn Risk Classification';
    protected static ?int $sort = 6;

    // ✅ Tambahkan dropdown filter berdasarkan dealer
    protected function getFilters(): ?array
    {
        return DB::table('outlet_dealers')
            ->orderBy('outlet_name')
            ->pluck('outlet_name', 'outlet_name') // ['Astra' => 'Astra']
            ->prepend('All Dealers', '')
            ->toArray();
    }

    protected function getDefaultFilter(): ?string
    {
        return ''; // Default semua dealer
    }

    protected function getData(): array
    {
        $selectedDealer = $this->filter;

        // Query dasar
        $query = DB::table('sales_orders')
            ->join('outlet_dealers', 'sales_orders.customer_id', '=', 'outlet_dealers.outlet_code');

        if (!empty($selectedDealer)) {
            $query->where('outlet_dealers.outlet_name', $selectedDealer);
        }

        $customerDays = $query
            ->select('sales_orders.customer_id', DB::raw('MAX(order_date) as last_order_date'))
            ->groupBy('sales_orders.customer_id')
            ->get()
            ->map(function ($row) {
                $row->days_since_last = now()->diffInDays(Carbon::parse($row->last_order_date));
                return $row;
            });

        $days = $customerDays->pluck('days_since_last');

        $min = $days->min();
        $max = $days->max();
        $median = $days->median();

        $riskCounts = ['Low' => 0, 'Medium' => 0, 'High' => 0];

        foreach ($customerDays as $row) {
            if ($row->days_since_last <= $median) {
                $riskCounts['Low']++;
            } elseif ($row->days_since_last < $max) {
                $riskCounts['Medium']++;
            } else {
                $riskCounts['High']++;
            }
        }

        return [
            'labels' => array_keys($riskCounts),
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => array_values($riskCounts),
                    'backgroundColor' => ['#22c55e', '#facc15', '#ef4444'], // green, yellow, red
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
