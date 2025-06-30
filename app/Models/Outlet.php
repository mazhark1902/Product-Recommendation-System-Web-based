<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $table = 'outlet_dealers';
    protected $primaryKey = 'outlet_code';
    public $incrementing = false;

    protected $fillable = [
        'outlet_code',
        'outlet_name',
        'dealer_code',
        'email',
        'phone',
        'address',
        'credit_limit',
    ];

    public function dealer()
{
    return $this->belongsTo(\App\Models\Dealer::class, 'dealer_code', 'dealer_code');
}
}
