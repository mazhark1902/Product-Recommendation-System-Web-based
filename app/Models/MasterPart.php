<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterPart extends Model
{
    use HasFactory;

    protected $table = 'master_part';

    protected $primaryKey = 'part_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'part_number',
        'part_name',
        'part_price',
    ];

    public function subParts()
    {
        return $this->hasMany(SubPart::class, 'part_number', 'part_number');
    }
}
