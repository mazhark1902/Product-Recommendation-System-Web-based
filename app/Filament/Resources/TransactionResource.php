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
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class TransactionResource extends Resource
{
    use HasShieldFormComponents;
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
                Action::make('email_payment')
                    ->label('Email & Payment')
                    ->icon('heroicon-o-envelope')
                    ->color('secondary')
                    ->url(fn (Transaction $record) => TransactionResource::getUrl('email-payment', ['record' => $record]))
                    ->openUrlInNewTab()

                        
                    ->requiresConfirmation()
                    ->color('primary'),
                    
                    
                
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'email-payment' => Pages\EmailAndPayment::route('/{record}/email-payment'),
        ];
    }
}