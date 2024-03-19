@push('head')
<style>
   
    table, tbody, td, th {
        border: 1px solid black !important;
    }
    .table-container {
        display: flex;
        justify-content: center;
    }

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
        <i class="fa fa-pencil"></i><strong> Detail {{CRUDBooster::getCurrentModule()->name}}</strong>
    </div>
    <div class="panel-body">
            <h3 class="text-center text-bold">ITEM DETAILS</h3>
            <div class="row table-container">
                <div class="col-md-8 offset-md-4">
                    <table class="table-responsive table">
                        <tbody>
                            <tr>
                                <th colspan="2">Digits Code</th>
                                <td colspan="2">{{ $item->digits_code }}</td>
                            </tr>
                           <tr>
                                <th>Old LC per Carton</th>
                                <td>{{ $item->old_lc_per_carton }}</td>
                                <th>New LC per Carton</th>
                                <td>{{ $item->new_lc_per_carton }}</td>
                           </tr>
                           <tr>
                                <th>Old LC per PC</th>
                                <td>{{ $item->old_lc_per_pc }}</td>
                                <th>New LC per PC</th>
                                <td>{{ $item->new_lc_per_pc }}</td>
                            </tr>
                           <tr>
                                <th>Old LC Margin per PC</th>
                                <td>{{ $item->old_lc_margin_per_pc }}</td>
                                <th>New LC Margin per PC</th>
                                <td>{{ $item->new_lc_margin_per_pc }}</td>
                            </tr>
                            <tr>
                                <th>Old SC per PC</th>
                                <td>{{ $item->old_sc_per_pc }}</td>
                                <th>New SC per PC</></th>
                                <td>{{ $item->new_sc_per_pc }}</td>
                            </tr>
                            <tr>
                                <th>Old SC Margin per PC</th>
                                <td>{{ $item->old_sc_margin_per_pc }}</td>
                                <th>New SC Margin per PC</th>
                                <td>{{ $item->new_sc_margin_per_pc }}</td>
                            </tr>
                            <tr>
                                <th>Old Supplier Cost</th>
                                <td>{{ $item->old_supplier_cost }}</td>
                                <th>New Supplier Cost</th>
                                <td>{{ $item->new_supplier_cost}}</td>
                            </tr>
                            <tr>
                                <th>Approved By</th>
                                <td>{{ $item->name }}</td>
                                <th>Approved Date</th>
                                <td>{{ $item->approved_at_acct}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    <div class="panel-footer">
        <a href='{{ CRUDBooster::mainpath() }}' class='btn btn-default'>Cancel</a>
    </div>
</div>
@endsection
