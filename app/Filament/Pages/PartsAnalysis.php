<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\TopSparePartsByQuantity;
use App\Filament\Widgets\TopSparePartsByRevenue;
use App\Filament\Widgets\SparePartMonthlyTrend;
use App\Filament\Widgets\SparePartCustomerSpread;
use App\Filament\Widgets\SparePartsStats;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class PartsAnalysis extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-s-chart-pie';
    protected static ?string $navigationGroup = 'Customer Analysis';
    protected static string $view = 'filament.pages.parts-analysis';
    protected function getHeaderWidgets(): array
    {
    return [
     SparePartsStats::class,
     TopSparePartsByQuantity::class,
     TopSparePartsByRevenue::class,
     SparePartMonthlyTrend::class,
     SparePartCustomerSpread::class   
    ];
    }
}
