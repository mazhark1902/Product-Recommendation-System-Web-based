<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueLineChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trends';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => [1000, 1800, 1200, 2100, 2500, 2700, 2300, 2400, 2600, 2900, 3100, 3300],
                    'borderColor' => '#3B82F6',
                    'fill' => false,
                    'tension' => 0.4, // optional: makes line smoother
                ],
            ],
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): ?array
    {
        return [
            'animation' => [
                'duration' => 1000, // 1000ms = 1 second
                'easing' => 'easeOutQuart',
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
