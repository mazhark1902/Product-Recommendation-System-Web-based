<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SalesDetailsTable extends BaseWidget
{
    protected static ?string $heading = 'Sales Details';

    protected function getTableQuery(): Builder
    {
        return \App\Models\Sale::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('month'),
            Tables\Columns\TextColumn::make('year'),
            Tables\Columns\TextColumn::make('revenue')->money('IDR', true),
            Tables\Columns\TextColumn::make('sales')->numeric(),
            Tables\Columns\TextColumn::make('orders')->numeric(),
            Tables\Columns\TextColumn::make('returns')->numeric(),
        ];
    }

    // 👇 This sets the widget to span full width (e.g., 2 or more columns)
    public function getColumnSpan(): int | string | array
    {
        return 'full'; // or return 2 / 3 based on your grid
    }
}
