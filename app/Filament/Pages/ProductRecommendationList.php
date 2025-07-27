<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\TopRecommendation;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ProductRecommendationList extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-s-gift';
    protected static ?string $navigationGroup = 'Customer Analysis'; 

    protected static string $view = 'filament.pages.product-recommendation-list';

    public ?string $selectedDealer = '';
    public $recommendation = null;

    public function searchRecommendation()
    {
        if (!$this->selectedDealer) {
            $this->recommendation = null;
            return;
        }

        $this->recommendation = TopRecommendation::where('dealer_id', $this->selectedDealer)->first();
    }
}
