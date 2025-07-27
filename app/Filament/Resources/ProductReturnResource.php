<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReturnResource\Pages;
use App\Models\ProductReturn;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Filament\Forms\Form;
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
            ->columns([
                TextColumn::make('return_id')->label('Return ID')->searchable(),
                TextColumn::make('sales_order_id')->label('Sales Order ID')->searchable(),
                TextColumn::make('part_number')->label('Part Number')->searchable(),
                TextColumn::make('quantity')->label('Qty'),
                BadgeColumn::make('condition')->label('Kondisi')
                    ->colors([
                        'success' => 'GOOD',
                        'danger' => 'DAMAGED',
                    ]),
                TextColumn::make('reason')->label('Alasan Retur'),
                BadgeColumn::make('status')
                    ->label('Status Proses')
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
            ->actions([
                Action::make('process_return')
                    ->label('Proses Retur')
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Barang Retur')
                    ->modalDescription('Aksi ini akan menambahkan stok kembali ke inventaris. Lanjutkan?')
                    ->action(function (ProductReturn $record) {
                        DB::transaction(function () use ($record) {
                            $inventory = Inventory::firstOrCreate(
                                ['product_id' => $record->part_number],
                                ['quantity_available' => 0, 'minimum_stock' => 10]
                            );

                            if ($record->condition === 'GOOD') {
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
                                'notes' => "Stok masuk dari retur #{$record->return_id}, Kondisi: {$record->condition}",
                            ]);
                        });

                        Notification::make()
                            ->title('Retur Berhasil Diproses')
                            ->body("Stok untuk part {$record->part_number} telah dikembalikan ke inventaris.")
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