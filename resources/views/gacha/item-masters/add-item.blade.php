@push('head')
<style>

</style>
@endpush
@extends('crudbooster::admin_template')
@section('content')


<p class="noprint">
    <a title='Return' href="{{ CRUDBooster::mainPath() }}">
        <i class='fa fa-chevron-circle-left '></i> &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}
    </a>
</p>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-pencil"></i><strong> Add {{CRUDBooster::getCurrentModule()->name}}</strong>
    </div>
    <div class="panel-body">
        <h1>Helloo</h1>
    </div>
    <div class="panel-footer">
        <a href='{{ CRUDBooster::mainpath() }}' class='btn btn-default'>Cancel</a>
		<button class="btn btn-primary pull-right _action="save" id="save-btn"><i class="fa fa-save"></i> Save</button>
    </div>
</div>

<script type="application/javascript">

</script>
@endsection