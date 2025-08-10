<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SubPart Restock - {{ $restockId }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PT. Nama Perusahaan</h2>
        <p>Alamat Perusahaan | Telp: 021-xxxxxxx | Email: info@perusahaan.com</p>
        <hr>
        <h3>Dokumen SubPart Restock</h3>
    </div>

    <p><strong>Nomor Dokumen:</strong> {{ $restockId }}</p>
    <p><strong>Tanggal:</strong> {{ $createdAt->format('d/m/Y') }}</p>
    <p><strong>Sales Order ID:</strong> {{ $salesOrder->sales_order_id }}</p>
    <p><strong>Customer:</strong> {{ $salesOrder->dealer->name ?? '-' }}</p>
    <p><strong>Delivery Address:</strong> {{ $salesOrder->delivery_address }}</p>

    <table>
        <thead>
            <tr>
                <th>Part Number</th>
                <th>Part Name</th>
                <th>Qty Required</th>
                <th>Qty Available</th>
                <th>Qty Shortage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['part_number'] }}</td>
                    <td>{{ $item['part_name'] }}</td>
                    <td>{{ $item['required'] }}</td>
                    <td>{{ $item['available'] }}</td>
                    <td>{{ $item['shortage'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px;">Mohon untuk segera melakukan restock sub part sesuai dengan kebutuhan di atas agar proses pengiriman tidak terhambat.</p>
</body>
</html>
