<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class VoucherPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Inventory'; 

    protected static string $view = 'filament.pages.voucher-page';
}
