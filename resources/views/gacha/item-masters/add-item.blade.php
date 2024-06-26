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
        <i class="fa fa-pencil"></i><strong> Add/Edit {{CRUDBooster::getCurrentModule()->name}}</strong>
    </div>
    <div class="panel-body">
        <form action="{{ $action == 'add' ? route('submit_new_gacha_item') : route('submit_edit_gacha_item') }} " method="POST" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="path" value="{{ $path }}">
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
                                <th><span class="required-star">*</span> JAN Number</th>
                                <td><input value="{{ $item->jan_no }}" type="text" name="jan_no" id="jan_no" class="form-control" required></td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Item Number</th>
                                <td><input value="{{ $item->item_no }}" type="text" name="item_no" id="item_no" class="form-control" required></td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> SAP Number</th>
                                <td><input value="{{ $item->sap_no }}" type="text" name="sap_no" id="sap_no" class="form-control" required></td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span>  Product Type</th>
                                <td>
                                    <select style="width: 100%" name="gacha_product_types_id" id="gacha_product_types_id" class="form-control" required>
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
                                <th><span class="required-star">*</span>  Brand Description</th>
                                <td>
                                    <select style="width: 100%" name="gacha_brands_id" id="gacha_brands_id" class="form-control" required>
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
                                <th><span class="required-star">*</span>  SKU Status</th>
                                <td>
                                    <select style="width: 100%" name="gacha_sku_statuses_id" id="gacha_sku_statuses_id" class="form-control" required>
                                        @if ($sku_statuses)
                                        @foreach ($sku_statuses as $sku_status)
                                        <option value="{{ $sku_status->id }}" {{ $sku_status->id == $item->gacha_sku_statuses_id ? 'selected' : '' }}>{{ $sku_status->status_description }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Item Description</th>
                                <td><input value="{{ $item->item_description ?: '' }}" type="text" name="item_description" id="item_description" class="form-control" required oninput="this.value = this.value.toUpperCase()" {{ $item->digits_code && !in_array(CRUDBooster::myEmail(), config('privileges.edit_gacha_item_desc')) ? 'readonly' : '' }}></td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Model</th>
                                <td><input value="{{ $item->gacha_models ?: '' }}" type="text" name="gacha_models" id="item_description" class="form-control" required oninput="this.value = this.value.toUpperCase()"></td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Category</th>
                                <td>
                                    <select style="width: 100%" name="gacha_categories_id" id="gacha_categories_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Warehouse Category Description</th>
                                <td>
                                    <select style="width: 100%" name="gacha_wh_categories_id" id="gacha_wh_categories_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Current SRP</th>
                                <td>
                                    <input type="number" value="{{ $item->current_srp }}" class="form-control" name="current_srp" id="current_srp" step="0.01" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> No. of Tokens</th>
                                <td>
                                    <input type="number" value="{{ $item->no_of_tokens }}" class="form-control" name="no_of_tokens" id="no_of_tokens" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> PCS Per CTN</th>
                                <td>
                                    <input type="number" value="{{ $item->pcs_ctn }}" class="form-control" name="pcs_ctn" id="pcs_ctn" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> DP Per CTN</th>
                                <td>
                                    <input type="number" value="{{ $item->dp_ctn }}" class="form-control" name="dp_ctn" id="dp_ctn" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> PCS Per DP</th>
                                <td>
                                    <input type="number" value="{{ $item->pcs_dp }}" class="form-control" name="pcs_dp" id="pcs_dp" required>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-responsive">
                        <tbody>
                            <tr>
                                <th><span class="required-star">*</span> MOQ</th>
                                <td>
                                    <input type="number" value="{{ $item->moq }}" class="form-control" name="moq" id="moq" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Order CTN</th>
                                <td>
                                    <input type="number" value="{{ $item->no_of_ctn }}" class="form-control" name="no_of_ctn" id="no_of_ctn" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Number of Assort</th>
                                <td>
                                    <input type="number" value="{{ $item->no_of_assort }}" class="form-control" name="no_of_assort" id="no_of_assort" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Country of Origin</th>
                                <td>
                                    <select style="width: 100%" name="gacha_countries_id" id="gacha_countries_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Incoterms</th>
                                <td>
                                    <select style="width: 100%" name="gacha_incoterms_id" id="gacha_incoterms_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Currency</th>
                                <td>
                                    <select style="width: 100%" name="currencies_id" id="currencies_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> UOM Code</th>
                                <td>
                                    <select style="width: 100%" name="gacha_uoms_id" id="gacha_uoms_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Inventory Type</th>
                                <td>
                                    <select style="width: 100%" name="gacha_inventory_types_id" id="gacha_inventory_types_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Vendor Type</th>
                                <td>
                                    <select style="width: 100%" name="gacha_vendor_types_id" id="gacha_vendor_types_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Vendor Group Name</th>
                                <td>
                                    <select style="width: 100%" name="gacha_vendor_groups_id" id="gacha_vendor_groups_id" class="form-control" required>
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
                                <th><span class="required-star">*</span> Age Grade</th>
                                <td>
                                    <input type="text" value="{{ $item->age_grade }}" class="form-control" name="age_grade" id="age_grade" required>
                                </td>
                            </tr>
                            <tr>
                                <th><span class="required-star">*</span> Battery</th>
                                <td>
                                    <input type="text" value="{{ $item->battery }}" class="form-control" name="battery" id="battery" required>
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
        $('select').select2();
        $('#gacha_sku_statuses_id').select2();

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

        $(document).on('input', '#jan_no', function(event) {
            const value = $(this).val().replace(/[^a-z0-9]/gi, '');
            $(this).val(value);
        })

</script>
@endsection