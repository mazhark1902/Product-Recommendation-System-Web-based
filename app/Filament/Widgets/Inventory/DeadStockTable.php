<?php

namespace App\Filament\Widgets\Inventory;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DeadStockTable extends BaseWidget
{
    protected static ?int $sort = 11;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Laporan Stok Mati (Tidak Ada Penjualan 6 Bulan Terakhir)';

    public function table(Table $table): Table
    {
        // Subquery untuk mendapatkan produk yang terjual dalam 6 bulan terakhir
        $recentlySoldProducts = \App\Models\InventoryMovement::query()
            ->select('product_id')
            ->where('movement_type', 'out')
            ->where('movement_date', '>=', Carbon::now()->subMonths(1))
            ->distinct();

        return $table
            ->query(
                Inventory::query()
                    ->join('sub_parts', 'inventory.product_id', '=', 'sub_parts.sub_part_number')
                    ->where('inventory.quantity_available', '>', 0)
                    // Ambil produk yang TIDAK ADA dalam subquery di atas
                    ->whereNotIn('inventory.product_id', $recentlySoldProducts)
                    ->select('inventory.*', 'sub_parts.sub_part_name', 'sub_parts.price')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_id')->label('Kode Produk'),
                Tables\Columns\TextColumn::make('sub_part_name')->label('Nama Produk')->searchable(),
                Tables\Columns\TextColumn::make('quantity_available')->label('Jml Stok')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('last_updated')->label('Update Terakhir')->date()->sortable(),
                Tables\Columns\TextColumn::make('potential_loss')
                    ->label('Potensi Kerugian')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->quantity_available * $record->price),
            ])
            ->emptyStateHeading('Tidak ada stok mati yang teridentifikasi.');
    }
}