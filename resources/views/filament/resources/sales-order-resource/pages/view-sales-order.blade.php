
<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Sales Order Info Card --}}
        <x-filament::card>
            <h2 class="text-xl font-bold mb-4">Sales Order Detail</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="font-semibold">Sales Order ID:</span>
                    <div>{{ $this->record->sales_order_id }}</div>
                </div>
                <div>
                    <span class="font-semibold">Dealer:</span>
                    <div>{{ $this->record->outlet->dealer->dealer_name ?? '-' }}</div>
                </div>
                <div>
                    <span class="font-semibold">Outlet:</span>
                    <div>{{ $this->record->outlet->outlet_name ?? '-' }}</div>
                </div>
                <div>
                    <span class="font-semibold">Order Date:</span>
                    <div>{{ \Carbon\Carbon::parse($this->record->order_date)->format('d M Y') }}</div>
                </div>
                <div>
                    <span class="font-semibold">Status:</span>
                    <div>{{ ucfirst($this->record->status) }}</div>
                </div>
                <div>
                    <span class="font-semibold">Total Amount:</span>
                    <div>Rp {{ number_format($this->record->total_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        </x-filament::card>

        {{-- Sales Order Items --}}
        <x-filament::card>
            <h2 class="text-xl font-bold mb-4">Items</h2>

            @forelse($this->record->items as $item)
                <div class="border rounded-lg p-4 mb-3 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-semibold">Part Number:</span>
                            <div>{{ $item->part_number }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Quantity:</span>
                            <div>{{ $item->quantity }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Unit Price:</span>
                            <div>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="font-semibold">Subtotal:</span>
                            <div>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No items found.</p>
            @endforelse
        </x-filament::card>

        {{-- Transaction Information --}}
        <x-filament::card>
            <h2 class="text-xl font-bold mb-4">Transaction Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="font-semibold">Invoice ID:</span>
                    <div>{{ $this->record->transaction->invoice_id ?? '-' }}</div>
                </div>
                <div>
                    <span class="font-semibold">Invoice Date:</span>
                    <div>{{ \Carbon\Carbon::parse($this->record->transaction->invoice_date ?? '')->format('d M Y') }}</div>
                </div>
                <div>
                    <span class="font-semibold">Due Date:</span>
                    <div>{{ \Carbon\Carbon::parse($this->record->transaction->due_date ?? '')->format('d M Y') }}</div>
                </div>
                <div>
                    <span class="font-semibold">Status:</span>
                    <div>{{ ucfirst($this->record->transaction->status ?? '-') }}</div>
                </div>
                <div>
                    <span class="font-semibold">Total Amount:</span>
                    <div>Rp {{ number_format($this->record->transaction->total_amount ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </x-filament::card>

    </div>
</x-filament-panels::page>