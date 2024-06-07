<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryType extends Model
{
    protected $table = 'inventory_types';

    public function scopeGetType($query, $type){
        return $query->where('inventory_type_description',$type)->value('id');
    }

    public function scopeGetCodeById($query, $id){
        return $query->where('id',$id)->value('inventory_type_code');
    }
}
