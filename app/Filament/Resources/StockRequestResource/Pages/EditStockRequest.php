<?php

namespace App\Filament\Resources\StockRequestResource\Pages;

use App\Filament\Resources\StockRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockRequest extends EditRecord
{
    protected static string $resource = StockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
