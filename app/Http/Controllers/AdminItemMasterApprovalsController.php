<?php namespace App\Http\Controllers;

	use Session;
	use DB;
	use CRUDBooster;
	use Excel;
	use App\Http\Traits\ItemTraits;
	use App\ItemMaster;
	use App\ItemMasterApproval;
	use App\Size;
	use App\WorkflowSetting;

	class AdminItemMasterApprovalsController extends \crocodicstudio\crudbooster\controllers\CBController {

		use ItemTraits;

        public function __construct() {
			DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
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
    		
    		# START COLUMNS DO NOT REMOVE THIS LINE
    		$this->col = [];
    		$this->col[] = ["label"=>"APPROVAL STATUS","name"=>"approval_status","join"=>"status_states,status_state"];
			foreach (config("user-export.access") as $key => $value) {
				if (in_array($key, $this->getItemAccess())) {
					$this->col[] = $value;
				}
			}
    		# END COLUMNS DO NOT REMOVE THIS LINE
    
    		# START FORM DO NOT REMOVE THIS LINE
    		$this->form = [];
			if(in_array(CRUDBooster::getCurrentMethod(), ['getEdit', 'postEditSave'])){
				foreach (config("user-export.forms") as $key => $value) {
					
					if(in_array($key, $this->getItemUpdateReadOnly('item_master_approvals'))){
						$value+=['readonly'=>true];
					}
					if (in_array($key, $this->getItemUpdate('item_master_approvals'))) {
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
    		# END FORM DO NOT REMOVE THIS LINE
			
	        $this->button_selected = array();
            if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "MCB TL") { //if approver
	        	$this->button_selected[] = ["label"=>"APPROVE","icon"=>"fa fa-check-circle","name"=>"approve"];
				$this->button_selected[] = ["label"=>"REJECT","icon"=>"fa fa-times-circle","name"=>"reject"];
	        }
	                
			$this->index_button = array();
			if(CRUDBooster::getCurrentMethod() == 'getIndex'){
				$this->index_button[] = ['label'=>'Export Pending Items','title'=>'Export Pending Items','color'=>'warning','url'=>route('exportPendingItems'),'icon'=>'fa fa-upload'];
			}
			
	        $this->table_row_color = array();     	          
            $this->table_row_color[] = ["condition"=>"[approval_status] == '3'","color"=>"danger"];//rejected items
	        
	        $this->load_js = array();
	        $this->load_js[] = asset("js/item_master_approval.js");        
	        
	    }
		
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
									'approval_status'=>$this->getStatusByDescription('APPROVED'),
									'approved_by'=>CRUDBooster::myId(),
									'approved_at'=>date('Y-m-d H:i:s')
								]);

								$new_item = ItemMasterApproval::where('id',$item->id)->first()->toArray();
								unset($new_item['item_masters_id']);
								unset($new_item['id']);
								ItemMaster::where('id',$item->item_masters_id)->update($new_item);

								$this->updateCounter(substr($itemCode, 0, 1));
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
									'approval_status'=>$this->getStatusByDescription('REJECTED')
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
				$query->where('item_master_approvals.approval_status',$this->getStatusByDescription('PENDING')); 
			elseif(!CRUDBooster::isSuperadmin()) {
				$query->where([
					'item_master_approvals.created_by'=>CRUDBooster::myId(),
					'item_master_approvals.approval_status'=>$this->getStatusByDescription('REJECTED')
				]);
			}   
	    }
		
	    public function hook_before_add(&$postdata) {        
	        //Your code here
            $postdata["created_by"]=CRUDBooster::myId();
	    }

	    public function hook_before_edit(&$postdata,$id) {        
			//Your code here
			$size = Size::getSizeCode($postdata["sizes_id"]);
			
            if(isset($postdata["warranty_duration"])){
                if(is_null($postdata["warranty_duration"]) || $postdata["warranty_duration"] == "")
                    $postdata["warranty_duration"] = 1;
            }
			if(isset($postdata['compatibility'])){
    	        $postdata['compatibility'] = implode(" / ",$postdata['compatibility']);
    		}
			$postdata["size"] = ($postdata["size_value"] == 0)? $size : $postdata["size_value"].''.$size;
			$postdata["approval_status"] = $this->getStatusByDescription("PENDING");
			$postdata["updated_by"] = CRUDBooster::myId();
			$this->setSerialFlags($postdata);
	    }
		
	    public function hook_after_edit($id) {
	        //Your code here 
			$item = ItemMasterApproval::where('id', $id)->first();
			$this->sendUpdateNotification($item->upc_code, CRUDBooster::myPrivilegeId());
	    }
		
		public function getEdit($id) {
			$edit_item = ItemMasterApproval::getEditDetails($id)->first();

			Session::put('brand_code'.CRUDBooster::myId(), $edit_item->brand_code);
			Session::put('category_code'.CRUDBooster::myId(), $edit_item->category_code);
			Session::put('model'.CRUDBooster::myId(), $edit_item->model);
			Session::put('model_specific'.CRUDBooster::myId(), $edit_item->model_specific_code);
			Session::put('size_value'.CRUDBooster::myId(), $edit_item->size_value);
			Session::put('size_code'.CRUDBooster::myId(), $edit_item->size_code);
			Session::put('actual_color'.CRUDBooster::myId(), $edit_item->actual_color);

			return parent::getEdit($id);
		}

		public function generateItemCode($category_id, $inventory_type_id, $vendor_type_id) {

			$data=[
				// 'module_id' => $module_id,
				'category_id' => $category_id,
				'inventory_type_id' => $inventory_type_id,
				'vendor_type_id' => $vendor_type_id
			];
			return $this->getDigitsCode($data);
			
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
				'action_types_id'=>$this->getActionByDescription("CREATE"),
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
			$field_names = $this->getItemIdentifier();

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
			$pendingItems = ItemMasterApproval::getPendingItems()
				->where('item_master_approvals.approval_status',$this->getStatusByDescription("PENDING"));

			if(!CRUDBooster::isSuperadmin() && !in_array(CRUDBooster::myPrivilegeName(),["ADMIN","MCB TL"])){
				$pendingItems->where('item_master_approvals.created_by',CRUDBooster::myId());
			}
			$pending_Items = $pendingItems->get();
			$headings = array('UPC Code','Supplier Item Code','Item Description','Brand Description','Category Description','Created By','Created Date');

			Excel::create('Pending Items for Approval-'.date("d M Y - h.i.sa"), function($excel) use ($pending_Items,$headings) {
				$excel->sheet('pending-items', function($sheet) use ($pending_Items,$headings) {
		        	// Set auto size for sheet
					$sheet->setAutoSIZE(true);
					$sheet->setColumnFormat(array(

					));
					
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
	
	}