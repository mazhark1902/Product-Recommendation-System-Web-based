<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class CustomerChurnStats extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        $today = Carbon::create(2025, 6, 29);

        $data = DB::table('sales_orders')
            ->select('customer_id', DB::raw('MAX(order_date) as last_order_date'))
            ->groupBy('customer_id')
            ->get();

        $totalCustomers = $data->count();

        $activeLast30 = $data->filter(function ($row) use ($today) {
            return Carbon::parse($row->last_order_date)->diffInDays($today) <= 30;
        })->count();

        $highRisk = $data->filter(function ($row) use ($today) {
            return Carbon::parse($row->last_order_date)->diffInDays($today) > 60;
        })->count();

        $avgDaysSince = $data->map(function ($row) use ($today) {
            return Carbon::parse($row->last_order_date)->diffInDays($today);
        })->avg();

        $churnRate = $totalCustomers > 0 ? ($highRisk / $totalCustomers) * 100 : 0;

        return [
            Stat::make('Active Customers (Last 30 Days)', $activeLast30)
                ->description('Placed order in last 30 days')
                ->color('success'),

            Stat::make('High Risk Customers', $highRisk)
                ->description('No orders in >60 days')
                ->color('danger'),

            Stat::make('Avg Days Since Last Order', number_format($avgDaysSince, 1) . ' days')
                ->description('Across all customers')
                ->color('warning'),

            Stat::make('Churn Rate', number_format($churnRate, 1) . '%')
                ->description('Inactive >60 days / total customers')
                ->color('gray'),
        ];
    }
}
