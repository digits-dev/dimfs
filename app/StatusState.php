<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusState extends Model
{
    protected $table = 'status_states';

    public function scopeGetState($query, $status) {
        return $query->where('status_state', $status)->value('id');
    }
}
