<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarginCategory extends Model
{
    protected $table = 'margin_categories';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
