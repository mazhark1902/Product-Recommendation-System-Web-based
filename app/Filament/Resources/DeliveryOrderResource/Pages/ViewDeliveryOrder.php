<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryOrder extends ViewRecord
{
    protected static string $resource = DeliveryOrderResource::class;
    protected static string $view = 'filament.resources.delivery-order-resource.pages.view-delivery-order';
    protected function getHeaderActions(): array
    {
        return [];
    }

    // public function getInventoryMovements()
    // {
    //     return $this->record->inventoryMovements; // Pastikan relasi 'inventoryMovements' ada di model DeliveryOrder
    // }
}