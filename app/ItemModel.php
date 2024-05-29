<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemModel extends Model
{
    use SoftDeletes;
    protected $table = 'item_models';

    protected $fillable = ['model_description','created_by','created_at'];
}
