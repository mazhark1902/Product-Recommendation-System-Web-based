<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    protected $fillable = [
        'dealer_code',
        'dealer_name',
        'province',
        'email'
        // tambahkan field lain jika perlu
    ];
}
