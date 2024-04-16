-- run query on the db the following code

-- create view gacha_item_master_approvals_export as

select
    gacha_item_master_approvals.id as gacha_item_master_approvals_id,
    gacha_item_master_approvals.approval_status,
    gacha_item_master_approvals.jan_no,
    gacha_item_master_approvals.digits_code,
    gacha_item_master_approvals.item_no,
    gacha_item_master_approvals.sap_no,
    gacha_item_master_approvals.initial_wrr_date,
    gacha_item_master_approvals.latest_wrr_date,
    gacha_product_types.product_type_description,
    gacha_brands.brand_description,
    gacha_brand_statuses.status_description as brand_status,
    gacha_sku_statuses.status_description,
    gacha_item_master_approvals.item_description,
    gacha_item_master_approvals.gacha_models,
    gacha_categories.category_description,
    gacha_wh_categories.category_description as wh_category_description,
    gacha_item_master_approvals.msrp,
    gacha_item_master_approvals.current_srp,
    gacha_item_master_approvals.no_of_tokens,
    gacha_item_master_approvals.store_cost,
    gacha_item_master_approvals.sc_margin,
    gacha_item_master_approvals.lc_per_pc,
    gacha_item_master_approvals.lc_margin_per_pc,
    gacha_item_master_approvals.lc_per_carton,
    gacha_item_master_approvals.lc_margin_per_carton,
    gacha_item_master_approvals.dp_ctn,
    gacha_item_master_approvals.pcs_dp,
    gacha_item_master_approvals.moq,
    gacha_item_master_approvals.pcs_ctn,
    gacha_item_master_approvals.no_of_ctn,
    gacha_item_master_approvals.no_of_assort,
    gacha_countries.country_code,
    gacha_incoterms.incoterm_description,
    currencies.currency_code,
    gacha_item_master_approvals.supplier_cost,
    gacha_uoms.uom_code,
    gacha_inventory_types.inventory_type_description,
    gacha_vendor_types.vendor_type_code,
    gacha_vendor_groups.vendor_group_description,
    gacha_vendor_group_statuses.status_description as vendor_group_status,
    gacha_item_master_approvals.age_grade,
    gacha_item_master_approvals.battery,
    gacha_item_master_approvals.created_at,
    created_by.name as created_name,
    gacha_item_master_approvals.approved_at,
    approved_by.name as approved_name,
    gacha_item_master_approvals.updated_at,
    updated_by.name as updated_name
from
    gacha_item_master_approvals
    left join gacha_product_types on gacha_product_types.id = gacha_item_master_approvals.gacha_product_types_id
    left join gacha_brands on gacha_brands.id = gacha_item_master_approvals.gacha_brands_id
    left join gacha_brand_statuses on gacha_brands.gacha_brand_statuses_id = gacha_brand_statuses.id
    left join gacha_sku_statuses on gacha_sku_statuses.id = gacha_item_master_approvals.gacha_sku_statuses_id
    left join gacha_categories on gacha_categories.id = gacha_item_master_approvals.gacha_categories_id
    left join gacha_wh_categories on gacha_wh_categories.id = gacha_item_master_approvals.gacha_wh_categories_id
    left join gacha_countries on gacha_countries.id = gacha_item_master_approvals.gacha_countries_id
    left join gacha_incoterms on gacha_incoterms.id = gacha_item_master_approvals.gacha_incoterms_id
    left join currencies on currencies.id = gacha_item_master_approvals.currencies_id
    left join gacha_uoms on gacha_uoms.id = gacha_item_master_approvals.gacha_uoms_id
    left join gacha_inventory_types on gacha_inventory_types.id = gacha_item_master_approvals.gacha_inventory_types_id
    left join gacha_vendor_types on gacha_vendor_types.id = gacha_item_master_approvals.gacha_vendor_types_id
    left join gacha_vendor_groups on gacha_vendor_groups.id = gacha_item_master_approvals.gacha_vendor_groups_id
    left join gacha_vendor_group_statuses on gacha_vendor_groups.gacha_vendor_group_statuses_id = gacha_vendor_group_statuses.id
    left join cms_users as created_by on created_by.id = gacha_item_master_approvals.created_by
    left join cms_users as updated_by on updated_by.id = gacha_item_master_approvals.updated_by
    left join cms_users as approved_by on approved_by.id = gacha_item_master_approvals.approved_by