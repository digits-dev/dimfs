@extends('crudbooster::admin_template')

@section('content')

<div id='box_main' class="box box-primary">
    <div class="box-header with-border text-center">
        <h3 class="box-title"><b>Item Master Import Modules</b></h3>       
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                <th scope="col" style="text-align: center">#</th>
                <th scope="col" style="text-align: center">Module</th>
                <th scope="col" style="text-align: center">Description</th>
                <th scope="col" style="text-align: center">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>Item Master bulk import</td>
                    <td>NEW item creation bulk upload (MCB)</td>
                    <td style="text-align: center">
                        <a href="{{ route('importItemView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">NEW Item Import</button></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>SKU Legend/Segmentation bulk import</td>
                    <td>Existing item SKU legend/Segmentation bulk update</td>
                    <td style="text-align: center">
                        <a href="{{ route('importSKULegendView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">SKU Legend/Segmentation Import</button></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">3</th>
                    <td>WRR Date bulk import</td>
                    <td>Existing item WRR date bulk update</td>
                    <td style="text-align: center">
                        <a href="{{ route('importWRRView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">WRR Date Import</button></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">4</th>
                    <td>ECOM Details bulk import</td>
                    <td>Existing item ECOM details bulk update</td>
                    <td style="text-align: center">
                        <a href="{{ route('importECOMView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">ECOM Details Import</button></a>
                    </td>
                </tr>
                <tr>
                    <th scope="row">5</th>
                    <td>Item Master bulk import (Accounting)</td>
                    <td>Existing item master bulk update</td>
                    <td style="text-align: center">
                        <a href="{{ route('importItemAccountingView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">Update Item Import</button></a>
                    </td>
                </tr>

                <tr>
                    <th scope="row">6</th>
                    <td>Item Master bulk import (MCB)</td>
                    <td>Existing item master bulk update</td>
                    <td style="text-align: center">
                        <a href="{{ route('importItemMcbView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">Update Item Import</button></a>
                    </td>
                </tr>

                <tr>
                    <th scope="row">7</th>
                    <td>SKU Status/Segmentation bulk import</td>
                    <td>Existing item SKU status/Segmentation bulk update</td>
                    <td style="text-align: center">
                        <a href="{{ route('importSKULegendView') }}" target="_parent"><button class="btn btn-primary" style="width:80%">SKU Status/Segmentation Import</button></a>
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>

    <div class="box-footer">
            
        <a href="{{ CRUDBooster::mainpath() }}" class='btn btn-default pull-left'>Cancel</a>
        
    </div><!-- /.box-footer-->

    
</div><!-- /.box -->

@endsection
