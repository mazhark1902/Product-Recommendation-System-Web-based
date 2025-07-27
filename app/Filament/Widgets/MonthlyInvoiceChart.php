<?php

namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class MonthlyInvoiceChart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Top 4 Months by Invoice Amount';

    protected function getData(): array
    {
        // Step 1: Get top 4 months by total invoice amount
        $topMonths = DB::table('transaction')
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(total_amount) as total")
            ->groupBy('month')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        // Step 2: Sort the months chronologically (optional)
        $sortedMonths = $topMonths->sortBy('month');

        $labels = [];
        $values = [];
        $invoiceIdMap = [];

        foreach ($sortedMonths as $row) {
            $month = $row->month;

            $labels[] = $month;
            $values[] = $row->total;

            // Get invoice IDs for this month
            $ids = DB::table('transaction')
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$month])
                ->pluck('invoice_id') // adjust if your column name differs
                ->toArray();

            // Limit to 3 IDs for cleaner tooltip (optional)
            $limitedIds = array_slice($ids, 0, 3);
            $invoiceIdMap[$month] = implode(', ', $limitedIds) . (count($ids) > 3 ? ', ...' : '');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Invoice Amount',
                    'data' => array_values($values),
                    'backgroundColor' => '#3b82f6',
                    'invoice_ids' => array_values($invoiceIdMap), // pass custom tooltip data
                ],
            ],
            'labels' => $labels,
            'options' => [
                'plugins' => [
                    'tooltip' => [
                        'callbacks' => [
                            'label' => RawJs::make("
                                function(context) {
                                    const index = context.dataIndex;
                                    const value = context.dataset.data[index];
                                    const ids = context.dataset.invoice_ids[index];
                                    return 'Total: ' + value.toLocaleString() + '\\nInvoices: ' + ids;
                                }
                            "),
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected static ?int $sort = 1;
}
