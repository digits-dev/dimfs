<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use CRUDBooster;
use Carbon\Carbon;
use Excel;
use App\ItemPriceChangeApproval;
use App\ItemMaster;
use App\MarginCategory;
use App\VendorType;
use App\MarginMatrix;
use Illuminate\Support\Facades\Input;

class AccountingUploadController extends \crocodicstudio\crudbooster\controllers\CBController
{
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
    
    public function importAccountingTemplate()
	{
	    Excel::create('acctg-update-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('dimfs', function ($sheet) {
				$sheet->row(1, 
					array(
						'DIGITS CODE',
						'STORE COST',
                        'ECOM STORE COST',
						'LANDED COST',
						'AVERAGE LANDED COST',
						'LANDED COST VIA SEA',
						'WORKING STORE COST',
                        'WORKING ECOM STORE COST',
						'WORKING LANDED COST',
						'EFFECTIVE DATE'
						)
					);
		});

		})->download('csv');
	}
	
	public function importAccountingEdit(Request $request) 
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
			
			$dataExcelEffectiveDateRaw = Excel::load($path, function($reader) {
            	$reader->select(array('effective_date'));
            })->get()->toArray();
            
            $dataExcelEffectiveDate = collect($dataExcelEffectiveDateRaw)->unique()->values()->all();
			
			$unMatch = [];
			$header = array(
				'DIGITS CODE',
				'STORE COST',
                'ECOM STORE COST',
				'LANDED COST',
				'AVERAGE LANDED COST',
				'LANDED COST VIA SEA',
				'WORKING STORE COST',
                'WORKING ECOM STORE COST',
				'WORKING LANDED COST',
				'EFFECTIVE DATE');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			foreach($dataExcelEffectiveDate as $key => $value){
			 //   if(is_null($value['effective_date'])){
			 //       return back()->with('error_import', 'Failed ! Please check effective date should be recent or future date.');
			 //   }
			 //   if (Carbon::parse($value['effective_date'])->lt(Carbon::now())){
			 //       return back()->with('error_import', 'Failed ! Please check effective date should be recent or future date.');
			 //   }
			}
			
			if(!empty($dataExcel) && $dataExcel->count()) {
			    
                $data = array();
       
                
                foreach ($dataExcel as $key => $value) {
                    $update_data=array();
                    $line_item = 0;	
                    $line_item = $key+1;
                    $itemDetails = ItemMaster::where('digits_code', $value->digits_code)->first();
                    $marginCategory = MarginCategory::where('id', $itemDetails->margin_categories_id)->first();
                    $vendorType = VendorType::where('id', $itemDetails->vendor_types_id)->first();
                    $storeCostPercentage = self::setStoreCostUploadPercentage($value, $itemDetails);
                    
                    // EDITED BY LEWIE
                    $EcomStoreCostPercentage = self::setEcomStoreCostUploadPercentage($value, $itemDetails);
                    // -----------------
         
                    $workingStoreCostPercentage = self::setWorkingStoreCostUploadPercentage($value, $itemDetails);
                    
                    // ADDED BY LEWIE
                    $workingEcomStoreCostPercentage = self::setWorkingEcomStoreCostUploadPercentage($value, $itemDetails);
                    //***************************************************** */

                    if(empty($itemDetails)){
						array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
					}
					if(empty($value->store_cost) || empty($value->landed_cost)){
					    array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has empty store cost / landed cost.');
					}
                    if(!empty($value->working_store_cost) && !empty($value->working_landed_cost) && !empty($value->working_ecom_store_cost)) {
                        $working_store_cost = self::setWorkingStoreCostUploadPercentage($value, $itemDetails);
                        
                        if($marginCategory->margin_category_description == "UNITS"){
                            $checkUntWCost = self::checkUntWorkingStoreCost($value, $itemDetails);
                            if($checkUntWCost == 1){
                                array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" check store cost.');
                            }
                        }elseif($marginCategory->margin_category_description == "ACCESSORIES"){
                        
                            $checkAccWCost = self::checkAccWorkingStoreCost($value, $itemDetails, $storeCostPercentage);
                            if($checkAccWCost == 1){
                                array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" check store cost.');
                            }
                            
                            // ADDED BY LEWIE 
                            $checkEcomAccWCost = self::checkAccEcomWorkingStoreCost($value, $itemDetails, $EcomStoreCostPercentage);
                            if($checkEcomAccWCost == 1){
                                array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" check store cost.');
                            }
                            //**************************************************************************************** */
                        }
                    }
                    
                    $vendor_type = ["LOC-CON","LOC-OUT","LR-CON","LR-OUT"];
                    
                    if(in_array($vendorType->vendor_type_code,$vendor_type)){
                        $checkLocalCost = self::checkLocalStoreCost($value, $itemDetails);
                        if($checkLocalCost == 1){
                            array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" check store cost.');
                        }
                    }
                    elseif($marginCategory->margin_category_description == "UNITS"){
                        
                        $checkUntCost = self::checkUntStoreCost($value, $itemDetails);
                        if($checkUntCost == 1){
                            array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" check store cost.');
                        }
                        
                    }
                    
                    elseif($marginCategory->margin_category_description == "ACCESSORIES"){
                        
                        $checkAccCost = self::checkAccStoreCost($value, $itemDetails, $storeCostPercentage);
                        if($checkAccCost == 1){
                            array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" check store cost.');
                        }
                    }
        
                    
                    if(empty($errors)){
                        $update_data = [
                        'item_masters_id'   => $itemDetails->id,
                        'digits_code'       => intval($value->digits_code),
                        'brands_id'         => $itemDetails->brands_id,
                        'categories_id'     => $itemDetails->categories_id,
                        'margin_categories_id'  => $itemDetails->margin_categories_id,
                        'current_srp'       => $itemDetails->current_srp,
                        'promo_srp'         => $itemDetails->promo_srp,
                        'store_cost'        => $value->store_cost,
                        'store_cost_percentage' => $storeCostPercentage,
                        'landed_cost'       => $value->landed_cost,
                        'actual_landed_cost'       => $value->average_landed_cost,
                        'landed_cost_sea'       => $value->landed_cost_via_sea,
                        'working_store_cost'    => $value->working_store_cost,
                        'working_store_cost_percentage' => $workingStoreCostPercentage,
                        'working_landed_cost'   => $value->working_landed_cost,
                        'duration_from'     => $itemDetails->duration_from,
                        'duration_to'       => $itemDetails->duration_to,
                        'support_types_id'  => $itemDetails->support_types_id,
                        'approval_status'       => $this->pending,
                        'encoder_privileges_id' => CRUDBooster::myPrivilegeId(),
                        'updated_by'            => CRUDBooster::myId(),
                        'approval_status'       => 1,
                        'effective_date'       =>  Carbon::parse($value->effective_date)->format('Y-m-d'),
                        'created_at'            => date('Y-m-d H:i:s'),
                        'ecom_store_margin'     => $value->ecom_store_cost,                      // ADDED BY LEWIE
                        'ecom_store_margin_percentage' => $EcomStoreCostPercentage,              // ADDED BY LEWIE
                        'working_ecom_store_margin'     => $value->working_ecom_store_cost,              // ADDED BY LEWIE
                        'working_ecom_store_margin_percentage' => $workingEcomStoreCostPercentage, // ADDED BY LEWIE
                    ];
                        // array_filter($update_data, 'strlen')
                        array_push($data, $update_data);
                        $cnt_success++;
                    }
                }
                
                if(empty($errors)){
                    ItemPriceChangeApproval::insert($data);
                }
				
			}
		}
		
		if(empty($errors)){
			return redirect('/admin/item_masters/import-acctg-view')->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
		}
		else{
			return redirect('/admin/item_masters/import-acctg-view')->with('error_import', implode("<br>", $errors));
		}
	}
	
	public static function getComputedUploadMarginPercentage($margin_percentage, $margin_categories_id, $margin_category, $brand){
    
        $marginMatrix = MarginMatrix::where('margin_category',$margin_category)
            ->whereRaw($margin_percentage.' between `min` and `max`')
            // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
            // ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
            ->where('matrix_type','ADD TO LC')
            ->where('brands_id', $brand)
            ->where('status','ACTIVE')
            ->where('margin_categories_id','LIKE', '%'.$margin_categories_id.'%')->first();
        //whereRaw('? between min and max', [$margin_percentage])
        if(empty($marginMatrix)){
            return MarginMatrix::where('margin_category',$margin_category)
            ->whereRaw($margin_percentage.' between `min` and `max`')
            // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
            // ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
            ->where('matrix_type','ADD TO LC')
            ->where('status','ACTIVE')
            ->whereNull('brands_id')
            ->where('margin_categories_id','LIKE', '%'.$margin_categories_id.'%')->first();
        }
        else{
            return $marginMatrix;
        }
    
    }
    
    public static function getComputedLocalMarginPercentage($margin_percentage, $vendor_type_id, $brands){
        
        DB::enableQueryLog();
        
        $matrix = DB::table('margin_matrices')
            // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
            // ->whereRaw('min <= (? + 0.0)',[$margin_percentage])
            ->whereRaw($margin_percentage.' between `min` and `max`')
            ->where('matrix_type','ADD TO LC')
            ->where('vendor_types_id', $vendor_type_id)
            ->where('brands_id',$brands)
            ->where('status','ACTIVE')->first();   
        \Log::debug(DB::getQueryLog()); 
        if(empty($matrix)){
            \Log::debug("----");
            return DB::table('margin_matrices')
                // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
                // ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
                ->whereRaw($margin_percentage.' between `min` and `max`')
                ->where('matrix_type','ADD TO LC')
                ->where('vendor_types_id', $vendor_type_id)
                ->whereNull('brands_id')
                ->where('status','ACTIVE')->first();
            
        }
        \Log::debug(DB::getQueryLog());  
        return $matrix;
    
    }
    
    public static function getComputedMarginPercentage($margin_percentage, $margin_categories_id, $margin_category, $brand){
	    
	    $marginMatrix = MarginMatrix::where('margin_category',$margin_category)
	        ->whereRaw($margin_percentage.' between `min` and `max`')
	        // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
            // ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
            ->where('brands_id', $brand)->first();
        
        if(empty($marginMatrix)){
            return MarginMatrix::where('margin_category',$margin_category)
            ->whereRaw($margin_percentage.' between `min` and `max`')
            // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
            // ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
            ->whereNull('brands_id')->first();
        }
        else{
            return $marginMatrix;
        }
		
	}
	
      // ADDED BY LEWIE
      public static function getComputedEcomMarginPercentage($margin_percentage, $margin_categories_id, $margin_category, $brand){
	    
	    $marginMatrix = DB::table('ecom_margin_matrices')->whereRaw($margin_percentage.' between `min` and `max`')
	       // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
        //     ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
            ->where('margin_category',$margin_category)
            ->where('brands_id', $brand)->first();
        
        if(empty($marginMatrix)){
            return DB::table('ecom_margin_matrices')->whereRaw($margin_percentage.' between `min` and `max`')
            // ->whereRaw('max >= (? + 0.0)',[$margin_percentage])
            // ->whereRaw('min >= (? + 0.0)',[$margin_percentage])
            ->where('margin_category',$margin_category)
            ->whereNull('brands_id')->first();
        }
        else{
            return $marginMatrix;
        }
	}
    //************************************************************************************************** */
	
	public static function setStoreCostUploadPercentage($upload_values, $item_details){
		$csm_percentage = 0.0000;

        if(empty($item_details->promo_srp)) {
            $csm_percentage = ($item_details->current_srp - $upload_values->store_cost)/$item_details->current_srp;
        }
        
        if(!empty($item_details->promo_srp)){
            $csm_percentage = ($item_details->promo_srp - $upload_values->store_cost)/$item_details->promo_srp;
            
        }
        
        return number_format($csm_percentage , 4, '.', '');
        
	}

    // ADDED BY LEWIE
    public static function setEcomStoreCostUploadPercentage($upload_values, $item_details){
        $csm_percentage = 0.0000;
        if(empty($item_details->promo_srp)) {
            $csm_percentage = ($item_details->current_srp - $upload_values->ecom_store_cost)/$item_details->current_srp;
        }
        
        if(!empty($item_details->promo_srp)){
            $csm_percentage = ($item_details->promo_srp - $upload_values->ecom_store_cost)/$item_details->promo_srp;
            
        }
        
        return number_format($csm_percentage , 4, '.', '');
    }
    //******************************************************** */
      
	public static function setWorkingStoreCostUploadPercentage($upload_values, $item_details){
		$cwsm_percentage = 0.0000;
        
		if(empty($item_details->promo_srp)) {
            $cwsm_percentage = ($item_details->current_srp - $upload_values->working_store_cost)/$item_details->current_srp;
        }
        
        if(!empty($item_details->promo_srp)){
            $cwsm_percentage = ($item_details->promo_srp - $upload_values->working_store_cost)/$item_details->promo_srp;
            
        }
		
		return number_format($cwsm_percentage,4, '.', '');
	}
	
    // ADDED BY LEWIE
    public static function setWorkingEcomStoreCostUploadPercentage($upload_values, $item_details){
		$cwsm_percentage = 0.0000;
        
		if(empty($item_details->promo_srp)) {
            $cwsm_percentage = ($item_details->current_srp - $upload_values->working_ecom_store_cost)/$item_details->current_srp;
        }
        
        if(!empty($item_details->promo_srp)){
            $cwsm_percentage = ($item_details->promo_srp - $upload_values->working_ecom_store_cost)/$item_details->promo_srp;
            
        }
		
		return number_format($cwsm_percentage,4, '.', '');
	}
    //************************************************************** */
	
	public static function checkUntStoreCost($upload_values, $item_details){
	    
	    if(empty($item_details->promo_srp)){
			$csm = ($item_details->current_srp - $upload_values->store_cost) / $item_details->current_srp; //sm%
            $ccsm = self::getComputedUploadMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "UNITS", $item_details->brands_id);
            if(number_format($csm,7, '.', '') < 0){
				return 1;
			}
// 			$com_sc = (1 + (number_format($ccsm->store_margin_percentage,4, '.', '')) * $upload_values->landed_cost);
// 			if(number_format($com_sc,2, '.', '') != number_format($upload_values->store_cost,2, '.', '')){
// 			    return 1;
// 			}
		}
		else{
			$csm = ($item_details->promo_srp - $upload_values->store_cost) / $item_details->promo_srp; 
			$ccsm = self::getComputedUploadMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "UNITS", $item_details->brands_id);
            if(number_format($csm,7, '.', '') < 0){
				return 1;
			}
// 			$com_sc = (1 + (number_format($ccsm->store_margin_percentage,4, '.', '')) * $upload_values->landed_cost);
// 			if(number_format($com_sc,2, '.', '') != number_format($upload_values->store_cost,2, '.', '')){
// 			    return 1;
// 			}
		}
		return 0;
	}
	
	public static function checkLocalStoreCost($upload_values, $item_details){
	    
	    if(empty($item_details->promo_srp) || is_null($item_details->promo_srp)){
	        \Log::debug('test item: '.$item_details->digits_code);
	        \Log::debug('test lc: '.$upload_values->landed_cost);
	        \Log::debug('test brand: '.$item_details->brands_id);
	        $srplc = (floatval($item_details->current_srp) - floatval($upload_values->landed_cost));
			$csm = $srplc/floatval($item_details->current_srp);
			\Log::debug('test im: '.$csm);
            $ccsm = self::getComputedLocalMarginPercentage(number_format($csm,4, '.', ''), $item_details->vendor_types_id,$item_details->brands_id);
            if(number_format($csm,7, '.', '') < 0){
				return 1;
			}
			
			\Log::debug('test mm: '.$ccsm->store_margin_percentage);
            $addToLC = 1+(number_format($ccsm->store_margin_percentage,4, '.', ''));
            $com_sc = $addToLC * floatval($upload_values->landed_cost);
            \Log::debug('test sc: '.$com_sc);
			if(number_format($com_sc,2, '.', '') != number_format($upload_values->store_cost,2, '.', '')){
			    return 1;
			}
		}
		else{
		    
		    $srplc = (floatval($item_details->promo_srp) - floatval($upload_values->landed_cost));
			$csm = $srplc / floatval($item_details->promo_srp);
			
			$ccsm = self::getComputedLocalMarginPercentage(number_format($csm,4, '.', ''), $item_details->vendor_types_id,$item_details->brands_id);
            if(number_format($csm,7, '.', '') < 0){
				return 1;
			}
			
            $addToLC = 1+(number_format($ccsm->store_margin_percentage,4, '.', ''));
            $com_sc = $addToLC * floatval($upload_values->landed_cost);
            
			if(number_format($com_sc,2, '.', '') != number_format($upload_values->store_cost,2, '.', '')){
			    return 1;
			}
		}
		return 0;
	}
	
	public static function checkAccStoreCost($upload_values, $item_details, $dtp_rf_percentage){
	    
	    if(empty($item_details->promo_srp)){
		
			$csm = ($item_details->current_srp - $upload_values->landed_cost) / $item_details->current_srp;
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
			
			switch($ccsm->matrix_type){
			    case 'BASED ON MATRIX': {
			        if(number_format($ccsm->store_margin_percentage,4, '.', '') != $dtp_rf_percentage || number_format($csm,7, '.', '') < 0) {
        				return 1;
        			}
			    }
			    break;
			    
			    case 'ADD TO LC':{
			        $ccsm = self::getComputedUploadMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
            
        			$com_sc = (1 + (number_format($ccsm->store_margin_percentage,4, '.', '')) * $upload_values->landed_cost);
        			if(number_format($com_sc,2, '.', '') != number_format($upload_values->store_cost,2, '.', '')){
        			    return 1;
        			}
			    }
			    break;
			}
		}

		else{
		    
			$csm = ($item_details->promo_srp - $upload_values->landed_cost) / $item_details->promo_srp;
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
			
			switch($ccsm->matrix_type){
			    case 'BASED ON MATRIX': {
			        if(number_format($ccsm->store_margin_percentage,4, '.', '') != $dtp_rf_percentage || number_format($csm,7, '.', '') < 0) {
        				return 1;
        			}
			    }
			    break;
			    
			    case 'ADD TO LC':{
			        $ccsm = self::getComputedUploadMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
            
        			$com_sc = (1 + (number_format($ccsm->store_margin_percentage,4, '.', '')) * $upload_values->landed_cost);
        			if(number_format($com_sc,2, '.', '') != number_format($upload_values->store_cost,2, '.', '')){
        			    return 1;
        			}
			    }
			    break;
			}
		}
		return 0;
	}
	
	public static function checkUntWorkingStoreCost($upload_values, $item_details){
	    if(in_array($item_details->vendor_types_id, [3,4,5,6])){
            return 0;
        }
	    if(empty($item_details->promo_srp)){
			$csm = ($item_details->current_srp - $upload_values->working_store_cost) / $item_details->current_srp;
            $ccsm = self::getComputedUploadMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "UNITS", $item_details->brands_id);
            if(number_format($csm,7, '.', '') < 0){
				return 1;
			}
// 			$com_sc = (1 + (number_format($ccsm->store_margin_percentage,4, '.', '')) * $upload_values->working_landed_cost);
// 			if(number_format($com_sc,2, '.', '') != number_format($upload_values->working_store_cost,2, '.', '')){
// 			    return 1;
// 			}
		}
		else{
			$csm = ($item_details->promo_srp - $upload_values->working_store_cost) / $item_details->promo_srp;
			$ccsm = self::getComputedUploadMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "UNITS", $item_details->brands_id);
            if(number_format($csm,7, '.', '') < 0){
				return 1;
			}
// 			$com_sc = (1 + (number_format($ccsm->store_margin_percentage,4, '.', '')) * $upload_values->working_landed_cost);
// 			if(number_format($com_sc,2, '.', '') != number_format($upload_values->working_store_cost,2, '.', '')){
// 			    return 1;
// 			}
		}
		return 0;
	}
	
	public static function checkAccWorkingStoreCost($upload_values, $item_details, $dtp_rf_percentage){
	    if(in_array($item_details->vendor_types_id, [3,4,5,6])){
            return 0;
        }
	    if(empty($item_details->promo_srp)){
		
			$csm = ($item_details->current_srp - $upload_values->working_landed_cost) / $item_details->current_srp;
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
			
			if(number_format($ccsm->store_margin_percentage,4, '.', '') != $dtp_rf_percentage || number_format($csm,7, '.', '') < 0) {
				return 1;
			}
		}

		else{
		    
			$csm = ($item_details->promo_srp - $upload_values->working_landed_cost) / $item_details->promo_srp;
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
			
			if(number_format($ccsm->store_margin_percentage,4, '.', '') != $dtp_rf_percentage || number_format($csm,7, '.', '') < 0) {
				return 1;
			}
		}
		return 0;
	}
    
    // ADDED BY LEWIE
    public static function checkAccEcomWorkingStoreCost($upload_values, $item_details, $EcomStoreCostPercentage){
	    if(in_array($item_details->vendor_types_id, [3,4,5,6])){
            return 0;
        }
	    if(empty($item_details->promo_srp)){
		
			$csm = ($item_details->current_srp - $upload_values->working_landed_cost) / $item_details->current_srp;
			$ccsm = self::getComputedEcomMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
			
			if(number_format($ccsm->ecom_store_margin_percentage,4, '.', '') != $EcomStoreCostPercentage || number_format($csm,7, '.', '') < 0) {
				return 1;
			}
		}

		else{
		    
			$csm = ($item_details->promo_srp - $upload_values->working_landed_cost) / $item_details->promo_srp;
			$ccsm = self::getComputedEcomMarginPercentage(number_format($csm,4, '.', ''), $item_details->margin_categories_id, "ACCESSORIES", $item_details->brands_id);
			
			if(number_format($ccsm->ecom_store_margin_percentage,4, '.', '') != $EcomStoreCostPercentage || number_format($csm,7, '.', '') < 0) {
				return 1;
			}
		}
		return 0;
	}
    //*********************************************************************** */
}
