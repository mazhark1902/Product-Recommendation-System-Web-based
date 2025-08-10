<?php

namespace App\Filament\Resources\DeliveryOrderSalesResource\Pages;

use App\Filament\Resources\DeliveryOrderSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrderSales extends EditRecord
{
    protected static string $resource = DeliveryOrderSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
