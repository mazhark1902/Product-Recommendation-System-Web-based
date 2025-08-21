<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'master_payment_methods';

    protected $fillable = [
        'name',
    ];
}
