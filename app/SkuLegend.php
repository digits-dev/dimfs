<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SkuLegend extends Model
{
    protected $table = 'sku_legends';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
