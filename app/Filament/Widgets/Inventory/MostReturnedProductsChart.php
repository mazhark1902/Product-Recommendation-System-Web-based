<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\ProductReturn;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class MostReturnedProductsChart extends ChartWidget
{
    use HasWidgetShield;
    
    // PERBAIKAN 1: Ubah judul dari Top 5 menjadi Top 10
    protected static ?string $heading = 'Top 10 Most Frequently Returned Products';
    
    protected static ?int $sort = 6;

    // PERBAIKAN 2: Tambahkan properti ini untuk membuat widget menjadi lebar penuh
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = ProductReturn::query()
            ->join('sub_parts', 'product_returns.part_number', '=', 'sub_parts.sub_part_number')
            ->select('sub_parts.sub_part_name', DB::raw('SUM(product_returns.quantity) as total_returned'))
            ->groupBy('sub_parts.sub_part_name')
            ->orderBy('total_returned', 'desc')
            // PERBAIKAN 3: Ubah limit dari 5 menjadi 10
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Returned',
                    'data' => $data->pluck('total_returned')->all(),
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => $data->pluck('sub_part_name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
