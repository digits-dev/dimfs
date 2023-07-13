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
	use App\SupportType;
	use App\WorkflowSetting;
	use App\Platform;
	use App\PromoType;

	class AdminItemMasterApprovalsController extends \crocodicstudio\crudbooster\controllers\CBController {

		private $approved;
		private $rejected;
		private $pending;
		private $module_id;
		private $create;
		private $update;

        public function __construct() {
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
			$this->orderby = "id,asc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = true;
			$this->button_delete = true;
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
    		$this->col[] = ["label"=>"UPC CODE-1","name"=>"upc_code"];
    		$this->col[] = ["label"=>"UPC CODE-2","name"=>"upc_code2","visible"=>false];
    		$this->col[] = ["label"=>"UPC CODE-3","name"=>"upc_code3","visible"=>false];
    		$this->col[] = ["label"=>"UPC CODE-4","name"=>"upc_code4","visible"=>false];
    		$this->col[] = ["label"=>"UPC CODE-5","name"=>"upc_code5","visible"=>false];
    		$this->col[] = ["label"=>"SUPPLIER ITEM CODE","name"=>"supplier_item_code"];
    		$this->col[] = ["label"=>"MODEL NUMBER","name"=>"model_number"];
    		$this->col[] = ["label"=>"INITIAL WRR DATE (YYYY-MM-DD)","name"=>"initial_wrr_date","visible"=>CRUDBooster::myColumnView()->initial_wrr_date ? true:false];
    		$this->col[] = ["label"=>"LATEST WRR DATE (YYYY-MM-DD)","name"=>"latest_wrr_date","visible"=>CRUDBooster::myColumnView()->latest_wrr_date ? true:false];
    		$this->col[] = ["label"=>"BRAND DESCRIPTION","name"=>"brands_id","join"=>"brands,brand_description","visible"=>CRUDBooster::myColumnView()->brand_description ? true:false];
    		$this->col[] = ["label"=>"BRAND STATUS","name"=>"brands_id","join"=>"brands,status","visible"=>CRUDBooster::myColumnView()->brand_status ? true:false];
    		$this->col[] = ["label"=>"SKU STATUS","name"=>"sku_statuses_id","join"=>"sku_statuses,sku_status_description","visible"=>CRUDBooster::myColumnView()->sku_status ? true:false];
    		$this->col[] = ["label"=>"SKU LEGEND (RE-ORDER MATRIX)","name"=>"sku_legends_id","join"=>"sku_legends,sku_legend_description","visible"=>CRUDBooster::myColumnView()->sku_legend ? true:false];
    		$this->col[] = ["label"=>"ITEM DESCRIPTION","name"=>"item_description"];
    		$this->col[] = ["label"=>"MODEL","name"=>"model","visible"=>CRUDBooster::myColumnView()->model ? true:false];
    		$this->col[] = ["label"=>"YEAR LAUNCH","name"=>"year_launch","visible"=>CRUDBooster::myColumnView()->year_launch ? true:false];
    		$this->col[] = ["label"=>"MODEL SPECIFIC DESCRIPTION","name"=>"model_specifics_id","join"=>"model_specifics,model_specific_description","visible"=>CRUDBooster::myColumnView()->model_specific_desc ? true:false];
    		$this->col[] = ["label"=>"COMPATIBILITY","name"=>"compatibility"];
    		$this->col[] = ["label"=>"MARGIN CATEGORY DESCRIPTION","name"=>"margin_categories_id","join"=>"margin_categories,margin_category_description","visible"=>CRUDBooster::myColumnView()->margin_category_desc ? true:false];
    		$this->col[] = ["label"=>"CATEGORY DESCRIPTION","name"=>"categories_id","join"=>"categories,category_description","visible"=>CRUDBooster::myColumnView()->category_description ? true:false];
    		$this->col[] = ["label"=>"CLASS DESCRIPTION","name"=>"classes_id","join"=>"classes,class_description","visible"=>CRUDBooster::myColumnView()->class_description ? true:false];
    		$this->col[] = ["label"=>"SUBCLASS DESCRIPTION","name"=>"subclasses_id","join"=>"subclasses,subclass_description","visible"=>CRUDBooster::myColumnView()->subclass ? true:false];
    		$this->col[] = ["label"=>"WH CATEGORY DESCRIPTION","name"=>"warehouse_categories_id","join"=>"warehouse_categories,warehouse_category_description","visible"=>CRUDBooster::myColumnView()->wh_category ? true:false];
    		$this->col[] = ["label"=>"ORIGINAL SRP","name"=>"original_srp","visible"=>CRUDBooster::myColumnView()->original_srp ? true:false];
    		$this->col[] = ["label"=>"CURRENT SRP","name"=>"current_srp","visible"=>CRUDBooster::myColumnView()->current_srp ? true:false];
    		$this->col[] = ["label"=>"DG SRP","name"=>"promo_srp","visible"=>CRUDBooster::myColumnView()->promo_srp ? true:false];
    		$this->col[] = ["label"=>"PRICE CHANGE","name"=>"price_change","visible"=>CRUDBooster::myColumnView()->price_change ? true:false];
    		$this->col[] = ["label"=>"PRICE CHANGE DATE","name"=>"effective_date","visible"=>CRUDBooster::myColumnView()->price_effective_date ? true:false];
    		
    		$this->col[] = ["label"=>"STORE COST","name"=>"dtp_rf","visible"=>CRUDBooster::myColumnView()->store_cost_rf ? true:false];
    		$this->col[] = ["label"=>"STORE MARGIN (%)","name"=>"dtp_rf_percentage","visible"=>CRUDBooster::myColumnView()->store_cost_prf ? true:false];
    		$this->col[] = ["label"=>"LANDED COST","name"=>"landed_cost","visible"=>CRUDBooster::myColumnView()->landed_cost ? true:false];
    		$this->col[] = ["label"=>"AVERAGE LANDED COST","name"=>"actual_landed_cost","visible"=>CRUDBooster::myColumnView()->actual_landed_cost ? true:false];
    		$this->col[] = ["label"=>"LANDED COST VIA SEA","name"=>"landed_cost_sea","visible"=>CRUDBooster::myColumnView()->landed_cost_sea ? true:false];
    		
    		$this->col[] = ["label"=>"WORKING STORE COST","name"=>"working_dtp_rf","visible"=>CRUDBooster::myColumnView()->w_store_cost_rf ? true:false];
    		$this->col[] = ["label"=>"WORKING STORE MARGIN (%)","name"=>"working_dtp_rf_percentage","visible"=>CRUDBooster::myColumnView()->w_store_cost_prf ? true:false];
    		$this->col[] = ["label"=>"WORKING LANDED COST","name"=>"working_landed_cost","visible"=>CRUDBooster::myColumnView()->w_landed_cost ? true:false];
    		
    		$this->col[] = ["label"=>"DURATION FROM","name"=>"duration_from","visible"=>CRUDBooster::myColumnView()->duration_from ? true:false];
    		$this->col[] = ["label"=>"DURATION TO","name"=>"duration_to","visible"=>CRUDBooster::myColumnView()->duration_to ? true:false];
    		$this->col[] = ["label"=>"SUPPORT TYPE","name"=>"support_types_id","visible"=>CRUDBooster::myColumnView()->support_type ? true:false];
    		$this->col[] = ["label"=>"VENDOR TYPE CODE","name"=>"vendor_types_id","join"=>"vendor_types,vendor_type_code","visible"=>CRUDBooster::myColumnView()->vendor_type ? true:false];
    		$this->col[] = ["label"=>"MOQ","name"=>"moq","visible"=>CRUDBooster::myColumnView()->moq ? true:false];
    		$this->col[] = ["label"=>"INCOTERMS","name"=>"vendors_id","join"=>"vendors,incoterms_id,incoterms,incoterms_code","visible"=>CRUDBooster::myColumnView()->incoterms ? true:false];
    		$this->col[] = ["label"=>"CURRENCY","name"=>"currencies_id","join"=>"currencies,currency_code","visible"=>CRUDBooster::myColumnView()->currency_1 ? true:false];
    		$this->col[] = ["label"=>"SUPPLIER COST","name"=>"purchase_price","visible"=>CRUDBooster::myColumnView()->purchase_price_1 ? true:false];
    		$this->col[] = ["label"=>"SIZE","name"=>"size","visible"=>CRUDBooster::myColumnView()->size ? true:false];
    		$this->col[] = ["label"=>"ACTUAL COLOR","name"=>"actual_color","visible"=>CRUDBooster::myColumnView()->actual_color ? true:false];
    		$this->col[] = ["label"=>"MAIN COLOR DESCRIPION","name"=>"colors_id","join"=>"colors,color_description","visible"=>CRUDBooster::myColumnView()->color_description ? true:false];
    		$this->col[] = ["label"=>"UOM","name"=>"uoms_id","join"=>"uoms,uom_code","visible"=>CRUDBooster::myColumnView()->uom ? true:false];
    		$this->col[] = ["label"=>"INVENTORY TYPE","name"=>"inventory_types_id","join"=>"inventory_types,inventory_type_description","visible"=>CRUDBooster::myColumnView()->inventory_type ? true:false];
    		$this->col[] = ["label"=>"SKU CLASS","name"=>"sku_classes_id","join"=>"sku_classes,sku_class_description","visible"=>CRUDBooster::myColumnView()->sku_class ? true:false];
    		
    		foreach ($segmentations as $segmentation) {
    			$this->col[] = ["label"=>$segmentation->segmentation_description,"name"=>$segmentation->segmentation_column, "visible"=>true];
    		}
    		
    // 		$this->col[] = ["label"=>"MAX CONSIGNMENT RATE (%)","name"=>"dtp_dcon_percentage","visible"=>CRUDBooster::myColumnView()->store_cost_pdcon ? true:false];
    // 		$this->col[] = ["label"=>"LIGHTROOM COST","name"=>"lightroom_cost","visible"=>CRUDBooster::myColumnView()->lightroom_cost ? true:false];
    		
    		$this->col[] = ["label"=>"VENDOR NAME","name"=>"vendors_id","join"=>"vendors,vendor_name","visible"=>CRUDBooster::myColumnView()->vendor_name ? true:false];
    		$this->col[] = ["label"=>"VENDOR STATUS","name"=>"vendors_id","join"=>"vendors,status","visible"=>CRUDBooster::myColumnView()->vendor_status ? true:false];
    		$this->col[] = ["label"=>"VENDOR GROUP","name"=>"vendor_groups_id","join"=>"vendor_groups,vendor_group_name","visible"=>CRUDBooster::myColumnView()->vendor_group_name ? true:false];
    		$this->col[] = ["label"=>"VENDOR GROUP STATUS","name"=>"vendor_groups_id","join"=>"vendor_groups,status","visible"=>CRUDBooster::myColumnView()->vendor_group_status ? true:false];
    		$this->col[] = ["label"=>"WARRANTY DURATION","name"=>"warranty_duration"];
    		$this->col[] = ["label"=>"WARRANTY","name"=>"warranties_id","join"=>"warranties,warranty_description"];
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
    			'validation'=>'required|max:60', //|unique:item_masters,upc_code,'.$row->item_masters_id 
    			
    			'visible'=>self::getAllAccess('upc_code_1')
    		];
    		$this->form[] = ['label'=>'UPC CODE-2','name'=>'upc_code2','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60', //|unique:item_masters,upc_code2,'.$row->item_masters_id
    			
    			'visible'=>self::getEditAccessOnly('upc_code_2')
    		];
    		$this->form[] = ['label'=>'UPC CODE-3','name'=>'upc_code3','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60', //|unique:item_masters,upc_code3,'.$row->item_masters_id
    			
    			'visible'=>self::getEditAccessOnly('upc_code_3')
    		];
    		$this->form[] = ['label'=>'UPC CODE-4','name'=>'upc_code4','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60', //|unique:item_masters,upc_code4,'.$row->item_masters_id
    			
    			'visible'=>self::getEditAccessOnly('upc_code_4')
    		];
    		$this->form[] = ['label'=>'UPC CODE-5','name'=>'upc_code5','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'max:60', //|unique:item_masters,upc_code5,'.$row->item_masters_id
    			
    			'visible'=>self::getEditAccessOnly('upc_code_5')
    		];
    		
    		$this->form[] = ['label'=>'SUPPLIER ITEM CODE','name'=>'supplier_item_code','type'=>'text','width'=>'col-sm-6',
    			'validation'=>'required|min:2|max:60',
    			
    			'visible'=>self::getAllAccess('supplier_item_code')
    		];
    		$this->form[] = ['label'=>'BRAND DESCRIPTION','name'=>'brands_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'brands,brand_description',
    			'datatable_where'=>"status!='INACTIVE'",
    			
    			'visible'=>self::getAllAccess('brand_description')
    		];
    		$this->form[] = ['label'=>'VENDOR','name'=>'vendors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendors,vendor_name',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('vendor_name')
    		];
    		$this->form[] = ['label'=>'VENDOR TYPE','name'=>'vendor_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendor_types,vendor_type_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('vendor_type')
    		];
    		$this->form[] = ['label'=>'VENDOR GROUP','name'=>'vendor_groups_id','type'=>'select2','validation'=>'integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'vendor_groups,vendor_group_name',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('vendor_group_name')
    		];
    		$this->form[] = ['label'=>'CATEGORY DESCRIPTION','name'=>'categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'categories,category_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('category_description')
    		];
    		$this->form[] = ['label'=>'CLASS DESCRIPTION','name'=>'classes_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'classes,class_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('class_description')
    		];
    		
    		$this->form[] = ['label'=>'SUBCLASS','name'=>'subclasses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'subclasses,subclass_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('subclass')
    		];
    		$this->form[] = ['label'=>'MARGIN CATEGORY','name'=>'margin_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'margin_categories,margin_category_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('margin_category_desc')
    		];
    		
    		$this->form[] = ['label'=>'WH CATEGORY','name'=>'warehouse_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'warehouse_categories,warehouse_category_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('wh_category')
    		];
    		$this->form[] = ['label'=>'MODEL','name'=>'model','type'=>'text','validation'=>'required|min:2|max:60','width'=>'col-sm-6',
    			
    			'visible'=>self::getAllAccess('model')
    		];
    		$this->form[] = ['label'=>'YEAR LAUNCH','name'=>'year_launch','type'=>'number',(CRUDBooster::isSuperadmin())?:'validation'=>'required','width'=>'col-sm-6',
            	'readonly'=>self::getEditAccessReadOnly('year_launch'),
            	'visible'=>self::getAllAccess('year_launch')
            ];
    		$this->form[] = ['label'=>'MODEL SPECIFIC DESCRIPTION','name'=>'model_specifics_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'model_specifics,model_specific_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('model_specific_desc')
    		];
    		$this->form[] = ['label'=>'COMPATIBILITY','name'=>'compatibility','type'=>'select2-multiple','validation'=>'required','width'=>'col-sm-6',
    		    'datatable'=>'model_specifics,model_specific_description',
    		    'multiple'=>'multiple',
    		    
    			'visible'=>self::getAllAccess('compatibility')
    		];
    		$this->form[] = ['label'=>'MAIN COLOR DESCRIPTION','name'=>'colors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'colors,color_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('color_description')
    		];
    		$this->form[] = ['label'=>'ACTUAL COLOR','name'=>'actual_color','type'=>'text','validation'=>'required|min:3|max:50','width'=>'col-sm-6',
    			
    			'visible'=>self::getAllAccess('actual_color')
    		];
    		$this->form[] = ['label'=>'SIZE','name'=>'size_value','type'=>'number','validation'=>'required|min:0','step'=>0.01,'width'=>'col-sm-6',
    			'help'=>'Enter zero (0) if size description is N/A',
    			
    			'visible'=>self::getAllAccess('size')
    		];
    		$this->form[] = ['label'=>'SIZE DESCRIPTION','name'=>'sizes_id','type'=>'select2','validation'=>'required','width'=>'col-sm-6',
    			'datatable'=>'sizes,size_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('size')
    		];
    		$this->form[] = ['label'=>'ITEM DESCRIPTION','name'=>'item_description','type'=>'text','validation'=>'required|min:3|max:60','width'=>'col-sm-6',
    			
    			'visible'=>self::getAllAccess('item_description')
    		];
    		$this->form[] = ['label'=>'UOM','name'=>'uoms_id','type'=>'select2','validation'=>'required','width'=>'col-sm-6',
    			'datatable'=>'uoms,uom_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('uom')
    		];
    		$this->form[] = ['label'=>'INVENTORY TYPE','name'=>'inventory_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'inventory_types,inventory_type_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'help'=>'*If category description is STORE DEMO, please select TRADE',
    			
    			'visible'=>self::getAllAccess('inventory_type')
    		];
    		$this->form[] = ['label'=>'CURRENT SRP','name'=>'current_srp','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			
    			'visible'=>self::getEditAccessOnly('current_srp')
    		];
    		$this->form[] = ['label'=>'ORIGINAL SRP','name'=>'original_srp','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6','step'=>'0.01',
    			'help'=>'*SRP must be ending in 90, unless otherwise stated or something similar',
    			
    			'visible'=>self::getAllAccess('original_srp')
    		];
    		$this->form[] = ['label'=>'DG SRP','name'=>'promo_srp','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getAllAccess('promo_srp')
    		];
    		$this->form[] = ['label'=>'PRICE CHANGE','name'=>'price_change','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getEditAccessOnly('price_change')
    		];
    		$this->form[] = ['label'=>'EFFECTIVE DATE','name'=>'effective_date','type'=>'date','validation'=>'date','width'=>'col-sm-6',
    			
    			'visible'=>self::getEditAccessOnly('price_effective_date')
    		];
    		
    		foreach ($promo_types as $promo_type) {
        		$this->form[] = ['label'=>$promo_type->promo_type_description,'name'=>$promo_type->promo_type_column,'type'=>'number','step'=>'.01','width'=>'col-sm-6',
        			
        			'visible'=>self::getEditAccessOnly($promo_type->promo_type_column)
        		];
            }
            		
    		$this->form[] = ['label'=>'STORE COST','name'=>'dtp_rf','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getAllAccess('store_cost_rf')
    		];
    		$this->form[] = ['label'=>'STORE MARGIN (%)','name'=>'dtp_rf_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.0001',
    			
    			'visible'=>self::getEditAccessOnly('store_cost_prf')
    		];
    // 		$this->form[] = ['label'=>'MAX CONSIGNMENT RATE (%)','name'=>'dtp_dcon_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.0001',
    			
    // 			'visible'=>self::getEditAccessOnly('store_cost_pdcon')
    // 		];
            
            $this->form[] = ['label'=>'MOQ','name'=>'moq','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
    			
    			'visible'=>self::getAllAccess('moq')
    		];
    		$this->form[] = ['label'=>'CURRENCY','name'=>'currencies_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'currencies,currency_code',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getAllAccess('currency_1')
    		];
    		$this->form[] = ['label'=>'SUPPLIER COST','name'=>'purchase_price','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getAllAccess('purchase_price_1')
    		];
    				
    		$this->form[] = ['label'=>'LANDED COST','name'=>'landed_cost','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getEditAccessOnly('landed_cost')
    		];
    		
    		$this->form[] = ['label'=>'AVERAGE LANDED COST','name'=>'actual_landed_cost','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getEditAccessOnly('actual_landed_cost')
    		];
    		
    		$this->form[] = ['label'=>'LANDED COST VIA SEA','name'=>'landed_cost_sea','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getEditAccessOnly('landed_cost_sea')
    		];
    		
    		$this->form[] = ['label'=>'WORKING STORE COST','name'=>'working_dtp_rf','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getEditAccessOnly('w_store_cost_rf')
    		];
    		$this->form[] = ['label'=>'WORKING STORE MARGIN %','name'=>'working_dtp_rf_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.0001',
    			
    			'visible'=>self::getEditAccessOnly('w_store_cost_prf')
    		];
    		$this->form[] = ['label'=>'WORKING LANDED COST','name'=>'working_landed_cost','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.01',
    			
    			'visible'=>self::getEditAccessOnly('w_landed_cost')
    		];
    		$this->form[] = ['label'=>'INCOTERMS','name'=>'incoterms_id','type'=>'select2','width'=>'col-sm-6',
    			'datatable'=>'incoterms,incoterms_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'visible'=>self::getEditAccessOnly('incoterms')
    		];
    
    		$this->form[] = ['label'=>'SKU CLASS','name'=>'sku_classes_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'sku_classes,sku_class_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getEditAccessOnly('sku_class')
    		];
    
    		$this->form[] = ['label'=>'SKU STATUS','name'=>'sku_statuses_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'sku_statuses,sku_status_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
    			'visible'=>self::getEditAccessOnly('sku_status')
    		];
    		
    		$this->form[] = ['label'=>'SKU LEGEND (RE-ORDER MATRIX)','name'=>'sku_legends_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'sku_legends,sku_legend_description',
    			'datatable_where'=>"status='ACTIVE'",
    			
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
    			
    			'visible'=>self::getEditAccessOnly('warranty')
    		];
    		$this->form[] = ['label'=>'WARRANTY DURATION','name'=>'warranty_duration','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			
    			'visible'=>self::getEditAccessOnly('warranty_duration')
    		];
    		
            $this->form[] = ['label'=>'PLATFORM','name'=>'platform','type'=>'checkbox','validation'=>'required|min:0','width'=>'col-sm-6',
    			'datatable'=>'platforms,platform_description',
    			'datatable_where'=>"status='ACTIVE'",
    			'visible'=>self::getEditAccessOnly('platform')
    		];
    
    		$this->form[] = ['label'=>'LENGTH','name'=>'item_length','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    		    'help'=>'*must be in cm (centimeter).',
    			'visible'=>self::getEditAccessOnly('item_length')
    		];
    
    		$this->form[] = ['label'=>'WIDTH','name'=>'item_width','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    			'help'=>'*must be in cm (centimeter).',
    			'visible'=>self::getEditAccessOnly('item_width')
    		];
    
    		$this->form[] = ['label'=>'HEIGHT','name'=>'item_height','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    			'help'=>'*must be in cm (centimeter).',
    			'visible'=>self::getEditAccessOnly('item_height')
    		];
    		
    		$this->form[] = ['label'=>'WEIGHT','name'=>'item_weight','type'=>'number','validation'=>'required|min:0','step'=>'0.01','width'=>'col-sm-6',
    			'help'=>'*must be in kg (kilogram).',
    			'visible'=>self::getEditAccessOnly('item_weight')
    		];
    
    		$this->form[] = ['label'=>'INITIAL WRR DATE (YYYY-MM-DD)','name'=>'initial_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-6',
    			'visible'=>self::getDetailAccessOnly('initial_wrr_date')
    		];
    		$this->form[] = ['label'=>'LATEST WRR DATE (YYYY-MM-DD)','name'=>'latest_wrr_date','type'=>'date','validation'=>'required|date','width'=>'col-sm-6',
    			'visible'=>self::getDetailAccessOnly('latest_wrr_date')
    		];
    		
    		$this->form[] = ['label'=>'APPROVED BY','name'=>'approved_by','type'=>'select','validation'=>'required|integer|min:0','width'=>'col-sm-6',
    			'datatable'=>'cms_users,name',
    			'visible'=>self::getDetailAccessOnly('approvedby')
    		];
    		$this->form[] = ['label'=>'APPROVED AT','name'=>'approved_at','type'=>'datetime','validation'=>'required|date_format:Y-m-d H:i:s','width'=>'col-sm-6',
    			'visible'=>self::getDetailAccessOnly('approved_date')
    		];
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
            if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "MCB TL") { //if approver
	        	$this->button_selected[] = ["label"=>"APPROVE","icon"=>"fa fa-check-circle","name"=>"approve"];
				$this->button_selected[] = ["label"=>"REJECT","icon"=>"fa fa-times-circle","name"=>"reject"];
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
			if(CRUDBooster::getCurrentMethod() == 'getIndex'){
				$this->index_button[] = ['label'=>'Export Pending Items','title'=>'Export Pending Items','color'=>'warning','url'=>CRUDBooster::mainpath('export-pending'),'icon'=>'fa fa-upload'];
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
	        $this->load_js[] = asset("js/item_master_approval.js");
	        
	        
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
			$selected_items = ItemMasterApproval::whereIn('id',$id_selected)->get();

	        switch ($button_name) {
				case 'approve':
					{
						foreach ($selected_items as $item) {
							
							if(is_null($item->digits_code)){
								//generate item code
								$itemCode = $this->generateItemCode($item->categories_id,$item->inventory_types_id,$item->vendor_types_id);
								
								//update digits code
								ItemMasterApproval::where('id',$item->id)->update([
									'digits_code'=>$itemCode,
									'approval_status'=>$this->approved,
									'approved_by'=>CRUDBooster::myId(),
									'approved_at'=>date('Y-m-d H:i:s')
								]);

								$new_item = ItemMasterApproval::where('id',$item->id)->first()->toArray();
								unset($new_item['item_masters_id']);
								unset($new_item['id']);
								ItemMaster::where('id',$item->item_masters_id)->update($new_item);

								$this->updateCodeCounter(substr($itemCode, 0, 1));
								//send notification
								$this->sendApprovedNotification($itemCode,$item->created_by);
							}
						}

						CRUDBooster::redirectBack('The item(s) has been approved successfully !','success');
					}
					break;
				case 'reject':
					{
						foreach ($selected_items as $item) {
							if(is_null($item->digits_code)){
								ItemMasterApproval::where('id',$item->id)->update([
									'approval_status'=>$this->rejected
								]);

								$this->sendRejectedNotification($item->upc_code,$item->id,$item->created_by);
							}
						}

						CRUDBooster::redirectBack('The item(s) has been rejected successfully !','info');
					}
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
			if(in_array(CRUDBooster::myPrivilegeName(),["ADMIN","MCB TL"]))
				$query->where('item_master_approvals.approval_status',$this->pending); 
			elseif(!CRUDBooster::isSuperadmin()) {
				$query->where([
					'item_master_approvals.created_by'=>CRUDBooster::myId(),
					'item_master_approvals.approval_status'=>$this->rejected
				]);
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
			$size = Size::where('id',$postdata["sizes_id"])->value('size_code');
			
            if(isset($postdata["warranty_duration"])){
                if(is_null($postdata["warranty_duration"]) || $postdata["warranty_duration"] == "")
                    $postdata["warranty_duration"] = 1;
            }
			if(isset($postdata['compatibility'])){
    	        $postdata['compatibility'] = implode(" / ",$postdata['compatibility']);
    		}
			$postdata["size"] = ($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
			$postdata["approval_status"] = $this->pending;
			$postdata["updated_by"] = CRUDBooster::myId();
			$this->setSerialFlags($postdata);
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
			$item = ItemMasterApproval::where('id', $id)->first();
			$this->sendUpdateNotification($item->upc_code, CRUDBooster::myPrivilegeId());
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
		
		public function getEdit($id) {
			$edit_item = ItemMasterApproval::where('item_master_approvals.id',$id)
				->join('brands','item_master_approvals.brands_id','=','brands.id')
				->join('categories','item_master_approvals.categories_id','=','categories.id')
				->join('model_specifics','item_master_approvals.model_specifics_id','=','model_specifics.id')
				->join('sizes','item_master_approvals.sizes_id','=','sizes.id')
				->first();

			Session::put('brand_code'.CRUDBooster::myId(), $edit_item->brand_code);
			Session::put('category_code'.CRUDBooster::myId(), $edit_item->category_code);
			Session::put('model'.CRUDBooster::myId(), $edit_item->model);
			Session::put('model_specific'.CRUDBooster::myId(), $edit_item->model_specific_code);
			Session::put('size_value'.CRUDBooster::myId(), $edit_item->size_value);
			Session::put('size_code'.CRUDBooster::myId(), $edit_item->size_code);
			Session::put('actual_color'.CRUDBooster::myId(), $edit_item->actual_color);

			return parent::getEdit($id);
		}

		public function updateCodeCounter($item_code) {

			switch ($item_code) {
				case '1':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_1');
					break;
				case '2':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_2');
					break;
				case '3':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_3');
					break;
				case '4':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_4');
					break;
				case '5':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_5');
					break;
				case '6':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_6');
					break;
				case '7':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_7');
					break;
				case '8':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_8');
					break;
				case '9':
					Counter::where('cms_moduls_id',$this->module_id)->increment('code_9');
					break;
				
				default:
					# code...
					break;
			}
		}

		public function generateItemCode($category_id, $inventory_type_id, $vendor_type_id) {
			$category_code = Category::where('id',$category_id)->value('category_code');
			$inventory_type = InventoryType::where('id',$inventory_type_id)->value('inventory_type_code');
			$vendor_type = VendorType::where('id',$vendor_type_id)->value('vendor_type_code');

			if($category_code == 'SPR') {
				return Counter::where('cms_moduls_id', $this->module_id)->value('code_2');
			}
			elseif(in_array($category_code,['DEM','SAM'])) {
				return Counter::where('cms_moduls_id', $this->module_id)->value('code_9');
			}
			elseif(in_array($category_code,['MKT','PPB','OTH'])) {
				return Counter::where('cms_moduls_id', $this->module_id)->value('code_3');
			}
			else {
				if($inventory_type == 'N-TRADE') {
					return Counter::where('cms_moduls_id', $this->module_id)->value('code_3');
				}
				else {
					if(in_array($vendor_type,['IMP-OUT','LR-OUT','LOC-OUT'])) {
						return Counter::where('cms_moduls_id', $this->module_id)->value('code_8');
					}
					elseif(in_array($vendor_type,['IMP-CON','LOC-CON','LR-CON'])){
						return Counter::where('cms_moduls_id', $this->module_id)->value('code_7');
					}
				}
			}
			
		}

		public function makeHistoryChanges(&$postdata) {
			# code...
		}

		public function sendApprovedNotification($item_code,$encoder_id) {
			//send notification to encoder
			$config['content'] = CRUDBooster::myName(). " has approved your item with DIGITS CODE: ".$item_code." at Item Master Module!";
			$config['to'] = CRUDBooster::adminPath('item_masters?q='.$item_code);
			$config['id_cms_users'] = [$encoder_id];
			CRUDBooster::sendNotification($config);
		}

		public function sendRejectedNotification($item_code,$item_id,$encoder_id) {
			//send notification to encoder
			$config['content'] = CRUDBooster::myName(). " has rejected your item with UPC CODE: ".$item_code." at Item Master Approval Module!";
			$config['to'] = CRUDBooster::adminPath('item_master_approvals/edit/'.$item_id);
			$config['id_cms_users'] = [$encoder_id];
			CRUDBooster::sendNotification($config);
		}

		public function sendUpdateNotification($upc_code,$encoder_id) {

			//get workflow settings
			$workflow = WorkflowSetting::where([
				'cms_moduls_id'=>$this->module_id,
				'action_types_id'=>$this->create,
				'encoder_privileges_id'=>$encoder_id
			])->first();
			
			$approvers_id = DB::table('cms_users')
				->where('id_cms_privileges',$workflow->approver_privileges_id)
				->pluck('id')->toArray();
			//get users id of approvers

			$config['content'] = CRUDBooster::myName(). " has edited an item with UPC CODE: ".$upc_code." at Item Master Approval Module!";
			$config['to'] = CRUDBooster::adminPath('item_master_approvals?q='.$upc_code);
			$config['id_cms_users'] = $approvers_id;
			CRUDBooster::sendNotification($config);
		}

		public function setSerialFlags(&$postdata) {
			//get all checked form settings
			$item_identifiers = explode(';',$postdata["serialized"]);

			//get field names with respect to what module name
			$field_names = ItemIdentifier::where('status','ACTIVE')->orderBy('item_identifier','ASC')->get();

			if(!empty($item_identifiers)){
				//reset field names to 0 flag
				foreach ($field_names as $field_name) {
					$postdata[$field_name->column_field_name]=0;
				}
				//compare field names
				foreach ($item_identifiers as $item_identifier) {
					foreach ($field_names as $field_name) {
						//make flag 1 if column name is checked
						switch ($item_identifier) {

							case $field_name->item_identifier:
								$postdata[$field_name->column_field_name]=1;
								break;
						}
					}
				}

				$postdata["serialized_by"]=CRUDBooster::myId();
				$postdata["serialized_at"]=date('Y-m-d H:i:s');
			}
			else{
				$postdata["serialized"]=NULL;
			}
		}

		public function exportPendingItems(){
			//export excel all pending items for approval
			$pendingItems = ItemMasterApproval::join('brands','item_master_approvals.brands_id','=','brands.id')
				->join('categories','item_master_approvals.categories_id','=','categories.id')
				->join('cms_users','item_master_approvals.created_by','=','cms_users.id')
				->select(
					'upc_code',
					'supplier_item_code',
					'item_description',
					'brands.brand_description',
					'categories.category_description',
					'cms_users.name as encoded_by',
					'item_master_approvals.created_at as creation_date'
				)->where('approval_status',$this->pending);

			if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeName(),["ADMIN","MCB TL"])){
				$pendingItems->where('item_master_approvals.created_by',CRUDBooster::myId());
			}
			$pending_Items = $pendingItems->orderBy('item_master_approvals.created_at','asc')->get();

			Excel::create('Pending Items for Approval-'.date("d M Y - h.i.sa"), function($excel) use ($pending_Items) {
				$excel->sheet('pending-items', function($sheet) use ($pending_Items) {
		        	// Set auto size for sheet
					$sheet->setAutoSIZE(true);
					$sheet->setColumnFormat(array(

					));
		        	
		        	$headings = array('UPC Code','Supplier Item Code','Item Description','Brand Description','Category Description','Created By','Created Date');
					
					foreach($pending_Items as $item) {

		                $items_array[] = array(
							$item->upc_code,
							$item->supplier_item_code,
							$item->item_description,
							$item->brand_description,
							$item->category_description,
							$item->encoded_by,
							$item->creation_date
		                );
	                }
	                
					$sheet->fromArray($items_array, null, 'A1', false, false);
					$sheet->prependRow(1, $headings);
					$sheet->row(1, function($row) {
					    $row->setBackground('#FFFF00');
					    $row->setAlignment('center');
					});
					
		        });
			})->export('xls');
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