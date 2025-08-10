<?php

namespace App\Filament\Resources\DeliveryOrderSalesResource\Pages;

use App\Filament\Resources\DeliveryOrderSalesResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryOrderSales extends ViewRecord
{
    protected static string $resource = DeliveryOrderSalesResource::class;
    protected static string $view = 'filament.resources.delivery-order-sales-resource.pages.view-delivery-order-sales';
    protected function getHeaderActions(): array
    {
        return [];
    }

    // public function getInventoryMovements()
    // {
    //     return $this->record->inventoryMovements; // Pastikan relasi 'inventoryMovements' ada di model DeliveryOrder
    // }
}