<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .header { border-bottom: 2px solid #004085; padding-bottom: 10px; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; color: #004085; }
        .content { line-height: 1.6; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PT. XYZ</div>
        <div>Jl. Contoh Alamat No. 123, Jakarta</div>
        <div>Email: info@perusahaan.com | Telp: (021) 123456</div>
    </div>

    <div class="content">
        <p>Kepada Yth,</p>
        <p><strong>{{ $quotation->outlet->outlet_name ?? 'Dealer' }}</strong></p>
        <p>
            Bersama email ini kami sampaikan bahwa Quotation dengan ID 
            <strong>{{ $quotation->quotation_id }}</strong> sedang dalam proses review.
        </p>

        <p>Berikut ringkasan informasi:</p>
        <ul>
            <li><strong>Tanggal:</strong> {{ now()->format('d/m/Y') }}</li>
            <li><strong>Dealer:</strong> {{ $quotation->outlet->outlet_name ?? '-' }}</li>
            <li><strong>Alamat:</strong> {{ $quotation->outlet->address ?? '-' }}</li>
            <li><strong>Total Quotation:</strong> Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}</li>
        </ul>

        <p>Silakan melihat detail lengkap pada dokumen PDF terlampir.</p>

        <p>Terima kasih atas perhatian dan kerja samanya.</p>
    </div>

    <div class="footer">
        Hormat kami,<br>
        <strong>PT. XYZ/strong><br>
        <em>Dokumen ini bersifat rahasia dan hanya diperuntukkan bagi pihak yang dituju.</em>
    </div>
</body>
</html>
