<?php

namespace App\Filament\Resources\DemandForecastResource\Pages;

use App\Filament\Resources\DemandForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDemandForecasts extends ListRecords
{
    protected static string $resource = DemandForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
