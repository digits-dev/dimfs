<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Counter extends Model
{
    protected $table = 'counters';

    public function scopeIncrementColumn($query, $column){
        return $query->where('cms_moduls_id',self::getItemModuleId())->increment($column);
    }

    public function scopeGetCode($query, $column){
        return $query->where('cms_moduls_id',self::getItemModuleId())->value($column);
    }

    public function getItemModuleId(){
        return DB::table('cms_moduls')->where('table_name','item_masters')->value('id');
    }
}
