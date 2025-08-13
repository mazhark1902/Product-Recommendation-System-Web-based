<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class churnriskchart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Customer Churn Risk';
    protected static ?int $sort = 6;

    // ✅ Tambahkan dropdown filter berdasarkan dealer
    protected function getFilters(): ?array
    {
        return DB::table('outlet_dealers')
            ->orderBy('outlet_name')
            ->pluck('outlet_name', 'outlet_name') // ['Astra' => 'Astra']
            ->prepend('All Dealers', '')
            ->toArray();
    }

    protected function getDefaultFilter(): ?string
    {
        return ''; // Default semua dealer
    }

    protected function getData(): array
{
    $selectedDealer = $this->filter;

    $query = DB::table('sales_orders')
        ->join('outlet_dealers', 'sales_orders.customer_id', '=', 'outlet_dealers.outlet_code');

    if (!empty($selectedDealer)) {
        $query->where('outlet_dealers.outlet_name', $selectedDealer);
    }

    // Ambil data R, F, M
    $customerStats = $query
        ->select(
            'sales_orders.customer_id',
            DB::raw('MAX(order_date) as last_order_date'),
            DB::raw('COUNT(*) as frequency'),
            DB::raw('SUM(total_amount) as monetary')
        )
        ->groupBy('sales_orders.customer_id')
        ->get();

    // Hitung skor RFM (1–5)
    $recencyScores = $customerStats->pluck('last_order_date')->map(function ($date) {
        $days = now()->diffInDays(Carbon::parse($date));
        return $days;
    });

    // Ranking untuk R
    $recencyRank = $recencyScores->sort()->values()->map(function ($val, $i) use ($recencyScores) {
        return ceil(($i + 1) / ($recencyScores->count() / 5));
    });

    // Ranking untuk F dan M
    $frequencyRank = $customerStats->pluck('frequency')->sortDesc()->values()->map(function ($val, $i) use ($customerStats) {
        return ceil(($i + 1) / ($customerStats->count() / 5));
    });

    $monetaryRank = $customerStats->pluck('monetary')->sortDesc()->values()->map(function ($val, $i) use ($customerStats) {
        return ceil(($i + 1) / ($customerStats->count() / 5));
    });

    // Gabungkan skor ke customer
    $rfmData = [];
    foreach ($customerStats as $index => $row) {
        $rScore = 6 - $recencyRank[$index]; // Recency dibalik, semakin kecil hari → semakin besar skor
        $fScore = $frequencyRank[$index];
        $mScore = $monetaryRank[$index];
        $totalScore = $rScore + $fScore + $mScore;

        if ($totalScore <= 6) {
            $risk = 'High';
        } elseif ($totalScore <= 10) {
            $risk = 'Medium';
        } else {
            $risk = 'Low';
        }

        $rfmData[] = $risk;
    }

    // Hitung jumlah per kategori risiko
    $riskCounts = array_count_values($rfmData);

    return [
        'labels' => ['Low', 'Medium', 'High'],
        'datasets' => [
            [
                'label' => 'Customers',
                'data' => [
                    $riskCounts['Low'] ?? 0,
                    $riskCounts['Medium'] ?? 0,
                    $riskCounts['High'] ?? 0,
                ],
                'backgroundColor' => ['#22c55e', '#facc15', '#ef4444'],
            ],
        ],
    ];
}


    protected function getType(): string
    {
        return 'bar';
    }
}
