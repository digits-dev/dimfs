<?php 
namespace App\Http\Controllers;

use App\PasswordHistory;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Session;
use Request;
use CRUDBooster;

class CBHook extends Controller {

	/*
	| --------------------------------------
	| Please note that you should re-login to see the session work
	| --------------------------------------
	|
	*/
	public function afterLogin() {
		$users = DB::table(config('crudbooster.USER_TABLE'))->where("email", request('email'))->first();
		
        if (Hash::check(request('password'), $users->password)) {
            if($users->status == 'INACTIVE'){
                Session::flush();
                return redirect()->route('getLogin')->with('message', 'The user does not exist!');
            }
        }
		
		$moduls_id = DB::table('cms_moduls')->where('table_name','item_masters')->value('id');

		$actionTypes = [
			"addform_views" => "CREATE",
			"add_readonly" => "CREATE READONLY",
			"editform_views" => "UPDATE",
			"edit_readonly" => "UPDATE READONLY"
		];

		$column_views = DB::table('view_settings')->where([
			'cms_privileges_id' => $users->id_cms_privileges,
			'cms_moduls_id' => $moduls_id
		])->get();

		Session::put('column_views', $column_views);

		foreach ($actionTypes as $key => $value) {
			$access = DB::table('form_settings')->where([
				'cms_privileges_id' => $users->id_cms_privileges,
				'cms_moduls_id' => $moduls_id,
				'action_types_id' => self::getActionType($value)
			])->get();

			Session::put($key, $access);
		}
		
		
	}

	public function getActionType($actionType){
		return DB::table('action_types')->where('action_type',$actionType)->value('id');
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