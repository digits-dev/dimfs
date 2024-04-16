<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GachaItemEditHistory extends Model
{
    protected $table = 'gacha_item_edit_histories';

    public function scopeDetails($query, $id)
    {
        return $query->join('cms_users', 'gacha_item_edit_histories.approved_by_acct', '=', 'cms_users.id')
        ->select('gacha_item_edit_histories.*', 'cms_users.name')
        ->where('gacha_item_edit_histories.id', $id)
        ->first();
    }
}