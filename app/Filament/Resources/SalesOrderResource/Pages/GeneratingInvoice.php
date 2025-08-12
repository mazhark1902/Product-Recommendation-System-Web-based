<?php

namespace App\Filament\Resources\SalesOrderResource\Pages;

use App\Filament\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Inventory;
use App\Models\CreditMemos;

use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\DeliveryOrder;
use App\Models\DeliveryItem;
use App\Models\StockReservation;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Dealer;
use App\Models\SubPart;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


class GeneratingInvoice extends Page
{
    protected static string $resource = SalesOrderResource::class;

    public SalesOrder $record;
    public array $items = [];
    public bool $allAvailable = true;
    public array $notAvailableItems = [];
    protected $listeners = ['generateConfirmed' => 'confirmOrder'];


    // data untuk tabel yang akan dirender di blade
    public array $tableData = [];
    // total (sum of subtotal)
    public float $tableTotal = 0.0;

    protected static string $view = 'filament.resources.sales-order-resource.pages.generating-invoice';

    public function mount(SalesOrder $record): void
    {
        $this->record = $record;

        // Ambil sales order items (collection)
        $salesOrderItems = SalesOrderItem::where('sales_order_id', $record->sales_order_id)->get();

        // Simpan juga untuk keperluan lain (jika masih diperlukan)
        $this->items = $salesOrderItems->toArray();

        // Stock availability check (sama seperti sebelumnya)
        foreach ($this->items as $item) {
            $inventory = Inventory::where('product_id', $item['part_number'])->first();
            $availableQty = ($inventory->quantity_available ?? 0) - ($inventory->quantity_reserved ?? 0);
            if ($availableQty < $item['quantity']) {
                $this->notAvailableItems[] = [
                    'part_number' => $item['part_number'],
                    'required' => $item['quantity'],
                    'available' => $availableQty,
                ];
                $this->allAvailable = false;
            }
        }

        // Bangun tableData: Product, order_qty, delivered_qty, unit_price, subtotal
        $this->tableData = $salesOrderItems->map(function ($item) use ($record) {
            // sub_parts primary key: sub_part_number
            $subPart = SubPart::where('sub_part_number', $item->part_number)->first();

            $productName = $subPart ? ($subPart->sub_part_name ?? $item->part_number) : $item->part_number;
            $unitPrice = $subPart ? (float) ($subPart->price ?? 0) : 0.0;

            // Hitung delivered qty dari delivery_orders yang berstatus 'delivered'
            $deliveredQuantity = DeliveryItem::whereIn('delivery_order_id', function ($query) use ($record) {
                    $query->select('delivery_order_id')
                          ->from('delivery_orders')
                          ->where('sales_order_id', $record->sales_order_id)
                          ->where('status', 'delivered');
                })
                ->where('part_number', $item->part_number)
                ->sum('quantity');

            $subtotal = $deliveredQuantity * $unitPrice;

            return [
                'product' => $productName,
                'order_qty' => (int) $item->quantity,
                'delivered_qty' => (int) $deliveredQuantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        })->toArray();

        // Hitung total dari subtotal
        $this->tableTotal = collect($this->tableData)->sum('subtotal');
    }

public function confirmOrder()
{
    DB::transaction(function () {
        $salesOrder = $this->record;

        $salesOrder->update(['status' => 'confirmed']);

        $transaction = Transaction::create([
            'invoice_id' => 'INV' . now()->format('YmdHis'),
            'sales_order_id' => $salesOrder->sales_order_id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'unpaid',
            'total_amount' => $this->tableTotal,
        ]);

        // Ambil credit memo dealer (total semua yg available)
        $oldCreditMemo = CreditMemos::where('customer_id', $salesOrder->outlet->outlet_code)
            ->sum('amount');

        // Hitung credit memo yang akan digunakan (maksimal sebesar total_amount)
        $creditMemoUsed = min($oldCreditMemo, $this->tableTotal);

        // Hitung sisa credit memo setelah dipakai
        $currentCreditMemo = $oldCreditMemo - $creditMemoUsed;

        // Hitung total yang harus dibayar setelah potongan credit memo
        $payableAmount = $this->tableTotal - $creditMemoUsed;

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', [
            'transaction' => $transaction,
            'tableData' => $this->tableData,
            'tableTotal' => $this->tableTotal,
            'oldCreditMemo' => $oldCreditMemo,
            'currentCreditMemo' => $currentCreditMemo,
            'creditMemoUsed' => $creditMemoUsed,
            'payableAmount' => $payableAmount
        ]);

        $pdfPath = storage_path('app/public/invoice_' . $transaction->invoice_id . '.pdf');
        $pdf->save($pdfPath);

        // Email
        $dealerEmail = $salesOrder->dealer->email ?? null;
        if ($dealerEmail) {
            Mail::send('emails.invoice_notification', [
                'transaction' => $transaction,
                'tableData' => $this->tableData,
                'tableTotal' => $this->tableTotal,
                'oldCreditMemo' => $oldCreditMemo,
                'currentCreditMemo' => $currentCreditMemo,
                'creditMemoUsed' => $creditMemoUsed,
                'payableAmount' => $payableAmount
            ], function ($message) use ($dealerEmail, $pdfPath, $transaction) {
                $message->to($dealerEmail)
                        ->subject('Invoice ' . $transaction->invoice_id)
                        ->attach($pdfPath);
            });
        }

        Notification::make()
            ->title("Successfully generated: {$transaction->invoice_id} & sent to email: {$dealerEmail}")
            ->success()
            ->send();

        return redirect(Storage::url('invoice_' . $transaction->invoice_id . '.pdf'));
    });
}

    public function emailRestock()
{
    if (empty($this->notAvailableItems)) {
        Notification::make()
            ->title('Tidak ada sub part yang perlu restock.')
            ->info()
            ->send();
        return;
    }

    DB::transaction(function () {
        $salesOrder = $this->record;

        // Nomor dokumen restock
        $restockId = 'RSK' . now()->format('YmdHis');

        // Ambil detail sub part
        $itemsDetail = collect($this->notAvailableItems)->map(function ($item) {
            $subPart = \App\Models\SubPart::where('sub_part_number', $item['part_number'])->first();
            return [
                'part_number' => $item['part_number'],
                'part_name' => $subPart->sub_part_name ?? '-',
                'required' => $item['required'],
                'available' => $item['available'],
                'shortage' => $item['required'] - $item['available'],
            ];
        });

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.subpart_restock', [
            'restockId' => $restockId,
            'salesOrder' => $salesOrder,
            'items' => $itemsDetail,
            'createdAt' => now(),
        ])->setPaper('A4');

        $pdfFileName = "subpart_restock_{$restockId}.pdf";
        $pdfPath = storage_path("app/public/{$pdfFileName}");
        $pdf->save($pdfPath);

        // Kirim email
        \Illuminate\Support\Facades\Mail::send('emails.subpart_restock_email', [
            'salesOrder' => $salesOrder,
            'restockId' => $restockId,
            'items' => $itemsDetail,
        ], function ($message) use ($pdfPath, $pdfFileName) {
            $message->to('raihan.almi@student.president.ac.id')
                ->cc(['mazhar1902@gmail.com', 'hadi13november@gmail.com'])
                ->subject('SubPart Restock Notification')
                ->attach($pdfPath);
        });

        // Notifikasi sukses
        Notification::make()
            ->title("Berhasil mengirim email SubPart Restock: {$restockId}")
            ->success()
            ->send();

        $this->redirect(SalesOrderResource::getUrl('index'));
    });
}
    public function rejectOrder()
    {
        $this->record->update(['status' => 'rejected']);
        Notification::make()->danger()->title('Order Rejected')->send();
        $this->redirect(SalesOrderResource::getUrl('index'));
    }

    public function checkStock()
    {
        $this->redirect(SalesOrderResource::getUrl('generating-invoice', ['record' => $this->record->getKey()]));
    }
}
