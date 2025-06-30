<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrder extends Model
{
    protected $fillable = [
        'sales_order_id',
        'customer_id',
        'quotation_id',
        'order_date',
        'status',
        'total_amount',
        'delivery_address',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id', 'sales_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(OutletDealer::class, 'customer_id', 'outlet_code');
    }
    

    public function dealer()
    {
        return $this->hasOneThrough(
            Dealer::class,
            Outlet  ::class,
            'outlet_code', // Foreign key di tabel outlet_dealers
            'dealer_code', // Foreign key di tabel dealers
            'customer_id', // Local key di tabel sales_orders
            'dealer_code'  // Local key di tabel outlet_dealers
        );
    }
    
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'quotation_id');
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class, 'sales_order_id', 'sales_order_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Outlet::class, 'customer_id', 'outlet_code');
    }
    
        
    // Hapus relasi dealer() di SalesOrder, gunakan accessor berikut:
    public function getDealerNameAttribute()
    {
        return $this->outlet?->dealer?->dealer_name;
    }

        public function transaction()
    {
        return $this->hasOne(Transaction::class, 'sales_order_id', 'sales_order_id');
    }

}

