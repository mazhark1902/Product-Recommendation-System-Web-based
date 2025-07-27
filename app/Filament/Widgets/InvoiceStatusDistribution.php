<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class InvoiceStatusDistribution extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Invoice Status Distribution';

    protected function getData(): array
    {
        $data = DB::table('transaction')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $labels = $data->pluck('status')->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Status Count',
                    'data' => $values,
                    'backgroundColor' => [
                        '#f87171', // green (paid)
                        '#34d399', // red (unpaid)
                        '#facc15', // yellow (pending)
                        '#60a5fa', // blue (processing)
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Change to 'pie' if you prefer
    }

    protected static ?int $sort = 3;
}
