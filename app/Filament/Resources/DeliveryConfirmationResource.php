<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryConfirmationResource\Pages;
use App\Models\DeliveryOrder;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\StockReservation;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms;
use Filament\Tables\Filters\SelectFilter; // <-- TAMBAHKAN IMPORT INI
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class DeliveryConfirmationResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Delivery Orders';
    protected static ?int $navigationSort = 3;

    public static function getPluralModelLabel(): string
    {
        return 'Delivery Orders';
    }

    public static function table(Table $table): Table
    {
        return $table
            // --- PERBAIKAN 1: Hapus query awal agar semua status tampil ---
            // ->query(
            //     DeliveryOrder::query()->whereIn('status', ['pending', 'ready'])
            // )
            ->columns([
                TextColumn::make('delivery_order_id')->label('Delivery ID')->searchable()->sortable(),
                TextColumn::make('salesOrder.customer.outlet_name')->label('Customer')->searchable()->placeholder('Sales Order not found'),
                TextColumn::make('delivery_date')->date(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'ready',
                        'success' => 'delivered', // Tambahkan warna untuk status delivered
                    ])
                    ->sortable(),
            ])
            // --- PERBAIKAN 2: Tambahkan filter untuk status ---
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'ready' => 'Ready',
                        'delivered' => 'Delivered',
                    ])
            ])
            ->actions([
                Action::make('print_delivery_note')
                    ->label('Print Delivery Note')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (DeliveryOrder $record) => route('print.delivery.note', $record), true)
                    ->visible(fn(DeliveryOrder $record) => $record->status === 'delivered'),

                Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->infolist([
                        TextEntry::make('delivery_order_id')->label('Delivery ID'),
                        TextEntry::make('sales_order_id')->label('Sales Order ID'),
                        TextEntry::make('salesOrder.customer.outlet_name')->label('Customer Name'),
                        TextEntry::make('delivery_date')->label('Delivery Date')->date(),
                        Section::make('Ordered Items')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('part.sub_part_name')->label('Item Name')->weight('bold'),
                                        TextEntry::make('part_number')->label('Part Number'),
                                        TextEntry::make('quantity')->label('Quantity'),
                                    ])->columns(3)
                            ])
                    ])
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Action::make('confirm_delivery')
                    ->label('Confirm & Ship')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('shipping_courier')
                            ->label('Shipping Courier')
                            ->options([
                                'JNE' => 'JNE',
                                'J&T' => 'J&T',
                                'SiCepat' => 'SiCepat',
                                'Internal' => 'Delivered by Internal Team',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking Number'),
                    ])
                    ->modalHeading('Confirm Goods Shipment')
                    ->modalDescription('You are about to change the status to "Delivered" and decrease stock. This action cannot be undone. Continue?')
                    ->action(function (DeliveryOrder $record, array $data) {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                $record->load(['items', 'salesOrder']);

                                if (!$record->salesOrder) {
                                    throw new \Exception("Sales Order reference for DO #{$record->delivery_order_id} not found.");
                                }

                                foreach ($record->items as $item) {
                                    $inventory = Inventory::where('product_id', $item->part_number)->lockForUpdate()->first();
                                    if (!$inventory) {
                                        throw new \Exception("Product ID {$item->part_number} not found in inventory.");
                                    }

                                    if ($inventory->quantity_available < $item->quantity) {
                                        throw new \Exception("Available stock for part {$item->part_number} is insufficient. Available: {$inventory->quantity_available}, Required: {$item->quantity}.");
                                    }
                                    
                                    $inventory->decrement('quantity_available', $item->quantity);

                                    if ($inventory->quantity_reserved < $item->quantity) {
                                        Notification::make()
                                            ->title('Data Inconsistency Warning')
                                            ->body("Reserved stock for part {$item->part_number} ({$inventory->quantity_reserved}) is less than the quantity shipped ({$item->quantity}). The system will set the reserved stock to 0.")
                                            ->warning()
                                            ->send();
                                        $inventory->quantity_reserved = 0;
                                    } else {
                                        $inventory->decrement('quantity_reserved', $item->quantity);
                                    }
                                    $inventory->save();

                                    InventoryMovement::create([
                                        'inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),
                                        'product_id' => $item->part_number,
                                        'movement_type' => 'OUT',
                                        'quantity' => -$item->quantity,
                                        'movement_date' => now(),
                                        'reference_type' => 'DELIVERY_ORDER',
                                        'reference_id' => $record->id,
                                        'notes' => "Shipment for Sales Order {$record->sales_order_id}",
                                    ]);
                                }

                                StockReservation::where('sales_order_id', $record->sales_order_id)
                                    ->where('status', 'ACTIVE')
                                    ->update(['status' => 'RELEASED']);

                                $record->update([
                                    'status' => 'delivered',
                                    'shipping_courier' => $data['shipping_courier'],
                                    'tracking_number' => $data['tracking_number'],
                                ]);
                                if ($record->salesOrder) {
                                    $record->salesOrder->update(['status' => 'delivered']);
                                }
                            });

                            Notification::make()
                                ->title('Shipment Confirmed Successfully')
                                ->body("Stock has been successfully updated for DO #{$record->delivery_order_id}.")
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
                    // Tombol ini hanya akan muncul untuk order yang belum dikirim
                    ->visible(fn(DeliveryOrder $record) => $record->status !== 'delivered'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryConfirmations::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}