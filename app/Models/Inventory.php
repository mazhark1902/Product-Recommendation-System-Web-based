<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory'; // Nama tabel yang benar

    protected $fillable = [
        'product_id',
        'location',
        'batch_number',
        'quantity_available',
        'quantity_reserved',
        'quantity_damaged',
        'minimum_stock',
        'last_updated',
    ];
}