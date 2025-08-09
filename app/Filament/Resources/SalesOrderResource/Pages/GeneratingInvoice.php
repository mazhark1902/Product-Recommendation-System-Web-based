<?php

namespace App\Filament\Resources\SalesOrderResource\Pages;

use App\Filament\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Inventory;

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

        // 1. Update status sales order
        $salesOrder->update(['status' => 'confirmed']);

        // 2. Buat transaksi
        $transaction = Transaction::create([
            'invoice_id' => 'INV' . now()->format('YmdHis'),
            'sales_order_id' => $salesOrder->sales_order_id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'unpaid',
            'total_amount' => $this->tableTotal, // sum subtotal
        ]);

        // 3. Generate PDF dari view
        $pdf = Pdf::loadView('pdf.invoice', [
            'transaction' => $transaction,
            'tableData' => $this->tableData,
            'tableTotal' => $this->tableTotal
        ]);

        $pdfPath = storage_path('app/public/invoice_' . $transaction->invoice_id . '.pdf');
        $pdf->save($pdfPath);

        // 4. Kirim email ke dealer
        $dealerEmail = $salesOrder->dealer->email ?? null;
        if ($dealerEmail) {
            Mail::send('emails.reminder', [
                'transaction' => $transaction,
                'creditAmount' => 0
            ], function ($message) use ($dealerEmail, $pdfPath, $transaction) {
                $message->to($dealerEmail)
                        ->subject('Invoice ' . $transaction->invoice_id)
                        ->attach($pdfPath);
            });
        }

        // 5. Notifikasi sukses
        Notification::make()
            ->title("Successfully generated: {$transaction->invoice_id} & sent to email: {$dealerEmail}")
            ->success()
            ->send();

        // 6. Redirect ke halaman index SalesOrderResource
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
        $this->redirect(SalesOrderResource::getUrl('check-availability', ['record' => $this->record->getKey()]));
    }
}
