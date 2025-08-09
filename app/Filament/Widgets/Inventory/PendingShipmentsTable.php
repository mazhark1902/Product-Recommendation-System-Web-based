<?php

namespace App\Filament\Widgets\Inventory;

use App\Models\DeliveryOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\DeliveryOrderResource;
use Filament\Tables\Filters\SelectFilter; // Import SelectFilter
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PendingShipmentsTable extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // The 'DeliveryOrder' model must point to the 'delivery_orders' table
        // and have a relationship with SalesOrder.
        return $table
            ->query(
                DeliveryOrder::query()
                    ->whereIn('status', ['pending', 'ready'])
                    ->orderBy('delivery_date', 'desc')
            )
            ->heading('Pending Shipments')
            ->columns([
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable()
                    ->label('Shipment Date'),

                // Get customer name via relationship: DeliveryOrder -> SalesOrder -> OutletDealer
                Tables\Columns\TextColumn::make('salesOrder.customer.outlet_name')
                    ->label('Dealer/Customer Name')
                    ->searchable()
                    ->placeholder('Customer not registered'),

                Tables\Columns\TextColumn::make('sales_order_id')
                    ->label('Sales Order ID')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'ready',
                    ]),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'ready' => 'Ready',
                    ])
            ])

            ->actions([
                Tables\Actions\Action::make('viewDelivery')
                    ->label('View Details')
                    ->icon('heroicon-o-truck')
                    // Redirect to the DeliveryOrder resource if it exists
                    ->url(fn (DeliveryOrder $record): string => DeliveryOrderResource::getUrl('view', ['record' => $record->id])),
            ])
            ->emptyStateHeading('No pending shipments');
    }

    /**
     * Ensure the following models have the correct relationships.
     *
     * In App\Models\DeliveryOrder.php:
     * public function salesOrder() {
     * return $this->belongsTo(\App\Models\SalesOrder::class, 'sales_order_id', 'sales_order_id');
     * }
     *
     * In App\Models\SalesOrder.php:
     * public function customer() {
     * return $this->belongsTo(\App\Models\OutletDealer::class, 'customer_id', 'outlet_code');
     * }
     *
     * Also, ensure you have the OutletDealer.php model.
     */
}
