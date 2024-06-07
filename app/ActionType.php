<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionType extends Model
{
    protected $table = 'action_types';

    public function scopeGetType($query, string $type){
        return $query->where('action_type',$type)->value('id');
    }
}
