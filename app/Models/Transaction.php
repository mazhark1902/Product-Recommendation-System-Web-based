<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
       protected $table = 'transaction';

    protected $fillable = [
        'invoice_id',
        'sales_order_id',
        'invoice_date',
        'due_date',
        'status',
        'total_amount',
        'status_reminder',
        'proof',
        
    ];
    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'sales_order_id');
    }
}
