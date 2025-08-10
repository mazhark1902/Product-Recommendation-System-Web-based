<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';

    protected $fillable = [
        'inventory_movement_id',
        'product_id',
        'movement_type',
        'quantity',
        'movement_date',
        'reference_type',
        'reference_id',
        'notes',
        'batch_number',
    ];

    /**
     * Menambahkan relasi ke model SubPart.
     * Ini akan memungkinkan kita untuk menampilkan nama produk.
     */
    public function subPart(): BelongsTo
    {
        return $this->belongsTo(SubPart::class, 'product_id', 'sub_part_number');
    }
}