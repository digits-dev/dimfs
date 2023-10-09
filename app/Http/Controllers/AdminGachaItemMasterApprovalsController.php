<?php namespace App\Http\Controllers;

	use Session;
	use Illuminate\Http\Request;
	use DB;
	use CRUDBooster;
	use App\GachaItemApproval;

	class AdminGachaItemMasterApprovalsController extends \crocodicstudio\crudbooster\controllers\CBController {

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
			$this->approver = [];
        }

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = true;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "gacha_item_master_approvals";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Approval Status","name"=>"approval_status"];
			$this->col[] = ["label"=>"Digits Code","name"=>"digits_code"];
			$this->col[] = ["label"=>"Item No","name"=>"item_no"];
			$this->col[] = ["label"=>"Initial Wrr Date","name"=>"initial_wrr_date"];
			$this->col[] = ["label"=>"Latest Wrr Date","name"=>"latest_wrr_date"];
			$this->col[] = ["label"=>"Brand","name"=>"gacha_brands_id","join"=>"gacha_brands,brand_description"];
			$this->col[] = ["label"=>"Sku Status","name"=>"gacha_sku_statuses_id","join"=>"gacha_sku_statuses,status_description"];
			$this->col[] = ["label"=>"Item Description","name"=>"item_description"];
			$this->col[] = ['label'=>'Model','name'=>'gacha_models'];
			$this->col[] = ['label'=>'WH Category','name'=>'gacha_wh_categories_id','join'=>'gacha_wh_categories,category_description'];
			$this->col[] = ['label'=>'MSRP (JPY)','name'=>'msrp'];
			$this->col[] = ['label'=>'Current SRP','name'=>'current_srp'];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Jan No','name'=>'jan_no','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Digits Code','name'=>'digits_code','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Item No','name'=>'item_no','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Sap No','name'=>'sap_no','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Initial Wrr Date','name'=>'initial_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Latest Wrr Date','name'=>'latest_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Gacha Brands Id','name'=>'gacha_brands_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_brands,id'];
			$this->form[] = ['label'=>'Gacha Sku Statuses Id','name'=>'gacha_sku_statuses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_sku_statuses,id'];
			$this->form[] = ['label'=>'Item Description','name'=>'item_description','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Gacha Models','name'=>'gacha_models','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Gacha Wh Categories Id','name'=>'gacha_wh_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_wh_categories,id'];
			$this->form[] = ['label'=>'Msrp','name'=>'msrp','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Current Srp','name'=>'current_srp','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'No Of Tokens','name'=>'no_of_tokens','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Store Cost','name'=>'store_cost','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Sc Margin','name'=>'sc_margin','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Lc Per Pc','name'=>'lc_per_pc','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Lc Margin Per Pc','name'=>'lc_margin_per_pc','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Lc Per Carton','name'=>'lc_per_carton','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Lc Margin Per Carton','name'=>'lc_margin_per_carton','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Dp Ctn','name'=>'dp_ctn','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Pcs Dp','name'=>'pcs_dp','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Moq','name'=>'moq','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'No Of Assort','name'=>'no_of_assort','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Gacha Countries Id','name'=>'gacha_countries_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_countries,country_name'];
			$this->form[] = ['label'=>'Gacha Incoterms Id','name'=>'gacha_incoterms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_incoterms,id'];
			$this->form[] = ['label'=>'Currencies Id','name'=>'currencies_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'currencies,id'];
			$this->form[] = ['label'=>'Supplier Cost','name'=>'supplier_cost','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Gacha Uoms Id','name'=>'gacha_uoms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_uoms,id'];
			$this->form[] = ['label'=>'Gacha Inventory Types Id','name'=>'gacha_inventory_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_inventory_types,id'];
			$this->form[] = ['label'=>'Gacha Vendor Types Id','name'=>'gacha_vendor_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_vendor_types,id'];
			$this->form[] = ['label'=>'Gacha Vendor Groups Id','name'=>'gacha_vendor_groups_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_vendor_groups,id'];
			$this->form[] = ['label'=>'Age Grade','name'=>'age_grade','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Battery','name'=>'battery','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Approved By','name'=>'approved_by','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Approved At','name'=>'approved_at','type'=>'datetime','validation'=>'required|date_format:Y-m-d H:i:s','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Created By','name'=>'created_by','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Updated By','name'=>'updated_by','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Status','name'=>'status','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"Jan No","name"=>"jan_no","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Digits Code","name"=>"digits_code","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Item No","name"=>"item_no","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Sap No","name"=>"sap_no","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Initial Wrr Date","name"=>"initial_wrr_date","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
			//$this->form[] = ["label"=>"Latest Wrr Date","name"=>"latest_wrr_date","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
			//$this->form[] = ["label"=>"Gacha Brands Id","name"=>"gacha_brands_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_brands,id"];
			//$this->form[] = ["label"=>"Gacha Sku Statuses Id","name"=>"gacha_sku_statuses_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_sku_statuses,id"];
			//$this->form[] = ["label"=>"Item Description","name"=>"item_description","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Gacha Models","name"=>"gacha_models","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Gacha Wh Categories Id","name"=>"gacha_wh_categories_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_wh_categories,id"];
			//$this->form[] = ["label"=>"Msrp","name"=>"msrp","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Current Srp","name"=>"current_srp","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"No Of Tokens","name"=>"no_of_tokens","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Store Cost","name"=>"store_cost","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Sc Margin","name"=>"sc_margin","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Lc Per Pc","name"=>"lc_per_pc","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Lc Margin Per Pc","name"=>"lc_margin_per_pc","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Lc Per Carton","name"=>"lc_per_carton","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Lc Margin Per Carton","name"=>"lc_margin_per_carton","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Dp Ctn","name"=>"dp_ctn","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Pcs Dp","name"=>"pcs_dp","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Moq","name"=>"moq","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"No Of Assort","name"=>"no_of_assort","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Gacha Countries Id","name"=>"gacha_countries_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_countries,country_name"];
			//$this->form[] = ["label"=>"Gacha Incoterms Id","name"=>"gacha_incoterms_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_incoterms,id"];
			//$this->form[] = ["label"=>"Currencies Id","name"=>"currencies_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"currencies,id"];
			//$this->form[] = ["label"=>"Supplier Cost","name"=>"supplier_cost","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Gacha Uoms Id","name"=>"gacha_uoms_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_uoms,id"];
			//$this->form[] = ["label"=>"Gacha Inventory Types Id","name"=>"gacha_inventory_types_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_inventory_types,id"];
			//$this->form[] = ["label"=>"Gacha Vendor Types Id","name"=>"gacha_vendor_types_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_vendor_types,id"];
			//$this->form[] = ["label"=>"Gacha Vendor Groups Id","name"=>"gacha_vendor_groups_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"gacha_vendor_groups,id"];
			//$this->form[] = ["label"=>"Age Grade","name"=>"age_grade","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Battery","name"=>"battery","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Approved By","name"=>"approved_by","type"=>"number","required"=>TRUE,"validation"=>"required|integer|min:0"];
			//$this->form[] = ["label"=>"Approved At","name"=>"approved_at","type"=>"datetime","required"=>TRUE,"validation"=>"required|date_format:Y-m-d H:i:s"];
			//$this->form[] = ["label"=>"Created By","name"=>"created_by","type"=>"number","required"=>TRUE,"validation"=>"required|integer|min:0"];
			//$this->form[] = ["label"=>"Updated By","name"=>"updated_by","type"=>"number","required"=>TRUE,"validation"=>"required|integer|min:0"];
			//$this->form[] = ["label"=>"Status","name"=>"status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			# OLD END FORM

			/* 
	        | ---------------------------------------------------------------------- 
	        | Sub Module
	        | ----------------------------------------------------------------------     
			| @label          = Label of action 
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class  
			| @parent_columns = Separate with comma, e.g : name,created_at
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
			if (in_array(CRUDBooster::myPrivilegeName(), $this->approver) || CRUDBooster::isSuperadmin()) {
	        	$this->button_selected[] = ['label'=>'APPROVE','icon'=>'fa fa-check','name'=>'approve'];
				$this->button_selected[] = ['label'=>'REJECT','icon'=>'fa fa-times','name'=>'reject'];
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
            // $this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];
	        
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
			$approval_status_badges = [
				202 => '<span class="label label-warning">PENDING</span>',
				200 => '<span class="label label-success">APPROVED</span>',
				400 => '<span class="label label-danger">REJECTED</span>',
			];

	    	if ($column_index == 2) {
				$column_value = $approval_status_badges[$column_value];
			}
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

		public function submitNewItem(Request $request) {
			$request = $request->all();
			$time_stamp = date('Y-m-d H:i:s');
			$action_by = CRUDBooster::myId();
			$digits_code = $request['digits_code'];
			$gacha_item_master_approvals_id = $request['gacha_item_master_approvals_id'];
			$item_description = $request['item_description'];

			$data = [
				'approval_status' => 202,
				'jan_no' => $request['jan_no'],
				'digits_code' => $request['digits_code'],
				'item_no' => $request['item_no'],
				'sap_no' => $request['sap_no'],
				'gacha_brands_id' => $request['gacha_brands_id'],
				'gacha_sku_statuses_id' => $request['gacha_sku_statuses_id'],
				'item_description' => $item_description,
				'gacha_models' => $request['gacha_models'],
				'gacha_wh_categories_id' => $request['gacha_wh_categories_id'],
				'msrp' => $request['msrp'],
				'current_srp' => $request['current_srp'],
				'no_of_tokens' => $request['no_of_tokens'],
				'dp_ctn' => $request['dp_ctn'],
				'pcs_dp' => $request['pcs_dp'],
				'moq' => $request['moq'],
				'no_of_assort' => $request['no_of_assort'],
				'gacha_countries_id' => $request['gacha_countries_id'],
				'gacha_incoterms_id' => $request['gacha_incoterms_id'],
				'currencies_id' => $request['currencies_id'],
				'supplier_cost' => $request['supplier_cost'],
				'gacha_uoms_id' => $request['gacha_uoms_id'],
				'gacha_inventory_types_id' => $request['gacha_inventory_types_id'],
				'gacha_vendor_types_id' => $request['gacha_vendor_types_id'],
				'gacha_vendor_groups_id' => $request['gacha_vendor_groups_id'],
				'age_grade' => $request['age_grade'],
				'battery' => $request['battery'],
				'created_at' => $time_stamp,
				'created_by' => $action_by,
			];


			GachaItemApproval::insert($data);

			$message = "✔️ New Item: $item_description added pending for Approval";

			return redirect(CRUDBooster::adminPath('gacha_item_masters'))->with([
				'message_type' => 'success',
				'message' => $message,
			]);
		}

		public function submitEditItem(Request $request) {
			$request = $request->all();
			$time_stamp = date('Y-m-d H:i:s');
			$action_by = CRUDBooster::myId();
			$digits_code = $request['digits_code'];
			$gacha_item_master_approvals_id = $request['gacha_item_master_approvals_id'];
			$item_description = $request['item_description'];

			$data = [
				'approval_status' => 202,
				'jan_no' => $request['jan_no'],
				'digits_code' => $request['digits_code'],
				'item_no' => $request['item_no'],
				'sap_no' => $request['sap_no'],
				'gacha_brands_id' => $request['gacha_brands_id'],
				'gacha_sku_statuses_id' => $request['gacha_sku_statuses_id'],
				'item_description' => $item_description,
				'gacha_models' => $request['gacha_models'],
				'gacha_wh_categories_id' => $request['gacha_wh_categories_id'],
				'msrp' => $request['msrp'],
				'current_srp' => $request['current_srp'],
				'no_of_tokens' => $request['no_of_tokens'],
				'dp_ctn' => $request['dp_ctn'],
				'pcs_dp' => $request['pcs_dp'],
				'moq' => $request['moq'],
				'no_of_assort' => $request['no_of_assort'],
				'gacha_countries_id' => $request['gacha_countries_id'],
				'gacha_incoterms_id' => $request['gacha_incoterms_id'],
				'currencies_id' => $request['currencies_id'],
				'supplier_cost' => $request['supplier_cost'],
				'gacha_uoms_id' => $request['gacha_uoms_id'],
				'gacha_inventory_types_id' => $request['gacha_inventory_types_id'],
				'gacha_vendor_types_id' => $request['gacha_vendor_types_id'],
				'gacha_vendor_groups_id' => $request['gacha_vendor_groups_id'],
				'age_grade' => $request['age_grade'],
				'battery' => $request['battery'],
				'updated_at' => $time_stamp,
				'updated_by' => $action_by,
			];

			GachaItemApproval::updateOrInsert(['id' => $gacha_item_master_approvals_id], $data);


		}


	}