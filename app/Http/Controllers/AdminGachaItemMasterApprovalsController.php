<?php namespace App\Http\Controllers;

	use Session;
	use Illuminate\Http\Request;
	use DB;
	use CRUDBooster;
	use App\GachaItemApproval;
	use App\GachaItemMaster;
	use Schema;

	class AdminGachaItemMasterApprovalsController extends \crocodicstudio\crudbooster\controllers\CBController {

		private $approver;
		private $main_controller;

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
			$this->approver = [];
			$this->main_controller = new AdminGachaItemMastersController;

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
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "gacha_item_master_approvals";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Approval Status","name"=>"approval_status"];
			$this->col[] = ["label"=>"JAN Number","name"=>"jan_no"];
			$this->col[] = ["label"=>"Digits Code","name"=>"digits_code"];
			$this->col[] = ["label"=>"Item Number","name"=>"item_no"];
			$this->col[] = ["label"=>"SAP Number","name"=>"sap_no"];
			$this->col[] = ["label"=>"Initial WRR Date","name"=>"initial_wrr_date"];
			$this->col[] = ["label"=>"Latest WRR Date","name"=>"latest_wrr_date"];
			$this->col[] = ["label"=>"Brand","name"=>"gacha_brands_id","join"=>"gacha_brands,brand_description"];
			$this->col[] = ["label"=>"Brand Status","name"=>"gacha_brands_id","join"=>"gacha_brand_statuses,status_description","join_id"=>"id"];
			$this->col[] = ["label"=>"SKU Status","name"=>"gacha_sku_statuses_id","join"=>"gacha_sku_statuses,status_description"];
			$this->col[] = ["label"=>"Item Description","name"=>"item_description"];
			$this->col[] = ['label'=>'Model','name'=>'gacha_models'];
			$this->col[] = ['label'=>'WH Category','name'=>'gacha_wh_categories_id','join'=>'gacha_wh_categories,category_description'];
			$this->col[] = ['label'=>'MSRP JPY','name'=>'msrp'];
			$this->col[] = ['label'=>'Current SRP','name'=>'current_srp'];
			$this->col[] = ['label'=>'Number of Tokens','name'=>'no_of_tokens'];
			$this->col[] = ['label'=>'LC Per Carton','name'=>'lc_per_carton'];
			$this->col[] = ['label'=>'LC Margin Per Carton (%)','name'=>'lc_margin_per_carton'];
			$this->col[] = ['label'=>'LC Per PC','name'=>'lc_per_pc'];
			$this->col[] = ['label'=>'LC Margin Per PC (%)','name'=>'lc_margin_per_pc'];
			$this->col[] = ['label'=>'SC Per PC','name'=>'store_cost'];
			$this->col[] = ['label'=>'SC Margin Per PC (%)','name'=>'sc_margin'];
			$this->col[] = ['label'=>'PCS Per CTN','name'=>'pcs_ctn'];
			$this->col[] = ['label'=>'DP Per CTN','name'=>'dp_ctn'];
			$this->col[] = ['label'=>'PCS Per DP','name'=>'pcs_dp'];
			$this->col[] = ['label'=>'MOQ','name'=>'moq'];
			$this->col[] = ['label'=>'Order CTN','name'=>'no_of_ctn'];
			$this->col[] = ['label'=>'Number of Assort','name'=>'no_of_assort'];
			$this->col[] = ['label'=>'Country of Origin','name'=>'gacha_countries_id','join'=>'gacha_countries,country_code'];
			$this->col[] = ['label'=>'Incoterms','name'=>'gacha_incoterms_id','join'=>'gacha_incoterms,incoterm_description'];
			$this->col[] = ['label'=>'Currency','name'=>'currencies_id','join'=>'currencies,currency_code'];
			$this->col[] = ['label'=>'Supplier Cost','name'=>'supplier_cost'];
			$this->col[] = ['label'=>'UOM','name'=>'gacha_uoms_id','join'=>'gacha_uoms,uom_code'];
			$this->col[] = ['label'=>'Inventory Type','name'=>'gacha_inventory_types_id','join'=>'gacha_inventory_types,inventory_type_description'];
			$this->col[] = ['label'=>'Vendor Type','name'=>'gacha_vendor_types_id','join'=>'gacha_vendor_types,vendor_type_description'];
			$this->col[] = ["label"=>"Vendor Group","name"=>"gacha_vendor_groups_id","join"=>"gacha_vendor_groups,vendor_group_description"];
			$this->col[] = ["label"=>"Vendor Group Status","name"=>"gacha_vendor_groups_id","join"=>"gacha_vendor_group_statuses,status_description","join_id"=>"id"];
			$this->col[] = ['label'=>'Age Grade','name'=>'age_grade'];
			$this->col[] = ['label'=>'Battery','name'=>'battery'];
			$this->col[] = ['label'=>'Created Date','name'=>'created_at'];
			$this->col[] = ['label'=>'Updated Date','name'=>'updated_at'];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'JAN Number','name'=>'jan_no','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Digits Code','name'=>'digits_code','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Item Number','name'=>'item_no','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'SAP Number','name'=>'sap_no','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Initial WRR Date','name'=>'initial_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Latest WRR Date','name'=>'latest_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Brand','name'=>'gacha_brands_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_brands,brand_description'];
			$this->form[] = ['label'=>'SKU Status','name'=>'gacha_sku_statuses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_sku_statuses,status_description'];
			$this->form[] = ['label'=>'Item Description','name'=>'item_description','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Model','name'=>'gacha_models','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'WH Category','name'=>'gacha_wh_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_wh_categories,category_description'];
			$this->form[] = ['label'=>'MSRp','name'=>'msrp','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Current SRP','name'=>'current_srp','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'No of Tokens','name'=>'no_of_tokens','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Store Cost','name'=>'store_cost','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'SC Margin','name'=>'sc_margin','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'LC Per Pc','name'=>'lc_per_pc','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'LC Margin Per Pc','name'=>'lc_margin_per_pc','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'LC Per Carton','name'=>'lc_per_carton','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'LC Margin Per Carton','name'=>'lc_margin_per_carton','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'DP / Ctn','name'=>'dp_ctn','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Pcs / DP','name'=>'pcs_dp','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'MOQ','name'=>'moq','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'No Of Assort','name'=>'no_of_assort','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Original Country','name'=>'gacha_countries_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_countries,country_name'];
			$this->form[] = ['label'=>'Incoterm','name'=>'gacha_incoterms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_incoterms,incoterm_description'];
			$this->form[] = ['label'=>'Currency','name'=>'currencies_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'currencies,currency_code'];
			$this->form[] = ['label'=>'Supplier Cost','name'=>'supplier_cost','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'UOM','name'=>'gacha_uoms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_uoms,uom_description'];
			$this->form[] = ['label'=>'Inventory Type','name'=>'gacha_inventory_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_inventory_types,inventory_type_description'];
			$this->form[] = ['label'=>'Vendor Type','name'=>'gacha_vendor_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_vendor_types,vendor_type_description'];
			$this->form[] = ['label'=>'Vendor Groups','name'=>'gacha_vendor_groups_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'gacha_vendor_groups,vendor_group_description'];
			$this->form[] = ['label'=>'Age Grade','name'=>'age_grade','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Battery','name'=>'battery','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Approved By','name'=>'approved_by','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'cms_users,name'];
			$this->form[] = ['label'=>'Approved At','name'=>'approved_at','type'=>'datetime','validation'=>'required|date_format:Y-m-d H:i:s','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Created By','name'=>'created_by','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'cms_users,name'];
			$this->form[] = ['label'=>'Created At','name'=>'created_at','type'=>'datetime','validation'=>'required|date_format:Y-m-d H:i:s','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Updated By','name'=>'updated_by','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'cms_users,name'];
			$this->form[] = ['label'=>'Updated At','name'=>'updated_at','type'=>'datetime','validation'=>'required|date_format:Y-m-d H:i:s','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Status','name'=>'status','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
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

			if (CRUDBooster::isUpdate()) {
				$this->addaction[] = [
					'title'=>'Edit',
					'url'=>CRUDBooster::mainpath('edit/[id]'),
					'icon'=>'fa fa-pencil',
					'color' => ' ',
					"showIf"=>"[approval_status] == 400",
				];
			}



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
	        return self::approveOrReject($id_selected, $button_name);     
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
			$item_description = trim(strtoupper(preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request['item_description'])));

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
				'pcs_ctn' => $request['pcs_ctn'],
				'no_of_ctn' => $request['no_of_ctn'],
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

			$message = "✔️ New Item: $item_description is added pending for approval";

			return redirect(CRUDBooster::adminPath('gasha_item_masters'))->with([
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
			$item_description = trim(strtoupper(preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request['item_description'])));

			$data = [
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
				'pcs_ctn' => $request['pcs_ctn'],
				'no_of_ctn' => $request['no_of_ctn'],
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

			if ($digits_code) {
				// means already approved... just updating the details
				$message = "✔️ Item Details: $item_description successfully updated.";
				$approval_item = GachaItemApproval::where('digits_code', $digits_code);
				$approval_item->update($data);
				$differences = self::getDifferences($approval_item->first()->id);
				if ($differences) self::createHistory($differences, $digits_code);
				GachaItemMaster::where('digits_code', $digits_code)->update($data);
				
			} else {
				// means rejected newly created item but updating the item
				$data['approval_status'] = 202;
				GachaItemApproval::updateOrInsert(['id' => $gacha_item_master_approvals_id], $data);
				$message = "✔️ Item: $item_description is added pending for approval";
			}

			return redirect(CRUDBooster::adminPath($request['path']))->with([
				'message_type' => 'success',
				'message' => $message,
			]);

		}

		public function getEdit($id) {
			if (!CRUDBooster::isUpdate()) CRUDBooster::redirect(
				CRUDBooster::adminPath(),
				trans('crudbooster.denied_access')
			);

			$data = [];
			$data['item'] = (object) GachaItemApproval::where('id', $id)->first()->toArray();
			$data['gacha_item_master_approvals_id'] = $data['item']->id;
			$data['page_title'] = 'Edit Item';
			$data['action'] = 'edit';
			$data['path'] = 'gasha_item_master_approvals';
			$data = array_merge($data, $this->main_controller->getSubmaster());

			return view('gacha/item-masters/add-item',$data);
		}

		public function approveOrReject($ids, $action) {
			if (!is_array($ids)) $ids = [$ids];

			foreach($ids as $id) {
				$item = DB::table('gacha_item_master_approvals')
					->where('id', $id)
					->first();
				
				if ($item->approval_status == 200) return;

				if ($action == 'approve') {
					$digits_code = $item->digits_code;
					if (!$digits_code) {
						$digits_code = self::createDigitsCode();
					}
					$differences = self::getDifferences($id);
					if ($differences) self::createHistory($differences, $digits_code);
					$item = DB::table('gacha_item_master_approvals')
						->where('id', $id);
					$update = $item->update([
						'approval_status' => 200,
						'digits_code' => $digits_code,
						'approved_at' => date('Y-m-d H:i:s'),
						'approved_by' => CRUDBooster::myId(),
					]);
					$item = $item->first();
					unset($item->id);

					DB::table('gacha_item_masters')->updateOrInsert(['digits_code' => $digits_code], (array) $item);
					$message = "✔️ Successfully approved Item: $digits_code";
					$message_type = "success";
					
				} else if ($action == 'reject') {
					DB::table('gacha_item_master_approvals')
						->where('id', $id)
						->update(['approval_status' => 400]);

					$message = "👎🏽 Successfully rejected Item.";
					$message_type = "info";
				}
			}
			return redirect(CRUDBooster::mainPath())->with([
				'message_type' => $message_type,
				'message' => $message,
			]);
		}

		public function createDigitsCode() {
			$current_max_digits_code = DB::table('gacha_item_masters')
				->max('digits_code');

			if (!$current_max_digits_code) {
				return 600000000;
			}
			
			return $current_max_digits_code + 1;
		}

		public function getDifferences($id) {
			$new_values = DB::table('gacha_item_master_approvals')
				->where('id', $id)
				->first();

			if (!$new_values->digits_code) {
				return false;
			}

			$old_values = DB::table('gacha_item_masters')
				->where('digits_code', $new_values->digits_code)
				->first();

			$column_names = array_keys((array) $old_values);
			$differences = [];
			$column_differences = [];

			foreach ($column_names as $column) {
				$old_value = $old_values->{$column};
				$new_value = $new_values->{$column};
				if ($new_value != $old_value) {
					$differences[$column]['old'] = $old_value;
					$differences[$column]['new'] = $new_value;
					$column_differences[] = $column;
				}
			}

			$data = [
				'differences' => $differences,
				'column_differences' => $column_differences,
			];

			return $data;
		}

		public function createHistory($differences, $digits_code) {
			$to_be_recorded = [
				'msrp',
				'current_srp',
				'store_cost',
				'sc_margin',
				'lc_per_pc',
				'lc_margin_per_pc',
				'lc_per_carton',
				'lc_margin_per_carton',
				'supplier_cost',
			];

			$data = [];

			foreach ($differences['column_differences'] as $column) {
				if (in_array($column, $to_be_recorded)) {
					$data['old_' . $column] = $differences['differences'][$column]['old'];
					$data['new_' . $column] = $differences['differences'][$column]['new'];
				}
			}
			$data['digits_code'] = $digits_code;
			$data['history_json'] = json_encode($differences['differences']);
			$data['created_at'] = date('Y-m-d H:i:s');
			$data['created_by'] = CRUDBooster::myId();

			DB::table('gacha_item_histories')->insert($data);
		}

	}