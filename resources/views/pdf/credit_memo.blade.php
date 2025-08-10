<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Credit Memo - {{ $creditMemo->credit_memos_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #004085;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-info h2 {
            margin: 0;
            font-size: 20px;
        }
        .company-contact {
            font-size: 10px;
            color: #ddd;
        }
        .content {
            padding: 20px;
        }
        h3 {
            color: #004085;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 40px;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Credit Memo</h1>
    </div>

    <div class="content">
        <div class="company-info">
            <h2>PT Perusahaan Anda</h2>
            <div class="company-contact">
                Jl. Contoh Alamat No. 123, Jakarta, Indonesia<br>
                Phone: +62 21 1234 5678 | Email: info@perusahaananda.com | Website: www.perusahaananda.com
            </div>
        </div>

        <h3>Credit Memo Details</h3>
        <table>
            <tr>
                <th>Credit Memo ID</th>
                <td>{{ $creditMemo->credit_memos_id }}</td>
                <th>Issued Date</th>
                <td>{{ \Carbon\Carbon::parse($creditMemo->issued_date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Return ID</th>
                <td>{{ $creditMemo->return_id }}</td>
                <th>Status</th>
                <td>{{ ucfirst(strtolower($creditMemo->status)) }}</td>
            </tr>
            <tr>
                <th>Amount (IDR)</th>
                <td colspan="3">Rp {{ number_format($creditMemo->amount, 2, ',', '.') }}</td>
            </tr>
        </table>

        <h3>Dealer Information</h3>
        <table>
            <tr>
                <th>Dealer ID</th>
                <td>{{ $creditMemo->customer_id }}</td>
            </tr>
            <tr>
                <th>Dealer Name</th>
                <td>{{ optional($creditMemo->dealer)->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Dealer Email</th>
                <td>{{ optional($creditMemo->dealer)->email ?? '-' }}</td>
            </tr>
            <tr>
                <th>Dealer Address</th>
                <td>{{ optional($creditMemo->dealer)->address ?? '-' }}</td>
            </tr>
        </table>

        <p>
            This credit memo represents a credit balance available to your account which can be used
            to offset future invoices or payments with PT Perusahaan Anda.
        </p>

        <p>
            If you have any questions regarding this credit memo, please contact our support team at
            <a href="mailto:info@perusahaananda.com">info@perusahaananda.com</a> or call +62 21 1234 5678.
        </p>

        <p>
            Thank you for your continued partnership.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} PT Perusahaan Anda. All rights reserved.
        </div>
    </div>
</body>
</html>
