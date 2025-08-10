<h1>Quotation {{ $quotation->quotation_id }}</h1>
<p>Dealer: {{ $quotation->dealer->name ?? '-' }}</p>
<table border="1" width="100%">
    <tr>
        <th>Part Number</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
    </tr>
    @foreach($items as $item)
        <tr>
            <td>{{ $item->sub_part_number }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td>{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
    @endforeach
</table>
<p>Total: {{ number_format($quotation->total_amount, 0, ',', '.') }}</p>
