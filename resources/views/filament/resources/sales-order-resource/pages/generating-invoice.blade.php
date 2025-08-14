<x-filament-panels::page>
    {{-- CARD INFORMASI --}}
    <x-filament::card>
        <h2 class="text-xl font-bold mb-4">Sales Order Overview</h2>
        <p><strong>Sales Order ID:</strong> {{ $record->sales_order_id }}</p>
        <p>
            <strong>Status:</strong> 
            <span @class([
                'font-medium',
                'text-gray-600' => $record->status === 'draft',
                'text-success-600' => $record->status === 'confirmed' || $record->status === 'delivered',
                'text-danger-600' => $record->status === 'rejected',
            ])>
                {{ ucfirst($record->status) }}
            </span>
        </p>
        <p><strong>Delivery Address:</strong> {{ $record->delivery_address }}</p>

        <p>
            <strong>Status Stock:</strong>
            @if ($allAvailable)
                <span class="font-medium text-success-600">Available</span>
            @else
                <span class="font-medium text-danger-600">Insufficient</span>
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
                <thead class="bg-gray-100 dark:bg-gray-800">
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

    {{-- ============================================ --}}
    {{-- PERBAIKAN LOGIKA TOMBOL AKSI DIMULAI DI SINI --}}
    {{-- ============================================ --}}
    <div class="mt-4">
        {{-- Tampilkan tombol aksi hanya jika status order adalah 'draft' --}}
        @if ($record->status === 'confirmed')
            <div class="flex gap-2">
                @if ($allAvailable)
                    {{-- Stok tersedia, tombol utama aktif --}}
                    <x-filament::button wire:click="confirmOrder" color="success">Generate & Sent Invoice</x-filament::button>
                    <x-filament::button wire:click="rejectOrder" color="danger">Reject</x-filament::button>
                @else
                    {{-- Stok tidak cukup, tombol utama nonaktif --}}
                    <x-filament::button color="success" disabled title="Stock is not sufficient to generate an invoice.">Generate & Sent Invoice</x-filament::button>
                    <x-filament::button wire:click="rejectOrder" color="danger">Reject</x-filament::button>
                    <x-filament::button wire:click="checkStock" color="gray">Check Stock</x-filament::button>
                    <x-filament::button wire:click="emailRestock" color="warning">Email Restock</x-filament::button>
                @endif
            </div>
        @else
            {{-- Jika status BUKAN 'draft', tampilkan pesan informasi --}}
            <div class="p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                <span class="font-medium">This sales order has not yet been confirmed.</span> Its current status is '{{ $record->status }}'. No further actions can be taken from this page.
            </div>
        @endif
    </div>
</x-filament-panels::page>
