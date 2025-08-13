<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletDealerResource\Pages;
use App\Filament\Resources\OutletDealerResource\RelationManagers;
use App\Models\OutletDealer;
use App\Models\Dealer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


use Illuminate\Support\Str;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class OutletDealerResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = OutletDealer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Customer Analysis'; // Or keep it hidden if only managed via MasterPart
    protected static bool $shouldRegisterNavigation = false; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('dealer_code')
                //     ->relationship('Dealer', 'dealer_code') // Assumes masterPart relation exists on SubPart model
                //     ->label('Dealer')
                //     ->options(MasterPart::pluck('part_name', 'part_number')) // Provide options
                //     ->searchable()
                //     ->required()
                //     // When creating from ViewSubParts, this will be pre-filled and potentially disabled.
                //     // ->disabled(fn (string $context, ?SubPart $record, Forms\Get $get) => $context === 'edit' || $get('is_contextual_create') === true)
                //     ->dehydrated(), // Ensure it's saved

                Forms\Components\TextInput::make('outlet_code')
                    ->label('outlet code')
                    ->required()
                    ->maxLength(50)
                    ->unique(OutletDealer::class, 'outlet_code', ignoreRecord: true)
                    ->default(fn () => 'OUTLET-' . strtoupper(Str::random(8))) // Optional: default generator
                    ->disabledOn('edit'), // Usually PKs are not editable

                Forms\Components\TextInput::make('outlet_name')
                    ->label('Outlet Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                ->label('Email Address')
                ->email() // sets HTML input type to "email"
                ->required()
                ->rule('email')
                ->placeholder('outlet_outletname@gmail.com'),

                 Forms\Components\TextInput::make('address')
                    ->label('Address')
                    ->required()
                    ->maxLength(255),
                
                //  Forms\Components\TextInput::make('phone')
                //     ->label('Address')
                //     ->required()
                //     ->maxLength(255),

                 Forms\Components\TextInput::make('phone')
                ->label('Phone Number')
                ->required()
                ->maxLength(255)
                ->rule('regex:/^\+62[0-9]{8,}$/') // must start with +62 and have at least 8 digits after
                ->placeholder('+628123456789'), // e
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('outlet_code')
                ->label('Outlet Code')
                ->searchable(),
            Tables\Columns\TextColumn::make('outlet_name')
                ->label('Outlet Name')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),
            Tables\Columns\TextColumn::make('address')
                ->label('Address')
                ->searchable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListOutletDealers::route('/'),
            'create' => Pages\CreateOutletDealer::route('/create'),
            'edit' => Pages\EditOutletDealer::route('/{record}/edit'),
        ];
    }
}
