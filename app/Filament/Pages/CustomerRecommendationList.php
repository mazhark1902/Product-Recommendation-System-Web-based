<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomerRecommendationList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Inventory'; 

    protected static string $view = 'filament.pages.customer-recommendation-list';
}
