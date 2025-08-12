<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Notification - {{ $transaction->invoice_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f8fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 650px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ddd;
            overflow: hidden;
        }
        .header {
            background-color: #004085;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
        }
        .body {
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        th {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total {
            font-weight: bold;
            text-align: right;
            font-size: 16px;
            padding-top: 10px;
        }
        .footer {
            background-color: #f0f0f0;
            color: #555;
            padding: 15px;
            text-align: center;
            font-size: 12px;
        }
        .highlight {
            color: #004085;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h2>Invoice Notification</h2>
            <p>Invoice ID: {{ $transaction->invoice_id }}</p>
        </div>

        <!-- BODY -->
        <div class="body">
            <p>Dear <span class="highlight">{{ $transaction->salesOrder->outlet->outlet_name ?? 'Valued outlet' }}</span>,</p>
            <p>We are pleased to inform you that your invoice has been generated with the following details:</p>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tableData as $row)
                        <tr>
                            <td>{{ $row['product'] }}</td>
                            <td>{{ $row['delivered_qty'] }}</td>
                            <td>Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">
                Total: Rp {{ number_format($tableTotal, 0, ',', '.') }}
            </div>

            @if($oldCreditMemo > 0)
    <p>Old Credit Memo: Rp {{ number_format($oldCreditMemo, 0, ',', '.') }}</p>
    <p>Credit Memo Used: Rp {{ number_format($creditMemoUsed, 0, ',', '.') }}</p>
    <p>Current Credit Memo: Rp {{ number_format($currentCreditMemo, 0, ',', '.') }}</p>
    <h3>Payable Amount: Rp {{ number_format($payableAmount, 0, ',', '.') }}</h3>
@endif

            <p>Please find the attached PDF for the full invoice details.</p>
            <p>If you have any questions, contact our Finance Department at <a href="mailto:support@company.com">support@company.com</a>.</p>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            &copy; {{ date('Y') }} Your Company Name. All Rights Reserved.<br>
            This is an automated message, please do not reply directly.
        </div>
    </div>
</body>
</html>
