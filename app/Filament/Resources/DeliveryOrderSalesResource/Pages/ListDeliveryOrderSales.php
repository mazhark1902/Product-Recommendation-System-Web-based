<?php

namespace App\Filament\Resources\DeliveryOrderSalesResource\Pages;

use App\Filament\Resources\DeliveryOrderSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryOrderSales extends ListRecords
{
    protected static string $resource = DeliveryOrderSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
