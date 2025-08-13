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
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class InventoryResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = Inventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Sub Part')
                    ->options(SubPart::all()->pluck('sub_part_name', 'sub_part_number'))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('quantity_available')->label('Available Stock')->numeric()->required()->default(0),
                Forms\Components\TextInput::make('minimum_stock')->label('Minimum Stock')->numeric()->required()->default(10),
                Forms\Components\TextInput::make('quantity_reserved')->label('Reserved Stock')->numeric()->default(0),
                Forms\Components\TextInput::make('quantity_damaged')->label('Damaged Stock')->numeric()->default(0),
                Forms\Components\TextInput::make('location')->label('Storage Location')->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subPart.sub_part_name')->label('Sub Part Name')->searchable()->sortable()->placeholder('N/A'),
                Tables\Columns\TextColumn::make('product_id')->label('Sub Part Code')->searchable(),
                Tables\Columns\TextColumn::make('quantity_available')
                    ->label('Available Stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state, $record) => $state > $record->minimum_stock ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('minimum_stock')->label('Min. Stock')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('quantity_reserved')->label('Reserved')->numeric()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity_damaged')->label('Damaged')->numeric()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location')->label('Location')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('critical_stock')->label('Critical Stock')->query(fn (Builder $query): Builder => $query->whereColumn('quantity_available', '<=', 'minimum_stock')),
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
        return [];
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
