<?php

namespace App\Http\Controllers;

use App\ItemMaster;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class WrrController extends Controller
{
    public function importWRRTemplate()
	{
		Excel::create('wrr-date-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('wrr', function ($sheet) {
				$sheet->row(1, array('DIGITS CODE','LATEST WRR DATE'));
				$sheet->row(2, array('80000001','yyyy-mm-dd'));
			});
		})->download('csv');
	}

    public function importWRR(Request $request)
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
			$header = array('DIGITS CODE','LATEST WRR DATE');

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
						array_push($errors, "Line $line_item : with digits code $value->digits_code not found in item master");
					}

					if(is_null($value->latest_wrr_date)){
						array_push($errors, "Line $line_item : with digits code $value->digits_code has blank wrr date");
					}

					$dateObj = DateTime::createFromFormat("Y-m-d", $value->latest_wrr_date);
					if (!$dateObj){
						array_push($errors, "Line $line_item : could not parse latest wrr date $value->latest_wrr_date");
						// throw new \UnexpectedValueException("Could not parse the date: $value->latest_wrr_date");
					}

					if(empty($existingItem->initial_wrr_date) || is_null($existingItem->initial_wrr_date)){
						$data = [
							'initial_wrr_date' => date('Y-m-d', strtotime((string)$value->latest_wrr_date)),
							'latest_wrr_date' => date('Y-m-d', strtotime((string)$value->latest_wrr_date))
						];
					}
					else{
						$data = self::getLatestWRRDate($value->digits_code, $value->latest_wrr_date);
					}

					try {
						if(empty($errors)){
							$cnt_success++;
							ItemMaster::where('digits_code', intval($value->digits_code))->update($data);
						}
						
					} catch (Exception $e) {
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

    public function getLatestWRRDate($digits_code, $latest_wrr_date)
	{
		$data = array();
		$existingItemLatestWRR = ItemMaster::where('digits_code', intval($digits_code))->value('latest_wrr_date');
		$first = new Carbon((string)$existingItemLatestWRR);
		$second = new Carbon((string)$latest_wrr_date);
		
		if($first->gte($second)){
			$data = [ 'latest_wrr_date' => $existingItemLatestWRR ];
		}
		elseif(!is_null($latest_wrr_date)){
			$data = [ 'latest_wrr_date' => date('Y-m-d', strtotime((string)$latest_wrr_date)) ];
		}
		else{
			$data = [ 'latest_wrr_date' => $existingItemLatestWRR ];
		}
		return $data;
	}
}
