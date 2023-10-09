<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Excel;
use CRUDBooster;

class GachaponItemMasterImportController extends Controller
{
    public function __construct() {
		  DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping("enum", "string");
    }

    public function importItems(Request $request){

    }

    public function importItemTemplate(){
      Excel::create('gachapon-item-'.date("Ymd").'-'.date("h.i.sa"), function ($excel) {
        $excel->sheet('item', function ($sheet) {
          $sheet->row(1, array(
            "ITEM NUMBER",
            "BRAND DESCRIPTION",
            "SKU STATUS",
            "ITEM DESCRIPTION",
            "MODEL",
            "WH CATEGORY DESCRIPTION",
            "MSRP",
            "CURRENT SRP",
            "NUMBER OF TOKENS",
            "DP PER CTN",
            "PCS PER DP",
            "MOQ",
            "NUMBER OF ASSORT",
            "COUNTRY OF ORIGIN",
            "INCOTERMS",
            "CURRENCY",
            "SUPPLIER COST",
            "UOM CODE",
            "INVENTORY TYPE",
            "VENDOR TYPE",
            "VENDOR GROUP NAME",
            "AGE GRADE",
            "BATTERY"
          ));
        });
      })->download('csv');
    }
}
