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
use App\SkuClass;
use App\SkuLegend;
use App\ActionType;
use App\StatusState;
use App\Vendor;
use App\WorkflowSetting;
use Illuminate\Support\Facades\Input;

class McbUploadController extends \crocodicstudio\crudbooster\controllers\CBController
{
    
    private $pending;
    private $create;
    private $update;
    private $active;
    private $invalid;
    private $inactive_inventory;
    private $trade_inventory;
    
    public function __construct() {
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
		
		$this->pending = StatusState::where('status_state','PENDING')->value('id');

		$this->create = ActionType::where('action_type',"CREATE")->value('id');
		$this->update = ActionType::where('action_type',"UPDATE")->value('id');
		$this->active = SkuStatus::where('sku_status_description','ACTIVE')->value('id');
		$this->invalid = SkuStatus::where('sku_status_description','INVALID')->value('id');
		$this->inactive_inventory = InventoryType::where('inventory_type_description','INACTIVE')->value('id');
		$this->trade_inventory = InventoryType::where('inventory_type_description','TRADE')->value('id');
	}
	
    public function cbInit() {
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function importItemTemplate()
	{
		Excel::create('new-item-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('dimfs', function ($sheet) {
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
			
		$validator = \Validator::make(
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

			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			if(!empty($dataExcel) && $dataExcel->count()) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					//checking of all submasters
					$existingUPC = ItemMaster::where('upc_code', trim($value->upc_code))->first();

					$apple_lobs_id = AppleLob::where('apple_lob_description', $value->apple_lob)
						->where('status', 'ACTIVE')->first();

					$brand_id = Brand::where('brand_description', $value->brand_description)
						->where('status','ACTIVE')->first();

					$category_id = Category::where('category_description', $value->category_description)
						->where('status','ACTIVE')->first();
					
					$class_id = DB::table('classes')->where([
						'class_description' => $value->class_description,
						'status' =>'ACTIVE',
						'categories_id' => $category_id->id])->first();
					
					$subclass_id = Subclass::where([
						'subclass_description' => $value->subclass,
						'status' => 'ACTIVE',
						'classes_id' => $class_id->id])->first();
					
					$margin_category_id = MarginCategory::where([
						'margin_category_description' => $value->margin_category,
						'status' => 'ACTIVE',
						'subclasses_id' => $subclass_id->id])->first();
					
					$warehouse_category_id = WarehouseCategory::where('warehouse_category_description', $value->warehouse_category)
						->where('status','ACTIVE')->first();

					$model_specific_id = ModelSpecific::where('model_specific_description', $value->model_specific_description)
						->where('status','ACTIVE')->first();

					$size_id = Size::where('size_code', $value->size_code)
						->where('status','ACTIVE')->first();

					$color_id = Color::where('color_description', $value->main_color_description)
						->where('status','ACTIVE')->first();
					
					$uom_id = Uom::where('uom_code', $value->uom_code)
						->where('status','ACTIVE')->first();

					$vendor_type_id = VendorType::where('vendor_type_code', $value->vendor_type_code)
						->where('status','ACTIVE')->first();
						
					$inventory_type_id = InventoryType::where('inventory_type_description', $value->inventory_type)
						->where('status','ACTIVE')->first();

					$currency_id = Currency::where('currency_code', $value->currency)
						->where('status','ACTIVE')->first();

					$sku_status_id = SkuStatus::where('sku_status_description', $value->sku_status)
						->where('status','ACTIVE')->first();
					
					$sku_legend_id = SkuLegend::where('sku_legend_description', $value->sku_legend)
						->where('status','ACTIVE')->first();
					
					if(!is_null($value->sku_class)){
						$sku_class_id = SkuClass::where('sku_class_description', $value->sku_class)
							->where('status','ACTIVE')->first();
					}

					if(!is_null($value->incoterms)){
						$incoterm_id = Incoterm::where('incoterms_description', $value->incoterms)
							->where('status','ACTIVE')->first();
					}

					$vendor_id = Vendor::where([
						'vendor_name' => $value->vendor_name,
						'status' => 'ACTIVE',
						'brands_id' => $brand_id->id])->first();

					$skulegend_btb = SkuLegend::where('sku_legend_description', $value->btb)->first();
					$skulegend_baseus = SkuLegend::where('sku_legend_description', $value->baseus)->first();
					$skulegend_dw = SkuLegend::where('sku_legend_description', $value->dw)->first();
					$skulegend_omg = SkuLegend::where('sku_legend_description', $value->omg)->first();
					$skulegend_online = SkuLegend::where('sku_legend_description', $value->online)->first();
					$skulegend_guam = SkuLegend::where('sku_legend_description', $value->guam)->first();
					$skulegend_distri_con = SkuLegend::where('sku_legend_description', $value->distri_con)->first();
					$skulegend_distri_out = SkuLegend::where('sku_legend_description', $value->distri_out)->first();
					$skulegend_dw_machine = SkuLegend::where('sku_legend_description', $value->dw_machine)->first();
                    $skulegend_franchise = SkuLegend::where('sku_legend_description', $value->franchise)->first();
                    $skulegend_newstore = SkuLegend::where('sku_legend_description', $value->new_store)->first();
                    $skulegend_mi = SkuLegend::where('sku_legend_description', $value->mi)->first();
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
						array_push($errors, 'Line '.$line_item.': with margin category "'.$value->margin_category.'" is not found in submaster.');
					}
					if(empty($warehouse_category_id)){
						array_push($errors, 'Line '.$line_item.': with wh category "'.$value->warehouse_category.'" is not found in submaster.');
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
					if(empty($skulegend_btb)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->btb.'" at column BTB not found in submaster.');
					}
					if(empty($skulegend_baseus)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->baseus.'" at column BASEUS not found in submaster.');
					}
					if(empty($skulegend_dw)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->dw.'" at column DW not found in submaster.');
					}
					if(empty($skulegend_omg)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->omg.'" at column OMG not found in submaster.');
					}
					if(empty($skulegend_online)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->online.'" at column ONLINE not found in submaster.');
					}
					if(empty($skulegend_guam)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->guam.'" at column GUAM not found in submaster.');
					}
					if(empty($skulegend_distri_con)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->distri_con.'" at column DISTRI CON not found in submaster.');
					}
					if(empty($skulegend_distri_out)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->distri_out.'" at column DISTRI OUT not found in submaster.');
					}
					if(empty($skulegend_dw_machine)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->dw_machine.'" at column DW MACHINE not found in submaster.');
					}
					if(empty($skulegend_franchise)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->franchise.'" at column FRANCHISE not found in submaster.');
					}
					if(empty($skulegend_newstore)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->new_store.'" at column NEW STORE not found in submaster.');
					}
					if(empty($skulegend_mi)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->mi.'" at column MI not found in submaster.');
					}

					$data = [
						'upc_code' => $value->upc_code,
						'supplier_item_code' => $value->supplier_item_code,
						'model_number' => $value->model_number,
						'item_description' => $value->item_description,
						'apple_lobs_id' => $apple_lobs_id->id,
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
						'year_launch' => $value->year_launch,
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
						'sku_classes_id' => (is_null($value->sku_class)) ? null : $sku_class_id->id,
						'incoterms_id' => (is_null($value->incoterms)) ? null : $incoterm_id->id,
						'current_srp' => (is_null($value->current_srp)) ? '0.00' : $value->current_srp,
						'original_srp' => (is_null($value->original_srp)) ? '0.00' : $value->original_srp,
						'promo_srp' => (is_null($value->dg_srp)) ? null : $value->dg_srp,
						'moq' => (is_null($value->moq)) ? null : $value->moq,
						'purchase_price' => (is_null($value->supplier_cost)) ? null : $value->supplier_cost,
						'dtp_rf' => (is_null($value->store_cost)) ? null : $value->store_cost,
						'dtp_rf_percentage' => (is_null($value->store_margin_percentage)) ? null : $value->store_margin_percentage,
						'working_dtp_rf' => (is_null($value->working_store_cost)) ? null : $value->working_store_cost,
						'working_dtp_rf_percentage' => (is_null($value->working_store_margin_percentage)) ? null : $value->working_store_margin_percentage,
						'landed_cost' => (is_null($value->landed_cost)) ? null : $value->landed_cost,
						'working_landed_cost' => (is_null($value->working_landed_cost)) ? null : $value->working_landed_cost,
						'warranties_id' => 1, //year
						'warranty_duration' => 1,
						'btb_segmentation' => $value->btb,
						'baseus_segmentation' => $value->baseus,
						'dw_segmentation' => $value->dw,
						'omg_segmentation' => $value->omg,
						'online_segmentation' => $value->online,
						'guam_segmentation' => $value->guam,
						'dcon_segmentation' => $value->distri_con,
						'dout_segmentation' => $value->distri_out,
						'dwmachine_segmentation' => $value->dw_machine,
						'franchise_segmentation' => $value->franchise,
						'newstore_segmentation' => $value->new_store,
						'mi_segmentation' => $value->mi,
						'has_serial' => $value->serial_code,
						'imei_code1' => '0',
						'imei_code2' => '0',
						'approval_status' => $this->pending,
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
		Excel::create('mcb-update-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('dimfs', function ($sheet) {
				$sheet->row(1, 
					array(
						'DIGITS CODE',
					    'UPC CODE 1',
						'UPC CODE 2',
						'UPC CODE 3',
						'UPC CODE 4',
						'UPC CODE 5',
						'SUPPLIER ITEM CODE',
						'MODEL NUMBER',
						'BRAND DESCRIPTION',
						'SKU STATUS',
						'SKU LEGEND',
						'ITEM DESCRIPTION',
						'MODEL',
						'YEAR LAUNCH',
						'MODEL SPECIFIC DESCRIPTION',
						'COMPATIBILITY',
						'MARGIN CATEGORY DESCRIPTION',
						'CATEGORY DESCRIPTION',
						'CLASS DESCRIPTION',
						'SUBCLASS DESCRIPTION',
						'WH CATEGORY DESCRIPTION',
						'ORIGINAL SRP',
						'CURRENT SRP',
						'DG SRP',
						'PRICE CHANGE',
						'PRICE CHANGE DATE',
						'DURATION FROM',
						'DURATION TO',
						'SUPPORT TYPE',
						'VENDOR TYPE CODE',
						'MOQ',
						'CURRENCY',
						'SUPPLIER COST',
						'SIZE',
						'SIZE CODE',
						'LENGTH',
                        'WIDTH',
                        'HEIGHT',
                        'WEIGHT',
						'ACTUAL COLOR',
						'MAIN COLOR DESCRIPTION',
						'UOM CODE',
						'INVENTORY TYPE',
						'SKU CLASS',
						'BASEUS',
						'BTB',
						'DISTRI CON',
						'DISTRI OUT',
						'DW',
						'DW MACHINE',
						'GUAM',
						'OMG',
						'ONLINE',
						'FRANCHISE',
						'NEW STORE',
						'MI',
						'SERIAL CODE'
						)
					);
			});

			})->download('csv');
	}
	
	public function importMcbEdit(Request $request)
	{
		$errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
			
		$validator = \Validator::make(
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
			$header = array(
				'DIGITS CODE',
				'UPC CODE 1',
				'UPC CODE 2',
				'UPC CODE 3',
				'UPC CODE 4',
				'UPC CODE 5',
				'SUPPLIER ITEM CODE',
				'MODEL NUMBER',
				'BRAND DESCRIPTION',
				'SKU STATUS',
				'SKU LEGEND',
				'ITEM DESCRIPTION',
				'MODEL',
				'YEAR LAUNCH',
				'MODEL SPECIFIC DESCRIPTION',
				'COMPATIBILITY',
				'MARGIN CATEGORY DESCRIPTION',
				'CATEGORY DESCRIPTION',
				'CLASS DESCRIPTION',
				'SUBCLASS DESCRIPTION',
				'WH CATEGORY DESCRIPTION',
				'ORIGINAL SRP',
				'CURRENT SRP',
				'DG SRP',
				'PRICE CHANGE',
				'PRICE CHANGE DATE',
				'DURATION FROM',
				'DURATION TO',
				'SUPPORT TYPE',
				'VENDOR TYPE CODE',
				'MOQ',
				'CURRENCY',
				'SUPPLIER COST',
				'SIZE',
				'SIZE CODE',
				'LENGTH',
                'WIDTH',
                'HEIGHT',
                'WEIGHT',
				'ACTUAL COLOR',
				'MAIN COLOR DESCRIPTION',
				'UOM CODE',
				'INVENTORY TYPE',
				'SKU CLASS',
				'BASEUS',
				'BTB',
				'DISTRI CON',
				'DISTRI OUT',
				'DW',
				'DW MACHINE',
				'GUAM',
				'OMG',
				'ONLINE',
				'FRANCHISE',
				'NEW STORE',
				'MI',
				'SERIAL CODE');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			if(!empty($dataExcel) && $dataExcel->count()) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					//checking of all submasters
					$existingDigitsCode = ItemMaster::where('digits_code', $value->digits_code)->first();
					$existingUPCCode = ItemMaster::where('upc_code', $value->upc_code_1)->count();

					$brand_id = Brand::where('brand_description', $value->brand_description)
						->where('status','ACTIVE')->first();

					$category_id = Category::where('category_description', $value->category_description)
						->where('status','ACTIVE')->first();
					
					$class_id = DB::table('classes')->where([
						'class_description' => $value->class_description,
						'status' =>'ACTIVE',
						'categories_id' => $category_id->id])->first();
					
					$subclass_id = Subclass::where([
						'subclass_description' => $value->subclass_description,
						'status' => 'ACTIVE',
						'classes_id' => $class_id->id])->first();
					
					$margin_category_id = MarginCategory::where([
						'margin_category_description' => $value->margin_category_description,
						'status' => 'ACTIVE',
						'subclasses_id' => $subclass_id->id])->first();
					
					$warehouse_category_id = WarehouseCategory::where('warehouse_category_description', $value->wh_category_description)
						->where('status','ACTIVE')->first();

					$model_specific_id = ModelSpecific::where('model_specific_description', $value->model_specific_description)
						->where('status','ACTIVE')->first();

					$size_id = Size::where('size_code', $value->size_code)
						->where('status','ACTIVE')->first();

					$color_id = Color::where('color_description', $value->main_color_description)
						->where('status','ACTIVE')->first();
					
					$uom_id = Uom::where('uom_code', $value->uom_code)
						->where('status','ACTIVE')->first();

					$vendor_type_id = VendorType::where('vendor_type_code', $value->vendor_type_code)
						->where('status','ACTIVE')->first();
						
					$inventory_type_id = InventoryType::where('inventory_type_description', $value->inventory_type)
						->where('status','ACTIVE')->first();

					$currency_id = Currency::where('currency_code', $value->currency)
						->where('status','ACTIVE')->first();

					$sku_status_id = SkuStatus::where('sku_status_description', $value->sku_status)
						->where('status','ACTIVE')->first();
					
					$sku_legend_id = SkuLegend::where('sku_legend_description', $value->sku_legend)
						->where('status','ACTIVE')->first();
					
					if(!is_null($value->sku_class)){
						$sku_class_id = SkuClass::where('sku_class_description', $value->sku_class)
							->where('status','ACTIVE')->first();
					}

					$skulegend_btb = SkuLegend::where('sku_legend_description', $value->btb)->first();
					$skulegend_baseus = SkuLegend::where('sku_legend_description', $value->baseus)->first();
					$skulegend_dw = SkuLegend::where('sku_legend_description', $value->dw)->first();
					$skulegend_omg = SkuLegend::where('sku_legend_description', $value->omg)->first();
					$skulegend_online = SkuLegend::where('sku_legend_description', $value->online)->first();
					$skulegend_guam = SkuLegend::where('sku_legend_description', $value->guam)->first();
					$skulegend_distri_con = SkuLegend::where('sku_legend_description', $value->distri_con)->first();
					$skulegend_distri_out = SkuLegend::where('sku_legend_description', $value->distri_out)->first();
					$skulegend_dw_machine = SkuLegend::where('sku_legend_description', $value->dw_machine)->first();
					$skulegend_franchise = SkuLegend::where('sku_legend_description', $value->franchise)->first();
					$skulegend_newstore = SkuLegend::where('sku_legend_description', $value->new_store)->first();
					$skulegend_mi = SkuLegend::where('sku_legend_description', $value->mi)->first();

					//---------------------------
					if(empty($existingDigitsCode)){
						array_push($errors, 'Line '.$line_item.': item code "'.$value->digits_code.'" not found.');
					}
					if($existingUPCCode > 1){
						array_push($errors, 'Line '.$line_item.': duplicate upc code has been detected.');
					}
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
					if(empty($sku_class_id) && !is_null($value->sku_class)){
						array_push($errors, 'Line '.$line_item.': with sku class "'.$value->sku_class.'" is not found in submaster.');
					}
					if(empty($skulegend_btb) && !empty($value->btb)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->btb.'" at column BTB not found in submaster.');
					}
					if(empty($skulegend_baseus) && !empty($value->baseus)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->baseus.'" at column BASEUS not found in submaster.');
					}
					if(empty($skulegend_dw) && !empty($value->dw)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->dw.'" at column DW not found in submaster.');
					}
					if(empty($skulegend_omg) && !empty($value->omg)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->omg.'" at column OMG not found in submaster.');
					}
					if(empty($skulegend_online) && !empty($value->online)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->online.'" at column ONLINE not found in submaster.');
					}
					if(empty($skulegend_guam) && !empty($value->guam)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->guam.'" at column GUAM not found in submaster.');
					}
					if(empty($skulegend_distri_con) && !empty($value->distri_con)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->distri_con.'" at column DISTRI CON not found in submaster.');
					}
					if(empty($skulegend_distri_out) && !empty($value->distri_out)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->distri_out.'" at column DISTRI OUT not found in submaster.');
					}
					if(empty($skulegend_dw_machine) && !empty($value->dw_machine)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->dw_machine.'" at column DW MACHINE not found in submaster.');
					}
					if(empty($skulegend_franchise) && !empty($value->franchise)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->franchise.'" at column FRANCHISE not found in submaster.');
					}
					if(empty($skulegend_newstore) && !empty($value->new_store)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->new_store.'" at column NEW STORE not found in submaster.');
					}
					if(empty($skulegend_mi) && !empty($value->mi)){
						array_push($errors, 'Line '.$line_item.': with segmentation "'.$value->mi.'" at column MI not found in submaster.');
					}

					$data = [
						'upc_code' => $value->upc_code_1,
						'upc_code_2' => $value->upc_code_2,
						'upc_code_3' => $value->upc_code_3,
						'upc_code_4' => $value->upc_code_4,
						'upc_code_5' => $value->upc_code_5,
						'supplier_item_code' => $value->supplier_item_code,
						'item_description' => $value->item_description,
						'brands_id' => $brand_id->id,
						'categories_id' => $category_id->id,
						'classes_id' => $class_id->id,
						'subclasses_id' => $subclass_id->id,
						'margin_categories_id' => $margin_category_id->id,
						'warehouse_categories_id' => $warehouse_category_id->id,
						'compatibility' => $value->compatibility,
						'model_number' => $value->model_number,
						'model' => $value->model,
						'year_launch' => $value->year_launch,
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
						'sku_classes_id' => $sku_class_id->id,
						'current_srp' => $value->current_srp,
						'original_srp' => $value->original_srp,
						'promo_srp' => $value->dg_srp,
						'moq' => $value->moq,
						'purchase_price' => $value->supplier_cost,
						'btb_segmentation' => $value->btb,
						'baseus_segmentation' => $value->baseus,
						'dw_segmentation' => $value->dw,
						'omg_segmentation' => $value->omg,
						'online_segmentation' => $value->online,
						'guam_segmentation' => $value->guam,
						'dcon_segmentation' => $value->distri_con,
						'dout_segmentation' => $value->distri_out,
						'dwmachine_segmentation' => $value->dw_machine,
						'franchise_segmentation' => $value->franchise,
						'newstore_segmentation' => $value->new_store,
						'mi_segmentation' => $value->mi,
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
			'action_types_id'=>$this->create,
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
			'action_types_id'=>$this->update,
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
