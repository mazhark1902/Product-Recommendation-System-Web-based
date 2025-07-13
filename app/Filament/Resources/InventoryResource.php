<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\SubPart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2; // Atur posisi menu

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Sub Part')
                    ->options(SubPart::all()->pluck('sub_part_name', 'sub_part_number'))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true) // Hanya izinkan satu record inventaris per sub part
                    ->disabledOn('edit'), // Tidak bisa diubah saat edit
                Forms\Components\TextInput::make('quantity_available')
                    ->label('Stok Tersedia')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->required()
                    ->default(10),
                Forms\Components\TextInput::make('quantity_reserved')
                    ->label('Stok Dipesan')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('quantity_damaged')
                    ->label('Stok Rusak')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('location')
                    ->label('Lokasi Penyimpanan')
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subPart.sub_part_name')
                    ->label('Nama Sub Part')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('product_id')
                    ->label('Kode Sub Part')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_available')
                    ->label('Stok Tersedia')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Stok Min.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_reserved')
                    ->label('Dipesan')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity_damaged')
                    ->label('Rusak')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Filter untuk menampilkan stok yang kritis
                Tables\Filters\Filter::make('critical_stock')
                    ->label('Stok Kritis')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('quantity_available', '<=', 'minimum_stock')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}