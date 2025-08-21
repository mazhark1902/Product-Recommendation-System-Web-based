<?php

namespace App\Filament\Resources\MasterPaymentMethodResource\Pages;

use App\Filament\Resources\MasterPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterPaymentMethod extends EditRecord
{
    protected static string $resource = MasterPaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
