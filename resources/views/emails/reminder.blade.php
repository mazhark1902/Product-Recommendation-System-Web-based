<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Reminder - {{ $transaction->invoice_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #ddd;
            max-width: 700px;
            margin: auto;
        }
        .header {
            border-bottom: 2px solid #004085;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #004085;
            font-size: 22px;
        }
        .greeting {
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background: #f0f0f0;
            text-align: left;
        }
        .total {
            font-weight: bold;
            color: #d9534f;
        }
        .footer {
            margin-top: 25px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Payment Reminder</h1>
        <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
    </div>

    <p class="greeting">Dear {{ $transaction->salesOrder->outlet->outlet_name ?? 'Valued Customer' }},</p>

    <p>
        This is a friendly reminder regarding your outstanding payment.  
        Please find the transaction details below:
    </p>

    @php
        $credit = $creditAmount ?? 0;
        $total = $transaction->total_amount;

        if ($credit >= $total) {
            $adjustedTotal = 0;
            $isCoveredByCredit = true;
        } else {
            $adjustedTotal = $total - $credit;
            $isCoveredByCredit = false;
        }
    @endphp

    <table>
        <thead>
        <tr>
            <th>Invoice ID</th>
            <th>Sales Order ID</th>
            <th>Total Amount</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $transaction->invoice_id }}</td>
            <td>{{ $transaction->sales_order_id }}</td>
            @php
                $total = $transaction->total_amount;
                $credit = min($total, $creditAmount ?? 0);
                $payable = max($total - ($creditAmount ?? 0), 0);
            @endphp
            <td>
                Rp {{ number_format($total, 0, ',', '.') }}
                @if ($credit > 0)
                    <br><small>Credit Used: Rp {{ number_format($credit, 0, ',', '.') }}</small>
                    @if ($payable == 0)
                        <br><strong style="color:green;">Paid in full using Credit Memo</strong>
                    @else
                        <br><small>Payable Amount: Rp {{ number_format($payable, 0, ',', '.') }}</small>
                    @endif
                @endif
            </td>

            <td>{{ ucfirst($transaction->status) }}</td>
        </tr>
        </tbody>
    </table>

    <p>
        Please make the payment at your earliest convenience or reply to this email with your payment proof.  
        The invoice document is attached for your reference.
    </p>

    <p>Thank you for your cooperation.</p>

    <div class="footer">
        &copy; {{ date('Y') }} PT XYZ. All rights reserved.
    </div>
</div>
</body>
</html>
