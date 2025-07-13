<?php

namespace App\Filament\Resources\DemandForecastResource\Pages;

use App\Filament\Resources\DemandForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemandForecast extends EditRecord
{
    protected static string $resource = DemandForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
