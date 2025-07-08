<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderFrequencyByCustomer extends ChartWidget
{
    protected static ?string $heading = 'Order Frequency by Customer (Last 12 Months)';
    protected static ?int $sort = 7;

    // ✅ Tambahkan filter outlet_dealer (pakai outlet_name)
    protected function getFilters(): ?array
    {
        return DB::table('outlet_dealers')
            ->orderBy('outlet_name')
            ->pluck('outlet_name', 'outlet_name') // ['Astra' => 'Astra']
            ->prepend('All Dealers', '') // Tambahkan opsi default
            ->toArray();
    }

    protected function getDefaultFilter(): ?string
    {
        return ''; // All Dealers (default)
    }

    protected function getData(): array
    {
        $selectedDealer = $this->filter; // Ambil dari dropdown filter
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Base query
        $baseQuery = DB::table('sales_orders')
            ->join('outlet_dealers', 'sales_orders.customer_id', '=', 'outlet_dealers.outlet_code')
            ->whereBetween('order_date', [$startDate, $endDate]);

        if (!empty($selectedDealer)) {
            $baseQuery->where('outlet_dealers.outlet_name', $selectedDealer);
        }

        // Get top 10 customers
        $topCustomers = (clone $baseQuery)
            ->select('sales_orders.customer_id', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('sales_orders.customer_id')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->pluck('customer_id');

        // Get monthly order count
        $monthlyOrders = (clone $baseQuery)
            ->whereIn('sales_orders.customer_id', $topCustomers)
            ->selectRaw("sales_orders.customer_id, DATE_FORMAT(order_date, '%Y-%m') as month, COUNT(*) as order_count")
            ->groupBy('sales_orders.customer_id', 'month')
            ->orderBy('month')
            ->get();

        // Generate last 12 months
        $months = collect(range(0, 11))->map(fn($i) => Carbon::now()->subMonths(11 - $i)->format('Y-m'));

        // Format datasets
        $datasets = [];
        foreach ($topCustomers as $customerId) {
            $customerData = $monthlyOrders->where('customer_id', $customerId)->keyBy('month');
            $values = $months->map(fn($month) => $customerData[$month]->order_count ?? 0)->toArray();

            $datasets[] = [
                'label' => $customerId,
                'data' => $values,
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
