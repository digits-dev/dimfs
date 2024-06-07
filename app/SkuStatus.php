<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SkuStatus extends Model
{
    protected $table = 'sku_statuses';

    public function scopeStatus($query, $status) {
        return $query->where('sku_status_description', $status)->value('id');
    }
}
