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
        .header h1 { margin: 0; color: #004085; font-size: 24px; }
        .company-info {
            font-size: 12px;
            margin-bottom: 20px;
        }
        .company-info strong { font-size: 14px; color: #004085; }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .bill-to, .invoice-details {
            width: 48%;
            font-size: 12px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background-color: #f8f9fa; color: #333; }
        .text-right { text-align: right; }
        .total-row th, .total-row td { font-weight: bold; font-size: 13px; }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>Invoice #: {{ $transaction->invoice_id }}</p>
    </div>

    <div class="company-info">
        <strong>PT. XYZ</strong><br>
        Jl. Contoh Alamat No. 123, Jakarta<br>
        Phone: (021) 123-4567 | Email: info@company.com
    </div>

    <div class="info-section">
        <div class="bill-to">
            <strong>Bill To:</strong><br>
            {{ $transaction->salesOrder->outlet->outlet_name ?? '-' }}<br>
            {{ $transaction->salesOrder->outlet->address ?? '-' }}<br>
            Email: {{ $transaction->salesOrder->dealer->email ?? '-' }}
        </div>
        <div class="invoice-details">
            <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($transaction->invoice_date)->format('d M Y') }}<br>
            <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($transaction->due_date)->format('d M Y') }}<br>
            <strong>Status:</strong> {{ ucfirst($transaction->status) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
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
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">Rp {{ number_format($tableTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @if($oldCreditMemo > 0)
    <table style="margin-top: 15px; width: 50%;">
        <tr>
            <th>Old Credit Memo</th>
            <td class="text-right">Rp {{ number_format($oldCreditMemo, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Credit Memo Used</th>
            <td class="text-right">Rp {{ number_format($creditMemoUsed, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Current Credit Memo</th>
            <td class="text-right">Rp {{ number_format($currentCreditMemo, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Payable Amount</th>
            <td class="text-right">Rp {{ number_format($payableAmount, 0, ',', '.') }}</td>
        </tr>
    </table>
@endif

    </table>

    <div class="footer">
        <p>Terima kasih atas kepercayaan Anda. Harap lakukan pembayaran sebelum tanggal jatuh tempo.</p>
        <p>Bank Transfer: BCA 123-456-7890 a.n PT.XYZ</p>
    </div>
</body>
</html>
