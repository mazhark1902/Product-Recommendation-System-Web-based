<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SalesDashboardKpi extends BaseWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        // Total Revenue (from paid invoices)
        $totalRevenue = DB::table('transaction')
            ->where('status', 'PAID')
            ->sum('total_amount');

        // Total Sales Orders (CONFIRMED & DELIVERED)
        $totalSalesOrders = DB::table('sales_orders')
            ->whereIn('status', ['CONFIRMED', 'DELIVERED'])
            ->count();

        // Total quotations
        $totalQuotations = DB::table('quotations')->count();

        // Quotations successfully converted to sales orders
        $convertedToSO = DB::table('sales_orders')
            ->whereNotNull('quotation_id')
            ->distinct('quotation_id')
            ->count('quotation_id');

        // Calculate conversion rate
        $conversionRate = $totalQuotations > 0
            ? round(($convertedToSO / $totalQuotations) * 100, 2)
            : 0;

        // Outstanding Invoices (unpaid)
        $outstandingInvoices = DB::table('transaction')
            ->where('status', 'unpaid')
            ->count();

        // Total Returns (total quantity returned + refund amount)
        $totalReturnsQty = DB::table('product_returns')->sum('quantity');
        $totalRefundAmount = DB::table('credit_memos')->sum('amount');

        // Average Order Value
        $avgOrderValue = $totalSalesOrders > 0
            ? round($totalRevenue / $totalSalesOrders, 2)
            : 0;

        return [
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 2))
                ->description('Total from paid invoices')
                ->color('success'),

            Stat::make('Total Sales Orders', $totalSalesOrders)
                ->description('Status: Confirmed & Delivered')
                ->color('primary'),

            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description('Quotation → Sales Order')
                ->color('info'),

            Stat::make('Outstanding Invoices', $outstandingInvoices)
                ->description('Unpaid invoices')
                ->color('danger'),

            Stat::make('Total Returns', $totalReturnsQty . ' pcs / Rp ' . number_format($totalRefundAmount, 2))
                ->description('Quantity returned & Credit Memo amount')
                ->color('warning'),

            Stat::make('Average Order Value', 'Rp ' . number_format($avgOrderValue, 2))
                ->description('Revenue per Sales Order')
                ->color('gray'),
        ];
    }
}
