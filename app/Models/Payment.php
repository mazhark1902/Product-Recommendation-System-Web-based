<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{   
    protected $table = 'payments'; // Nama tabel di database
    protected $fillable = [
        'payment_id',
        'invoice_id',
        'amount_paid',
        'payment_date',
        'payment_method',
    ];
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'invoice_id', 'invoice_id');
    }
}
