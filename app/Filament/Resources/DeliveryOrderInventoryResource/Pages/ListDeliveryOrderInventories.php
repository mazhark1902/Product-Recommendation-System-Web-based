<?php

namespace App\Filament\Resources\DeliveryOrderInventoryResource\Pages;

use App\Filament\Resources\DeliveryOrderInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryOrderInventories extends ListRecords
{
    protected static string $resource = DeliveryOrderInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
