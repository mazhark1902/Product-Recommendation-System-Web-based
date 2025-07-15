<?php

namespace App\Filament\Resources\ProductReturnResource\Pages;

use App\Filament\Resources\ProductReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReturn extends EditRecord
{
    protected static string $resource = ProductReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
