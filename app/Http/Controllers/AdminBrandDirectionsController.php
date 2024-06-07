<?php 

	namespace App\Http\Controllers;

	use App\BrandDirection;
	use CRUDBooster;

	class AdminBrandDirectionsController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "brand_direction_description";
			$this->limit = "20";
			$this->orderby = "brand_direction_description,asc";
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
			$this->table = "brand_directions";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Brand Direction Code","name"=>"brand_direction_code"];
			$this->col[] = ["label"=>"Brand Direction Description","name"=>"brand_direction_description"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),["ADVANCED","MCB TL"])) {
				$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
				$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
				$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
				$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			}
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Brand Direction Code','name'=>'brand_direction_code','type'=>'text','validation'=>'required|min:1|max:100','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Brand Direction Description','name'=>'brand_direction_description','type'=>'text','validation'=>'required|min:1|max:100','width'=>'col-sm-6'];
			if(in_array(CRUDBooster::getCurrentMethod(),["getEdit","postEditSave","getDetail"])) {
				$this->form[] = ['label'=>'Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-6','dataenum'=>'ACTIVE;INACTIVE;STATUS QUO;CORE',"visible"=>CRUDBooster::myColumnView()->brand_status ? true:false];
			}
			# END FORM DO NOT REMOVE THIS LINE

	        $this->button_selected = array();
            if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Status ACTIVE","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
	        }
			
	        $this->table_row_color = array();     	          
            $this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];
	        
	        $this->load_js = array();	        
	        
	    }
		
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	        $status = '';
	        switch ($button_name) {
				case 'set_status_ACTIVE':
					$status = 'ACTIVE';
					break;
				case 'set_status_INACTIVE':
					$status = 'INACTIVE';
					break;
				default:
					$status = 'ACTIVE';
					break;    
			}

			BrandDirection::whereIn('id',$id_selected)->update([
				'status' => $status,
				'updated_by' => CRUDBooster::myId(),
				'updated_at' => date('Y-m-d H:i:s')
			]);    
	    }

	    public function hook_before_add(&$postdata) {        
	        //Your code here
            $postdata["created_by"]=CRUDBooster::myId();
			$postdata["created_at"] = date('Y-m-d H:i:s');
	    }

	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here
            $postdata["updated_by"]=CRUDBooster::myId();
			$postdata["updated_at"] = date('Y-m-d H:i:s');
	    }

	}