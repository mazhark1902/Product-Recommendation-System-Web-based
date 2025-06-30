<x-filament::page>
    {{-- CARD INFORMASI --}}
    <x-filament::card>
        <h2 class="text-xl font-bold mb-4">Sales Order Overview</h2>
        <p><strong>Sales Order ID:</strong> {{ $record->sales_order_id }}</p>
        <p><strong>Status:</strong> {{ $record->status }}</p>
        <p><strong>Delivery Address:</strong> {{ $record->delivery_address }}</p>
        <p><strong>Subtotal:</strong> Rp {{ number_format(collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']), 0, ',', '.') }}</p>
        <p>
            <strong>Status Stock:</strong> 
            @if ($allAvailable)
                <span class="text-green-600" style="color: #16a34a;">Available</span>
            @else
                <span class="text-red-600" style="color: #dc2626;">Empty</span>
            @endif
        </p>
        <hr class="my-2">
        <h3 class="font-semibold">Items:</h3>
        <ul class="list-disc ml-5">
        @foreach ($items as $item)
            @php
                $inventory = \App\Models\Inventory::where('product_id', $item['part_number'])->first();
                $availableQty = ($inventory->quantity_available ?? 0) - ($inventory->quantity_reserved ?? 0);
            @endphp
            <li>
                {{ $item['part_number'] }} - {{ $item['quantity'] }} pcs 
            </li>
        @endforeach
        </ul>

    </x-filament::card>

    {{-- TOMBOL AKSI --}}
    <div class="mt-4 flex gap-2">
        @if ($allAvailable)
            <x-filament::button wire:click="confirmOrder" color="success">Confirm</x-filament::button>
            <x-filament::button wire:click="rejectOrder" color="danger">Reject</x-filament::button>
        @else
            <x-filament::button color="success" disabled>Confirm</x-filament::button>
            <x-filament::button wire:click="rejectOrder" color="danger">Reject</x-filament::button>
            <x-filament::button wire:click="checkStock" color="gray">Check Stock</x-filament::button>
            <x-filament::button color="warning">Email Restock</x-filament::button>
        @endif
    </div>

    {{-- POPUP AUTOMATIS --}}
    <div
        x-data="{ showModal: true }"
        x-init="setTimeout(() => showModal = false, 5000)"
        x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] text-center">
            @if ($allAvailable)
                <h2 class="text-lg font-semibold text-green-600">Semua barang tersedia</h2>
                <p class="text-sm mt-2">Anda dapat melanjutkan konfirmasi pengiriman.</p>
            @else
                <h2 class="text-lg font-semibold text-red-600">Stok tidak mencukupi</h2>
                <p class="text-sm mt-2">Beberapa item tidak tersedia atau telah terreservasi.</p>
                <ul class="mt-3 text-left text-sm text-gray-700">
                    @foreach ($notAvailableItems as $na)
                        <li>
                            - {{ $na['part_number'] }}: butuh {{ $na['required'] }} pcs, tersedia {{ $na['available'] }} pcs
                        </li>
                    @endforeach
                </ul>
            @endif
            <br>
            <button @click="showModal = false" class="mt-4 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Tutup
            </button>
        </div>
    </div>
</x-filament::page>
