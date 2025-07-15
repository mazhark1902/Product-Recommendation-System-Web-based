<?php

namespace App\Filament\Resources\DeliveryConfirmationResource\Pages;

use App\Filament\Resources\DeliveryConfirmationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryConfirmation extends EditRecord
{
    protected static string $resource = DeliveryConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
