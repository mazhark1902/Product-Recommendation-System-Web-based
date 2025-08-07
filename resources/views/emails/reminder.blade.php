<!DOCTYPE html>
<html>
<head>
    <title>Reminder Dealer</title>
</head>
<body>
    <h1>Reminder for Payment</h1>
    <p>Dear {{ $transaction->salesOrder->outlet->outlet_name }},</p>
    <p>Please find below the details of the transaction:</p>

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


    <table border="1" cellpadding="10">
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
                <td>
                Rp{{ number_format($adjustedTotal, 2) }}
                @if ($credit > 0)
                    <br><small>Credit: Rp{{ number_format($credit, 2) }}</small>
                    @if ($isCoveredByCredit)
                        <br><small><strong>Status: Lunas (covered by credit)</strong></small>
                    @endif
                @endif
                </td>

                <td>{{ ucfirst($transaction->status) }}</td>
            </tr>
        </tbody>
    </table>

    <p>Please reply to this email with the payment proof.</p>
</body>
</html>
