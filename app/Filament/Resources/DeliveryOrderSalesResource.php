<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderSalesResource\Pages;
use App\Filament\Resources\DeliveryOrderSalesResource\RelationManagers;
use App\Models\DeliveryOrderSales;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;


use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class DeliveryOrderSalesResource extends Resource
{
    protected static ?string $model = DeliveryOrderSales::class;

    
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    public static ?string $navigationGroup = 'Sales';
    protected static ?string $slug = 'delivery-orders';
    
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('delivery_order_id')
                ->label('Delivery ID')
                ->disabled(), // Biasanya ID tidak bisa diedit

            Forms\Components\TextInput::make('sales_order_id')
                ->label('Sales Order ID')
                ->disabled(), // Terkait dengan SO, seharusnya tidak diubah manual

            Forms\Components\DatePicker::make('delivery_date')
                ->label('Tanggal Pengiriman')
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'ready' => 'Ready to Ship',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ])
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('Catatan Tambahan')
                ->columnSpanFull(),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('delivery_order_id')
                ->searchable()
                ->sortable(),

            TextColumn::make('sales_order_id')
                ->searchable()
                ->sortable(),

            TextColumn::make('delivery_date')
                ->date()
                ->sortable(),

            TextColumn::make('status')
                ->badge()
                ->sortable(),

            TextColumn::make('notes')
                ->limit(30)
                ->sortable(), // biar bisa diurutkan juga walau dibatasi
        ])
        ->defaultSort('delivery_date', 'desc') // urut default terbaru
        ->actions([
            Action::make('view')
                ->label('View Detail')
                ->url(fn ($record) => DeliveryOrderSalesResource::getUrl('view', ['record' => $record])),
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
            'index' => Pages\ListDeliveryOrderSales::route('/'),
            'view' => Pages\ViewDeliveryOrderSales::route('/{record}'),
            'create' => Pages\CreateDeliveryOrderSales::route('/create'),
            'edit' => Pages\EditDeliveryOrderSales::route('/{record}/edit'),
        ];
    }
}
