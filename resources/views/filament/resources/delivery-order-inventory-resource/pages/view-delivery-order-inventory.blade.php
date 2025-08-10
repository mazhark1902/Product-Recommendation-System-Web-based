<x-filament::page>
    {{-- Delivery Order Details --}}
    <x-filament::section heading="Delivery Order Details">
        <div><strong>Delivery Order ID:</strong> {{ $record->delivery_order_id }}</div>
        <div><strong>Sales Order ID:</strong> {{ $record->sales_order_id }}</div>
        <div><strong>Delivery Date:</strong> {{ $record->delivery_date }}</div>
        <div><strong>Status:</strong> {{ ucfirst($record->status) }}</div>
        <div><strong>Notes:</strong> {{ $record->notes }}</div>
    </x-filament::section>

    {{-- Delivery Items --}}
    <x-filament::section heading="Delivery Items">
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
                @foreach ($record->items as $item)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $item->part_number }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $item->quantity }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            Rp {{ number_format($item->part->price ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            Rp {{ number_format(($item->quantity ?? 0) * ($item->part->price ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>

    {{-- Related Sales Order --}}
    @if ($record->salesOrder)
        <x-filament::section heading="Related Sales Order">
            <div><strong>Sales Order ID:</strong> {{ $record->salesOrder->sales_order_id }}</div>
            <div><strong>Dealer:</strong> {{ $record->salesOrder->outlet->dealer->dealer_name ?? '-' }}</div>
            <div><strong>Outlet:</strong> {{ $record->salesOrder->outlet->outlet_name ?? '-' }}</div>
            <div><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($record->salesOrder->order_date)->format('d M Y') }}</div>
            <div><strong>Status:</strong> {{ ucfirst($record->salesOrder->status) }}</div>
            <div><strong>Total Amount:</strong> 
                Rp {{ number_format($record->salesOrder->total_amount ?? 0, 0, ',', '.') }}
            </div>
        </x-filament::section>
    @endif
</x-filament::page>