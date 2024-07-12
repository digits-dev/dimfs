<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SegmentationLegend extends Model
{
    protected $table = 'segmentation_legends';
    protected $guarded = [];
    public function scopeActive($query){
        return $query->where('status','ACTIVE')
            ->orderBy('segment_legend_description','ASC');
    }
}
