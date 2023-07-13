<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('admin/login');
});
Route::group(['middleware' => ['web', '\crocodicstudio\crudbooster\middlewares\CBBackend']], function () {
    //item master
    Route::get(config('crudbooster.ADMIN_PATH').'/getBrandCode/{id}','AdminBrandsController@getBrandCode')->name('getBrandCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getCategoryCode/{id}','AdminCategoriesController@getCategoryCode')->name('getCategoryCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getSizeCode/{id}','AdminSizesController@getSizeCode')->name('getSizeCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getModelSpecificCode/{id}','AdminModelSpecificsController@getModelSpecificCode')->name('getModelSpecificCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getVendorTypeCode/{vendor_id}','AdminVendorsController@getVendorTypeCode')->name('getVendorTypeCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getVendorByBrand/{brand_id}','AdminVendorsController@getVendorByBrand')->name('getVendorByBrand');
    Route::get(config('crudbooster.ADMIN_PATH').'/getVendorIncoterms/{vendor_id}','AdminVendorsController@getVendorIncoterms')->name('getVendorIncoterms');
    Route::get(config('crudbooster.ADMIN_PATH').'/getVendorGroupByVendor/{vendor_id}','AdminVendorGroupsController@getVendorGroupByVendor')->name('getVendorGroupByVendor');
    Route::get(config('crudbooster.ADMIN_PATH').'/getClassByCategory/{category_id}','AdminClassesController@getClassByCategory')->name('getClassByCategory');
    Route::get(config('crudbooster.ADMIN_PATH').'/getSubclassByClass/{class_id}','AdminSubclassesController@getSubclassByClass')->name('getSubclassByClass');
    Route::get(config('crudbooster.ADMIN_PATH').'/getCategoryClassCode/{class_id}','AdminClassesController@getCategoryClassCode')->name('getCategoryClassCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getMarginCategoryBySubclass/{subclass_id}','AdminMarginCategoriesController@getMarginCategoryBySubclass')->name('getMarginCategoryBySubclass');
    Route::get(config('crudbooster.ADMIN_PATH').'/getStoreCategoryBySubclass/{subclass_id}','AdminStoreCategoriesController@getStoreCategoryBySubclass')->name('getStoreCategoryBySubclass');

    Route::get(config('crudbooster.ADMIN_PATH').'/users', 'AdminCmsUsersController@getIndex')->name('AdminCmsUsersControllerGetIndex');
    //exports
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/export-pos', 'AdminItemMastersController@exportPOSFormat')->name('exportPOSFormat');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/export-bartender', 'AdminItemMastersController@exportBartenderFormat')->name('exportBartenderFormat');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/export-all', 'AdminItemMastersController@exportAllItems')->name('exportAllItems');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/export-margin', 'AdminItemMastersController@exportMargin')->name('exportMargin');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_master_approvals/export-pending', 'AdminItemMasterApprovalsController@exportPendingItems')->name('exportPendingItems');
    Route::get(config('crudbooster.ADMIN_PATH').'/ecom_price_changes/export-all', 'AdminEcomPriceChangesController@exportAllEcomChanges')->name('exportAllEcomChanges');
    
    //imports - item master
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-view', 'AdminItemMastersController@importView')->name('importView');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-wrr-view', 'AdminItemMastersController@importWRRView')->name('importWRRView');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-item-view', 'AdminItemMastersController@importItemView')->name('importItemView');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-skulegend-view', 'AdminItemMastersController@importSKULegendView')->name('importSKULegendView');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-ecom-view', 'AdminItemMastersController@importECOMView')->name('importECOMView');
    
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-wrr-template', 'AdminItemMastersController@importWRRTemplate')->name('upload.wrr-template');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-item-template', 'McbUploadController@importItemTemplate')->name('upload.item-template');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-skulegend-template', 'AdminItemMastersController@importSKULegendTemplate')->name('upload.skulegend-template');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-ecom-template', 'AdminItemMastersController@importECOMTemplate')->name('upload.ecom-template');
    
    Route::post(config('crudbooster.ADMIN_PATH').'/item_masters/import-wrr', 'AdminItemMastersController@importWRR')->name('upload.wrr');
    Route::post(config('crudbooster.ADMIN_PATH').'/item_masters/import-item', 'McbUploadController@importItem')->name('upload.item');
    Route::post(config('crudbooster.ADMIN_PATH').'/item_masters/import-skulegend', 'AdminItemMastersController@importSKULegend')->name('upload.skulegend');
    Route::post(config('crudbooster.ADMIN_PATH').'/item_masters/import-ecom', 'AdminItemMastersController@importECOM')->name('upload.ecom');
    
    //imports - ecom price change
    Route::get(config('crudbooster.ADMIN_PATH').'/ecom_price_changes/import-price-view', 'AdminEcomPriceChangesController@importPriceView')->name('importPriceView');
    Route::get(config('crudbooster.ADMIN_PATH').'/ecom_price_changes/import-bau-price-template', 'AdminEcomPriceChangesController@importBauPriceTemplate')->name('upload.bau-price-template');
    Route::get(config('crudbooster.ADMIN_PATH').'/ecom_price_changes/import-promo-price-template', 'AdminEcomPriceChangesController@importPromoPriceTemplate')->name('upload.promo-price-template');
    Route::post(config('crudbooster.ADMIN_PATH').'/ecom_price_changes/import-price', 'AdminEcomPriceChangesController@importPrice')->name('upload.price');
    
    //imports - warranty change
    Route::get(config('crudbooster.ADMIN_PATH').'/warranty_changes/import-warranty-view', 'AdminWarrantyChangesController@importWarrantyView')->name('importWarrantyView');
    Route::get(config('crudbooster.ADMIN_PATH').'/warranty_changes/import-warranty-template', 'AdminWarrantyChangesController@importWarrantyTemplate')->name('upload.warranty-template');
    Route::post(config('crudbooster.ADMIN_PATH').'/warranty_changes/import-warranty', 'AdminWarrantyChangesController@importWarranty')->name('upload.warranty');
    
    Route::post(config('crudbooster.ADMIN_PATH').'/getExistingUPC', 'AdminItemMastersController@getExistingUPC')->name('getExistingUPC');
    Route::post(config('crudbooster.ADMIN_PATH').'/getExistingDigitsCode', 'AdminItemMastersController@getExistingDigitsCode')->name('getExistingDigitsCode');
    Route::post(config('crudbooster.ADMIN_PATH').'/compareCurrentSRP', 'AdminItemMastersController@compareCurrentSRP')->name('compareCurrentSRP');
    Route::post(config('crudbooster.ADMIN_PATH').'/getUnitsMarginPercentage', 'AdminItemMastersController@getUnitsMarginPercentage')->name('getUnitsMarginPercentage');
    Route::post(config('crudbooster.ADMIN_PATH').'/getAccessoriesMarginPercentage', 'AdminItemMastersController@getAccessoriesMarginPercentage')->name('getAccessoriesMarginPercentage');
    
    // Edited by Lewie 
    Route::post(config('crudbooster.ADMIN_PATH').'/EcomMarginPercentage', 'AdminItemMastersController@EcomMarginPercentage')->name('EcomMarginPercentage');
    
    Route::get(config('crudbooster.ADMIN_PATH').'/send-notification', 'AdminItemMastersController@sendApprovedItemEmailNotif')->name('sendApprovedItemEmailNotif');
    
    //rma item master
    Route::get(config('crudbooster.ADMIN_PATH').'/getRMACategoryCode/{id}','AdminRmaCategoriesController@getRMACategoryCode')->name('getRMACategoryCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getRMAModelSpecificCode/{id}','AdminRmaModelSpecificsController@getRMAModelSpecificCode')->name('getRMAModelSpecificCode');
    Route::get(config('crudbooster.ADMIN_PATH').'/getRMAClassByCategory/{category_id}','AdminRmaClassesController@getRMAClassByCategory')->name('getRMAClassByCategory');
    Route::get(config('crudbooster.ADMIN_PATH').'/getRMASubclassByClass/{class_id}','AdminRmaSubclassesController@getRMASubclassByClass')->name('getRMASubclassByClass');
    
    Route::get(config('crudbooster.ADMIN_PATH').'/getMarginMatrixByMarginCategory/{margin_category}/{brand_id}/{vendor_type_id}','AdminItemMastersController@getMarginMatrixByMarginCategory')->name('getMarginMatrixByMarginCategory');
    Route::get(config('crudbooster.ADMIN_PATH').'/getMarginMatrixByOtherMarginCategory/{margin_category}','AdminItemMastersController@getMarginMatrixByOtherMarginCategory')->name('getMarginMatrixByOtherMarginCategory');
    
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-acctg-view', 'AdminItemMastersController@importItemAccountingView')->name('importItemAccountingView');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-mcb-view', 'AdminItemMastersController@importItemMcbView')->name('importItemMcbView');
    
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-acctg-template', 'AccountingUploadController@importAccountingTemplate')->name('upload.acctg-template');
    Route::get(config('crudbooster.ADMIN_PATH').'/item_masters/import-mcb-template', 'McbUploadController@importMcbTemplate')->name('upload.mcb-template');
    
    Route::post(config('crudbooster.ADMIN_PATH').'/item_masters/import-acctg', 'AccountingUploadController@importAccountingEdit')->name('upload.acctg');
    Route::post(config('crudbooster.ADMIN_PATH').'/item_masters/import-mcb', 'McbUploadController@importMcbEdit')->name('upload.mcb');
    
});