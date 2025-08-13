<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class LastPurchaseByCustomer extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Last Purchase Date by Customer';
    protected static ?int $sort = 6;

    // ✅ Tambahkan filter dropdown berdasarkan outlet_dealers
    protected function getFilters(): ?array
    {
        return DB::table('outlet_dealers')
            ->orderBy('outlet_name')
            ->pluck('outlet_name', 'outlet_name') // ['Astra' => 'Astra']
            ->prepend('All Dealers', '') // Optional: tambahkan pilihan 'All'
            ->toArray();
    }

    protected function getDefaultFilter(): ?string
    {
        return ''; // Default: semua dealer
    }

    protected function getData(): array
    {
        $today = Carbon::create(2025, 6, 29);
        $selectedDealer = $this->filter;

        $query = DB::table('sales_orders')
            ->join('outlet_dealers', 'sales_orders.customer_id', '=', 'outlet_dealers.outlet_code');

        if (!empty($selectedDealer)) {
            $query->where('outlet_dealers.outlet_name', $selectedDealer);
        }

        $data = $query
            ->selectRaw("sales_orders.customer_id, MAX(order_date) as last_order")
            ->groupBy('sales_orders.customer_id')
            ->orderByRaw("MAX(order_date) ASC") // Show most inactive first
            ->limit(10)
            ->get();

        $labels = $data->pluck('customer_id')->toArray();

        $values = $data->map(function ($item) use ($today) {
            return Carbon::parse($item->last_order)->diffInDays($today);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Days Since Last Order',
                    'data' => $values,
                    'backgroundColor' => '#facc15', // yellow-400
                ],
            ],
            'labels' => $labels,
        ];
    }

        protected function getMaxHeight(): ?string
{
    return '800px'; // adjust as needed (e.g. 150px, 250px)
}

    protected function getType(): string
    {
        return 'bar';
    }
}
