<?php

namespace App\Http\Controllers;

use App\Segmentation;
use App\ItemMaster;
use App\SkuLegend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Excel;

class SegmentationController extends Controller
{
    public function importTemplate(){
		$segmentations = Segmentation::active()->get();
		Excel::create('skulegend-segmentation-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) use ($segmentations) {
			$excel->sheet('skulegend', function ($sheet) use ($segmentations) {
			    $headers = array('DIGITS CODE', 'SKU LEGEND');
			    $lines = array('80000001', 'CORE');
			    foreach($segmentations as $segmentation){
			        array_push($headers, $segmentation->segmentation_description);
			        array_push($lines, 'CORE');
			    }
				$sheet->row(1, $headers);
				$sheet->row(2, $lines);
			});
		})->download('csv');
    }

    public function importSKULegendSegmentation(Request $request)
	{
	    ini_set('memory_limit', '-1');
		$errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
		$segmentations = Segmentation::active()->get();
		$skulegends = SkuLegend::active()->get();
			
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
			$header = array('DIGITS CODE', 'SKU LEGEND');
            foreach($segmentations as $segmentation){
		        array_push($header, $segmentation->segmentation_description);
		    }
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

					$skulegend = $skulegends->where('sku_legend_description',$value->sku_legend)->first();
					foreach($segmentations as $segmentation){
        		        $segment = strtolower(str_replace(" ","_",$segmentation->segmentation_description));
        		        $sku_segment = $skulegends->where('sku_legend_description', $value->$segment)->first();
        		        
        		        if(empty($sku_segment)){
                            $segmentColumn = $segmentation->segmentation_description;
                            $segmentValue = $value->$segment;
    						array_push($errors, "Line $line_item : with segmentation $segmentValue at column $segmentColumn not found in submaster.");
    					}
    					else{
							$data[$segmentation->segmentation_column] = $value->$segment;
						}
    				    
        		    }

					if(empty($skulegend)){
                        $skuLegendValue = $value->sku_legend;
						array_push($errors, "Line $line_item : with sku legend $skuLegendValue not found in submaster.");
					}
					else{
						$data['sku_legends_id'] = $skulegend->id;
					}
					
					try {
                        $cnt_success++;
                        ItemMaster::where('digits_code', intval($value->digits_code))->update($data);
					} catch (\Exception $e) {
						$cnt_fail++;
						array_push($errors, "Line $line_item : with error ".json_encode($e));
					}
				}
			}
		}

		if(empty($errors)){
			return back()->with('success_import', "Success ! $cnt_success item(s) were updated successfully.");
		}
		else{
			return back()->with('error_import', "$cnt_success item(s) were updated successfully but with errors at : <br>". implode("<br>", $errors));
		}
		
	}
}
