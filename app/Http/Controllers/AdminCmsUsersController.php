<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDbooster;

use App\PasswordHistory;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
		$this->form[] = ["label"=>"First Name","name"=>"first_name","type"=>"text","width" => "col-md-6",'validation'=>'required|alpha_spaces','readonly'=>(CRUDBooster::getCurrentMethod() == "getProfile") ? true : false];
		$this->form[] = ["label"=>"Last Name","name"=>"last_name","type"=>"text","width" => "col-md-6",'validation'=>'required|alpha_spaces','readonly'=>(CRUDBooster::getCurrentMethod() == "getProfile") ? true : false];
		$this->form[] = ["label"=>"Email","name"=>"email",'type'=>'email',"width" => "col-md-6",'validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId(),'readonly'=>(CRUDBooster::getCurrentMethod() == "getProfile") ? true : false];		
		$this->form[] = ["label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px","width" => "col-md-6",'validation'=>'image|max:1000','resize_width'=>90,'resize_height'=>90];											
		$this->form[] = ["label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name","width" => "col-md-6",'required'=>true];						
		// $this->form[] = ["label"=>"Password","name"=>"password","type"=>"password","width" => "col-md-6","help"=>"Please leave empty if not changed"];
		if((CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ADMIN") && (in_array(CRUDBooster::getCurrentMethod(),['getEdit','postEditSave']))){
		    $this->form[] = ["label"=>"Status","name"=>"status","type"=>"select","validation"=>"required","width"=>"col-sm-6","dataenum"=>"ACTIVE;INACTIVE"];
		}
		if(in_array(CRUDBooster::getCurrentMethod(), ['getAdd', 'getEdit','postEditSave', 'getDetail', 'postAddSave'])) {
			$this->form[] = array("label"=>"Password","name"=>"password","type"=>"password",'width'=>'col-sm-4',"help"=>"Please leave empty if not changed");
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

	public function showChangePassword(){
		$data['page_title'] = 'Change Password' ;
		return view('user-account.change-password',$data);
	}

	public function passwordValidation()
	{
		try {
			$email = trim(request('email'));
			$password = trim(request('password'));
			$defaultPassword = "qwerty";
	
			Log::info('Password validation called', ['email' => $email]);
	
			$user = User::where('email', $email)->first();
	
			if (!$user) {
				Log::info('User not found.');
				return $this->errorResponse('Invalid Credentials.');
			}
	
			if (!Hash::check($password, $user->password)) {
				Log::info('Incorrect Password, Error at CBHook @ passwordValidation line 87');
				
				return $this->errorResponse('Invalid Credentials.');
			}
	
			if (Hash::check($defaultPassword, $user->password)) {
				return response()->json([
					'status' => 'success',
					'changePass' => true,
					'note_message' => "You're currently using the default password, which could put your account at risk. Please update your password."
				]);
			}
	
			return $this->checkPasswordUpdateRequirement($user);
	
		} catch (\Exception $e) {
			Log::error($e->getMessage());
			return response()->json(['error' => 'An error occurred'], 500);
		}
	}

	public function changePassword()
	{
		try {
			$waive = request('waive');
			$email = request('email') ?: CRUDBooster::me()->email; 
			$password = request('password');
			$newPassword = request('new_password');
			$changePassInside = request('change_pass_inside');

			$user = User::where('email', $email)->first();

			if (!$user) {
				return response()->json([
					'status' => 'error', 
					'message' => 'Invalid Credentials.',
				]);
			}

			$isCurrPassErr = self::isCurrentPasswordIncorrect($user, $password, $changePassInside);

			if($isCurrPassErr){
				return $isCurrPassErr;
			}

			if ($waive) {
				return $this->handlePasswordWaive($user, $password);
			}

			return $this->handlePasswordChange($user, $newPassword, $changePassInside);
			
		} catch (\Exception $e) {
			Log::error($e->getMessage());
			return response()->json(['error' => 'An error occurred'], 500);
		}
	}
	
	private function errorResponse($message)
	{
		return response()->json([
			'status' => 'error',
			'message' => $message,
		]);
	}

	private function errorFormResponse($message, $name = null, $changePassForm = false)
	{
		Log::info($message);

		return response()->json([
			'status' => 'error',
			'change_pass_form' => $changePassForm,
			'name' => $name,
			'message' => $message,
		]);
	}
	
	private function checkPasswordUpdateRequirement($user)
	{
		$lastUpdatedDate = Carbon::parse($user->updated_password_at);
		$diffInMonths = $lastUpdatedDate->diffInMonths(now());
		$waiveCount = $user->waive_count;
	
		if ($diffInMonths >= 3) {
			$message = "It’s been 90 days since your last password update. To keep your account secure, please change your password now. Make sure your new password is unique." . 
					   ($waiveCount > 3 ? " Just to inform you, you’ve reached your limit of four waives, so waiving is no longer an option; it’s now required." : "");
			
			return response()->json([
				'status' => 'success',
				'changePass' => true,
				'note_message' => $message,
				'waive' => $waiveCount <= 3,
			]);
		}
	
		return response()->json([
			'status' => 'success',
			'message' => 'All validations passed.',
		]);
	}
	
	private function isCurrentPasswordIncorrect($user, $password, $changePassInside)
	{

		if($changePassInside){
			if (!Hash::check($password, $user->password)) {
				Log::info('Incorrect Current Password, Error at CBHook @ changePassword ' );

				return $this->errorFormResponse('Incorrect Current Password.', 'password', $changePassInside);
			}
		} else {
			if (!Hash::check($password, $user->password)) {
				Log::info('Incorrect Password, Error at CBHook @ changePassword ' );

				return response()->json([
					'status' => 'error', 
					'message' => 'Invalid Credentials.123',
				]);
			}
		}
	
	}

	private function handlePasswordWaive($user, $password)
	{
		if (!Hash::check($password, $user->password)) {
			return response()->json([
				'status' => 'error', 
				'message' => 'Invalid Password.',
			]);
		}

		$currWaive = $user->waive_count;

		try {
			$user->update([
				'waive_count' => $currWaive + 1,
				'updated_password_at' => now(),
			]);

			return response()->json([
				'status' => 'success',
				'message' => 'Password waived successfully! You will be logged in automatically in a moment. If not, please log in manually.',
			]);

		} catch (\Exception $e) {
			Log::error($e->getMessage());
			return response()->json(['status' => 'error', 'message' => 'Password Waived Error'], 500);
		}
	}

	private function handlePasswordChange($user, $newPassword, $changePassInside = false)
	{
		$validator = Validator::make(request()->all(), [
			'new_password' => [
				'required',
				'string',
				'min:8',                // Minimum length of 8 characters
				'regex:/[A-Z]/',        // Must contain an uppercase letter
				'regex:/[0-9]/',        // Must contain a number
				'regex:/[\W_]/',        // Must contain a special character
			],
			'confirm_password' => [
				'required',
				'string',
				'same:new_password',  
			],
			
		]);

		if ($validator->fails()) {
			Log::error('Errors in Changing Password: ' . json_encode($validator->errors()));
			return $this->errorFormResponse('Make sure to comply with the password requirements.', 'new_password', true);
		}

		if ($this->isPasswordInHistory($user, $newPassword)) {
			return $this->errorFormResponse("You've already used this password on your account. Please enter a different one.", 'new_password', true);
		}

		$hashedPassword = Hash::make($newPassword);

		$user->update([
			'password' => $hashedPassword,
			'updated_password_at' => now(),
			'waive_count' => 0,
		]);

		PasswordHistory::create([
			'user_id' => $user->id,
			'password' => $hashedPassword,
		]);

		if($changePassInside){
			return response()->json([
				'status' => 'success', 
				'message' => 'Your password has been updated successfully! You will be logged out shortly. Once you are logged out, please log in again to confirm that your updated password is working.',
			]);
		} else {
			return response()->json([
				'status' => 'success', 
				'message' => 'Your password has been updated successfully! You will be logged in automatically in a moment. If not, please log in manually.',
			]);
		}

	}

	private function isPasswordInHistory($user, $newPassword)
	{
		return PasswordHistory::where('user_id', $user->id)
			->get()
			->contains(function ($passwordHistory) use ($newPassword) {
				return Hash::check($newPassword, $passwordHistory->password);
			});
	}
}
