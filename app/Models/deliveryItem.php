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
}