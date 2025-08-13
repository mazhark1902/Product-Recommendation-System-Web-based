<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderInventoryResource\Pages;
use App\Filament\Resources\DeliveryOrderInventoryResource\RelationManagers;
use App\Models\DeliveryOrderInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\StockReservation;


use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

use Filament\Tables\Filters\SelectFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class DeliveryOrderInventoryResource extends Resource
{
    protected static ?string $model = DeliveryOrderInventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Delivery Orders Inventory';
    protected static ?int $navigationSort = 5;
    public static function getPluralModelLabel(): string
    {
        return 'Delivery Orders Inventory';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_order_id')->label('Delivery ID')->searchable()->sortable(),
                TextColumn::make('salesOrder.customer.outlet_name')->label('Customer')->searchable()->placeholder('Sales Order not found'),
                TextColumn::make('delivery_date')->date(),
                BadgeColumn::make('status')
                    ->colors(['warning' => 'pending', 'primary' => 'ready', 'success' => 'delivered'])
                    ->sortable(),
                TextColumn::make('notes')->label('Notes')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(['pending' => 'Pending', 'ready' => 'Ready', 'delivered' => 'Delivered'])
            ])
            ->actions([
                Action::make('print_delivery_note')
                    ->label('Print Delivery Note')->icon('heroicon-o-printer')->color('gray')
                    ->url(fn (DeliveryOrderInventory $record) => route('print.delivery.note', $record), true)
                    ->visible(fn(DeliveryOrderInventory $record) => $record->status === 'delivered'),

                Action::make('view_details')
                    ->label('View Details')->icon('heroicon-o-eye')->color('gray')
                    ->infolist([
                        TextEntry::make('delivery_order_id')->label('Delivery ID'),
                        TextEntry::make('sales_order_id')->label('Sales Order ID'),
                        TextEntry::make('salesOrder.customer.outlet_name')->label('Customer Name'),
                        TextEntry::make('delivery_date')->label('Delivery Date')->date(),
                        TextEntry::make('notes'),
                        Section::make('Ordered Items')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('')->schema([
                                        TextEntry::make('part.sub_part_name')->label('Item Name')->weight('bold'),
                                        TextEntry::make('part_number')->label('Part Number'),
                                        TextEntry::make('quantity')->label('Quantity'),
                                    ])->columns(3)
                            ])
                    ])
                    ->modalWidth('3xl')->modalSubmitAction(false)->modalCancelActionLabel('Close'),

                // --- Tombol Tahap 1: Muncul hanya saat status 'pending' ---
                Action::make('check_availability')
                    ->label('Check Availability')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->action(function (DeliveryOrderInventory $record) {
                        $record->load('items.part');
                        $insufficientItems = [];

                        foreach ($record->items as $item) {
                            $inventory = Inventory::where('product_id', $item->part_number)->first();
                            $effectiveStock = ($inventory->quantity_available ?? 0) - ($inventory->quantity_reserved ?? 0);

                            if (!$inventory || $effectiveStock < $item->quantity) {
                                $insufficientItems[] = [
                                    'name' => $item->part->sub_part_name ?? $item->part_number,
                                    'required' => $item->quantity,
                                    'available' => max(0, $effectiveStock),
                                ];
                            }
                        }

                        if (empty($insufficientItems)) {
                            $record->update(['status' => 'ready']);
                            Notification::make()->title('Stock Available')->body('Order is now ready to be shipped.')->success()->send();
                        } else {
                            $message = "Insufficient stock for the following items:\n\n";
                            foreach ($insufficientItems as $shortItem) {
                                $message .= "- **{$shortItem['name']}**\n (Required: {$shortItem['required']}, Available: {$shortItem['available']})\n";
                            }
                            Notification::make()->title('Insufficient Stock!')->danger()->body($message)->persistent()->send();
                        }
                    })
                    ->visible(fn(DeliveryOrderInventory $record) => $record->status === 'pending'),

                // --- Tombol Tahap 2: Muncul hanya saat status 'ready' ---
                Action::make('confirm_delivery')
                    ->label('Confirm & Ship')->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('shipping_courier')
                            ->label('Shipping Courier')
                            ->options(['JNE' => 'JNE', 'J&T' => 'J&T', 'SiCepat' => 'SiCepat', 'Internal' => 'Delivered by Internal Team'])
                            ->required(),
                        Forms\Components\TextInput::make('tracking_number')->label('Tracking Number'),
                    ])
                    ->modalHeading('Confirm Goods Shipment')
                    ->modalDescription('You are about to change the status to "Delivered". Continue?')
                    ->action(function (DeliveryOrderInventory $record, array $data) {
                        // Logika yang sudah ada sebelumnya tetap dipertahankan
                        try {
                            DB::transaction(function () use ($record, $data) {
                                $isReplacement = Str::startsWith($record->notes, 'Replacement for return');
                                $record->load('items');

                                foreach ($record->items as $item) {
                                    $inventory = Inventory::where('product_id', $item->part_number)->lockForUpdate()->first();
                                    if (!$inventory || $inventory->quantity_available < $item->quantity) {
                                        // Validasi terakhir untuk mencegah race condition
                                        throw new \Exception("Stock for {$item->part_number} is no longer sufficient. Please re-check availability.");
                                    }

                                    if ($isReplacement) {
                                        $inventory->decrement('quantity_available', $item->quantity);
                                        $inventoryMovementNotes = "Replacement shipment for return referenced by {$record->sales_order_id}";
                                    } else {
                                        $inventory->decrement('quantity_available', $item->quantity);
                                        if ($inventory->quantity_reserved < $item->quantity) {
                                            $inventory->quantity_reserved = 0;
                                        } else {
                                            $inventory->decrement('quantity_reserved', $item->quantity);
                                        }
                                        $inventoryMovementNotes = "Shipment for Sales Order {$record->sales_order_id}";
                                    }
                                    $inventory->save();
                                    
                                    InventoryMovement::create(['inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)), 'product_id' => $item->part_number, 'movement_type' => 'OUT', 'quantity' => -$item->quantity, 'movement_date' => now(), 'reference_type' => 'DELIVERY_ORDER', 'reference_id' => $record->id, 'notes' => $inventoryMovementNotes]);
                                }

                                if (!$isReplacement) {
                                    $record->load('salesOrder');
                                    StockReservation::where('sales_order_id', $record->sales_order_id)->where('status', 'ACTIVE')->update(['status' => 'RELEASED']);
                                    if ($record->salesOrder) {
                                        $record->salesOrder->update(['status' => 'delivered']);
                                    }
                                }

                                $record->update(['status' => 'delivered', 'shipping_courier' => $data['shipping_courier'], 'tracking_number' => $data['tracking_number']]);
                            });
                            Notification::make()->title('Shipment Confirmed Successfully')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Process Failed')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn(DeliveryOrderInventory $record) => $record->status === 'ready'),

                // --- Tombol Reject: Muncul saat status 'pending' atau 'ready' ---
                Action::make('reject_delivery')
                    ->label('Reject Delivery')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Delivery Order')
                    ->modalDescription('Are you sure you want to reject this delivery? This will release the reserved stock.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_notes')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function (DeliveryOrderInventory $record, array $data) {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                // 1. Update status DO menjadi 'cancelled'
                                $record->update([
                                    'status' => 'cancelled',
                                    'notes' => $record->notes . "\n\nREJECTED: " . $data['rejection_notes'],
                                ]);

                                // 2. Bebaskan reservasi stok
                                foreach ($record->items as $item) {
                                    $inventory = Inventory::where('product_id', $item->part_number)->lockForUpdate()->first();
                                    if ($inventory && $inventory->quantity_reserved >= $item->quantity) {
                                        $inventory->decrement('quantity_reserved', $item->quantity);
                                    }
                                }

                                // 3. Update status reservasi di tabel stock_reservations
                                StockReservation::where('sales_order_id', $record->sales_order_id)
                                    ->where('status', 'ACTIVE')
                                    ->update(['status' => 'RELEASED']);

                                // 4. (Opsional) Update status sales order jika perlu
                                // $record->salesOrder->update(['status' => 'pending_delivery']);
                            });

                            Notification::make()
                                ->title('Delivery Order Rejected')
                                ->body('The delivery has been cancelled and stock has been released.')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Process Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(DeliveryOrderInventory $record) => in_array($record->status, ['pending', 'ready'])),
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
            'index' => Pages\ListDeliveryOrderInventories::route('/'),
            // 'create' => Pages\CreateDeliveryOrderInventory::route('/create'),
            // 'edit' => Pages\EditDeliveryOrderInventory::route('/{record}/edit'),
        ];
    }
        public static function canCreate(): bool
    {
        return false;
    }
}
