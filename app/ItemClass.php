<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemClass extends Model
{
    protected $table = 'classes';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
