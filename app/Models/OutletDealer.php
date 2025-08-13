<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutletDealer extends Model
{
    protected $table = 'outlet_dealers'; // Nama tabel di database
    protected $primaryKey = 'outlet_code';
    public $incrementing = false; // if not auto-increment
    protected $keyType = 'string';
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
        return $this->belongsTo(Dealer::class, 'dealer_code', 'dealer_code');
    }
    // protected function getTableQuery(): Builder
    // {
    // return $this->Dealer->outlets()->getQuery()->orderBy('outlet_code');
    // }

}