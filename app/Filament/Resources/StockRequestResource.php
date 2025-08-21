<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockRequestResource\Pages;
use App\Models\StockRequest;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class StockRequestResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = StockRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Inventory';

    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'approve'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\TextInput::make('request_id')
                            ->default('REQ-' . strtoupper(Str::random(8)))
                            ->disabled()->dehydrated()->required(),

                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->label('Destination Warehouse')
                            ->required(),

                         Select::make('source_type')
                            ->label('Source of Goods')
                            ->options([
                                'VENDOR' => 'Vendor (Pembelian)',
                                'WAREHOUSE_TRANSFER' => 'Warehouse Transfer (Pindahan)',
                                'PRODUCTION' => 'Production Output (Hasil Produksi)',
                                'ADJUSTMENT' => 'Stock Adjustment (Penyesuaian)',
                            ])
                            ->required(),

                        TextInput::make('source_reference')
                            ->label('Source Reference (PO/TO/Doc No.)')
                            ->helperText('Contoh: PO-12345 atau TO-WH1-WH2-001')
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')->label('Requester')
                            ->default(auth()->id())->disabled()->dehydrated()->required(),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Request Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('sub_part_number')
                                    ->relationship('subPart', 'sub_part_name')
                                    ->searchable()->required(),
                                Forms\Components\TextInput::make('quantity_requested')
                                    ->numeric()->required()->minValue(1)->extraAttributes([
                        'onkeydown' => "if(event.key==='-'){event.preventDefault();}"]),
                            ])
                            ->columns(2)->defaultItems(1)->reorderable(false)
                            ->addActionLabel('Add More Items'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_id')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Requester')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'COMPLETED',
                        'danger' => 'REJECTED',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'COMPLETED' => 'Completed',
                        'REJECTED' => 'Rejected',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn ($record) => $record->status === 'PENDING'),

                // Tombol Approve
                Tables\Actions\Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->action(function (StockRequest $record) {
                        static::processApproval($record);
                    })
                    ->visible(fn (StockRequest $record) => auth()->user()->can('approve', $record)),

                // Tombol Reject
                Tables\Actions\Action::make('reject')
                    ->label('Reject')->icon('heroicon-o-x-circle')->color('danger')
                    ->requiresConfirmation()
                    ->form([Forms\Components\Textarea::make('rejection_reason')->required()])
                    ->action(function (StockRequest $record, array $data) {
                        static::processRejection($record, $data['rejection_reason']);
                    })
                    ->visible(fn (StockRequest $record) => auth()->user()->can('approve', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Fungsi helper untuk logika approval
    public static function processApproval(StockRequest $record): void
    {
        try {
            DB::transaction(function () use ($record) {
                if (is_null($record->warehouse_id)) {
                    throw new \Exception("Destination warehouse has not been set.");
                }

                foreach ($record->items as $item) {
                    $inventory = Inventory::firstOrCreate(
                        ['product_id' => $item->sub_part_number, 'warehouse_id' => $record->warehouse_id],
                        ['quantity_available' => 0]
                    );
                    $inventory->increment('quantity_available', $item->quantity_requested);
                    
                    InventoryMovement::create([
                        'inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),
                        'product_id' => $item->sub_part_number,
                        'movement_type' => 'IN',
                        'quantity' => $item->quantity_requested,
                        'movement_date' => now(),
                        'reference_type' => 'STOCK_REQUEST',
                        'reference_id' => $record->id,
                        'notes' => "Approved request: #{$record->request_id} into Warehouse: {$record->warehouse->name}",
                    ]);
                    $item->update(['quantity_received' => $item->quantity_requested]);
                }
                $record->update(['status' => 'COMPLETED']);

                Notification::make()->title('Stock Request Approved')->success()->send();
                Notification::make()->title('Your request was approved')->success()->sendToDatabase($record->user);
            });
        } catch (\Exception $e) {
            Notification::make()->title('Approval Failed')->body($e->getMessage())->danger()->send();
        }
    }

    // Fungsi helper untuk logika rejection
    public static function processRejection(StockRequest $record, string $reason): void
    {
        $record->update([
            'status' => 'REJECTED',
            'notes' => $record->notes . "\n\nReason for rejection: " . $reason,
        ]);

        Notification::make()->title('Stock Request Rejected')->warning()->send();
        Notification::make()->title('Your request was rejected')->body("Reason: $reason")->warning()->sendToDatabase($record->user);
    }
    
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockRequests::route('/'),
            'create' => Pages\CreateStockRequest::route('/create'),
            'edit' => Pages\EditStockRequest::route('/{record}/edit'),
            'view' => Pages\ViewStockRequest::route('/{record}'),
        ];
    }
}