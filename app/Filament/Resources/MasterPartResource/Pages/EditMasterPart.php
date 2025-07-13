<?php

namespace App\Filament\Resources\MasterPartResource\Pages;

use App\Filament\Resources\MasterPartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterPart extends EditRecord
{
    protected static string $resource = MasterPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
