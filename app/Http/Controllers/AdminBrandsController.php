<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use App\Brand;

	class AdminBrandsController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "brand_description";
			$this->limit = "20";
			$this->orderby = "brand_description,asc";
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
			$this->button_import = (CRUDBooster::isSuperadmin())?true:false;
			$this->button_export = true;
			$this->table = "brands";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Brand Code","name"=>"brand_code"];
			$this->col[] = ["label"=>"Brand Description","name"=>"brand_description"];
			$this->col[] = ["label"=>"Brand Group","name"=>"brand_group"];
			$this->col[] = ["label"=>"Contact Email","name"=>"contact_email"];
			$this->col[] = ["label"=>"Contact Name","name"=>"contact_name"];
			$this->col[] = ["label"=>"Brand Status","name"=>"status","visible"=>CRUDBooster::myColumnView()->brand_status ? true:false];
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),["ADVANCED","MCB TL"])) {
				$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
				$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
				$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
				$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			}
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = [
				'label'=>'Brand Code',
				'name'=>'brand_code',
				'type'=>'text',
				'validation'=>'required|alpha_num|min:3|max:3|unique:brands',
				'width'=>'col-sm-6',
				'help'=>'Brand Code must be 3 letter unique code format. (Example: ABC)',
				'readonly'=>(CRUDBooster::getCurrentMethod() == "getEdit" && !CRUDBooster::isSuperadmin())?true:false
			];
			$this->form[] = [
				'label'=>'Brand Description',
				'name'=>'brand_description',
				'type'=>'text',
				'validation'=>'required|alpha_num_spaces|min:2|max:30|unique:brands',
				'width'=>'col-sm-6'
			];
			$this->form[] = [
				'label'=>'Brand Group',
				'name'=>'brand_group',
				'type'=>'select',
				'validation'=>'required',
				'dataenum'=>'APPLE;NON APPLE;INVALID',
				'width'=>'col-sm-6'
			];
			$this->form[] = [
				'label'=>'Contact Email',
				'name'=>'contact_email',
				'type'=>'email',
				'validation'=>'required',
				'width'=>'col-sm-6'
			];
			
			$this->form[] = [
				'label'=>'Contact Name',
				'name'=>'contact_name',
				'type'=>'text',
				'validation'=>'required|alpha_num_spaces|min:2|max:100',
				'width'=>'col-sm-6'
			];
			if(in_array(CRUDBooster::getCurrentMethod(),["getEdit","postEditSave","getDetail"])) {
				$this->form[] = ['label'=>'Brand Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-6','dataenum'=>'ACTIVE;INACTIVE;STATUS QUO;CORE',"visible"=>CRUDBooster::myColumnView()->brand_status ? true:false];
			}
			# END FORM DO NOT REMOVE THIS LINE

	        $this->button_selected = array();
			if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Brand Status ACTIVE ","icon"=>"fa fa-check-circle","name"=>"set_brand_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Brand Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_brand_status_INACTIVE"];
				$this->button_selected[] = ['label'=>'Set Brand Status STATUS QUO','icon'=>'fa fa-times-circle','name'=>'set_brand_status_STATUS_QUO'];
				$this->button_selected[] = ['label'=>'Set Brand Status CORE','icon'=>'fa fa-play-times','name'=>'set_brand_status_CORE'];

				$this->button_selected[] = ["label"=>"Set Brand Group APPLE","icon"=>"fa fa-times-circle","name"=>"set_brand_group_APPLE"];
				$this->button_selected[] = ['label'=>'Set Brand Group NON APPLE','icon'=>'fa fa-times-circle','name'=>'set_brand_group_NONAPPLE'];
				$this->button_selected[] = ['label'=>'Set Brand Group INVALID','icon'=>'fa fa-times-circle','name'=>'set_brand_group_INVALID'];
			}
	                
	        $this->table_row_color = array();     	          
			$this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];
	        
	        $this->load_js = array();       
	        
	    }
		
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
			$status = '';
			$group = '';
	        switch ($button_name) {
				case 'set_brand_status_ACTIVE':
					$status = 'ACTIVE';
					break;
				case 'set_brand_status_INACTIVE':
					$status = 'INACTIVE';
					break;
				case 'set_brand_status_STATUS_QUO':
					$status = 'STATUS QUO';
					break;
				case 'set_brand_status_CORE':
					$status = 'CORE';
					break;
				case 'set_brand_group_APPLE':
					$group = 'APPLE';
					break;
				case 'set_brand_group_NONAPPLE':
					$group = 'NON APPLE';
					break;
				case 'set_brand_group_INVALID':
					$group = 'INVALID';
					break;
				default:
					# code...
					break;
			}

			foreach ($id_selected as $key => $value) {
				$brand = Brand::find($value);
				if($status != ''){
					$brand->status = $status;
				}
				if($group != ''){
					$brand->brand_group = $group;
				}
				$brand->updated_at = date('Y-m-d H:i:s');
				$brand->updated_by = CRUDBooster::myId();
				$brand->save();
			}
	    }

	    public function hook_before_add(&$postdata) {        
	        //Your code here
            $postdata["created_by"]=CRUDBooster::myId();
            $postdata["created_at"]=date('Y-m-d H:i:s');
            $emails = ['rma@digits.ph', 'merchandising@digits.ph'];
            $data = [
                'date'=> date('Y-m-d'),
                'brand_description'=>$postdata["brand_description"],
                'contact_email'=>$postdata["contact_email"],
                'contact_name'=>$postdata["contact_name"]
            ];
            CRUDBooster::sendEmail(['to'=>'sdm@digits.ph','data'=>$data,'template'=>'brand_created','attachments'=>[]]);
            foreach($emails as $email){
                CRUDBooster::sendEmail(['to'=>$email,'data'=>$data,'template'=>'new_brand_created','attachments'=>[]]);
            }
            
	    }
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here
            $postdata["updated_by"]=CRUDBooster::myId();
			$postdata["updated_at"]=date('Y-m-d H:i:s');
	    }

		public function getBrandCode($id) {
			if(is_numeric($id)) return Brand::where('id', $id)->value('brand_code');
		}
	}