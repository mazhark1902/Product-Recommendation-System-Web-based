<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-height: 60px; }
        .company-info { text-align: center; font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f2f2f2; }
        .section-title { margin-top: 20px; font-weight: bold; font-size: 14px; }
        .footer { margin-top: 40px; font-size: 11px; color: #666; text-align: center; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <img src="{{ public_path('images/company-logo.png') }}" alt="Logo">
        <h2>PT. XYZ</h2>
        <div class="company-info">
            Jl. Contoh Alamat No. 123, Jakarta | Telp: (021) 123456 | Email: info@perusahaan.com
        </div>
    </div>

    {{-- Informasi Quotation --}}
    <div>
        <h3 class="section-title">Informasi Quotation</h3>
        <table>
            <tr>
                <th>ID Quotation</th>
                <td>{{ $quotation->quotation_id }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td>{{ now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Dealer</th>
                <td>{{ $quotation->outlet->outlet_name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Alamat Dealer</th>
                <td>{{ $quotation->outlet->address ?? '-' }}</td>
            </tr>
            <tr>
                <th>Total</th>
                <td>Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- Detail Item --}}
    <div>
        <h3 class="section-title">Detail Item</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Part Number</th>
                    <th>Deskripsi</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->sub_part_number }}</td>
                        <td>{{ $item->subPart->sub_part_name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Catatan --}}
    <div>
        <h3 class="section-title">Catatan</h3>
        <p>Quotation ini berlaku selama 14 hari sejak tanggal diterbitkan. Mohon melakukan konfirmasi sebelum batas waktu berakhir.</p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Dokumen ini dibuat secara otomatis dan sah tanpa tanda tangan basah.
    </div>

</body>
</html>
