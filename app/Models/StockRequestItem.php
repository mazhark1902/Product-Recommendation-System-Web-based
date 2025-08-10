<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockRequestItem extends Model
{
    use HasFactory;

    /**
     * Memberitahu Laravel untuk TIDAK mengelola kolom created_at dan updated_at.
     * Ini akan memperbaiki error "Unknown column 'updated_at'".
     */
    public $timestamps = false;

    public $incrementing = true; 

    protected $fillable = [
        'stock_request_id',
        'sub_part_number',
        'quantity_requested',
        'quantity_received',
    ];

    public function stockRequest(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class);
    }

    public function subPart(): BelongsTo
    {
        return $this->belongsTo(SubPart::class, 'sub_part_number', 'sub_part_number');
    }
}