<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TransactionAnalysis extends Page
{
    protected static ?string $navigationIcon = 'heroicon-s-banknotes';
    protected static ?string $navigationGroup = 'Customer Analysis';
    protected static string $view = 'filament.pages.transaction-analysis';
}
