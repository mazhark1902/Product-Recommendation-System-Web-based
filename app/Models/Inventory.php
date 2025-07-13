<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // --- TAMBAHKAN ATAU GANTI DENGAN BLOK DI BAWAH INI ---
    protected $fillable = [
        'product_id',
        'quantity_available',
        'minimum_stock',
        'quantity_reserved',
        'quantity_damaged',
        'location',
    ];
    // -------------------------------------------------

    /**
     * Mendapatkan data SubPart yang terkait dengan record inventaris ini.
     */
    public function subPart(): BelongsTo
    {
        return $this->belongsTo(SubPart::class, 'product_id', 'sub_part_number');
    }
}