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
use App\Models\CreditMemos;
use Illuminate\Support\Facades\DB; // <- tambah DB
use Barryvdh\DomPDF\Facade\Pdf; // tambahkan di atas
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\DeliveryOrder;
use App\Models\DeliveryItem;
use App\Models\SubPart;





class EmailAndPayment extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = TransactionResource::class;

    protected static string $view = 'filament.resources.transaction-resource.pages.email-and-payment';

    public Transaction $record;



public function mount(Transaction $record): void
{
    $this->record = $record;

    if (request()->query('uploaded') == 1) {
        $salesOrderNo = $this->record->salesOrder->sales_order_no ?? 'Unknown';

        Notification::make()
            ->title('✅ Proof Uploaded Successfully')
            ->body("Proof has been saved for Sales Order: <strong style='color:#0d6efd;'>{$salesOrderNo}</strong>")
            ->success()
            ->send();
    }
}


    // Ganti jadi:
public $proofFile = null;

public function submit()
{
    if ($this->proofFile) {
        $path = $this->proofFile->store('proofs', 'public');

        $this->record->update([
            'proof' => $path,
        ]);

        $salesOrderNo = $this->record->salesOrder->sales_order_no ?? 'Unknown';

        Notification::make()
            ->title('✅ Proof Uploaded Successfully')
            ->body("Proof has been saved for Sales Order: <strong style='color:#0d6efd;'>{$salesOrderNo}</strong>")
            ->success()
            ->send();

        $this->proofFile = null; // reset supaya input kosong
    } else {
        Notification::make()
            ->title('❌ Failed to Upload Proof')
            ->body('No proof file was selected.')
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
                        Notification::make()->title('Incomplete data')->danger()->send();
                        return;
                    }

                    // --- Hitung oldCreditMemo (semua credit memo ISSUED untuk customer/outlet) ---
                    $oldCreditMemo = \App\Models\CreditMemos::join(
                            'product_returns', 'credit_memos.return_id', '=', 'product_returns.return_id'
                        )
                        ->join('sales_orders', 'product_returns.sales_order_id', '=', 'sales_orders.sales_order_id')
                        ->where('sales_orders.customer_id', $salesOrder->customer_id)
                        ->where('credit_memos.status', 'ISSUED')
                        ->sum('credit_memos.amount');

                    // --- Hitung currentCreditMemo (credit memo yang berasal dari return untuk sales order ini) ---
                    $currentCreditMemo = \App\Models\CreditMemos::join(
                            'product_returns', 'credit_memos.return_id', '=', 'product_returns.return_id'
                        )
                        ->where('product_returns.sales_order_id', $salesOrder->sales_order_id)
                        ->sum('credit_memos.amount');

                    // --- Build tableData (nama produk dari sub_parts, delivered qty dari delivery_items) ---
                    $tableData = [];
                    $tableTotal = 0;

                    $soItems = SalesOrderItem::where('sales_order_id', $salesOrder->sales_order_id)->get();

                    foreach ($soItems as $item) {
                        // ambil nama & harga dari sub_parts kalau ada
                        $subPart = SubPart::where('sub_part_number', $item->part_number)->first();
                        $productName = $subPart ? ($subPart->sub_part_name ?? $item->part_number) : $item->part_number;
                        $unitPrice = $subPart ? (float) ($subPart->price ?? $item->unit_price ?? 0) : (float) ($item->unit_price ?? 0);

                        // hitung delivered qty dari delivery_orders yang status 'delivered'
                        $deliveredQty = DeliveryItem::whereIn('delivery_order_id', function ($q) use ($salesOrder) {
                                $q->select('delivery_order_id')
                                  ->from('delivery_orders')
                                  ->where('sales_order_id', $salesOrder->sales_order_id)
                                  ->where('status', 'delivered');
                            })
                            ->where('part_number', $item->part_number)
                            ->sum('quantity');

                        $subtotal = $deliveredQty * $unitPrice;

                        $tableData[] = [
                            'product' => $productName,
                            'delivered_qty' => (int) $deliveredQty,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                        ];

                        $tableTotal += $subtotal;
                    }

                    // --- Hitung total setelah credit (hanya untuk tampilan, tidak mengubah DB) ---
                    $totalCreditAvailable = (float) $oldCreditMemo + (float) $currentCreditMemo;
                    $creditMemoUsed = min($tableTotal, $totalCreditAvailable);
                    $payableAmount = max(0, $tableTotal - $creditMemoUsed);

                    // (opsional) bagi penggunaan credit antara old/current untuk tampilan:
                    $usedFromOld = min((float) $oldCreditMemo, $creditMemoUsed);
                    $usedFromCurrent = $creditMemoUsed - $usedFromOld;

                    // --- Generate PDF dan simpan ---
                    $pdf = Pdf::loadView('pdf.invoice', [
                        'transaction' => $this->record,
                        'tableData' => $tableData,
                        'tableTotal' => $tableTotal,
                        'oldCreditMemo' => $oldCreditMemo,
                        'currentCreditMemo' => $currentCreditMemo,
                        'creditMemoUsed' => $creditMemoUsed,
                        'payableAmount' => $payableAmount,
                        'usedFromOld' => $usedFromOld,
                        'usedFromCurrent' => $usedFromCurrent,
                    ])->setPaper('A4');

                    $pdfPath = storage_path('app/public/invoice_' . $this->record->invoice_id . '.pdf');
                    $pdf->save($pdfPath);

                    // --- Kirim email ke dealer + outlet sekaligus (kedua alamat) ---
                    Mail::send('emails.reminder', [
                        'transaction' => $this->record,
                        // untuk kompatibilitas view lama sekaligus data baru
                        'creditAmount' => $oldCreditMemo,
                        'oldCreditMemo' => $oldCreditMemo,
                        'currentCreditMemo' => $currentCreditMemo,
                        'creditMemoUsed' => $creditMemoUsed,
                        'payableAmount' => $payableAmount,
                        'tableData' => $tableData,
                        'tableTotal' => $tableTotal,
                    ], function ($message) use ($salesOrder, $pdfPath) {
                        // kirim ke dealer dan outlet
                        $toAddresses = [$salesOrder->dealer->email];
                        if (!empty($salesOrder->outlet->email)) {
                            $toAddresses[] = $salesOrder->outlet->email;
                        }

                        $message->to($toAddresses)
                            ->subject("Payment Reminder - {$this->record->invoice_id}")
                            ->attach($pdfPath);
                    });

                    // update flag reminder dan notifikasi
                    $this->record->update(['status_reminder' => 'has been sent']);

                    Notification::make()
                        ->title('Email Reminder Success')
                        ->body("Successfully sent email to: {$salesOrder->dealer->email}" . (!empty($salesOrder->outlet->email) ? " & {$salesOrder->outlet->email}" : ''))
                        ->success()
                        ->send();
                }),


            Actions\Action::make('Open Email')
                ->color('gray')
                ->url('https://mail.google.com/mail/u/0/#inbox', true),

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
                        ->dehydrated(true),

                    TextInput::make('invoice_id')
                        ->default(fn ($livewire) => $livewire->record->invoice_id)
                        ->disabled()
                        ->dehydrated(true),

                    DatePicker::make('payment_date')->required(),
                    TextInput::make('amount_paid')->required(),
                    Select::make('payment_method')
                        ->options([
                            'Bank Transfer' => 'Bank Transfer',
                            'Credit Note' => 'Credit Note',
                            'Credit Note & Bank Transfer' => 'Credit Note & Bank Transfer',
                        ])
                        ->required(),
                ])
                ->modalHeading('Add New Payment')
                ->modalButton('Save')
                ->modalSubmitActionLabel('Save')
                ->modalCancelActionLabel('Decline')
                ->action(function (array $data, EmailAndPayment $livewire) {
                    $paymentAmount = (float) $data['amount_paid'];
                    $paymentMethod = $data['payment_method'];

                    $salesOrder = $livewire->record->salesOrder;
                    if (!$salesOrder || !$salesOrder->customer_id) {
                        Notification::make()
                            ->title('Sales order / customer not found for this transaction.')
                            ->danger()
                            ->send();
                        return redirect()->to(\App\Filament\Resources\TransactionResource::getUrl('index'));
                    }

                    $customerId = $salesOrder->customer_id;

                    // === Pre-check untuk CREDIT NOTE ===
                    if ($paymentMethod === 'Credit Note') {
                        $creditMemos = CreditMemos::where('customer_id', $customerId)
                            ->where('status', 'ISSUED')
                            ->orderBy('issued_date')
                            ->get();

                        $totalAvailable = $creditMemos->sum('amount');

                        if ($totalAvailable < $paymentAmount) {
                            Notification::make()
                                ->title('Insufficient credit memo for this customer.')
                                ->danger()
                                ->send();

                            return redirect()->to(\App\Filament\Resources\TransactionResource::getUrl('index'));
                        }
                    }

                    DB::transaction(function () use ($data, $livewire, $paymentAmount, $paymentMethod, $customerId) {
                        // === CASE: CREDIT NOTE ===
                        if ($paymentMethod === 'Credit Note') {
                            $creditMemos = CreditMemos::where('customer_id', $customerId)
                                ->where('status', 'ISSUED')
                                ->orderBy('issued_date')
                                ->get();

                            $remaining = $paymentAmount;
                            foreach ($creditMemos as $memo) {
                                if ($remaining <= 0) break;

                                if ($memo->amount <= $remaining) {
                                    $remaining -= $memo->amount;
                                    $memo->status = 'REFUNDED';
                                    $memo->save();
                                } else {
                                    $memo->amount -= $remaining;
                                    $memo->save();
                                    $remaining = 0;
                                }
                            }
                        }

                        // === CASE: CREDIT NOTE & BANK TRANSFER ===
                        if ($paymentMethod === 'Credit Note & Bank Transfer') {
                            $creditMemos = CreditMemos::where('customer_id', $customerId)
                                ->where('status', 'ISSUED')
                                ->orderBy('issued_date')
                                ->get();

                            foreach ($creditMemos as $memo) {
                                $memo->status = 'REFUNDED';
                                $memo->save();
                            }
                        }

                        // === Create Payment record ===
                        Payment::create([
                            'payment_id' => $data['payment_id'],
                            'invoice_id' => $livewire->record->invoice_id,
                            'payment_date' => $data['payment_date'],
                            'amount_paid' => $paymentAmount,
                            'payment_method' => $paymentMethod,
                            'created_at' => now(),
                        ]);

                        // Update transaction status -> paid
                        $livewire->record->update(['status' => 'paid']);
                    });

                    // Ambil sales order & data dealer/outlet
                    $salesOrder = $livewire->record->salesOrder;
                    $dealerName = $salesOrder->outlet->outlet_name ?? 'Outlet';
                    $outletEmail = $salesOrder->outlet->email ?? null;

                    // Ambil item untuk di receipt
                    $soItems = \App\Models\SalesOrderItem::where('sales_order_id', $salesOrder->sales_order_id)->get();
                    $tableData = [];
                    $tableTotal = 0;
                    foreach ($soItems as $item) {
                        $subPart = \App\Models\SubPart::where('sub_part_number', $item->part_number)->first();
                        $productName = $subPart ? ($subPart->sub_part_name ?? $item->part_number) : $item->part_number;
                        $unitPrice = $subPart ? (float) ($subPart->price ?? $item->unit_price ?? 0) : (float) ($item->unit_price ?? 0);
                        $deliveredQty = \App\Models\DeliveryItem::whereIn('delivery_order_id', function ($q) use ($salesOrder) {
                                $q->select('delivery_order_id')->from('delivery_orders')
                                ->where('sales_order_id', $salesOrder->sales_order_id)
                                ->where('status', 'delivered');
                            })
                            ->where('part_number', $item->part_number)
                            ->sum('quantity');

                        $subtotal = $deliveredQty * $unitPrice;
                        $tableData[] = [
                            'product' => $productName,
                            'delivered_qty' => (int) $deliveredQty,
                            'unit_price' => $unitPrice,
                            'subtotal' => $subtotal,
                        ];
                        $tableTotal += $subtotal;
                    }

                    // Generate PDF Receipt
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', [
                        'invoiceId' => $livewire->record->invoice_id,
                        'customerName' => $salesOrder->outlet->outlet_name ?? '-',
                        'paymentDate' => $data['payment_date'],
                        'paymentMethod' => $data['payment_method'],
                        'amountPaid' => $paymentAmount,
                        'items' => $tableData,
                        'tableTotal' => $tableTotal,
                    ])->setPaper('A4');

                    // Simpan PDF di storage/app/public
                    $fileName = 'receipt_' . $livewire->record->invoice_id . '.pdf';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $pdf->output());

                    // Path absolut untuk attachment email
                    $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($fileName);

                    $invoiceId = $livewire->record->invoice_id;

                    \Mail::send('emails.payment_receipt', [
                        'dealerName' => $dealerName,
                        'invoiceId' => $invoiceId,
                        'paymentDate' => $data['payment_date'],
                        'paymentMethod' => $data['payment_method'],
                        'amountPaid' => $paymentAmount,
                    ], function ($message) use ($salesOrder, $absolutePath, $invoiceId) {
                        $to = [$salesOrder->outlet->email];
                        if (!empty($salesOrder->outlet->email)) {
                            $to[] = $salesOrder->outlet->email;
                        }
                        $message->to($to)
                            ->subject("Payment Receipt - {$invoiceId}")
                            ->attach($absolutePath);
                    });

                    // URL publik setelah php artisan storage:link
                    $receiptUrl = asset('storage/' . $fileName);

                    // Kirim notifikasi dengan link PDF
                    Notification::make()
                        ->title('Payment Successfully')
                        ->body('<a href="' . $receiptUrl . '" target="_blank">Lihat Receipt</a>')
                        ->success()
                        ->send();
                        
                    return redirect()->to(\App\Filament\Resources\TransactionResource::getUrl('index'));
                })





                ->disabled(fn () => $this->record->proof === null),


            Actions\Action::make('Check Credit Memo')
    ->label('Check Credit Memo')
    ->color('warning')
    ->action(function () {
        $salesOrder = $this->record->salesOrder;

        if (!$salesOrder || !$salesOrder->customer_id) {
            Notification::make()
                ->title('Dealer not found for this sales order.')
                ->danger()
                ->send();
            return;
        }

        $customerId = $salesOrder->customer_id;

        // Ambil total credit memo dari customer tersebut dengan status ISSUED
        $totalCreditMemo = \App\Models\CreditMemos::where('customer_id', $customerId)
            ->where('status', 'ISSUED')
            ->sum('amount');

        if ($totalCreditMemo > 0) {
            Notification::make()
                ->title("Credit Memo found for Dealer: {$customerId}")
                ->body("Total credit memo (status ISSUED): Rp " . number_format($totalCreditMemo, 0, ',', '.'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Dealer {$customerId} does not have a credit memo with status ISSUED")
                ->warning()
                ->send();
        }
    }),

        ];
    }
}
