<?php

namespace App\Filament\Resources\DeliveryOrderInventoryResource\Pages;

use App\Filament\Resources\DeliveryOrderInventoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryOrderInventory extends ViewRecord
{
    protected static string $resource = DeliveryOrderInventoryResource::class;
    protected static string $view = 'filament.resources.delivery-order-inventory-resource.pages.view-delivery-order-inventory';
    protected function getHeaderActions(): array
    {
        return [];
    }

    // public function getInventoryMovements()
    // {
    //     return $this->record->inventoryMovements; // Pastikan relasi 'inventoryMovements' ada di model DeliveryOrder
    // }
}