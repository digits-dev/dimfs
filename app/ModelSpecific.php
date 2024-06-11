<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelSpecific extends Model
{
    protected $table = 'model_specifics';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
