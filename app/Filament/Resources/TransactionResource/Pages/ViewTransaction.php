<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\Page;
use App\Models\Transaction;
use Filament\Resources\Pages\ViewRecord;


class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected static string $view = 'filament.resources.transaction-resource.pages.view-transaction';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
