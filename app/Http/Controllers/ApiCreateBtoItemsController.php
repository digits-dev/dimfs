<?php namespace App\Http\Controllers;

    use Session;
    use Request;
    use DB;
    use CRUDBooster;
    use App\Counter;

    class ApiCreateBtoItemsController extends \crocodicstudio\crudbooster\controllers\ApiController {

        function __construct() {    
            $this->table       = "item_masters";        
            $this->permalink   = "create_bto_items";    
            $this->method_type = "post";    
        }

        public function hook_before(&$postdata) {
            // Validate the incoming request data
            $validator = \Validator::make($postdata, [
                'upc_code' => 'required|string|max:255',
                'item_description' => 'required|string|max:255',
                'dtp_rf' => 'required',
                'landed_cost' => 'required',
                'purchase_price' => 'required',
                'current_srp' => 'required',
            ]);

            if ($validator->fails()) {
                $response = [
                    'api_status' => 0,
                    'api_message' => $validator->errors()->first()
                ];
                response()->json($response, 400)->send();
                exit;
            }
            Counter::where('id', 1)->increment('code_5');
        
             $postdata['digits_code'] = Counter::where('id', 1)->value('code_5');
        }
        public function hook_query(&$query) {
            // Customize the SQL query if needed
        }

        public function hook_after($postdata,&$result) {
            // This method will be executed after the main process
            $result['api_status'] = 1;
            $result['api_message'] = "Item created successfully";
         
        }
    }