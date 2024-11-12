<?php

namespace App;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Database\Eloquent\Model;

class AccountingItem extends Model
{
    protected $fillable = [
        'digits_code',
        'item_description',
        'current_srp',
        'status'
    ];
    public static function boot(){
        parent::boot();
        static::creating(function($model) {
            $ref = Order::orderBy('created_at','DESC')->max('id')+1;
            $model->created_by = CRUDBooster::myId();
            $model->reference = str_pad($ref, 8, "0", STR_PAD_LEFT);
        });

        static::updating(function($model) {
            $model->updated_by = CRUDBooster::myId();
        });
    }
}
