<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class DashboardOverview extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $totalSales = Invoice::sum('invoice_amount');
        $totalOrders = Invoice::sum('supplied_qty');
        $uniqueParts = Invoice::whereNotNull('part_no_supplied')->distinct('part_no_supplied')->count('part_no_supplied');

        return [
            Card::make('Total Sales', 'Rp ' . number_format($totalSales, 0, ',', '.'))
                ->description('Total Sales')
                ->color('success'),

            Card::make('Number of Part Sold', number_format($totalOrders))
                ->description('Parts')
                ->color('primary'),

            Card::make('Unique Part Count', $uniqueParts)
                ->description('Parts')
                ->color('info'),
        ];
    }
}
