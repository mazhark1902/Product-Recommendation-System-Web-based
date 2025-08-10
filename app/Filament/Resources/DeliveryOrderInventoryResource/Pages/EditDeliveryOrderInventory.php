<?php

namespace App\Filament\Resources\DeliveryOrderInventoryResource\Pages;

use App\Filament\Resources\DeliveryOrderInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrderInventory extends EditRecord
{
    protected static string $resource = DeliveryOrderInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
