<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use Excel;
	use App\ActionType;
	use App\StatusState;
	use App\ItemPriceChangeApproval;
	use App\ItemMaster;

	class AdminItemPriceChangeApprovalsController extends \crocodicstudio\crudbooster\controllers\CBController {
        
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
			$this->title_field = "item_masters_id";
			$this->limit = "20";
			$this->orderby = "id,desc";
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
			$this->table = "item_price_change_approvals";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"APPROVAL STATUS","name"=>"approval_status","join"=>"status_states,status_state"];
			$this->col[] = ["label"=>"DIGITS CODE","name"=>"item_masters_id","join"=>"item_masters,digits_code"];
			$this->col[] = ["label"=>"BRAND DESCRIPTION","name"=>"brands_id","join"=>"brands,brand_description"];
			$this->col[] = ["label"=>"CATEGORY DESCRIPTION","name"=>"categories_id","join"=>"categories,category_description"];
			$this->col[] = ["label"=>"MARGIN CATEGORY DESCRIPTION","name"=>"margin_categories_id","join"=>"margin_categories,margin_category_description"];
			$this->col[] = ["label"=>"STORE COST","name"=>"store_cost","callback_php"=>'number_format($row->store_cost,2)'];
			$this->col[] = ["label"=>"STORE MARGIN (%)","name"=>"store_cost_percentage","callback_php"=>'number_format($row->store_cost_percentage,4)'];
			
			// Edited by Lewie
			$this->col[] = ["label"=>"ECOMM - STORE COST","name"=>"ecom_store_margin","callback_php"=>'number_format($row->ecom_store_margin,2)'];
			$this->col[] = ["label"=>"ECOMM - STORE MARGIN (%)","name"=>"ecom_store_margin_percentage","callback_php"=>'number_format($row->ecom_store_margin_percentage,4)'];
			
            $this->col[] = ["label"=>"LANDED COST","name"=>"landed_cost","callback_php"=>'number_format($row->landed_cost,2)'];
			$this->col[] = ["label"=>"LANDED COST VIA SEA","name"=>"landed_cost_sea","callback_php"=>'number_format($row->landed_cost_sea,2)'];
			$this->col[] = ["label"=>"ACTUAL LANDED COST","name"=>"actual_landed_cost","callback_php"=>'number_format($row->actual_landed_cost,2)'];
			$this->col[] = ["label"=>"WORKING STORE COST","name"=>"working_store_cost","callback_php"=>'number_format($row->working_store_cost,2)'];
			$this->col[] = ["label"=>"WORKING STORE MARGIN (%)","name"=>"working_store_cost_percentage","callback_php"=>'number_format($row->working_store_cost_percentage,2)'];
			// Edited by Mike 20220207
			$this->col[] = ["label"=>"ECOMM - WORKING STORE COST","name"=>"working_ecom_store_margin","callback_php"=>'number_format($row->working_ecom_store_margin,2)'];
			$this->col[] = ["label"=>"ECOMM - WORKING STORE MARGIN (%)","name"=>"working_ecom_store_margin_percentage","callback_php"=>'number_format($row->working_ecom_store_margin_percentage,4)'];
			// END Edited by Mike 20220207
			$this->col[] = ["label"=>"WORKING LANDED COST","name"=>"working_landed_cost","callback_php"=>'number_format($row->working_landed_cost,2)'];
			$this->col[] = ["label"=>"EFFECTIVE DATE","name"=>"effective_date"];
			$this->col[] = ["label"=>"DURATION FROM","name"=>"duration_from"];
			$this->col[] = ["label"=>"DURATION TO","name"=>"duration_to"];
			$this->col[] = ["label"=>"SUPPORT TYPE","name"=>"support_types_id","join"=>"support_types,support_type_description"];
			$this->col[] = ["label"=>"UPDATED BY","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"UPDATED DATE","name"=>"updated_at"];
			
            # END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'DIGITS CODE','name'=>'digits_code','type'=>'text','validation'=>'required|integer|min:0','width'=>'col-sm-6','readonly'=>true];
			$this->form[] = ['label'=>'BRAND DESCRIPTION','name'=>'brands_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'brands,brand_description','readonly'=>true];
			$this->form[] = ['label'=>'CATEGORY DESCRIPTION','name'=>'categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'categories,category_description','readonly'=>true];
			$this->form[] = ['label'=>'MARGIN CATEGORY DESCRIPTION','name'=>'margin_categories_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'margin_categories,margin_category_description','readonly'=>true];
			$this->form[] = ['label'=>'MARGIN CATEGORY','name'=>'margin_category','type'=>'hidden','value'=>''];
			$this->form[] = ['label'=>'CURRENT SRP','name'=>'current_srp','type'=>'number','validation'=>'required','width'=>'col-sm-6','readonly'=>true];
			$this->form[] = ['label'=>'DG SRP','name'=>'promo_srp','type'=>'number','validation'=>'required','width'=>'col-sm-6','readonly'=>true];
			$this->form[] = ['label'=>'STORE COST','name'=>'store_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'STORE MARGIN (%)','name'=>'store_cost_percentage','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'ECOMM - STORE COST','name'=>'ecom_store_margin','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'ECOMM - STORE MARGIN %','name'=>'ecom_store_margin_percentage','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
	        $this->form[] = ['label'=>'LANDED COST','name'=>'landed_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'ACTUAL LANDED COST','name'=>'actual_landed_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'WORKING STORE COST','name'=>'working_store_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'WORKING STORE MARGIN %','name'=>'working_store_cost_percentage','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'ECOMM - WORKING STORE COST','name'=>'working_ecom_store_margin','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'ECOMM - WORKING STORE MARGIN %','name'=>'working_ecom_store_margin_percentage','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'WORKING LANDED COST','name'=>'working_landed_cost','type'=>'number','validation'=>'required','width'=>'col-sm-6'];
		    $this->form[] = ['label'=>'DURATION FROM','name'=>'duration_from','type'=>'number','width'=>'col-sm-6','disabled'=>true];
			$this->form[] = ['label'=>'DURATION TO','name'=>'duration_to','type'=>'number','width'=>'col-sm-6','disabled'=>true];
			$this->form[] = ['label'=>'SUPPORT TYPE','name'=>'support_types_id','type'=>'select2','width'=>'col-sm-6','datatable'=>'margin_categories,margin_category_description','readonly'=>true];
		    
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
            if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ACCTG HEAD") { //if approver
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
				// $this->index_button[] = ['label'=>'Export Pending Items','title'=>'Export Pending Items','color'=>'warning','url'=>CRUDBooster::mainpath('export-pending'),'icon'=>'fa fa-upload'];
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
	        $items = ItemPriceChangeApproval::whereIn('id',$id_selected)->get();
	        
	        if($button_name == 'approve'){
                ItemPriceChangeApproval::whereIn('id',$id_selected)->update([
                    'approval_status' => $this->approved,
                    'approved_by' => CRUDBooster::myId(),
                    'approved_at' => date('Y-m-d H:i:s')
                ]);
                
                // foreach($items as $item){
                //     ItemMaster::where('id', $item->item_masters_id)->update([
                //         'dtp_rf' 				    => $item->store_cost,
                //         'dtp_rf_percentage' 	    => $item->store_cost_percentage,
                //         'working_dtp_rf' 			=> $item->working_store_cost,
                //         'working_dtp_rf_percentage' => $item->working_store_cost_percentage,
                //         'ecom_store_margin' 				    => $item->ecom_store_margin,
                //         'ecom_store_margin_percentage' 	        => $item->ecom_store_margin_percentage,
                //         'working_ecom_store_margin' 			=> $item->working_ecom_store_margin,
                //         'working_ecom_store_margin_percentage'  => $item->working_ecom_store_margin_percentage,
                //         'landed_cost' 				=> $item->landed_cost,
                //         'landed_cost_sea' 			=> $item->landed_cost_sea,
                //         'actual_landed_cost' 		=> $item->actual_landed_cost,
                //         'working_landed_cost'		=> $item->working_landed_cost,
                //         'updated_by'                => CRUDBooster::myId(),
                //         'updated_at'                => date('Y-m-d H:i:s')
                //     ]);
                //     $itemCode = ItemMaster::where('id', $item->item_masters_id)->value('digits_code');
                //     $this->sendApprovedNotification($itemCode, $item->updated_by);
                // }
	        }   
	        else if($button_name == 'reject'){
	            ItemPriceChangeApproval::whereIn('id',$id_selected)->update([
	                'approval_status' => $this->rejected
	            ]);
	            foreach($items as $item){
	                $itemCode = ItemMaster::where('id', $item->item_masters_id)->value('digits_code');
	                $this->sendRejectedNotification($itemCode, $item->id, $item->updated_by);
	            }
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
	        if(in_array(CRUDBooster::myPrivilegeName(),["ADMIN","ACCTG HEAD"]))
				$query->where('item_price_change_approvals.approval_status',$this->pending); 
			elseif(!CRUDBooster::isSuperadmin()) {
				$query->where([
					'item_price_change_approvals.updated_by'=>CRUDBooster::myId(),
					'item_price_change_approvals.approval_status'=>$this->rejected
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
        
        public function sendApprovedNotification($item_code,$encoder_id) {
			//send notification to encoder
			$config['content'] = CRUDBooster::myName(). " has approved your item with DIGITS CODE: ".$item_code." at Item Master Module!";
			$config['to'] = CRUDBooster::adminPath('item_masters?q='.$item_code);
			$config['id_cms_users'] = [$encoder_id];
			CRUDBooster::sendNotification($config);
		}

		public function sendRejectedNotification($item_code,$item_id,$encoder_id) {
			//send notification to encoder
			$config['content'] = CRUDBooster::myName(). " has rejected your item with DIGITS CODE: ".$item_code." at Accounting Approval Module!";
			$config['to'] = CRUDBooster::adminPath('item_price_change_approvals/edit/'.$item_id);
			$config['id_cms_users'] = [$encoder_id];
			CRUDBooster::sendNotification($config);
		}


	}