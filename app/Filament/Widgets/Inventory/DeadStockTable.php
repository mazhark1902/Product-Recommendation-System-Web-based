<?php

namespace App\Filament\Widgets\Inventory;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class DeadStockTable extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 11;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Deadstock Report (No Sales in the Last 6 Months)';

    public function table(Table $table): Table
    {
        // Subquery to get products sold in the last 6 months
        $recentlySoldProducts = \App\Models\InventoryMovement::query()
            ->select('product_id')
            ->where('movement_type', 'out')
            ->where('movement_date', '>=', Carbon::now()->subMonths(6)) // Corrected to 6 months to match heading
            ->distinct();

        return $table
            ->query(
                Inventory::query()
                    ->join('sub_parts', 'inventory.product_id', '=', 'sub_parts.sub_part_number')
                    ->where('inventory.quantity_available', '>', 0)
                    // Get products that are NOT in the subquery above
                    ->whereNotIn('inventory.product_id', $recentlySoldProducts)
                    ->select('inventory.*', 'sub_parts.sub_part_name', 'sub_parts.cost')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_id')->label('Product ID'),
                Tables\Columns\TextColumn::make('sub_part_name')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('quantity_available')->label('Stock Qty')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('last_updated')->label('Last Updated')->date()->sortable(),
                Tables\Columns\TextColumn::make('potential_loss')
                    ->label('Potential Loss')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->quantity_available * $record->cost),
            ])
            ->emptyStateHeading('No dead stock identified.');
    }
}
