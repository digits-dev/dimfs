<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Excel;
use CRUDBooster;
use Illuminate\Support\Facades\Input;
use App\GachaItemApproval;
use App\GachaItemEditHistory;
class GachaponItemMasterImportController extends Controller
{
	private $errors;
    public function __construct() {
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
		$this->errors = array();
    }

    public function importItems(Request $request){
      $cnt_success = 0;
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
			$dataExcel = Excel::load($path, function($reader) {})->get()->toArray();
			
			$unMatch = [];
			$header = config('excel-template.gashapon-item-master');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			if(!empty($dataExcel)) {

				$submasters = (new AdminGachaItemMastersController)->getSubmaster();

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					
					$nullItems = array_filter($value, function ($obj) {
						return $obj == null;
					});

					if(!empty($nullItems)){
						$nullColumns = strtoupper(str_replace('_',' ',array_keys($nullItems)[0]));
						array_push($this->errors, "Line $line_item : $nullColumns is blank!");
					}
					$productType = self::filterValues($submasters,'product_types',$value,'product_type','product_type_description',$line_item,'Product Type');
					$brand = self::filterValues($submasters,'brands',$value,'brand_description','brand_description',$line_item,'Brand');
					$skuStatus = self::filterValues($submasters,'sku_statuses',$value,'sku_status','status_description',$line_item,'SKU Status');
					$category = self::filterValues($submasters,'categories',$value,'category','category_description',$line_item,'Category');
					$whCategory = self::filterValues($submasters,'warehouse_categories',$value,'wh_category_description','category_description',$line_item,'WH Category');
					$country = self::filterValues($submasters,'countries',$value,'country_of_origin','country_code',$line_item,'Country');
					$incoterm = self::filterValues($submasters,'incoterms',$value,'incoterms','incoterm_description',$line_item,'Incoterm');
					$currency = self::filterValues($submasters,'currencies',$value,'currency','currency_code',$line_item,'Currency');
					$uom = self::filterValues($submasters,'uoms',$value,'uom_code','uom_code',$line_item,'Uom');
					$inventoryType = self::filterValues($submasters,'inventory_types',$value,'inventory_type','inventory_type_description',$line_item,'Inventory Type');
					$vendorType = self::filterValues($submasters,'vendor_types',$value,'vendor_type','vendor_type_code',$line_item,'Vendor Type');
					$vendorGroup = self::filterValues($submasters,'vendor_groups',$value,'vendor_group_name','vendor_group_description',$line_item,'Vendor Group');
          			//data checking

					if(!empty($this->errors)){
						return back()->with('error_import', implode("<br>", $this->errors));
					}

					$jan_number = preg_replace("/[^A-Za-z0-9 ]/", '', $value['jan_number']);
					$item_description = trim(strtoupper(preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $value['item_description'])));
					
					$itemExists = GachaItemApproval::where('jan_no',$jan_number)->first();
					
					if(!empty($itemExists)){
						array_push($this->errors, "Line $line_item : $jan_number already exists!");
						return back()->with('error_import', implode("<br>", $this->errors));
					}
					
					$data = [
						'approval_status' => 202,
						'jan_no' => $jan_number,
						'item_no' => $value['item_number'],
						'sap_no' => $value['sap_number'],
						'gacha_product_types_id' => $productType[array_keys($productType)[0]]->id,
						'gacha_brands_id' => $brand[array_keys($brand)[0]]->id,
						'gacha_sku_statuses_id' => $skuStatus[array_keys($skuStatus)[0]]->id,
						'item_description' => $item_description,
						'gacha_models' => trim(strtoupper($value['model'])),
						'gacha_categories_id' => $category[array_keys($category)[0]]->id,
						'gacha_wh_categories_id' => $whCategory[array_keys($whCategory)[0]]->id,
						'msrp' => $value['msrp_jpy'],
						'current_srp' => $value['current_srp'],
						'no_of_tokens' => $value['number_of_tokens'],
						'dp_ctn' => $value['dp_per_ctn'],
						'pcs_dp' => $value['pcs_per_dp'],
						'moq' => $value['moq'],
						'pcs_ctn' => $value['pcs_per_ctn'],
						'no_of_ctn' => $value['order_ctn'],
						'no_of_assort' => $value['number_of_assort'],
						'gacha_countries_id' => $country[array_keys($country)[0]]->id,
						'gacha_incoterms_id' => $incoterm[array_keys($incoterm)[0]]->id,
						'currencies_id' => $currency[array_keys($currency)[0]]->id,
						'supplier_cost' => $value['supplier_cost'],
						'gacha_uoms_id' => $uom[array_keys($uom)[0]]->id,
						'gacha_inventory_types_id' => $inventoryType[array_keys($inventoryType)[0]]->id,
						'gacha_vendor_types_id' => $vendorType[array_keys($vendorType)[0]]->id,
						'gacha_vendor_groups_id' => $vendorGroup[array_keys($vendorGroup)[0]]->id,
						'age_grade' => trim(strtoupper($value['age_grade'])),
						'battery' => $value['battery'],
						'created_at' => date('Y-m-d H:i:s'),
						'created_by' => CRUDBooster::myId(),
					];

					
					
					try {
						if(empty($this->errors)){
							$cnt_success++;
							GachaItemApproval::updateOrInsert(['jan_no' => $jan_number],$data);
						}

					} catch (\Exception $e) {
						array_push($this->errors, "Line $line_item : with error ".json_encode($e));
					}
				}
			}
		}

		if(empty($this->errors)){
			return back()->with('success_import', "Success ! $cnt_success item(s) were created/updated successfully.🤩🤩🤩");
		}
		else{
			return back()->with('error_import', implode("<br>", $this->errors));
		}
    }

	private function filterValues($submasters,$submasterValue,$excelValue,$valueObject,$arrayObject,$lineItem,$errorColumn){
		$itemExists = array_filter($submasters[$submasterValue], function($obj) use($excelValue,$valueObject,$arrayObject){
			return $obj->$arrayObject == $excelValue[$valueObject];
		});

		if(empty($itemExists)){
			$errorValue = $excelValue[$valueObject];
			array_push($this->errors, "Line $lineItem : $errorColumn with value $errorValue is not found in submaster.");
		}

		return $itemExists;
	}

	public function editItems(Request $request){

		$cnt_success = 0;
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
			$dataExcel = Excel::load($path, function($reader) {})->get()->toArray();
	
			$unMatch = [];
			$header = config('excel-template.gashapon-item-master-edit');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}
			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			if ($dataExcel == null) {
				return back()->with('error_import', 'Failed ! Empty Excel');
			}
			if(!empty($dataExcel)) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					
					$nullItems = array_filter($value, function ($obj) {
						return $obj == null;
					});
					
					if(!empty($nullItems)){
						$nullColumns = strtoupper(str_replace('_',' ',array_keys($nullItems)[0]));
						array_push($this->errors, "Line $line_item : $nullColumns is blank!");
					}
					
					$digits_code = preg_replace("/[^A-Za-z0-9 ]/", '', $value['digits_code']);
					$item = GachaItemApproval::where('digits_code', $digits_code)->first();
					
					if ($item->approval_status_acct == 202) {
						array_push($this->errors, "Digits Code $digits_code on line $line_item has a pending status.");
					}
				}
				if(empty($this->errors)){
				foreach ($dataExcel as $key => $value) { 
				

					$digits_code = preg_replace("/[^A-Za-z0-9 ]/", '', $value['digits_code']);
					$item = GachaItemApproval::where('digits_code', $digits_code)->first();
					
					$current_srp = $item->current_srp;
				
					$new_lc_margin_per_pc = (($current_srp - $value['lc_per_pc']) / $current_srp  * 100);
					$new_sc_margin_per_pc = (($current_srp - $value['sc_per_pc']) / $current_srp  * 100);

					
					$data = [
						'approval_status_acct' => 202,
						'lc_per_carton' => $value['lc_per_carton'],
						'lc_per_pc' => $value['lc_per_pc'],
						'lc_margin_per_pc' => $new_lc_margin_per_pc,
						'store_cost' => $value['sc_per_pc'],
						'sc_margin' => $new_sc_margin_per_pc,
						'supplier_cost' => $value['supplier_cost'],
						'updated_by' => CRUDBooster::myId(),
						'updated_at' => date('Y-m-d H:i:s'),
					];
							$cnt_success++;
							$approval_item = GachaItemApproval::where('digits_code', $digits_code);
							$approval_item->update($data);
				}
			}

			}
		}

		if(empty($this->errors)){
			return back()->with('success_import', "Success ! $cnt_success item(s) added to pending items for accounting approval.");
		}
		else{
			return back()->with('error_import', implode("<br>", $this->errors));
		}
    }


    public function importItemTemplate(){
		Excel::create('gashapon-item-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('item', function ($sheet) {
			$sheet->row(1, config('excel-template.gashapon-item-master'));
			});
		})->download('csv');
    }

    public function importItemEditTemplate(){
		Excel::create('gashapon-item-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('item', function ($sheet) {
			$sheet->row(1, config('excel-template.gashapon-item-master-edit'));
			});
		})->download('csv');
    }
}