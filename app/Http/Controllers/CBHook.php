<?php 
namespace App\Http\Controllers;

use DB;
use Illuminate\Support\Facades\Hash;
use Session;
use Request;

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
			"editform_views" => "CREATE READONLY",
			"add_readonly" => "UPDATE",
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
}