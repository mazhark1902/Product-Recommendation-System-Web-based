<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    protected $table = 'product_returns';
    public $incrementing = false;
    protected $primaryKey = 'return_id';
    protected $fillable = [
        'return_id','sales_order_id','part_number','quantity',
        'return_date','reason','condition','refund_action',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'sales_order_id');
    }

    public function part()
    {
        return $this->belongsTo(SubPart::class, 'part_number', 'sub_part_number');
    }
}