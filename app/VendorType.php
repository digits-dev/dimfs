<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorType extends Model
{
    protected $table = 'vendor_types';

    public function scopeGetCodeById($query, $id){
        return $query->where('id',$id)->value('vendor_type_code');
    }

    public function scopeActive($query){
        return $query->where('status','ACTIVE');
    }
}
