<?php

namespace App\Filament\Resources\DealerResource\Pages;

use App\Filament\Resources\DealerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDealers extends ListRecords
{
    protected static string $resource = DealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
