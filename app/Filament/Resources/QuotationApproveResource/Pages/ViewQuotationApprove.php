<?php

namespace App\Filament\Resources\QuotationApproveResource\Pages;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Inventory;
use App\Models\DeliveryOrderSales;
use App\Models\DeliveryItem;
use App\Models\StockReservation;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Illuminate\Support\Facades\Storage;


class ViewQuotationApprove extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\QuotationApproveResource::class;

    protected function getActions(): array
    {
        return [
                        Action::make('Approve')
                ->label('Approve')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Sales Order, Delivery Order & Send Quotation PDF')
                ->modalSubheading("This will generate sales & delivery orders, send quotation PDF to outlet, and download it.")
                ->action(function () {
                                            $fileName = null;  // Inisialisasi variabel di luar transaction
                    DB::transaction(function () use (&$fileName) {

                        // Step 1: Approve quotation
                        $this->record->update(['status' => 'Approved']);

                        // Step 2: Generate sales order
                        $quotation = $this->record->load(['items', 'outlet']);
                        $lastSo = SalesOrder::orderBy('sales_order_id', 'desc')->first()?->sales_order_id ?? 'SO55000';
                        $nextNumber = str_pad((intval(substr($lastSo, 2)) + 1), 5, '0', STR_PAD_LEFT);
                        $salesOrderId = 'SO' . $nextNumber;

                        $salesOrder = SalesOrder::create([
                            'sales_order_id' => $salesOrderId,
                            'customer_id' => $quotation->outlet_code,
                            'quotation_id' => $quotation->quotation_id,
                            'order_date' => now(),
                            'status' => 'draft',
                            'total_amount' => $quotation->total_amount,
                            'delivery_address' => $quotation->outlet->address ?? null,
                        ]);

                        foreach ($quotation->items as $item) {
                            SalesOrderItem::create([
                                'sales_order_id' => $salesOrderId,
                                'part_number' => $item->sub_part_number,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'subtotal' => $item->subtotal,
                            ]);
                        }

                        // Step 3: Create Delivery Order
                        $lastDO = DeliveryOrder::orderBy('delivery_order_id', 'desc')->first();
                        $newDoId = 'DO' . str_pad((int) Str::after($lastDO->delivery_order_id ?? 'DO00000', 'DO') + 1, 5, '0', STR_PAD_LEFT);

                        $deliveryOrder = DeliveryOrder::create([
                            'delivery_order_id' => $newDoId,
                            'sales_order_id' => $salesOrder->sales_order_id,
                            'delivery_date' => now(),
                            'status' => 'pending',
                        ]);

                        // Step 4: Delivery items + stock reservation + inventory update
                        foreach ($quotation->items as $item) {
                            DeliveryItem::create([
                                'delivery_order_id' => $deliveryOrder->delivery_order_id,
                                'part_number' => $item->sub_part_number,
                                'quantity' => $item->quantity,
                            ]);

                            StockReservation::create([
                                'part_number' => $item->sub_part_number,
                                'sales_order_id' => $salesOrder->sales_order_id,
                                'reserved_quantity' => $item->quantity,
                                'reservation_date' => now(),
                                'status' => 'ACTIVE',
                            ]);

                            $inventory = Inventory::where('product_id', $item->sub_part_number)->first();
                            if ($inventory) {
                                $inventory->update([
                                    'quantity_reserved' => $inventory->quantity_reserved + $item->quantity,
                                ]);
                            }
                        }

                        // Step 5: Generate PDF and save
                        $pdf = \PDF::loadView('pdf.quotation', [
                            'quotation' => $this->record->load(['items', 'outlet']),
                            'items' => $this->record->items,
                        ]);

                        $fileName = "quotation_{$this->record->quotation_id}.pdf";
                        $pdfPath = storage_path("app/public/{$fileName}");
                        $pdf->save($pdfPath);

                        // Step 6: Kirim email
                        $quotation = $this->record->load(['outlet']);
                        if ($quotation->outlet && $quotation->outlet->email) {
                            Mail::send('emails.quotation_review', [
                                'quotation' => $quotation
                            ], function ($message) use ($quotation, $pdfPath) {
                                $message->to($quotation->outlet->email)
                                    ->subject("Quotation {$quotation->quotation_id} sedang direview")
                                    ->attach($pdfPath);
                            });
                        }
                    });

                    Notification::make()
                        ->title("Sales Order, Delivery Order, dan Quotation PDF berhasil dibuat & dikirim.")
                        ->success()
                        ->send();

                    // Sekarang $fileName sudah bisa dipakai
                    return redirect()->to(asset('storage/' . $fileName));
                }),



            Action::make('Reject')
                ->label('Reject')
                ->requiresConfirmation()
                ->modalHeading('Reject Quotation')
                ->modalSubheading('Are you sure you want to reject this quotation? This action cannot be undone.')
                ->color('danger')
                ->action(function () {
                    $this->record->update(['status' => 'Rejected']);
                    Notification::make()
                        ->title('Quotation Rejected')
                        ->danger()
                        ->send();
                    $this->redirect(\App\Filament\Resources\QuotationApproveResource::getUrl());
                }),
        ];
    }

    // Tambahkan method untuk handle generate sales order
    public function generateSalesOrder()
    {
        $quotation = $this->record;

        // Generate sales_order_id otomatis
        $lastSo = SalesOrder::orderBy('sales_order_id', 'desc')->first()?->sales_order_id ?? 'SO55000';
        $nextNumber = str_pad((intval(substr($lastSo, 2)) + 1), 5, '0', STR_PAD_LEFT);
        $salesOrderId = 'SO' . $nextNumber;

        // Insert ke sales_orders
        $salesOrder = SalesOrder::create([
            'sales_order_id' => $salesOrderId,
            'customer_id' => $quotation->outlet_code,
            'quotation_id' => $quotation->quotation_id,
            'order_date' => now(),
            'status' => 'draft',
            'total_amount' => $quotation->total_amount,
            'delivery_address' => $quotation->outlet->address ?? null,
        ]);

        // Insert ke sales_order_items
        foreach ($quotation->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $salesOrderId,
                'part_number' => $item->sub_part_number,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ]);
        }

        Notification::make()
            ->title("Sales Order $salesOrderId berhasil dibuat dari Quotation {$quotation->quotation_id}")
            ->success()
            ->send();

        $this->redirect(\App\Filament\Resources\QuotationApproveResource::getUrl());
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        return [
            'items' => QuotationItem::where('quotation_id', $this->record->quotation_id)->get(),
        ];
    }

    public function getView(): string
    {
        return 'filament.resources.quotation-approve-resource.pages.view-quotation-approve';
    }
}