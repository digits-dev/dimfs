<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Segmentation extends Model
{
    protected $table = 'segmentations';
    protected $guarded = [];
    public function scopeActive($query){
        return $query->where('status','ACTIVE')
            ->orderBy('segmentation_description','ASC');
    }
}
