<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseCategory extends Model
{
    protected $table = 'warehouse_categories';

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
