<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\TopCustomerBarChart;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\AdminDashboardSales;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Widgets\AdminDashboardInventory;
use App\Filament\Widgets\AdminDashboardCustomer;
use App\Filament\Widgets\RevenueMonthLineChart;
use App\Filament\Widgets\Inventory\MostReturnedProductsChart;
use App\Filament\Widgets\churnriskchart;
use App\Filament\Widgets\OrderFrequencyByCustomer;







class AdminDashboard extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = null;
    protected static string $view = 'filament.pages.admin-dashboard';
    

    public function getHeaderWidgets(): array
    {
        return [
            AdminDashboardSales::class,
            AdminDashboardInventory::class,
            AdminDashboardCustomer::class,
            RevenueMonthLineChart::class,
            TopCustomerBarChart::class,
            MostReturnedProductsChart::class,
            // OrderFrequencyByCustomer::class,
            // churnriskchart::class,

            
        ];
    }
}
