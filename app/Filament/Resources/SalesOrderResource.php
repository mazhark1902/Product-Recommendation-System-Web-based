<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class SalesOrderResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = SalesOrder::class;
    public static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sales_order_id')
                ->label('Order ID')
                ->sortable()
                ->searchable(),
            
            Tables\Columns\TextColumn::make('outlet.dealer.dealer_name')
                ->label('Dealer'),
            
            Tables\Columns\TextColumn::make('outlet.outlet_name')
                ->label('Outlet'),
                
                Tables\Columns\TextColumn::make('total_amount')
                ->label('Total Amount')
                ->money('IDR', true), // true untuk ribuan separator

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'delivered' => 'success',
                        'rejected' => 'danger',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y, H:i'),
            ])
            ->filters([
                // Tambahkan filter jika diperlukan
            ])
            ->actions([
                Action::make('view_detail')
                    ->label('View Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (SalesOrder $record) => route('filament.admin.resources.sales-orders.view', ['record' => $record]))
                    ->openUrlInNewTab(),

                Action::make('check_availability')
                    ->label('Check Availability')
                    ->icon('heroicon-o-check-circle')
                    ->url(fn (SalesOrder $record) => route('filament.admin.resources.sales-orders.check-availability', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn (SalesOrder $record) => $record->status === 'draft'), // Tambahkan kondisi ini
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            // 'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
            'view' => Pages\ViewSalesOrder::route('/{record}'), // custom view detail
            'check-availability' => Pages\CheckAvailability::route('/{record}/check-availability'), // custom page
        ];
    }
}
