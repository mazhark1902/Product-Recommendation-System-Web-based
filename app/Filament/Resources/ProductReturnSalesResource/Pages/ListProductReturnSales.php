<?php

namespace App\Filament\Resources\ProductReturnSalesResource\Pages;

use App\Filament\Resources\ProductReturnSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductReturnSales extends ListRecords
{
    protected static string $resource = ProductReturnSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
