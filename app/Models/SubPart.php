<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubPart extends Model
{
    use HasFactory;

    protected $table = 'sub_parts';

    protected $primaryKey = 'sub_part_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sub_part_number',
        'sub_part_name',
        'part_number',
        'price',
        'cost', // <-- TAMBAHKAN INI
    ];

    public function masterPart()
    {
        return $this->belongsTo(MasterPart::class, 'part_number', 'part_number');
    }
}
