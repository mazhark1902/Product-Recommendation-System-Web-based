<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SalesDataList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Inventory'; 

    protected static string $view = 'filament.pages.sales-data-list';
}
