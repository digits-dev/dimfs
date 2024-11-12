<?php namespace App\Http\Controllers;

use App\AccountingItem;
use App\Counter;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

	class AdminAccountingItemsController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "digits_code";
			$this->limit = "20";
			$this->orderby = "digits_code,asc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "accounting_items";
			
			$this->col = [];
			$this->col[] = ["label"=>"Digits Code","name"=>"digits_code"];
			$this->col[] = ["label"=>"Item Description","name"=>"item_description"];
			$this->col[] = ["label"=>"Current SRP","name"=>"current_srp"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			
			$this->form = [];
			if(in_array(CRUDBooster::getCurrentMethod(),["getEdit","postEditSave","getDetail"])) {
				$this->form[] = ['label'=>'Digits Code','name'=>'digits_code','type'=>'text','validation'=>'required','width'=>'col-sm-5','readonly'=>true];
			}
			$this->form[] = ['label'=>'Item Description','name'=>'item_description','type'=>'text','validation'=>'required|min:1|max:150','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Current SRP','name'=>'current_srp','type'=>'number','validation'=>'required','width'=>'col-sm-5'];
			if(in_array(CRUDBooster::getCurrentMethod(),["getEdit","postEditSave","getDetail"])) {
				$this->form[] = ['label'=>'Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-5','dataenum'=>'ACTIVE;INACTIVE'];
			}
	        $this->button_selected = array();
            if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Status ACTIVE","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
	        }
	        
	    }
		
	    public function actionButtonSelected($id_selected,$button_name) {
			$data = [
				'updated_at'=>date('Y-m-d H:i:s'),
				'updated_by'=>CRUDBooster::myId()
			];
	        switch ($button_name) {
				case 'set_status_ACTIVE':
					$data['status'] = 'ACTIVE';
					break;
				case 'set_status_INACTIVE':
					$data['status'] = 'INACTIVE';
					break;
				default:

				}

	        AccountingItem::whereIn('id', $id_selected)->update($data);
	    }

		public function hook_before_add(&$postdata) {
	        //Your code here
			$postdata["digits_code"] = Counter::getCode('code_1');
            $postdata["created_by"] = CRUDBooster::myId();
			$postdata["created_at"] = date("Y-m-d H:i:s");
	    }

		public function hook_after_add($id) {
	        Counter::incrementColumn('code_1');
	    }

		public function hook_before_edit(&$postdata,$id) {
	        //Your code here
            $postdata["updated_by"] = CRUDBooster::myId();
			$postdata["updated_at"] = date("Y-m-d H:i:s");
	    }

	}