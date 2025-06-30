{{-- filepath: c:\xampp\htdocs\Product-Recommendation-System-Web-based\resources\views\emails\reminder.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>Reminder Dealer</title>
</head>
<body>
    <h1>Reminder for Payment</h1>
    <p>Dear {{ $transaction->salesOrder->outlet->outlet_name }},</p>
    <p>Please find below the details of the transaction:</p>

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
                <td>Rp{{ number_format($transaction->total_amount, 2) }}</td>
                <td>{{ ucfirst($transaction->status) }}</td>
            </tr>
        </tbody>
    </table>

    <p>Please reply to this email with the payment proof.</p>
</body>
</html>