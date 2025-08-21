<?php

namespace App\Filament\Resources\MasterPaymentMethodResource\Pages;

use App\Filament\Resources\MasterPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterPaymentMethods extends ListRecords
{
    protected static string $resource = MasterPaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
