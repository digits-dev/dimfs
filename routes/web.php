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
Route::group(['middleware' => ['web', '\crocodicstudio\crudbooster\middlewares\CBBackend'],'prefix' => config('crudbooster.ADMIN_PATH')], function () {
    //item master
    Route::get('/getBrandCode/{id}','AdminBrandsController@getBrandCode')->name('getBrandCode');
    Route::get('/getCategoryCode/{id}','AdminCategoriesController@getCategoryCode')->name('getCategoryCode');
    Route::get('/getSizeCode/{id}','AdminSizesController@getSizeCode')->name('getSizeCode');
    Route::get('/getModelSpecificCode/{id}','AdminModelSpecificsController@getModelSpecificCode')->name('getModelSpecificCode');
    Route::get('/getVendorTypeCode/{vendor_id}','AdminVendorsController@getVendorTypeCode')->name('getVendorTypeCode');
    Route::get('/getVendorByBrand/{brand_id}','AdminVendorsController@getVendorByBrand')->name('getVendorByBrand');
    Route::get('/getVendorIncoterms/{vendor_id}','AdminVendorsController@getVendorIncoterms')->name('getVendorIncoterms');
    Route::get('/getVendorGroupByVendor/{vendor_id}','AdminVendorGroupsController@getVendorGroupByVendor')->name('getVendorGroupByVendor');
    Route::get('/getClassByCategory/{category_id}','AdminClassesController@getClassByCategory')->name('getClassByCategory');
    Route::get('/getSubclassByClass/{class_id}','AdminSubclassesController@getSubclassByClass')->name('getSubclassByClass');
    Route::get('/getCategoryClassCode/{class_id}','AdminClassesController@getCategoryClassCode')->name('getCategoryClassCode');
    Route::get('/getMarginCategoryBySubclass/{subclass_id}','AdminMarginCategoriesController@getMarginCategoryBySubclass')->name('getMarginCategoryBySubclass');
    Route::get('/getStoreCategoryBySubclass/{subclass_id}','AdminStoreCategoriesController@getStoreCategoryBySubclass')->name('getStoreCategoryBySubclass');

    Route::get('/users', 'AdminCmsUsersController@getIndex')->name('AdminCmsUsersControllerGetIndex');
    //exports
    Route::get('/item_masters/export-pos', 'AdminItemMastersController@exportPOSFormat')->name('exportPOSFormat');
    Route::get('/item_masters/export-bartender', 'AdminItemMastersController@exportBartenderFormat')->name('exportBartenderFormat');
    Route::get('/item_masters/export-all', 'AdminItemMastersController@exportAllItems')->name('exportAllItems');
    Route::get('/item_masters/export-margin', 'AdminItemMastersController@exportMargin')->name('exportMargin');
    Route::get('/item_master_approvals/export-pending', 'AdminItemMasterApprovalsController@exportPendingItems')->name('exportPendingItems');
    Route::get('/ecom_price_changes/export-all', 'AdminEcomPriceChangesController@exportAllEcomChanges')->name('exportAllEcomChanges');
    
    //imports - item master
    Route::get('/item_masters/import-view', 'AdminItemMastersController@importView')->name('importView');
    Route::get('/item_masters/import-wrr-view', 'AdminItemMastersController@importWRRView')->name('importWRRView');
    Route::get('/item_masters/import-item-view', 'AdminItemMastersController@importItemView')->name('importItemView');
    Route::get('/item_masters/import-skulegend-view', 'AdminItemMastersController@importSKULegendView')->name('importSKULegendView');
    Route::get('/item_masters/import-ecom-view', 'AdminItemMastersController@importECOMView')->name('importECOMView');
    
    Route::get('/item_masters/import-wrr-template', 'AdminItemMastersController@importWRRTemplate')->name('upload.wrr-template');
    Route::get('/item_masters/import-item-template', 'McbUploadController@importItemTemplate')->name('upload.item-template');
    Route::get('/item_masters/import-skulegend-template', 'SegmentationController@importTemplate')->name('upload.skulegend-template');
    Route::get('/item_masters/import-ecom-template', 'AdminItemMastersController@importECOMTemplate')->name('upload.ecom-template');
    
    Route::post('/item_masters/import-wrr', 'AdminItemMastersController@importWRR')->name('upload.wrr');
    Route::post('/item_masters/import-item', 'McbUploadController@importItem')->name('upload.item');
    Route::post('/item_masters/import-skulegend', 'SegmentationController@importSKULegendSegmentation')->name('upload.skulegend');
    Route::post('/item_masters/import-ecom', 'AdminItemMastersController@importECOM')->name('upload.ecom');
    
    //imports - ecom price change
    Route::get('/ecom_price_changes/import-price-view', 'AdminEcomPriceChangesController@importPriceView')->name('importPriceView');
    Route::get('/ecom_price_changes/import-bau-price-template', 'AdminEcomPriceChangesController@importBauPriceTemplate')->name('upload.bau-price-template');
    Route::get('/ecom_price_changes/import-promo-price-template', 'AdminEcomPriceChangesController@importPromoPriceTemplate')->name('upload.promo-price-template');
    Route::post('/ecom_price_changes/import-price', 'AdminEcomPriceChangesController@importPrice')->name('upload.price');
    
    //imports - warranty change
    Route::get('/warranty_changes/import-warranty-view', 'AdminWarrantyChangesController@importWarrantyView')->name('importWarrantyView');
    Route::get('/warranty_changes/import-warranty-template', 'AdminWarrantyChangesController@importWarrantyTemplate')->name('upload.warranty-template');
    Route::post('/warranty_changes/import-warranty', 'AdminWarrantyChangesController@importWarranty')->name('upload.warranty');
    
    Route::post('/getExistingUPC', 'AdminItemMastersController@getExistingUPC')->name('getExistingUPC');
    Route::post('/getExistingDigitsCode', 'AdminItemMastersController@getExistingDigitsCode')->name('getExistingDigitsCode');
    Route::post('/compareCurrentSRP', 'AdminItemMastersController@compareCurrentSRP')->name('compareCurrentSRP');
    Route::post('/getUnitsMarginPercentage', 'AdminItemMastersController@getUnitsMarginPercentage')->name('getUnitsMarginPercentage');
    Route::post('/getAccessoriesMarginPercentage', 'AdminItemMastersController@getAccessoriesMarginPercentage')->name('getAccessoriesMarginPercentage');
    
    // Edited by Lewie 
    Route::post('/EcomMarginPercentage', 'AdminItemMastersController@EcomMarginPercentage')->name('EcomMarginPercentage');
    
    Route::get('/send-notification', 'AdminItemMastersController@sendApprovedItemEmailNotif')->name('sendApprovedItemEmailNotif');
    
    //rma item master
    Route::get('/getRMACategoryCode/{id}','AdminRmaCategoriesController@getRMACategoryCode')->name('getRMACategoryCode');
    Route::get('/getRMAModelSpecificCode/{id}','AdminRmaModelSpecificsController@getRMAModelSpecificCode')->name('getRMAModelSpecificCode');
    Route::get('/getRMAClassByCategory/{category_id}','AdminRmaClassesController@getRMAClassByCategory')->name('getRMAClassByCategory');
    Route::get('/getRMASubclassByClass/{class_id}','AdminRmaSubclassesController@getRMASubclassByClass')->name('getRMASubclassByClass');
    
    Route::get('/getMarginMatrixByMarginCategory/{margin_category}/{brand_id}/{vendor_type_id}','AdminItemMastersController@getMarginMatrixByMarginCategory')->name('getMarginMatrixByMarginCategory');
    Route::get('/getMarginMatrixByOtherMarginCategory/{margin_category}','AdminItemMastersController@getMarginMatrixByOtherMarginCategory')->name('getMarginMatrixByOtherMarginCategory');
    
    Route::get('/item_masters/import-acctg-view', 'AdminItemMastersController@importItemAccountingView')->name('importItemAccountingView');
    Route::get('/item_masters/import-mcb-view', 'AdminItemMastersController@importItemMcbView')->name('importItemMcbView');
    
    Route::get('/item_masters/import-acctg-template', 'AccountingUploadController@importAccountingTemplate')->name('upload.acctg-template');
    Route::get('/item_masters/import-mcb-template', 'McbUploadController@importMcbTemplate')->name('upload.mcb-template');
    
    Route::post('/item_masters/import-acctg', 'AccountingUploadController@importAccountingEdit')->name('upload.acctg');
    Route::post('/item_masters/import-mcb', 'McbUploadController@importMcbEdit')->name('upload.mcb');


    //gachapon import items
    Route::post('gasha_item_masters/add-item', 'AdminGachaItemMasterApprovalsController@submitNewItem')->name('submit_new_gacha_item');
    Route::post('gasha_item_masters/edit-item', 'AdminGachaItemMasterApprovalsController@submitEditItem')->name('submit_edit_gacha_item');
    Route::get('gasha_item_masters/edit-item-accounting-detail/{id}', 'AdminGachaItemMasterApprovalsController@getEditAccounting');
    Route::post('gasha_item_masters/edit-item-accounting-detail', 'AdminGachaItemMasterApprovalsController@submitEditAccounting')->name('submit_edit_accounting');
    Route::get('gasha_item_masters/import-view', 'AdminGachaItemMastersController@importItemView')->name('importGachaItemView');
    Route::get('gasha_item_masters/import-gacha-template', 'GachaponItemMasterImportController@importItemTemplate')->name('upload.gacha-template');
    Route::post('gasha_item_masters/import-gacha-items', 'GachaponItemMasterImportController@importItems')->name('upload.gachaItems');

    Route::get('item_models/get-item-models', 'AdminItemModelsController@getItemModels')->name('get-item-models');
    
    Route::get('gasha_item_masters/import-edit-view', 'AdminGachaItemMastersController@importItemEditView')->name('importGachaItemEditView');
    Route::get('gasha_item_masters/import-edit-template', 'GachaponItemMasterImportController@importItemEditTemplate')->name('edit.gacha-template');
    Route::post('gasha_item_masters/edit-gacha-items', 'GachaponItemMasterImportController@editItems')->name('edit.gachaItems');

    Route::post('export_privileges/create/save','AdminExportPrivilegesController@saveExport')->name('export-privileges.save');
    Route::post('export_privileges/get/table-columns','AdminExportPrivilegesController@getTableColumns')->name('export-privileges.getTableColumns');
    Route::post('export_privileges/get/user-table-columns','AdminExportPrivilegesController@getUserTableColumns')->name('export-privileges.getUserTableColumns');

});