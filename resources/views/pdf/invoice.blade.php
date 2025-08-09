<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Invoice</h2>
    <p><strong>Invoice ID:</strong> {{ $transaction->invoice_id }}</p>
    <p><strong>Sales Order ID:</strong> {{ $transaction->sales_order_id }}</p>
    <p><strong>Date:</strong> {{ $transaction->invoice_date }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tableData as $row)
                <tr>
                    <td>{{ $row['product'] }}</td>
                    <td class="text-right">{{ $row['delivered_qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="text-right">Total: Rp {{ number_format($tableTotal, 0, ',', '.') }}</h3>
</body>
</html>
