<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\TransactionResource;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use App\Models\Transaction;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\Payment;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;   
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\WithFileUploads;
use Filament\Forms\Contracts\HasForms;




class EmailAndPayment extends Page implements HasForms
{

    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = TransactionResource::class;

    protected static string $view = 'filament.resources.transaction-resource.pages.email-and-payment';

    public Transaction $record;

    public $proofFile; 

    public function mount(Transaction $record): void
    {
        $this->record = $record;
    }
    public function submit()
    {
        if ($this->proofFile) {
            $path = $this->proofFile->store('proofs', 'public'); // atau 'local' jika pakai default

            $this->record->update([
                'proof' => $path,
            ]);

            Notification::make()
                ->title('Bukti pembayaran berhasil disimpan')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Bukti pembayaran belum dipilih')
                ->danger()
                ->send();
        }
    }



        protected function getFormSchema(): array
        {
            return [
                FileUpload::make('proofFile')
                    ->label('Upload Bukti Pembayaran')
                    ->acceptedFileTypes(['image/png', 'image/jpeg'])
                    ->directory('proofs')
                    ->required()
                    ->columnSpanFull(),
            ];
        }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Email Reminder')
                ->color('primary')
                ->action(function () {
                    $salesOrder = $this->record->salesOrder;

                    if (!$salesOrder || !$salesOrder->dealer || !$salesOrder->outlet) {
                        Notification::make()->title('Data tidak lengkap')->danger()->send();
                        return;
                    }

                    Mail::send('emails.reminder', ['transaction' => $this->record], function ($message) use ($salesOrder) {
                        $message->to($salesOrder->dealer->email)
                            ->subject("NO {$this->record->invoice_id}_Dealer Reminder");
                        $message->to($salesOrder->outlet->email)
                            ->subject("NO {$this->record->invoice_id}_Outlet Reminder");
                    });

                    $this->record->update(['status_reminder' => 'has been sent']);

                    Notification::make()->title('Email berhasil dikirim')->success()->send();
                }),

            Actions\Action::make('Open Email')
                ->color('gray')
                ->url('https://mail.google.com/mail/u/0/#inbox', true),

            Actions\Action::make('Change Status to Paid')
                ->color('success')
                ->disabled(fn () => $this->record->proof === null)
                ->action(function () {
                    $this->record->update(['status' => 'paid']);
                    Notification::make()->title('Status diubah menjadi Paid')->success()->send();
                }),

                // Actions\Action::make('Download Proof')
                //     ->color('secondary')
                //     ->disabled(fn () => $this->record->proof === null)
                //     ->action(function () {
                //         $proofPath = 'proofs/' . $this->record->proof;
                //         if (Storage::exists($proofPath)) {
                //             return Storage::download($proofPath);
                //         } else {
                //             Notification::make()->title('Bukti pembayaran tidak ditemukan')->danger()->send();
                //         }

                //     }),

                Actions\Action::make('Add Payment')
                ->label('Add Payment')
                ->color('success')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('payment_id')
                        ->default(function () {
                            $last = Payment::latest('id')->first();
                            $lastNumber = $last ? intval(substr($last->payment_id, 3)) : 0;
                            return 'PY-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                        })
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('invoice_id')
                        ->default(fn ($livewire) => $livewire->record->invoice_id)
                        ->disabled()
                        ->dehydrated(false),

                    DatePicker::make('payment_date')->required(),
                    DatePicker::make('amount_date')->required(),

                    Select::make('payment_method')
                        ->options([
                            'transfer' => 'Transfer',
                            'cash' => 'Cash',
                            'credit' => 'Credit',
                        ])
                        ->required(),
                ])
                ->modalHeading('Add New Payment')
                ->modalButton('Save')
                ->modalSubmitActionLabel('Save')
                ->modalCancelActionLabel('Decline')
                ->action(function (array $data, EmailAndPayment $livewire) {
                    Payment::create([
                        'payment_id' => $data['payment_id'],
                        'invoice_id' => $livewire->record->invoice_id,
                        'payment_date' => $data['payment_date'],
                        'amount_date' => $data['amount_date'],
                        'payment_method' => $data['payment_method'],
                        'created_at' => now(),
                    ]);

                    Notification::make()->title('Payment saved successfully')->success()->send();
                })
        ];
    }
}
