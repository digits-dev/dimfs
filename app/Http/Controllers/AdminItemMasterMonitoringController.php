<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use Excel;
	use App\ActionType;
	use App\Segmentation;
	use App\VendorType;
	use App\Category;
	use App\Counter;
	use App\InventoryType;
	use App\ItemIdentifier;
	use App\ItemMaster;
	use App\ItemMasterApproval;
	use App\Size;
	use App\SkuStatus;
	use App\StatusState;
	use App\WorkflowSetting;
	use App\Platform;
	use App\PromoType;

	class AdminItemMasterMonitoringController extends \crocodicstudio\crudbooster\controllers\CBController {
	    
        private $approved;
		private $rejected;
		private $pending;
		private $module_id;
		private $create;
		private $update;
		
        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
            $this->approved = StatusState::where('status_state','APPROVED')->value('id');
			$this->rejected = StatusState::where('status_state','REJECTED')->value('id');
			$this->pending = StatusState::where('status_state','PENDING')->value('id');
			$this->create = ActionType::where('action_type',"CREATE")->value('id');
			$this->update = ActionType::where('action_type',"UPDATE")->value('id');
			$this->module_id = DB::table('cms_moduls')->where('table_name','item_masters')->value('id');
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
			$this->button_add = false;
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "item_master_approvals";
			# END CONFIGURATION DO NOT REMOVE THIS LINE
            
            $segmentations = Segmentation::where('status','ACTIVE')->orderBy('segmentation_description','ASC')->get();
    		$platforms = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();
    		$promo_types = PromoType::where('status','ACTIVE')->orderBy('promo_type_description','ASC')->get();
    		
			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"APPROVAL STATUS","name"=>"approval_status","join"=>"status_states,status_state"];
    		$this->col[] = ["label"=>"DIGITS CODE","name"=>"digits_code"];
    		$this->col[] = ["label"=>"SKU STATUS","name"=>"sku_statuses_id","join"=>"sku_statuses,sku_status_description","visible"=>CRUDBooster::myColumnView()->sku_status ? true:false];
    		$this->col[] = ["label"=>"SKU LEGEND","name"=>"sku_legends_id","join"=>"sku_legends,sku_legend_description","visible"=>CRUDBooster::myColumnView()->sku_status ? true:false];
    		$this->col[] = ["label"=>"UPC CODE-1","name"=>"upc_code"];
    		$this->col[] = ["label"=>"UPC CODE-2","name"=>"upc_code2","visible"=>false];
    		$this->col[] = ["label"=>"UPC CODE-3","name"=>"upc_code3","visible"=>false];
    		$this->col[] = ["label"=>"UPC CODE-4","name"=>"upc_code4","visible"=>false];
    		$this->col[] = ["label"=>"UPC CODE-5","name"=>"upc_code5","visible"=>false];
    		$this->col[] = ["label"=>"SUPPLIER ITEM CODE","name"=>"supplier_item_code"];
    		$this->col[] = ["label"=>"MODEL NUMBER","name"=>"model_number"];
    		$this->col[] = ["label"=>"ITEM DESCRIPTION","name"=>"item_description"];
    		$this->col[] = ["label"=>"BRAND DESCRIPTION","name"=>"brands_id","join"=>"brands,brand_description","visible"=>CRUDBooster::myColumnView()->brand_description ? true:false];
    		$this->col[] = ["label"=>"BRAND STATUS","name"=>"brands_id","join"=>"brands,status","visible"=>CRUDBooster::myColumnView()->brand_status ? true:false];
    		$this->col[] = ["label"=>"MARGIN CATEGORY DESCRIPTION","name"=>"margin_categories_id","join"=>"margin_categories,margin_category_description","visible"=>CRUDBooster::myColumnView()->margin_category_desc ? true:false];
    		$this->col[] = ["label"=>"CATEGORY DESCRIPTION","name"=>"categories_id","join"=>"categories,category_description","visible"=>CRUDBooster::myColumnView()->category_description ? true:false];
    		$this->col[] = ["label"=>"CLASS DESCRIPTION","name"=>"classes_id","join"=>"classes,class_description","visible"=>CRUDBooster::myColumnView()->class_description ? true:false];
    		$this->col[] = ["label"=>"SUBCLASS","name"=>"subclasses_id","join"=>"subclasses,subclass_description","visible"=>CRUDBooster::myColumnView()->subclass ? true:false];
    		$this->col[] = ["label"=>"WH CATEGORY","name"=>"warehouse_categories_id","join"=>"warehouse_categories,warehouse_category_description","visible"=>CRUDBooster::myColumnView()->wh_category ? true:false];
    		$this->col[] = ["label"=>"MODEL","name"=>"model","visible"=>CRUDBooster::myColumnView()->model ? true:false];
    		$this->col[] = ["label"=>"MODEL SPECIFIC DESCRIPTION","name"=>"model_specifics_id","join"=>"model_specifics,model_specific_description","visible"=>CRUDBooster::myColumnView()->model_specific_desc ? true:false];
    		$this->col[] = ["label"=>"COMPATIBILITY","name"=>"compatibility"];
    		$this->col[] = ["label"=>"SIZE","name"=>"size","visible"=>CRUDBooster::myColumnView()->size ? true:false];
    		$this->col[] = ["label"=>"ACTUAL COLOR","name"=>"actual_color","visible"=>CRUDBooster::myColumnView()->actual_color ? true:false];
    		$this->col[] = ["label"=>"MAIN COLOR DESCRIPION","name"=>"colors_id","join"=>"colors,color_description","visible"=>CRUDBooster::myColumnView()->color_description ? true:false];
    		$this->col[] = ["label"=>"UOM","name"=>"uoms_id","join"=>"uoms,uom_code","visible"=>CRUDBooster::myColumnView()->uom ? true:false];
    		$this->col[] = ["label"=>"VENDOR TYPE CODE","name"=>"vendor_types_id","join"=>"vendor_types,vendor_type_code","visible"=>CRUDBooster::myColumnView()->vendor_type ? true:false];
    		$this->col[] = ["label"=>"INVENTORY TYPE","name"=>"inventory_types_id","join"=>"inventory_types,inventory_type_description","visible"=>CRUDBooster::myColumnView()->inventory_type ? true:false];
    		
    		foreach ($segmentations as $segmentation) {
    			$this->col[] = ["label"=>$segmentation->segmentation_description,"name"=>$segmentation->segmentation_column, "visible"=>false];
    		}
    		
    		$this->col[] = ["label"=>"ORIGINAL SRP","name"=>"original_srp","visible"=>CRUDBooster::myColumnView()->original_srp ? true:false];
    		$this->col[] = ["label"=>"CURRENT SRP","name"=>"current_srp","visible"=>CRUDBooster::myColumnView()->current_srp ? true:false];
    		$this->col[] = ["label"=>"DG SRP","name"=>"promo_srp","visible"=>CRUDBooster::myColumnView()->promo_srp ? true:false];
    		$this->col[] = ["label"=>"PRICE CHANGE","name"=>"price_change","visible"=>CRUDBooster::myColumnView()->price_change ? true:false];
    		$this->col[] = ["label"=>"PRICE CHANGE DATE","name"=>"effective_date","visible"=>CRUDBooster::myColumnView()->price_effective_date ? true:false];
    		$this->col[] = ["label"=>"STORE COST","name"=>"dtp_rf","visible"=>CRUDBooster::myColumnView()->store_cost_rf ? true:false];
    		$this->col[] = ["label"=>"STORE MARGIN (%)","name"=>"dtp_rf_percentage","visible"=>CRUDBooster::myColumnView()->store_cost_prf ? true:false];
    		$this->col[] = ["label"=>"MAX CONSIGNMENT RATE (%)","name"=>"dtp_dcon_percentage","visible"=>CRUDBooster::myColumnView()->store_cost_pdcon ? true:false];
    		$this->col[] = ["label"=>"MOQ","name"=>"moq","visible"=>CRUDBooster::myColumnView()->moq ? true:false];
    		$this->col[] = ["label"=>"INCOTERMS","name"=>"vendors_id","join"=>"vendors,incoterms_id,incoterms,incoterms_code","visible"=>CRUDBooster::myColumnView()->incoterms ? true:false];
    		$this->col[] = ["label"=>"CURRENCY","name"=>"currencies_id","join"=>"currencies,currency_code","visible"=>CRUDBooster::myColumnView()->currency_1 ? true:false];
    		$this->col[] = ["label"=>"SUPPLIER COST","name"=>"purchase_price","visible"=>CRUDBooster::myColumnView()->purchase_price_1 ? true:false];
    		$this->col[] = ["label"=>"LANDED COST","name"=>"landed_cost","visible"=>CRUDBooster::myColumnView()->landed_cost ? true:false];
    		$this->col[] = ["label"=>"WORKING LANDED COST","name"=>"working_landed_cost","visible"=>CRUDBooster::myColumnView()->w_landed_cost ? true:false];
    		$this->col[] = ["label"=>"WORKING STORE COST","name"=>"working_dtp_rf","visible"=>CRUDBooster::myColumnView()->w_store_cost_rf ? true:false];
    		$this->col[] = ["label"=>"WORKING STORE MARGIN (%)","name"=>"working_dtp_rf_percentage","visible"=>CRUDBooster::myColumnView()->w_store_cost_prf ? true:false];
    		$this->col[] = ["label"=>"LIGHTROOM COST","name"=>"lightroom_cost","visible"=>CRUDBooster::myColumnView()->lightroom_cost ? true:false];
    		$this->col[] = ["label"=>"VENDOR","name"=>"vendors_id","join"=>"vendors,vendor_name","visible"=>CRUDBooster::myColumnView()->vendor_name ? true:false];
    		$this->col[] = ["label"=>"VENDOR STATUS","name"=>"vendors_id","join"=>"vendors,status","visible"=>CRUDBooster::myColumnView()->vendor_status ? true:false];
    		$this->col[] = ["label"=>"VENDOR GROUP","name"=>"vendor_groups_id","join"=>"vendor_groups,vendor_group_name","visible"=>CRUDBooster::myColumnView()->vendor_group_name ? true:false];
    		$this->col[] = ["label"=>"VENDOR GROUP STATUS","name"=>"vendor_groups_id","join"=>"vendor_groups,status","visible"=>CRUDBooster::myColumnView()->vendor_group_status ? true:false];
    		$this->col[] = ["label"=>"WARRANTY DURATION","name"=>"warranty_duration"];
    		$this->col[] = ["label"=>"WARRANTY DURATION TYPE","name"=>"warranties_id","join"=>"warranties,warranty_description"];
    		$this->col[] = ["label"=>"SERIAL CODE","name"=>"has_serial","visible"=>CRUDBooster::myColumnView()->serialized ? true:false];
    		$this->col[] = ["label"=>"IMEI CODE 1","name"=>"imei_code1","visible"=>CRUDBooster::myColumnView()->serialized ? true:false];
    		$this->col[] = ["label"=>"IMEI CODE 2","name"=>"imei_code2","visible"=>CRUDBooster::myColumnView()->serialized ? true:false];
            $this->col[] = ["label"=>"LENGTH [CM]","name"=>"item_length","visible"=>CRUDBooster::myColumnView()->item_length ? true:false];
			$this->col[] = ["label"=>"WIDTH [CM]","name"=>"item_width","visible"=>CRUDBooster::myColumnView()->item_width ? true:false];
			$this->col[] = ["label"=>"HEIGHT [CM]","name"=>"item_height","visible"=>CRUDBooster::myColumnView()->item_height ? true:false];
			$this->col[] = ["label"=>"WEIGHT [KG]","name"=>"item_weight","visible"=>CRUDBooster::myColumnView()->item_weight ? true:false];

    		$this->col[] = ["label"=>"APPROVED DATE","name"=>"approved_at","visible"=>CRUDBooster::myColumnView()->approved_date ? true:false];
    		$this->col[] = ["label"=>"APPROVED BY","name"=>"approved_by","join"=>"cms_users,name","visible"=>CRUDBooster::myColumnView()->approvedby ? true:false];
    		$this->col[] = ["label"=>"CREATED BY","name"=>"created_by","join"=>"cms_users,name","visible"=>CRUDBooster::myColumnView()->createdby ? true:false];
    		$this->col[] = ["label"=>"CREATED DATE","name"=>"created_at","visible"=>CRUDBooster::myColumnView()->created_date ? true:false];
    		$this->col[] = ["label"=>"UPDATED BY","name"=>"updated_by","join"=>"cms_users,name","visible"=>CRUDBooster::myColumnView()->updatedby ? true:false];
    		$this->col[] = ["label"=>"UPDATED DATE","name"=>"updated_at","visible"=>CRUDBooster::myColumnView()->updated_date ? true:false];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'DIGITS CODE','name'=>'digits_code','type'=>'text','validation'=>'min:8|max:8','width'=>'col-sm-6',
    			'readonly'=>'readonly',
    			'visible'=>self::getEditAccessOnly('digits_code')
    		];
    		$this->form[] = ['label'=>'UPC CODE-1','name'=>'upc_code','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'required|alpha_num_spaces|max:60|unique:item_masters,upc_code,'.CRUDBooster::getCurrentId(),
    			'readonly'=>self::getAllAccessReadOnly('upc_code_1'),
    			'visible'=>self::getAllAccess('upc_code_1')
    		];
    		$this->form[] = ['label'=>'UPC CODE-2','name'=>'upc_code2','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60|unique:item_masters,upc_code2,'.CRUDBooster::getCurrentId(),
    			'readonly'=>self::getEditAccessReadOnly('upc_code_2'),
    			'visible'=>self::getEditAccessOnly('upc_code_2')
    		];
    		$this->form[] = ['label'=>'UPC CODE-3','name'=>'upc_code3','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60|unique:item_masters,upc_code3,'.CRUDBooster::getCurrentId(),
    			'readonly'=>self::getEditAccessReadOnly('upc_code_3'),
    			'visible'=>self::getEditAccessOnly('upc_code_3')
    		];
    		$this->form[] = ['label'=>'UPC CODE-4','name'=>'upc_code4','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60|unique:item_masters,upc_code4,'.CRUDBooster::getCurrentId(),
    			'readonly'=>self::getEditAccessReadOnly('upc_code_4'),
    			'visible'=>self::getEditAccessOnly('upc_code_4')
    		];
    		$this->form[] = ['label'=>'UPC CODE-5','name'=>'upc_code5','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60|unique:item_masters,upc_code5,'.CRUDBooster::getCurrentId(),
    			'readonly'=>self::getEditAccessReadOnly('upc_code_5'),
    			'visible'=>self::getEditAccessOnly('upc_code_5')
    		];
    		$this->form[] = ['label'=>'SUPPLIER ITEM CODE','name'=>'supplier_item_code','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'required|min:3|max:60',
    			'readonly'=>self::getEditAccessReadOnly('supplier_item_code'),
    			'visible'=>self::getAllAccess('supplier_item_code')
    		];
    		$this->form[] = ['label'=>'BRAND DESCRIPTION','name'=>'brands_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'brands,brand_description',
    			'datatable_where'=>"status!='INACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('brand_description'),
    			'visible'=>self::getAllAccess('brand_description')
    		];
    		$this->form[] = ['label'=>'VENDOR','name'=>'vendors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendors,vendor_name',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('vendor_name'),
    			'visible'=>self::getAllAccess('vendor_name')
    		];
    		$this->form[] = ['label'=>'VENDOR TYPE','name'=>'vendor_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendor_types,vendor_type_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('vendor_type'),
    			'visible'=>self::getAllAccess('vendor_type')
    		];
    		$this->form[] = ['label'=>'VENDOR GROUP','name'=>'vendor_groups_id','type'=>'select2','validation'=>'integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendor_groups,vendor_group_name',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('vendor_group_name'),
    			'visible'=>self::getAllAccess('vendor_group_name')
    		];
    		$this->form[] = ['label'=>'CATEGORY DESCRIPTION','name'=>'categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'categories,category_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('category_description'),
    			'visible'=>self::getAllAccess('category_description')
    		];
    		$this->form[] = ['label'=>'CLASS DESCRIPTION','name'=>'classes_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'classes,class_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('class_description'),
    			'visible'=>self::getAllAccess('class_description')
    		];
    		
    		$this->form[] = ['label'=>'SUBCLASS','name'=>'subclasses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'subclasses,subclass_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('subclass'),
    			'visible'=>self::getAllAccess('subclass')
    		];
    		$this->form[] = ['label'=>'MARGIN CATEGORY','name'=>'margin_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'margin_categories,margin_category_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('margin_category_desc'),
    			'visible'=>self::getAllAccess('margin_category_desc')
    		];
    		
    		$this->form[] = ['label'=>'WH CATEGORY','name'=>'warehouse_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'warehouse_categories,warehouse_category_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('wh_category'),
    			'visible'=>self::getAllAccess('wh_category')
    		];
    		$this->form[] = ['label'=>'MODEL','name'=>'model','type'=>'text','validation'=>'required|min:3|max:60','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('model'),
    			'visible'=>self::getAllAccess('model')
    		];
    		$this->form[] = ['label'=>'MODEL SPECIFIC DESCRIPTION','name'=>'model_specifics_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'model_specifics,model_specific_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('model_specific_desc'),
    			'visible'=>self::getAllAccess('model_specific_desc')
    		];
    		$this->form[] = ['label'=>'MAIN COLOR DESCRIPTION','name'=>'colors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'colors,color_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('color_description'),
    			'visible'=>self::getAllAccess('color_description')
    		];
    		$this->form[] = ['label'=>'ACTUAL COLOR','name'=>'actual_color','type'=>'text','validation'=>'required|min:3|max:50','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('actual_color'),
    			'visible'=>self::getAllAccess('actual_color')
    		];
    		$this->form[] = ['label'=>'SIZE','name'=>'size_value','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			'help'=>'Enter zero (0) if size description is N/A',
    			'readonly'=>self::getEditAccessReadOnly('size'),
    			'visible'=>self::getAllAccess('size')
    		];
    		$this->form[] = ['label'=>'SIZE DESCRIPTION','name'=>'sizes_id','type'=>'select2','validation'=>'required','width'=>'col-sm-6',
    			'datatable'=>'sizes,size_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('size'),
    			'visible'=>self::getAllAccess('size')
    		];
    		$this->form[] = ['label'=>'ITEM DESCRIPTION','name'=>'item_description','type'=>'text','validation'=>'required|min:3|max:60','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('item_description'),
    			'visible'=>self::getAllAccess('item_description')
    		];
    		$this->form[] = ['label'=>'UOM','name'=>'uoms_id','type'=>'select2','validation'=>'required','width'=>'col-sm-6',
    			'datatable'=>'uoms,uom_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('uom'),
    			'visible'=>self::getAllAccess('uom')
    		];
    		$this->form[] = ['label'=>'INVENTORY TYPE','name'=>'inventory_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'inventory_types,inventory_type_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'help'=>'*If category description is STORE DEMO, please select TRADE',
    			'readonly'=>self::getEditAccessReadOnly('inventory_type'),
    			'visible'=>self::getAllAccess('inventory_type')
    		];
    		$this->form[] = ['label'=>'CURRENT SRP','name'=>'current_srp','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			'readonly'=>'readonly',
    			'visible'=>self::getEditAccessOnly('current_srp')
    		];
    		$this->form[] = ['label'=>'ORIGINAL SRP','name'=>'original_srp','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6','step'=>'.01',
    			'help'=>'*SRP must be ending in 90, unless otherwise stated or something similar',
    			'readonly'=>self::getEditAccessReadOnly('original_srp'),
    			'visible'=>self::getAllAccess('original_srp')
    		];
    		$this->form[] = ['label'=>'DG SRP','name'=>'promo_srp','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'.01',
    			'readonly'=>self::getEditAccessReadOnly('promo_srp'),
    			'visible'=>self::getAllAccess('promo_srp')
    		];
    		$this->form[] = ['label'=>'PRICE CHANGE','name'=>'price_change','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('price_change'),
    			'visible'=>self::getEditAccessOnly('price_change')
    		];
    		$this->form[] = ['label'=>'EFFECTIVE DATE','name'=>'effective_date','type'=>'date','validation'=>'date','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('price_effective_date'),
    			'visible'=>self::getEditAccessOnly('price_effective_date')
    		];
    		
    		foreach ($promo_types as $promo_type) {
        		$this->form[] = ['label'=>$promo_type->promo_type_description,'name'=>$promo_type->promo_type_column,'type'=>'number','step'=>'.01','width'=>'col-sm-6',
        			'readonly'=>self::getEditAccessReadOnly($promo_type->promo_type_column),
        			'visible'=>self::getEditAccessOnly($promo_type->promo_type_column)
        		];
            }
            		
    		$this->form[] = ['label'=>'STORE COST','name'=>'dtp_rf','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('store_cost_rf'),
    			'visible'=>self::getAllAccess('store_cost_rf')
    		];
    		$this->form[] = ['label'=>'STORE MARGIN (%)','name'=>'dtp_rf_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('store_cost_prf'),
    			'visible'=>self::getEditAccessOnly('store_cost_prf')
    		];
    		$this->form[] = ['label'=>'MAX CONSIGNMENT RATE (%)','name'=>'dtp_dcon_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('store_cost_pdcon'),
    			'visible'=>self::getEditAccessOnly('store_cost_pdcon')
    		];
    		
    		$this->form[] = ['label'=>'MOQ','name'=>'moq','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('moq'),
    			'visible'=>self::getAllAccess('moq')
    		];
    		$this->form[] = ['label'=>'CURRENCY','name'=>'currencies_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'currencies,currency_code',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('currency_1'),
    			'visible'=>self::getAllAccess('currency_1')
    		];
    		$this->form[] = ['label'=>'SUPPLIER COST','name'=>'purchase_price','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('purchase_price_1'),
    			'visible'=>self::getAllAccess('purchase_price_1')
    		];
    				
    		$this->form[] = ['label'=>'LANDED COST','name'=>'landed_cost','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('landed_cost'),
    			'visible'=>self::getEditAccessOnly('landed_cost')
    		];
    		$this->form[] = ['label'=>'WORKING LANDED COST','name'=>'working_landed_cost','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('w_landed_cost'),
    			'visible'=>self::getEditAccessOnly('w_landed_cost')
    		];
    		$this->form[] = ['label'=>'WORKING STORE COST','name'=>'working_dtp_rf','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('w_store_cost_rf'),
    			'visible'=>self::getEditAccessOnly('w_store_cost_rf')
    		];
    		$this->form[] = ['label'=>'WORKING STORE MARGIN %','name'=>'working_dtp_rf_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('w_store_cost_prf'),
    			'visible'=>self::getEditAccessOnly('w_store_cost_prf')
    		];
    		$this->form[] = ['label'=>'INCOTERMS','name'=>'incoterms_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'incoterms,incoterms_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('incoterms'),
    				'visible'=>self::getEditAccessOnly('incoterms')
    		];
    
    		$this->form[] = ['label'=>'SKU CLASS','name'=>'sku_classes_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'sku_classes,sku_class_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('sku_class'),
    			'visible'=>self::getEditAccessOnly('sku_class')
    		];
    
    		$this->form[] = ['label'=>'SKU STATUS','name'=>'sku_statuses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'sku_statuses,sku_status_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('sku_status'),
    			'visible'=>self::getEditAccessOnly('sku_status')
    		];
    		
    		$this->form[] = ['label'=>'SKU LEGEND','name'=>'sku_legends_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'sku_legends,sku_legend_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'readonly'=>self::getEditAccessReadOnly('sku_legend'),
    			'visible'=>self::getAllAccess('sku_legend')
    		];
    		$this->form[] = ['label'=>'SERIALIZED','name'=>'serialized','type'=>'checkbox','validation'=>'min:0','width'=>'col-sm-6',
    			'datatable'=>'item_identifiers,item_identifier',
    			'datatable_where'=>"status='ACTIVE'",
    			'visible'=>self::getAllAccess('serialized')
    		];
    		
    		foreach ($segmentations as $segmentation) {
    			$this->form[] = ['label'=>$segmentation->segmentation_description,'name'=>$segmentation->segmentation_column,'type'=>'select-custom','validation'=>'required','width'=>'col-sm-6',
    				'datatable'=>'sku_legends,sku_legend_description',
    				'datatable_where'=>"status='ACTIVE'",
    				'visible'=>self::getAllAccess('segmentation')
    			];
    		}
    
    		$this->form[] = ['label'=>'WARRANTY','name'=>'warranties_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'warranties,warranty_description',
    			'readonly'=>self::getEditAccessReadOnly('warranty'),
    			'visible'=>self::getEditAccessOnly('warranty')
    		];
    		$this->form[] = ['label'=>'WARRANTY DURATION','name'=>'warranty_duration','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'readonly'=>self::getEditAccessReadOnly('warranty_duration'),
    			'visible'=>self::getEditAccessOnly('warranty_duration')
    		];
    
    		$this->form[] = ['label'=>'LENGTH','name'=>'item_length','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    		    'help'=>'*must be in cm (centimeter).',
    			'readonly'=>self::getEditAccessReadOnly('item_length'),
    			'visible'=>self::getEditAccessOnly('item_length')
    		];
    
    		$this->form[] = ['label'=>'WIDTH','name'=>'item_width','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    			'help'=>'*must be in cm (centimeter).',
    			'readonly'=>self::getEditAccessReadOnly('item_width'),
    			'visible'=>self::getEditAccessOnly('item_width')
    		];
    
    		$this->form[] = ['label'=>'HEIGHT','name'=>'item_height','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    			'help'=>'*must be in cm (centimeter).',
    			'readonly'=>self::getEditAccessReadOnly('item_height'),
    			'visible'=>self::getEditAccessOnly('item_height')
    		];
    		
    		$this->form[] = ['label'=>'WEIGHT','name'=>'item_weight','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    			'help'=>'*must be in kg (kilogram).',
    			'readonly'=>self::getEditAccessReadOnly('item_weight'),
    			'visible'=>self::getEditAccessOnly('item_weight')
    		];
    
    		$this->form[] = ['label'=>'INITIAL WRR DATE (YYYY-MM-DD)','name'=>'initial_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-6',
    			'visible'=>self::getDetailAccessOnly('initial_wrr_date')
    		];
    		$this->form[] = ['label'=>'LATEST WRR DATE','name'=>'latest_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-6',
    			'visible'=>self::getDetailAccessOnly('latest_wrr_date')
    		];
    		
    		$this->form[] = ['label'=>'APPROVED BY','name'=>'approved_by','type'=>'select','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'cms_users,name',
    			'visible'=>self::getDetailAccessOnly('approvedby')
    		];
    		$this->form[] = ['label'=>'APPROVED AT','name'=>'approved_at','type'=>'datetime','validation'=>'required|date_format:Y-m-d H:i:s','width'=>'col-sm-6',
    			'visible'=>self::getDetailAccessOnly('approved_date')
    		];
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
            $this->table_row_color[] = ["condition"=>"[approval_status] == '3'","color"=>"danger"];//rejected items
	        
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
            if(in_array(CRUDBooster::myPrivilegeName(),["MCB TM"])) {
                $query->where('item_master_approvals.created_by',CRUDBooster::myId()); 
            }  
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
        
        public function getAllAccess($column_access) {
    		return ((in_array(CRUDBooster::getCurrentMethod(), ['getAdd', 'postAddSave']) && CRUDBooster::myAddForm()->$column_access ? true : false)
    		|| (in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditForm()->$column_access ? true : false )
    		|| (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_access ? true : false));
    	}
    
    	public function getEditAccessOnly($column_access) {
    		return ((in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditForm()->$column_access ? true : false )
    		|| (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_access ? true : false));
    	}
    	
    	public function getDetailAccessOnly($column_access) {
    		return (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_access ? true : false);
    	}
    
    	public function getAllAccessReadOnly($column_readonly) {
    		return ((in_array(CRUDBooster::getCurrentMethod(), ['getAdd', 'postAddSave']) && CRUDBooster::myAddReadOnly()->$column_readonly ? true : false)
    		|| (in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditReadOnly()->$column_readonly ? true : false ));
    	}
    
    	public function getEditAccessReadOnly($column_readonly) {
    		return ((in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave']) && CRUDBooster::myEditReadOnly()->$column_readonly ? true : false )
    		|| (CRUDBooster::getCurrentMethod() == "getDetail" && CRUDBooster::myColumnView()->$column_readonly ? true : false));
    	}


	}