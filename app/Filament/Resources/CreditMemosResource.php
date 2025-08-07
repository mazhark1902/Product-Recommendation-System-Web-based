<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditMemosResource\Pages;
use App\Filament\Resources\CreditMemosResource\RelationManagers;
use App\Models\CreditMemos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;


class CreditMemosResource extends Resource 
{

    use HasShieldFormComponents;
    protected static ?string $model = CreditMemos::class;
    public static ?string $navigationGroup = 'Sales';
    

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

 public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('credit_memos_id')->label('Credit Memo ID')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('return_id')->label('Return ID')->sortable(),
            Tables\Columns\TextColumn::make('customer_id')->label('Dealer')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('amount')->label('Amount')->money('IDR'),
            Tables\Columns\TextColumn::make('issued_date')->label('Issued At')->dateTime(),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'primary' => 'ISSUED',
                    'success' => 'PAID',
                    'danger' => 'EXPIRED',
                ])
                ->label('Status'),
        ])
        ->filters([
            //
        ])
->actions([

    // 💬 View Modal Custom
    Tables\Actions\Action::make('viewCreditMemoDetails')
        ->label('View Credit Memo')
        ->icon('heroicon-o-eye')
        ->color('primary')
        ->modalHeading('Credit Memo Details')
        ->modalSubmitAction(false)
        ->modalCancelActionLabel('Close')
        ->modalContent(function (CreditMemos $record) {
            return view('credit-memos.modal-details', compact('record'));
        }),

            // 📧 Email Credit Memo
            Tables\Actions\Action::make('emailCreditMemo')
                ->label('Email Credit Memo')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (CreditMemos $record) {
                    $dealerEmail = optional($record->dealer)->email;

                    if (!$dealerEmail) {
                        \Filament\Notifications\Notification::make()
                            ->title('Email dealer tidak ditemukan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    \Illuminate\Support\Facades\Mail::send('emails.credit-memo', ['creditMemo' => $record], function ($message) use ($dealerEmail, $record) {
                        $message->to($dealerEmail)
                                ->subject("Credit Memo Notification - {$record->credit_memos_id}");
                    });

                    \Filament\Notifications\Notification::make()
                        ->title('Email berhasil dikirim ke dealer.')
                        ->success()
                        ->send();
                }),
        ])

        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditMemos::route('/'),
            'create' => Pages\CreateCreditMemos::route('/create'),
            'edit' => Pages\EditCreditMemos::route('/{record}/edit'),
        ];
    }
}
