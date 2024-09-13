<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use Illuminate\Support\Facades\DB;
	use CRUDBooster;
	use Excel;
	use App\Counter;
	use App\Brand;
	use App\RmaCategory;
	use App\Color;
	use App\Currency;
	use App\Size;
	use App\RmaMarginCategory;
	use App\RmaModelSpecific;
	use App\StatusState;
	use App\SkuStatus;
	use App\InventoryType;
	use App\RmaItemMaster;

	class AdminRmaItemMastersController extends \crocodicstudio\crudbooster\controllers\CBController {
	    
	    private $approved;
    	private $rejected;
    	private $pending;
    	private $active;
    	private $invalid;
    	private $inactive_inventory;
    	private $trade_inventory;

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
            $this->approved = StatusState::where('status_state','APPROVED')->value('id');
    		$this->rejected = StatusState::where('status_state','REJECTED')->value('id');
    		$this->pending = StatusState::where('status_state','PENDING')->value('id');
    		$this->active = SkuStatus::where('sku_status_description','ACTIVE')->value('id');
    		$this->invalid = SkuStatus::where('sku_status_description','INVALID')->value('id');
    		$this->inactive_inventory = InventoryType::where('inventory_type_description','INACTIVE')->value('id');
    		$this->trade_inventory = InventoryType::where('inventory_type_description','TRADE')->value('id');
        }

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "upc_code";
			$this->limit = "20";
			$this->orderby = "id,desc";
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
			$this->table = "rma_item_masters";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Digits Code","name"=>"digits_code"];
			$this->col[] = ["label"=>"UPC Code","name"=>"upc_code"];
			$this->col[] = ["label"=>"Supplier Item Code","name"=>"supplier_item_code"];
			$this->col[] = ["label"=>"Item Description","name"=>"item_description"];
			$this->col[] = ["label"=>"Brand Description","name"=>"brands_id","join"=>"brands,brand_description"];
			$this->col[] = ["label"=>"Category Description","name"=>"rma_categories_id","join"=>"rma_categories,category_description"];
			$this->col[] = ["label"=>"Class Description","name"=>"rma_classes_id","join"=>"rma_classes,class_description"];
			$this->col[] = ["label"=>"Subclass","name"=>"rma_subclasses_id","join"=>"rma_subclasses,subclass_description"];
			$this->col[] = ["label"=>"Model","name"=>"model"];
			$this->col[] = ["label"=>"Model Specific Description","name"=>"rma_model_specifics_id","join"=>"rma_model_specifics,model_specific_description"];
			$this->col[] = ["label"=>"Actual Color","name"=>"actual_color"];
			$this->col[] = ["label"=>"Size","name"=>"size"];
			$this->col[] = ["label"=>"UOM","name"=>"rma_uoms_id","join"=>"rma_uoms,uom_code"];
			$this->col[] = ["label"=>"Inventory Type","name"=>"inventory_types_id","join"=>"inventory_types,inventory_type_description"];
			$this->col[] = ['label'=>'Store Cost','name'=>'dtp_rf'];
			$this->col[] = ['label'=>'Store Margin Percentage','name'=>'dtp_rf_percentage'];
			$this->col[] = ['label'=>'Original SRP','name'=>'original_srp'];
			$this->col[] = ['label'=>'Current SRP','name'=>'current_srp'];
			$this->col[] = ['label'=>'MOQ','name'=>'moq'];
			$this->col[] = ['label'=>'Currency','name'=>'currencies_id',"join"=>"currencies,currency_code"];
			$this->col[] = ['label'=>'Purchase Price','name'=>'purchase_price'];
			$this->col[] = ["label"=>"Vendor Name","name"=>"vendors_id","join"=>"vendors,vendor_name"];
			
			$this->col[] = ['label'=>'Warranty Duration','name'=>'warranty_duration'];
			$this->col[] = ['label'=>'Warranty Duration Type','name'=>'warranties_id',"join"=>"warranties,warranty_description"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit','postEditSave'])){
			    $this->form[] = ['label'=>'Digits Code','name'=>'digits_code','type'=>'text','validation'=>'required|min:8|max:8','width'=>'col-sm-6','readonly'=>true];
			}
			
			$this->form[] = ['label'=>'UPC Code','name'=>'upc_code','type'=>'text','validation'=>'required|min:1|max:60','width'=>'col-sm-6'];
			
			if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit','postEditSave'])){
    			$this->form[] = ['label'=>'UPC Code 2','name'=>'upc_code2','type'=>'text','validation'=>'max:60','width'=>'col-sm-6'];
    			$this->form[] = ['label'=>'UPC Code 3','name'=>'upc_code3','type'=>'text','validation'=>'max:60','width'=>'col-sm-6'];
    			$this->form[] = ['label'=>'UPC Code 4','name'=>'upc_code4','type'=>'text','validation'=>'max:60','width'=>'col-sm-6'];
    			$this->form[] = ['label'=>'UPC Code 5','name'=>'upc_code5','type'=>'text','validation'=>'max:60','width'=>'col-sm-6'];
			}
			
			$this->form[] = ['label'=>'Supplier Item Code','name'=>'supplier_item_code','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Brand Description','name'=>'brands_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'brands,brand_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Vendor','name'=>'vendors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendors,vendor_name',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Vendor Type','name'=>'vendor_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendor_types,vendor_type_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Item Description','name'=>'item_description','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Category Description','name'=>'rma_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'rma_categories,category_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Class Description','name'=>'rma_classes_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'rma_classes,class_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Subclass','name'=>'rma_subclasses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'rma_subclasses,subclass_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
// 			$this->form[] = ['label'=>'Store Category','name'=>'rma_store_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'rma_store_categories,store_category_description'];
// 			$this->form[] = ['label'=>'Margin Category','name'=>'rma_margin_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'rma_margin_categories,margin_category_description'];
			$this->form[] = ['label'=>'Warehouse Category','name'=>'warehouse_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'warehouse_categories,warehouse_category_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Model','name'=>'model','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Model Specific','name'=>'rma_model_specifics_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'rma_model_specifics,model_specific_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Colors','name'=>'colors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'colors,color_description',
    			'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Actual Color','name'=>'actual_color','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Size Value','name'=>'size_value','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Size','name'=>'sizes_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			    'datatable'=>'sizes,size_description',
			    'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'UOM','name'=>'rma_uoms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			    'datatable'=>'rma_uoms,uom_code',
			    'datatable_where'=>"status='ACTIVE'"
			];
// 			$this->form[] = ['label'=>'Incoterm','name'=>'incoterms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'incoterms,id'];
			$this->form[] = ['label'=>'Inventory Type','name'=>'inventory_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			    'datatable'=>'inventory_types,inventory_type_description',
			    'datatable_where'=>"status='ACTIVE'"
			];
			
			$this->form[] = ['label'=>'Serialized','name'=>'serialized','type'=>'checkbox','validation'=>'min:0','width'=>'col-sm-6',
    			'datatable'=>'item_identifiers,item_identifier',
    			'datatable_where'=>"status='ACTIVE'"
			];
			
			if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit','postEditSave'])){
    		    $this->form[] = ['label'=>'SKU Status','name'=>'sku_statuses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'sku_statuses,sku_status_description'];
    // 			$this->form[] = ['label'=>'SKU Legend','name'=>'sku_legends_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'sku_legends,sku_legend_description'];
			}
			
			$this->form[] = ['label'=>'Original SRP','name'=>'original_srp','type'=>'number','validation'=>'required','width'=>'col-sm-6','step'=>'0.01'];
			
			if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit','postEditSave'])){
			    $this->form[] = ['label'=>'Current SRP','name'=>'current_srp','type'=>'number','validation'=>'required','width'=>'col-sm-6','step'=>'0.01','readonly'=>true];
			 //   $this->form[] = ['label'=>'Promo SRP','name'=>'promo_srp','type'=>'number','validation'=>'required','width'=>'col-sm-6','step'=>'0.01'];
    			$this->form[] = ['label'=>'Price Change','name'=>'price_change','type'=>'number','validation'=>'required','width'=>'col-sm-6','step'=>'0.01'];
    			$this->form[] = ['label'=>'Effective Date','name'=>'effective_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-6'];
			}
			
		
			$this->form[] = ['label'=>'MOQ','name'=>'moq','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'Currency','name'=>'currencies_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			    'datatable'=>'currencies,currency_code',
			    'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Purchase Price','name'=>'purchase_price','type'=>'number','validation'=>'required','width'=>'col-sm-6','step'=>'0.01'];
			if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit','postEditSave'])){
                // $this->form[] = ['label'=>'Cost Factor','name'=>'cost_factor','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
    			$this->form[] = ['label'=>'Store Cost','name'=>'dtp_rf','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
    			$this->form[] = ['label'=>'Store Margin Percentage','name'=>'dtp_rf_percentage','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
    			$this->form[] = ['label'=>'Landed Cost','name'=>'landed_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
    // 			$this->form[] = ['label'=>'Working Landed Cost','name'=>'working_landed_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
    // 			$this->form[] = ['label'=>'Working Store Cost','name'=>'working_dtp_rf','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
    // 			$this->form[] = ['label'=>'Working Store Margin Percentage','name'=>'working_dtp_rf_percentage','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			}
			$this->form[] = ['label'=>'Warranty','name'=>'warranties_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			    'datatable'=>'warranties,warranty_description',
			    'datatable_where'=>"status='ACTIVE'"
			];
			$this->form[] = ['label'=>'Warranty Duration','name'=>'warranty_duration','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-6'];
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

			$this->index_button[] = ["title"=>"Import Module","label"=>"Import Module",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-rma-view')];


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------     
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.        
	        | 
	        */
	        $this->table_row_color = array();     	          
            $this->table_row_color[] = ["condition"=>"[inventory_types_id] == 1","color"=>"danger"];
	        
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
	        $this->load_js[] = asset("js/rma_item_master.js");
	        
	        
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
	        $item_code = Counter::where('id',1)->value('code_2');
	        $postdata['digits_code'] = $item_code;
            $postdata["created_by"]=CRUDBooster::myId();
            
            $size = Size::where('id',$postdata["sizes_id"])->value('size_code');
		
    		$postdata["size"]=($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
    		$postdata["warranties_id"] = 0;
    		$postdata["warranty_duration"] = 0;
    		$postdata["sku_statuses_id"] = $this->active;
    		$postdata["approval_status"] = $this->approved;
    		$postdata["current_srp"] = $postdata["original_srp"];
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
	        Counter::where('id',1)->increment('code_2');

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
            if(!empty($postdata["sizes_id"]) && !empty($postdata["size_value"])){
    			$size = Size::where('id',$postdata["sizes_id"])->value('size_code');
    			$postdata["size"]=($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
    		}
    		
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

		public function getApiItems($secret_key) {
			if ($secret_key != config('key-api.secret_key')) {
				return response([
					'message' => 'Error: Bad Request',
				], 404);
			}
	
			$created_items = RmaItemMaster::GenerateExport()
				->whereBetween(DB::raw('DATE(approved_at)'), [date('Y-m-d',strtotime("-1 days")), date('Y-m-d')])
				->get()
				->toArray();
				
			return response()->json([
				'created_items' => $created_items
			]);
		}

		public function importRmaView(){

			$data['page_title'] = 'Import Module';
	    	return view('item-master.rma-upload',$data);
		}

	}