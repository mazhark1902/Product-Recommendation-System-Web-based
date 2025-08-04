<?php

namespace App\Filament\Resources\ProductReturnSalesResource\Pages;

use App\Filament\Resources\ProductReturnSalesResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ProductReturn;
use App\Models\CreditMemos;
use App\Models\SubPart;

class CreateProductReturnSales extends CreateRecord
{
    protected static string $resource = ProductReturnSalesResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // return_id sudah di-set di default
        return $data;
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