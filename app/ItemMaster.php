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

    public function scopeGenerateExport($query){
        return $query->select(
            "item_masters.digits_code",
            "inventory_types.inventory_type_description as inventory_type_code",
            "item_masters.upc_code",
            "item_masters.upc_code2",
            "item_masters.upc_code3",
            "item_masters.upc_code4",
            "item_masters.upc_code5",
            "item_masters.supplier_item_code",
            "item_masters.model_number",
            "item_masters.initial_wrr_date",
            "item_masters.latest_wrr_date",
            "brand_groups.brand_group_description",
            "brand_directions.brand_direction_description",
            "brands.brand_description",
            "brands.status as brand_status",
            "sku_statuses.sku_status_description",
            "sku_legends.sku_legend_description",
            "item_masters.item_description",
            "item_masters.model",
            "model_specifics.model_specific_description",
            "item_masters.compatibility",
            "apple_lobs.apple_lob_description as apple_lob",
            "item_masters.apple_report_inclusion",
            "categories.category_description",
            "classes.class_description",
            "subclasses.subclass_description",
            "warehouse_categories.warehouse_category_description",
            "item_masters.original_srp",
            "item_masters.current_srp",
            "item_masters.promo_srp",
            "item_masters.price_change",
            "item_masters.effective_date",
            "item_masters.dtp_rf",
            "item_masters.dtp_rf_percentage",
            "item_masters.ecom_store_margin",
            "item_masters.ecom_store_margin_percentage",
            "item_masters.landed_cost",
            "item_masters.working_dtp_rf",
            "item_masters.working_dtp_rf_percentage",
            "item_masters.working_ecom_store_margin",
            "item_masters.working_ecom_store_margin_percentage",
            "item_masters.working_landed_cost",
            "item_masters.duration_from",
            "item_masters.duration_to",
            "support_types.support_type_description",
            "vendor_types.vendor_type_code",
            "item_masters.moq",
            "incoterms.incoterms_code",
            "currencies.currency_code",
            "item_masters.purchase_price",
            "item_masters.size as size_description",
            "item_masters.item_length",
            "item_masters.item_width",
            "item_masters.item_height",
            "item_masters.item_weight",
            "item_masters.actual_color",
            "colors.color_description",
            "uoms.uom_code",
            "item_masters.af_segmentation as acefast",
            "item_masters.btb_segmentation",
            "item_masters.dw_segmentation",
            "item_masters.opensource_segmentation",
            "item_masters.svc_segmentation as service_center",
            "item_masters.laz_segmentation as lazada",
            "item_masters.spe_segmentation as shopee",
            "item_masters.tik_segmentation as tiktok",
            "item_masters.web_segmentation as website",
            "item_masters.dcon_segmentation",
            "item_masters.dout_segmentation",
            "item_masters.franchise_segmentation",
            "item_masters.guam_segmentation",
            "item_masters.newstore_segmentation",
            "vendors.vendor_name",
            "vendors.status as vendor_status",
            "vendor_groups.vendor_group_name",
            "vendor_groups.status as vendor_group_status",
            "item_masters.warranty_duration",
            "warranties.warranty_description",
            "item_masters.has_serial",
            "item_masters.imei_code1",
            "item_masters.imei_code2",
            "createdby.name as createdby",
            "item_masters.created_at as createddate",
            "approvedby.name as approvedby as approved_by",
            "item_masters.approved_at",
            "updatedby.name as updatedby",
            "item_masters.updated_at as updateddate"
        )
        ->leftJoin('apple_lobs', 'item_masters.apple_lobs_id', '=', 'apple_lobs.id')
        ->leftJoin('brands', 'item_masters.brands_id', '=', 'brands.id')
        ->leftJoin('brand_groups', 'item_masters.brand_groups_id', '=', 'brand_groups.id')
        ->leftJoin('brand_directions', 'item_masters.brand_directions_id', '=', 'brand_directions.id')
        ->leftJoin('categories', 'item_masters.categories_id', '=', 'categories.id')
        ->leftJoin('classes', 'item_masters.classes_id', '=', 'classes.id')
        ->leftJoin('subclasses', 'item_masters.subclasses_id', '=', 'subclasses.id')
        ->leftJoin('warehouse_categories', 'item_masters.warehouse_categories_id', '=', 'warehouse_categories.id')
        ->leftJoin('model_specifics', 'item_masters.model_specifics_id', '=', 'model_specifics.id')
        ->leftJoin('colors', 'item_masters.colors_id', '=', 'colors.id')
        ->leftJoin('uoms', 'item_masters.uoms_id', '=', 'uoms.id')
        ->leftJoin('vendor_types', 'item_masters.vendor_types_id', '=', 'vendor_types.id')
        ->leftJoin('inventory_types', 'item_masters.inventory_types_id', '=', 'inventory_types.id')
        ->leftJoin('sku_statuses', 'item_masters.sku_statuses_id', '=', 'sku_statuses.id')
        ->leftJoin('sku_legends', 'item_masters.sku_legends_id', '=', 'sku_legends.id')
        ->leftJoin('currencies', 'item_masters.currencies_id', '=', 'currencies.id')
        ->leftJoin('vendors', 'item_masters.vendors_id', '=', 'vendors.id')
        ->leftJoin('vendor_groups', 'item_masters.vendor_groups_id', '=', 'vendor_groups.id')
        ->leftJoin('incoterms', 'item_masters.incoterms_id', '=', 'incoterms.id')
        ->leftJoin('support_types', 'item_masters.support_types_id', '=', 'support_types.id')
        ->leftJoin('warranties', 'item_masters.warranties_id', '=', 'warranties.id')
        ->leftJoin('cms_users as createdby', 'item_masters.created_by', '=', 'createdby.id')
        ->leftJoin('cms_users as updatedby', 'item_masters.updated_by', '=', 'updatedby.id')
        ->leftJoin('cms_users as approvedby', 'item_masters.approved_by', '=', 'approvedby.id')
        ->where('item_masters.approval_status',self::APPROVED)
        ->orderBy('item_masters.digits_code','ASC');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }
}
