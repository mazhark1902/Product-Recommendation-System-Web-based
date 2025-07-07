<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InvoiceAgingChart extends ChartWidget
{
    protected static ?string $heading = 'Invoice Aging Analysis';

    protected function getData(): array
    {
        // Query all invoices with the aging days
        $data = DB::table('transaction')
            ->select(
                'invoice_id',
                DB::raw('DATEDIFF(due_date, invoice_date) as aging_days')
            )
            ->whereNotNull('due_date')
            ->whereNotNull('invoice_date')
            ->orderByDesc('aging_days')
            ->limit(10) // Limit to top 10 for readability
            ->get();

        $labels = $data->pluck('invoice_id')->toArray();
        $values = $data->pluck('aging_days')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Days Until Due',
                    'data' => $values,
                    'backgroundColor' => '#facc15', // amber-400
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected static ?int $sort = 2;
}
