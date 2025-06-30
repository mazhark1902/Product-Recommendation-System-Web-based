<?php

namespace App\Filament\Resources\SalesOrderResource\Pages;

use App\Filament\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected static string $view = 'filament.resources.sales-order-resource.pages.view-sales-order';

    public function mount($record): void
    {
        parent::mount($record);

        // Eager load relasi yang diperlukan
        $this->record->load(['items', 'transaction', 'outlet.dealer']);
    }
}