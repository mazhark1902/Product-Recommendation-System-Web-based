<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AveragePaymentTerm extends BaseWidget
{
    use HasWidgetShield;
    protected function getStats(): array
    {
        return [
            Stat::make('Avg Payment Term', round($this->getAverageDays()) . ' days')
                ->description('Average days from invoice to due')
                ->color('primary'),

            Stat::make('Total Invoiced Amount', 'Rp ' . number_format($this->getTotalInvoiced(), 0, ',', '.'))
                ->description('Sum of all invoice totals')
                ->color('success'),

            Stat::make('Total Invoice Count', $this->getTotalInvoiceCount())
                ->description('All-time invoice volume')
                ->color('info'),

            Stat::make('Unpaid Invoices', $this->getUnpaidCount())
                ->description('Outstanding invoices')
                ->color('danger'),
        ];
    }

    private function getAverageDays(): float
    {
        return DB::table('transaction')
            ->whereNotNull('invoice_date')
            ->whereNotNull('due_date')
            ->selectRaw('AVG(DATEDIFF(due_date, invoice_date)) as avg_days')
            ->value('avg_days') ?? 0;
    }

    private function getTotalInvoiced(): float
    {
        return DB::table('transaction')->sum('total_amount');
    }

    private function getTotalInvoiceCount(): int
    {
        return DB::table('transaction')->count();
    }

    private function getUnpaidCount(): int
    {
        return DB::table('transaction')
            ->where('status', 'unpaid')
            ->count();
    }
}
