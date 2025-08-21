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
            Tables\Columns\TextColumn::make('credit_memos_id')
                ->label('Credit Memo ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('return_id')
                ->label('Return ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('outlet.dealer.dealer_name')
                ->label('Dealer')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('amount')
                ->label('Amount')
                ->money('IDR')
                ->sortable(),

            Tables\Columns\TextColumn::make('issued_date')
                ->label('Issued At')
                ->dateTime()
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'primary' => 'ISSUED',
                    'success' => 'PAID',
                    'danger' => 'EXPIRED',
                ])
                ->label('Status')
                ->sortable(),
        ])
        ->defaultSort('issued_date', 'desc') // tampil dari issued_date terbaru
        ->filters([
            //
        ])
->actions([

    // 💬 View Modal Custom
    Tables\Actions\Action::make('emailCreditMemo')
    ->label('Email Credit Memo')
    ->icon('heroicon-o-envelope')
    ->color('success')
    ->requiresConfirmation()
    ->action(function (CreditMemos $record) {
        $dealerEmail = optional($record->dealer)->email;

        if (!$dealerEmail) {
            \Filament\Notifications\Notification::make()
                ->title('Dealer email not found.')
                ->danger()
                ->send();
            return;
        }

        // Generate PDF dokumen credit memo
        $pdf = \PDF::loadView('pdf.credit_memo', [
            'creditMemo' => $record,
            // Tambahkan data lain jika perlu, misal relasi, detail item, dll
        ]);

        $fileName = "credit_memo_{$record->credit_memos_id}.pdf";
        $pdfPath = storage_path("app/public/{$fileName}");
        $pdf->save($pdfPath);

        // Kirim email dengan attachment PDF
        \Illuminate\Support\Facades\Mail::send('emails.credit-memo', [
            'creditMemo' => $record,
            'dealerEmail' => $dealerEmail,
            // Bisa juga pass company info, contact person, dll ke view email
        ], function ($message) use ($dealerEmail, $fileName, $pdfPath, $record) {
            $message->to($dealerEmail)
                ->subject("Credit Memo Notification - {$record->credit_memos_id}")
                ->attach($pdfPath);
        });

        \Filament\Notifications\Notification::make()
            ->title("Successfully Generate Document Credit Memo & Send Email to: {$dealerEmail}")
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
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['ISSUED', 'REFUNDED']);
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
