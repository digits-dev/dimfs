<?php 

	namespace App\Http\Controllers;

	use App\ExportPrivilege;
	use App\UserPrivilege;
	use Session;
	use DB;
	use CRUDBooster;
	use Illuminate\Http\Request;

	class AdminExportPrivilegesController extends \crocodicstudio\crudbooster\controllers\CBController {
		
	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "table_name";
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
			$this->button_export = false;
			$this->table = "export_privileges";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Privilege","name"=>"cms_privileges_id","join"=>"cms_privileges,name"];
			$this->col[] = ["label"=>"Module","name"=>"cms_moduls_id","join"=>"cms_moduls,name"];
			$this->col[] = ["label"=>"Table Name","name"=>"table_name"];
			$this->col[] = ["label"=>"Report Header","name"=>"report_header"];
			// $this->col[] = ["label"=>"Report Query","name"=>"report_query"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Privileges','name'=>'cms_privileges_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'cms_privileges,name'];
			$this->form[] = ['label'=>'Modules','name'=>'cms_moduls_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'cms_moduls,name'];
			$this->form[] = ['label'=>'Table Name','name'=>'table_name','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Report Header','name'=>'report_header','type'=>'textarea','validation'=>'required|string|min:5|max:5000','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Report Query','name'=>'report_query','type'=>'textarea','validation'=>'required|string|min:5|max:5000','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Status','name'=>'status','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			// $this->form[] = ['label'=>'Created By','name'=>'created_by','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			// $this->form[] = ['label'=>'Updated By','name'=>'updated_by','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-10'];
			// # END FORM DO NOT REMOVE THIS LINE

	        $this->button_selected = array();
            if(CRUDBooster::isUpdate()) {
	        	$this->button_selected[] = ["label"=>"Set Status ACTIVE","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
				$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
	        }
	                
	        $this->table_row_color = array();     	          
            $this->table_row_color[] = ["condition"=>"[status] == 'INACTIVE'","color"=>"danger"];
	        
	        
	        $this->load_js = array();
	        
	        
	    }
		
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }
		
	    public function hook_before_add(&$postdata) {        
	        //Your code here
            $postdata["created_by"]=CRUDBooster::myId();
			$postdata["created_at"]=date("Y-m-d H:i:s");
	    }
		
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here
            $postdata["updated_by"]=CRUDBooster::myId();
			$postdata["updated_at"]=date("Y-m-d H:i:s");
	    }
		
	    public function hook_before_delete($id) {
	        //Your code here

	    }

		public function getAdd() {
			
            if(!CRUDBooster::isCreate()) CRUDBooster::redirect(CRUDBooster::adminPath(),trans('crudbooster.denied_access'));

            $data = [];
            $data['page_title'] = 'Create User Privilege';
			$data['actionTypes'] = DB::table('action_types')->orderBy('action_type','asc')->get();
            $data['modules'] = DB::table('cms_moduls')->orderBy('name','asc')->get();
            $data['privileges'] = CRUDBooster::isSuperAdmin() ? UserPrivilege::all() : UserPrivilege::privileges();

            return view('export-privilege.create',$data);
        }

		public function getEdit($id){
			if(!CRUDBooster::isUpdate()) CRUDBooster::redirect(CRUDBooster::adminPath(),trans('crudbooster.denied_access'));

			$data = [];
            $data['page_title'] = 'Update User Privilege';
			$data['row'] = ExportPrivilege::find($id);
			$data['actionTypes'] = DB::table('action_types')->orderBy('action_type','asc')->get();
            $data['modules'] = DB::table('cms_moduls')->orderBy('name','asc')->get();
            $data['privileges'] = CRUDBooster::isSuperAdmin() ? UserPrivilege::all() : UserPrivilege::privileges();

            return view('export-privilege.edit',$data);
		}

		public function getTableColumns(Request $request){
            return config('user-export.'.$request->tableName);
		}

		public function saveExport(Request $request) {

            // $request->validate([
            //     'modules' => 'required',
            //     'cms_privileges' => 'required',
            // ]);

            $reportQuery = [];
            $reportHeader = [];

            foreach ($request->table_columns as $key => $value) {
                array_push($reportQuery, $key);
                array_push($reportHeader, $value);
            }

            ExportPrivilege::updateOrCreate([
                'cms_moduls_id'=>$request->modules,
				'action_types_id'=>$request->action_types,
                'cms_privileges_id'=>$request->cms_privileges,
                'table_name'=>$request->table_name
            ],[
                'cms_moduls_id'=>$request->modules,
				'action_types_id'=>$request->action_types,
                'cms_privileges_id'=>$request->cms_privileges,
                'table_name'=>$request->table_name,
                'report_query' => implode("`,`",$reportQuery),
                'report_header' => implode(",",$reportHeader),
				'created_by' => CRUDBooster::myId(),
            ]);

            return redirect(CRUDBooster::mainpath())->with(['message_type' => 'success', 'message' => 'Saved!']);
        }


	}