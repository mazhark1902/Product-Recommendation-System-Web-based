<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class RevenueMonthLineChart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Revenue per Month';

    // ✅ Tambahkan filter tahun
    protected function getFilters(): ?array
    {
        // Bisa kamu ubah/otomatis dari DB kalau mau
        return [
            '2025' => 'Year 2025',
            '2024' => 'Year 2024',
            '2023' => 'Year 2023',
            '2022' => 'Year 2022',
        ];
    }

    protected function getDefaultFilter(): ?string
    {
        return '2025'; // Tahun default
    }

    protected function getData(): array
    {
        $year = $this->filter ?? '2025';

        $data = DB::table('transaction')
            ->selectRaw("DATE_FORMAT(invoice_date, '%b') as month, SUM(total_amount) as revenue")
            ->whereYear('invoice_date', $year)
            ->where('status', 'PAID')
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%b')")
            ->get();

        // Bikin array bulan Jan–Dec supaya datanya selalu urut lengkap
        $allMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $revenueByMonth = collect($allMonths)->map(function ($month) use ($data) {
            return $data->firstWhere('month', $month)->revenue ?? 0;
        });

        return [
            'datasets' => [
                [
                    'label' => "Revenue in $year",
                    'data' => $revenueByMonth,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => '#3B82F6',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $allMonths,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): ?array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
