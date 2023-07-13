<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use App\Brand;

	class AdminBrandsController extends \crocodicstudio\crudbooster\controllers\CBController {

        public function __construct() {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
        }

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
				'dataenum'=>'APPLE & BEATS;NON APPLE',
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

			/* 
	        | ---------------------------------------------------------------------- 
	        | Sub Module
	        | ----------------------------------------------------------------------     
			| @label          = Label of action 
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class  
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        | 
	        */
	        $this->sub_module = array();


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------     
	        | @label       = Label of action 
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)     
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        | 
	        */
	        $this->addaction = array();


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add More Button Selected
	        | ----------------------------------------------------------------------     
	        | @label       = Label of action 
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button 
	        | Then about the action, you should code at actionButtonSelected method 
	        | 
	        */
	        $this->button_selected = array();
			if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Brand Status ACTIVE ","icon"=>"fa fa-check-circle","name"=>"set_brand_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Brand Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_brand_status_INACTIVE"];
				$this->button_selected[] = ['label'=>'Set Brand Status STATUS QUO','icon'=>'fa fa-pause-circle','name'=>'set_brand_status_STATUS_QUO'];
				$this->button_selected[] = ['label'=>'Set Brand Status CORE','icon'=>'fa fa-play-circle','name'=>'set_brand_status_CORE'];
			}
	                
	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------     
	        | @message = Text of message 
	        | @type    = warning,success,danger,info        
	        | 
	        */
	        $this->alert = array();
	                

	        
	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add more button to header button 
	        | ----------------------------------------------------------------------     
	        | @label = Name of button 
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        | 
	        */
	        $this->index_button = array();



	        /* 
	        | ---------------------------------------------------------------------- 
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------     
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.        
	        | 
	        */
	        $this->table_row_color = array();     	          
			$this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | You may use this bellow array to add statistic at dashboard 
	        | ---------------------------------------------------------------------- 
	        | @label, @count, @icon, @color 
	        |
	        */
	        $this->index_statistic = array();



	        /*
	        | ---------------------------------------------------------------------- 
	        | Add javascript at body 
	        | ---------------------------------------------------------------------- 
	        | javascript code in the variable 
	        | $this->script_js = "function() { ... }";
	        |
	        */
	        $this->script_js = NULL;


            /*
	        | ---------------------------------------------------------------------- 
	        | Include HTML Code before index table 
	        | ---------------------------------------------------------------------- 
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
	        $this->pre_index_html = null;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include HTML Code after index table 
	        | ---------------------------------------------------------------------- 
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
	        $this->post_index_html = null;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include Javascript File 
	        | ---------------------------------------------------------------------- 
	        | URL of your javascript each array 
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
	        $this->load_js = array();
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Add css style at body 
	        | ---------------------------------------------------------------------- 
	        | css code in the variable 
	        | $this->style_css = ".style{....}";
	        |
	        */
	        $this->style_css = NULL;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include css File 
	        | ---------------------------------------------------------------------- 
	        | URL of your css each array 
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
	        $this->load_css = array();
	        
	        
	    }


	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for button selected
	    | ---------------------------------------------------------------------- 
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	        switch ($button_name) {
				case 'set_brand_status_ACTIVE':

					Brand::whereIn('id',$id_selected)->update([
						'status'=>'ACTIVE', 
						'updated_at' => date('Y-m-d H:i:s'), 
						'updated_by' => CRUDBooster::myId()
					]);
					break;
				case 'set_brand_status_INACTIVE':

					Brand::whereIn('id',$id_selected)->update([
						'status'=>'INACTIVE', 
						'updated_at' => date('Y-m-d H:i:s'), 
						'updated_by' => CRUDBooster::myId()
					]);
					break;
				case 'set_brand_status_STATUS_QUO':

					Brand::whereIn('id',$id_selected)->update([
						'status'=>'STATUS QUO', 
						'updated_at' => date('Y-m-d H:i:s'), 
						'updated_by' => CRUDBooster::myId()
					]);
					break;
				case 'set_brand_status_CORE':
					
					Brand::whereIn('id',$id_selected)->update([
						'status'=>'CORE', 
						'updated_at' => date('Y-m-d H:i:s'), 
						'updated_by' => CRUDBooster::myId()
					]);
					break;
				default:
					# code...
					break;
			}
	    }


	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate query of index result 
	    | ---------------------------------------------------------------------- 
	    | @query = current sql query 
	    |
	    */
	    public function hook_query_index(&$query) {
	        //Your code here
	            
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate row of index table html 
	    | ---------------------------------------------------------------------- 
	    |
	    */    
	    public function hook_row_index($column_index,&$column_value) {	        
	    	//Your code here
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before add data is execute
	    | ---------------------------------------------------------------------- 
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {        
	        //Your code here
            $postdata["created_by"]=CRUDBooster::myId();
            
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

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after add public static function called 
	    | ---------------------------------------------------------------------- 
	    | @id = last insert id
	    | 
	    */
	    public function hook_after_add($id) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before update data is execute
	    | ---------------------------------------------------------------------- 
	    | @postdata = input post data 
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here
            $postdata["updated_by"]=CRUDBooster::myId();
	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_edit($id) {
	        //Your code here 

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_delete($id) {
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_delete($id) {
	        //Your code here

        }

		public function getBrandCode($id) {
			if(is_numeric($id)) return Brand::where('id', $id)->value('brand_code');
		}
	}