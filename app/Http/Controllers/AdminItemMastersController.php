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
	use App\ItemMaster;
	use App\ItemMasterApproval;
	use App\ItemPriceChangeApproval;
	use App\ItemCurrentPriceChange;
	use App\Platform;
	use App\Size;
	use App\WorkflowSetting;
	use Illuminate\Http\Request;
	use Illuminate\Support\Arr;
	use Rap2hpoutre\FastExcel\FastExcel;

class AdminItemMastersController extends \crocodicstudio\crudbooster\controllers\CBController {

	use ItemTraits;

	public function __construct() {
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
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
		$this->button_export = false;
		$this->table = "item_masters";
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = [];
		foreach (config("user-export.access") as $key => $value) {
			if (in_array($key, $this->getItemAccess())) {
				$this->col[] = $value;
			}
		}
		
		$this->form = [];
		if(in_array(CRUDBooster::getCurrentMethod(), ['getAdd', 'postAddSave'])){
			foreach (config("user-export.forms") as $key => $value) {
				if($key=="size_description"){
					$value+=[config("user-export.forms.size_value")];
				}
				if (in_array($key, $this->getItemCreate())) {
					$value+=['width'=>'col-sm-6'];
					$this->form[] = $value;
				}
			}
		}
		if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave'])){
			foreach (config("user-export.forms") as $key => $value) {
				
				if(in_array($key, $this->getItemUpdateReadOnly())){
					$value+=['readonly'=>true];
				}
				if (in_array($key, $this->getItemUpdate())) {
					if($key=="size_description"){
						$this->form[]=config("user-export.forms.size_value");
					}
					$value+=['width'=>'col-sm-6'];
					$this->form[] = $value;
				}
			}
		}
		if(CRUDBooster::getCurrentMethod() == "getDetail"){
			foreach (config("user-export.forms") as $key => $value) {
				
				if (in_array($key, $this->getItemAccess())) {
					if($key=="size_description"){
						$this->form[]=config("user-export.forms.size_value");
					}
					$value+=['width'=>'col-sm-6'];
					$this->form[] = $value;
				}
				
			}
		}
		
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
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['BRAND MDSG TM','SDM TL','REPORTS','ECOMM STORE MDSG TM','MCB TL','COST ACCTG'])){
			    $this->index_button[] = ["title"=>"Import Module","label"=>"Import Module",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-view')];
			}
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['COST ACCTG','SALES ACCTG'])){
			    $this->index_button[] = ["title"=>"POS Format","label"=>"POS Format",'color'=>'warning',"icon"=>"fa fa-cart-plus","url"=>CRUDBooster::mainpath('export-pos').'?'.urldecode(http_build_query(@$_GET))];
			}
			if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['WHS TL','WIMS TM','WHS TM'])){
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
	
	public function actionButtonSelected($id_selected,$button_name) {
		//Your code here
		switch ($button_name) {
			case 'set_sku_status_ACTIVE':
				{
					ItemMaster::whereIn('id',$id_selected)->update([	
						'sku_statuses_id' => $this->getSkuStatus('ACTIVE'),
						'updated_by' => CRUDBooster::myId(),
						'updated_at' => date('Y-m-d H:i:s')
					]);
				}
				break;
			case 'set_sku_status_INVALID':
				{
					ItemMaster::whereIn('id',$id_selected)->update([	
						'sku_statuses_id' => $this->getSkuStatus('INVALID'),
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
	
	public function hook_query_index(&$query) {
		//Your code here
		
		if(!CRUDBooster::isSuperadmin() && (in_array(CRUDBooster::myPrivilegeName(), ["MCB TL","BRAND MDSG TM","ACCTG HEAD","ADVANCED","REPORTS","ECOMM STORE MDSG TL"]))){
        	$query->where('approval_status',$this->getStatusByDescription('APPROVED'));
        }
		else if(!CRUDBooster::isSuperadmin() && (!in_array(CRUDBooster::myPrivilegeName(), ["MCB TL","ACCTG HEAD","ADVANCED","REPORTS","ECOMM STORE MDSG TL"]))){
		    $query->where('approval_status',$this->getStatusByDescription('APPROVED'))
		        ->where('inventory_types_id','!=',$this->getInventoryType('INACTIVE'))
		        ->where('sku_statuses_id','!=',$this->getSkuStatus('INVALID'));
		}
	}
	
	public function hook_before_add(&$postdata) {        
		//Your code here
		$size = Size::where('id',$postdata["sizes_id"])->value('size_code');
		
		$postdata["size"]=($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
		$postdata["created_by"] = CRUDBooster::myId();
		$postdata["subcategories_id"] = 0;
		$postdata["warranties_id"] = 0;
		$postdata["warranty_duration"] = 0;
		$postdata["sku_statuses_id"] = $this->getSkuStatus('ACTIVE');
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
    	
    	if($postdata['sku_statuses_id'] == $this->getSkuStatus('INVALID')){
    	    $postdata['inventory_types_id'] = $this->getInventoryType('INACTIVE');
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
		
		elseif(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "BRAND MDSG TM"){
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
		$posItems->transform(function($pos){
			return [
				'Product ID' => $pos->digits_code,
				'Product Name' => $pos->item_description,
				'Active Flag' => '1',
				'Memo' => '',
				'Tax Type' => '0',
				'Sale Flag' => '1',
				'Unit of Measure ID' => $pos->uom_code,
				'Standard Cost' => $pos->dtp_rf,
				'List Price' => $pos->current_srp,
				'Generic Name' => '',
				'Barcode 1' => $pos->upc_code,
				'Barcode 2' => '',
				'Barcode 3' => '',
				'Alternate Code' => '',
				'Product Type' => $pos->has_serial,
				'Class ID' => '',
				'Color Highlight' => '',
				'Supplier ID' => '',
				'Reorder Quantity' => '0',
				'Track Expiry' => '0',
				'Track Warranty' => '1',
				'Warranty Duration' => '1',
				'Duration Type' => 'Years',
				'Category 1' => $pos->category_code,
				'Category 2' => $pos->subclass_code,
				'Category 3' => $pos->brand_code,
				'Category 4' => $pos->class_code,
				'Category 5' => '',
				'Category 6' => ''
			];
		});

		$filename = 'Export DIMFSv3.0 POS Items '.date("Ymd-His").'.xlsx';
		return (new FastExcel($posItems))->download($filename);
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

	public function exportMargin(){
	    
	    $margins = ItemMaster::marginMonitoring();

		$marginMonitoring = $margins->get();

		$marginMonitoring->transform(function($item){
			return [
				'Product ID' => $item->digits_code,
				'INITIAL WRR DATE (YYYY-MM-DD)' => $item->initial_wrr_date,
				'LATEST WRR DATE (YYYY-MM-DD)' => $item->latest_wrr_date,
				'MOQ' => $item->moq,
				'CURRENCY' => $item->currency_code,
				'SUPPLIER COST' => $item->purchase_price,
				'ITEM DESCRIPTION' => $item->item_description,
				'BRAND' => $item->brand_description,
				'MARGIN CATEGORY' => $item->margin_category_description,
				'CATEGORY' => $item->category_description,
				'SUBCLASS' => $item->subclass_description,
				'CURRENT SRP' => $item->current_srp,
				'DG SRP' => $item->promo_srp,
				'PRICE CHANGE' => $item->price_change,
				'PRICE CHANGE DATE' => $item->effective_date,
				'STORE COST' => $item->dtp_rf,
				'STORE MARGIN (%)' => $item->dtp_rf_percentage,
				'LANDED COST' => $item->landed_cost,
				'WORKING STORE COST' => $item->working_dtp_rf,
				'WORKING STORE MARGIN (%)' => $item->working_dtp_rf_percentage,
				'WORKING LANDED COST' => $item->working_landed_cost,
				'DURATION FROM' => $item->duration_from,
				'DURATION TO' => $item->duration_to,
				'SUPPORT TYPE' => $item->support_type_description,
				'LANDED COST VIA SEA' => $item->landed_cost_sea
			];
		});

		$filename = 'Export Margin Monitoring '.date("Ymd-His").'.xlsx';
		return (new FastExcel($marginMonitoring))->download($filename);
		
	}

	public function exportAllItems() {
		$allItems = ItemMaster::generateExport();
		if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeName(), ["ACCTG HEAD","MCB TL","ADVANCED","REPORTS","ECOMM STORE MDSG TL"])){
			$exportItems = $allItems->where('item_masters.sku_statuses_id','!=',$this->getSkuStatus("INVALID"))
				->where('item_masters.inventory_types_id','!=',$this->getInventoryType("INACTIVE"))->get();
		}
		else{
			$exportItems = $allItems->get();
		}
		$exportItems->transform(function($items){
			$exportItem = [];
			foreach ($this->getItemExport() as $newKey => $oldKey) {
				$exportItem[$newKey] = isset($items->$oldKey) ? $items->$oldKey : null;
			}	
			return $exportItem;
		});

		$filename = 'Export DIMFSv3.0 All Items '.date("Ymd-His").'.xlsx';
		return (new FastExcel($exportItems))->download($filename);	
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
		if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeName(),["BRAND MDSG TM","MCB TL"])) {   
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

		return json_encode($data);
	}

	public function getExistingDigitsCode(Request $request) {
		$data = array();
		$data['status_no'] = 0;
		$data['message'] ='No digits code found!';

		$existingItem = ItemMaster::where('digits_code', $request->digits_code)->first();
		if(!empty($existingItem)){
			$data['status_no'] = 1;
			$data['message'] ='Existing digits code found!';
		}

		return json_encode($data);
	}
	
	public function compareCurrentSRP(Request $request) {
	    $data = array();
		$data['status_no'] = 0;
		$data['message'] ='Price is greater than current srp!';

		$existingItem = ItemMaster::where('digits_code', $request->digits_code)->first();
		if($request->price_change < $existingItem->current_srp){
			$data['status_no'] = 1;
		}

		return json_encode($data);
	}

	public function sendApprovedItemEmailNotif() {
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
	
	public static function getUnitsMarginPercentage(Request $request) {
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
	
	public static function getAccessoriesMarginPercentage(Request $request) {
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

    public function ecomMarginPercentage(Request $request) {
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

	public function getApiItems($secret_key) {
		if ($secret_key != config('key-api.secret_key')) {
			return response([
				'message' => 'Error: Bad Request',
			], 404);
		}

		$created_items = ItemMaster::GenerateExport()
			->whereBetween(DB::raw('DATE(approved_at)'), [date('Y-m-d',strtotime("-1 days")), date('Y-m-d')])
			->get()
			->toArray();
			
		return response()->json([
			'created_items' => $created_items
		]);
	}
}