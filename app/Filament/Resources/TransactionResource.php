<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    public static ?string $navigationGroup = 'Sales';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_id')->searchable(),
                TextColumn::make('sales_order_id')->searchable(),
                TextColumn::make('invoice_date')->date(),
                TextColumn::make('due_date')->date(),
                TextColumn::make('status')->badge(),
                TextColumn::make('total_amount')->money('IDR'),
            ])
            ->actions([
                Action::make('View Detail')
                    ->url(fn (Transaction $record) => TransactionResource::getUrl('view', ['record' => $record])),
                    Action::make('reminder_dealer')
                    ->label('Reminder Dealer')
                    ->action(function (Transaction $record) {
                        // Pastikan relasi salesOrder, outlet, dan dealer ada
                        $salesOrder = $record->salesOrder;
                        if (!$salesOrder || !$salesOrder->outlet || !$salesOrder->dealer) {
                            throw new \Exception('Outlet or Dealer information is missing for this transaction.');
                        }
                
                        $outletEmail = $salesOrder->outlet->email;
                        $dealerEmail = $salesOrder->dealer->email;
                
                        // Kirim email ke outlet
                        Mail::send('emails.reminder', ['transaction' => $record], function ($message) use ($outletEmail, $record) {
                            $message->to($outletEmail)
                                ->subject("NO {$record->invoice_id}_Outlet Reminder");
                        });
                
                        // Kirim email ke dealer
                        Mail::send('emails.reminder', ['transaction' => $record], function ($message) use ($dealerEmail, $record) {
                            $message->to($dealerEmail)
                                ->subject("NO {$record->invoice_id}_Dealer Reminder");
                        });
                
                        // Update status_reminder
                        $record->update(['status_reminder' => 'has been sent']);
                    })
                    ->requiresConfirmation()
                    ->color('primary')
                
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
}