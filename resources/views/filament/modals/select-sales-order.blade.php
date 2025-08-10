{{-- resources/views/filament/modals/select-sales-order.blade.php --}}
<div class="p-4">
    <h3 class="text-lg font-semibold mb-3">Pilih Sales Order</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 text-left">Sales Order ID</th>
                    <th class="border px-2 py-1 text-left">Order Date</th>
                    <th class="border px-2 py-1 text-left">Customer / Outlet</th>
                    <th class="border px-2 py-1 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesOrders as $so)
                    <tr class="hover:bg-gray-50">
                        <td class="border px-2 py-1 align-top">{{ $so->sales_order_id }}</td>
                        <td class="border px-2 py-1 align-top">{{ optional($so->order_date)->format('d M Y') ?? '-' }}</td>
                        <td class="border px-2 py-1 align-top">{{ $so->outlet->outlet_name ?? $so->customer_id ?? '-' }}</td>
                        <td class="border px-2 py-1 text-center">
                            <button
                                type="button"
                                class="inline-block px-3 py-1 rounded bg-primary-600 text-white hover:bg-primary-700"
                                onclick="selectSalesOrder('{{ $so->sales_order_id }}')"
                            >
                                Select
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        function selectSalesOrder(id) {
            // set value ke input form (gunakan name attribute)
            const input = document.querySelector('input[name="sales_order_id"], input[id$="sales_order_id"]');
            if (input) {
                input.value = id;
                // trigger event supaya Livewire/Filament tahu ada perubahan
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // coba tutup modal Filament:
            // 1) cari tombol modal yang memiliki teks 'Close' / 'Tutup' atau atribut aria-label
            const closeBtnCandidates = Array.from(document.querySelectorAll('button'));
            const closeBtn = closeBtnCandidates.find(b => {
                const t = (b.textContent || '').trim().toLowerCase();
                const a = (b.getAttribute('aria-label') || '').toLowerCase();
                return t === 'close' || t === 'tutup' || a === 'close' || a === 'tutup' || b.dataset.action === 'close';
            });
            if (closeBtn) {
                closeBtn.click();
                return;
            }

            // fallback: dispatch event untuk memerintahkan Filament menutup modal (beberapa versi Filament mendengarkan ini)
            window.dispatchEvent(new CustomEvent('close-modal'));
        }
    </script>
</div>
