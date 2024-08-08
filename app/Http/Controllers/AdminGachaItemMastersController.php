<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use App\GachaItemApproval;
	use App\GachaItemMaster;

	class AdminGachaItemMastersController extends \crocodicstudio\crudbooster\controllers\CBController {

		private $editor_details;
		private $editor_accounting;

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
			$this->editor_details = ['MCB TM','MCB TL'];
			$this->editor_accounting = ['COST ACCTG', 'ACCTG HEAD'];
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
			$this->button_add = true;
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "gacha_item_masters";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"DIGITS CODE","name"=>"digits_code"];
			$this->col[] = ["label"=>"JAN NUMBER","name"=>"jan_no"];
			$this->col[] = ["label"=>"ITEM NUMBER","name"=>"item_no"];
			$this->col[] = ["label"=>"SAP NUMBER","name"=>"sap_no"];
			$this->col[] = ["label"=>"INITIAL WRR DATE (YYYY-MM-DD)","name"=>"initial_wrr_date"];
			$this->col[] = ["label"=>"LATEST WRR DATE (YYYY-MM-DD)","name"=>"latest_wrr_date"];
			$this->col[] = ["label"=>"PRODUCT TYPE","name"=>"gacha_product_types_id","join"=>"gacha_product_types,product_type_description"];
			$this->col[] = ["label"=>"BRAND DESCRIPTION","name"=>"gacha_brands_id","join"=>"gacha_brands,brand_description"];
			$this->col[] = ["label"=>"BRAND STATUS","name"=>"gacha_brands_id","join"=>"gacha_brand_statuses,status_description","join_id"=>"id"];
			$this->col[] = ["label"=>"SKU STATUS","name"=>"gacha_sku_statuses_id","join"=>"gacha_sku_statuses,status_description"];
			$this->col[] = ["label"=>"ITEM DESCRIPTION","name"=>"item_description"];
			$this->col[] = ['label'=>'MODEL','name'=>'gacha_models'];
			$this->col[] = ['label'=>'CATEGORY','name'=>'gacha_categories_id','join'=>'gacha_categories,category_description'];
			$this->col[] = ['label'=>'WH CATEGORY DESCRIPTION','name'=>'gacha_wh_categories_id','join'=>'gacha_wh_categories,category_description'];
			$this->col[] = ['label'=>'MSRP JPY','name'=>'msrp'];
			$this->col[] = ['label'=>'CURRENT SRP','name'=>'current_srp'];
			$this->col[] = ['label'=>'NUMBER OF TOKENS','name'=>'no_of_tokens'];
			if(!in_array(CRUDBooster::myPrivilegeName(),['IC TM','REPORTS'])){
				if(!in_array(CRUDBooster::myPrivilegeName(),['IC TM','REPORTS, WIMS TL'])){
					$this->col[] = ['label'=>'LC PER CARTON','name'=>'lc_per_carton'];
					$this->col[] = ['label'=>'LC PER PC','name'=>'lc_per_pc'];
					$this->col[] = ['label'=>'LC MARGIN PER PC (%)','name'=>'lc_margin_per_pc'];
				}
			$this->col[] = ['label'=>'SC PER PC','name'=>'store_cost'];
			$this->col[] = ['label'=>'SC MARGIN PER PC (%)','name'=>'sc_margin'];
			$this->col[] = ['label'=>'PCS PER CTN','name'=>'pcs_ctn'];
			$this->col[] = ['label'=>'DP PER CTN','name'=>'dp_ctn'];
			$this->col[] = ['label'=>'PCS PER DP','name'=>'pcs_dp'];
			$this->col[] = ['label'=>'MOQ','name'=>'moq'];
			$this->col[] = ['label'=>'ORDER CTN','name'=>'no_of_ctn'];
			$this->col[] = ['label'=>'NUMBER OF ASSORT','name'=>'no_of_assort'];
			$this->col[] = ['label'=>'COUNTRY OF ORIGIN','name'=>'gacha_countries_id','join'=>'gacha_countries,country_code'];
			$this->col[] = ['label'=>'INCOTERMS','name'=>'gacha_incoterms_id','join'=>'gacha_incoterms,incoterm_description'];
			}
			$this->col[] = ['label'=>'CURRENCY','name'=>'currencies_id','join'=>'currencies,currency_code'];
			if(!in_array(CRUDBooster::myPrivilegeName(),['REPORTS'])){
			$this->col[] = ['label'=>'SUPPLIER COST','name'=>'supplier_cost'];
			}
			$this->col[] = ['label'=>'UOM CODE','name'=>'gacha_uoms_id','join'=>'gacha_uoms,uom_code'];
			$this->col[] = ['label'=>'INVENTORY TYPE','name'=>'gacha_inventory_types_id','join'=>'gacha_inventory_types,inventory_type_description'];
			$this->col[] = ['label'=>'VENDOR TYPE','name'=>'gacha_vendor_types_id','join'=>'gacha_vendor_types,vendor_type_description'];
			$this->col[] = ["label"=>"VENDOR GROUP NAME","name"=>"gacha_vendor_groups_id","join"=>"gacha_vendor_groups,vendor_group_description"];
			$this->col[] = ["label"=>"VENDOR GROUP STATUS","name"=>"gacha_vendor_groups_id","join"=>"gacha_vendor_group_statuses,status_description","join_id"=>"id"];
			$this->col[] = ['label'=>'AGE GRADE','name'=>'age_grade'];
			$this->col[] = ['label'=>'BATTERY','name'=>'battery'];
			$this->col[] = ["label"=>"CREATED BY","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"CREATED DATE","name"=>"created_at"];
			if(!in_array(CRUDBooster::myPrivilegeName(),['IC TM','REPORTS'])){
			$this->col[] = ["label"=>"APPROVED BY","name"=>"approved_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"APPROVED DATE","name"=>"approved_at"];
			$this->col[] = ["label"=>"APPROVED BY ACCTNG","name"=>"approved_by_acct","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"APPROVED DATE ACCTNG","name"=>"approved_at_acct"];
			$this->col[] = ["label"=>"UPDATED BY","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"UPDATED DATE","name"=>"updated_at"];
			$this->col[] = ["label"=>"status","name"=>"approval_status_acct" ,'callback'=>function($row){
				return $row->status;
			}, "visible"=> false];
			}
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
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
				if (in_array(CRUDBooster::myPrivilegeName(), $this->editor_details) || CRUDBooster::isSuperAdmin()) {
					$this->addaction[] = [
						'title'=>'Edit',
						'url'=>CRUDBooster::mainpath('edit/[id]'),
						'icon'=>'fa fa-pencil',
						'color' => ' ',
					];
				}
				if (in_array(CRUDBooster::myPrivilegeName(), $this->editor_accounting) || CRUDBooster::isSuperAdmin()) {
					$this->addaction[] = [
						'title'=>'Edit Accounting Details',
						'url'=>CRUDBooster::mainpath('edit-item-accounting-detail/[id]'),
						'icon'=>'fa fa-pencil',
						'color' => ' ',
						'showIf' => "[status] != 202",
					];
				}


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
            if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Status ACTIVE","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
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
			if(CRUDBooster::getCurrentMethod() == 'getIndex') {
				if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['MCB TM','MCB TL'])){
					$this->index_button[] = ["title"=>"Import Items","label"=>"Import Items",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-view')];
					
				}
				if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['COST ACCTG'])) {
					$this->index_button[] = ["title"=>"Import Items","label"=>"Import Updates",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-edit-view')];
				}
			}

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
	        $query->addSelect('gacha_item_master_approvals.approval_status_acct as status')->leftJoin('gacha_item_master_approvals', 'gacha_item_masters.digits_code', 'gacha_item_master_approvals.digits_code' );    
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

		public function getAdd() {
			if (!CRUDBooster::isCreate()) CRUDBooster::redirect(
				CRUDBooster::adminPath(),
				trans('crudbooster.denied_access')
			);

			$data = [];
			$data['page_title'] = 'Add Item';
			$data['action'] = 'add';

			$data = array_merge($data, self::getSubmaster());

			return view('gacha/item-masters/add-item', $data);
		}

		public function getEdit($id) {
			if (!CRUDBooster::isUpdate()) CRUDBooster::redirect(
				CRUDBooster::adminPath(),
				trans('crudbooster.denied_access')
			);

			$digits_code = DB::table('gacha_item_masters')->where('id', $id)->pluck('digits_code')->first();

			$data = [];
			$data['item'] = (object) GachaItemApproval::where('digits_code', $digits_code)->first()->toArray();
			$data['gacha_item_master_approvals_id'] = $data['item']->id;
			$data['page_title'] = 'Edit Item';
			$data['action'] = 'edit';
			$data['path'] = 'gasha_item_masters';
			$data = array_merge($data, self::getSubmaster());

			return view('gacha/item-masters/add-item',$data);
		}

		public function getDetail($id) {
			$data = [];
			$data['item'] = self::getItemDetails($id, 'gacha_item_masters_export');
			return view('gacha/item-masters/detail-item', $data);
		}

		public function importItemView() {
			if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {    
				CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
			}
			$data['page_title'] = 'Import New Item';
			return view('gacha/item-masters/new-item-upload',$data);
		}

		public function importItemEditView() {
			if(!CRUDBooster::isUpdate() && $this->global_privilege==FALSE || $this->button_edit==FALSE) {    
				CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
			}
			$data['page_title'] = 'Import Updates';
			return view('gacha/item-masters/upload-edit-item-acct',$data);
		}
		
		public function getSubmaster() {
			$data = [];

			$data['product_types'] = DB::table('gacha_product_types')
				->where('status', 'ACTIVE')
				->orderBy('product_type_description')
				->get()
				->toArray();

			$data['brands'] = DB::table('gacha_brands')
				->where('status', 'ACTIVE')
				->orderBy('brand_description')
				->get()
				->toArray();

			$data['sku_statuses'] = DB::table('gacha_sku_statuses')
				->where('status', 'ACTIVE')
				->orderBy('status_description')
				->get()
				->toArray();
			
			$data['categories'] = DB::table('gacha_categories')
				->where('status', 'ACTIVE')
				->orderBy('category_description')
				->get()
				->toArray();

			$data['warehouse_categories'] = DB::table('gacha_wh_categories')
				->where('status', 'ACTIVE')
				->orderBy('category_description')
				->get()
				->toArray();

			$data['countries'] = DB::table('gacha_countries')
				->where('status', 'ACTIVE')
				->orderBy('country_name')
				->get()
				->toArray();

			$data['incoterms'] = DB::table('gacha_incoterms')
				->where('status', 'ACTIVE')
				->orderBy('incoterm_description')
				->get()
				->toArray();

			$data['currencies'] = DB::table('currencies')
				->where('status', 'ACTIVE')
				->orderBy('currency_description')
				->get()
				->toArray();

			$data['uoms'] = DB::table('gacha_uoms')
				->where('status', 'ACTIVE')
				->orderBy('uom_code')
				->get()
				->toArray();

			$data['inventory_types'] = DB::table('gacha_inventory_types')
				->where('status', 'ACTIVE')
				->orderBy('inventory_type_description')
				->get()
				->toArray();

			$data['vendor_types'] = DB::table('gacha_vendor_types')
				->where('status', 'ACTIVE')
				->orderBy('vendor_type_description')
				->get()
				->toArray();

			$data['vendor_groups'] = DB::table('gacha_vendor_groups')
				->where('status', 'ACTIVE')
				->orderBy('vendor_group_description')
				->get()
				->toArray();

			return $data;
		}

		public function getItemDetails($id, $view_name) {
			if ($view_name == 'gacha_item_masters_export') {
				$primary_key = 'gacha_item_masters_id';
			} else if ($view_name == 'gacha_item_master_approvals_export') {
				$primary_key = 'gacha_item_master_approvals_id';
			}
			$item = DB::table($view_name)->where($primary_key, $id)->get()->first();
			return $item;
		}

		public function getApiItems($secret_key) {
			if ($secret_key != config('key-api.secret_key')) {
				return response([
					'message' => 'Error: Bad Request',
				], 404);
			}
	
			$created_items = GachaItemMaster::GenerateExport()
				->whereBetween(DB::raw('DATE(approved_at_acct)'), [date('Y-m-d',strtotime("-1 days")), date('Y-m-d')])
				->get()
				->toArray();
				
			return response()->json([
				'created_items' => $created_items
			]);
		}

	}