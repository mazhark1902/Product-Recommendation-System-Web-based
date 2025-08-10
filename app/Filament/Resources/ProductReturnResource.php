<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReturnResource\Pages;
use App\Models\DeliveryOrder;
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
    protected static ?int $navigationSort = 4;

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
                TextColumn::make('part_number')->label('Part Number')->searchable(),
                TextColumn::make('quantity')->label('Qty'),
                TextColumn::make('return_date')->label('Return Date')->date()->sortable(),
                BadgeColumn::make('refund_action')->label('Action')
                    ->colors(['info' => 'RETURN', 'primary' => 'CREDIT_MEMO']),
                BadgeColumn::make('status')
                    ->label('Process Status')
                    ->getStateUsing(fn (ProductReturn $record): string => $record->inventory_movements_count > 0 ? 'Processed' : 'Pending')
                    ->colors(['warning' => 'Pending', 'success' => 'Processed']),
                BadgeColumn::make('condition')->label('Condition')
                    ->placeholder('Not Set') // Tampilkan jika NULL
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
                        TextEntry::make('part_number'),
                        TextEntry::make('quantity'),
                        TextEntry::make('return_date')->date(),
                        TextEntry::make('condition')->badge()->placeholder('Not Set')->colors(['success' => 'GOOD', 'danger' => 'DAMAGED']),
                        TextEntry::make('reason'),
                        TextEntry::make('refund_action')->badge()->colors(['info' => 'RETURN', 'primary' => 'CREDIT_MEMO']),
                        TextEntry::make('status')->label('Process Status')->badge()
                            ->getStateUsing(fn (ProductReturn $record): string => $record->inventory_movements_count > 0 ? 'Processed' : 'Pending')
                            ->colors(['warning' => 'Pending', 'success' => 'Processed']),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                // --- PERBAIKAN: Tombol Tahap 1 untuk Set Kondisi ---
                Action::make('set_condition')
                    ->label('Set Condition')
                    ->icon('heroicon-o-tag')
                    ->color('warning')
                    ->form([
                        Forms\Components\Radio::make('condition')
                            ->label('Set Item Condition')
                            ->options(['GOOD' => 'Good', 'DAMAGED' => 'Damaged'])
                            ->required(),
                    ])
                    ->action(function (ProductReturn $record, array $data): void {
                        $record->update(['condition' => $data['condition']]);
                        Notification::make()->title('Condition Saved')->body("Item condition has been set to {$data['condition']}. You can now process the return.")->success()->send();
                    })
                    ->visible(fn (ProductReturn $record): bool => is_null($record->condition) && $record->inventory_movements_count === 0),

                // --- PERBAIKAN: Tombol Tahap 2 untuk Proses Inventaris ---
                Action::make('process_return')
                    ->label('Process Return')
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->form([
                        Forms\Components\Radio::make('final_condition')
                            ->label('Confirm Final Item Condition')
                            ->options(['GOOD' => 'Good (Return to Available Stock)', 'DAMAGED' => 'Damaged (Move to Damaged Stock)'])
                            ->default(fn (ProductReturn $record) => $record->condition) // Ambil default dari kondisi yang sudah diset
                            ->required(),
                        Forms\Components\Placeholder::make('info')
                             ->label('Next Step')
                             ->content('A new Delivery Order will be created to send the replacement item to the customer.')
                             ->visible(fn (Model $record) => $record->refund_action === 'RETURN'),
                    ])
                    ->action(function (ProductReturn $record, array $data) {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                // Update kondisi jika diubah saat proses akhir
                                if ($record->condition !== $data['final_condition']) {
                                    $record->update(['condition' => $data['final_condition']]);
                                }

                                $inventory = Inventory::firstOrCreate(['product_id' => $record->part_number],['quantity_available' => 0, 'minimum_stock' => 10, 'quantity_damaged' => 0]);
                                if ($data['final_condition'] === 'GOOD') {
                                    $inventory->increment('quantity_available', $record->quantity);
                                } else {
                                    $inventory->increment('quantity_damaged', 'quantity');
                                }
                                InventoryMovement::create(['inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),'product_id' => $record->part_number,'movement_type' => 'IN','quantity' => $record->quantity,'movement_date' => now(),'reference_type' => 'PRODUCT_RETURN','reference_id' => $record->id,'notes' => "Stock in from return #{$record->return_id}, Final Condition: {$data['final_condition']}",]);
                                if ($record->refund_action === 'RETURN') {
                                    $originalSalesOrder = $record->salesOrder()->with('customer')->first();
                                    if(!$originalSalesOrder || !$originalSalesOrder->customer) { throw new \Exception("Customer data not found for original Sales Order."); }
                                    $lastDO = DeliveryOrder::orderBy('delivery_order_id', 'desc')->first();
                                    $newDoId = 'DO' . str_pad((int) Str::after($lastDO->delivery_order_id ?? 'DO00000', 'DO') + 1, 5, '0', STR_PAD_LEFT);
                                    $deliveryOrder = DeliveryOrder::create(['delivery_order_id' => $newDoId,'sales_order_id' => $record->sales_order_id,'delivery_date' => now(),'status' => 'pending','notes' => 'Replacement for return ' . $record->return_id,]);
                                    DeliveryItem::create(['delivery_order_id' => $deliveryOrder->delivery_order_id,'part_number' => $record->part_number,'quantity' => $record->quantity,]);
                                }
                            });
                            Notification::make()->title('Return Processed Successfully')->body("Stock updated and required actions have been taken.")->success()->send();
                        } catch (\Exception $e) {
                             Notification::make()->title('Process Failed')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (ProductReturn $record): bool => !is_null($record->condition) && $record->inventory_movements_count === 0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReturns::route('/'),
        ];
    }
}