<?php

namespace App\Filament\Resources\CreditMemosResource\Pages;

use App\Filament\Resources\CreditMemosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditMemos extends EditRecord
{
    protected static string $resource = CreditMemosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
