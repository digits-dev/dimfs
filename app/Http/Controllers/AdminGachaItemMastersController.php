<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use App\GachaItemApproval;
	use App\GachaItemMaster;

	class AdminGachaItemMastersController extends \crocodicstudio\crudbooster\controllers\CBController {

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
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
			$this->button_edit = true;
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
			$this->col[] = ["label"=>"Digits Code","name"=>"digits_code"];
			$this->col[] = ["label"=>"JAN Number","name"=>"jan_no"];
			$this->col[] = ["label"=>"SAP Number","name"=>"sap_no"];
			$this->col[] = ["label"=>"Item No","name"=>"item_no"];
			$this->col[] = ["label"=>"Initial WRR Date","name"=>"initial_wrr_date"];
			$this->col[] = ["label"=>"Latest WRR Date","name"=>"latest_wrr_date"];
			$this->col[] = ["label"=>"Brand","name"=>"gacha_brands_id","join"=>"gacha_brands,brand_description"];
			$this->col[] = ["label"=>"SKU Status","name"=>"gacha_sku_statuses_id","join"=>"gacha_sku_statuses,status_description"];
			$this->col[] = ["label"=>"Item Description","name"=>"item_description"];
			$this->col[] = ['label'=>'Model','name'=>'gacha_models'];
			$this->col[] = ['label'=>'WH Category','name'=>'gacha_wh_categories_id','join'=>'gacha_wh_categories,category_description'];
			$this->col[] = ['label'=>'MSRP JPY','name'=>'msrp'];
			$this->col[] = ['label'=>'Current SRP','name'=>'current_srp'];
			$this->col[] = ['label'=>'MOQ','name'=>'moq'];
			$this->col[] = ['label'=>'Country of Origin','name'=>'gacha_countries_id','join'=>'gacha_countries,country_code'];
			$this->col[] = ['label'=>'Incoterms','name'=>'gacha_incoterms_id','join'=>'gacha_incoterms,incoterm_description'];
			$this->col[] = ['label'=>'Currency','name'=>'currencies_id','join'=>'currencies,currency_code'];
			$this->col[] = ['label'=>'UOM','name'=>'gacha_uoms_id','join'=>'gacha_uoms,uom_code'];
			$this->col[] = ['label'=>'Inventory Type','name'=>'gacha_inventory_types_id','join'=>'gacha_inventory_types,inventory_type_description'];
			$this->col[] = ['label'=>'Vendor Type','name'=>'gacha_vendor_types_id','join'=>'gacha_vendor_types,vendor_type_description'];
			$this->col[] = ['label'=>'Vendor Group','name'=>'gacha_vendor_groups_id','join'=>'gacha_vendor_groups,vendor_group_description'];
			$this->col[] = ['label'=>'Age Grade','name'=>'age_grade'];
			$this->col[] = ['label'=>'Battery','name'=>'battery'];
			$this->col[] = ['label'=>'Created By','name'=>'created_by','join'=>'cms_users,name'];
			$this->col[] = ['label'=>'Created Date','name'=>'created_at'];
			$this->col[] = ['label'=>'Updated By','name'=>'updated_by','join'=>'cms_users,name'];
			$this->col[] = ['label'=>'Updated Date','name'=>'updated_at'];
			$this->col[] = ['label'=>'Approved By','name'=>'approved_by','join'=>'cms_users,name'];
			$this->col[] = ['label'=>'Approved Date','name'=>'approved_at'];
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
			// $item = GachaItemMaster::where('id', $id)->first();
			// $item['gacha_item_masters_id'] = $id;
			// //insert to approval table
			// ItemMasterApproval::insert($item->toArray());

			// $module_id = CRUDBooster::getCurrentModule()->id;
			// if(!is_null($item) && (CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == 'ADVANCED')){
			// 	Counter::where('cms_moduls_id',$module_id)->increment('code_6');
			// }
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

		public function importItemView() {
			if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {    
				CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
			}
			$data['page_title'] = 'Import New Item';
			return view('gacha/item-masters/new-item-upload',$data);
		}

		public function getSubmaster() {
			$data = [];
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

	}