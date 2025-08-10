<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderSales extends Model
{
    protected $table = 'delivery_orders'; // Pastikan nama tabel sesuai dengan database

    protected $fillable = [
        'delivery_order_id',
        'sales_order_id',
        'delivery_date',
        'status',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_order_id', 'delivery_order_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'sales_order_id');
    }
}
