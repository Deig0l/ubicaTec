<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHit extends Model
{
    public $timestamps = false;

    protected $fillable = ['location_id', 'created_at'];
}
