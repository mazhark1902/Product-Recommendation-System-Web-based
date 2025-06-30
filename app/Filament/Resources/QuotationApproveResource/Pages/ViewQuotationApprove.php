<?php

namespace App\Filament\Resources\QuotationApproveResource\Pages;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

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
            ->modalHeading('Generate Sales Order')
            ->modalSubheading("Generate otomatis sales order dari Quotation ID: {$this->record->quotation_id}?")
            ->action(function () {
                // Step 1: Approve quotation
                $this->record->update(['status' => 'Approved']);

                // Step 2: Generate sales order
                $quotation = $this->record->load(['items', 'outlet']);
                $lastSo = \App\Models\SalesOrder::orderBy('sales_order_id', 'desc')->first()?->sales_order_id ?? 'SO55000';
                $nextNumber = str_pad((intval(substr($lastSo, 2)) + 1), 5, '0', STR_PAD_LEFT);
                $salesOrderId = 'SO' . $nextNumber;

                $salesOrder = \App\Models\SalesOrder::create([
                    'sales_order_id' => $salesOrderId,
                    'customer_id' => $quotation->outlet_code,
                    'quotation_id' => $quotation->quotation_id,
                    'order_date' => now(),
                    'status' => 'draft',
                    'total_amount' => $quotation->total_amount,
                    'delivery_address' => $quotation->outlet->address ?? null,
                ]);

                foreach ($quotation->items as $item) {
                    \App\Models\SalesOrderItem::create([
                        'sales_order_id' => $salesOrderId,
                        'part_number' => $item->sub_part_number,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ]);
                }

                \Filament\Notifications\Notification::make()
                    ->title("Sales Order $salesOrderId berhasil dibuat dari Quotation {$quotation->quotation_id}")
                    ->success()
                    ->send();

                $this->redirect(\App\Filament\Resources\QuotationApproveResource::getUrl());
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