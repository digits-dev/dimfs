-- run query on the db the following code

-- create view gacha_item_masters_export as

select
    gacha_item_masters.id as gacha_item_masters_id,
    gacha_item_masters.approval_status,
    gacha_item_masters.jan_no,
    gacha_item_masters.digits_code,
    gacha_item_masters.item_no,
    gacha_item_masters.sap_no,
    gacha_item_masters.initial_wrr_date,
    gacha_item_masters.latest_wrr_date,
    gacha_brands.brand_description,
    gacha_sku_statuses.status_description,
    gacha_item_masters.item_description,
    gacha_item_masters.gacha_models,
    gacha_wh_categories.category_description,
    gacha_item_masters.msrp,
    gacha_item_masters.current_srp,
    gacha_item_masters.no_of_tokens,
    gacha_item_masters.store_cost,
    gacha_item_masters.sc_margin,
    gacha_item_masters.lc_per_pc,
    gacha_item_masters.lc_margin_per_pc,
    gacha_item_masters.lc_per_carton,
    gacha_item_masters.lc_margin_per_carton,
    gacha_item_masters.dp_ctn,
    gacha_item_masters.pcs_dp,
    gacha_item_masters.moq,
    gacha_item_masters.pcs_ctn,
    gacha_item_masters.no_of_ctn,
    gacha_item_masters.no_of_assort,
    gacha_countries.country_code,
    gacha_incoterms.incoterm_description,
    currencies.currency_code,
    gacha_item_masters.supplier_cost,
    gacha_uoms.uom_code,
    gacha_inventory_types.inventory_type_description,
    gacha_vendor_types.vendor_type_code,
    gacha_vendor_groups.vendor_group_description,
    gacha_item_masters.age_grade,
    gacha_item_masters.battery,
    gacha_item_masters.created_at,
    created_by.name as created_name,
    gacha_item_masters.approved_at,
    approved_by.name as approved_name,
    gacha_item_masters.updated_at,
    updated_by.name as updated_name
from gacha_item_masters
    left join gacha_brands on gacha_brands.id = gacha_item_masters.gacha_brands_id
    left join gacha_sku_statuses on gacha_sku_statuses.id = gacha_item_masters.gacha_sku_statuses_id
    left join gacha_wh_categories on gacha_wh_categories.id = gacha_item_masters.gacha_wh_categories_id
    left join gacha_countries on gacha_countries.id = gacha_item_masters.gacha_countries_id
    left join gacha_incoterms on gacha_incoterms.id = gacha_item_masters.gacha_incoterms_id
    left join currencies on currencies.id = gacha_item_masters.currencies_id
    left join gacha_uoms on gacha_uoms.id = gacha_item_masters.gacha_uoms_id
    left join gacha_inventory_types on gacha_inventory_types.id = gacha_item_masters.gacha_inventory_types_id
    left join gacha_vendor_types on gacha_vendor_types.id = gacha_item_masters.gacha_vendor_types_id
    left join gacha_vendor_groups on gacha_vendor_groups.id = gacha_item_masters.gacha_vendor_groups_id
    left join cms_users as created_by on created_by.id = gacha_item_masters.created_by
    left join cms_users as updated_by on updated_by.id = gacha_item_masters.updated_by
    left join cms_users as approved_by on approved_by.id = gacha_item_masters.approved_by