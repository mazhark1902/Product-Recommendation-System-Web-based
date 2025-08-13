<?php

namespace App\Filament\Resources\ProductReturnSalesResource\Pages;

use App\Filament\Resources\ProductReturnSalesResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ProductReturn;
use App\Models\CreditMemos;
use App\Models\SubPart;
use Filament\Notifications\Notification;
use App\Models\SalesOrderItem;
use Filament\Actions;

class CreateProductReturnSales extends CreateRecord
{
    protected static string $resource = ProductReturnSalesResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // return_id sudah di-set di default
        return $data;
    }
protected function getFormActions(): array
    {
        return [
            Actions\Action::make('validateReturn')
    ->label('Validate Product Return')
    ->color('info')
    ->action(function () {
        $data = $this->data; // Ambil semua input form yang sudah diisi

        if (empty($data['sales_order_id']) || empty($data['part_number']) || empty($data['quantity'])) {
            Notification::make()
                ->title('Missing data')
                ->body('Please select a Sales Order, Part Number, and Quantity before validation.')
                ->danger()
                ->send();
            return;
        }

        $orderItem = SalesOrderItem::where('sales_order_id', $data['sales_order_id'])
            ->where('part_number', $data['part_number'])
            ->first();

        if (!$orderItem) {
            Notification::make()
                ->title('Product not found')
                ->body("The selected product does not exist in Sales Order {$data['sales_order_id']}.")
                ->danger()
                ->send();
            return;
        }

        $orderedQty = (int) $orderItem->quantity;
        $returnQty = (int) $data['quantity'];

        if ($returnQty > $orderedQty) {
            Notification::make()
                ->title('Quantity exceeds order limit')
                ->body("The return quantity for {$orderItem->part_number} exceeds the ordered quantity in Sales Order {$data['sales_order_id']}.")
                ->danger()
                ->send();
        } else {
            Notification::make()
                ->title('Return validated')
                ->body("The return quantity for {$orderItem->part_number} is valid and can be processed.")
                ->success()
                ->send();
        }
    }),

            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
        ];
    }
protected function afterCreate(): void
{
    $record = $this->record;

    if ($record->refund_action === 'CREDIT_MEMO') {
        $last = CreditMemos::orderByDesc('id')->first();
        $num = $last ? (int) substr($last->credit_memos_id, -5) : 0;
        $cmId = 'CM-' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);

        // Ambil harga dari sub_parts
        $price = \App\Models\SubPart::where('sub_part_number', $record->part_number)->value('price') ?? 0;
        $amount = $price * $record->quantity;

        // Ambil customer_id dari relasi sales_order → sales_orders.customer_id
        $customerId = \App\Models\SalesOrder::where('sales_order_id', $record->sales_order_id)->value('customer_id');

        // Buat credit memo dengan customer_id
        \App\Models\CreditMemos::create([
            'credit_memos_id' => $cmId,
            'return_id' => $record->return_id,
            'amount' => $amount,
            'issued_date' => now(),
            'due_date' => now()->addYear(),
            'status' => 'ISSUED',
            'customer_id' => $customerId, // ← ini tambahan penting
        ]);
    }
}

}