<?php

namespace App\Filament\Resources\OutletDealerResource\Pages;

use App\Filament\Resources\OutletDealerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutletDealer extends EditRecord
{
    protected static string $resource = OutletDealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
