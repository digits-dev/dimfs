@extends('crudbooster::admin_template')
@section('content')

<div id='box_main' class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Upload a File</h3>
        <div class="box-tools"></div>
    </div>

    @if ($message = Session::get('success_import'))
    <div class="alert alert-success" role="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ Session::get('success_import') }}
    </div>
    @endif 
    @if ($message = Session::get('error_import'))
    <div class="alert alert-danger" role="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        Errors Found !<br>
            {!! Session::get('error_import') !!}
    </div>
    @endif
    
    @if ($message = Session::get('warning_import'))
    <div class="alert alert-warning" role="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        Errors Found !<br>
            {!! Session::get('warning_import') !!}
    </div>
    @endif

    <form method='post' id="form" enctype="multipart/form-data" action="{{ route('upload.price') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="box-body">

            <div class='callout callout-success'>
                <h4>Welcome to Data Importer Tool</h4>
                Before uploading a file, please read below instructions : <br/>
                1. Do not upload items not existing in IMFS<br/>
                2. Date format should be "YYYY-mm-dd" e.g. 2020-12-31<br/>
                3. File format should be : CSV file format<br/>
            </div>

            <label class='col-sm-2 control-label'>Import Template File: </label>
            <div class='col-sm-4'>
                @if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ECOM (PRODUCTS I)")
                <a href="{{ route('upload.bau-price-template') }}" class="btn btn-primary" role="button">Download Bau Template</a>
                @endif
                @if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ECOM (MARKETING)")
                <a href="{{ route('upload.promo-price-template') }}" class="btn btn-primary" role="button">Download Promo Template</a>
                @endif
            </div>
            
            <div class='col-sm-6 col-sm-offset-6'>
            </div>
            
            <br>
            <br>
                
            <label class='col-sm-2 control-label'>Upload Type: </label>
            <div class='col-sm-4'>
                <select class="form-control select2" style="width: 100%;" required name="upload_type" id="upload_type">
                    <option value="">Select Upload Type</option>
                    @if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ECOM (PRODUCTS I)")
                    <option selected value="bau">BAU</option>
                    @endif
                    @if(CRUDBooster::isSuperadmin() || CRUDBooster::myPrivilegeName() == "ECOM (MARKETING)")
                    <option value="other_promo">OTHER PROMO</option>
                    @endif
                </select>
            </div>
            
            <div class='col-sm-6 col-sm-offset-6'>
            </div>
            
            <br>
            <br>
            
            <label for='import_file' class='col-sm-2 control-label'>File to Import: </label>
            <div class='col-sm-4'>
                <input type='file' name='import_file' class='form-control' required accept=".csv"/>
                <div class='help-block'>File type supported only : CSV</div>
            </div>

        </div><!-- /.box-body -->

        <div class="box-footer">
            <a href="{{ CRUDBooster::mainpath() }}" class='btn btn-default pull-left'>Cancel</a>
            <button class="btn btn-primary pull-right" type="submit" id="btnSubmit"> <i class="fa fa-save" ></i> Upload</button>
        </div><!-- /.box-footer-->
    </form>
</div><!-- /.box -->

@endsection

@push('bottom')
<script type="text/javascript">
$(document).ready(function() {
    $("#btnSubmit").click(function() {
        $(this).prop("disabled", true);
        $("#form").submit();
    });
});
</script>
@endpush