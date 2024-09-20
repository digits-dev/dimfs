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

	
	public function passwordValidation() {
		try {
			$email = trim(request('email'));
			$password = trim(request('password'));
			$defaultPassword = "qwerty";

			Log::info('Password validation called', ['email' => $email]);
	
			$user = User::where('email', $email)->first();
	
			if (!$user) {
				Log::info('User not found.');

				return response()->json([
					'status' => 'error', 
					'message' => 'Invalid Credentials.',
				]);
			}

			if (!Hash::check($password, $user->password)) {
				Log::info('Incorrect Password, Error at CBHook @ passwordValidation line 96');
				
				return response()->json([
					'status' => 'error', 
					'message' => 'Invalid Credentials.',
				]);
			}

			if (Hash::check($defaultPassword, $user->password)) {

				return response()->json([
					'status' => 'success',
					'changePass' => true, 
					'note_message' => "You’re still using the default password, which may leave your account vulnerable. To enhance your account's security, please update your password." 
				]);
			}
	
			$lastUpdatedDate = Carbon::parse($user->updated_password_at); 
			$diffInMonths = $lastUpdatedDate->diffInMonths(now());
			$waiveCount = $user->waive_count;

			if ($diffInMonths >= 3) {

				$data = [
					'status' => 'success',
					'changePass' => true,
					'note_message' => "It has been 90 days since your last password update. To protect your account, please change your password now. Ensure your new password is strong and unique for better security." . 
									  ($waiveCount > 3 ? " Just to inform you, you’ve reached your limit of four waives, so waiving is no longer an option; it’s now required." : "")
				];

				$data['waive'] = $waiveCount <= 3 ? true : false;

				return response()->json($data);
			}
	
			return response()->json([
				'status' => 'success',
				'message' => 'All validations passed.',
			]);
	
		} catch (\Exception $e) {
			Log::error($e->getMessage());
			return response()->json(['error' => 'An error occurred'], 500);
		}
	}
	

    public function changePassword()
    {
        try {
			$waive = request('waive');
			$email = request('email');
			$password = request('password');
			$newPassword = request('new_password');

			$user =  User::where('email', $email)->first();

			if (!Hash::check($password, $user->password)) {
				Log::info('Incorrect Password, Error at CBHook @ changePassword line 146' );

				return response()->json([
					'status' => 'error', 
					'message' => 'Invalid Credentials.',
				]);
            }

			if($waive){

				if (Hash::check($password, $user->password)) {
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
				} else {
					return response()->json(['status' => 'error', 'message' => 'Invalid password.'], 400);
				}

			} else {
			
				$validator = Validator::make(request()->all(), [
					'new_password' => [
						'required',
						'string',
						'min:8',               // Minimum length of 8 characters
						'regex:/[A-Z]/',        // Must contain an uppercase letter
						'regex:/[0-9]/',        // Must contain a number
						'regex:/[\W_]/',        // Must contain a special character
					],
				]);
		
				if ($validator->fails()) {
					Log::error('Errors in Changing Password: ' . $validator->errors());
					return response()->json([ 
						'status' => 'error', 
						'change_pass_form' => true,
						'message' => 'Please make sure to fulfilled the requirement for the password.'
					]);
				}

				$passwordHistories = PasswordHistory::where('user_id', $user->id)->get();

				if($passwordHistories) {
					foreach($passwordHistories as $passwordHistory) {
						if(Hash::check($newPassword, $passwordHistory->password)){
							return response()->json([ 
								'status' => 'error', 
								'change_pass_form' => true,
								'message' => 'The password you submitted has already been used on your account. Please enter a different one.'
							]);
						}
					}
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

				return response()->json([
					'status' => 'success', 
					'message' => 'Your password has been updated successfully! You will be logged in automatically in a moment. If not, please log in manually.',
				]);

			}

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}