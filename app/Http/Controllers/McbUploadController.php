<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use CRUDBooster;
use Excel;
use App\AppleLob;
use App\Brand;
use App\Category;
use App\Subclass;
use App\MarginCategory;
use App\WarehouseCategory;
use App\ModelSpecific;
use App\Size;
use App\Color;
use App\Uom;
use App\VendorType;
use App\InventoryType;
use App\ItemMaster;
use App\ItemMasterApproval;
use App\Incoterm;
use App\Currency;
use App\SkuStatus;
use App\SkuLegend;
use App\BrandDirection;
use App\BrandGroup;
use App\BrandMarketing;
use App\Http\Traits\UploadTraits;
use App\ItemClass;
use App\Segmentation;
use App\SegmentationLegend;
use App\Vendor;
use App\WorkflowSetting;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;

class McbUploadController extends \crocodicstudio\crudbooster\controllers\CBController
{

	use UploadTraits;
    
    public function __construct() {
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
	}
    
    public function importItemTemplate()
	{
		// $template = config('excel-template.item-master');
		$file_name = 'new-item-'.date("Ymd-His");//.'.csv';
		// return (new FastExcel([$template]))->download($file_name);
		Excel::create($file_name, function ($excel) {
			$excel->sheet('edit', function ($sheet) {
				$sheet->row(1, config('excel-template.item-master'));
			});
		})->download('csv');
	}
	
	public function importItem(Request $request)
	{
		$errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
		$segments = Segmentation::active()->get();
			
		$validator = Validator::make(
			['file' => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
			['file' => 'required', 'extension' => 'required|in:csv',]
		);

		if ($validator->fails()) {
			return back()->with('error_import', 'Failed ! Please check required file extension.');
		}

		if (Input::hasFile('import_file')) {
			$path = Input::file('import_file')->getRealPath();
			
			$csv = array_map('str_getcsv', file($path));
			$dataExcel = Excel::load($path, function($reader) {})->get();
			
			$unMatch = [];
			$header = config('excel-template.item-master');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			$unmatchHeaders = json_encode($unMatch);
			
			if(!empty($unMatch)) {
				return back()->with('error_import', "Failed ! Please check template headers, mismatched detected. $unmatchHeaders");
			}

			$appleLobs = AppleLob::active()->get();
			$brandGroups = BrandGroup::active()->get();
			$brandDirections = BrandDirection::active()->get();
			// $brandMarketings = BrandMarketing::active()->get();
			$brands = Brand::active()->get();
			$categories = Category::active()->get();
			$classes = ItemClass::active()->get();
			$subClasses = Subclass::active()->get();
			$marginCategories = MarginCategory::active()->get();
			$warehouseCategories = WarehouseCategory::active()->get();
			$modelSpecifics = ModelSpecific::active()->get();
			$sizes = Size::active()->get();
			$colors = Color::active()->get();
			$uoms = Uom::active()->get();
			$vendorTypes = VendorType::active()->get();
			$inventoryTypes = InventoryType::active()->get();
			$currencies = Currency::active()->get();
			$skuStatuses = SkuStatus::active()->get();
			$skuLegends = SkuLegend::active()->get();
			$incoterms = Incoterm::active()->get();
			$vendors = Vendor::active()->get();
			$segmentLegends = SegmentationLegend::active()->get();
						
			if(!empty($dataExcel) && $dataExcel->count()) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					//checking of all submasters
					$existingUPC = ItemMaster::where('upc_code', trim($value->upc_code))->first();

					$apple_lobs_id = $appleLobs->where('apple_lob_description', $value->apple_lob)->first();
					$brand_groups = $brandGroups->where('brand_group_description', $value->brand_group)->first();
					$brand_directions = $brandDirections->where('brand_direction_description', $value->brand_direction)->first();
					// $brand_marketings = $brandMarketings->where('brand_marketing_description', $value->brand_marketing)->first();
					$brand_id = $brands->where('brand_description', $value->brand_description)->first();
					$category_id = $categories->where('category_description', $value->category_description)->first();
					
					if(!is_null($value->class_description) && !is_null($category_id)){
						$class_id = $classes->where('class_description', $value->class_description)
							->where('categories_id', $category_id->id)->first();
					}
					
					if(!is_null($value->subclass_description) && !is_null($class_id)){
						$subclass_id = $subClasses->where('subclass_description', $value->subclass_description)
							->where('classes_id', $class_id->id)->first();
					}
					
					if(!is_null($value->margin_category_description) && !is_null($subclass_id)){
						$margin_category_id = $marginCategories->where('margin_category_description', $value->margin_category_description)
							->where('subclasses_id', $subclass_id->id)->first();
					}
					
					$warehouse_category_id = $warehouseCategories->where('warehouse_category_description', $value->wh_category_description)->first();
					$model_specific_id = $modelSpecifics->where('model_specific_description', $value->model_specific_description)->first();

					$size_id = $sizes->where('size_code', $value->size_code)->first();
					$color_id = $colors->where('color_description', $value->main_color_description)->first();
					$uom_id = $uoms->where('uom_code', $value->uom_code)->first();
					$vendor_type_id = $vendorTypes->where('vendor_type_code', $value->vendor_type_code)->first();
					$inventory_type_id = $inventoryTypes->where('inventory_type_description', $value->inventory_type)->first();
					$currency_id = $currencies->where('currency_code', $value->currency)->first();
					$sku_status_id = $skuStatuses->where('sku_status_description', $value->sku_status)->first();
					$sku_legend_id =$skuLegends->where('sku_legend_description', $value->sku_legend)->first();

					if(!is_null($value->incoterms)){
						$incoterm_id = $incoterms->where('incoterms_description', $value->incoterms)->first();
					}

					$vendor_id = $vendors->where('vendor_name', $value->vendor_name)
						->where('brands_id', $brand_id->id)->first();
					//---------------------------
					if(!empty($existingUPC)){
						array_push($errors, 'Line '.$line_item.': existing upc code "'.$value->upc_code.'" has been detected.');
					}
					if(empty($value->upc_code)){
						array_push($errors, 'Line '.$line_item.': upc code can\'t be null or blank.');
					}
					if(empty($value->supplier_item_code)){
						array_push($errors, 'Line '.$line_item.': upc code can\'t be null or blank.');
					}
					if(empty($value->item_description)){
						array_push($errors, 'Line '.$line_item.': item description can\'t be null or blank.');
					}
					if(strlen($value->item_description) > 60){
						array_push($errors, 'Line '.$line_item.': item description exceed 60 characters.');
					}
					if(empty($apple_lobs_id)){
						array_push($errors, 'Line '.$line_item.': with apple lob "'.$value->apple_lob.'" is not found in submaster.');
					}
					if($value->apple_report_inclusion != 0 && $value->apple_report_inclusion != 1){
						array_push($errors, 'Line '.$line_item.': Invalid value: apple report inclusion.');
					}
					if(empty($brand_id)){
						array_push($errors, 'Line '.$line_item.': with brand "'.$value->brand_description.'" is not found in submaster.');
					}
					if(empty($category_id)){
						array_push($errors, 'Line '.$line_item.': with category "'.$value->category_description.'" is not found in submaster.');
					}
					if(empty($class_id)){
						array_push($errors, 'Line '.$line_item.': with class description "'.$value->class_description.'" is not found in submaster.');
					}
					if(empty($subclass_id)){
						array_push($errors, 'Line '.$line_item.': with subclass "'.$value->subclass.'" is not found in submaster.');
					}
					if(empty($margin_category_id)){
						array_push($errors, 'Line '.$line_item.': with margin category "'.$value->margin_category_description.'" is not found in submaster.');
					}
					if(empty($warehouse_category_id)){
						array_push($errors, 'Line '.$line_item.': with wh category "'.$value->wh_category_description.'" is not found in submaster.');
					}
					if(empty($value->model)){
						array_push($errors, 'Line '.$line_item.': model can\'t be null or blank.');
					}
					if(empty($model_specific_id)){
						array_push($errors, 'Line '.$line_item.': with model specific "'.$value->model_specific_description.'" is not found in submaster.');
					}
					if(empty($size_id)){
						array_push($errors, 'Line '.$line_item.': with size code "'.$value->size_code.'" is not found in submaster.');
					}
					if(empty($color_id)){
						array_push($errors, 'Line '.$line_item.': with color description "'.$value->main_color_description.'" is not found in submaster.');
					}
					if(empty($uom_id)){
						array_push($errors, 'Line '.$line_item.': with uom "'.$value->uom_code.'" is not found in submaster.');
					}
					if(empty($vendor_type_id)){
						array_push($errors, 'Line '.$line_item.': with vendor type "'.$value->vendor_type_code.'" is not found in submaster.');
					}
					if(empty($inventory_type_id)){
						array_push($errors, 'Line '.$line_item.': with inventory type "'.$value->inventory_type.'" is not found in submaster.');
					}
					if(empty($currency_id)){
						array_push($errors, 'Line '.$line_item.': with currency "'.$value->currency.'" is not found in submaster.');
					}
					if(empty($sku_status_id)){
						array_push($errors, 'Line '.$line_item.': with sku status "'.$value->sku_status.'" is not found in submaster.');
					}
					if(empty($sku_legend_id)){
						array_push($errors, 'Line '.$line_item.': with sku legend "'.$value->sku_legend.'" is not found in submaster.');
					}
					if(empty($sku_class_id) && !is_null($value->sku_class)){
						array_push($errors, 'Line '.$line_item.': with sku class "'.$value->sku_class.'" is not found in submaster.');
					}
					if(empty($incoterm_id) && !is_null($value->incoterms)){
						array_push($errors, 'Line '.$line_item.': with incoterm "'.$value->incoterms.'" is not found in submaster.');
					}
					if(empty($vendor_id)){
						array_push($errors, 'Line '.$line_item.': with vendor "'.$value->vendor_name.'" is not found in submaster.');
					}

					foreach ($segments as $key_segment => $value_segment) {
						$seg = $value_segment->import_header_name;
						$seg_description = $value_segment->segmentation_description;
						$seg_value = $value_segment->$seg;
						$legendExists = $segmentLegends->where('segment_legend_description', $seg_value)->first();
						if(empty($legendExists) && !empty($seg_value)){
							array_push($errors, "Line $line_item : with segmentation $seg_value at column $seg_description not found in submaster.");
						}
					}
					
					$data = [
						'upc_code' => $value->upc_code,
						'supplier_item_code' => $value->supplier_item_code,
						'model_number' => $value->model_number,
						'item_description' => $value->item_description,
						'apple_lobs_id' => $apple_lobs_id->id,
						'brand_groups_id' => $brand_groups->id,
						'brand_directions_id' => $brand_directions->id,
						// 'brand_marketings_id' => $brand_marketings->id,
						'apple_report_inclusion' => $value->apple_report_inclusion,
						'brands_id' => $brand_id->id,
						'categories_id' => $category_id->id,
						'subcategories_id' => 0,
						'classes_id' => $class_id->id,
						'subclasses_id' => $subclass_id->id,
						'margin_categories_id' => $margin_category_id->id,
						'warehouse_categories_id' => $warehouse_category_id->id,
						'compatibility' => $value->compatibility,
						'model' => $value->model,
						'model_specifics_id' => $model_specific_id->id,
						'sizes_id' => $size_id->id,
						'size_value' => $value->size,
						'size' => ($value->size	== 0) ? $size_id->size_code : $value->size.''.$size_id->size_code,
						'item_length' => $value->length,
						'item_width' => $value->width,
						'item_height' => $value->height,
						'item_weight' => $value->weight,
						'actual_color' => $value->actual_color,
						'colors_id' => $color_id->id,
						'uoms_id' => $uom_id->id,
						'vendors_id' => $vendor_id->id,
						'vendor_types_id' => $vendor_type_id->id,
						'inventory_types_id' => $inventory_type_id->id,
						'currencies_id' => $currency_id->id,
						'sku_legends_id' => $sku_legend_id->id,
						'sku_statuses_id' => $sku_status_id->id,
						'incoterms_id' => (is_null($value->incoterms)) ? null : $incoterm_id->id,
						'current_srp' => (is_null($value->current_srp)) ? '0.00' : $value->current_srp,
						'original_srp' => (is_null($value->original_srp)) ? '0.00' : $value->original_srp,
						'promo_srp' => (is_null($value->dg_srp)) ? null : $value->dg_srp,
						'moq' => (is_null($value->moq)) ? null : $value->moq,
						'purchase_price' => (is_null($value->supplier_cost)) ? null : $value->supplier_cost,
						// 'dtp_rf' => (is_null($value->store_cost)) ? null : $value->store_cost,
						// 'dtp_rf_percentage' => (is_null($value->store_margin_percentage)) ? null : $value->store_margin_percentage,
						// 'working_dtp_rf' => (is_null($value->working_store_cost)) ? null : $value->working_store_cost,
						// 'working_dtp_rf_percentage' => (is_null($value->working_store_margin_percentage)) ? null : $value->working_store_margin_percentage,
						// 'landed_cost' => (is_null($value->landed_cost)) ? null : $value->landed_cost,
						// 'working_landed_cost' => (is_null($value->working_landed_cost)) ? null : $value->working_landed_cost,
						// 'warranties_id' => 1, //year
						// 'warranty_duration' => 1,
						'btb_segmentation' => $value->beyond_the_box,
						'dw_segmentation' => $value->digital_walker,
						'guam_segmentation' => $value->guam,
						'dcon_segmentation' => $value->distribution_consignment,
						'dout_segmentation' => $value->distribution_outright,
						'franchise_segmentation' => $value->franchise,
						'newstore_segmentation' => $value->new_store,
						'opensource_segmentation' => $value->opensource,
						'web_segmentation' => $value->website,
						'af_segmentation' => $value->acefast,
						'svc_segmentation' => $value->service_center,
						'laz_segmentation' => $value->lazada,
						'spe_segmentation' => $value->shopee,
						'tik_segmentation' => $value->tiktok,
						'has_serial' => $value->serial_code,
						'imei_code1' => '0',
						'imei_code2' => '0',
						'approval_status' => $this->getStatusByDescription("PENDING"),
						'created_by' => CRUDBooster::myId(),
						'created_at' => date('Y-m-d H:i:s')
					];

					try {
						if(empty($errors)){
							$cnt_success++;
							$newItem = ItemMaster::insertGetId($data);
							$data['item_masters_id'] = $newItem;
							ItemMasterApproval::insert($data);
							
							$this->sendCreateNotification($value->upc_code, CRUDBooster::myPrivilegeId());
						}

					} catch (\Exception $e) {
						$cnt_fail++;
						array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
					}
				}
			}
		}

		if(empty($errors)){
			return back()->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
		}
		else{
			return back()->with('error_import', implode("<br>", $errors));
		}
	}
    
    public function importMcbTemplate()
	{
		// $template = config('excel-template.item-master-edit');
		$file_name = 'mcb-update-'.date("Ymd-His");//.'.csv';
		// return (new FastExcel([$template]))->download($file_name);
		Excel::create($file_name, function ($excel) {
			$excel->sheet('edit', function ($sheet) {
				$sheet->row(1, config('excel-template.item-master-edit'));
			});
		})->download('csv');
	}
	
	public function importMcbEdit(Request $request)
	{
		$errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
		$segments = Segmentation::active()->get();
			
		$validator = Validator::make(
			['file' => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
			['file' => 'required', 'extension' => 'required|in:csv',]
		);

		if ($validator->fails()) {
			return back()->with('error_import', 'Failed ! Please check required file extension.');
		}

		if (Input::hasFile('import_file')) {
			$path = Input::file('import_file')->getRealPath();
			
			$csv = array_map('str_getcsv', file($path));
			$dataExcel = Excel::load($path, function($reader) {})->get();
			$unMatch = [];
			$header = config('excel-template.item-master-edit');
			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if(!in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			$unmatchHeaders = json_encode($unMatch);

			if(!empty($unMatch)) {
				return back()->with('error_import', "Failed ! Please check template headers, mismatched detected. $unmatchHeaders");
			}

			$appleLobs = AppleLob::active()->get();
			$brandGroups = BrandGroup::active()->get();
			$brandDirections = BrandDirection::active()->get();
			$brands = Brand::active()->get();
			$categories = Category::active()->get();
			$classes = ItemClass::active()->get();
			$subClasses = Subclass::active()->get();
			$marginCategories = MarginCategory::active()->get();
			$warehouseCategories = WarehouseCategory::active()->get();
			$modelSpecifics = ModelSpecific::active()->get();
			$sizes = Size::active()->get();
			$colors = Color::active()->get();
			$uoms = Uom::active()->get();
			$vendorTypes = VendorType::active()->get();
			$inventoryTypes = InventoryType::active()->get();
			$currencies = Currency::active()->get();
			$skuStatuses = SkuStatus::active()->get();
			$skuLegends = SkuLegend::active()->get();
			$segmentLegends = SegmentationLegend::active()->get();
			// $incoterms = Incoterm::active()->get();
			// $vendors = Vendor::active()->get();
			
			if(!empty($dataExcel) && $dataExcel->count()) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					//checking of all submasters
					$existingDigitsCode = ItemMaster::where('digits_code', $value->digits_code)->first();
					$existingUPCCode = ItemMaster::where('upc_code', $value->upc_code_1)->count();

					$apple_lobs_id = $appleLobs->where('apple_lob_description', $value->apple_lob)->first();
					$brand_groups = $brandGroups->where('brand_group_description', $value->brand_group)->first();
					$brand_directions = $brandDirections->where('brand_direction_description', $value->brand_direction)->first();
					$brand_id = $brands->where('brand_description', $value->brand_description)->first();
					$category_id = $categories->where('category_description', $value->category_description)->first();

					$class_id = null;
					if(!is_null($value->class_description) && !empty($category_id)){
					    $class_id = $classes->where('class_description', $value->class_description)
							->where('categories_id', $category_id->id)->first();
					}
					
					$subclass_id = null;
					if(!is_null($value->subclass_description) && !is_null($class_id)){
					    $subclass_id = $subClasses->where('subclass_description', $value->subclass_description)
							->where('classes_id', $class_id->id)->first();
					}

					$margin_category_id = null;
					if(!is_null($value->margin_category_description) && !is_null($subclass_id)){
						$margin_category_id = $marginCategories->where('margin_category_description', $value->margin_category_description)
							->where('subclasses_id', $subclass_id->id)->first();
					}			
					
					$warehouse_category_id = $warehouseCategories->where('warehouse_category_description', $value->wh_category_description)->first();
					$model_specific_id = $modelSpecifics->where('model_specific_description', $value->model_specific_description)->first();
					$size_id = $sizes->where('size_code', $value->size_code)->first();
					$color_id = $colors->where('color_description', $value->main_color_description)->first();
					$uom_id = $uoms->where('uom_code', $value->uom_code)->first();
					$vendor_type_id = $vendorTypes->where('vendor_type_code', $value->vendor_type_code)->first();
					$inventory_type_id = $inventoryTypes->where('inventory_type_description', $value->inventory_type)->first();
					$currency_id = $currencies->where('currency_code', $value->currency)->first();
					$sku_status_id = $skuStatuses->where('sku_status_description', $value->sku_status)->first();
					$sku_legend_id =$skuLegends->where('sku_legend_description', $value->sku_legend)->first();

					if(empty($existingDigitsCode)){
						array_push($errors, 'Line '.$line_item.': item code "'.$value->digits_code.'" not found.');
					}
					// if($existingUPCCode > 1){
					// 	array_push($errors, 'Line '.$line_item.': duplicate upc code has been detected.');
					// }
					if(strlen($value->item_description) > 60 && !empty($value->item_description)){
						array_push($errors, 'Line '.$line_item.': item description exceed 60 characters.');
					}
					if(empty($brand_id) && !empty($value->brand_description)){
						array_push($errors, 'Line '.$line_item.': with brand "'.$value->brand_description.'" is not found in submaster.');
					}
					if(empty($category_id) && !empty($value->category_description)){
						array_push($errors, 'Line '.$line_item.': with category "'.$value->category_description.'" is not found in submaster.');
					}
					if(empty($class_id) && !empty($value->class_description)){
						array_push($errors, 'Line '.$line_item.': with class description "'.$value->class_description.'" is not found in submaster.');
					}
					if(empty($subclass_id) && !empty($value->subclass_description)){
						array_push($errors, 'Line '.$line_item.': with subclass "'.$value->subclass_description.'" is not found in submaster.');
					}
					if(empty($margin_category_id) && !empty($value->margin_category_description)){
						array_push($errors, 'Line '.$line_item.': with margin category "'.$value->margin_category_description.'" is not found in submaster.');
					}
					if(empty($warehouse_category_id) && !empty($value->wh_category_description)){
						array_push($errors, 'Line '.$line_item.': with wh category "'.$value->wh_category_description.'" is not found in submaster.');
					}
					if(empty($model_specific_id) && !empty($value->model_specific_description)){
						array_push($errors, 'Line '.$line_item.': with model specific "'.$value->model_specific_description.'" is not found in submaster.');
					}
					if(empty($size_id) && !empty($value->size_code)){
						array_push($errors, 'Line '.$line_item.': with size code "'.$value->size_code.'" is not found in submaster.');
					}
					if(empty($color_id) && !empty($value->main_color_description)){
						array_push($errors, 'Line '.$line_item.': with color description "'.$value->main_color_description.'" is not found in submaster.');
					}
					if(empty($uom_id) && !empty($value->uom_code)){
						array_push($errors, 'Line '.$line_item.': with uom "'.$value->uom_code.'" is not found in submaster.');
					}
					if(empty($vendor_type_id) && !empty($value->vendor_type_code)){
						array_push($errors, 'Line '.$line_item.': with vendor type "'.$value->vendor_type_code.'" is not found in submaster.');
					}
					if(empty($inventory_type_id) && !empty($value->inventory_type)){
						array_push($errors, 'Line '.$line_item.': with inventory type "'.$value->inventory_type.'" is not found in submaster.');
					}
					if(empty($currency_id) && !empty($value->currency)){
						array_push($errors, 'Line '.$line_item.': with currency "'.$value->currency.'" is not found in submaster.');
					}
					if(empty($sku_status_id) && !empty($value->sku_status)){
						array_push($errors, 'Line '.$line_item.': with sku status "'.$value->sku_status.'" is not found in submaster.');
					}
					if(empty($sku_legend_id) && !empty($value->sku_legend)){
						array_push($errors, 'Line '.$line_item.': with sku legend "'.$value->sku_legend.'" is not found in submaster.');
					}

					foreach ($segments as $key_segment => $value_segment) {
						$seg = $value_segment->import_header_name;
						$seg_description = $value_segment->segmentation_description;
						$seg_value = $value_segment->$seg;
						$legendExists = $segmentLegends->where('segment_legend_description', $seg_value)->first();
						if(empty($legendExists) && !empty($seg_value)){
							array_push($errors, "Line $line_item : with segmentation $seg_value at column $seg_description not found in submaster.");
						}
					}
					
					$data = [
						'upc_code' => $value->upc_code_1,
						'upc_code_2' => $value->upc_code_2,
						'upc_code_3' => $value->upc_code_3,
						'upc_code_4' => $value->upc_code_4,
						'upc_code_5' => $value->upc_code_5,
						'supplier_item_code' => $value->supplier_item_code,
						'item_description' => $value->item_description,
						'apple_lobs_id' => $apple_lobs_id->id,
						'brand_groups_id' => $brand_groups->id,
						'brand_directions_id' => $brand_directions->id,
						'apple_report_inclusion' => $value->apple_report_inclusion,
						'brands_id' => $brand_id->id,
						'categories_id' => $category_id->id,
						'classes_id' => $class_id->id,
						'subclasses_id' => $subclass_id->id,
						'margin_categories_id' => $margin_category_id->id,
						'warehouse_categories_id' => $warehouse_category_id->id,
						'compatibility' => $value->compatibility,
						'model_number' => $value->model_number,
						'model' => $value->model,
						'model_specifics_id' => $model_specific_id->id,
						'sizes_id' => $size_id->id,
						'size_value' => $value->size,
						'size' => ($value->size	== 0) ? $size_id->size_code : $value->size.' '.$size_id->size_code,
						'item_length' => $value->length,
						'item_width' => $value->width,
						'item_height' => $value->height,
						'item_weight' => $value->weight,
						'actual_color' => $value->actual_color,
						'colors_id' => $color_id->id,
						'uoms_id' => $uom_id->id,
						'vendor_types_id' => $vendor_type_id->id,
						'inventory_types_id' => $inventory_type_id->id,
						'currencies_id' => $currency_id->id,
						'sku_legends_id' => $sku_legend_id->id,
						'sku_statuses_id' => $sku_status_id->id,
						'current_srp' => $value->current_srp,
						'original_srp' => $value->original_srp,
						'promo_srp' => $value->dg_srp,
						'moq' => $value->moq,
						'purchase_price' => $value->supplier_cost,
						'btb_segmentation' => $value->beyond_the_box,
						'dw_segmentation' => $value->digital_walker,
						'guam_segmentation' => $value->guam,
						'dcon_segmentation' => $value->distribution_consignment,
						'dout_segmentation' => $value->distribution_outright,
						'franchise_segmentation' => $value->franchise,
						'newstore_segmentation' => $value->new_store,
						'opensource_segmentation' => $value->opensource,
						'web_segmentation' => $value->website,
						'af_segmentation' => $value->acefast,
						'svc_segmentation' => $value->service_center,
						'laz_segmentation' => $value->lazada,
						'spe_segmentation' => $value->shopee,
						'tik_segmentation' => $value->tiktok,
						'has_serial' => $value->serial_code,
						'updated_by' => CRUDBooster::myId(),
						'updated_at' => date('Y-m-d H:i:s')
					];

					$update_data = array_filter($data, 'strlen');
					
					try {
						if(empty($errors)){
							$cnt_success++;
							ItemMaster::where('digits_code', $value->digits_code)->update($update_data);
							
							$old_values = json_decode(json_encode($existingDigitsCode), true);
							$new_values = json_decode(json_encode($update_data), true);
							CRUDBooster::insertLog(trans("crudbooster.log_update", [
                                'name' => $value->digits_code,
                                'module' => CRUDBooster::getCurrentModule()->name,
                            ]), app('crocodicstudio\crudbooster\controllers\LogsController')->displayDiff($old_values, $new_values));
						}

					} catch (\Exception $e) {
						$cnt_fail++;
						array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
					}
				}
			}
		}

		if(empty($errors)){
			return back()->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
		}
		else{
			return back()->with('error_import', implode("<br>", $errors));
		}
	}
	
	public function sendCreateNotification($upc_code,$encoder_id) {

		//get workflow settings
		$workflow = WorkflowSetting::where([
			'cms_moduls_id'=>CRUDBooster::getCurrentModule()->id,
			'action_types_id'=>$this->getActionByDescription("CREATE"),
			'encoder_privileges_id'=>$encoder_id
		])->first();
		
		$approvers_id = DB::table('cms_users')
			->where('id_cms_privileges',$workflow->approver_privileges_id)
			->pluck('id')->toArray();
		//get users id of approvers

		$config['content'] = CRUDBooster::myName(). " has added an item with UPC CODE: ".$upc_code." at Item Master Approval Module!";
		$config['to'] = CRUDBooster::adminPath('item_master_approvals?q='.$upc_code);
		$config['id_cms_users'] = $approvers_id;
		CRUDBooster::sendNotification($config);
	}
	
	public function sendUpdateNotification($digits_code,$encoder_id) {
		//get workflow settings
		$workflow = WorkflowSetting::where([
			'cms_moduls_id'=>CRUDBooster::getCurrentModule()->id,
			'action_types_id'=>$this->getActionByDescription("UPDATE"),
			'encoder_privileges_id'=>$encoder_id
		])->first();
		
		$approvers_id = DB::table('cms_users')
			->where('id_cms_privileges',$workflow->approver_privileges_id)
			->pluck('id')->toArray();
		//get users id of approvers

		$config['content'] = CRUDBooster::myName(). " has added an item with DIGITS CODE: ".$digits_code." at Item Master Price Module!";
		$config['to'] = CRUDBooster::adminPath('item_price_approvals?q='.$digits_code);
		$config['id_cms_users'] = $approvers_id;
		CRUDBooster::sendNotification($config);
	}
}
