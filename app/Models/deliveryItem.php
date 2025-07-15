<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    protected $table = 'delivery_items'; // Pastikan nama tabel sesuai dengan database

    protected $fillable = [
        'delivery_order_id',
        'part_number',
        'quantity',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id', 'delivery_order_id');
    }

    // --- TAMBAHKAN FUNGSI RELASI INI ---
    /**
     * Mendapatkan data SubPart yang terkait dengan item pengiriman ini.
     */
    public function part()
    {
        return $this->belongsTo(SubPart::class, 'part_number', 'sub_part_number');
    }
    // -------------------------------------
}