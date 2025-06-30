<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomerChurnList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Customer Analysis'; 

    protected static string $view = 'filament.pages.customer-churn-list';
}
