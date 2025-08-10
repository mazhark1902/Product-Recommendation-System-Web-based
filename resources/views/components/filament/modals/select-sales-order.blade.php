<div class="space-y-2">
    @foreach ($salesOrders as $order)
        <div 
            class="p-2 border rounded hover:bg-gray-100 cursor-pointer"
            onclick="window.livewire.emit('selectSalesOrderId', '{{ $order->id }}')"
        >
            {{ $order->id }} — {{ $order->customer_name }}
        </div>
    @endforeach
</div>
