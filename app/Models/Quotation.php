<?php

// app/Models/Quotation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{

    
    protected $primaryKey = 'quotation_id';
    public $incrementing = false;
    protected $fillable = [
        'quotation_id', 'outlet_code', 'quotation_date', 'status', 'total_amount', 'valid_until',
    ];

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id', 'quotation_id');
    }
        
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_code', 'outlet_code');
    }
    
    protected $attributes = [
        'status' => 'Pending',
    ];

    public function getRouteKeyName()
    {
        return 'quotation_id';
    }
    
    
}

