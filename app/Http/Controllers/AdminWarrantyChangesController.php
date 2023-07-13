<?php namespace App\Http\Controllers;

	use Session;
	use DB;
	use CRUDBooster;
	use App\Warranty;
	use App\ItemMaster;
	use App\WarrantyChange;
	use Illuminate\Support\Facades\Input;
	use Illuminate\Http\Request;
	use Excel;

	class AdminWarrantyChangesController extends \crocodicstudio\crudbooster\controllers\CBController {

        public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
        }

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "digits_code";
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
			$this->button_export = true;
			$this->table = "warranty_changes";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"DIGITS CODE","name"=>"digits_code"];
			$this->col[] = ["label"=>"WARRANTY DURATION","name"=>"warranty_duration"];
			$this->col[] = ["label"=>"WARRANTY","name"=>"warranties_id","join"=>"warranties,warranty_description"];
			$this->col[] = ["label"=>"UPDATED BY","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"CREATED DATE","name"=>"created_at"];
			$this->col[] = ["label"=>"UPDATED DATE","name"=>"updated_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'DIGITS CODE','name'=>'digits_code','type'=>'text','validation'=>'required|min:8|max:8','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'WARRANTY DURATION','name'=>'warranty_duration','type'=>'number','validation'=>'required|integer|min:0','width'=>'col-sm-6'];
			$this->form[] = ['label'=>'WARRANTY','name'=>'warranties_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-6','datatable'=>'warranties,warranty_description'];
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
				$this->index_button[] = ["title"=>"Import Warranty","label"=>"Import Warranty",'color'=>'info',"icon"=>"fa fa-upload","url"=>CRUDBooster::mainpath('import-warranty-view')];
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
	        $this->load_js[] = asset("js/warranty_change.js");
	        
	        
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

		public function importWarrantyView()
		{
			$data['page_title'] = 'Import Warranty Change';
	    	return view('warranty-change.warranty-upload',$data);
		}

		public function importWarrantyTemplate()
		{
			Excel::create('warranty-change-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
				$excel->sheet('pricing', function ($sheet) {
					$header = array('DIGITS CODE','WARRANTY DURATION','WARRANTY');
					
					$sheet->row(1, $header);
					$sheet->row(2, array('80000001','1','YEAR'));
				});
			})->download('csv');
		}

		public function importWarranty(Request $request)
		{
			$errors = array();
			$cnt_success = 0;
			$cnt_fail = 0;
			$file = $request->file('import_file');
				
			$validator = \Validator::make(
				['file' => $file, 'extension' => strtolower($file->getClientOriginalExtension()),],
				['file' => 'required', 'extension' => 'required|in:csv',]
			);

			if ($validator->fails()) {
				return back()->with('error_import', 'Failed ! Please check required file extension.');
			}

			if (Input::hasFile('import_file')) {
				$path = Input::file('import_file')->getRealPath();
				$csv = array_map('str_getcsv', file($path));
				$dataExcel = Excel::load($path, function($reader) {})->get();
				
				$unMatch = [];
				$header = array('DIGITS CODE','WARRANTY DURATION','WARRANTY');
				
				for ($i=0; $i < sizeof($csv[0]); $i++) {
					if (! in_array($csv[0][$i], $header)) {
						$unMatch[] = $csv[0][$i];
					}
				}

				if(!empty($unMatch)) {
					return back()->with('error_import', 'Failed ! Please check template headers, mismatched detected.');
				}
				
				if(!empty($dataExcel) && $dataExcel->count()) {

					foreach ($dataExcel as $key => $value) {
						$data = array();
						$line_item = 0;	
						$line_item = $key+1;

						$existingItem = ItemMaster::where('digits_code', $value->digits_code)->first();
						$warranty_id = Warranty::where('warranty_description', $value->warranty)->first();

						if(is_null($value->digits_code)){
							array_push($errors, 'Line '.$line_item.": digits code can'\t be null/blank.");
						}

						if(empty($existingItem)){
							array_push($errors, 'Line '.$line_item.': with digits code "'.$value->digits_code.'" not found in item master.');
						}

						if(empty($warranty_id)){
							array_push($errors, 'Line '.$line_item.': with warranty "'.$value->warranty.'" not found in submaster.');
						}

						$data = [
							'warranty_duration' => intval($value->warranty_duration),
							'warranties_id' => $warranty_id->id
						];
						
						try {
							if(empty($errors)){
								$cnt_success++;
								ItemMaster::where('digits_code', intval($value->digits_code))->update($data);
								$data['digits_code'] = intval($value->digits_code);
								$data['created_at'] = date('Y-m-d H:i:s');
								$data['updated_by'] = CRUDBooster::myId();
								WarrantyChange::insert($data);
							}
							
						} catch (\Exception $e) {
							$cnt_fail++;
							array_push($errors, 'Line '.$line_item.': with error '.$e->errorInfo[2]);
						}
					}
				}
			}

			if(empty($errors)){
				return back()->with('success_import', 'Success ! ' . $cnt_success . ' item(s) were updated successfully.');
			}
			else{
				return back()->with('error_import', implode("<br>", $errors));
			}
		}


	}