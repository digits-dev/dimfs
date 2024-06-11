<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    public function scopeGetCodeById($query, $id){
        return $query->where('id',$id)->value('category_code');
    }

    public function itemMasters() {
        return $this->hasMany(ItemMaster::class, 'categories_id');
    }

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
