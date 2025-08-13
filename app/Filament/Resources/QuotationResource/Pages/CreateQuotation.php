<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Facades\DB;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;
        protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Quotation Created')
            ->body("Quotation {$this->record->quotation_id} has been created successfully.")
            ->success();
    }
}
