<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        h2 { color: #0b5394; }
        .details { margin: 20px 0; }
        .details table { width: 100%; border-collapse: collapse; }
        .details th, .details td { border: 1px solid #ddd; padding: 8px; }
        .details th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Payment Receipt</h2>
    <p>Dear {{ $dealerName }},</p>
    <p>We have successfully received your payment. Please find the payment details below:</p>

    <div class="details">
        <table>
            <tr>
                <th>Invoice ID</th>
                <td>{{ $invoiceId }}</td>
            </tr>
            <tr>
                <th>Payment Date</th>
                <td>{{ $paymentDate }}</td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ $paymentMethod }}</td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td>Rp {{ number_format($amountPaid, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <p>The receipt is attached to this email for your records.</p>
    <p>Thank you for your business.</p>
    <br>
    <p>Best regards,<br>
    <strong>Finance Department</strong><br>
    PT. Nama Perusahaan</p>
</body>
</html>
