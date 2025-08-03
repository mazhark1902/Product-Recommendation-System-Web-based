<?php

namespace App\Filament\Resources\CreditMemosResource\Pages;

use App\Filament\Resources\CreditMemosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditMemos extends ListRecords
{
    protected static string $resource = CreditMemosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
