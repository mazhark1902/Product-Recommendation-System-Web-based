<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $table = 'import_logs';

    protected $fillable = [
        'file_name',
        'file_checksum',
        'status',
        'total_rows',
        'processed_rows',
        'new_records',
        'updated_records',
        'error_message',
        'user_id',
    ];
}

