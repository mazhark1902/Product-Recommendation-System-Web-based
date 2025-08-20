<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'warehouse_id', // <-- TAMBAHKAN INI
        'quantity_available',
        'minimum_stock',
        'quantity_reserved',
        'quantity_damaged',
    ];

    public function subPart(): BelongsTo
    {
        return $this->belongsTo(SubPart::class, 'product_id', 'sub_part_number');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}