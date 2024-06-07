<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemMasterApproval extends Model
{
    protected $table = 'item_master_approvals';

    public function scopeGetEditDetails($query, $id){
        return $query->where('item_master_approvals.id',$id)->join('brands','item_master_approvals.brands_id','=','brands.id')
            ->join('categories','item_master_approvals.categories_id','=','categories.id')
            ->join('model_specifics','item_master_approvals.model_specifics_id','=','model_specifics.id')
            ->join('sizes','item_master_approvals.sizes_id','=','sizes.id')
            ->select('brands.brand_code',
                'categories.category_code',
                'item_master_approvals.model',
                'model_specifics.model_specific_code',
                'item_master_approvals.size_value',
                'sizes.size_code',
                'item_master_approvals.actual_color'
            );
    }

    public function scopeGetPendingItems($query){
        return $query->join('brands','item_master_approvals.brands_id','=','brands.id')
            ->join('categories','item_master_approvals.categories_id','=','categories.id')
            ->join('cms_users','item_master_approvals.created_by','=','cms_users.id')
            ->select(
                'item_master_approvals.upc_code',
                'item_master_approvals.supplier_item_code',
                'item_master_approvals.item_description',
                'brands.brand_description',
                'categories.category_description',
                'cms_users.name as encoded_by',
                'item_master_approvals.created_at as creation_date'
            )->orderBy('item_master_approvals.created_at','asc');
    }
}
