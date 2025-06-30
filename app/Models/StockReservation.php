<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    protected $table = 'stock_reservations';

    protected $fillable = [
        'part_number',
        'sales_order_id',
        'reserved_quantity',
        'reservation_date',
        'status',
    ];
}
