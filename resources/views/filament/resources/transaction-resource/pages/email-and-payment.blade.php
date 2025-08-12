
<x-filament::page>
    
    <div class="space-y-6">
        <h2 class="text-xl font-bold">Email & Payment for Invoice: {{ $record->invoice_id }}</h2>

        <div class="border p-4 rounded-md shadow">
            <h3 class="font-semibold mb-2">Detail Pembayaran</h3>
            <table class="table-auto w-full text-left text-sm">
                <tr><th>Sales Order</th><td>{{ $record->sales_order_id }}</td></tr>
                <tr><th>Dealer Email</th><td>{{ $record->salesOrder->dealer->email ?? '-' }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($record->status) }}</td></tr>
                <tr><th>Total</th><td>Rp{{ number_format($record->total_amount) }}</td></tr>
                <tr><th>Proof</th><td>
                    @if ($record->proof)
                        <a href="{{ Storage::url($record->proof) }}" target="_blank" class="text-blue-500 underline">Lihat Bukti</a>
                    @else
                        <span class="text-gray-500 italic">Not Available</span>
                    @endif
                </td></tr>
            </table>
        </div>

        {{-- Form Upload --}}
        <div class="mt-6">
@if(session('success'))
    <script>
        Swal.fire('Berhasil', '{{ session('success') }}', 'success');
    </script>
@endif

<form action="{{ route('proof.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <input type="hidden" name="transaction_id" value="{{ $record->id }}">

    <label class="block font-medium" for="proof">Upload Proof Payment</label>
    <input type="file" name="proof" accept="image/png,image/jpeg" class="block w-full border p-2 rounded" required>

<x-filament::button type="submit" color="primary">
    Save Proof
</x-filament::button>
</form>

        </div>
    </div>

    {{-- SweetAlert --}}
    <script>
        window.addEventListener('swal:success', event => {
            Swal.fire({
                title: event.detail.title,
                text: event.detail.text,
                icon: 'success',
                confirmButtonText: 'OK',
            });
        });

        window.addEventListener('swal:error', event => {
            Swal.fire({
                title: event.detail.title,
                text: event.detail.text,
                icon: 'error',
                confirmButtonText: 'OK',
            });
        });
    </script>
    <div 
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:show-toast.window="
        show = true;
        message = $event.detail.message;
        type = $event.detail.type;
        setTimeout(() => show = false, 3000);
    "
    x-show="show"
    x-transition
    class="fixed bottom-5 right-5 px-4 py-2 rounded shadow text-white"
    :class="{
        'bg-green-500': type === 'success',
        'bg-red-500': type === 'error',
    }"
    style="display: none;"
>
    <span x-text="message"></span>
</div>

</x-filament::page>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

