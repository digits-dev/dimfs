<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RmaItemMaster extends Model
{
    protected $table = 'rma_item_masters';
    const APPROVED = 200;
    public function scopeGenerateExport($query){
        return $query->select(
            "rma_item_masters.digits_code",
            "rma_item_masters.item_description",
            "rma_item_masters.upc_code",
            "brands.brand_description",
            "rma_categories.category_description",
            "vendor_types.vendor_type_code",
            "inventory_types.inventory_type_description",
            "rma_item_masters.current_srp",
            "rma_item_masters.initial_wrr_date",
            "sku_statuses.sku_status_description  as status"

        )
        ->leftJoin('brands', 'rma_item_masters.brands_id', '=', 'brands.id')
        ->leftJoin('rma_categories', 'rma_item_masters.rma_categories_id', '=', 'rma_categories.id')
        ->leftJoin('vendor_types', 'rma_item_masters.vendor_types_id', '=', 'vendor_types.id')
        ->leftJoin('inventory_types', 'rma_item_masters.inventory_types_id', '=', 'inventory_types.id')
        ->leftJoin('sku_statuses', 'rma_item_masters.sku_statuses_id', '=', 'sku_statuses.id')
        ->leftJoin('cms_users as createdby', 'rma_item_masters.created_by', '=', 'createdby.id')
        ->leftJoin('cms_users as updatedby', 'rma_item_masters.updated_by', '=', 'updatedby.id')
        ->leftJoin('cms_users as approvedby', 'rma_item_masters.approved_by', '=', 'approvedby.id')
        ->orderBy('rma_item_masters.digits_code','ASC');
    }
}
