<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditMemos extends Model
{
    use HasFactory;

    protected $table = 'credit_memos';

    protected $fillable = [
        'credit_memos_id',
        'sales_order_id',
        'return_id',
        'amount',
        'issued_date',
        'due_date',
        'status',
        'customer_id',
    ];

    protected $dates = [
        'issued_at',
    ];

    // Optional: Default value untuk status jika belum diisi
    protected $attributes = [
        'status' => 'unused',
    ];

    // Relasi ke Sales Order
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

// App\Models\CreditMemos.php

public function outlet()
{
    // customer_id pada credit_memos berisi outlet_code (string)
    return $this->belongsTo(\App\Models\Outlet::class, 'customer_id', 'outlet_code');
}

// Helper accessor biar gampang dipakai di resource / email
public function getDealerNameAttribute(): ?string
{
    return optional($this->outlet->dealer)->dealer_name;
}

public function getDealerEmailAttribute(): ?string
{
    return optional($this->outlet->dealer)->email;
}

    
    // Relasi ke Product Return
    public function productReturn()
    {
        return $this->belongsTo(ProductReturn::class, 'return_id');
    }

    // Auto generate credit memo ID
    public static function generateCreditMemoId()
    {
        $prefix = 'CM-';
        $last = self::latest('id')->first();
        $number = $last ? ((int)str_replace($prefix, '', $last->credit_memo_id)) + 1 : 1;
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
    
}
