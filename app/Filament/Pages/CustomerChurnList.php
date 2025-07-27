<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\LastPurchaseByCustomer;
use App\Filament\Widgets\OrderFrequencyByCustomer;
use App\Filament\Widgets\TopSpendingCustomersChart;
use App\Filament\Widgets\churnriskchart;
use App\Filament\Widgets\CustomerChurnStats;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;


class CustomerChurnList extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-s-arrow-left-start-on-rectangle';
    protected static ?string $navigationGroup = 'Customer Analysis'; 

    protected static string $view = 'filament.pages.customer-churn-list';
    protected function getHeaderWidgets(): array
{
    return [
        // other widgets...
        CustomerChurnStats::class,
        LastPurchaseByCustomer::class,
        OrderFrequencyByCustomer::class,
        TopSpendingCustomersChart::class,
        churnriskchart::class,
    ];
}
}
