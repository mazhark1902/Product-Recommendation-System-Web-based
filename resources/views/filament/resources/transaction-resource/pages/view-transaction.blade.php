<x-filament::page>
    <x-filament::section heading="Transaction Details">
        <div><strong>Invoice ID:</strong> {{ $record->invoice_id }}</div>
        <div><strong>Sales Order ID:</strong> {{ $record->sales_order_id }}</div>
        <div><strong>Invoice Date:</strong> {{ $record->invoice_date }}</div>
        <div><strong>Status:</strong> {{ ucfirst($record->status) }}</div>
        <div><strong>Total Amount:</strong> Rp{{ number_format($record->total_amount, 2) }}</div>
    </x-filament::section>

    <x-filament::section heading="Sales Order Items">
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2">Part Number</th>
                    <th class="border border-gray-300 px-4 py-2">Quantity</th>
                    <th class="border border-gray-300 px-4 py-2">Unit Price</th>
                    <th class="border border-gray-300 px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->salesOrder?->items ?? [] as $item)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $item->part_number }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $item->quantity }}</td>
                        <td class="border border-gray-300 px-4 py-2">Rp{{ number_format($item->unit_price, 2) }}</td>
                        <td class="border border-gray-300 px-4 py-2">Rp{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>

    <x-filament::section heading="Payments">
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2">Payment ID</th>
                    <th class="border border-gray-300 px-4 py-2">Date</th>
                    <th class="border border-gray-300 px-4 py-2">Method</th>
                    <th class="border border-gray-300 px-4 py-2">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($record->payments as $payment)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $payment->payment_id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $payment->payment_date }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $payment->payment_method }}</td>
                        <td class="border border-gray-300 px-4 py-2">Rp{{ number_format($payment->amount_paid, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>
</x-filament::page>