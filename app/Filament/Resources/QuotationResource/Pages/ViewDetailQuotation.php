<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Models\Quotation;
use Filament\Resources\Pages\Page;

class ViewDetailQuotation extends Page
{
    protected static string $resource = QuotationResource::class;

    protected static string $view = 'filament.resources.quotation-resource.pages.view-detail-quotation';

    public Quotation $record;

    public function mount(Quotation $record): void
    {
        $this->record = $record->load(['items', 'outlet']);
    }
    

    


        
}
