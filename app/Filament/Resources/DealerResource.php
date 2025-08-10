<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealerResource\Pages;
use App\Filament\Resources\DealerResource\RelationManagers;
use App\Models\Dealer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class DealerResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = Dealer::class;
    public static ?string $navigationGroup = 'Customer Analysis';
    protected static ?string $navigationIcon = 'heroicon-s-building-office-2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::Make('dealer_code')->required()->visibleOn('create'),
                TextInput::Make('dealer_name')->required(),
                TextInput::Make('province')->required(),
                TextInput::Make('email')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dealer_name')->sortable()->searchable(),
                TextColumn::make('province')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                // TextColumn::make('address')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('view_details')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->infolist([
                TextEntry::make('dealer_code')->label('Dealer Code'),
                TextEntry::make('dealer_name')->label('Dealer Name'),
                TextEntry::make('province')->label('Province'),
                TextEntry::make('email')->label('Email'),

                // Outlets Section
                \Filament\Infolists\Components\RepeatableEntry::make('outlets')
                ->schema([
                TextEntry::make('outlet_name')->label('Outlet Name'),
                TextEntry::make('email')->label('Outlet Email'),
                TextEntry::make('phone')->label('Phone'),
                TextEntry::make('address')->label('Address'),
                 ])
                ->label('Outlets')
                ->columns(2),
                    ])
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDealers::route('/'),
            'create' => Pages\CreateDealer::route('/create'),
            'edit' => Pages\EditDealer::route('/{record}/edit'),
        ];
    }
}
