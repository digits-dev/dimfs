<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemIdentifier extends Model
{
    protected $table = 'item_identifiers';

    public function scopeActive($query){
        return $query->where('status','ACTIVE')
            ->orderBy('item_identifier','ASC');
    }
}
