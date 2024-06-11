<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    protected $table = 'uoms';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
