<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subclass extends Model
{
    protected $table = 'subclasses';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
