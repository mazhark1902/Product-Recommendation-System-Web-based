<p>Kepada Yth, PIC Inventory</p>

<p>Bersama email ini kami informasikan bahwa terdapat kebutuhan restock sub part untuk memenuhi Sales Order berikut:</p>

<ul>
    <li><strong>Nomor Dokumen:</strong> {{ $restockId }}</li>
    <li><strong>Sales Order ID:</strong> {{ $salesOrder->sales_order_id }}</li>
    <li><strong>Customer:</strong> {{ $salesOrder->dealer->name ?? '-' }}</li>
    <li><strong>Alamat Pengiriman:</strong> {{ $salesOrder->delivery_address }}</li>
</ul>

<p>Detail kebutuhan restock dapat dilihat pada dokumen PDF terlampir.</p>

<p>Mohon segera dilakukan pengecekan dan tindakan lebih lanjut agar pengiriman dapat berjalan sesuai jadwal.</p>

<p>Terima kasih atas perhatian dan kerja samanya.</p>

<p>Hormat kami,<br>
<b>PT. Nama Perusahaan</b></p>
