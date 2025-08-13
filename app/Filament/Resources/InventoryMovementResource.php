<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class InventoryMovementResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return 'Inventory Movements';
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Movement Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('subPart.sub_part_name')->label('Product Name'),
                        Infolists\Components\TextEntry::make('product_id')->label('Product ID'),
                        // --- PERBAIKAN DI SINI ---
                        Infolists\Components\TextEntry::make('movement_type')
                            ->badge() // Menjadikannya badge
                            ->label('Type')
                            ->color(fn (string $state): string => match ($state) {
                                'IN' => 'success',
                                'OUT' => 'danger',
                                default => 'gray',
                            }),
                        // -------------------------
                        Infolists\Components\TextEntry::make('quantity'),
                        Infolists\Components\TextEntry::make('movement_date')->dateTime(),
                    ])->columns(2),
                Infolists\Components\Section::make('Reference Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('reference_type')->label('Reference Type'),
                        Infolists\Components\TextEntry::make('reference_id')->label('Reference ID'),
                        Infolists\Components\TextEntry::make('notes')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subPart.sub_part_name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                BadgeColumn::make('movement_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'IN',
                        'danger' => 'OUT',
                    ]),
                TextColumn::make('quantity'),
                TextColumn::make('movement_date')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label('Reference Type')
                    ->searchable(),
                TextColumn::make('reference_id')
                    ->label('Reference ID')
                    ->searchable(),
                TextColumn::make('notes')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->options([
                        'IN' => 'IN',
                        'OUT' => 'OUT',
                    ]),
                Filter::make('movement_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('movement_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'view' => Pages\ViewInventoryMovement::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}