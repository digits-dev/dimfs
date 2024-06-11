<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandGroup extends Model
{
    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
