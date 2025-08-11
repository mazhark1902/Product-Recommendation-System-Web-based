<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Credit Memo Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f8fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        .email-header {
            background-color: #004085;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header h2 {
            margin: 0;
            font-size: 22px;
        }
        .email-body {
            padding: 20px;
        }
        .email-body p {
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
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .email-footer {
            background-color: #f0f0f0;
            color: #555;
            padding: 15px;
            text-align: center;
            font-size: 12px;
        }
        .highlight {
            font-weight: bold;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- HEADER -->
        <div class="email-header">
            <h2>Credit Memo Notification</h2>
            <p style="margin:0; font-size: 14px;">Official Notification from Our Company</p>
        </div>

        <!-- BODY -->
        <div class="email-body">
            <p>Dear <span class="highlight">Dealer (ID: {{ $creditMemo->customer_id }})</span>,</p>

            <p>We would like to inform you that you currently have an active <strong>Credit Memo</strong> with the following details:</p>

            <table>
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
                        <td>{{ \Carbon\Carbon::parse($creditMemo->issued_date)->format('d M Y') }}</td>
                        <td>{{ ucfirst($creditMemo->status) }}</td>
                    </tr>
                </tbody>
            </table>

            <p>You may use this credit to reduce your upcoming payments. Please ensure that you keep this information for your records.</p>

            <p>If you have any questions or require further assistance, feel free to contact our <strong>Finance Department</strong> at <a href="mailto:support@company.com">support@company.com</a> or call <strong>(021) 123-4567</strong>.</p>

            <p>Thank you for your continued trust in our services.</p>
        </div>

        <!-- FOOTER -->
        <div class="email-footer">
            &copy; {{ date('Y') }} Your Company Name. All rights reserved.<br>
            This is an automated message, please do not reply directly to this email.
        </div>
    </div>
</body>
</html>
