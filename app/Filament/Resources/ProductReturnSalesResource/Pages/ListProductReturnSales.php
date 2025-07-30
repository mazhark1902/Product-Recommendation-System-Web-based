<?php

namespace App\Filament\Resources\ProductReturnSalesResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProductReturnSalesResource;
use Filament\Pages\Actions;

class ListProductReturnSales extends ListRecords
{
    protected static string $resource = ProductReturnSalesResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // ✅ ini yang menampilkan tombol "Create"
        ];
    }
}