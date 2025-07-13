<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DemandForecastResource\Pages;
use App\Models\DemandForecast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DemandForecastResource extends Resource
{
    protected static ?string $model = DemandForecast::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 5; // Atur urutan di navigasi

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form tidak diperlukan karena data ini hanya untuk ditampilkan (read-only)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sub_part_number')
                    ->label('Kode Part')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_part_name')
                    ->label('Nama Part')
                    ->searchable(),
                Tables\Columns\TextColumn::make('average_monthly_sales')
                    ->label('Rata-Rata Penjualan/Bulan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recommended_stock_level')
                    ->label('Rekomendasi Stok')
                    ->numeric()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('forecast_date')
                    ->label('Tanggal Prediksi')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('average_monthly_sales', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDemandForecasts::route('/'),
        ];
    }
    
    // Nonaktifkan tombol Create karena data di-generate oleh sistem
    public static function canCreate(): bool
    {
        return false;
    }
}