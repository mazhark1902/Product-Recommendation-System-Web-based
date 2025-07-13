<?php

namespace App\Filament\Resources\DemandForecastResource\Pages;

use App\Filament\Resources\DemandForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDemandForecast extends ViewRecord
{
    protected static string $resource = DemandForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
