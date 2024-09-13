<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\ItemPriceChangeApproval;
use App\RmaItemMaster;
use App\MarginCategory;
use App\VendorType;
use App\MarginMatrix;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RmaUploadController extends Controller
{
    public function downloadRmaImportTemplate()
	{
	    Excel::create('rma-update-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
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

    public function importRma(Request $request)
    {
        $errors = [];
        $cnt_success = 0;
        $cnt_fail = 0;

        $file = $request->file('import_file')->getRealPath();

        $extension = $request->file('import_file')->getClientOriginalExtension();
        if ($extension === 'csv') {
            // Handle CSV file
            $csvData = array_map('str_getcsv', file($file));
            $header = array_shift($csvData);
            $dataArray = $csvData;
        } else {
            // Handle Excel file
            $data = Excel::load($file, function ($reader) {})->get();
            $header = array_keys($data->first()->toArray());
            $dataArray = $data->toArray();
        }

        $dataToInsert = [];

        // Process each row
        foreach ($dataArray as $key => $row) {
            if (is_array($row) && isset($row[0])) {
                $row = array_combine($header, $row);
            }

            $itemDetails = RmaItemMaster::where('digits_code', $row['DIGITS CODE'])->first();
            $marginCategory = MarginCategory::where('id', $itemDetails->rma_margin_categories_id)->first();
            $vendorType = VendorType::where('id', $itemDetails->vendor_types_id)->first();

            if (!$itemDetails) {
                CRUDBooster::redirect('/admin/rma_item_masters/import-rma-view', trans('Line ' . ($key + 1) . ' Digits Code: ' . $row['DIGITS CODE'] . ' not found in RMA item master'), 'danger');
                $cnt_fail++;
                continue;
            }

            if (empty($row['STORE COST']) || empty($row['LANDED COST'])) {
                CRUDBooster::redirect('/admin/rma_item_masters/import-rma-view', trans('Line ' . ($key + 1) . ' Digits Code: ' . $row['DIGITS CODE'] . ' Has empty value on STORE COST / LANDED COST.'), 'danger');
                $cnt_fail++;
                continue;
            }

            $storeCostPercentage = self::setStoreCostUploadPercentage($row, $itemDetails);
            $EcomStoreCostPercentage = self::setEcomStoreCostUploadPercentage($row, $itemDetails);
            $workingStoreCostPercentage = self::setWorkingStoreCostUploadPercentage($row, $itemDetails);
            $workingEcomStoreCostPercentage = self::setWorkingEcomStoreCostUploadPercentage($row, $itemDetails);

            if (!empty($row['WORKING STORE COST']) && !empty($row['WORKING LANDED COST']) && !empty($row['WORKING ECOM STORE COST'])) {
                $working_store_cost = self::setWorkingStoreCostUploadPercentage($row, $itemDetails);

                if ($marginCategory->margin_category_description == "UNITS") {
                    $checkUntWCost = self::checkUntWorkingStoreCost($row, $itemDetails);
                    if ($checkUntWCost == 1) {
                        array_push($errors, 'Line ' . ($key + 1) . ': with digits code "' . $row['DIGITS CODE'] . '" check store cost.');
                    }
                } elseif ($marginCategory->margin_category_description == "ACCESSORIES") {

                    $checkAccWCost = self::checkAccWorkingStoreCost($row, $itemDetails, $storeCostPercentage);
                    if ($checkAccWCost == 1) {
                        array_push($errors, 'Line ' . ($key + 1) . ': with digits code "' . $row['DIGITS CODE'] . '" check store cost.');
                    }

                    // ADDED BY LEWIE 
                    $checkEcomAccWCost = self::checkAccEcomWorkingStoreCost($row, $itemDetails, $EcomStoreCostPercentage);
                    if ($checkEcomAccWCost == 1) {
                        array_push($errors, 'Line ' . ($key + 1) . ': with digits code "' . $row['DIGITS CODE'] . '" check store cost.');
                    }
                }
            }

            $vendor_type = ["LOC-CON", "LOC-OUT", "LR-CON", "LR-OUT"];

            if (in_array($vendorType->vendor_type_code, $vendor_type)) {
                $checkLocalCost = self::checkLocalStoreCost($row, $itemDetails);
                if ($checkLocalCost == 1) {
                    array_push($errors, 'Line ' . ($key + 1) . ': with digits code "' . $row['DIGITS CODE'] . '" check store cost.');
                }
            } elseif ($marginCategory->margin_category_description == "UNITS") {

                $checkUntCost = self::checkUntStoreCost($row, $itemDetails);
                if ($checkUntCost == 1) {
                    array_push($errors, 'Line ' . ($key + 1) . ': with digits code "' . $row['DIGITS CODE'] . '" check store cost.');
                }
            } elseif ($marginCategory->margin_category_description == "ACCESSORIES") {

                $checkAccCost = self::checkAccStoreCost($row, $itemDetails, $storeCostPercentage);
                if ($checkAccCost == 1) {
                    array_push($errors, 'Line ' . ($key + 1) . ': with digits code "' . $row['DIGITS CODE'] . '" check store cost.');
                }
            }

            $update_data = [
                'rma_item_masters_id' => $itemDetails->id,
                'digits_code' => intval($row['DIGITS CODE']),
                'brands_id' => $itemDetails->brands_id,
                'categories_id' => $itemDetails->rma_categories_id,
                'margin_categories_id' => $itemDetails->rma_margin_categories_id,
                'current_srp' => $itemDetails->current_srp,
                'promo_srp' => $itemDetails->promo_srp,
                'store_cost' => $row['STORE COST'],
                'store_cost_percentage' => $storeCostPercentage,
                'landed_cost' => $row['LANDED COST'],
                'actual_landed_cost' => $row['AVERAGE LANDED COST'],
                'landed_cost_sea' => $row['LANDED COST VIA SEA'],
                'working_store_cost' => $row['WORKING STORE COST'],
                'working_store_cost_percentage' => $workingStoreCostPercentage,
                'working_landed_cost' => $row['WORKING LANDED COST'],
                // 'duration_from' => $itemDetails->duration_from,
                // 'duration_to' => $itemDetails->duration_to,
                // 'support_types_id' => $itemDetails->support_types_id,
                'approval_status' => 1,
                'encoder_privileges_id' => CRUDBooster::myPrivilegeId(),
                'updated_by' => CRUDBooster::myId(),
                'effective_date' => Carbon::parse($row['EFFECTIVE DATE'])->format('Y-m-d'),
                'created_at' => now(),
                'ecom_store_margin' => $row['ECOM STORE COST'],
                'ecom_store_margin_percentage' => $EcomStoreCostPercentage,
                'working_ecom_store_margin' => $row['WORKING ECOM STORE COST'],
                'working_ecom_store_margin_percentage' => $workingEcomStoreCostPercentage,
            ];


            array_push($dataToInsert, $update_data);
            $cnt_success++;
        }

        // Insert data if no errors
        if (empty($errors)) {

            ItemPriceChangeApproval::insert($dataToInsert);
        }

        if (empty($errors)) {
            CRUDBooster::redirect('/admin/rma_item_masters/import-rma-view  ', trans($cnt_success . " RMA items were updated successfully!"), 'success');
        } else {
            CRUDBooster::redirect('/admin/rma_item_masters/import-rma-view  ', trans($errors), 'danger');
        }
    }

    public static function setStoreCostUploadPercentage($rows, $item_details)
    {
        $csm_percentage = 0.0000;

        if (empty($item_details->promo_srp)) {
            $csm_percentage = ($item_details->current_srp - $rows['STORE COST']) / $item_details->current_srp;
        }

        if (!empty($item_details->promo_srp)) {
            $csm_percentage = ($item_details->promo_srp - $rows['STORE COST']) / $item_details->promo_srp;
        }

        return number_format($csm_percentage, 4, '.', '');
    }

    public static function setEcomStoreCostUploadPercentage($rows, $item_details)
    {
        $csm_percentage = 0.0000;
        if (empty($item_details->promo_srp)) {
            $csm_percentage = ($item_details->current_srp - $rows['ECOM STORE COST']) / $item_details->current_srp;
        }

        if (!empty($item_details->promo_srp)) {
            $csm_percentage = ($item_details->promo_srp - $rows['ECOM STORE COST']) / $item_details->promo_srp;
        }

        return number_format($csm_percentage, 4, '.', '');
    }

    public static function setWorkingStoreCostUploadPercentage($rows, $item_details)
    {
        $cwsm_percentage = 0.0000;

        if (empty($item_details->promo_srp)) {
            $cwsm_percentage = ($item_details->current_srp - $rows['WORKING STORE COST']) / $item_details->current_srp;
        }

        if (!empty($item_details->promo_srp)) {
            $cwsm_percentage = ($item_details->promo_srp - $rows['WORKING STORE COST']) / $item_details->promo_srp;
        }

        return number_format($cwsm_percentage, 4, '.', '');
    }

    public static function setWorkingEcomStoreCostUploadPercentage($rows, $item_details)
    {
        $cwsm_percentage = 0.0000;

        if (empty($item_details->promo_srp)) {
            $cwsm_percentage = ($item_details->current_srp - $rows['WORKING ECOM STORE COST']) / $item_details->current_srp;
        }

        if (!empty($item_details->promo_srp)) {
            $cwsm_percentage = ($item_details->promo_srp - $rows['WORKING ECOM STORE COST']) / $item_details->promo_srp;
        }

        return number_format($cwsm_percentage, 4, '.', '');
    }

    public static function checkUntWorkingStoreCost($rows, $item_details)
    {
        if (in_array($item_details->vendor_types_id, [3, 4, 5, 6])) {
            return 0;
        }
        if (empty($item_details->promo_srp)) {
            $csm = ($item_details->current_srp - $rows['WORKING STORE COST']) / $item_details->current_srp;
            $ccsm = self::getComputedUploadMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "UNITS", $item_details->brands_id);
            if (number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        } else {
            $csm = ($item_details->promo_srp - $rows['WORKING STORE COST']) / $item_details->promo_srp;
            $ccsm = self::getComputedUploadMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "UNITS", $item_details->brands_id);
            if (number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        }
        return 0;
    }

    public static function checkAccWorkingStoreCost($rows, $item_details, $dtp_rf_percentage)
    {
        if (in_array($item_details->vendor_types_id, [3, 4, 5, 6])) {
            return 0;
        }
        if (empty($item_details->promo_srp)) {

            $csm = ($item_details->current_srp - $rows['WORKING LANDED COST']) / $item_details->current_srp;
            $ccsm = self::getComputedMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

            if (number_format($ccsm->store_margin_percentage, 4, '.', '') != $dtp_rf_percentage || number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        } else {

            $csm = ($item_details->promo_srp - $rows['WORKING LANDED COST']) / $item_details->promo_srp;
            $ccsm = self::getComputedMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

            if (number_format($ccsm->store_margin_percentage, 4, '.', '') != $dtp_rf_percentage || number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        }
        return 0;
    }

    public static function checkAccEcomWorkingStoreCost($rows, $item_details, $EcomStoreCostPercentage)
    {
        if (in_array($item_details->vendor_types_id, [3, 4, 5, 6])) {
            return 0;
        }
        if (empty($item_details->promo_srp)) {

            $csm = ($item_details->current_srp - $rows['WORKING LANDED COST']) / $item_details->current_srp;
            $ccsm = self::getComputedEcomMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

            if (number_format($ccsm->ecom_store_margin_percentage, 4, '.', '') != $EcomStoreCostPercentage || number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        } else {

            $csm = ($item_details->promo_srp - $rows['WORKING LANDED COST']) / $item_details->promo_srp;
            $ccsm = self::getComputedEcomMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

            if (number_format($ccsm->ecom_store_margin_percentage, 4, '.', '') != $EcomStoreCostPercentage || number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        }
        return 0;
    }

    public static function checkLocalStoreCost($rows, $item_details)
    {

        if (empty($item_details->promo_srp) || is_null($item_details->promo_srp)) {
            Log::debug('test item: ' . $item_details->digits_code);
            Log::debug('test lc: ' . $rows['LANDED COST']);
            Log::debug('test brand: ' . $item_details->brands_id);
            $srplc = (floatval($item_details->current_srp) - floatval($rows['LANDED COST']));
            $csm = $srplc / floatval($item_details->current_srp);
            Log::debug('test im: ' . $csm);
            $ccsm = self::getComputedLocalMarginPercentage(number_format($csm, 4, '.', ''), $item_details->vendor_types_id, $item_details->brands_id);
            if (number_format($csm, 7, '.', '') < 0) {
                return 1;
            }

            Log::debug('test mm: ' . $ccsm->store_margin_percentage);
            $addToLC = 1 + (number_format($ccsm->store_margin_percentage, 4, '.', ''));
            $com_sc = $addToLC * floatval($rows['LANDED COST']);
            Log::debug('test sc: ' . $com_sc);
            if (number_format($com_sc, 2, '.', '') != number_format($rows['STORE COST'], 2, '.', '')) {
                return 1;
            }
        } else {

            $srplc = (floatval($item_details->promo_srp) - floatval($rows['LANDED COST']));
            $csm = $srplc / floatval($item_details->promo_srp);

            $ccsm = self::getComputedLocalMarginPercentage(number_format($csm, 4, '.', ''), $item_details->vendor_types_id, $item_details->brands_id);
            if (number_format($csm, 7, '.', '') < 0) {
                return 1;
            }

            $addToLC = 1 + (number_format($ccsm->store_margin_percentage, 4, '.', ''));
            $com_sc = $addToLC * floatval($rows['LANDED COST']);

            if (number_format($com_sc, 2, '.', '') != number_format($rows['STORE COST'], 2, '.', '')) {
                return 1;
            }
        }
        return 0;
    }

    public static function checkUntStoreCost($rows, $item_details)
    {

        if (empty($item_details->promo_srp)) {
            $csm = ($item_details->current_srp - $rows['STORE COST']) / $item_details->current_srp; //sm%
            $ccsm = self::getComputedUploadMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "UNITS", $item_details->brands_id);
            if (number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        } else {
            $csm = ($item_details->promo_srp - $rows['STORE COST']) / $item_details->promo_srp;
            $ccsm = self::getComputedUploadMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "UNITS", $item_details->brands_id);
            if (number_format($csm, 7, '.', '') < 0) {
                return 1;
            }
        }
        return 0;
    }

    public static function checkAccStoreCost($rows, $item_details, $dtp_rf_percentage)
    {

        if (empty($item_details->promo_srp)) {

            $csm = ($item_details->current_srp - $rows['LANDED COST']) / $item_details->current_srp;
            $ccsm = self::getComputedMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

            switch ($ccsm->matrix_type) {
                case 'BASED ON MATRIX': {
                        if (number_format($ccsm->store_margin_percentage, 4, '.', '') != $dtp_rf_percentage || number_format($csm, 7, '.', '') < 0) {
                            return 1;
                        }
                    }
                    break;

                case 'ADD TO LC': {
                        $ccsm = self::getComputedUploadMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

                        $com_sc = (1 + (number_format($ccsm->store_margin_percentage, 4, '.', '')) * $rows['LANDED COST']);
                        if (number_format($com_sc, 2, '.', '') != number_format($rows['STORE COST'], 2, '.', '')) {
                            return 1;
                        }
                    }
                    break;
            }
        } else {

            $csm = ($item_details->promo_srp - $rows['LANDED COST']) / $item_details->promo_srp;
            $ccsm = self::getComputedMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

            switch ($ccsm->matrix_type) {
                case 'BASED ON MATRIX': {
                        if (number_format($ccsm->store_margin_percentage, 4, '.', '') != $dtp_rf_percentage || number_format($csm, 7, '.', '') < 0) {
                            return 1;
                        }
                    }
                    break;

                case 'ADD TO LC': {
                        $ccsm = self::getComputedUploadMarginPercentage(number_format($csm, 4, '.', ''), $item_details->rma_margin_categories_id, "ACCESSORIES", $item_details->brands_id);

                        $com_sc = (1 + (number_format($ccsm->store_margin_percentage, 4, '.', '')) * $rows['LANDED COST']);
                        if (number_format($com_sc, 2, '.', '') != number_format($rows['STORE COST'], 2, '.', '')) {
                            return 1;
                        }
                    }
                    break;
            }
        }
        return 0;
    }

    public static function getComputedUploadMarginPercentage($margin_percentage, $rma_margin_categories_id, $margin_category, $brand)
    {

        $marginMatrix = MarginMatrix::where('margin_category', $margin_category)
            ->whereRaw($margin_percentage . ' between `min` and `max`')
            ->where('matrix_type', 'ADD TO LC')
            ->where('brands_id', $brand)
            ->where('status', 'ACTIVE')
            ->where('margin_categories_id', 'LIKE', '%' . $rma_margin_categories_id . '%')->first();
        if (empty($marginMatrix)) {
            return MarginMatrix::where('margin_category', $margin_category)
                ->whereRaw($margin_percentage . ' between `min` and `max`')
                ->where('matrix_type', 'ADD TO LC')
                ->where('status', 'ACTIVE')
                ->whereNull('brands_id')
                ->where('margin_categories_id', 'LIKE', '%' . $rma_margin_categories_id . '%')->first();
        } else {
            return $marginMatrix;
        }
    }

    public static function getComputedMarginPercentage($margin_percentage, $margin_categories_id, $margin_category, $brand)
    {

        $marginMatrix = MarginMatrix::where('margin_category', $margin_category)
            ->whereRaw($margin_percentage . ' between `min` and `max`')
            ->where('brands_id', $brand)->first();

        if (empty($marginMatrix)) {
            return MarginMatrix::where('margin_category', $margin_category)
                ->whereRaw($margin_percentage . ' between `min` and `max`')
                ->whereNull('brands_id')->first();
        } else {
            return $marginMatrix;
        }
    }

    public static function getComputedEcomMarginPercentage($margin_percentage, $margin_categories_id, $margin_category, $brand)
    {

        $marginMatrix = DB::table('ecom_margin_matrices')->whereRaw($margin_percentage . ' between `min` and `max`')
            ->where('margin_category', $margin_category)
            ->where('brands_id', $brand)->first();

        if (empty($marginMatrix)) {
            return DB::table('ecom_margin_matrices')->whereRaw($margin_percentage . ' between `min` and `max`')
                ->where('margin_category', $margin_category)
                ->whereNull('brands_id')->first();
        } else {
            return $marginMatrix;
        }
    }

    public static function getComputedLocalMarginPercentage($margin_percentage, $vendor_type_id, $brands)
    {

        DB::enableQueryLog();

        $matrix = DB::table('margin_matrices')
            ->whereRaw($margin_percentage . ' between `min` and `max`')
            ->where('matrix_type', 'ADD TO LC')
            ->where('vendor_types_id', $vendor_type_id)
            ->where('brands_id', $brands)
            ->where('status', 'ACTIVE')->first();
        Log::debug(DB::getQueryLog());
        if (empty($matrix)) {
            Log::debug("----");
            return DB::table('margin_matrices')
                ->whereRaw($margin_percentage . ' between `min` and `max`')
                ->where('matrix_type', 'ADD TO LC')
                ->where('vendor_types_id', $vendor_type_id)
                ->whereNull('brands_id')
                ->where('status', 'ACTIVE')->first();
        }
        Log::debug(DB::getQueryLog());
        return $matrix;
    }
}
