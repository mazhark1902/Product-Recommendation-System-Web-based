<x-filament::page>
    <div class="space-y-6">
        <h2 class="text-xl font-bold">Email & Payment for Invoice: {{ $record->invoice_id }}</h2>

        <div class="border p-4 rounded-md shadow">
            <h3 class="font-semibold mb-2">Detail Pembayaran</h3>
            <table class="table-auto w-full text-left">
                <tr><th>Sales Order</th><td>{{ $record->sales_order_id }}</td></tr>
                <tr><th>Dealer Email</th><td>{{ $record->salesOrder->dealer->email ?? '-' }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($record->status) }}</td></tr>
                <tr><th>Total</th><td>Rp{{ number_format($record->total_amount) }}</td></tr>
                <tr><th>Proof</th><td>
                    @if ($record->proof)
                        <a href="{{ Storage::url($record->proof) }}" target="_blank" class="text-blue-500 underline">Lihat Bukti</a>
                    @else
                        <span class="text-gray-500 italic">Belum ada</span>
                    @endif
                </td></tr>
            </table>
        </div>

        {{-- Menampilkan form upload bukti pembayaran --}}
        {{ $this->form }}

        <x-filament::button wire:click="submit" class="mt-4">
            Save Proof
        </x-filament::button>



</x-filament::page>
