<?php namespace App\Http\Controllers;

	use Session;
	use DB;
	use CRUDBooster;
	use App\EcomPriceChange;
	use App\Platform;
	use App\ItemMaster;
	use App\PromoType;
	use Illuminate\Support\Facades\Input;
	use Illuminate\Http\Request;
	use Carbon\Carbon;
	use Excel;

	class AdminEcomPriceChangesController extends \crocodicstudio\crudbooster\controllers\CBController {

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
        }

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "digits_code";
			$this->limit = "20";
			$this->orderby = "created_at,desc,digits_code";
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
			$this->button_export = true;
			$this->table = "ecom_price_changes";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			$g_platforms = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"DIGITS CODE","name"=>"digits_code"];
			$this->col[] = ["label"=>"ITEM DESCRIPTION","name"=>"item_masters_id","join"=>"item_masters,item_description"];
			$this->col[] = ["label"=>"BRAND","name"=>"brands_id","join"=>"brands,brand_description"];
			$this->col[] = ["label"=>"PRICE CHANGE","name"=>"price_change"];
			$this->col[] = ["label"=>"FROM DATE","name"=>"from_date"];
			$this->col[] = ["label"=>"FROM TIME","name"=>"from_time"];
			$this->col[] = ["label"=>"TO DATE","name"=>"to_date"];
			$this->col[] = ["label"=>"TO TIME","name"=>"to_time"];
			$this->col[] = ["label"=>"PROMO TYPE","name"=>"promo_types_id","join"=>"promo_types,promo_type_description"];
			$this->col[] = ["label"=>"CAMPAIGN","name"=>"campaign"];
			$this->col[] = ["label"=>"PLATFORM","name"=>"platform"];
			$this->col[] = ["label"=>"STATUS","name"=>"status"];
// 			foreach ($g_platforms as $g_platform) {
// 				$this->col[] = ["label"=>$g_platform->platform_description,"name"=>$g_platform->platform_column];
// 			}
			$this->col[] = ["label"=>"UPDATED BY","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"CREATED DATE","name"=>"created_at"];
			$this->col[] = ["label"=>"UPDATED DATE","name"=>"updated_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			if(in_array(CRUDBooster::getCurrentMethod(),['getAdd','postAddSave'])){
			    $this->form[] = ['label'=>'DIGITS CODE','name'=>'digits_code','type'=>'text','validation'=>'required|min:8|max:8','width'=>'col-sm-6'];
			}
			else{
			    $this->form[] = ['label'=>'DIGITS CODE','name'=>'digits_code','type'=>'text','validation'=>'required|min:8|max:8','width'=>'col-sm-6', 'readonly'=>true];
			}
			$this->form[] = ['label'=>'PRICE CHANGE','name'=>'price_change','type'=>'number','step'=>'0.01', 'validation'=>'required','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'FROM DATE','name'=>'from_date','type'=>'date-custom','validation'=>'required|date','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'FROM TIME','name'=>'from_time','type'=>'time','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'TO DATE','name'=>'to_date','type'=>'date-custom','validation'=>'date','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'TO TIME','name'=>'to_time','type'=>'time','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'CAMPAIGN','name'=>'campaign','type'=>'text','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'PROMO TYPE','name'=>'promo_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6',
				'datatable'=>'promo_types,promo_type_description'
			];
			$this->form[] = ['label'=>'PLATFORM','name'=>'platform','type'=>'radio-custom','validation'=>'required|min:0','width'=>'col-sm-6',
				'datatable'=>'platforms,platform_description',
				'datatable_where'=>"status='ACTIVE'"
			];
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
			if(CRUDBooster::getCurrentMethod() == 'getIndex') {
			    $this->index_button[] = ["title"=>"Export All","label"=>"Export All",'color'=>'info',"icon"=>"fa fa-download","url"=>CRUDBooster::mainpath('export-all').'?'.urldecode(http_build_query(@$_GET))];
			
			    if(CRUDBooster::isSuperadmin() || in_array(CRUDBooster::myPrivilegeName(),['ECOMM STORE MERCH TM','ECOMM STORE MERCH TL','ECOMM STORE MKTG TL','ECOMM STORE MKTG TM'])){
				    $this->index_button[] = ["title"=>"Import Price","label"=>"Import Price",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-price-view')];
			    }
			    
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
	        $this->load_js[] = asset("js/ecom_price_change.js");
	        
	        
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
	        $existingItem = ItemMaster::where('digits_code', $postdata["digits_code"])->first();
	        $postdata["item_masters_id"]= $existingItem->id;
	        $postdata["brands_id"]= $existingItem->brands_id;
            $postdata["updated_by"]=CRUDBooster::myId();
			$this->setPlatformFlags($postdata);
			
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
            $existingData = EcomPriceChange::where('id', $id)->first();
            $promo_price = PromoType::where('id', $existingData->promo_types_id)->first();
            $platforms = Platform::where('status','ACTIVE')->get();
	        
            $change_data = array();
            $change_data[$promo_price->promo_type_column] = $existingData->price_change;
            $change_data['platform'] = $existingData->platform;
            
            foreach($platforms as $platform){
                $column = $platform->platform_column;
                $change_data[$column] = $existingData->$column;
            }
			
			ItemMaster::where('digits_code', $existingData->digits_code)->update($change_data);

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
	        $existingItem = ItemMaster::where('digits_code', $postdata["digits_code"])->first();
	        $postdata["item_masters_id"]= $existingItem->id;
	        $postdata["brands_id"]= $existingItem->brands_id;
            $postdata["updated_by"]=CRUDBooster::myId();
			$this->setPlatformFlags($postdata);
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
	        
	        $existingData = EcomPriceChange::where('id', $id)->first();
            $promo_price = PromoType::where('id', $existingData->promo_types_id)->first();
            $platforms = Platform::where('status','ACTIVE')->get();
	        
            $change_data = array();
            $change_data[$promo_price->promo_type_column] = $existingData->price_change;
            $change_data['platform'] = $existingData->platform;
            
            foreach($platforms as $platform){
                $column = $platform->platform_column;
                $change_data[$column] = $existingData->$column;
            }
			
            ItemMaster::where('digits_code', $existingData->digits_code)->update($change_data);
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

		public function setPlatformFlags(&$postdata) {
			//get all checked form settings
			$platforms = explode(';',$postdata["platform"]);
	
			//get field names with respect to what module name
			$field_names = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();
	
			if(!empty($platforms)){
				//reset field names to 0 flag
				foreach ($field_names as $field_name) {
					$postdata[$field_name->platform_column]=NULL;
				}
				//compare field names
				foreach ($platforms as $platform) {
					foreach ($field_names as $field_name) {
						//make flag 1 if column name is checked
						switch ($platform) {
	
							case $field_name->platform_description:
								$postdata[$field_name->platform_column]=$postdata['price_change'];
								break;
						}
					}
				}
			}
			else{
				$postdata["platform"]=NULL;
			}
		}

		public function importPriceView()
		{
			$data['page_title'] = 'Import Price Change';
	    	return view('ecom-price-change.price-upload',$data);
		}

		public function importBauPriceTemplate()
		{
			$g_platforms = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();

			Excel::create('ecom-price-change-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) use ($g_platforms){
				$excel->sheet('pricing', function ($sheet) use ($g_platforms){
					$header = array('DIGITS CODE','ITEM DESCRIPTION','PRICE CHANGE', 'FROM DATE', 'FROM TIME', 'TO DATE', 'TO TIME', 'TAGGING', 'CAMPAIGN','PLATFORM');
					
					$sheet->row(1, $header);
					foreach ($g_platforms as $key => $value) {
				        $sheet->row($key+2, array('80000001','12S ACC BASELIFT MACBOOK RED','1290.00','yyyy-mm-dd','','','','BAU','',$value->platform_description));
				    }
					
				});
			})->download('csv');
		}
		
		public function importPromoPriceTemplate()
		{
			$g_platforms = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();

			Excel::create('ecom-price-change-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) use ($g_platforms){
				$excel->sheet('pricing', function ($sheet) use ($g_platforms){
					$header = array('DIGITS CODE','ITEM DESCRIPTION','PRICE CHANGE', 'FROM DATE', 'FROM TIME', 'TO DATE', 'TO TIME', 'TAGGING', 'CAMPAIGN','PLATFORM');
					$cnt = count($g_platforms)+2;
					$sheet->row(1, $header);
					foreach ($g_platforms as $key => $value) {
				        $sheet->row($key+2, array('80000001','12S ACC BASELIFT MACBOOK RED','1290.00','yyyy-mm-dd','','yyyy-mm-dd','','MINOR','MINOR CAMPAIGN',$value->platform_description));
				        $sheet->row($key+$cnt, array('80000001','12S ACC BASELIFT MACBOOK RED','1290.00','yyyy-mm-dd','','yyyy-mm-dd','','MEGA','MEGA CAMPAIGN',$value->platform_description));
				        $sheet->row($key+$cnt+4, array('80000001','12S ACC BASELIFT MACBOOK RED','1290.00','yyyy-mm-dd','10:00:00','yyyy-mm-dd','14:00:00','BMO','OTHER CAMPAIGN',$value->platform_description));
				        $sheet->row($key+$cnt+$cnt+2, array('80000001','12S ACC BASELIFT MACBOOK RED','1290.00','yyyy-mm-dd','10:00:00','yyyy-mm-dd','14:00:00','FS','OTHER CAMPAIGN',$value->platform_description));
					}
					
				});
			})->download('csv');
		}

		public function importPrice(Request $request)
		{
			$errors = array();
			$warning_errors = array();
			$cnt_success = 0;
			$cnt_fail = 0;
			$file = $request->file('import_file');
			$uploadType = $request->input('upload_type');
				
			$validator = \Validator::make(
				['file' => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
				['file' => 'required', 'extension' => 'required|in:csv',]
			);

			if ($validator->fails()) {
				return back()->with('error_import', 'Failed ! Please check required file extension.');
			}

			if (Input::hasFile('import_file')) {
				$path = Input::file('import_file')->getRealPath();
				$g_promotypes = PromoType::where('status','ACTIVE')->get();
				$g_platforms = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();
				$csv = array_map('str_getcsv', file($path));
				$dataExcel = Excel::load($path, function($reader) {})->get();
				
				$dataExcelRaw = Excel::load($path, function($reader) {
				    $reader->select(array('digits_code','from_date','from_time','to_date','to_time','tagging','platform'));
				})->get()->toArray();
				
				$uniqueDataExcel = collect($dataExcelRaw)->unique()->values()->all();
				
				if(count($dataExcel) != count($uniqueDataExcel)){
				    return back()->with('error_import', 'Failed ! Please check for duplicate/blank records.');
				}
				
				$data_digits_code = array();
				$data_dates1 = array();
				$data_dates2 = array();
				
				
				foreach($uniqueDataExcel as $key => $value){
				    $line_item = 0;	
					$line_item = $key+1;
					
				    if(!in_array($value['digits_code'], $data_digits_code)){
				      $data_dates1[$value['digits_code']] = [
				            'from_date' => $value['from_date'],
				            'to_date' => $value['to_date'],
				            'from_time' => $value['from_time'],
				            'to_time' => $value['to_time']
				        ];
				        array_push($data_digits_code, $value['digits_code']);
				    }
				        
				    else{
				        $data_dates2[$value['digits_code']] = [
				            'from_date'=>$value['from_date'],
				            'to_date'=>$value['to_date'],
				            'from_time' => $value['from_time'],
				            'to_time' => $value['to_time']
				        ];
				    }
				    
 				    if(key($data_dates1) == key($data_dates2)){
 				        
				        $date2_ftime = $data_dates2[key($data_dates2)]['from_date'].' '.$data_dates2[key($data_dates2)]['from_time'];
				        $date1_ftime = $data_dates1[key($data_dates1)]['from_date'].' '.$data_dates1[key($data_dates1)]['from_time'];
				        $date2_ttime = $data_dates2[key($data_dates2)]['to_date'].' '.$data_dates2[key($data_dates2)]['to_time'];
				        $date1_ttime = $data_dates1[key($data_dates1)]['to_date'].' '.$data_dates1[key($data_dates1)]['to_time'];
				        
				        if(is_null($data_dates2[key($data_dates2)]['from_time']) && $data_dates2[key($data_dates2)]['from_date'] > $data_dates1[key($data_dates1)]['from_date'] && $data_dates2[key($data_dates2)]['from_date'] < $data_dates1[key($data_dates1)]['to_date']){
				                array_push($errors, 'Line '.$line_item.': overlapping dates with current file promos.');
				        }
				        elseif(is_null($data_dates1[key($data_dates1)]['from_time']) && $data_dates1[key($data_dates1)]['from_date'] > $data_dates2[key($data_dates2)]['from_date'] && $data_dates1[key($data_dates1)]['from_date'] < $data_dates2[key($data_dates2)]['to_date']){
				                array_push($errors, 'Line '.$line_item.': overlapping dates with current file promos.');
				        }
				        
				        elseif(!is_null($data_dates2[key($data_dates2)]['from_time']) && $date2_ftime > $date1_ftime && $date2_ftime < $date1_ttime){
				                array_push($errors, 'Line '.$line_item.': overlapping time with current file promos.');
				        }
				        
				        elseif(!is_null($data_dates1[key($data_dates1)]['from_time']) && $date1_ftime > $date2_ftime && $date1_ftime < $date2_ttime){
				                array_push($errors, 'Line '.$line_item.': overlapping time with current file promos.');
				        }
				        
				    }
				    //count($data_dates1)>1 && 
				    else{
				        
				    }
				}
				
				
				$unMatch = [];
				$header = array('DIGITS CODE','ITEM DESCRIPTION','PRICE CHANGE', 'FROM DATE', 'FROM TIME', 'TO DATE', 'TO TIME', 'TAGGING','CAMPAIGN','PLATFORM');

				for ($i=0; $i < sizeof($csv[0]); $i++) {
					if (! in_array($csv[0][$i], $header)) {
						$unMatch[] = $csv[0][$i];
					}
				}

				if(!empty($unMatch)) {
					return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
				}
				
				$dataTagging = array();
				$tagging = array();
				$selectedPlatform = array();
				$dataPlatform = array();
				$splatform = array();
				$duplicate = array();

				foreach ($g_promotypes as $key => $value) {
					array_push($dataTagging, $value->promo_type_description);
					$tagging[$value->promo_type_column] = $value->promo_type_description;
				}
				
				foreach ($g_platforms as $key => $value) {
				    array_push($dataPlatform, $value->platform_description);
					$splatform[$value->platform_column] = $value->platform_description;
				}
				
				// bau
				if($uploadType == "bau" && !empty($dataExcel) && $dataExcel->count()) {
                    
					foreach ($dataExcel as $key => $value) {
					    
                    
						$data = array();
						$line_item = 0;	
						$line_item = $key+1;

						$existingItem = ItemMaster::where('digits_code', $value->digits_code)->first();

						if(is_null($value->digits_code)){
							array_push($errors, 'Line '.$line_item.": digits code can'\t be null/blank.");
						}

						if(empty($existingItem)){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
						}

						if(is_null($value->price_change)){
							array_push($errors, 'Line '.$line_item.": price can'\t be null/blank.");
						}
						
						if($value->tagging !== 'BAU'){
						    array_push($errors, 'Line '.$line_item.': with tag "'.$value->tagging.'" can\'t be uploaded.');
						}
						
				// 		if($value->price_change > $existingItem->current_srp){
				// 			array_push($errors, 'Line '.$line_item.": price change can'\t be greater than current srp.");
				// 		}

						if(is_null($value->from_date)){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has blank from date.');
						}
						
						if(!in_array($value->tagging, $dataTagging)){
							array_push($errors, 'Line '.$line_item.': with tagging "'.$value->tagging.'" not found in submaster.');
						}
						
						if(!in_array($value->platform, $dataPlatform)){
							array_push($errors, 'Line '.$line_item.': with platform "'.$value->platform.'" not found in submaster.');
						}

						$dateFromObj = \DateTime::createFromFormat("Y-m-d", $value->from_date);
						if (!$dateFromObj ){
							array_push($errors, 'Line '.$line_item.': could not parse from date "'.$value->from_date.'".');
						}
						$dateToObj = \DateTime::createFromFormat("Y-m-d", $value->to_date);
						if (!$dateToObj && $value->tagging !== 'BAU'){
							array_push($errors, 'Line '.$line_item.': could not parse to date "'.$value->to_date.'".');
						}

						$promo = array_search ($value->tagging, $tagging);
						$spform = array_search ($value->platform, $splatform);
						
						//check date
						$data[$promo] = $value->price_change;
						$data[$spform] = $value->price_change;
						
						$existingEcomDetail = EcomPriceChange::where([
							'status' => 'ACTIVE',
							'digits_code' => $value->digits_code,
							'price_change' => number_format($value->price_change, 2, '.', ''),
							'from_date' => $value->from_date,
							'platform' => $value->platform
						])->first();
                        
						$oldPromo = EcomPriceChange::where([
							'digits_code' => $value->digits_code,
							'platform' => $value->platform,
						])->where('promo_types_id', 1)
						->where('from_date', $dateFromObj)
						->get();

						$oldPromo1 = EcomPriceChange::where([
							'status' => 'ACTIVE',
							'digits_code' => $value->digits_code,
							'platform' => $value->platform,
						])->where('promo_types_id','!=',1)
						->get();
						
						$oldPromo3 = EcomPriceChange::where([
						    'status' => 'ACTIVE',
							'digits_code' => $value->digits_code,
							'platform' => $value->platform,
						])->where('promo_types_id', 1)
						->get();
						
						if(!empty($oldPromo[0])){
							array_push($errors, 'Line '.$line_item.' can\'t be uploaded existing bau price detected.');
						}

						if(!empty($oldPromo1)){
						    
						    foreach($oldPromo1 as $o_promo1){
						        if(($o_promo1->to_date > $value->from_date || $o_promo1->from_date > $value->from_date) && $value->from_date >= Carbon::now()->toDateString("Y-m-d") ){
						            //accepted
						            if($value->from_date > $o_promo1->from_date && $value->from_date < $o_promo1->to_date){
    						            array_push($errors, 'Line '.$line_item.': existing active promo detected.');
    						        }
						        }
						        
						        else{
						            array_push($errors, 'Line '.$line_item.': existing active promo detected.');
						        }
						    }
							
						}
						
						if(!empty($oldPromo3[0])){
						    
						    foreach($oldPromo3 as $o_promo3){
						        if($value->from_date > $o_promo3->from_date){
						            //accepted
						            
						            EcomPriceChange::where('id', $o_promo3->id)->update(['status'=>'INACTIVE']);
						        }
						        else{
						            array_push($errors, 'Line '.$line_item.': existing latest active bau price detected.');
						        }
						    }
							
						}

						if(!empty($existingEcomDetail[0])){
							array_push($errors, 'Line '.$line_item.': existing entries detected.');
						}
						
						try {
							if(empty($errors)){
								$cnt_success++;
								
								// ItemMaster::where('digits_code', intval($value->digits_code))->update($data, ['platform' => DB::raw('CONCAT(platform,;,'.$value->platform.')') ]);
                                
								unset($data[$promo]);
								$data['platform'] = $value->platform;
								$data['digits_code'] = intval($value->digits_code);
								$data['item_masters_id'] = $existingItem->id;
								$data['brands_id'] = $existingItem->brands_id;
								$data['price_change'] = number_format($value->price_change, 2, '.', '');
								$data['from_date'] = $value->from_date;
								$data['to_date'] = $value->to_date;
								$data['from_time'] = NULL;
								$data['to_time'] = NULL;
								$data['campaign'] = $value->campaign;
								$data['promo_types_id'] = PromoType::where('promo_type_column',$promo)->value('id');
								$data['platforms_id'] = Platform::where('platform_column',$spform)->value('id');
								$data['created_at'] = date('Y-m-d H:i:s');
								$data['updated_by'] = CRUDBooster::myId();
								
								EcomPriceChange::insert($data);
								$selectedPlatform = [];
							}
							
						} catch (\Exception $e) {
							$cnt_fail++;
							array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
						}
					}
				}
				
				// other promo
				elseif($uploadType == "other_promo" && !empty($dataExcel) && $dataExcel->count()) {
                    
					foreach ($dataExcel as $key => $value) {
					    
						$data = array();
						$line_item = 0;	
						$line_item = $key+1;

						$existingItem = ItemMaster::where('digits_code', $value->digits_code)->first();

						if(is_null($value->digits_code)){
							array_push($errors, 'Line '.$line_item.": digits code can'\t be null/blank.");
						}

						if(empty($existingItem)){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
						}
						
						if($value->tagging == 'BAU'){
						    array_push($errors, 'Line '.$line_item.': with tag "'.$value->tagging.'" can\'t be uploaded.');
						}

						if(is_null($value->price_change)){
							array_push($errors, 'Line '.$line_item.": price can'\t be null/blank.");
						}
						
				// 		if($value->price_change > $existingItem->current_srp){
				// 			array_push($errors, 'Line '.$line_item.": price change can'\t be greater than current srp.");
				// 		}

						if(is_null($value->from_date)){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has blank from date.');
						}

						if(is_null($value->to_date)){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has blank to date.');
						}
						
						if(is_null($value->campaign)){
							array_push($errors, 'Line '.$line_item.": campaign can'\t be null/blank.");
						}
						
						if(is_null($value->from_time) && in_array($value->tagging,['BMO','FS'])){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has blank from time.');
						}
						
						if(is_null($value->to_time) && in_array($value->tagging,['BMO','FS'])){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" has blank to time.');
						}

						if(!in_array($value->tagging, $dataTagging)){
							array_push($errors, 'Line '.$line_item.': with tagging "'.$value->tagging.'" not found in submaster.');
						}
						
						if(!in_array($value->platform, $dataPlatform)){
							array_push($errors, 'Line '.$line_item.': with platform "'.$value->platform.'" not found in submaster.');
						}

						$dateFromObj = \DateTime::createFromFormat("Y-m-d", $value->from_date);
						if (!$dateFromObj){
							array_push($errors, 'Line '.$line_item.': could not parse from date "'.$value->from_date.'".');
						}
						$dateToObj = \DateTime::createFromFormat("Y-m-d", $value->to_date);
						if (!$dateToObj){
							array_push($errors, 'Line '.$line_item.': could not parse to date "'.$value->to_date.'".');
						}

						//if to date is lesser than from date
						if($dateFromObj > $dateToObj){
							array_push($errors, 'Line '.$line_item.': with from date "'.$value->from_date.'" can\'t be greater than to date.');
						}

						//if to time is lesser than from time

						$promo = array_search ($value->tagging, $tagging);
						$spform = array_search ($value->platform, $splatform);
						
						//check date
						$data[$promo] = $value->price_change;
						$data[$spform] = $value->price_change;
						
						$existingEcomDetail = EcomPriceChange::where([
							'status' => 'ACTIVE',
							'digits_code' => $value->digits_code,
							'price_change' => number_format($value->price_change, 2, '.', ''),
							'from_date' => $value->from_date,
							'to_date' => $value->to_date,
							'platform' => $value->platform
						])->orderBy('to_date','DESC')->first();
                        
						$oldPromo = EcomPriceChange::where([
							'status' => 'ACTIVE',
							'digits_code' => $value->digits_code,
							'platform' => $value->platform,
						])->where('promo_types_id','!=',1)
						->orderBy('to_date','ASC')
						->get();

						$oldPromo1 = EcomPriceChange::where([
							'status' => 'ACTIVE',
							'digits_code' => $value->digits_code,
							'platform' => $value->platform
						])->where('from_date','>=',$value->from_date)
						->where('to_date','<=',$value->from_date)
						->where('from_time','=>',$value->from_time)
						->orderBy('to_date','DESC')
						->get();
						
						if(!empty($oldPromo)){
						    
						    foreach($oldPromo as $o_promo){
						        //compare from date , and to date
						        if(($value->to_date < $o_promo->from_date && $value->from_date < $o_promo->from_date) || ($value->from_date > $o_promo->to_date && $value->to_date > $o_promo->to_date)){
						            //accepted
						        }
						        if(($value->to_date.' '.$value->to_time < $o_promo->from_date.' '.$o_promo->from_time && $value->from_date.' '.$value->from_time < $o_promo->from_date.' '.$o_promo->from_time) || ($value->from_date.' '.$value->from_time > $o_promo->to_date.' '.$o_promo->to_time && $value->to_date.' '.$value->to_time > $o_promo->to_date.' '.$o_promo->to_time)){
						            
						        }
						        else{
						            array_push($errors, 'Line '.$line_item.': overlapping dates with existing active promos.');
						        }
						    }
							
						}

						if(!empty($oldPromo1[0])){
							array_push($warning_errors, 'Line '.$line_item.': existing active promo will be superseded.');
						}

						if(!empty($existingEcomDetail[0])){
							array_push($errors, 'Line '.$line_item.': existing entries detected.');
						}
						
						$from_time = '00:00:00';
						$to_time = '23:59:59';
						
						
						
						try {
							if(empty($errors)){
								$cnt_success++;
								
								// ItemMaster::where('digits_code', intval($value->digits_code))->update($data, ['platform' => DB::raw('CONCAT(platform,;,'.$value->platform.')') ]);
                                
								unset($data[$promo]);
								$data['platform'] = $value->platform;
								$data['digits_code'] = intval($value->digits_code);
								$data['item_masters_id'] = $existingItem->id;
								$data['brands_id'] = $existingItem->brands_id;
								$data['price_change'] = number_format($value->price_change, 2, '.', '');
								$data['from_date'] = $value->from_date;
								$data['to_date'] = $value->to_date;
								$data['from_time'] = (in_array($value->tagging,['BMO','FS'])) ? $value->from_time : $from_time;
								$data['to_time'] = (in_array($value->tagging,['BMO','FS'])) ? $value->to_time : $to_time;
								$data['campaign'] = $value->campaign;
								$data['promo_types_id'] = PromoType::where('promo_type_column',$promo)->value('id');
								$data['platforms_id'] = Platform::where('platform_column',$spform)->value('id');
								$data['created_at'] = date('Y-m-d H:i:s');
								$data['updated_by'] = CRUDBooster::myId();
								// dd($data);
								EcomPriceChange::insert($data);
								$selectedPlatform = [];
							}
							
						} catch (\Exception $e) {
							$cnt_fail++;
							array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
						}
					}
				}
			}

			if(empty($errors) && empty($warning_errors)){
				return back()->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
			}
			elseif(!empty($warning_errors)){
				return back()->with('warning_import', implode("<br>", $warning_errors));
			}
			else{
				return back()->with('error_import', implode("<br>", $errors));
			}
		}

		public function activatePromoPrice(){
		    EcomPriceChange::where('to_date', '>' ,Carbon::now()->toDateString("Y-m-d"))
			->update(['status' => 'ACTIVE']);
			
			$inactivePromos = EcomPriceChange::where('promo_types_id','!=',1)
			->where('status','INACTIVE')
			->select('digits_code')
			->get()->toArray();
			
			
			if(!empty($inactivePromos)){
			    EcomPriceChange::where('promo_types_id',1)->where('status','INACTIVE')
    			->whereIn('digits_code', $inactivePromos)
    			->update(['status' => 'ACTIVE']);
			}
			
			
		}

		public function upcomingPromoPrice(){
		    EcomPriceChange::where('from_date', '>' ,Carbon::now()->toDateString("Y-m-d"))
			->update(['status' => 'UPCOMING']);
		}
		
		public function deactivatePromoPrice(){
			EcomPriceChange::where('from_date', '<' ,Carbon::now()->toDateString("Y-m-d"))
			// ->orWhere('to_date', '<' ,Carbon::now()->toDateString("Y-m-d"))
			->where('promo_types_id','!=',1)
			->update(['status' => 'INACTIVE']);
			
// 			EcomPriceChange::where('from_date', '<' ,Carbon::now()->toDateString("Y-m-d"))
// 			->where('promo_types_id',1)
// 			->update(['status' => 'INACTIVE']);

			EcomPriceChange::where('to_date', '<' ,Carbon::now()->toDateString("Y-m-d"))
			->whereNotNull('to_time')
			->whereNotNull('to_date')
			->where('to_time', '<' ,Carbon::now()->toTimeString("H:i:s"))
			->update(['status' => 'INACTIVE']);

			EcomPriceChange::where('to_date', '<' ,Carbon::now()->toDateString("Y-m-d"))
			->whereNull('to_time')
			->whereNotNull('to_date')
			->update(['status' => 'INACTIVE']);
			
// 			$duplicate = EcomPriceChange::select('digits_code')->groupBy('digits_code')->having('count(digits_code)', '>', 1)->orderBy('id','ASC')->get()->toArray();
		}
		
		public function exportAllEcomChanges (Request $request) {
            // $platforms = Platform::where('status','ACTIVE')->orderBy('platform_description','ASC')->get();

			self::deactivatePromoPrice();
            self::activatePromoPrice();
            self::upcomingPromoPrice();
			
            $db_con = mysqli_connect(
            	env('DB_HOST'), 
            	env('DB_USERNAME'), 
            	env('DB_PASSWORD'), 
            	env('DB_DATABASE'), 
            	env('DB_PORT')
            );
            
            if(!$db_con) {
            	die('Could not connect: ' . mysqli_error($db_con));
            }
            
            $item_header = array('DIGITS CODE','ITEM DESCRIPTION','BRAND','PRICE CHANGE','FROM DATE','FROM TIME', 'TO DATE', 'TO TIME','PROMO TYPE','CAMPAIGN','PLATFORM',);
            
            // foreach ($platforms as $platform) {
            // 	array_push($item_header, $platform->platform_description);
            // }
            
            array_push($item_header,'STATUS','UPDATED BY','CREATED DATE','UPDATED DATE');
            
            $filename = "Export ECOM Price Change - ".date("Ymd H:i:s"). ".csv";
            
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: text/csv; charset=UTF-16LE");
            
            $out = fopen("php://output", 'w');
            $flag = false;
            
            
            $sql_query = "SELECT `ecom_price_changes`.digits_code, 
                `item_masters`.item_description , 
                `brands`.brand_description, 
                `ecom_price_changes`.price_change,
                `ecom_price_changes`.from_date,
                `ecom_price_changes`.from_time,
                `ecom_price_changes`.to_date,
                `ecom_price_changes`.to_time,
                `promo_types`.promo_type_description,
                `ecom_price_changes`.campaign,`ecom_price_changes`.platform,";
            
            // foreach ($platforms as $platform) {
            // 	$sql_query .="`ecom_price_changes`.".$platform->platform_column.",";
            // }
			
			$sql_query .="`ecom_price_changes`.status,`cms_users`.name, `ecom_price_changes`.created_at,`ecom_price_changes`.updated_at FROM `ecom_price_changes`
			    LEFT JOIN `promo_types` ON `ecom_price_changes`.promo_types_id = `promo_types`.id
			    LEFT JOIN `item_masters` ON `ecom_price_changes`.item_masters_id = `item_masters`.id
			    LEFT JOIN `brands` ON `ecom_price_changes`.brands_id = `brands`.id
			    LEFT JOIN `cms_users` ON `ecom_price_changes`.updated_by = `cms_users`.id
			    ORDER BY `ecom_price_changes`.created_at DESC";
            
            $resultset = mysqli_query($db_con, $sql_query) or die("Database Error:". mysqli_error($db_con));
            
            while($row = mysqli_fetch_row($resultset)) {
            	if(!$flag) {
            	// display field/column names as first row
            	fputcsv($out, $item_header, ',', '"');
            	$flag = true;
            	}
            	$cnt_array = 0;
            	array_walk($row, 
            		function(&$str, $key) {
            		    
            			if($str == 't') $str = 'TRUE';
            			if($str == 'f') $str = 'FALSE';
            			if(in_array($key, [0])){
            			    $str ="='$str'";
            			}
            			if(strstr($str, '"')) {
            				$str = '"' . str_replace('"', '""', $str) . '"';
            			}
            			if(strstr($str, "'")) {
            				$str = str_replace("'", '"', $str);
            			}
            			$str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
            		}
            	);
            	
            	fputcsv($out, array_values($row), ',', '"');
            }
            
            fclose($out);
            exit;
		}

	}