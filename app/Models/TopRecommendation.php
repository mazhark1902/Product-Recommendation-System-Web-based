<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopRecommendation extends Model
{
    protected $table = 'top_recommendations_by_category';
    public $timestamps = false;
}
