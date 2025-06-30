<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';

    protected $fillable = [
        'sales_order_id',
        'part_number',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'sales_order_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(SubPart::class, 'part_number', 'sub_part_number');
    }
}
