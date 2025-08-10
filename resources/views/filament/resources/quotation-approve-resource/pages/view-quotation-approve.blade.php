<x-filament::page>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-4">Quotation Information</h2>
        <p><strong>ID:</strong> {{ $record->quotation_id }}</p>
        <p><strong>Outlet Code:</strong> {{ $record->outlet_code }}</p>
        <p><strong>Date:</strong> {{ $record->quotation_date }}</p>
        <p><strong>Status:</strong> {{ $record->status }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($record->total_amount, 2, ',', '.') }}</p>
    </x-filament::card>

    <x-filament::card class="mt-6">
        <h2 class="text-lg font-bold mb-4">Quotation Items</h2>
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th>Sub Part</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr class="border-t">
                        <td>{{ $item->sub_part_number }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::card>

<script>
Livewire.on('force-download', event => {
    window.open(event.url, '_blank');
});
</script>

</x-filament::page>