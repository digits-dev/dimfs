<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDbooster;

class AdminCmsUsersController extends \crocodicstudio\crudbooster\controllers\CBController {

	public function __construct()
	{
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
	}

	public function cbInit() {
		
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'cms_users';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';	
		$this->button_import 	   = FALSE;	
		$this->button_export 	   = TRUE;	
		# END CONFIGURATION DO NOT REMOVE THIS LINE
	
		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = ["label"=>"Full Name","name"=>"name"];
		$this->col[] = ["label"=>"Email","name"=>"email"];
		$this->col[] = ["label"=>"Privilege","name"=>"id_cms_privileges","join"=>"cms_privileges,name"];
		$this->col[] = ["label"=>"Photo","name"=>"photo","image"=>1];
		$this->col[] = ["label"=>"Status","name"=>"status"];	
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array(); 		
		$this->form[] = ["label"=>"First Name","name"=>"first_name","type"=>"text","width" => "col-md-6",'validation'=>'required|alpha_spaces|min:3','readonly'=>(CRUDBooster::getCurrentMethod() == "getProfile") ? true : false];
		$this->form[] = ["label"=>"Last Name","name"=>"last_name","type"=>"text","width" => "col-md-6",'validation'=>'required|alpha_spaces|min:3','readonly'=>(CRUDBooster::getCurrentMethod() == "getProfile") ? true : false];
		$this->form[] = ["label"=>"Email","name"=>"email",'type'=>'email',"width" => "col-md-6",'validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId(),'readonly'=>(CRUDBooster::getCurrentMethod() == "getProfile") ? true : false];		
		$this->form[] = ["label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px","width" => "col-md-6",'validation'=>'image|max:1000','resize_width'=>90,'resize_height'=>90];											
		$this->form[] = ["label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name","width" => "col-md-6",'required'=>true];						
		$this->form[] = ["label"=>"Password","name"=>"password","type"=>"password","width" => "col-md-6","help"=>"Please leave empty if not changed"];
		if((CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADMIN") && (in_array(CRUDBooster::getCurrentMethod(),['getEdit','postEditSave']))){
		    $this->form[] = ["label"=>"Status","name"=>"status","type"=>"select","validation"=>"required","width"=>"col-sm-6","dataenum"=>"ACTIVE;INACTIVE"];
		}
		# END FORM DO NOT REMOVE THIS LINE
		
		$this->button_selected = array();
		if(CRUDBooster::isUpdate()) {
			$this->button_selected[] = ["label"=>"Set Status ACTIVE ","icon"=>"fa fa-check-circle","name"=>"set_status_ACTIVE"];
			$this->button_selected[] = ["label"=>"Set Status INACTIVE","icon"=>"fa fa-times-circle","name"=>"set_status_INACTIVE"];
			$this->button_selected[] = ["label"=>"Reset Password","icon"=>"fa fa-refresh","name"=>"reset_password"];
		}
	}

	public function getProfile() {

		$this->button_addmore = FALSE;
		$this->button_cancel  = FALSE;
		$this->button_show    = FALSE;			
		$this->button_add     = FALSE;
		$this->button_delete  = FALSE;	
		$this->hide_form 	  = ['id_cms_privileges','status'];

		$data['page_title'] = trans("crudbooster.label_button_profile");
		$data['row'] = CRUDBooster::first('cms_users',CRUDBooster::myId());		
		$this->cbView('crudbooster::default.form',$data);				
	}

	public function getEdit($id) {
        if (!CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADMIN") {
            CRUDBooster::redirectBack(trans("crudbooster.denied_access"));
        }
        return parent::getEdit($id);
    }

	public function actionButtonSelected($id_selected,$button_name) {
		//Your code here
		switch ($button_name) {
			case 'set_status_ACTIVE':
				DB::table('cms_users')->whereIn('id',$id_selected)->update([
					'status'=>'ACTIVE', 
					'updated_at' => date('Y-m-d H:i:s')
				]);
				break;
			case 'set_status_INACTIVE':
				DB::table('cms_users')->whereIn('id',$id_selected)->update([
					'status'=>'INACTIVE', 
					'updated_at' => date('Y-m-d H:i:s')
				]);
				break;
			case 'reset_password':
				DB::table('cms_users')->whereIn('id',$id_selected)->update([
					'password'=>bcrypt('qwerty'),
					'updated_at' => date('Y-m-d H:i:s')
				]);
				break;
			default:
				# code...
				break;
		}    
	}

	public function hook_query_index(&$query) {
        //Your code here
        if(!CRUDBooster::isSuperadmin()) {
        	if(CRUDBooster::myPrivilegeName() == 'ADMIN'){
        		$query->where('cms_users.id_cms_privileges','!=','1');
        	}
        	else{
        		$query->where('cms_users.id',CRUDBooster::myId());
        	}
        }    
	}
	
	public function hook_before_add(&$postdata) {
		//Your code here
		$postdata["created_by"]=CRUDBooster::myId();
		$postdata['name'] = $postdata['first_name'].' '.$postdata['last_name'];
		$postdata["photo"] = (empty($postdata["photo"])) ? "uploads/avatar/avatar.png" : $postdata["photo"];
		$postdata["status"] = "ACTIVE";
	}

	public function hook_before_edit(&$postdata,$id) {
		//Your code here
		if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADMIN") {
            $postdata['name'] = $postdata['first_name'].' '.$postdata['last_name'];
		}
		$postdata["updated_by"]=CRUDBooster::myId();
	}
}
