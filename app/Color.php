<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'colors';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
