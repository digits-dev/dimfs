@push('head')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<style>
    select {
        width: 100%;
    }
    .required-star {
        color: red;
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
        <i class="fa fa-pencil"></i><strong> Add {{CRUDBooster::getCurrentModule()->name}}</strong>
    </div>
    <div class="panel-body">
        <form action="{{ route('submit_edit_accounting') }} " method="POST" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="gacha_item_master_approvals_id" value="{{ $gacha_item_master_approvals_id }}">
            <h3 class="text-center text-bold">ITEM DETAILS</h3>
            <div class="row">
                <div class="col-md-6">
                    <table class="table-responsive table">
                        <tbody>
                            @if ($item->digits_code)
                            <tr>
                                <th>Digits Code</th>
                                <td><input value="{{ $item->digits_code }}" type="text" name="digits_code" id="digits_code" class="form-control" readonly></td>
                            </tr>
                            @endif
                            <tr>
                                <th>JAN Number</th>
                                <td><input value="{{ $item->jan_no }}" type="text" name="jan_no" id="jan_no" class="form-control" readonly></td>
                            </tr>
                            <tr>
                                <th>Item Number</th>
                                <td><input value="{{ $item->item_no }}" type="text" name="item_no" id="item_no" class="form-control" readonly></td>
                            </tr>
                            <tr>
                                <th>SAP Number</th>
                                <td><input value="{{ $item->sap_no }}" type="text" name="sap_no" id="sap_no" class="form-control" readonly></td>
                            </tr>
                            <tr>
                                <th> Product Type</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_product_types_id" id="gacha_product_types_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($product_types)
                                        @foreach ($product_types as $product_type)
                                        <option value="{{ $product_type->id }}" {{ $product_type->id == $item->gacha_product_types_id ? 'selected' : '' }}>{{ $product_type->product_type_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th> Brand Description</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_brands_id" id="gacha_brands_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($brands)
                                        @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ $brand->id == $item->gacha_brands_id ? 'selected' : '' }}>{{ $brand->brand_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th> SKU Status</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_sku_statuses_id" id="gacha_sku_statuses_id" class="form-control" readonly>
                                        @if ($sku_statuses)
                                        @foreach ($sku_statuses as $sku_status)
                                        <option value="{{ $sku_status->id }}" {{ $sku_status->id == $item->gacha_sku_statuses_id ? 'selected' : '' }}>{{ $sku_status->status_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Item Description</th>
                                <td><input value="{{ $item->item_description ?: '' }}" type="text" name="item_description" id="item_description" class="form-control" required oninput="this.value = this.value.toUpperCase()" {{ $item->digits_code ? 'readonly' : '' }}></td>
                            </tr>
                            <tr>
                                <th>Model</th>
                                <td><input value="{{ $item->gacha_models ?: '' }}" type="text" name="gacha_models" id="item_description" class="form-control" readonly oninput="this.value = this.value.toUpperCase()"></td>
                            </tr>
                            <tr>
                                <th> Category</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_categories_id" id="gacha_categories_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($categories)
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $item->gacha_categories_id ? 'selected' : '' }}>{{ $category->category_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Warehouse Category Description</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_wh_categories_id" id="gacha_wh_categories_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($warehouse_categories)
                                        @foreach ($warehouse_categories as $warehouse_category)
                                        <option value="{{ $warehouse_category->id }}" {{ $warehouse_category->id == $item->gacha_wh_categories_id ? 'selected' : '' }}>{{ $warehouse_category->category_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> MSRP JPY</th>
                                <td>
                                    <input type="number" value="{{ $item->msrp }}" class="form-control" name="msrp" id="msrp" step="0.01" required>
                                </td>
                            </tr>
                            <tr>
                                <th>Current SRP</th>
                                <td>
                                    <input type="number" value="{{ $item->current_srp }}" class="form-control" name="current_srp" id="current_srp" step="0.01">
                                </td>
                            </tr>
                            <tr>
                                <th>No. of Tokens</th>
                                <td>
                                    <input type="number" value="{{ $item->no_of_tokens }}" class="form-control" name="no_of_tokens" id="no_of_tokens" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> LC Per Carton</th>
                                <td>
                                    <input margin-input-id="lc_margin_per_carton" type="number" value="{{ $item->lc_per_carton }}" class="form-control with-margin" name="lc_per_carton" id="lc_per_carton" required>
                                </td>
                            </tr>
                            <tr>
                                <th>LC Margin Per Carton (%)</th>
                                <td>
                                    <input type="number" value="{{ $item->lc_margin_per_carton }}" class="form-control" name="lc_margin_per_carton" id="lc_margin_per_carton" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> LC Per PC</th>
                                <td>
                                    <input margin-input-id="lc_margin_per_pc" type="number" value="{{ $item->lc_per_pc }}" class="form-control with-margin" name="lc_per_pc" id="lc_per_pc" required>
                                </td>
                            </tr>
                            <tr>
                                <th>LC Margin Per PC (%)</th>
                                <td>
                                    <input type="number" value="{{ $item->lc_margin_per_pc }}" class="form-control" name="lc_margin_per_pc" id="lc_margin_per_pc" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>SC Per PC</th>
                                <td>
                                    <input margin-input-id="sc_margin" type="number" value="{{ $item->store_cost }}" class="form-control" name="store_cost" id="store_cost" required readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>SC Margin Per PC (%)</th>
                                <td>
                                    <input type="number" value="{{ $item->sc_margin }}" class="form-control" name="sc_margin" id="sc_margin" readonly>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-responsive">
                        <tbody>
                            <tr>
                                <th>PCS Per CTN</th>
                                <td>
                                    <input type="number" value="{{ $item->pcs_ctn }}" class="form-control" name="pcs_ctn" id="pcs_ctn" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>DP Per CTN</th>
                                <td>
                                    <input type="number" value="{{ $item->dp_ctn }}" class="form-control" name="dp_ctn" id="dp_ctn" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>PCS Per DP</th>
                                <td>
                                    <input type="number" value="{{ $item->pcs_dp }}" class="form-control" name="pcs_dp" id="pcs_dp" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>MOQ</th>
                                <td>
                                    <input type="number" value="{{ $item->moq }}" class="form-control" name="moq" id="moq" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>Order CTN</th>
                                <td>
                                    <input type="number" value="{{ $item->no_of_ctn }}" class="form-control" name="no_of_ctn" id="no_of_ctn" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>Number of Assort</th>
                                <td>
                                    <input type="number" value="{{ $item->no_of_assort }}" class="form-control" name="no_of_assort" id="no_of_assort" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>Country of Origin</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_countries_id" id="gacha_countries_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($countries)
                                        @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" {{ $country->id == $item->gacha_countries_id ? 'selected' : '' }}>{{ $country->country_code }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Incoterms</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_incoterms_id" id="gacha_incoterms_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($incoterms)
                                        @foreach ($incoterms as $incoterm)
                                        <option value="{{ $incoterm->id }}" {{ $incoterm->id == $item->gacha_incoterms_id ? 'selected' : '' }}>{{ $incoterm->incoterm_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Currency</th>
                                <td>
                                    <select disabled style="width: 100%" name="currencies_id" id="currencies_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($currencies)
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ $currency->id == $item->currencies_id ? 'selected' : '' }}>{{ $currency->currency_code }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Supplier Cost</th>
                                <td>
                                    <input type="number" value="{{ $item->supplier_cost }}" class="form-control" name="supplier_cost" id="supplier_cost" step="0.01" required>
                                </td>
                            </tr>
                            <tr>
                                <th>UOM Code</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_uoms_id" id="gacha_uoms_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($uoms)
                                        @foreach ($uoms as $uom)
                                        <option value="{{ $uom->id }}" {{ $uom->id == $item->gacha_uoms_id ? 'selected' : '' }}>{{ $uom->uom_code }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Inventory Type</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_inventory_types_id" id="gacha_inventory_types_id" class="form-control" readonly>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($inventory_types)
                                        @foreach ($inventory_types as $inventory_type)
                                        <option value="{{ $inventory_type->id }}" {{ $inventory_type->id == $item->gacha_inventory_types_id ? 'selected' : '' }}>{{ $inventory_type->inventory_type_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Vendor Type</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_vendor_types_id" id="gacha_vendor_types_id" class="form-control" required>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($vendor_types)
                                        @foreach ($vendor_types as $vendor_type)
                                        <option value="{{ $vendor_type->id }}" {{ $vendor_type->id == $item->gacha_vendor_types_id ? 'selected' : '' }}>{{ $vendor_type->vendor_type_code }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Vendor Group Name</th>
                                <td>
                                    <select disabled style="width: 100%" name="gacha_vendor_groups_id" id="gacha_vendor_groups_id" class="form-control" required>
                                        <option value="" disabled selected>None selected...</option>
                                        @if ($vendor_groups)
                                        @foreach ($vendor_groups as $vendor_group)
                                        <option value="{{ $vendor_group->id }}" {{ $vendor_group->id == $item->gacha_vendor_groups_id ? 'selected' : '' }}>{{ $vendor_group->vendor_group_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Age Grade</th>
                                <td>
                                    <input type="text" value="{{ $item->age_grade }}" class="form-control" name="age_grade" id="age_grade" readonly>
                                </td>
                            </tr>
                            <tr>
                                <th>Battery</th>
                                <td>
                                    <input type="text" value="{{ $item->battery }}" class="form-control" name="battery" id="battery" readonly>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" id="submit-btn" class="hide">Submit</button>
        </form>
    </div>
    <div class="panel-footer">
        <a href='{{ CRUDBooster::mainpath() }}' class='btn btn-default'>Cancel</a>
		<button class="btn btn-primary pull-right _action="save" id="save-btn"><i class="fa fa-save"></i> Save</button>
    </div>
</div>

<script type="application/javascript">

    $('#save-btn').click(function() {
        Swal.fire({
            title: 'Do you want to save this item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Save',
            returnFocus: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $('#submit-btn').click();
            }
        });
    });

    $(document).on('input', '.with-margin', function() {
        const value = Number($(this).val());
        const moq = Number($('#moq').val());
        const marginInputId = $(this).attr('margin-input-id');
        
        const marginValue = (value / moq * 100).toFixed(2);
        $(`#${marginInputId}`).val(Number(marginValue));

        if ($(this).attr('id') == 'lc_per_pc') {
            const storeCost = $('#store_cost');
            const storeCostValue = ($(this).val() / 100 * 30) + Number($(this).val());
            storeCost.val(storeCostValue);
            const scMargin = (storeCostValue / moq * 100).toFixed(2);
            $('#sc_margin').val(Number(scMargin));
        }
    });
</script>
@endsection