<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header {
            text-align: center;
            border-bottom: 2px solid #004085;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 { margin: 0; color: #004085; }
        .company-info, .dealer-info {
            font-size: 12px;
            margin-bottom: 15px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>Invoice ID: {{ $transaction->invoice_id }}</p>
    </div>

    <div class="company-info">
        <strong>Your Company Name</strong><br>
        Address Line 1<br>
        Address Line 2<br>
        Phone: (021) 123-4567
    </div>

    <div class="dealer-info">
        <strong>Bill To:</strong><br>
        {{ $transaction->salesOrder->dealer->name ?? '-' }}<br>
        {{ $transaction->salesOrder->dealer->address ?? '-' }}<br>
        Email: {{ $transaction->salesOrder->dealer->email ?? '-' }}
    </div>

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

    <p class="text-right total">Total: Rp {{ number_format($tableTotal, 0, ',', '.') }}</p>
</body>
</html>
