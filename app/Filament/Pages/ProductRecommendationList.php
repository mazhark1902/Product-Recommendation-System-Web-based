<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProductRecommendationList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Customer Analysis'; 

    protected static string $view = 'filament.pages.product-recommendation-list';
}
