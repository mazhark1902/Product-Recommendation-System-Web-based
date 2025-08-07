<?php

namespace App\Filament\Resources\SalesOrderResource\Pages;

use App\Filament\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Inventory;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB; // Tambahkan ini
use Filament\Notifications\Notification;
use App\Models\DeliveryOrder; // Tambahkan ini
use App\Models\DeliveryItem;  // Tambahkan ini
use App\Models\StockReservation; // Tambahkan ini
use App\Models\Transaction; // Tambahkan ini
use Illuminate\Support\Str; // Tambahkan ini
use App\Models\Dealer; // Tambahkan ini jika perlu

class GeneratingInvoice extends Page
{
    protected static string $resource = SalesOrderResource::class; // Tambahkan ini

    public SalesOrder $record;
    public array $items = [];
    public bool $allAvailable = true;
    public array $notAvailableItems = [];

    protected static string $view = 'filament.resources.sales-order-resource.pages.generating-invoice';


    public function mount(SalesOrder $record): void
    {
        $this->record = $record;
        $this->items = SalesOrderItem::where('sales_order_id', $record->sales_order_id)->get()->toArray();

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
    }

    public function confirmOrder()
    {
        DB::transaction(function () {
            $salesOrder = $this->record;
    
            // 1. Update status sales order
            $salesOrder->update(['status' => 'confirmed']);
    
    
            // 4. Buat transaksi unpaid
            Transaction::create([
                'invoice_id' => 'INV' . now()->format('YmdHis'),
                'sales_order_id' => $salesOrder->sales_order_id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'unpaid',
                'total_amount' => $salesOrder->total_amount,
            ]);
        });
    
        Notification::make()->success()->title('Invoice Created')->send();
        $this->redirect(SalesOrderResource::getUrl('index'));
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