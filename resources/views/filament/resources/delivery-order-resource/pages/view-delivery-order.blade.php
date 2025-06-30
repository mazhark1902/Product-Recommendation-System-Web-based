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
                </tr>
            </thead>
            <tbody>
                @foreach ($record->items as $item)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $item->part_number }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::section>
</x-filament::page>