<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReturnResource\Pages;
use App\Models\DeliveryOrderInventory;
use App\Models\DeliveryItem;
use App\Models\ProductReturn;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Tables\Filters\SelectFilter;

class ProductReturnResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = ProductReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(ProductReturn::query()->withCount('inventoryMovements'))
            ->columns([
                TextColumn::make('return_id')->label('Return ID')->searchable(),
                TextColumn::make('part.sub_part_name')->label('Part Name')->searchable()->placeholder('N/A'),
                TextColumn::make('quantity')->label('Qty'),
                TextColumn::make('return_date')->label('Return Date')->date()->sortable(),
                BadgeColumn::make('refund_action')->label('Action')
                    ->colors(['info' => 'RETURN', 'primary' => 'CREDIT_MEMO']),
                BadgeColumn::make('status')
                    ->label('Process Status')
                    ->getStateUsing(fn (ProductReturn $record): string => !is_null($record->condition) ? 'Processed' : 'Pending')
                    ->colors(['warning' => 'Pending', 'success' => 'Processed']),
                BadgeColumn::make('condition')->label('Condition')
                    ->placeholder('Not Set')
                    ->colors(['success' => 'GOOD', 'danger' => 'DAMAGED']),
                TextColumn::make('reason')->label('Return Reason')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sales_order_id')->label('Sales Order ID')->searchable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('condition')->options(['GOOD' => 'Good', 'DAMAGED' => 'Damaged']),
                SelectFilter::make('refund_action')->options(['RETURN' => 'Return', 'CREDIT_MEMO' => 'Credit Memo']),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->infolist([
                        TextEntry::make('return_id'),
                        TextEntry::make('sales_order_id'),
                        TextEntry::make('part.sub_part_name')->label('Part Name'),
                        TextEntry::make('quantity'),
                        TextEntry::make('return_date')->date(),
                        TextEntry::make('condition')->badge()->placeholder('Not Set')->colors(['success' => 'GOOD', 'danger' => 'DAMAGED']),
                        TextEntry::make('reason'),
                        TextEntry::make('refund_action')->badge()->colors(['info' => 'RETURN', 'primary' => 'CREDIT_MEMO']),
                        TextEntry::make('status')->label('Process Status')->badge()
                            ->getStateUsing(fn (ProductReturn $record): string => !is_null($record->condition) ? 'Processed' : 'Pending')
                            ->colors(['warning' => 'Pending', 'success' => 'Processed']),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                // --- Tombol Tahap 1: Set Kondisi & Langsung Update Stok ---
                Action::make('set_condition_and_process_stock')
                    ->label('Set Condition & Process')
                    ->icon('heroicon-o-tag')
                    ->color('primary')
                    ->form([
                        Forms\Components\Radio::make('condition')
                            ->label('Set Item Condition')
                            ->options(['GOOD' => 'Good (Return to Available Stock)', 'DAMAGED' => 'Damaged (Move to Damaged Stock)'])
                            ->required(),
                    ])
                    ->action(function (ProductReturn $record, array $data): void {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                // 1. Update kondisi di record return
                                $record->update(['condition' => $data['condition']]);

                                // 2. Cari atau buat record inventory
                                $inventory = Inventory::firstOrCreate(
                                    ['product_id' => $record->part_number],
                                    ['quantity_available' => 0, 'minimum_stock' => 10, 'quantity_damaged' => 0]
                                );

                                // 3. Update stok berdasarkan kondisi
                                if ($data['condition'] === 'GOOD') {
                                    $inventory->increment('quantity_available', $record->quantity);
                                    $notes = "Stock in from return #{$record->return_id}, Condition: GOOD";
                                } else { // DAMAGED
                                    $inventory->increment('quantity_damaged', $record->quantity);
                                    $notes = "Stock in from return #{$record->return_id}, Condition: DAMAGED";
                                }

                                // 4. Catat pergerakan inventory
                                InventoryMovement::create([
                                    'inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),
                                    'product_id' => $record->part_number,
                                    'movement_type' => 'IN',
                                    'quantity' => $record->quantity,
                                    'movement_date' => now(),
                                    'reference_type' => 'PRODUCT_RETURN',
                                    'reference_id' => $record->id,
                                    'notes' => $notes,
                                ]);
                            });
                            Notification::make()->title('Return Processed & Stock Updated')->body("Stock for item #{$record->part_number} has been updated.")->success()->send();
                        } catch (\Exception $e) {
                             Notification::make()->title('Process Failed')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (ProductReturn $record): bool => is_null($record->condition)),

                // --- Tombol Tahap 2: Hanya untuk Buat Pengiriman Pengganti ---
                Action::make('create_replacement')
                    ->label('Create Replacement')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Create Replacement Delivery Order')
                    ->modalDescription('This will create a new Delivery Order to send the replacement item to the customer. Continue?')
                    ->action(function (ProductReturn $record) {
                        try {
                            DB::transaction(function () use ($record) {
                                // Cari SO original untuk data customer
                                $originalSalesOrder = $record->salesOrder()->with('customer')->first();
                                if(!$originalSalesOrder || !$originalSalesOrder->customer) { throw new \Exception("Customer data not found for original Sales Order."); }
                                
                                // Buat Delivery Order baru
                                $lastDO = DeliveryOrderInventory::orderBy('delivery_order_id', 'desc')->first();
                                $newDoId = 'DO' . str_pad((int) Str::after($lastDO->delivery_order_id ?? 'DO00000', 'DO') + 1, 5, '0', STR_PAD_LEFT);
                                
                                $deliveryOrder = DeliveryOrderInventory::create([
                                    'delivery_order_id' => $newDoId,
                                    'sales_order_id' => $record->sales_order_id,
                                    'delivery_date' => now(),
                                    'status' => 'pending',
                                    'notes' => 'Replacement for return ' . $record->return_id,
                                ]);

                                // Tambahkan item ke DO baru
                                DeliveryItem::create([
                                    'delivery_order_id' => $deliveryOrder->delivery_order_id,
                                    'part_number' => $record->part_number,
                                    'quantity' => $record->quantity,
                                ]);
                            });
                            Notification::make()->title('Replacement Delivery Order Created')->body("A new DO has been created for the replacement item.")->success()->send();
                        } catch (\Exception $e) {
                             Notification::make()->title('Failed to Create Replacement')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(function (ProductReturn $record): bool {
                        // Cek apakah replacement sudah pernah dibuat
                        $replacementExists = DeliveryOrderInventory::where('notes', 'Replacement for return ' . $record->return_id)->exists();
                        
                        // Tampilkan tombol JIKA:
                        // 1. Aksi refund adalah 'RETURN'
                        // 2. Kondisi sudah diset (artinya stok sudah diproses)
                        // 3. Replacement belum pernah dibuat sebelumnya
                        return $record->refund_action === 'RETURN' && !is_null($record->condition) && !$replacementExists;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReturns::route('/'),
        ];
    }
}