<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\SalesDashboardKpi;
use App\Filament\Widgets\RevenueMonthLineChart;
use App\Filament\Widgets\TopCustomerBarChart;
use App\Filament\Widgets\SalesOrderStatusPieChart;

class SalesDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Sales'; 

    protected static string $view = 'filament.pages.sales-dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            SalesDashboardKpi::class,
            RevenueMonthLineChart::class,
            TopCustomerBarChart::class,
            SalesOrderStatusPieChart::class,
 ];
    } 
}
