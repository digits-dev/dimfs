<?php

namespace App\Http\Traits;

use App\ActionType;
use App\Category;
use App\Counter;
use App\InventoryType;
use App\ItemIdentifier;
use App\PromoType;
use App\Segmentation;
use App\StatusState;
use App\VendorType;

trait ItemTraits {

    public function getSegmentations() {
        return Segmentation::active()->get();
    }

    public function getActionByDescription($action){
        return ActionType::getType($action);
    }

    public function getStatusByDescription($status){
        return StatusState::getState($status);
    }

	public function getItemIdentifier(){
		return ItemIdentifier::active()->get();
	}

	public function getPromoTypes(){
		return PromoType::active()->get();
	}

    public function updateCounter($item_code){
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

    public function getDigitsCode($params){

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

}