<?php

namespace App\Filament\Resources\SubPartResource\Pages;

use App\Filament\Resources\SubPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubParts extends ListRecords
{
    protected static string $resource = SubPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
