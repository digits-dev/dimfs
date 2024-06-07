<?php namespace App\Http\Controllers;

	use Session;
	use DB;
	use CRUDBooster;
	use Excel;
	use App\MarginMatrix;
	use App\HistoryLandedCost;
	use App\HistoryStoreCost;
	use App\HistorySupplierCost;
	use App\HistoryUpcCode;
	use App\Http\Traits\ItemTraits;
	use App\InventoryType;
	use App\ItemMaster;
	use App\ItemMasterApproval;
	use App\ItemPriceChangeApproval;
	use App\ItemCurrentPriceChange;
	use App\Platform;
	use App\Size;
	use App\SkuStatus;
	use App\WorkflowSetting;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\Input;
	use Illuminate\Http\Request;
	use Illuminate\Support\Arr;

class AdminItemMastersController extends \crocodicstudio\crudbooster\controllers\CBController {

	use ItemTraits;
	
	private $approved;
	private $rejected;
	private $pending;
	private $active;
	private $invalid;
	private $create;
	private $update;
	private $inactive_inventory;
	private $trade_inventory;

	public function __construct() {
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
		// $this->approved = StatusState::where('status_state','APPROVED')->value('id');
		// $this->rejected = StatusState::where('status_state','REJECTED')->value('id');
		// $this->pending = StatusState::where('status_state','PENDING')->value('id');
		$this->active = SkuStatus::where('sku_status_description','ACTIVE')->value('id');
		$this->invalid = SkuStatus::where('sku_status_description','INVALID')->value('id');
		$this->inactive_inventory = InventoryType::where('inventory_type_description','INACTIVE')->value('id');
		$this->trade_inventory = InventoryType::where('inventory_type_description','TRADE')->value('id');
	}

	public function cbInit() {

		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->title_field = "upc_code";
		$this->limit = "20";
		$this->orderby = "digits_code,desc";
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
		$this->table = "item_masters";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col[] = ["label"=>"DIGITS CODE","name"=>"digits_code"];
		$this->col[] = ["label"=>"UPC CODE-1","name"=>"upc_code","visible"=>CRUDBooster::myColumnView()->upc_code_1 ? true:false];
		$this->col[] = ["label"=>"UPC CODE-2","name"=>"upc_code2","visible"=>false];
		$this->col[] = ["label"=>"UPC CODE-3","name"=>"upc_code3","visible"=>false];
		$this->col[] = ["label"=>"UPC CODE-4","name"=>"upc_code4","visible"=>false];
		$this->col[] = ["label"=>"UPC CODE-5","name"=>"upc_code5","visible"=>false];
		$this->col[] = ["label"=>"SUPPLIER ITEM CODE","name"=>"supplier_item_code","visible"=>CRUDBooster::myColumnView()->supplier_item_code ? true:false];
		$this->col[] = ["label"=>"MODEL NUMBER","name"=>"model_number","visible"=>CRUDBooster::myColumnView()->model_number ? true:false];
		$this->col[] = ["label"=>"INITIAL WRR DATE (YYYY-MM-DD)","name"=>"initial_wrr_date","visible"=>CRUDBooster::myColumnView()->initial_wrr_date ? true:false];
		$this->col[] = ["label"=>"LATEST WRR DATE (YYYY-MM-DD)","name"=>"latest_wrr_date","visible"=>CRUDBooster::myColumnView()->latest_wrr_date ? true:false];
		$this->col[] = ["label"=>"APPLE LOB","name"=>"apple_lobs_id","join"=>"apple_lobs,apple_lob_description"];
		$this->col[] = ["label"=>"APPLE REPORT INCLUSION","name"=>"apple_report_inclusion"];
		$this->col[] = ["label"=>"BRAND GROUP","name"=>"brands_id","join"=>"brands,brand_group","visible"=>CRUDBooster::myColumnView()->brand_description ? true:false];
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
		// Edited by LEWIE
		$this->col[] = ["label"=>"ECOMM - STORE COST","name"=>"ecom_store_margin","visible"=>CRUDBooster::myColumnView()->store_cost_ecom ? true:false];
		$this->col[] = ["label"=>"ECOMM - STORE MARGIN (%)","name"=>"ecom_store_margin_percentage","visible"=>CRUDBooster::myColumnView()->store_cost_pecom ? true:false];
		// ------------------
		$this->col[] = ["label"=>"LANDED COST","name"=>"landed_cost","visible"=>CRUDBooster::myColumnView()->landed_cost ? true:false];
		$this->col[] = ["label"=>"AVERAGE LANDED COST","name"=>"actual_landed_cost","visible"=>CRUDBooster::myColumnView()->actual_landed_cost ? true:false];
		$this->col[] = ["label"=>"LANDED COST VIA SEA","name"=>"landed_cost_sea","visible"=>CRUDBooster::myColumnView()->landed_cost_sea ? true:false];
		$this->col[] = ["label"=>"WORKING STORE COST","name"=>"working_dtp_rf","visible"=>CRUDBooster::myColumnView()->w_store_cost_rf ? true:false];
		$this->col[] = ["label"=>"WORKING STORE MARGIN (%)","name"=>"working_dtp_rf_percentage","visible"=>CRUDBooster::myColumnView()->w_store_cost_prf ? true:false];
		// Edited by MIKE
		$this->col[] = ["label"=>"ECOMM - WORKING STORE COST","name"=>"working_ecom_store_margin","visible"=>CRUDBooster::myColumnView()->w_store_cost_ecom ? true:false];
		$this->col[] = ["label"=>"ECOMM - WORKING STORE MARGIN (%)","name"=>"working_ecom_store_margin_percentage","visible"=>CRUDBooster::myColumnView()->w_store_cost_pecom ? true:false];
		// ------------------
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
		$this->col[] = ["label"=>"LENGTH [CM]","name"=>"item_length","visible"=>CRUDBooster::myColumnView()->item_length ? true:false];
		$this->col[] = ["label"=>"WIDTH [CM]","name"=>"item_width","visible"=>CRUDBooster::myColumnView()->item_width ? true:false];
		$this->col[] = ["label"=>"HEIGHT [CM]","name"=>"item_height","visible"=>CRUDBooster::myColumnView()->item_height ? true:false];
		$this->col[] = ["label"=>"WEIGHT [KG]","name"=>"item_weight","visible"=>CRUDBooster::myColumnView()->item_weight ? true:false];
		$this->col[] = ["label"=>"ACTUAL COLOR","name"=>"actual_color","visible"=>CRUDBooster::myColumnView()->actual_color ? true:false];
		$this->col[] = ["label"=>"MAIN COLOR DESCRIPTION","name"=>"colors_id","join"=>"colors,color_description","visible"=>CRUDBooster::myColumnView()->color_description ? true:false];
		$this->col[] = ["label"=>"UOM","name"=>"uoms_id","join"=>"uoms,uom_code","visible"=>CRUDBooster::myColumnView()->uom ? true:false];
		$this->col[] = ["label"=>"INVENTORY TYPE","name"=>"inventory_types_id","join"=>"inventory_types,inventory_type_description","visible"=>CRUDBooster::myColumnView()->inventory_type ? true:false];
		$this->col[] = ["label"=>"SKU CLASS","name"=>"sku_classes_id","join"=>"sku_classes,sku_class_description","visible"=>CRUDBooster::myColumnView()->sku_class ? true:false];
		
		foreach ($this->getSegmentations() as $segmentation) {
			$this->col[] = ["label"=>$segmentation->segmentation_description,"name"=>$segmentation->segmentation_column, "visible"=>false];
		}
		
		$this->col[] = ["label"=>"VENDOR NAME","name"=>"vendors_id","join"=>"vendors,vendor_name","visible"=>CRUDBooster::myColumnView()->vendor_name ? true:false];
		$this->col[] = ["label"=>"VENDOR STATUS","name"=>"vendors_id","join"=>"vendors,status","visible"=>CRUDBooster::myColumnView()->vendor_status ? true:false];
		$this->col[] = ["label"=>"VENDOR GROUP","name"=>"vendor_groups_id","join"=>"vendor_groups,vendor_group_name","visible"=>CRUDBooster::myColumnView()->vendor_group_name ? true:false];
		$this->col[] = ["label"=>"VENDOR GROUP STATUS","name"=>"vendor_groups_id","join"=>"vendor_groups,status","visible"=>CRUDBooster::myColumnView()->vendor_group_status ? true:false];
		$this->col[] = ["label"=>"WARRANTY DURATION","name"=>"warranty_duration"];
		$this->col[] = ["label"=>"WARRANTY","name"=>"warranties_id","join"=>"warranties,warranty_description"];
		$this->col[] = ["label"=>"SERIAL CODE","name"=>"has_serial","visible"=>CRUDBooster::myColumnView()->serialized ? true:false];
		$this->col[] = ["label"=>"IMEI CODE 1","name"=>"imei_code1","visible"=>CRUDBooster::myColumnView()->imei_code_1 ? true:false];
		$this->col[] = ["label"=>"IMEI CODE 2","name"=>"imei_code2","visible"=>CRUDBooster::myColumnView()->imei_code_2 ? true:false];
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
			'validation'=>'required|max:60|unique:item_masters,upc_code,'.CRUDBooster::getCurrentId(),
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
		// $this->form[] = ['label'=>'LAZADA SKU','name'=>'lazada_sku','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		// $this->form[] = ['label'=>'SHOPEE SKU','name'=>'shopee_sku','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		// $this->form[] = ['label'=>'ITEM CODE CLIENT 1','name'=>'item_code_client1','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		// $this->form[] = ['label'=>'ITEM CODE CLIENT 2','name'=>'item_code_client2','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		// $this->form[] = ['label'=>'ITEM CODE CLIENT 3','name'=>'item_code_client3','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		// $this->form[] = ['label'=>'ITEM CODE CLIENT 4','name'=>'item_code_client4','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		// $this->form[] = ['label'=>'ITEM CODE CLIENT 5','name'=>'item_code_client5','type'=>'text','validation'=>'min:3|max:60','width'=>'col-sm-6'];
		//unique:item_masters,upc_code,upc_code2,upc_code3,upc_code4,upc_code5'
		$this->form[] = ['label'=>'SUPPLIER ITEM CODE','name'=>'supplier_item_code','type'=>'text','width'=>'col-sm-6',
			'validation'=>'required|min:2|max:60',
			'readonly'=>self::getEditAccessReadOnly('supplier_item_code'),
			'visible'=>self::getAllAccess('supplier_item_code')
		];
		$this->form[] = ['label'=>'APPLE LOBS','name'=>'apple_lobs_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			'datatable'=>'apple_lobs,apple_lob_description',
			'datatable_where'=>"status!='INACTIVE'",
			'readonly'=>self::getEditAccessReadOnly('brand_description'),
			'visible'=>self::getAllAccess('brand_description')
		];
		$this->form[] = [
			'label'=>'APPLE REPORT INCLUSION',
			'name'=>'apple_report_inclusion',
			'type'=>'select2',
			'validation'=>'required',
			'dataenum'=>'0;1',
			'width'=>'col-sm-6'
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
		$this->form[] = ['label'=>'MARGIN CATEGORY','name'=>'margin_category','type'=>'hidden','value'=>'','visible'=>self::getEditAccessOnly('margin_category_desc')];
		
		$this->form[] = ['label'=>'WH CATEGORY','name'=>'warehouse_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			'datatable'=>'warehouse_categories,warehouse_category_description',
			'datatable_where'=>"status='ACTIVE'",
			'readonly'=>self::getEditAccessReadOnly('wh_category'),
			'visible'=>self::getAllAccess('wh_category')
		];
		$this->form[] = ['label'=>'MODEL','name'=>'model','type'=>'text','validation'=>'required|min:2|max:60','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('model'),
			'visible'=>self::getAllAccess('model')
		];
		$this->form[] = ['label'=>'YEAR LAUNCH','name'=>'year_launch','type'=>'number',(CRUDBooster::isSuperadmin())?:'validation'=>'required','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('year_launch'),
			'visible'=>self::getAllAccess('year_launch')
		];
		$this->form[] = ['label'=>'MODEL SPECIFIC DESCRIPTION','name'=>'model_specifics_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			'datatable'=>'model_specifics,model_specific_description',
			'datatable_where'=>"status='ACTIVE'",
			'readonly'=>self::getEditAccessReadOnly('model_specific_desc'),
			'visible'=>self::getAllAccess('model_specific_desc')
		];
		
		$this->form[] = ['label'=>'COMPATIBILITY','name'=>'compatibility','type'=>'select2-multiple','validation'=>'required','width'=>'col-sm-6',
		    'datatable'=>'model_specifics,model_specific_description',
		    'multiple'=>'multiple',
		    'readonly'=>self::getEditAccessReadOnly('compatibility'),
			'visible'=>self::getAllAccess('compatibility')
		];
		
		$this->form[] = ['label'=>'MAIN COLOR DESCRIPTION','name'=>'colors_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
			'datatable'=>'colors,color_description',
			'datatable_where'=>"status='ACTIVE'",
			'readonly'=>self::getEditAccessReadOnly('color_description'),
			'visible'=>self::getAllAccess('color_description')
		];
		$this->form[] = ['label'=>'ACTUAL COLOR','name'=>'actual_color','type'=>'text','validation'=>'required|min:2|max:50','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('actual_color'),
			'visible'=>self::getAllAccess('actual_color')
		];
		$this->form[] = ['label'=>'SIZE','name'=>'size_value','type'=>'number','validation'=>'required|min:0','step'=>0.01,'width'=>'col-sm-6',
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
// 		$this->form[] = ['label'=>'SKU CLASS','name'=>'sku_classes_id','type'=>'select2','width'=>'col-sm-6',
// 			'datatable'=>'sku_classes,sku_class_description',
// 			'datatable_where'=>"status='ACTIVE'",
// 			'readonly'=>self::getEditAccessReadOnly('sku_class'),
// 			'visible'=>self::getAllAccess('sku_class')
// 		];
		
		$this->form[] = ['label'=>'CURRENT SRP','name'=>'current_srp','type'=>'number','validation'=>'required|min:0','width'=>'col-sm-6',
			'readonly'=>'readonly',
			'visible'=>self::getEditAccessOnly('current_srp')
		];
		$this->form[] = ['label'=>'ORIGINAL SRP','name'=>'original_srp','type'=>'number','validation'=>'required|min:0.00','width'=>'col-sm-6','step'=>'0.01',
			'help'=>'*SRP must be ending in 90, unless otherwise stated or something similar',
			'readonly'=>self::getEditAccessReadOnly('original_srp'),
			'visible'=>self::getAllAccess('original_srp')
		];
		$this->form[] = ['label'=>'DG SRP','name'=>'promo_srp','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('promo_srp'),
			'visible'=>self::getAllAccess('promo_srp')
		];
		$this->form[] = ['label'=>'PRICE CHANGE','name'=>'price_change','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('price_change'),
			'visible'=>self::getEditAccessOnly('price_change')
		];
		$this->form[] = ['label'=>'EFFECTIVE DATE','name'=>'effective_date','type'=>'date','validation'=>'date','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('price_effective_date'),
			'visible'=>self::getEditAccessOnly('price_effective_date')
		];
        		
		$this->form[] = ['label'=>'STORE COST','name'=>'dtp_rf','type'=>'number','validation'=>'required|min:0.00','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('store_cost_rf'),
			'visible'=>self::getAllAccess('store_cost_rf')
		];
		$this->form[] = ['label'=>'STORE MARGIN (%)','name'=>'dtp_rf_percentage','type'=>'number','validation'=>'min:0.00','width'=>'col-sm-6','step'=>'0.0001',
			'readonly'=>self::getEditAccessReadOnly('store_cost_prf'),
			'visible'=>self::getAllAccess('store_cost_prf')
		];

        // Edited by LEWIE
		$this->form[] = ['label'=>'ECOMM - STORE COST','name'=>'ecom_store_margin','type'=>'number','validation'=>'required|min:0.00','width'=>'col-sm-6','step'=>'0.01',
        'readonly'=>self::getEditAccessReadOnly('store_cost_ecom'),
        'visible'=>self::getAllAccess('store_cost_ecom')
        // 'readonly'=>true
        ];
        $this->form[] = ['label'=>'ECOMM - STORE MARGIN (%)','name'=>'ecom_store_margin_percentage','type'=>'number','validation'=>'min:0.00','width'=>'col-sm-6','step'=>'0.0001',
            'readonly'=>self::getEditAccessReadOnly('store_cost_pecom'),
            'visible'=>self::getAllAccess('store_cost_pecom')
            // 'readonly'=>true
        ];
        // ---------------------------------

		$this->form[] = ['label'=>'MAX CONSIGNMENT RATE (%)','name'=>'dtp_dcon_percentage','type'=>'number','validation'=>'min:0','width'=>'col-sm-6','step'=>'0.0001',
			'readonly'=>self::getEditAccessReadOnly('store_cost_pdcon'),
			'visible'=>self::getEditAccessOnly('store_cost_pdcon')
		];
		$this->form[] = ['label'=>'MOQ','name'=>'moq','type'=>'number',(CRUDBooster::isSuperadmin())?:'validation'=>'required|min:0','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('moq'),
			'visible'=>self::getAllAccess('moq')
		];
		$this->form[] = ['label'=>'CURRENCY','name'=>'currencies_id','type'=>'select2',(CRUDBooster::isSuperadmin())?:'validation'=>'required|integer|min:0','width'=>'col-sm-6',
			'datatable'=>'currencies,currency_code',
			'datatable_where'=>"status='ACTIVE'",
			'readonly'=>self::getEditAccessReadOnly('currency_1'),
			'visible'=>self::getAllAccess('currency_1')
		];
		$this->form[] = ['label'=>'SUPPLIER COST','name'=>'purchase_price','type'=>'number',(CRUDBooster::isSuperadmin())?:'validation'=>'required|min:0','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('purchase_price_1'),
			'visible'=>self::getAllAccess('purchase_price_1')
		];
		
		$this->form[] = ['label'=>'% ADD TO LC','name'=>'add_to_landed_cost','type'=>'select2',(CRUDBooster::isSuperadmin())?:'validation'=>'required','width'=>'col-sm-6',
			'dataenum'=>'0.01;0.05',
			'readonly'=>self::getEditAccessReadOnly('landed_cost'),
			'visible'=>self::getEditAccessOnly('landed_cost')
		];
		
        $this->form[] = ['label'=>'(ECOM) % ADD TO LC','name'=>'add_to_ecom_landed_cost','type'=>'select2',(CRUDBooster::isSuperadmin())?:'validation'=>'required','width'=>'col-sm-6',
            'dataenum'=>'0.02;0.05;0.1',
            'readonly'=>self::getEditAccessReadOnly('landed_cost'),
            'visible'=>self::getEditAccessOnly('landed_cost')
        ];
    
		$this->form[] = ['label'=>'DEDUCT FROM MARGIN % @ LC','name'=>'deduct_from_percent_landed_cost','type'=>'select2',(CRUDBooster::isSuperadmin())?:'validation'=>'required','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('landed_cost'),
			'visible'=>self::getEditAccessOnly('landed_cost')
		];
		
        $this->form[] = ['label'=>'(ECOM)DEDUCT FROM MARGIN % @ LC','name'=>'ecom_deduct_from_percent_landed_cost','type'=>'select2',(CRUDBooster::isSuperadmin())?:'validation'=>'required','width'=>'col-sm-6',
            'readonly'=>self::getEditAccessReadOnly('landed_cost'),
            'visible'=>self::getEditAccessOnly('landed_cost')
        ];
    
		$this->form[] = ['label'=>'LANDED COST','name'=>'landed_cost','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('landed_cost'),
			'visible'=>self::getEditAccessOnly('landed_cost')
		];
		$this->form[] = ['label'=>'AVERAGE LANDED COST','name'=>'actual_landed_cost','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('actual_landed_cost'),
			'visible'=>self::getEditAccessOnly('actual_landed_cost')
		];
		$this->form[] = ['label'=>'LANDED COST VIA SEA','name'=>'landed_cost_sea','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('landed_cost_sea'),
			'visible'=>self::getEditAccessOnly('landed_cost_sea')
		];
		
		$this->form[] = ['label'=>'WORKING STORE COST','name'=>'working_dtp_rf','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('w_store_cost_rf'),
			'visible'=>self::getEditAccessOnly('w_store_cost_rf')
		];
		$this->form[] = ['label'=>'WORKING STORE MARGIN %','name'=>'working_dtp_rf_percentage','type'=>'number','width'=>'col-sm-6','step'=>'0.0001',
			'readonly'=>self::getEditAccessReadOnly('w_store_cost_prf'),
			'visible'=>self::getEditAccessOnly('w_store_cost_prf')
		];
		// Edited by MIKE
		$this->form[] = ['label'=>'ECOMM - WORKING STORE COST','name'=>'working_ecom_store_margin','type'=>'number','validation'=>'required|min:0.00','width'=>'col-sm-6','step'=>'0.01',
        'readonly'=>self::getEditAccessReadOnly('w_store_cost_ecom'),
        'visible'=>self::getAllAccess('w_store_cost_ecom')
        // 'readonly'=>true
        ];
        $this->form[] = ['label'=>'ECOMM - WORKING STORE MARGIN (%)','name'=>'working_ecom_store_margin_percentage','type'=>'number','validation'=>'min:0.00','width'=>'col-sm-6','step'=>'0.0001',
            'readonly'=>self::getEditAccessReadOnly('w_store_cost_pecom'),
            'visible'=>self::getAllAccess('w_store_cost_pecom')
            // 'readonly'=>true
        ];
        // ---------------------------------
		$this->form[] = ['label'=>'WORKING LANDED COST','name'=>'working_landed_cost','type'=>'number','width'=>'col-sm-6','step'=>'0.01',
			'readonly'=>self::getEditAccessReadOnly('w_landed_cost'),
			'visible'=>self::getEditAccessOnly('w_landed_cost')
		];
		
		$this->form[] = ["label"=>"DURATION FROM","name"=>"duration_from",'type'=>'date-custom','width'=>'col-sm-6',
			'readonly'=>self::getEditAccessReadOnly('duration_from'),
			'visible'=>self::getEditAccessOnly('duration_from')];
			
		$this->form[] = ["label"=>"DURATION TO","name"=>"duration_to",'type'=>'date-custom','width'=>'col-sm-6',
		    'readonly'=>self::getEditAccessReadOnly('duration_to'),
			'visible'=>self::getEditAccessOnly('duration_to')];
			
		$this->form[] = ["label"=>"SUPPORT TYPE","name"=>"support_types_id",'type'=>'select2','width'=>'col-sm-6',
		    'datatable'=>'support_types,support_type_description',
			'datatable_where'=>"status='ACTIVE'",
		    'readonly'=>self::getEditAccessReadOnly('support_type'),
			'visible'=>self::getEditAccessOnly('support_type')];
		
// 		$this->form[] = ['label'=>'INCOTERMS','name'=>'incoterms_id','type'=>'select2','width'=>'col-sm-6',
// 			'datatable'=>'incoterms,incoterms_description',
// 			'datatable_where'=>"status='ACTIVE'",
// 			'readonly'=>self::getEditAccessReadOnly('incoterms'),
// 				'visible'=>self::getEditAccessOnly('incoterms')
// 		];

		$this->form[] = ['label'=>'SKU CLASS','name'=>'sku_classes_id','type'=>'select2','width'=>'col-sm-6',
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
		
		$this->form[] = ['label'=>'SKU LEGEND (RE-ORDER MATRIX)','name'=>'sku_legends_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
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
		
		foreach ($this->getSegmentations() as $segmentation) {
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
		
        $this->form[] = ['label'=>'PLATFORM','name'=>'platform','type'=>'checkbox','validation'=>'required|min:0','width'=>'col-sm-6',
			'datatable'=>'platforms,platform_description',
			'datatable_where'=>"status='ACTIVE'",
			'visible'=>self::getEditAccessOnly('platform')
		];

		$this->form[] = ['label'=>'LENGTH','name'=>'item_length','type'=>'number','validation'=>'required|min:0.00','step'=>'0.01','width'=>'col-sm-6',
		    'help'=>'*must be in cm (centimeter).',
			'readonly'=>self::getEditAccessReadOnly('item_length'),
			'visible'=>self::getEditAccessOnly('item_length')
		];

		$this->form[] = ['label'=>'WIDTH','name'=>'item_width','type'=>'number','validation'=>'required|min:0.00','step'=>'0.01','width'=>'col-sm-6',
			'help'=>'*must be in cm (centimeter).',
			'readonly'=>self::getEditAccessReadOnly('item_width'),
			'visible'=>self::getEditAccessOnly('item_width')
		];

		$this->form[] = ['label'=>'HEIGHT','name'=>'item_height','type'=>'number','validation'=>'required|min:0.00','step'=>'0.01','width'=>'col-sm-6',
			'help'=>'*must be in cm (centimeter).',
			'readonly'=>self::getEditAccessReadOnly('item_height'),
			'visible'=>self::getEditAccessOnly('item_height')
		];
		
		$this->form[] = ['label'=>'WEIGHT','name'=>'item_weight','type'=>'number','validation'=>'required|min:0.00','step'=>'0.01','width'=>'col-sm-6',
			'help'=>'*must be in kg (kilogram).',
			'readonly'=>self::getEditAccessReadOnly('item_weight'),
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
		
		
		$this->button_selected = array();
		if(CRUDBooster::isUpdate() && CRUDBooster::isSuperadmin()) { //
			$this->button_selected[] = ["label"=>"Set SKU Status ACTIVE","icon"=>"fa fa-check-circle","name"=>"set_sku_status_ACTIVE"];
			$this->button_selected[] = ["label"=>"Set SKU Status INVALID","icon"=>"fa fa-times-circle","name"=>"set_sku_status_INVALID"];
		}

		$this->index_button = array();
		if(CRUDBooster::getCurrentMethod() == 'getIndex') {

			$this->index_button[] = ["title"=>"Export All","label"=>"Export All",'color'=>'info',"icon"=>"fa fa-download","url"=>CRUDBooster::mainpath('export-all')];
// 			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['ADVANCED'])){
// 			    $this->index_button[] = ["title"=>"Export Outright","label"=>"Export Outright",'color'=>'primary',"icon"=>"fa fa-download","url"=>CRUDBooster::mainpath('export-margin')];
// 			}
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['MCB TM','SDM TL','REPORTS','ECOMM STORE MDSG TM','MCB TL','COST ACCTG'])){
			    $this->index_button[] = ["title"=>"Import Module","label"=>"Import Module",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-view')];
			}
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['COST ACCTG','SALES ACCTG'])){
			    $this->index_button[] = ["title"=>"POS Format","label"=>"POS Format",'color'=>'warning',"icon"=>"fa fa-cart-plus","url"=>CRUDBooster::mainpath('export-pos').'?'.urldecode(http_build_query(@$_GET))];
			}
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['WHS TL','SDM TM','WHS TM'])){
			    $this->index_button[] = ["title"=>"BAR Format","label"=>"BAR Format",'color'=>'default',"icon"=>"fa fa-qrcode","url"=>CRUDBooster::mainpath('export-bartender').'?'.urldecode(http_build_query(@$_GET))];
			}
		
			    
		}
		
		$this->script_js = NULL;
        $this->script_js = "$(function() {
		    var current_privilege = '".CRUDBooster::myPrivilegeName()."';
		    
            if(current_privilege == 'COST ACCTG'){
                $('#duration_from').css('pointer-events','none');
                $('#duration_to').css('pointer-events','none');
                $('.open-datetimepicker').css('pointer-events','none');
            }
		});";
		
		$this->load_js = array();
		$this->load_js[] = asset("js/item_master.js").'?r='.time();
		
		$this->load_css = array();
		$this->load_css[] = asset("css/item_master.css");
		
		
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
			case 'set_sku_status_ACTIVE':
				{
					ItemMaster::whereIn('id',$id_selected)->update([	
						'sku_statuses_id' => 1, //ACTIVE
						'updated_by' => CRUDBooster::myId(),
						'updated_at' => date('Y-m-d H:i:s')
					]);
				}
				break;
			case 'set_sku_status_INVALID':
				{
					ItemMaster::whereIn('id',$id_selected)->update([	
						'sku_statuses_id' => 4, //INVALID
						'updated_by' => CRUDBooster::myId(),
						'updated_at' => date('Y-m-d H:i:s')
					]);
				}
				break;
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
		
		if(!CRUDBooster::isSuperadmin() && (in_array(CRUDBooster::myPrivilegeName(), ["MCB TL","MCB TM","ACCTG HEAD","ADVANCED","REPORTS","ECOMM STORE MDSG TL"]))){
        	$query->where('approval_status',$this->getStatusByDescription('APPROVED'));
        }
		else if(!CRUDBooster::isSuperadmin() && (!in_array(CRUDBooster::myPrivilegeName(), ["MCB TL","ACCTG HEAD","ADVANCED","REPORTS","ECOMM STORE MDSG TL"]))){
		    $query->where('approval_status',$this->getStatusByDescription('APPROVED'))
		        ->where('inventory_types_id','!=',$this->inactive_inventory)
		        ->where('sku_statuses_id','!=',$this->invalid);
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
		$size = Size::where('id',$postdata["sizes_id"])->value('size_code');
		
		$postdata["size"]=($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
		$postdata["created_by"] = CRUDBooster::myId();
		$postdata["subcategories_id"] = 0;
		$postdata["warranties_id"] = 0;
		$postdata["warranty_duration"] = 0;
		$postdata["sku_statuses_id"] = $this->active;
		$postdata["approval_status"] = $this->getStatusByDescription('PENDING');
		$postdata["current_srp"] = $postdata["original_srp"];
		if(isset($postdata["promo_srp"])){
		    $postdata["bau_price"] = $postdata["promo_srp"];
		}
		
		if(isset($postdata['compatibility'])){
	        $postdata['compatibility'] = implode(" / ",$postdata['compatibility']);
		}
        if(isset($postdata["serialized"])){
           $this->setSerialFlags($postdata); 
        }
        else{
            $postdata["serialized"]=NULL;
            foreach ($this->getItemIdentifier() as $field_name) {
                $postdata[$field_name->column_field_name]=0;
            }
        }
		if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADVANCED"){
			$postdata["digits_code"] = $this->generateItemCode($postdata["categories_id"], $postdata["inventory_types_id"], $postdata["vendor_types_id"]);
			$postdata["approval_status"] = $this->getStatusByDescription('APPROVED');
			$postdata["approved_by"] = CRUDBooster::myId();
			$postdata["approved_at"] = date('Y-m-d H:i:s');
		}
		
		unset($postdata["margin_category"]);
		unset($postdata["add_to_landed_cost"]);
        unset($postdata["add_to_ecom_landed_cost"]);
		unset($postdata["deduct_from_percent_landed_cost"]);
        unset($postdata["ecom_deduct_from_percent_landed_cost"]);
	}
	
	public function hook_after_add($id) {        
		//Your code here
		$item = ItemMaster::where('id', $id)->first();
		$item['item_masters_id'] = $id;
		//insert to approval table
		ItemMasterApproval::insert($item->toArray());
		//update item counter if superadmin or admin
		if(!is_null($item) && (CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == 'ADVANCED')){
			$item_code = substr($item->digits_code, 0, 1);
			$this->updateCodeCounter($item_code);
		}
		else{
			$this->sendCreateNotification($item->upc_code, CRUDBooster::myPrivilegeId());
		}
	}
	
	public function hook_before_edit(&$postdata,$id) {        
		//Your code here
		$existingItem = ItemMaster::where('id', $id)->first();
		$postdata["id"] = $id;
		
		if(!empty($postdata["sizes_id"]) && !empty($postdata["size_value"])){
			$size = Size::where('id',$postdata["sizes_id"])->value('size_code');
			$postdata["size"]=($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
		}
		if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(), ["WHS TM","WHS TL"])){
    		if(isset($postdata["serialized"])){
    			self::setSerialFlags($postdata);
    		}
    		else{
    		    $postdata["serialized"]=NULL;
    			foreach ($this->getItemIdentifier() as $field_name) {
    				$postdata[$field_name->column_field_name]=0;
    			}
    		}
    	}
    	
    	if($postdata['sku_statuses_id'] == $this->invalid){
    	    $postdata['inventory_types_id'] = $this->inactive_inventory;
    	}
		
		if(isset($postdata['compatibility'])){
	        $postdata['compatibility'] = implode(" / ",$postdata['compatibility']);
		}
		
		if(isset($postdata["platform"])){
			$this->setPlatformFlags($postdata);
		}
		
		if(isset($postdata["promo_srp"])){
		    if(is_null($existingItem->bau_price)){
		        $postdata["bau_price"] = $postdata["promo_srp"];
		    }
		}
		
		if(isset($postdata["purchase_price"]) || isset($postdata["currencies_id"])){
		    if($existingItem->purchase_price != $postdata['purchase_price']) {
		        HistorySupplierCost::insert([
    		        'item_masters_id' => $postdata["id"],
    		        'brands_id' => $postdata["brands_id"],
    		        'categories_id' => $postdata["categories_id"],
    		        'supplier_cost' => $postdata["purchase_price"],
    		        'currencies_id' => $postdata["currencies_id"],
    		        'updated_by' => CRUDBooster::myId(),
    		        'created_at' => date('Y-m-d H:i:s')
    		    ]);
		    }
		}
		
		if(isset($postdata["upc_code"]) || isset($postdata["supplier_item_code"])){
		    if($existingItem->upc_code != $postdata['upc_code']) {
		        HistoryUpcCode::insert([
    		        'item_masters_id' => $postdata["id"],
    		        'brands_id' => $postdata["brands_id"],
    		        'categories_id' => $postdata["categories_id"],
    		        'upc_code' => $postdata["upc_code"],
    		        'supplier_item_code' => $postdata["supplier_item_code"],
    		        'updated_by' => CRUDBooster::myId(),
    		        'created_at' => date('Y-m-d H:i:s')
    		    ]);
		    }
		}
		
		if(isset($postdata["warranty_duration"])){
		    if(is_null($postdata["warranty_duration"]) || $postdata["warranty_duration"] == "")
		        $postdata["warranty_duration"] = 1;
		}
		
		$postdata["updated_by"] = CRUDBooster::myId();
		
        if(CRUDBooster::myPrivilegeName() == "COST ACCTG"){
            if(in_array($postdata["margin_category"],["ACCESSORIES","UNITS"])) {
                $postdata["dtp_rf_percentage"] = self::setStoreCostPercentage($postdata);
    			$postdata["working_dtp_rf_percentage"] = self::setWorkingStoreCostPercentage($postdata);
    
    			self::checkStoreCost($postdata);
    			self::checkWorkingStoreCost($postdata);
            }
            //save for approval
            ItemPriceChangeApproval::insert([
                'item_masters_id'   => $postdata["id"],
                'digits_code'       => $postdata["digits_code"],
                'brands_id'         => $postdata["brands_id"],
                'categories_id'     => $postdata["categories_id"],
                'margin_categories_id'     => $postdata["margin_categories_id"],
                'current_srp'       => $postdata["current_srp"],
                'promo_srp'         => $postdata["promo_srp"],
                'store_cost'        => $postdata["dtp_rf"],
                'store_cost_percentage' => $postdata["dtp_rf_percentage"],
                'ecom_store_margin'        => $postdata["ecom_store_margin"],
                'ecom_store_margin_percentage' => $postdata["ecom_store_margin_percentage"],
                'working_store_cost'    => $postdata["working_dtp_rf"],
                'working_store_cost_percentage' => $postdata["working_dtp_rf_percentage"],
                'landed_cost'           => $postdata["landed_cost"],
                'landed_cost_sea'       => $postdata["landed_cost_sea"],
                'working_landed_cost'   => $postdata["working_landed_cost"],
                'duration_from'     => $postdata["duration_from"],
                'duration_to'       => $postdata["duration_to"],
                'support_types_id'  => $postdata["support_types_id"],
                'approval_status'       => $this->getStatusByDescription('PENDING'),
                'encoder_privileges_id' => CRUDBooster::myPrivilegeId(),
                'updated_by'            => CRUDBooster::myId(),
                'created_at'            => date('Y-m-d H:i:s')
            ]);
                
            if(isset($postdata['landed_cost']) && (($existingItem->landed_cost != $postdata['landed_cost']) || ($existingItem->working_landed_cost != $postdata['working_landed_cost'])) ) {
                self::makeLCHistoryChanges($postdata);
            }
            
            if(isset($postdata['dtp_rf']) && (($existingItem->dtp_rf != $postdata['dtp_rf']) || ($existingItem->dtp_rf_percentage != $postdata['dtp_rf_percentage'])) ) {
                self::makeSCHistoryChanges($postdata);
            }
            
            unset($postdata['dtp_rf']);
            unset($postdata['dtp_rf_percentage']);
            unset($postdata['landed_cost']);
            unset($postdata['working_dtp_rf']);
            unset($postdata['working_dtp_rf_percentage']);
            unset($postdata['working_landed_cost']);
            unset($postdata['landed_cost_sea']);
		    
		    self::sendUpdateNotification($postdata['digits_code'], CRUDBooster::myPrivilegeId());
		    
		}
		
		elseif(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "MCB TM"){
		    if(($existingItem->price_change != $postdata['price_change'] && $postdata['price_change'] != '') || ($postdata['effective_date']) != '') {
		        ItemCurrentPriceChange::insert([
    		        'item_masters_id' => $postdata["id"],
    		        'current_srp' => $postdata["current_srp"],
    		        'price_change' => $postdata["price_change"],
    		        'effective_date' => $postdata["effective_date"],
    		        'updated_by' => CRUDBooster::myId(),
    		        'created_at' => date('Y-m-d H:i:s')
    		    ]);
		    }
		    
		}
		unset($postdata["id"]);
		unset($postdata["margin_category"]);
		unset($postdata["add_to_landed_cost"]);
        unset($postdata["add_to_ecom_landed_cost"]);
		unset($postdata["deduct_from_percent_landed_cost"]);
        unset($postdata["ecom_deduct_from_percent_landed_cost"]);
		
		$postdata = array_filter($postdata, 'strlen');
	}
	
	public function getEdit($id) {
		$edit_item = ItemMaster::getEditDetails($id)->first();

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
		$this->updateCounter($item_code);
	}

	public function generateItemCode($category_id, $inventory_type_id, $vendor_type_id) {
		// $module_id = CRUDBooster::getCurrentModule()->id;
		$data=[
			// 'module_id' => $module_id,
			'category_id' => $category_id,
			'inventory_type_id' => $inventory_type_id,
			'vendor_type_id' => $vendor_type_id
		];
		return $this->getDigitsCode($data);
		
	}

	public function makeLCHistoryChanges(&$postdata) {
		//landed cost
		
		if((isset($postdata['landed_cost']) || isset($postdata['working_landed_cost'])) && (!is_null($postdata['landed_cost'])) ) {
		    HistoryLandedCost::insert([
		        'item_masters_id' => $postdata['id'],
		        'brands_id' => $postdata['brands_id'],
		        'categories_id' => $postdata['categories_id'],
		        'landed_cost' => $postdata['landed_cost'],
		        'working_landed_cost' => $postdata['working_landed_cost'],
		        'updated_by' => CRUDBooster::myId(),
		        'updated_at' => date('Y-m-d H:i:s')
		    ]);
		}
	}
	
	public function makeSCHistoryChanges(&$postdata) {
		
		if((isset($postdata['dtp_rf']) || isset($postdata['working_dtp_rf'])) && (!is_null($postdata['dtp_rf'])) ) {
		    HistoryStoreCost::insert([
		        'item_masters_id' => $postdata['id'],
		        'brands_id' => $postdata['brands_id'],
		        'categories_id' => $postdata['categories_id'],
		        'store_cost' => $postdata['dtp_rf'],
		        'store_cost_percentage' => $postdata['dtp_rf_percentage'],
		        'working_store_cost' => $postdata['working_dtp_rf'],
		        'working_store_cost_percentage' => $postdata['working_dtp_rf_percentage'],
		        'updated_by' => CRUDBooster::myId(),
		        'updated_at' => date('Y-m-d H:i:s')
		    ]);
		}
	}

	public function sendCreateNotification($upc_code,$encoder_id) {

		//get workflow settings
		$workflow = WorkflowSetting::where([
			'cms_moduls_id'=>CRUDBooster::getCurrentModule()->id,
			'action_types_id'=>$this->getActionByDescription("CREATE"),
			'encoder_privileges_id'=>$encoder_id
		])->first();
		
		$approvers_id = DB::table('cms_users')
			->where('id_cms_privileges',$workflow->approver_privileges_id)
			->pluck('id')->toArray();
		//get users id of approvers

		$config['content'] = CRUDBooster::myName(). " has added an item with UPC CODE: ".$upc_code." at Item Master Approval Module!";
		$config['to'] = CRUDBooster::adminPath('item_master_approvals?q='.$upc_code);
		$config['id_cms_users'] = $approvers_id;
		CRUDBooster::sendNotification($config);
	}

	public function sendUpdateNotification($digits_code,$encoder_id) {
		//get workflow settings
		$workflow = WorkflowSetting::where([
			'cms_moduls_id'=>CRUDBooster::getCurrentModule()->id,
			'action_types_id'=>$this->getActionByDescription("UPDATE"),
			'encoder_privileges_id'=>$encoder_id
		])->first();
		
		$approvers_id = DB::table('cms_users')
			->where('id_cms_privileges',$workflow->approver_privileges_id)
			->pluck('id')->toArray();
		//get users id of approvers

		$config['content'] = CRUDBooster::myName(). " has added an item with DIGITS CODE: ".$digits_code." at Item Master Price Module!";
		$config['to'] = CRUDBooster::adminPath('item_price_approvals?q='.$digits_code);
		$config['id_cms_users'] = $approvers_id;
		CRUDBooster::sendNotification($config);
	}

	public function setSerialFlags(&$postdata) {
		//get all checked form settings
		$item_identifiers = explode(';',$postdata["serialized"]);

		//get field names with respect to what module name
		$field_names = $this->getItemIdentifier();

		if(!empty($item_identifiers) || !is_null($item_identifiers)){
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
			foreach ($field_names as $field_name) {
				$postdata[$field_name->column_field_name]=0;
			}
		}
		
	}
	
	public function setPlatformFlags(&$postdata) {
		//get all checked form settings
		$platforms = explode(';',$postdata["platform"]);

		//get field names with respect to what module name
		$field_names = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();

		if(!empty($platforms)){
			//reset field names to 0 flag
			foreach ($field_names as $field_name) {
				$postdata[$field_name->platform_column]=0;
			}
			//compare field names
			foreach ($platforms as $platform) {
				foreach ($field_names as $field_name) {
					//make flag 1 if column name is checked
					switch ($platform) {

						case $field_name->platform_description:
							$postdata[$field_name->platform_column]=1;
							break;
					}
				}
			}
		}
		else{
			$postdata["platform"]=NULL;
		}
	}

	public function exportPOSFormat() {

		$pos_item = ItemMaster::posFormat();

		if(\Request::get('filter_column')) {

			$filter_column = \Request::get('filter_column');

			$pos_item->where(function($w) use ($filter_column,$fc) {
				foreach($filter_column as $key=>$fc) {

					$value = @$fc['value'];
					$type  = @$fc['type'];

					if($type == 'empty') {
						$w->whereNull($key)->orWhere($key,'');
						continue;
					}

					if($value=='' || $type=='') continue;

					if($type == 'between') continue;

					switch($type) {
						default:
							if($key && $type && $value) $w->where($key,$type,$value);
						break;
						case 'like':
						case 'not like':
							$value = '%'.$value.'%';
							if($key && $type && $value) $w->where($key,$type,$value);
						break;
						case 'in':
						case 'not in':
							if($value) {
								$value = explode(',',$value);
								if($key && $value) $w->whereIn($key,$value);
							}
						break;
					}
				}
			});

			foreach($filter_column as $key=>$fc) {
				$value = @$fc['value'];
				$type  = @$fc['type'];
				$sorting = @$fc['sorting'];

				if($sorting!='') {
					if($key) {
						$pos_item->orderby($key,$sorting);
						$filter_is_orderby = true;
					}
				}

				if ($type=='between') {
					if($key && $value) $pos_item->whereBetween($key,$value);
				}

				else {
					continue;
				}
			}
		}

		$posItems = $pos_item->get();
		
		$headings = array('Product ID',
			'Product Name',
			'Active Flag',
			'Memo',
			'Tax Type',
			'Sale Flag',
			'Unit of Measure ID',
			'Standard Cost',
			'List Price',
			'Generic Name',
			'Barcode 1',
			'Barcode 2',
			'Barcode 3',
			'Alternate Code',
			'Product Type',
			'Class ID',
			'Color Highlight',
			'Supplier ID',
			'Reorder Quantity',
			'Track Expiry',
			'Track Warranty',
			'Warranty Duration',
			'Duration Type',
			'Category 1',
			'Category 2',
			'Category 3',
			'Category 4',
			'Category 5',
			'Category 6');

		Excel::create('Export DIMFSv3.0 POS Items - '.date("Ymd H:i:sa"), function($excel) use ($posItems, $headings) {

			$excel->sheet('posformat', function($sheet) use ($posItems, $headings) {
				// Set auto size for sheet
				$sheet->setAutoSIZE(true);
				$sheet->setColumnFormat(array(
					'H' => '0.00',	//for standard cost
					'I' => '0.00',	//for list price
					'K' => '@',		//for upc code/barcode 1
				));
				
				foreach($posItems as $item) {

					$items_array[] = array(
						$item->digits_code,			//product id
						$item->item_description,	//product name
						'1',						//active flag
						'',							//memo
						'0',						//tax type (0-all)
						'1',						//sale flag (1-all)
						$item->uom_code,			//uom
						$item->dtp_rf,				//standard cost (all blank - dtp_btbdw)
						$item->current_srp,			//list price
						'',							//generic name (all blank)
						$item->upc_code,			//barcode 1
						'',							//barcode 2
						'',							//barcode 3
						'',							//alternate code
						$item->has_serial,			//product type (serial code)
						'',							//class id
						'',							//color highlight
						'',							//supplier id
						'0',						//reorder quantity (0-all)
						'0',						//track expiry (0-all)
						'1',						//track warranty (1-all)
						'1',						//warranty duration
						'Years',					//duration type
						$item->category_code,		//category 1 (category code)
						$item->subclass_code,		//category 2 (subclass code)
						$item->brand_code,			//category 3 (brand code)
						$item->class_code,			//category 4 (class code)
						'',							//category 5
						'',							//category 6
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

	public function exportBartenderFormat() {

		$data_items = ItemMaster::bartenderFormat()->get();
	    $headings = array('DIGITS CODE', 'UPC CODE', 'ITEM DESCRIPTION', 'CURRENT SRP', 'ORIGINAL SRP');

	    Excel::create('Export DIMFSv3.0 Bartender Items -'.date("d M Y - h.i.sa"), function($excel) use ($data_items, $headings) {
    		// Set the title
            $excel->setTitle('bartender');
            // Chain the setters
            $excel->setCreator('Digits IMFS')->setCompany('DTC');
            $excel->setDescription('bartender');

			$excel->sheet('bartender', function($sheet) use ($data_items, $headings) {
	        	// Set auto size for sheet
				$sheet->setAutoSIZE(true);
				$sheet->setColumnFormat(array(
					'B' => '@',		//for upc code
				    'D' => '0.00',	//for current srp
				    'E' => '0.00',	//for original srp
				));

				foreach($data_items as $item) {
                    
	                $datas[] = array(
	                    $item->digits_code,
	                    $item->upc_code,
	                    $item->item_description,
	                    (is_null($item->price_change)) ? $item->current_srp : $item->price_change,
	                    '',
	                );
                }
                
				$sheet->fromArray($datas, null, 'A1', false, false);
				$sheet->prependRow(1, $headings);
				$sheet->row(1, function($row) {
				    $row->setBackground('#FFFF00');
				    $row->setAlignment('center');
				});
				$sheet->cells('A1:E1', function($cells) {
					// Set font weight to bold
				    $cells->setFontWeight('bold');
				    // Set all borders (top, right, bottom, left)
					$cells->setBorder('none', 'none', 'solid', 'none');
				});
				
	        });
		})->export('xlsx');
			
	}

	public function exportAllItems() {
		
		$db_con = mysqli_connect(
			env('DB_HOST'), 
			env('DB_USERNAME'), 
			env('DB_PASSWORD'), 
			env('DB_DATABASE'), 
			env('DB_PORT')
		);

		if(!$db_con) {
			die('Could not connect: ' . mysqli_error($db_con));
		}

		$item_header = array('DIGITS CODE');
        
		if(CRUDBooster::myColumnView()->upc_code_1){
		    array_push($item_header,'UPC CODE-1');
		}
		if(CRUDBooster::myColumnView()->upc_code_2){
		    array_push($item_header,'UPC CODE-2');
		}
		
		if(CRUDBooster::myColumnView()->upc_code_3){
		    array_push($item_header,'UPC CODE-3');
		}
		
		if(CRUDBooster::myColumnView()->upc_code_4){
		    array_push($item_header,'UPC CODE-4');
		}
		
		if(CRUDBooster::myColumnView()->upc_code_5){
		    array_push($item_header,'UPC CODE-5');
		}

		if(CRUDBooster::myColumnView()->supplier_item_code){
			array_push($item_header, 'SUPPLIER ITEM CODE');
		}
		
		if(CRUDBooster::myColumnView()->model_number){
			array_push($item_header, 'MODEL NUMBER');
		}

		if(CRUDBooster::myColumnView()->initial_wrr_date){
			array_push($item_header, 'INITIAL WRR DATE (YYYY-MM-DD)');
		}
		
		if(CRUDBooster::myColumnView()->latest_wrr_date){
			array_push($item_header, 'LATEST WRR DATE (YYYY-MM-DD)');
		}
		
		array_push($item_header, 'APPLE LOB');
		array_push($item_header, 'APPLE REPORT INCLUSION');

		if(CRUDBooster::myColumnView()->brand_description){
			array_push($item_header, 'BRAND GROUP');
		}

		if(CRUDBooster::myColumnView()->brand_description){
			array_push($item_header, 'BRAND DESCRIPTION');
		}

		if(CRUDBooster::myColumnView()->brand_status){
			array_push($item_header, 'BRAND STATUS');
		}

		if(CRUDBooster::myColumnView()->sku_status){
			array_push($item_header, 'SKU STATUS');
		}
		
        if(CRUDBooster::myColumnView()->sku_legend){
		    array_push($item_header,'SKU LEGEND (RE-ORDER MATRIX)');
        }

		array_push($item_header, 'ITEM DESCRIPTION');
		
		if(CRUDBooster::myColumnView()->model){
			array_push($item_header, 'MODEL');
		}
		
		if(CRUDBooster::myColumnView()->year_launch){
			array_push($item_header, 'YEAR LAUNCH');
		}
		
		if(CRUDBooster::myColumnView()->model_specific_desc){
			array_push($item_header, 'MODEL SPECIFIC DESCRIPTION');
		}

		if(CRUDBooster::myColumnView()->compatibility){
			array_push($item_header, 'COMPATIBILITY');
		}

		if(CRUDBooster::myColumnView()->margin_category_desc){
			array_push($item_header, 'MARGIN CATEGORY DESCRIPTION');
		}

		if(CRUDBooster::myColumnView()->category_description){
			array_push($item_header, 'CATEGORY DESCRIPTION');
		}
			
		if(CRUDBooster::myColumnView()->class_description){
			array_push($item_header, 'CLASS DESCRIPTION');
		}
		
		if(CRUDBooster::myColumnView()->subclass){
			array_push($item_header, 'SUBCLASS DESCRIPTION');
		}
		
		if(CRUDBooster::myColumnView()->wh_category){
			array_push($item_header, 'WH CATEGORY DESCRIPTION');
		}

		if(CRUDBooster::myColumnView()->original_srp){
			array_push($item_header, 'ORIGINAL SRP');
		}
		
		if(CRUDBooster::myColumnView()->current_srp){
			array_push($item_header, 'CURRENT SRP');
		}
		
		if(CRUDBooster::myColumnView()->promo_srp){
			array_push($item_header, 'DG SRP');
		}
		
		if(CRUDBooster::myColumnView()->price_change){
			array_push($item_header, 'PRICE CHANGE');
		}
		
		if(CRUDBooster::myColumnView()->price_effective_date){
			array_push($item_header, 'PRICE CHANGE DATE');
		}

		if(CRUDBooster::myColumnView()->store_cost_rf){
			array_push($item_header, 'STORE COST');
		}
		
		if(CRUDBooster::myColumnView()->store_cost_prf){
			array_push($item_header, 'STORE MARGIN (%)');
		}

        if(CRUDBooster::myColumnView()->store_cost_ecom){
			array_push($item_header, 'ECOMM - STORE COST');
		}
		
		if(CRUDBooster::myColumnView()->store_cost_pecom){
			array_push($item_header, 'ECOMM - STORE MARGIN (%)');
		}
		
		if(CRUDBooster::myColumnView()->landed_cost){
			array_push($item_header, 'LANDED COST');
		}
		
		if(CRUDBooster::myColumnView()->actual_landed_cost){
			array_push($item_header, 'AVERAGE LANDED COST');
		}

		if(CRUDBooster::myColumnView()->landed_cost_sea){
			array_push($item_header, 'LANDED COST VIA SEA');
		}
		
		if(CRUDBooster::myColumnView()->w_store_cost_rf){
			array_push($item_header, 'WORKING STORE COST');
		}
		
		if(CRUDBooster::myColumnView()->w_store_cost_prf){
			array_push($item_header, 'WORKING STORE MARGIN (%)');
		}
		
		if(CRUDBooster::myColumnView()->w_store_cost_ecom){
			array_push($item_header, 'ECOMM - WORKING STORE COST');
		}
		
		if(CRUDBooster::myColumnView()->w_store_cost_pecom){
			array_push($item_header, 'ECOMM - WORKING STORE MARGIN (%)');
		}
		
		if(CRUDBooster::myColumnView()->w_landed_cost){
			array_push($item_header, 'WORKING LANDED COST');
		}

		if(CRUDBooster::myColumnView()->duration_from){
			array_push($item_header, 'DURATION FROM');
		}

		if(CRUDBooster::myColumnView()->duration_to){
			array_push($item_header, 'DURATION TO');
		}

		if(CRUDBooster::myColumnView()->support_type){
			array_push($item_header, 'SUPPORT TYPE');
		}

		if(CRUDBooster::myColumnView()->vendor_type){
			array_push($item_header, 'VENDOR TYPE CODE');
		}

		if(CRUDBooster::myColumnView()->moq){
			array_push($item_header, 'MOQ');
		}
		
		if(CRUDBooster::myColumnView()->incoterms){
			array_push($item_header, 'INCOTERMS');
		}
		
		if(CRUDBooster::myColumnView()->currency_1){
			array_push($item_header, 'CURRENCY');
		}
		
		if(CRUDBooster::myColumnView()->purchase_price_1){
			array_push($item_header, 'SUPPLIER COST');
		}

		if(CRUDBooster::myColumnView()->size){
			array_push($item_header, 'SIZE');
		}
		
		if(CRUDBooster::myColumnView()->item_length){
            array_push($item_header, 'LENGTH [CM]');
        }
        
        if(CRUDBooster::myColumnView()->item_width){
            array_push($item_header, 'WIDTH [CM]');
        }
        
        if(CRUDBooster::myColumnView()->item_height){
            array_push($item_header, 'HEIGHT [CM]');
        }
        
        if(CRUDBooster::myColumnView()->item_weight){
            array_push($item_header, 'WEIGHT [KG]');
        }
		
		if(CRUDBooster::myColumnView()->actual_color){
			array_push($item_header, 'ACTUAL COLOR');
		}
		
		if(CRUDBooster::myColumnView()->color_description){
			array_push($item_header, 'MAIN COLOR DESCRIPTION');
		}
		
		if(CRUDBooster::myColumnView()->uom){
			array_push($item_header, 'UOM CODE');
		}
		
		if(CRUDBooster::myColumnView()->inventory_type){
			array_push($item_header, 'INVENTORY TYPE');
		}
		
		if(CRUDBooster::myColumnView()->sku_class){
			array_push($item_header, 'SKU CLASS');
		}
		
        if(CRUDBooster::myColumnView()->segmentation){
            foreach ($this->getSegmentations() as $segmentation) {
            	array_push($item_header, $segmentation->segmentation_description);
            }
        }

		if(CRUDBooster::myColumnView()->store_cost_pdcon){
			array_push($item_header, 'MAX CONSIGNMENT RATE (%)');
		}
		
		if(CRUDBooster::myColumnView()->lightroom_cost){
			array_push($item_header, 'LIGHTROOM COST');
		}
		
		if(CRUDBooster::myColumnView()->vendor_name){
			array_push($item_header, 'VENDOR NAME');
		}
		
		if(CRUDBooster::myColumnView()->vendor_status){
			array_push($item_header, 'VENDOR STATUS');
		}
		
		if(CRUDBooster::myColumnView()->vendor_group_name){
			array_push($item_header, 'VENDOR GROUP NAME');
		}
		
		if(CRUDBooster::myColumnView()->vendor_group_status){
			array_push($item_header, 'VENDOR GROUP STATUS');
		}
		
		if(CRUDBooster::myColumnView()->warranty_duration){
			array_push($item_header, 'WARRANTY DURATION');
		}
		
		if(CRUDBooster::myColumnView()->warranty){
			array_push($item_header, 'WARRANTY DURATION TYPE');
		}
		
		if(CRUDBooster::myColumnView()->serialized){
            array_push($item_header, 'SERIAL CODE');
        }
        if(CRUDBooster::myColumnView()->imei_code_1){
            array_push($item_header, 'IMEI CODE 1');
        }
        if(CRUDBooster::myColumnView()->imei_code_2){
            array_push($item_header, 'IMEI CODE 2');
        }
		
		if(CRUDBooster::myColumnView()->createdby){
			array_push($item_header, 'CREATED BY');
		}
		
		if(CRUDBooster::myColumnView()->created_date){
			array_push($item_header, 'CREATED DATE');
		}
		
		if(CRUDBooster::myColumnView()->approvedby){
			array_push($item_header, 'APPROVED BY');
		}
		
		if(CRUDBooster::myColumnView()->approved_date){
			array_push($item_header, 'APPROVED DATE');
		}
		
		if(CRUDBooster::myColumnView()->updatedby){
			array_push($item_header, 'UPDATED BY');
		}
		
		if(CRUDBooster::myColumnView()->updated_date){
			array_push($item_header, 'UPDATED DATE');
		}

		$filename = "Export DIMFSv3.0 Items - ".date("Ymd H:i:s"). ".csv";

		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: text/csv; charset=UTF-16LE");

		$out = fopen("php://output", 'w');
		$flag = false;

		
		$sql_query = "SELECT `digits_code`,";
		
		
        
        if(CRUDBooster::myColumnView()->upc_code_1){
            $sql_query .= "`upc_code`,";
        }
        if(CRUDBooster::myColumnView()->upc_code_2){
            $sql_query .= "`upc_code2`,";
        }
        if(CRUDBooster::myColumnView()->upc_code_3){
            $sql_query .= "`upc_code3`,";
        }
        if(CRUDBooster::myColumnView()->upc_code_4){
            $sql_query .= "`upc_code4`,";
        }
        if(CRUDBooster::myColumnView()->upc_code_5){
            $sql_query .= "`upc_code5`,";
        }
        
		if(CRUDBooster::myColumnView()->supplier_item_code){
			$sql_query .= "`supplier_item_code`,";
		}
		if(CRUDBooster::myColumnView()->model_number){
			$sql_query .= "`model_number`,";
		}

		if(CRUDBooster::myColumnView()->initial_wrr_date){
		    $sql_query .="`initial_wrr_date`,";
		}

		if(CRUDBooster::myColumnView()->latest_wrr_date){
			$sql_query .="`latest_wrr_date`,";
		}

		$sql_query .="`apple_lobs`.apple_lob_description,";
		$sql_query .="`apple_report_inclusion`,";

		if(CRUDBooster::myColumnView()->brand_description){
			$sql_query .= "`brands`.brand_group,";
		}

		if(CRUDBooster::myColumnView()->brand_description){
			$sql_query .= "`brands`.brand_description,";
		}

		if(CRUDBooster::myColumnView()->brand_status){
			$sql_query .= "`brands`.status,";
		}

		if(CRUDBooster::myColumnView()->sku_status){
			$sql_query .= "`sku_statuses`.sku_status_description,";
		}
        if(CRUDBooster::myColumnView()->sku_legend){
		    $sql_query .= "`sku_legends`.sku_legend_description,";
        }
		
		$sql_query .= "`item_description`,";

		if(CRUDBooster::myColumnView()->model){
			$sql_query .= "`model`,";
		}
		if(CRUDBooster::myColumnView()->year_launch){
			$sql_query .= "`year_launch`,";
		}
		if(CRUDBooster::myColumnView()->model_specific_desc){
			$sql_query .= "`model_specifics`.model_specific_description,";
		}
		if(CRUDBooster::myColumnView()->compatibility){
			$sql_query .= "`compatibility`,";
		}
		
		if(CRUDBooster::myColumnView()->margin_category_desc){
			$sql_query .= "`margin_categories`.margin_category_description,";
		}
		if(CRUDBooster::myColumnView()->category_description){
			$sql_query .= "`categories`.category_description,";
		}
		if(CRUDBooster::myColumnView()->class_description){
			$sql_query .= "`classes`.class_description,";
		}
		if(CRUDBooster::myColumnView()->subclass){
			$sql_query .= "`subclasses`.subclass_description,";
		}
		
		if(CRUDBooster::myColumnView()->wh_category){
			$sql_query .= "`warehouse_categories`.warehouse_category_description,";
		}
		
		if(CRUDBooster::myColumnView()->original_srp){
			$sql_query .="`original_srp`,";
		}
		if(CRUDBooster::myColumnView()->current_srp){
			$sql_query .="`current_srp`,";
		}
		if(CRUDBooster::myColumnView()->promo_srp){
			$sql_query .="`promo_srp`,";
		}
		if(CRUDBooster::myColumnView()->price_change){
			$sql_query .="`price_change`,";
		}
		if(CRUDBooster::myColumnView()->price_effective_date){
			$sql_query .="`effective_date`,";
		}
		if(CRUDBooster::myColumnView()->store_cost_rf){
		    $sql_query .="`dtp_rf`,";
		}
		if(CRUDBooster::myColumnView()->store_cost_prf){
			$sql_query .="`dtp_rf_percentage`,";
		}
        if(CRUDBooster::myColumnView()->store_cost_ecom){
		    $sql_query .="`ecom_store_margin`,";
		}
		if(CRUDBooster::myColumnView()->store_cost_pecom){
			$sql_query .="`ecom_store_margin_percentage`,";
		}
		if(CRUDBooster::myColumnView()->landed_cost){
			$sql_query .="`landed_cost`,";
		}
		if(CRUDBooster::myColumnView()->actual_landed_cost){
			$sql_query .="`actual_landed_cost`,";
		}
		if(CRUDBooster::myColumnView()->landed_cost_sea){
			$sql_query .="`landed_cost_sea`,";
		}
		if(CRUDBooster::myColumnView()->w_store_cost_rf){
			$sql_query .="`working_dtp_rf`,";
		}
		if(CRUDBooster::myColumnView()->w_store_cost_prf){
			$sql_query .="`working_dtp_rf_percentage`,";
		}
		if(CRUDBooster::myColumnView()->w_store_cost_ecom){
		    $sql_query .="`working_ecom_store_margin`,";
		}
		if(CRUDBooster::myColumnView()->w_store_cost_pecom){
			$sql_query .="`working_ecom_store_margin_percentage`,";
		}
		if(CRUDBooster::myColumnView()->w_landed_cost){
			$sql_query .="`working_landed_cost`,";
		}
		if(CRUDBooster::myColumnView()->duration_from){
			$sql_query .="`duration_from`,";
		}
		if(CRUDBooster::myColumnView()->duration_to){
			$sql_query .="`duration_to`,";
		}
		if(CRUDBooster::myColumnView()->support_type){
			$sql_query .="`support_types`.support_type_description,";
		}
		if(CRUDBooster::myColumnView()->vendor_type){
			$sql_query .= "`vendor_types`.vendor_type_code,";
		}
		if(CRUDBooster::myColumnView()->moq){
			$sql_query .="`moq`,";
		}
		if(CRUDBooster::myColumnView()->incoterms){
			$sql_query .="`incoterms`.incoterms_code,";
		}
		if(CRUDBooster::myColumnView()->currency_1){
			$sql_query .="`currencies_1`.currency_code,";
		}
		if(CRUDBooster::myColumnView()->purchase_price_1){
			$sql_query .="`purchase_price`,";
		}
		if(CRUDBooster::myColumnView()->size){
			$sql_query .= "`size`,";
		}
		if(CRUDBooster::myColumnView()->item_length){
            $sql_query .="`item_length`,";
        }
        if(CRUDBooster::myColumnView()->item_width){
            $sql_query .="`item_width`,";
        }
        if(CRUDBooster::myColumnView()->item_height){
            $sql_query .="`item_height`,";
        }
        if(CRUDBooster::myColumnView()->item_weight){
            $sql_query .="`item_weight`,";
        }
		if(CRUDBooster::myColumnView()->actual_color){
			$sql_query .= "`actual_color`,";
		}
		if(CRUDBooster::myColumnView()->color_description){
			$sql_query .= "`colors`.color_description,";
		}
		if(CRUDBooster::myColumnView()->uom){
			$sql_query .= "`uoms`.uom_code,";
		}
		if(CRUDBooster::myColumnView()->inventory_type){
			$sql_query .= "`inventory_types`.inventory_type_description,";
		}
		if(CRUDBooster::myColumnView()->sku_class){
			$sql_query .= "`sku_classes`.sku_class_description,";
		}
		
        if(CRUDBooster::myColumnView()->segmentation){
            foreach ($this->getSegmentations() as $segmentation) {
            	$sql_query .="`".$segmentation->segmentation_column."`,";
            }
        }
		
		if(CRUDBooster::myColumnView()->store_cost_pdcon){
			$sql_query .="`dtp_dcon_percentage`,";
		}
		
		if(CRUDBooster::myColumnView()->lightroom_cost){
		    $sql_query .="`lightroom_cost`,";
		}
		
		if(CRUDBooster::myColumnView()->vendor_name){
			$sql_query .="`vendors`.vendor_name,";
		}
		if(CRUDBooster::myColumnView()->vendor_status){
			$sql_query .="`vendors`.status,";
		}
		if(CRUDBooster::myColumnView()->vendor_group_name){
			$sql_query .="`vendor_groups`.vendor_group_name,";
		}
		if(CRUDBooster::myColumnView()->vendor_group_status){
			$sql_query .="`vendor_groups`.status,";
		}
		
		if(CRUDBooster::myColumnView()->warranty_duration){
			$sql_query .="`warranty_duration`,";
		}
		if(CRUDBooster::myColumnView()->warranty){
			$sql_query .="`warranties`.warranty_description,";
		}
		
		if(CRUDBooster::myColumnView()->serialized){
            $sql_query .="`has_serial`,";
        }
        if(CRUDBooster::myColumnView()->imei_code_1){
            $sql_query .="`imei_code1`,";
        }
        if(CRUDBooster::myColumnView()->imei_code_2){
            $sql_query .="`imei_code2`,";
        }
		if(CRUDBooster::myColumnView()->createdby){
			$sql_query .="`user1`.name,";
		}
		if(CRUDBooster::myColumnView()->created_date){
		    $sql_query .="`item_masters`.created_at,";
		}
		if(CRUDBooster::myColumnView()->approvedby){
			$sql_query .="`user3`.name,";
		}
		if(CRUDBooster::myColumnView()->approved_date){
			$sql_query .="`item_masters`.approved_at,";
		}
		if(CRUDBooster::myColumnView()->updatedby){
			$sql_query .="`user2`.name,";
		}
		if(CRUDBooster::myColumnView()->updated_date){
		    $sql_query .="`item_masters`.updated_at";
		}
		
		$sql_query = rtrim($sql_query, ',');

		$sql_query .=" FROM `item_masters` 
			LEFT JOIN `apple_lobs` ON `item_masters`.apple_lobs_id = `apple_lobs`.id 
			LEFT JOIN `brands` ON `item_masters`.brands_id = `brands`.id 
			LEFT JOIN `margin_categories` ON `item_masters`.margin_categories_id = `margin_categories`.id 
			LEFT JOIN `categories` ON `item_masters`.categories_id = `categories`.id 
			LEFT JOIN `classes` ON `item_masters`.classes_id = `classes`.id 
			LEFT JOIN `subclasses` ON `item_masters`.subclasses_id = `subclasses`.id 
			LEFT JOIN `warehouse_categories` ON `item_masters`.warehouse_categories_id = `warehouse_categories`.id 
			LEFT JOIN `model_specifics` ON `item_masters`.model_specifics_id = `model_specifics`.id 
			LEFT JOIN `colors` ON `item_masters`.colors_id = `colors`.id 
			LEFT JOIN `uoms` ON `item_masters`.uoms_id = `uoms`.id 
			LEFT JOIN `vendor_types` ON `item_masters`.vendor_types_id = `vendor_types`.id 
			LEFT JOIN `inventory_types` ON `item_masters`.inventory_types_id = `inventory_types`.id 
			LEFT JOIN `sku_statuses` ON `item_masters`.sku_statuses_id = `sku_statuses`.id 
			LEFT JOIN `sku_legends` ON `item_masters`.sku_legends_id = `sku_legends`.id
			LEFT JOIN `sku_classes` ON `item_masters`.sku_classes_id = `sku_classes`.id
			LEFT JOIN `currencies` as currencies_1 ON `item_masters`.currencies_id = `currencies_1`.id 
			LEFT JOIN `currencies` as currencies_2 ON `item_masters`.currencies_id1 = `currencies_2`.id 
			LEFT JOIN `vendors` ON `item_masters`.vendors_id = `vendors`.id 
			LEFT JOIN `vendor_groups` ON `item_masters`.vendor_groups_id = `vendor_groups`.id 
			LEFT JOIN `incoterms` ON `item_masters`.incoterms_id = `incoterms`.id 
			LEFT JOIN `support_types` ON `item_masters`.support_types_id = `support_types`.id 
			LEFT JOIN `warranties` ON `item_masters`.warranties_id = `warranties`.id 
			LEFT JOIN `cms_users` as user1 ON `item_masters`.created_by = `user1`.id 
			LEFT JOIN `cms_users` as user2 ON `item_masters`.updated_by = `user2`.id 
			LEFT JOIN `cms_users` as user3 ON `item_masters`.approved_by = `user3`.id";
		
		if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(), ["ACCTG HEAD","MCB TL","ADVANCED","REPORTS","ECOMM STORE MDSG TL"])){    
			$sql_query .=" WHERE `item_masters`.approval_status = '".$this->getStatusByDescription('APPROVED')."'";
		}
		else{    
			$sql_query .=" WHERE `item_masters`.approval_status = '".$this->getStatusByDescription('APPROVED')."' 
			    AND `item_masters`.sku_statuses_id != '".$this->invalid."'
				AND `item_masters`.inventory_types_id != '".$this->inactive_inventory."' ";
		}
		$sql_query .=" ORDER BY `item_masters`.digits_code ASC";
        ini_set('memory_limit', '-1');
		$resultset = mysqli_query($db_con, $sql_query) or die("Database Error:". mysqli_error($db_con));

		while($row = mysqli_fetch_row($resultset)) {
			if(!$flag) {
			// display field/column names as first row
			fputcsv($out, $item_header, ',', '"');
			$flag = true;
			}
			$cnt_array = 0;
			array_walk($row, 
				function(&$str, $key) {
				    
					if($str == 't') $str = 'TRUE';
					if($str == 'f') $str = 'FALSE';
					if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),["MCB TL","MCB TM"])){
    					if(in_array($key, [0,1,2,3,4,5,6,7])){
    					    $str ="='$str'";
    					}
    					
    				// 	if(strstr($str, '"')) {
    				// 		$str = '"' . str_replace('"', '""', $str) . '"';
    				// 	}
    					if(strstr($str, "'") && in_array($key, [0,1,2,3,4,5,6,7])) {
    						$str = str_replace("'", '"', $str);
    					}
					}
					$str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
				}
			);
			
			fputcsv($out, array_values($row), ',', '"');
		}

		fclose($out);
		exit;

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

	public function importView()
	{
		$data['page_title'] = 'Import Module';
	    return view('item-master.upload',$data);
	}

	public function importWRRView()
	{
		if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeName(),["SDM TL","REPORTS"])) {    
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}

		$data['page_title'] = 'Import Item WRR Date';
	    return view('item-master.wrr-date-upload',$data);
	}

	public function importItemView()
	{
		if(!CRUDBooster::isCreate() && $this->global_privilege==FALSE || $this->button_add==FALSE) {    
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		$data['page_title'] = 'Import New Item';
	    return view('item-master.new-item-upload',$data);
	}

	public function importSKULegendView()
	{
		if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeName(),["MCB TM","MCB TL"])) {   
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		$data['page_title'] = 'Import Item SKU Legend/Segmentation';
	    return view('item-master.skulegend-upload',$data);
	}
	
	public function importECOMView()
	{
		if(!CRUDBooster::isSuperadmin() && CRUDBooster::myPrivilegeName() != "ECOMM STORE MDSG TM") {   
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		$data['page_title'] = 'Import ECOM Details';
	    return view('item-master.ecom-upload',$data);
	}
	
	public function importItemAccountingView()
	{
		if(!CRUDBooster::isSuperadmin() && CRUDBooster::myPrivilegeName() != "COST ACCTG") {   
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		$data['page_title'] = 'Accounting Bulk Update';
	    return view('item-master.acctg-upload',$data);
	}

	public function importItemMcbView()
	{
		if(!CRUDBooster::isSuperadmin() && CRUDBooster::myPrivilegeName() != "MCB TL") {   
			CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
		}
		$data['page_title'] = 'MCB Bulk Update';
	    return view('item-master.mcb-upload',$data);
	}

	public function importWRRTemplate()
	{
		Excel::create('wrr-date-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('wrr', function ($sheet) {
				$sheet->row(1, array('DIGITS CODE','LATEST WRR DATE'));
				$sheet->row(2, array('80000001','yyyy-mm-dd'));
			});
		})->download('csv');
	}
	
	public function importECOMTemplate()
	{
		Excel::create('ecom-details-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
			$excel->sheet('ecom', function ($sheet) {
				$sheet->row(1, array('DIGITS CODE', 'LENGTH','WIDTH','HEIGHT','WEIGHT'));
				$sheet->row(2, array('80000001', '1.25','1.25','1.25','1.25'));
			});
		})->download('csv');
	}

	public function importWRR(Request $request)
	{
		$errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
			
		$validator = \Validator::make(
			['file' => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
			['file' => 'required', 'extension' => 'required|in:csv',]
		);

		if ($validator->fails()) {
			return back()->with('error_import', 'Failed ! Please check required file extension.');
		}

		if (Input::hasFile('import_file')) {
			$path = Input::file('import_file')->getRealPath();
			
			$csv = array_map('str_getcsv', file($path));
			$dataExcel = Excel::load($path, function($reader) {})->get();
			
			$unMatch = [];
			$header = array('DIGITS CODE','LATEST WRR DATE');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			if(!empty($dataExcel) && $dataExcel->count()) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;

					$existingItem = ItemMaster::where('digits_code', intval($value->digits_code))->first();

					if(empty($existingItem)){
						array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
					}

					if(is_null($value->latest_wrr_date)){
						array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has blank wrr date.');
					}

					$dateObj = \DateTime::createFromFormat("Y-m-d", $value->latest_wrr_date);
					if (!$dateObj){
						array_push($errors, 'Line '.$line_item.': could not parse latest wrr date "'.$value->latest_wrr_date.'".');
						// throw new \UnexpectedValueException("Could not parse the date: $value->latest_wrr_date");
					}

					if(empty($existingItem->initial_wrr_date) || is_null($existingItem->initial_wrr_date)){
						$data = [
							'initial_wrr_date' => date('Y-m-d', strtotime((string)$value->latest_wrr_date)),
							'latest_wrr_date' => date('Y-m-d', strtotime((string)$value->latest_wrr_date))
						];
					}
					else{
						$data = self::getLatestWRRDate($value->digits_code, $value->latest_wrr_date);
					}

					try {
						if(empty($errors)){
							$cnt_success++;
							ItemMaster::where('digits_code', intval($value->digits_code))->update($data);
						}
						
					} catch (\Exception $e) {
						$cnt_fail++;
						array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
					}
				}
			}
		}

		if(empty($errors)){
			return back()->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
		}
		else{
			return back()->with('error_import', implode("<br>", $errors));
		}

	}
	
	public function importECOM(Request $request)
	{
	    $errors = array();
		$cnt_success = 0;
		$cnt_fail = 0;
		$file = $request->file('import_file');
			
		$validator = \Validator::make(
			['file' => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
			['file' => 'required', 'extension' => 'required|in:csv',]
		);

		if ($validator->fails()) {
			return back()->with('error_import', 'Failed ! Please check required file extension.');
		}

		if (Input::hasFile('import_file')) {
			$path = Input::file('import_file')->getRealPath();
			
			$csv = array_map('str_getcsv', file($path));
			$dataExcel = Excel::load($path, function($reader) {})->get();
			
			$unMatch = [];
			$header = array('DIGITS CODE','LENGTH','WIDTH','HEIGHT','WEIGHT');

			for ($i=0; $i < sizeof($csv[0]); $i++) {
				if (! in_array($csv[0][$i], $header)) {
					$unMatch[] = $csv[0][$i];
				}
			}

			if(!empty($unMatch)) {
				return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
			}
			
			if(!empty($dataExcel) && $dataExcel->count()) {

				foreach ($dataExcel as $key => $value) {
					$data = array();
					$line_item = 0;	
					$line_item = $key+1;
					
					$existingItem = ItemMaster::where('digits_code', intval($value->digits_code))->first();
					
					if(empty($existingItem)){
						array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
					}
					
					if(empty($value->length)){
						array_push($errors, 'Line '.$line_item.': blank item length.');
					}
					
					if(empty($value->width)){
						array_push($errors, 'Line '.$line_item.': blank item width.');
					}
					
					if(empty($value->height)){
						array_push($errors, 'Line '.$line_item.': blank item height.');
					}
					
					if(empty($value->weight)){
						array_push($errors, 'Line '.$line_item.': blank item weight.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->length, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with length: "'.$value->length.'" should have 2 decimal places only.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->width, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with width: "'.$value->width.'" should have 2 decimal places only.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->height, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with height: "'.$value->height.'" should have 2 decimal places only.');
					}
					
					if(!preg_match('/^[0-9]+\.[0-9]{2}$/', number_format($value->weight, 2, '.', ''))){
					    array_push($errors, 'Line '.$line_item.': with weight: "'.$value->weight.'" should have 2 decimal places only.');
					}
					
					else{
					    $data["item_length"] = number_format($value->length, 2, '.', '');
					    $data["item_width"] = number_format($value->width, 2, '.', '');
					    $data["item_height"] = number_format($value->height, 2, '.', '');
					    $data["item_weight"] = number_format($value->weight, 2, '.', '');
					}

					try {
						if(empty($errors)){
							$cnt_success++;
							ItemMaster::where('digits_code', intval($value->digits_code))->update($data);
						}
						
					} catch (\Exception $e) {
						$cnt_fail++;
						array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
					}
				}
			}
		}

		if(empty($errors)){
			return back()->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
		}
		else{
			return back()->with('error_import', implode("<br>", $errors));
		}
	}

	public function getLatestWRRDate($digits_code, $latest_wrr_date)
	{
		$data = array();
		$existingItemLatestWRR = ItemMaster::where('digits_code', intval($digits_code))->value('latest_wrr_date');
		$first = new Carbon((string)$existingItemLatestWRR);
		$second = new Carbon((string)$latest_wrr_date);
		
		if($first->gte($second)){
			$data = [
				'latest_wrr_date' => $existingItemLatestWRR
			];
		}
		elseif(!is_null($latest_wrr_date)){
			$data = [
				'latest_wrr_date' => date('Y-m-d', strtotime((string)$latest_wrr_date))
			];
		}
		else{
			$data = [
				'latest_wrr_date' => $existingItemLatestWRR
			];
		}
		return $data;
	}

	public function getExistingUPC(Request $request)
	{
		$data = array();
		$data['status_no'] = 0;
		$data['message'] ='No upc code found!';

		$existingItem = ItemMaster::where('upc_code', $request->upc_code)->first();
		if(!empty($existingItem)){
			$data['status_no'] = 1;
			$data['message'] ='Existing upc code found!';
			$data['item'] = $existingItem;
		}

		echo json_encode($data);
		exit;
	}

	public function getExistingDigitsCode(Request $request)
	{
		$data = array();
		$data['status_no'] = 0;
		$data['message'] ='No digits code found!';

		$existingItem = ItemMaster::where('digits_code', $request->digits_code)->first();
		if(!empty($existingItem)){
			$data['status_no'] = 1;
			$data['message'] ='Existing digits code found!';
		}

		echo json_encode($data);
		exit;
	}
	
	public function compareCurrentSRP(Request $request)
	{
	    $data = array();
		$data['status_no'] = 0;
		$data['message'] ='Price is greater than current srp!';

		$existingItem = ItemMaster::where('digits_code', $request->digits_code)->first();
		if($request->price_change < $existingItem->current_srp){
			$data['status_no'] = 1;
		}

		echo json_encode($data);
		exit;
	}

	public function sendApprovedItemEmailNotif()
	{
		$data = array();
		$data['name'] = 'User';
		$data['date_today'] = date('Y-m-d');
		
		$approvedItems = ItemMaster::where('approved_at', '>', date('Y-m-d').' 00:00:00')->get();
		
		$items = "<table class='table table-bordered' style='border: 1px solid black;'></tbody><tr ><td style='border: 1px solid black;' width='50%'><b>Digits Code</b></td><td style='border: 1px solid black;' width='50%'><b>Item Description</b></td></tr>";
		foreach ($approvedItems as $key => $value) {
			$items .="<tr><td style='border: 1px solid black;'>".$value->digits_code."</td><td style='border: 1px solid black;'>".$value->item_description."</td></tr>";
		}
		$items .="</tbody></table>";
		$data['items'] = $items;
		CRUDBooster::sendEmail(['to'=>'bpg@digits.ph','data'=>$data,'template'=>'approved_items_rma_notification','attachments'=>[]]);
		
		CRUDBooster::sendEmail(['to'=>'bpg@digits.ph','data'=>$data,'template'=>'approved_items_ecom_notification','attachments'=>[]]);
	
	}
	
	public function exportMargin(){
	    
	    $db_con = mysqli_connect(
			env('DB_HOST'), 
			env('DB_USERNAME'), 
			env('DB_PASSWORD'), 
			env('DB_DATABASE'), 
			env('DB_PORT')
		);

		if(!$db_con) {
			die('Could not connect: ' . mysqli_error($db_con));
		}

		$item_header = array('DIGITS CODE',
            'INITIAL WRR DATE (YYYY-MM-DD)',
            'LATEST WRR DATE (YYYY-MM-DD)',
            'MOQ',
            'CURRENCY',
            'SUPPLIER COST',
            'ITEM DESCRIPTION',
            'BRAND',
            'MARGIN CATEGORY',
            'CATEGORY',
            'SUBCLASS',
            'CURRENT SRP',
            'DG SRP',
            'PRICE CHANGE',
            'PRICE CHANGE DATE',
            'STORE COST',
            'STORE MARGIN (%)',
            'LANDED COST',
            'WORKING STORE COST',
            'WORKING STORE MARGIN (%)',
            'WORKING LANDED COST',
            'DURATION FROM',
            'DURATION TO',
            'SUPPORT TYPE',
            'LANDED COST VIA SEA'
        );
        
        $filename = "Export DIMFSv3.0 Margin Monitoring - ".date("Ymd H:i:s"). ".csv";

		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: text/csv; charset=UTF-16LE");

		$out = fopen("php://output", 'w');
		$flag = false;

		
		$sql_query = "SELECT `digits_code`,
		    `initial_wrr_date`,
		    `latest_wrr_date`,
		    `moq`,
		    `currencies_1`.currency_code,
		    `purchase_price`,
		    `item_description`,
		    `brands`.brand_description,
		    `margin_categories`.margin_category_description,
		    `categories`.category_description,
		    `subclasses`.subclass_description,
		    `current_srp`,
		    `promo_srp`,
		    `price_change`,
		    `effective_date`,
		    `dtp_rf`,
		    `dtp_rf_percentage`,
		    `landed_cost`,
		    `working_dtp_rf`,
		    `working_dtp_rf_percentage`,
		    `working_landed_cost`,
		    `duration_from`,
		    `duration_to`,
		    `support_types`.support_type_description,
		    `landed_cost_sea`";
		
		$sql_query = rtrim($sql_query, ',');

		$sql_query .=" FROM `item_masters` 
			LEFT JOIN `brands` ON `item_masters`.brands_id = `brands`.id 
			LEFT JOIN `margin_categories` ON `item_masters`.margin_categories_id = `margin_categories`.id 
			LEFT JOIN `categories` ON `item_masters`.categories_id = `categories`.id 
			LEFT JOIN `subclasses` ON `item_masters`.subclasses_id = `subclasses`.id
			LEFT JOIN `currencies` as currencies_1 ON `item_masters`.currencies_id = `currencies_1`.id 
			LEFT JOIN `support_types` ON `item_masters`.support_types_id = `support_types`.id 
			WHERE `item_masters`.approval_status = '".$this->getStatusByDescription('APPROVED')."' AND 
			`brands`.status = 'ACTIVE'";
		
		$sql_query .=" ORDER BY `item_masters`.digits_code ASC";
        ini_set('memory_limit', '-1');
		$resultset = mysqli_query($db_con, $sql_query) or die("Database Error:". mysqli_error($db_con));

		while($row = mysqli_fetch_row($resultset)) {
			if(!$flag) {
			// display field/column names as first row
			fputcsv($out, $item_header, ',', '"');
			$flag = true;
			}
			$cnt_array = 0;
			array_walk($row, 
				function(&$str, $key) {
				    
					if($str == 't') $str = 'TRUE';
					if($str == 'f') $str = 'FALSE';
					if(in_array($key, [0])){
        			    $str ="='$str'";
        			}
        // 			if(strstr($str, '"')) {
        // 				$str = '"' . str_replace('"', '""', $str) . '"';
        // 			}
        			if(strstr($str, "'")) {
        				$str = str_replace("'", '"', $str);
        			}
					$str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
				}
			);
			
			fputcsv($out, array_values($row), ',', '"');
		}

		fclose($out);
		exit;
		
	}
	
	public static function setStoreCostPercentage(&$postdata){
		$csm_percentage = 0.0000;

        if(empty($postdata["promo_srp"]) && !empty($postdata["dtp_rf"])) {
            $csm_percentage = ($postdata["current_srp"] - $postdata["dtp_rf"])/$postdata["current_srp"];
        }
        
        if(!empty($postdata["promo_srp"]) && !empty($postdata["dtp_rf"])){
            $csm_percentage = ($postdata["promo_srp"] - $postdata["dtp_rf"])/$postdata["promo_srp"];
            
        }
        
        return number_format($csm_percentage , 4, '.', '');
        
	}

	public static function setWorkingStoreCostPercentage(&$postdata){
		$cwsm_percentage = 0.0000;
        
		if(empty($postdata["promo_srp"]) && !empty($postdata["working_dtp_rf"])){
			$cwsm_percentage = ($postdata["current_srp"] - $postdata["working_dtp_rf"])/$postdata["current_srp"];
			
		}
		if(!empty($postdata["promo_srp"]) && !empty($postdata["working_dtp_rf"])){
			$cwsm_percentage = ($postdata["promo_srp"] - $postdata["working_dtp_rf"])/$postdata["promo_srp"];
			
		}
		
		return number_format($cwsm_percentage,4, '.', '');
	}
	
	public static function getUnitsMarginPercentage(Request $request)
	{
		$data = array();
		$data['status_no'] = 0;
		$data['message'] ='No matrix found!';
		
		$existingMatrix = MarginMatrix::where('brands_id', $request->brand_id)->first();

		if(!empty($existingMatrix)){
			$data['status_no'] = 1;
			$data['message'] ='Existing matrix found!';
			$data['matrix'] = $existingMatrix;
		}
		else{
			$data['status_no'] = 1;
			$data['message'] ='Existing matrix found!';
			$data['matrix'] = MarginMatrix::whereNull('brands_id')->where('margin_category','UNITS')->first();
		}
		
		echo json_encode($data);
		exit;
		
	}
	
	public static function getAccessoriesMarginPercentage(Request $request)
	{
		$data = array();
		$data['status_no'] = 0;
		$data['message'] ='No matrix found!';
		
		$existingMatrix = MarginMatrix::whereRaw('? between min and max', [floatval($request->margin_percentage)])
			->where('margin_category','ACCESSORIES')->where('matrix_type','BASED ON MATRIX')->first();

		if(!empty($existingMatrix)){
			$data['status_no'] = 1;
			$data['message'] ='Existing matrix found!';
			$data['matrix'] = $existingMatrix;
		}
		else{
		    $existingMatrix2 = MarginMatrix::whereRaw('? between min and max', [floatval($request->margin_percentage)])
			    ->where('margin_category','ACCESSORIES')->where('matrix_type','ADD TO LC')->first();
			    
			$data['status_no'] = 2;
			$data['message'] ='Existing matrix found!';
			$data['matrix'] = $existingMatrix2;
		}
		
		echo json_encode($data);
		exit;
		
	}
	
	public static function getMarginMatrixByMarginCategory($margin_category,$brand_id,$vendor_type_id){
        $marginMatrix =  MarginMatrix::where('matrix_type','ADD TO LC')
            ->where("vendor_types_id", $vendor_type_id)
            ->where("status", "ACTIVE")
			->select("store_margin_percentage", "store_margin_percentage")->get()->toArray();
			
		if(empty($marginMatrix)){
		    $margin_Matrix = MarginMatrix::where('margin_category',$margin_category)
            ->where("brands_id", $brand_id)
            ->whereNull("vendor_types_id")
            ->where('matrix_type','ADD TO LC')
            ->where("status", "ACTIVE")
			->select("store_margin_percentage", "store_margin_percentage")->get()->toArray();
			
			return Arr::flatten($margin_Matrix);
		}
		else{
		    return Arr::flatten($marginMatrix);
		}
	}
	
	public static function checkStoreCost(&$postdata){ 

		$csm = 0;
        if(in_array($postdata["vendor_types_id"], [3,4,5,6])){
            return true;
        }
		if($postdata["margin_category"] == "UNITS" && empty($postdata["promo_srp"])){
			$csm = ($postdata["current_srp"] - $postdata["dtp_rf"]) / $postdata["current_srp"];
            
            if(number_format($csm,7, '.', '') < 0){
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check store cost of ".$postdata["digits_code"]."!")->send();
			}
		}
		elseif($postdata["margin_category"] == "UNITS" && !empty($postdata["promo_srp"])){
			$csm = ($postdata["promo_srp"] - $postdata["dtp_rf"]) / $postdata["promo_srp"];
            if(number_format($csm,7, '.', '') < 0){
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check store cost of ".$postdata["digits_code"]."!")->send();
			}
		}
		elseif($postdata["margin_category"] == "ACCESSORIES" && empty($postdata["promo_srp"])){
		
			$csm = ($postdata["current_srp"] - $postdata["landed_cost"]) / $postdata["current_srp"];
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $postdata["margin_categories_id"], $postdata["margin_category"], $postdata["brands_id"]);
			
			if(number_format($ccsm->store_margin_percentage,4, '.', '') != $postdata["dtp_rf_percentage"] || number_format($csm,7, '.', '') < 0) {
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check store cost of ".$postdata["digits_code"]."!")->send();
			}
		}

		elseif($postdata["margin_category"] == "ACCESSORIES" && !empty($postdata["promo_srp"])){
		
			$csm = ($postdata["promo_srp"] - $postdata["landed_cost"]) / $postdata["promo_srp"];
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $postdata["margin_categories_id"], $postdata["margin_category"], $postdata["brands_id"]);
			if(number_format($ccsm->store_margin_percentage,4, '.', '') != $postdata["dtp_rf_percentage"] || number_format($csm,7, '.', '') < 0) {
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check store cost of ".$postdata["digits_code"]."!")->send();
			}
		}
	}
	
	public static function checkWorkingStoreCost(&$postdata){ 

		$csm = 0;
        if(in_array($postdata["vendor_types_id"], [3,4,5,6])){
            return true;
        }
		if($postdata["margin_category"] == "UNITS" && !empty($postdata["working_dtp_rf"]) && empty($postdata["promo_srp"])){
			$csm = ($postdata["current_srp"] - $postdata["working_dtp_rf"]) / $postdata["current_srp"];
            
            if(number_format($csm,7, '.', '') < 0){
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check working store cost of ".$postdata["digits_code"]."!")->send();
			}
		}
		elseif($postdata["margin_category"] == "UNITS" && !empty($postdata["working_dtp_rf"]) && !empty($postdata["promo_srp"])){
			$csm = ($postdata["current_srp"] - $postdata["working_dtp_rf"]) / $postdata["current_srp"];
            
            if(number_format($csm,7, '.', '') < 0){
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check working store cost of ".$postdata["digits_code"]."!")->send();
			}
		}
		elseif($postdata["margin_category"] == "ACCESSORIES" && empty($postdata["promo_srp"]) && !empty($postdata["working_dtp_rf"])){
		
			$csm = ($postdata["current_srp"] - $postdata["working_landed_cost"]) / $postdata["current_srp"]; //67%
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $postdata["margin_categories_id"], $postdata["margin_category"], $postdata["brands_id"]);
			
			if(number_format($ccsm->store_margin_percentage,4, '.', '') != $postdata["working_dtp_rf_percentage"] || number_format($csm,7, '.', '') < 0) {
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check working store cost of ".$postdata["digits_code"]."!")->send();
			}
		}

		elseif($postdata["margin_category"] == "ACCESSORIES" && !empty($postdata["promo_srp"]) && !empty($postdata["working_dtp_rf"])){
		
			$csm = ($postdata["promo_srp"] - $postdata["working_landed_cost"]) / $postdata["promo_srp"];
			$ccsm = self::getComputedMarginPercentage(number_format($csm,4, '.', ''), $postdata["margin_categories_id"], $postdata["margin_category"], $postdata["brands_id"]);
			
			if(number_format($ccsm->store_margin_percentage,4, '.', '') != $postdata["working_dtp_rf_percentage"] || number_format($csm,7, '.', '') < 0) {
				CRUDBooster::redirect(CRUDBooster::mainPath(),"Please check working store cost of ".$postdata["digits_code"]."!")->send();
			}
		}
	}
	
	public static function getComputedMarginPercentage($margin_percentage, $margin_categories_id, $margin_category, $brand){
	    
        $marginMatrix = MarginMatrix::whereRaw('? between min and max', [floatval($margin_percentage)])
            ->where('margin_category',$margin_category)
            ->where('brands_id', $brand)
            ->where('margin_categories_id','LIKE', '%'.$margin_categories_id.'%')->first();
        
        if(empty($marginMatrix)){
            return MarginMatrix::whereRaw('? between min and max', [floatval($margin_percentage)])
            ->where('margin_category',$margin_category)
            ->where('margin_categories_id','LIKE', '%'.$margin_categories_id.'%')->first();
        }
        else{
            return $marginMatrix;
        }
		
	}
	
	public static function getMarginMatrixByOtherMarginCategory($margin_category){
	    $marginMatrix =  MarginMatrix::where('matrix_type','DEDUCT FROM MALC')
            ->where("status", "ACTIVE")
			->select("store_margin_percentage", "store_margin_percentage")->get()->toArray();
			
		if(empty($marginMatrix)){
		    $margin_Matrix = MarginMatrix::where('margin_category',$margin_category)
            ->whereNull("vendor_types_id")
            ->where('matrix_type','DEDUCT FROM MALC')
            ->where("status", "ACTIVE")
			->select("store_margin_percentage", "store_margin_percentage")->get()->toArray();
			
			return Arr::flatten($margin_Matrix);
		}
		else{
		    return Arr::flatten($marginMatrix);
		}
	}

    public function EcomMarginPercentage(Request $request)
	{
		$data = array();  
		$alldata = [];
		$initial_computation = (number_format($request->current_srp , 2, '.', '') - number_format($request->landed_cost , 2, '.', '')) / number_format($request->current_srp , 2, '.', '');   
		$EcomSCPercentage = 0;
		
		$VendorandBrand = DB::table('ecom_margin_matrices')->where('vendor_types_id', $request->vendor_type)->where('brands_id',$request->brand)->where('margin_category','LIKE', '%'.$request->margin_category.'%')->first();
		
		if(!empty($VendorandBrand)){
			if(trim($VendorandBrand->margin_category) == "ACCESSORIES")
	        {   
	            $a = DB::table('ecom_margin_matrices')->where('vendor_types_id', $request->vendor_type)->where('brands_id',$request->brand)->where('margin_category',"ACCESSORIES")->first();
				if($initial_computation <= $VendorandBrand->max && $initial_computation >= $VendorandBrand->min) // In Range
				{
					$EcomSCPercentage = number_format($request->landed_cost , 2, '.', '') + (number_format($request->landed_cost , 2, '.', '') * number_format($VendorandBrand->store_margin_percentage , 2, '.', ''));
				}else if($initial_computation < $VendorandBrand->max && $initial_computation < $VendorandBrand->min) // Below minimum
				{
					$EcomSCPercentage = number_format($EcomSCPercentage , 2, '.', '') - number_format($VendorandBrand->store_margin_percentage , 2, '.', '');
				}else if($initial_computation > $VendorandBrand->max && $initial_computation > $VendorandBrand->min) // Above maximum
				{
					$EcomSCPercentage = number_format($request->landed_cost , 2, '.', '') + number_format($VendorandBrand->store_margin_percentage , 2, '.', '');
				}
				
				$alldata = $VendorandBrand;
			}else if(trim($VendorandBrand->margin_category) != "ACCESSORIES" && trim($VendorandBrand->margin_category) != "UNITS")
	        { 	
	            $a = DB::table('ecom_margin_matrices')->where('vendor_types_id', $request->vendor_type)->where('brands_id',$request->brand)->where('margin_category',"UNITS")->first();
	            $alldata = $VendorandBrand;
	        }else{
			    $a = DB::table('ecom_margin_matrices')->where('vendor_types_id', $request->vendor_type)->where('brands_id',$request->brand)->where('margin_category','==',"ACCESSORIES")->first();
			    $alldata = $VendorandBrand;
				$EcomSCPercentage = number_format($request->landed_cost , 2, '.', '') + number_format($VendorandBrand->store_margin_percentage , 2, '.', '');
			}
			
		}else{

            // $MarginCategory = DB::table('ecom_margin_matrices')->whereRaw('? between min and max', [floatval($margin_percentage)])
            // ->where('margin_category',$margin_category)
            // ->where('margin_categories_id','LIKE', '%'.$margin_categories_id.'%')->first();
            
			if(trim($request->margin_category) == "ACCESSORIES"){
				$MarginCategory = DB::table('ecom_margin_matrices')->where('margin_category',$request->margin_category)->where('max','>=',$initial_computation)->where('min','<=',$initial_computation)->first();
 			}else{
				$MarginCategory = DB::table('ecom_margin_matrices')->where('max','<=', $initial_computation)->orWhere('min','<', $initial_computation)->first();
			}
			
		// 			if($initial_computation <= $VendorandBrand->max && $initial_computation >= $VendorandBrand->min) // In Range
		// 			{
		// 				$EcomSCPercentage = number_format($request->landed_cost , 2, '.', '') + (number_format($request->landed_cost , 2, '.', '') * number_format($VendorandBrand->store_margin_percentage , 2, '.', ''));
		// 			}else if($initial_computation < $VendorandBrand->max && $initial_computation < $VendorandBrand->min) // Below minimum
		// 			{
		// 				$EcomSCPercentage = number_format($EcomSCPercentage , 2, '.', '') - number_format($VendorandBrand->store_margin_percentage , 2, '.', '');
		// 			}else if($initial_computation > $VendorandBrand->max && $initial_computation > $VendorandBrand->min) // Above maximum
		// 			{
		// 				$EcomSCPercentage = number_format($request->landed_cost , 2, '.', '') + number_format($VendorandBrand->store_margin_percentage , 2, '.', '');
		// 			}
				
			$EcomSCPercentage = number_format($request->landed_cost , 2, '.', '') + number_format($MarginCategory->store_margin_percentage , 2, '.', '');
			$alldata = $MarginCategory;
		}

		$data['alldata'] = $alldata;
		$data['EcomSCPercentage'] = (number_format($request->current_srp , 2, '.', '') - number_format($EcomSCPercentage , 4, '.', '')) / number_format($request->current_srp , 2, '.', '');
		$data['InitialComputation'] = $initial_computation;
		return($data);		
	}
}