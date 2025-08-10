<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrderInventory;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Import facade PDF

class DeliveryNoteController extends Controller
{
    public function print(DeliveryOrderInventory $record)
    {
        // Eager load relasi untuk menghindari query N+1 di dalam view
        $record->load(['items.part', 'salesOrder.outlet.dealer']);

        // Data yang akan dikirim ke view PDF
        $data = [
            'deliveryOrder' => $record
        ];

        // Buat PDF
        $pdf = Pdf::loadView('pdf.delivery-note', $data);

        // Tampilkan PDF di browser
        return $pdf->stream('delivery-note-'.$record->delivery_order_id.'.pdf');
    }
}