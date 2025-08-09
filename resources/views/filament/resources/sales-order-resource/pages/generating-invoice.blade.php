<x-filament::page>
    {{-- CARD INFORMASI --}}
    <x-filament::card>
        <h2 class="text-xl font-bold mb-4">Sales Order Overview</h2>
        <p><strong>Sales Order ID:</strong> {{ $record->sales_order_id }}</p>
        <p><strong>Status:</strong> {{ $record->status }}</p>
        <p><strong>Delivery Address:</strong> {{ $record->delivery_address }}</p>

        <p>
            <strong>Status Stock:</strong>
            @if ($allAvailable)
                <span class="text-green-600" style="color: #16a34a;">Available</span>
            @else
                <span class="text-red-600" style="color: #dc2626;">Empty</span>
            @endif
        </p>

        <p>
    <strong>Email Dealer:</strong>
    {{ $record->dealer->email ?? '-' }}
</p>
        <hr class="my-4">

        <h3 class="font-semibold mb-2">Order & Delivery Details</h3>

        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-2 py-1 text-left">Product</th>
                        <th class="border px-2 py-1 text-center">Order Quantity</th>
                        <th class="border px-2 py-1 text-center">Delivered Quantity</th>
                        <th class="border px-2 py-1 text-right">Unit Price</th>
                        <th class="border px-2 py-1 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tableData as $row)
                        <tr>
                            <td class="border px-2 py-1">{{ $row['product'] }}</td>
                            <td class="border px-2 py-1 text-center">{{ $row['order_qty'] }}</td>
                            <td class="border px-2 py-1 text-center">{{ $row['delivered_qty'] }}</td>
                            <td class="border px-2 py-1 text-right">Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                            <td class="border px-2 py-1 text-right">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border px-2 py-4 text-center" colspan="5">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TOTAL: SUM dari subtotal per baris --}}
        <div class="mt-4 text-right font-bold">
            Total: Rp {{ number_format($tableTotal, 0, ',', '.') }}
        </div>
    </x-filament::card>

    {{-- TOMBOL AKSI --}}
    <div class="mt-4 flex gap-2">
        @if ($allAvailable)
            <x-filament::button wire:click="confirmOrder" color="success">Generate & Sent Invoice</x-filament::button>
            <x-filament::button wire:click="rejectOrder" color="danger">Reject</x-filament::button>
        @else
            <x-filament::button color="success" disabled>Generate & Sent Invoice</x-filament::button>
            <x-filament::button wire:click="rejectOrder" color="danger">Reject</x-filament::button>
            <x-filament::button wire:click="checkStock" color="gray">Check Stock</x-filament::button>
            <x-filament::button color="warning">Email Restock</x-filament::button>
        @endif
    </div>
</x-filament::page>
