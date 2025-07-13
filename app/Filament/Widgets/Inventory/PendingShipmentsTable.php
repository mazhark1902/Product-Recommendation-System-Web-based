<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\DeliveryOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingShipmentsTable extends BaseWidget
{
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Model 'DeliveryOrder' harus menunjuk ke tabel 'delivery_orders'
        // dan memiliki relasi ke SalesOrder
        return $table
            ->query(
                DeliveryOrder::query()
                    ->whereIn('status', ['pending', 'ready'])
                    ->orderBy('delivery_date', 'desc')
            )
            ->heading('Pengiriman Tertunda (Klaim/Retur)')
            ->columns([
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable()
                    ->label('Tgl. Pengiriman'),

                // Mengambil nama customer melalui relasi: DeliveryOrder -> SalesOrder -> OutletDealer
                Tables\Columns\TextColumn::make('salesOrder.customer.outlet_name')
                    ->label('Nama Dealer/Customer')
                    ->searchable()
                    ->placeholder('Customer tidak terdaftar'),

                Tables\Columns\TextColumn::make('sales_order_id')
                    ->label('ID Sales Order')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'ready',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('viewDelivery')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-truck')
                    // Arahkan ke resource DeliveryOrder jika ada
                    ->url(fn (DeliveryOrder $record): string => url("/admin/delivery-orders/{$record->id}/edit")),
            ])
            ->emptyStateHeading('Tidak ada pengiriman yang tertunda');
    }

    /**
     * Pastikan model-model berikut memiliki relasi yang tepat.
     * * di App\Models\DeliveryOrder.php:
     * public function salesOrder() {
     * return $this->belongsTo(\App\Models\SalesOrder::class, 'sales_order_id', 'sales_order_id');
     * }
     *
     * di App\Models\SalesOrder.php:
     * public function customer() {
     * return $this->belongsTo(\App\Models\OutletDealer::class, 'customer_id', 'outlet_code');
     * }
     *
     * Pastikan juga Anda memiliki model OutletDealer.php
     */
}