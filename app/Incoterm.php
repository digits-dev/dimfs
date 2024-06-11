<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Incoterm extends Model
{
    protected $table = 'incoterms';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
