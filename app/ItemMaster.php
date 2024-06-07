<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemMaster extends Model
{
    protected $table = 'item_masters';

    const APPROVED = 2;
    const REJECTED = 3;
    const INVALID = 4;
    const INACTIVE = 1;
    public function scopeGetEditDetails($query, $id){
        return $query->where('item_masters.id',$id)->join('brands','item_masters.brands_id','=','brands.id')
        ->join('categories','item_masters.categories_id','=','categories.id')
        ->join('model_specifics','item_masters.model_specifics_id','=','model_specifics.id')
        ->join('sizes','item_masters.sizes_id','=','sizes.id')
        ->select('brands.brand_code',
            'categories.category_code',
            'item_masters.model',
            'model_specifics.model_specific_code',
            'item_masters.size_value',
            'sizes.size_code',
            'item_masters.actual_color'
        );
    }

    public function scopeBartenderFormat($query){
        return $query->select('digits_code',
            'upc_code',
            'item_description',
            'current_srp',
            'original_srp',
            'price_change',
            'effective_date')
        ->where('approval_status',self::APPROVED);
    }

    public function scopePosFormat($query){
        return $query->leftJoin('uoms', 'item_masters.uoms_id', '=', 'uoms.id')
		->leftJoin('brands', 'item_masters.brands_id', '=', 'brands.id')
		->leftJoin('categories', 'item_masters.categories_id', '=', 'categories.id')
		->leftJoin('classes', 'item_masters.classes_id', '=', 'classes.id')
		->leftJoin('subclasses', 'item_masters.subclasses_id', '=', 'subclasses.id')
		->leftJoin('warranties', 'item_masters.warranties_id', '=', 'warranties.id')
        ->where('item_masters.approval_status', self::APPROVED)
		->where('item_masters.inventory_types_id','!=',self::INACTIVE)
		->where('item_masters.sku_statuses_id','!=',self::INVALID)
		->whereNull('item_masters.deleted_at')
        ->select(
			'item_masters.digits_code',
			'item_masters.item_description',
			'uoms.uom_code as uom_code',
			'item_masters.current_srp',
			'item_masters.upc_code',
			'item_masters.has_serial',
			'item_masters.warranty_duration',
			'warranties.warranty_description',
			'categories.category_code',
			'classes.class_code',
			'subclasses.subclass_code',
			'brands.brand_code',
			'item_masters.original_srp',
			'item_masters.dtp_rf',
			'item_masters.effective_date')
        ->orderBy('item_masters.digits_code', 'asc');
    }
}
