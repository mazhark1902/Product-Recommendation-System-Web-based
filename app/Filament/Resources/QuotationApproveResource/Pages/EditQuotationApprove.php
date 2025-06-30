<?php

namespace App\Filament\Resources\QuotationApproveResource\Pages;

use App\Filament\Resources\QuotationApproveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuotationApprove extends EditRecord
{
    protected static string $resource = QuotationApproveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
