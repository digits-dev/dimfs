<?php

namespace App\Http\Controllers;

use App\ItemMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class EcomController extends Controller
{
    public function importECOMTemplate()
	{
		Excel::create('ecom-details-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('ecom', function ($sheet) {
				$sheet->row(1, array('DIGITS CODE', 'LENGTH','WIDTH','HEIGHT','WEIGHT'));
				$sheet->row(2, array('80000001', '1.25','1.25','1.25','1.25'));
			});
		})->download('csv');
	}
	
	public function importECOM(Request $request)
	{
	    $errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
			
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
			$header = array('DIGITS CODE','LENGTH','WIDTH','HEIGHT','WEIGHT');

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
					
					$existingItem = ItemMaster::where('digits_code', intval($value->digits_code))->first();
					
					if(empty($existingItem)){
						array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
					}
					
					if(empty($value->length)){
						array_push($errors, 'Line '.$line_item.': blank item length.');
					}
					
					if(empty($value->width)){
						array_push($errors, 'Line '.$line_item.': blank item width.');
					}
					
					if(empty($value->height)){
						array_push($errors, 'Line '.$line_item.': blank item height.');
					}
					
					if(empty($value->weight)){
						array_push($errors, 'Line '.$line_item.': blank item weight.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->length, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with length: "'.$value->length.'" should have 2 decimal places only.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->width, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with width: "'.$value->width.'" should have 2 decimal places only.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->height, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with height: "'.$value->height.'" should have 2 decimal places only.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->weight, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with weight: "'.$value->weight.'" should have 2 decimal places only.');
					}
					
					else{
					    $data["item_length"] = number_format($value->length, 2, '.', '');
					    $data["item_width"] = number_format($value->width, 2, '.', '');
					    $data["item_height"] = number_format($value->height, 2, '.', '');
					    $data["item_weight"] = number_format($value->weight, 2, '.', '');
					}

					try {
						if(empty($errors)){
							$cnt_success++;
							ItemMaster::where('digits_code', intval($value->digits_code))->update($data);
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
}
