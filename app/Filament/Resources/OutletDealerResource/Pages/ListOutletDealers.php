<?php

namespace App\Filament\Resources\OutletDealerResource\Pages;

use App\Filament\Resources\OutletDealerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutletDealers extends ListRecords
{
    protected static string $resource = OutletDealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
