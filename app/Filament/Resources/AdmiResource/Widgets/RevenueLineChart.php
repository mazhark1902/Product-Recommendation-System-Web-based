<?php

namespace App\Filament\Resources\AdmiResource\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueLineChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
