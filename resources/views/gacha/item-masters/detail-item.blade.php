@push('head')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<style>
    select {
        width: 100%;
    }
    table, tbody, td, th {
        border: 1px solid black !important;
        padding-left: 50px;
    }

    th {
        width: 35%;
    }

    .photo-section {
        max-width: 400px;
        margin: 0 auto; 
    }

    .photo-section img {
        max-width: 100%;
        height: auto;
        display: block;
    }

    .swal2-html-container {
        line-height: 3rem !important;
    }


    .select2-container--default .select2-selection--single {
        border-radius: 0px !important
    }

    .select2-container .select2-selection--single {
        height: 35px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #3190c7 !important;
        border-color: #367fa9 !important;
        color: #fff !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
    }

    .select2-container--default .select2-selection--multiple{
        border-radius: 0px !important;
        width: 100% !important;
    }

    .select2-container .select2-selection--single .select2-selection__rendered{
        padding: 0 !important;
        margin-top: -2px;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear{
        margin-right: 10px !important;
        padding: 0 !important;
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
            <div class="row">
                <div class="col-md-6">
                    <table class="table-responsive table">
                        <tbody>
                            @if ($item->gacha_item_master_approvals_id) 
                            <tr>
                                <th>Approval Status</th>
                                <td>
                                    @if ($item->approval_status == 200 || $item->approval_status == 'APPROVED')
                                    <span class="label label-success">APRPOVED</span>
                                    @elseif ($item->approval_status == 202 || $item->approval_status == 'PENDING')
                                    <span class="label label-warning">PENDING</span>
                                    @elseif ($item->approval_status == 400 || $item->approval_status == 'REJECTED')
                                    <span class="label label-danger">REJECTED</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if ($item->digits_code)
                            <tr>
                                <th>Digits Code</th>
                                <td>{{ $item->digits_code }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>JAN Number</th>
                                <td>{{ $item->jan_no }}</td>
                            </tr>
                            <tr>
                                <th>Item Number</th>
                                <td>{{ $item->item_no }}</td>
                            </tr>
                            <tr>
                                <th>SAP Number</th>
                                <td>{{ $item->sap_no }}</td>
                            </tr>
                            <tr>
                                <th>Product Type</th>
                                <td>{{ $item->product_type_description }}</td>
                            </tr>
                            <tr>
                                <th> Brand Description</th>
                                <td>{{ $item->brand_description }}</td>
                            </tr>
                            <tr>
                                <th> Brand Status</th>
                                <td>{{ $item->brand_status }}</td>
                            </tr>
                            <tr>
                                <th> SKU Status</th>
                                <td>
                                    {{ $item->status_description }}
                                </td>
                            </tr>
                            <tr>
                                <th>Item Description</th>
                                <td>{{ $item->item_description }}</td>
                            </tr>
                            <tr>
                                <th>Model</th>
                                <td>{{ $item->gacha_models }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>{{ $item->category_description }}</td>
                            </tr>
                            <tr>
                                <th>Warehouse Category</th>
                                <td>{{ $item->wh_category_description }}</td>
                            </tr>
                            <tr>
                                <th>MSRP JPY</th>
                                <td>{{ $item->msrp }}</td>
                            </tr>
                            <tr>
                                <th>Current SRP</th>
                                <td>{{ $item->current_srp }}</td>
                            </tr>
                            <tr>
                                <th>No. of Tokens</th>
                                <td>{{ $item->no_of_tokens }}</td>
                            </tr>
                            <tr>
                                <th>LC Per Carton</th>
                                <td>{{ $item->lc_per_carton }}</td>
                            </tr>
                            <tr class="hide">
                                <th>LC Margin Per Carton (%)</th>
                                <td>{{ $item->lc_margin_per_carton }}</td>
                            </tr>
                            <tr>
                                <th>LC Per PC</th>
                                <td>{{ $item->lc_per_pc }}</td>
                            </tr>
                            <tr>
                                <th>LC Margin Per PC (%)</th>
                                <td>{{ $item->lc_margin_per_pc }}</td>
                            </tr>
                            <tr>
                                <th>SC Per PC</th>
                                <td>{{ $item->store_cost }}</td>
                            </tr>
                            <tr>
                                <th>SC Margin Per PC (%)</th>
                                <td>{{ $item->sc_margin }}</td>
                            </tr>
                            <tr>
                                <th>PCS Per CTN</th>
                                <td>{{ $item->pcs_ctn }}</td>
                            </tr>
                            <tr>
                                <th>DP Per CTN</th>
                                <td>{{ $item->dp_ctn }}</td>
                            </tr>
                            <tr>
                                <th>PCS Per DP</th>
                                <td>{{ $item->pcs_dp }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-responsive">
                        <tbody>
                            <tr>
                                <th>MOQ</th>
                                <td>{{ $item->moq }}</td>
                            </tr>
                            <tr>
                                <th>Order CTN</th>
                                <td>{{ $item->no_of_ctn }}</td>
                            </tr>
                            <tr>
                                <th>Number of Assort</th>
                                <td>{{ $item->no_of_assort }}</td>
                            </tr>
                            <tr>
                                <th>Country of Origin</th>
                                <td>{{ $item->country_code }}</td>
                            </tr>
                            <tr>
                                <th>Incoterms</th>
                                <td>{{ $item->incoterm_description }}</td>
                            </tr>
                            <tr>
                                <th>Currency</th>
                                <td>{{ $item->currency_code }}</td>
                            </tr>
                            <tr>
                                <th>Supplier Cost</th>
                                <td>{{ $item->supplier_cost }}</td>
                            </tr>
                            <tr>
                                <th>UOM Code</th>
                                <td>{{ $item->uom_code }}</td>
                            </tr>
                            <tr>
                                <th>Inventory Type</th>
                                <td>{{ $item->inventory_type_description }}</td>
                            </tr>
                            <tr>
                                <th>Vendor Type</th>
                                <td>{{ $item->vendor_type_code }}</td>
                            </tr>
                            <tr>
                                <th>Vendor Group Name</th>
                                <td>{{ $item->vendor_group_description }}</td>
                            </tr>
                            <tr>
                                <th>Vendor Group Status</th>
                                <td>{{ $item->vendor_group_status }}</td>
                            </tr>
                            <tr>
                                <th>Age Grade</th>
                                <td>{{ $item->age_grade }}</td>
                            </tr>
                            <tr>
                                <th>Battery</th>
                                <td>{{ $item->battery }}</td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td>{{ $item->created_name }}</td>
                            </tr>
                            <tr>
                                <th>Created Date</th>
                                <td>{{ $item->created_at }}</td>
                            </tr>
                            <tr>
                                <th>Updated By</th>
                                <td>{{ $item->updated_name }}</td>
                            </tr>
                            <tr>
                                <th>Updated Date</th>
                                <td>{{ $item->updated_at }}</td>
                            </tr>
                            <tr>
                                <th>Approved By</th>
                                <td>{{ $item->approved_name }}</td>
                            </tr>
                            <tr>
                                <th>Approved Date</th>
                                <td>{{ $item->approved_at }}</td>
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