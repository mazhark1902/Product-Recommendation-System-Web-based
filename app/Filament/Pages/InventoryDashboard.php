<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\Inventory\AfterSalesKpiWidget;
use App\Filament\Widgets\Inventory\DeadStockTable;
use App\Filament\Widgets\Inventory\StockAvailabilityWidget;
use App\Filament\Widgets\Inventory\SlowMovingItemsChart;
use App\Filament\Widgets\Inventory\MostReturnedProductsChart;
use App\Filament\Widgets\Inventory\FastMovingItemsChart;
use App\Filament\Widgets\Inventory\PendingShipmentsTable;

class InventoryDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Inventory'; 

    protected static string $view = 'filament.pages.inventory-dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            StockAvailabilityWidget::class,
            AfterSalesKpiWidget::class,
            DeadStockTable::class,
            SlowMovingItemsChart::class,
            MostReturnedProductsChart::class,
            // FastMovingItemsChart::class,
            PendingShipmentsTable::class,
            
 ];
    } 
}
 