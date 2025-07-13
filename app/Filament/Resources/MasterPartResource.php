<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterPartResource\Pages;
use App\Models\MasterPart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Str;

class MasterPartResource extends Resource
{
    protected static ?string $model = MasterPart::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('part_number')
                    ->label('Part Number')
                    ->default(function () { // Fungsi untuk generate default value
                        $prefix = 'PART-';
                        do {
                            $randomPart = strtoupper(Str::random(6)); // 6 karakter acak uppercase
                            $partNumber = $prefix . $randomPart;
                        } while (MasterPart::where('part_number', $partNumber)->exists()); // Cek keunikan
                        return $partNumber;
                    })
                    ->required()
                    ->maxLength(255) // Cukup untuk PART-XXXXXX
                    ->disabled()     // Nonaktifkan field agar pengguna tidak bisa mengubah
                    ->dehydrated()   // Pastikan nilai tetap dikirim meski disabled
                    ->unique(MasterPart::class, 'part_number', ignoreRecord: true), // Validasi keunikan di server
                Forms\Components\TextInput::make('part_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Part Name'),

                // For CREATE form context
                Placeholder::make('part_price_info_create')
                    ->label('Part Price')
                    ->content('The part price will be automatically calculated based on its sub parts after creation and adding sub parts.')
                    ->visibleOn('create'),

                // For EDIT form context
                Forms\Components\TextInput::make('part_price')
                    ->label('Current Total Price (Auto-calculated)')
                    ->numeric()
                    ->disabled() // Always disabled as it's calculated
                    ->dehydrated(false) // Ensures it's not saved back
                    ->visibleOn('edit'), // Only show on edit form

                // This placeholder is also good for edit, shows live sum if possible
                Placeholder::make('calculated_price_on_edit')
                    ->label('Calculated Price (from Sub Parts)')
                    ->content(function (?MasterPart $record): string {
                        if ($record) {
                            return number_format($record->subParts()->sum('price'), 2) . ' (Stored: ' . number_format($record->part_price, 2) . ')';
                        }
                        return 'N/A';
                    })
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
         return $table
            ->columns([
                Tables\Columns\TextColumn::make('part_number')
                    ->searchable()
                    ->sortable()
                    ->label('Part Number'),
                Tables\Columns\TextColumn::make('part_name')
                    ->searchable()
                    ->label('Part Name'),
                Tables\Columns\TextColumn::make('part_price')
                    ->money('IDR')
                    ->sortable()
                    ->label('Total Price'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('harga_diatas_100rb')
                    ->label('Harga > 100.000')
                    ->query(fn (Builder $query): Builder => $query->where('part_price', '>', 100000)),
                Filter::make('harga_dibawah_100rb')
                    ->label('Harga <= 100.000')
                    ->query(fn (Builder $query): Builder => $query->where('part_price', '<=', 100000)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\Action::make('viewSubParts')
                    ->label('Manage Sub Parts')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record): string => static::getUrl('sub-parts', ['part_number' => $record->part_number]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
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
            // If you decide to use a Relation Manager later, add it here.
            // Example: RelationManagers\SubPartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterParts::route('/'),
            'create' => Pages\CreateMasterPart::route('/create'),
            'edit' => Pages\EditMasterPart::route('/{record}/edit'),
            'sub-parts' => Pages\ViewSubParts::route('/{part_number}/sub-parts'),
        ];
    }

}
