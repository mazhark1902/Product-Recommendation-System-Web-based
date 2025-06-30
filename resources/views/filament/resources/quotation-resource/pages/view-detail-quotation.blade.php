<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Quotation Info Card --}}
        <x-filament::card>
            <h2 class="text-xl font-bold mb-4">Quotation Detail</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="font-semibold">Quotation ID:</span>
                    <div>{{ $this->record->quotation_id }}</div>
                </div>
                <div>
                    <span class="font-semibold">Outlet:</span>
                    <div>{{ $this->record->outlet->outlet_name ?? '-' }}</div>
                </div>
                <div>
                    <span class="font-semibold">Quotation Date:</span>
                    <div>{{ \Carbon\Carbon::parse($this->record->quotation_date)->format('d M Y') }}</div>
                </div>
                <div>
                    <span class="font-semibold">Valid Until:</span>
                    <div>{{ \Carbon\Carbon::parse($this->record->valid_until)->format('d M Y') }}</div>
                </div>
                <div>
                    <span class="font-semibold">Status:</span>
                    <div>{{ $this->record->status }}</div>
                </div>
                <div>
                    <span class="font-semibold">Total Amount:</span>
                    <div>${{ number_format($this->record->total_amount, 2) }}</div>
                </div>
            </div>
        </x-filament::card>

        {{-- Quotation Items --}}
        <x-filament::card>
            <h2 class="text-xl font-bold mb-4">Items</h2>

            @forelse($this->record->items as $item)
                <div class="border rounded-lg p-4 mb-3 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-semibold">Master Part:</span>
                            <div>{{ $item->part_number }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Sub Part:</span>
                            <div>{{ $item->sub_part_number }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Quantity:</span>
                            <div>{{ $item->quantity }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Unit Price:</span>
                            <div>${{ number_format($item->unit_price, 2) }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Subtotal:</span>
                            <div>${{ number_format($item->subtotal, 2) }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No items found.</p>
            @endforelse
        </x-filament::card>

    </div>
</x-filament-panels::page>

