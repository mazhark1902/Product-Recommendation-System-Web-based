<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Models\DeliveryOrder;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Form;
use Filament\Forms;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class DeliveryOrderResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    public static ?string $navigationGroup = 'Sales';
    protected static ?string $slug = 'delivery-orders';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_order_id')->searchable()->sortable(),
                TextColumn::make('sales_order_id')->searchable()->sortable(),
                TextColumn::make('delivery_date')->date(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('notes')->limit(30),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Detail')
                    ->url(fn ($record) => DeliveryOrderResource::getUrl('view', ['record' => $record])),
            ]);
    }

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryOrders::route('/'),
            'view' => Pages\ViewDeliveryOrder::route('/{record}'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
