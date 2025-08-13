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
use Filament\Forms\Components\Select;


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
                // TextInput::Make('dealer_code')->required()->visibleOn('create'),
                TextInput::make('dealer_code')
                ->default(function () {
                    $latest = Dealer::orderBy('dealer_code', 'desc')->first()?->dealer_code ?? 'DEA000';
                    $nextNumber = str_pad((intval(substr($latest, 3)) + 1), 3, '0', STR_PAD_LEFT);
                    return 'DEA' . $nextNumber;
                })

                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->visibleOn('create'),
                TextInput::Make('dealer_name')->required(),
                Select::make('province')
            ->required()
            ->options([
                'Aceh' => 'Aceh',
                'Sumatera Utara' => 'Sumatera Utara',
                'Sumatera Barat' => 'Sumatera Barat',
                'Riau' => 'Riau',
                'Kepulauan Riau' => 'Kepulauan Riau',
                'Jambi' => 'Jambi',
                'Sumatera Selatan' => 'Sumatera Selatan',
                'Bangka Belitung' => 'Bangka Belitung',
                'Bengkulu' => 'Bengkulu',
                'Lampung' => 'Lampung',
                'DKI Jakarta' => 'DKI Jakarta',
                'Jawa Barat' => 'Jawa Barat',
                'Banten' => 'Banten',
                'Jawa Tengah' => 'Jawa Tengah',
                'DI Yogyakarta' => 'DI Yogyakarta',
                'Jawa Timur' => 'Jawa Timur',
                'Bali' => 'Bali',
                'Nusa Tenggara Barat' => 'Nusa Tenggara Barat',
                'Nusa Tenggara Timur' => 'Nusa Tenggara Timur',
                'Kalimantan Barat' => 'Kalimantan Barat',
                'Kalimantan Tengah' => 'Kalimantan Tengah',
                'Kalimantan Selatan' => 'Kalimantan Selatan',
                'Kalimantan Timur' => 'Kalimantan Timur',
                'Kalimantan Utara' => 'Kalimantan Utara',
                'Sulawesi Utara' => 'Sulawesi Utara',
                'Gorontalo' => 'Gorontalo',
                'Sulawesi Tengah' => 'Sulawesi Tengah',
                'Sulawesi Barat' => 'Sulawesi Barat',
                'Sulawesi Selatan' => 'Sulawesi Selatan',
                'Sulawesi Tenggara' => 'Sulawesi Tenggara',
                'Maluku' => 'Maluku',
                'Maluku Utara' => 'Maluku Utara',
                'Papua' => 'Papua',
                'Papua Barat' => 'Papua Barat',
                'Papua Barat Daya' => 'Papua Barat Daya',
                'Papua Selatan' => 'Papua Selatan',
                'Papua Tengah' => 'Papua Tengah',
                'Papua Pegunungan' => 'Papua Pegunungan',
            ])
                ->searchable(),
                TextInput::make('email')
                ->label('Email Address')
                ->email() // sets HTML input type to "email"
                ->required()
                ->rule('email'),
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
