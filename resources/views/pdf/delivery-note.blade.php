<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note {{ $deliveryOrder->delivery_order_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .container { max-width: 800px; margin: auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 0; }
        .details { margin-bottom: 30px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .footer { margin-top: 40px; }
        .signature-area { width: 30%; float: left; text-align: center; margin-left: 20px; }
        .signature-area p { margin-top: 60px; border-top: 1px solid #000; padding-top: 5px; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DELIVERY NOTE</h1>
        </div>

        <div class="details clearfix">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>To:</strong><br>
                        {{ $deliveryOrder->salesOrder->outlet->dealer->dealer_name ?? 'N/A' }}<br>
                        {{ $deliveryOrder->salesOrder->outlet->outlet_name ?? 'N/A' }}<br>
                        {{ $deliveryOrder->salesOrder->delivery_address ?? '' }}
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: right;">
                        <strong>Delivery Note No:</strong> {{ $deliveryOrder->delivery_order_id }}<br>
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($deliveryOrder->delivery_date)->format('d M Y') }}<br>
                        <strong>Sales Order No:</strong> {{ $deliveryOrder->sales_order_id }}
                    </td>
                </tr>
            </table>
        </div>

        <p>Dear Sir/Madam,<br>We are pleased to send you the following items:</p>

        <table class="items-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->part_number }}</td>
                    <td>{{ $item->part->sub_part_name ?? 'Part Name Not Found' }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer clearfix">
            <div class="signature-area" style="float: left;">
                <p>Prepared By</p>
            </div>
            <div class="signature-area" style="float: right;">
                <p>Received By</p>
            </div>
        </div>
    </div>
</body>
</html>
