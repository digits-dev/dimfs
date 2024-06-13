<?php

namespace App\Http\Traits;

use App\ActionType;
use App\Category;
use App\Counter;
use App\ExportPrivilege;
use App\InventoryType;
use App\ItemIdentifier;
use App\PromoType;
use App\Segmentation;
use App\SkuStatus;
use App\StatusState;
use App\VendorType;
use CRUDBooster;

trait ItemTraits {

	public function getSkuStatus($status) {
		return SkuStatus::status($status);
	}

	public function getInventoryType($type) {
		return InventoryType::getType($type);
	}

    public function getSegmentations() {
        return Segmentation::active()->get();
    }

    public function getActionByDescription($action) {
        return ActionType::getType($action);
    }

    public function getStatusByDescription($status) {
        return StatusState::getState($status);
    }

	public function getItemIdentifier() {
		return ItemIdentifier::active()->get();
	}

	public function getPromoTypes() {
		return PromoType::active()->get();
	}

    public function updateCounter($item_code) {
        $column = '';
        switch ($item_code) {
			case '1':
				$column = 'code_1';
				break;
			case '2':
				$column = 'code_2';
				break;
			case '3':
				$column = 'code_3';
				break;
			case '4':
				$column = 'code_4';
				break;
			case '5':
				$column = 'code_5';
				break;
			case '6':
				$column = 'code_6';
				break;
			case '7':
				$column = 'code_7';
				break;
			case '8':
				$column = 'code_8';
				break;
			case '9':
				$column = 'code_9';
				break;
			
			default:
				# code...
				break;
		}

        Counter::incrementColumn($column);

    }

    public function getDigitsCode($params) {

        $category_code = Category::getCodeById($params['category_id']);
		$inventory_type_code = InventoryType::getCodeById('id',$params['inventory_type_id']);
		$vendor_type_code = VendorType::getCodeById($params['vendor_type_id']);

		if($category_code == 'SPR') {
			return Counter::getCode('code_2');
		}
		elseif(in_array($category_code,['DEM','SAM'])) {
			return Counter::getCode('code_9');
		}
		elseif(in_array($category_code,['MKT','PPB','OTH'])) {
			return Counter::getCode('code_3');
		}
		else {
			if($inventory_type_code == 'N-TRADE') {
				return Counter::getCode('code_3');
			}
			else {
				if(in_array($vendor_type_code,['IMP-OUT','LR-OUT','LOC-OUT'])) {
					return Counter::getCode('code_8');
				}
				elseif(in_array($vendor_type_code,['IMP-CON','LOC-CON','LR-CON'])){
					return Counter::getCode('code_7');
				}
			}
		}
    }

	public function getItemAccess(){
		$access =  ExportPrivilege::where('cms_privileges_id',CRUDBooster::myPrivilegeId())
			->where('table_name','item_masters')
			->where('action_types_id',5) //view
			->get(['report_header','report_query'])
			->toArray();
			// Split the strings into arrays
			$keys = explode(',', $access[0]['report_header']);
			$values = explode(',', $access[0]['report_query']);

			// Remove backticks from values
			$values = array_map(function($value) {
				return trim($value, '`');
			}, $values);
			
			// Combine the arrays into a key-value array
			// $access = array_combine($keys, $values);

			return $values;
	}

	public function getItemExport(){
		$access =  ExportPrivilege::where('cms_privileges_id',CRUDBooster::myPrivilegeId())
			->where('table_name','item_masters')
			->where('action_types_id',6) //export
			->get(['report_header','report_query'])
			->toArray();
			// Split the strings into arrays
			$keys = explode(',', $access[0]['report_header']);
			$values = explode(',', $access[0]['report_query']);

			// Remove backticks from values
			$values = array_map(function($value) {
				return trim($value, '`');
			}, $values);
			
			// Combine the arrays into a key-value array
			$export = array_combine($keys, $values);

			return $export;
	}

	public function getItemUpdateReadOnly($table='item_masters'){
		$access =  ExportPrivilege::where('cms_privileges_id',CRUDBooster::myPrivilegeId())
			->where('table_name',$table)
			->where('action_types_id',4) //update-read-only
			->get(['report_header','report_query'])
			->toArray();
			// Split the strings into arrays
			$keys = explode(',', $access[0]['report_header']);
			$values = explode(',', $access[0]['report_query']);

			// Remove backticks from values
			$values = array_map(function($value) {
				return trim($value, '`');
			}, $values);
			
			// Combine the arrays into a key-value array
			// $export = array_combine($keys, $values);

			return $values;
	}

	public function getItemCreate(){
		$access =  ExportPrivilege::where('cms_privileges_id',CRUDBooster::myPrivilegeId())
			->where('table_name','item_masters')
			->where('action_types_id',1) //create
			->get(['report_header','report_query'])
			->toArray();
			// Split the strings into arrays
			$keys = explode(',', $access[0]['report_header']);
			$values = explode(',', $access[0]['report_query']);

			// Remove backticks from values
			$values = array_map(function($value) {
				return trim($value, '`');
			}, $values);
			
			// Combine the arrays into a key-value array
			// $export = array_combine($keys, $values);

			return $values;
	}

	public function getItemUpdate($table='item_masters'){
		$access =  ExportPrivilege::where('cms_privileges_id',CRUDBooster::myPrivilegeId())
			->where('table_name',$table)
			->where('action_types_id',2) //update
			->get(['report_header','report_query'])
			->toArray();
			// Split the strings into arrays
			$keys = explode(',', $access[0]['report_header']);
			$values = explode(',', $access[0]['report_query']);

			// Remove backticks from values
			$values = array_map(function($value) {
				return trim($value, '`');
			}, $values);
			
			// Combine the arrays into a key-value array
			// $export = array_combine($keys, $values);

			return $values;
	}

	public function getItemForms(){
		$forms =  ExportPrivilege::where('cms_privileges_id',CRUDBooster::myPrivilegeId())
			->where('table_name','item_masters')
			->get(['report_header','report_query'])
			->toArray();
			// Split the strings into arrays
			$keys = explode(',', $forms[0]['report_header']);
			$values = explode(',', $forms[0]['report_query']);

			// Remove backticks from values
			$values = array_map(function($value) {
				return trim($value, '`');
			}, $values);
			
			// Combine the arrays into a key-value array
			// $array1 = array_combine($keys, $values);

			return $values;
	}

	public function getAllAccess($column_access) {
		return ((in_array(CRUDBooster::getCurrentMethod(), ['getAdd', 'postAddSave']) && CRUDBooster::myAddForm()->$column_access ? true : false)
		|| (in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditForm()->$column_access ? true : false )
		|| (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_access ? true : false));
	}

	public function getEditAccessOnly($column_access) {
		return ((in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditForm()->$column_access ? true : false )
		|| (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_access ? true : false));
	}
	
	public function getDetailAccessOnly($column_access) {
		return (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_access ? true : false);
	}

	public function getAllAccessReadOnly($column_readonly) {
		return ((in_array(CRUDBooster::getCurrentMethod(), ['getAdd', 'postAddSave']) && CRUDBooster::myAddReadOnly()->$column_readonly ? true : false)
		|| (in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditReadOnly()->$column_readonly ? true : false ));
	}

	public function getEditAccessReadOnly($column_readonly) {
		return ((in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditReadOnly()->$column_readonly ? true : false )
		|| (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_readonly ? true : false));
	}

}