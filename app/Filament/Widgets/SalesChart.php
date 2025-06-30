<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Chart';

    protected function getFilters(): ?array
    {
        return [
            '2023' => 'Year 2023',
            '2024' => 'Year 2024',
        ];
    }

    protected function getDefaultFilter(): ?string
    {
        return '2024'; // ✅ Default selected year
    }

    protected function getData(): array
    {
        $year = $this->filter ?? '2024'; // fallback just in case

        $data = match ($year) {
            '2023' => [1500, 2300, 1800, 2200, 2700, 3000, 2500, 2800, 2600, 3100, 3300, 2900],
            '2024' => [1800, 2500, 1900, 2300, 2800, 3200, 2700, 3000, 2700, 3400, 3600, 3100],
            default => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        };

        return [
            'datasets' => [
                [
                    'label' => "Sales in $year",
                    'data' => $data,
                    'backgroundColor' => '#10B981',
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
        return 'bar';
    }

    protected function getOptions(): ?array
    {
        return [
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeOutBounce',
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
