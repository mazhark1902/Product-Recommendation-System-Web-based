<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\MonthlyInvoiceChart;
use App\Filament\Widgets\InvoiceAgingChart;
use App\Filament\Widgets\InvoiceStatusDistribution;
use App\Filament\Widgets\AveragePaymentTerm;
use App\Filament\Widgets\TopCustomersByInvoice;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;




class TransactionAnalysis extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-s-banknotes';
    protected static ?string $navigationGroup = 'Customer Analysis';
    protected static string $view = 'filament.pages.transaction-analysis';
    protected function getHeaderWidgets(): array
{
    return [
        AveragePaymentTerm::class,
        MonthlyInvoiceChart::class,
        InvoiceAgingChart::class,
        InvoiceStatusDistribution::class,
        TopCustomersByInvoice::class,
        

    ];
}

}
