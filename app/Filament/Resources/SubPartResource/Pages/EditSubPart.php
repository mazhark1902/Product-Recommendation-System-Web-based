<?php

namespace App\Filament\Resources\SubPartResource\Pages;

use App\Filament\Resources\SubPartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubPart extends EditRecord
{
    protected static string $resource = SubPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
