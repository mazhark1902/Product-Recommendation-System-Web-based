<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SparePartMonthlyTrend extends ChartWidget
{
    protected static ?string $heading = 'Spare Part Monthly Trend';
    protected static ?int $sort = 3;

    protected function getFilters(): ?array
    {
        return DB::table('master_part')
            ->orderBy('part_name')
            ->pluck('part_name', 'part_name')
            ->prepend('All Parts', '')
            ->toArray();
    }

    protected function getDefaultFilter(): ?string
    {
        return '';
    }

    protected function getData(): array
    {
        // ✅ Ambil nilai filter dari bawaan ChartWidget
        $selectedPart = $this->filter;

        $query = DB::table('sales_order_items')
            ->join('sub_parts', 'sales_order_items.part_number', '=', 'sub_parts.sub_part_number')
            ->join('master_part', 'sub_parts.part_number', '=', 'master_part.part_number')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.sales_order_id')
            ->selectRaw('DATE_FORMAT(sales_orders.order_date, "%Y-%m") as month, SUM(sales_order_items.quantity) as total_quantity');

        // ✅ Terapkan filter jika ada
        if (!empty($selectedPart)) {
            $query->where('master_part.part_name', $selectedPart);
        }

        $query = $query
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $query->pluck('month'),
            'datasets' => [
                [
                    'label' => $selectedPart ?: 'All Parts',
                    'data' => $query->pluck('total_quantity'),
                    'borderColor' => '#3B82F6',
                    'fill' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    // ✅ Tambahan penting! Pastikan chart auto-refresh saat filter berubah
    protected static bool $isLazy = false;
}
