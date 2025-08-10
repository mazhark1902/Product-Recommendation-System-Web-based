<div>
    <h2 class="text-lg font-bold mb-2">Pilih Sales Order</h2>
    <ul class="list-disc pl-5">
        @foreach (\App\Models\SalesOrder::all() as $so)
            <li class="cursor-pointer text-blue-600 hover:underline"
                onclick="window.dispatchEvent(new CustomEvent('sales-order-selected', { detail: '{{ $so->sales_order_id }}' }))">
                {{ $so->sales_order_id }}
            </li>
        @endforeach
    </ul>
</div>
