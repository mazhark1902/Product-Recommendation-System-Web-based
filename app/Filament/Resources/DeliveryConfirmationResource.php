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
// Import yang diperlukan untuk Infolist
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class DeliveryConfirmationResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    // --- Pengaturan Navigasi & Label ---
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Delivery Confirmation';
    protected static ?int $navigationSort = 3;

    public static function getPluralModelLabel(): string
    {
        return 'Delivery Confirmation';
    }
    // ------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                DeliveryOrder::query()->whereIn('status', ['pending', 'ready'])
            )
            ->columns([
                TextColumn::make('delivery_order_id')->label('Delivery ID')->searchable()->sortable(),
                TextColumn::make('salesOrder.customer.outlet_name')->label('Customer')->searchable()->placeholder('Sales Order not found'),
                TextColumn::make('delivery_date')->date(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'ready',
                    ])
                    ->sortable(),
            ])
            ->actions([
                // --- AKSI BARU UNTUK MELIHAT DETAIL ---
                Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->infolist([
                        TextEntry::make('delivery_order_id')->label('Delivery ID'),
                        TextEntry::make('sales_order_id')->label('Sales Order ID'),
                        TextEntry::make('salesOrder.customer.outlet_name')->label('Nama Customer'),
                        TextEntry::make('delivery_date')->label('Tanggal Pengiriman')->date(),
                        // Section untuk menampilkan daftar barang
                        Section::make('Barang Pesanan')
                            ->schema([
                                // RepeatableEntry untuk melooping setiap item
                                RepeatableEntry::make('items')
                                    ->label('')
                                    ->schema([
                                        // Asumsi ada relasi 'part' di model DeliveryItem ke SubPart
                                        TextEntry::make('part.sub_part_name')->label('Nama Barang')->weight('bold'),
                                        TextEntry::make('part_number')->label('Part Number'),
                                        TextEntry::make('quantity')->label('Jumlah'),
                                    ])->columns(3)
                            ])
                    ])->modalWidth('3xl'),
                // -----------------------------------------

                Action::make('confirm_delivery')
                    ->label('Confirm & Ship')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengiriman Barang')
                    ->modalDescription('Anda akan mengubah status menjadi "Delivered" dan mengurangi stok. Aksi ini tidak dapat dibatalkan. Lanjutkan?')
                    ->action(function (DeliveryOrder $record) {
                        try {
                            DB::transaction(function () use ($record) {
                                $record->load(['items', 'salesOrder']);

                                if (!$record->salesOrder) {
                                    throw new \Exception("Referensi Sales Order untuk DO #{$record->delivery_order_id} tidak ditemukan.");
                                }

                                foreach ($record->items as $item) {
                                    $inventory = Inventory::where('product_id', $item->part_number)->lockForUpdate()->first();
                                    if (!$inventory) {
                                        throw new \Exception("Produk ID {$item->part_number} tidak ditemukan di inventaris.");
                                    }

                                    if ($inventory->quantity_available < $item->quantity) {
                                        throw new \Exception("Stok tersedia untuk part {$item->part_number} tidak mencukupi. Tersedia: {$inventory->quantity_available}, Dibutuhkan: {$item->quantity}.");
                                    }
                                    
                                    $inventory->decrement('quantity_available', $item->quantity);

                                    if ($inventory->quantity_reserved < $item->quantity) {
                                        Notification::make()
                                            ->title('Peringatan Inkonsistensi Data')
                                            ->body("Stok reservasi untuk part {$item->part_number} ({$inventory->quantity_reserved}) lebih kecil dari yang dikirim ({$item->quantity}). Sistem akan mengatur stok reservasi menjadi 0.")
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
                                        'notes' => "Pengiriman untuk Sales Order {$record->sales_order_id}",
                                    ]);
                                }

                                StockReservation::where('sales_order_id', $record->sales_order_id)
                                    ->where('status', 'ACTIVE')
                                    ->update(['status' => 'RELEASED']);

                                $record->update(['status' => 'delivered']);
                                if ($record->salesOrder) {
                                    $record->salesOrder->update(['status' => 'delivered']);
                                }
                            });

                            Notification::make()
                                ->title('Pengiriman Berhasil Dikonfirmasi')
                                ->body("Stok telah berhasil diperbarui untuk DO #{$record->delivery_order_id}.")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Proses Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
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