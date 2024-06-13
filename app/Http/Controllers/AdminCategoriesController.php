<?php 

	namespace App\Http\Controllers;
	
	use CRUDBooster;
	use App\Category;

	class AdminCategoriesController extends \crocodicstudio\crudbooster\controllers\CBController {
		
	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "category_description";
			$this->limit = "20";
			$this->orderby = "category_description,asc";
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
			$this->table = "categories";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Category Code","name"=>"category_code"];
			$this->col[] = ["label"=>"Category Description","name"=>"category_description"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName()=="ADMIN") {
				$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
				$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
				$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
				$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			}
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = [
				'label'=>'Category Code',
				'name'=>'category_code',
				'type'=>'text',
				'validation'=>'required|alpha_num|min:3|max:3|unique:categories',
				'width'=>'col-sm-6',
				'help'=>'Category Code must be 3 letter unique code format. (Example: ABC)',
				'readonly'=>(CRUDBooster::getCurrentMethod() == "getEdit" && !CRUDBooster::isSuperadmin())?true:false
			];
			$this->form[] = [
				'label'=>'Category Description',
				'name'=>'category_description',
				'type'=>'text',
				'validation'=>'required|alpha_num_spaces|min:2|max:30|unique:categories',
				'width'=>'col-sm-6'
			];
			if(in_array(CRUDBooster::getCurrentMethod(),["getEdit","postEditSave","getDetail"])) {	
				$this->form[] = ['label'=>'Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-6','dataenum'=>'ACTIVE;INACTIVE'];
			}
			
	        $this->button_selected = array();
			if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Status ACTIVE ","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
	        }
			
	        $this->table_row_color = array();     	          
			$this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];
	        
	        
	        $this->load_js = array();
	        $this->load_js[] = asset("js/category_submaster.js");        
	        
	    }
		
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	        switch ($button_name) {
				case 'set_status_ACTIVE':

					Category::whereIn('id',$id_selected)->update([
						'status'=>'ACTIVE', 
						'updated_at' => date('Y-m-d H:i:s'), 
						'updated_by' => CRUDBooster::myId()
					]);
					break;
				case 'set_status_INACTIVE':

					Category::whereIn('id',$id_selected)->update([
						'status'=>'INACTIVE', 
						'updated_at' => date('Y-m-d H:i:s'), 
						'updated_by' => CRUDBooster::myId()
					]);
					break;
				default:
					# code...
					break;
			}    
	    }
		
	    public function hook_before_add(&$postdata) {        
	        //Your code here
            $postdata["created_by"]=CRUDBooster::myId();
			$postdata["created_at"]=date("Y-m-d H:i:s");
	    }
		
	    public function hook_after_add($id) {        
	        //Your code here
            if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADMIN"){
	            return redirect()->action('AdminClassesController@getAdd')->send();
				exit;
	        }
	    }
		
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here
            $postdata["updated_by"]=CRUDBooster::myId();
			$postdata["updated_at"]=date("Y-m-d H:i:s");
	    }

		public function getCategoryCode($id) {
			if(is_numeric($id)) return Category::where('id', $id)->value('category_code');
		}
	}