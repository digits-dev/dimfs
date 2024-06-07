<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoType extends Model
{
    protected $table = 'promo_types';

    public function scopeActive($query){
        return $query->where('status','ACTIVE')
            ->orderBy('promo_type_description','ASC');
    }
}
