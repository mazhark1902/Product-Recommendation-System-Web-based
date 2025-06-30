<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


}