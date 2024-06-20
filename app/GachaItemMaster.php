<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GachaItemMaster extends Model
{
    protected $table = 'gacha_item_masters';
    const APPROVED = 200;
    public function scopeGenerateExport($query){
        return $query->select(
            "gacha_item_masters.digits_code",
            "gacha_item_masters.item_description",
            "gacha_item_masters.jan_no as upc_code",
            "gacha_brands.brand_description",
            "gacha_categories.category_description",
            "gacha_vendor_types.vendor_type_code",
            "gacha_inventory_types.inventory_type_description",
            "gacha_item_masters.current_srp",
            "gacha_item_masters.initial_wrr_date",
            "gacha_item_masters.status"

        )
        ->leftJoin('gacha_brands', 'gacha_item_masters.gacha_brands_id', '=', 'gacha_brands.id')
        ->leftJoin('gacha_categories', 'gacha_item_masters.gacha_categories_id', '=', 'gacha_categories.id')
        ->leftJoin('gacha_vendor_types', 'gacha_item_masters.gacha_vendor_types_id', '=', 'gacha_vendor_types.id')
        ->leftJoin('gacha_inventory_types', 'gacha_item_masters.gacha_inventory_types_id', '=', 'gacha_inventory_types.id')
        ->leftJoin('cms_users as createdby', 'gacha_item_masters.created_by', '=', 'createdby.id')
        ->leftJoin('cms_users as updatedby', 'gacha_item_masters.updated_by', '=', 'updatedby.id')
        ->leftJoin('cms_users as approvedby', 'gacha_item_masters.approved_by', '=', 'approvedby.id')
        ->where('gacha_item_masters.approval_status',self::APPROVED)
        ->orderBy('gacha_item_masters.digits_code','ASC');
    }
}
