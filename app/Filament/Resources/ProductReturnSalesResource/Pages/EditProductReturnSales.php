<?php

namespace App\Filament\Resources\ProductReturnSalesResource\Pages;

use App\Filament\Resources\ProductReturnSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReturnSales extends EditRecord
{
    protected static string $resource = ProductReturnSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
