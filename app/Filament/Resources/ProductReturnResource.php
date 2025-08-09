<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReturnResource\Pages;
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
            // --- PERBAIKAN: Menambahkan kolom yang bisa disembunyikan/ditampilkan (toggleable) ---
            ->columns([
                TextColumn::make('return_id')->label('Return ID')->searchable(),
                TextColumn::make('part.sub_part_name')->label('Product Name')->searchable()->sortable()->placeholder('N/A'),
                TextColumn::make('sales_order_id')->label('Sales Order ID')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('part_number')->label('Part Number')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity')->label('Qty'),
                TextColumn::make('return_date')->label('Return Date')->date()->sortable(),
                BadgeColumn::make('condition')->label('Condition')->toggleable()
                    ->colors([
                        'success' => 'GOOD',
                        'danger' => 'DAMAGED',
                    ]),
                TextColumn::make('reason')->label('Return Reason')->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('status')
                    ->label('Process Status')
                    ->getStateUsing(function (ProductReturn $record) {
                        $isProcessed = InventoryMovement::where('reference_type', 'PRODUCT_RETURN')
                            ->where('reference_id', $record->id)->exists();
                        return $isProcessed ? 'Processed' : 'Pending';
                    })
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Processed',
                    ])->toggleable(),
            ])
            ->filters([
                SelectFilter::make('condition')
                    ->options([
                        'GOOD' => 'Good',
                        'DAMAGED' => 'Damaged',
                    ]),
                SelectFilter::make('reason')
                    ->options([
                        'Wrong item delivered'=>'Wrong item delivered',
                        'Item defective on arrival'=>'Item defective on arrival',
                        'Customer changed mind'=>'Customer changed mind',
                        'Packaging damaged'=>'Packaging damaged',
                    ]),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->infolist([
                        TextEntry::make('return_id'),
                        TextEntry::make('sales_order_id'),
                        TextEntry::make('part.sub_part_name')->label('Product Name'),
                        TextEntry::make('part_number'),
                        TextEntry::make('quantity'),
                        TextEntry::make('return_date')->date(),
                        TextEntry::make('condition')->badge()->colors([
                            'success' => 'GOOD',
                            'danger' => 'DAMAGED',
                        ]),
                        TextEntry::make('reason'),
                        TextEntry::make('status')
                            ->label('Process Status')
                            ->badge()
                            ->getStateUsing(function (ProductReturn $record) {
                                $isProcessed = InventoryMovement::where('reference_type', 'PRODUCT_RETURN')
                                    ->where('reference_id', $record->id)->exists();
                                return $isProcessed ? 'Processed' : 'Pending';
                            })
                            ->colors([
                                'warning' => 'Pending',
                                'success' => 'Processed',
                            ]),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('process_return')
                    ->label('Process Return')
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->form([
                        Forms\Components\Radio::make('final_condition')
                            ->label('Confirm Item Condition')
                            ->options([
                                'GOOD' => 'Good (Stock will be returned to Available)',
                                'DAMAGED' => 'Damaged (Stock will be moved to Damaged)',
                            ])
                            ->default(fn (ProductReturn $record) => $record->condition)
                            ->required(),
                    ])
                    ->action(function (ProductReturn $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $inventory = Inventory::firstOrCreate(
                                ['product_id' => $record->part_number],
                                ['quantity_available' => 0, 'minimum_stock' => 10, 'quantity_damaged' => 0]
                            );

                            $finalCondition = $data['final_condition'];

                            if ($finalCondition === 'GOOD') {
                                $inventory->increment('quantity_available', $record->quantity);
                            } else {
                                $inventory->increment('quantity_damaged', $record->quantity);
                            }

                            InventoryMovement::create([
                                'inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),
                                'product_id' => $record->part_number,
                                'movement_type' => 'IN',
                                'quantity' => $record->quantity,
                                'movement_date' => now(),
                                'reference_type' => 'PRODUCT_RETURN',
                                'reference_id' => $record->id,
                                'notes' => "Stock in from return #{$record->return_id}, Final Condition: {$finalCondition}",
                            ]);
                        });

                        Notification::make()
                            ->title('Return Processed Successfully')
                            ->body("Stock for part {$record->part_number} has been returned to inventory.")
                            ->success()->send();
                    })
                    ->visible(function (ProductReturn $record): bool {
                         $isProcessed = InventoryMovement::where('reference_type', 'PRODUCT_RETURN')
                            ->where('reference_id', $record->id)->exists();
                        return !$isProcessed;
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReturns::route('/'),
        ];
    }
}