<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

class ApiModelSpecificsUpdatedController extends \crocodicstudio\crudbooster\controllers\ApiController {

	function __construct() {  
		DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");  
		$this->table       = "model_specifics";        
		$this->permalink   = "model_specifics_updated";    
		$this->method_type = "get";    
	}


	public function hook_before(&$postdata) {
		//This method will be execute before run the main process

	}

	public function hook_query(&$query) {
		//This method is to customize the sql query

	}

	public function hook_after($postdata,&$result) {
		//This method will be execute after run the main process

	}

}