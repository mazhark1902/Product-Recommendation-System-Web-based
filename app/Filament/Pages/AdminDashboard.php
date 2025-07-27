<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\TopCustomerBarChart;
use App\Filament\Widgets\SalesChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class AdminDashboard extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = null;
    protected static string $view = 'filament.pages.admin-dashboard';
    

    public function getHeaderWidgets(): array
    {
        return [
            TopCustomerBarChart::class,
            SalesChart::class,
            
        ];
    }
}
