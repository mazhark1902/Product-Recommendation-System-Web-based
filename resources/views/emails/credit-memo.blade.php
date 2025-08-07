<!DOCTYPE html>
<html>
<head>
    <title>Credit Memo Notification</title>
</head>
<body>
    <h2>Credit Memo Notification</h2>

    <p>Dear Dealer (ID: {{ $creditMemo->customer_id }}),</p>

    <p>You currently have a credit memo with the following details:</p>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Credit Memo ID</th>
                <th>Return ID</th>
                <th>Amount</th>
                <th>Issued Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $creditMemo->credit_memos_id }}</td>
                <td>{{ $creditMemo->return_id }}</td>
                <td>Rp{{ number_format($creditMemo->amount, 2, ',', '.') }}</td>
                <td>{{ $creditMemo->issued_date }}</td>
                <td>{{ $creditMemo->status }}</td>
            </tr>
        </tbody>
    </table>

    <p>This credit can be used to reduce your future payments.</p>

    <p>Thank you.</p>
</body>
</html>
