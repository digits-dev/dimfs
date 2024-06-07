<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $table = 'sizes';

    public function scopeGetSizeCode($query, $id) : string {
        return $query->where('id',$id)->value('size_code');
    }
}
