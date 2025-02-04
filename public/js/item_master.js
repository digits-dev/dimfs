let brand_code = '',
category_code = '',
model = '',
model_specific = '',
size_value = '',
size_code = '',
actual_color = '';

let is_reclass = false;

$('#vendors_id option:not(:selected)').remove();
$('#classes_id option:not(:selected)').remove();
$('#subclasses_id option:not(:selected)').remove();
$('#margin_categories_id option:not(:selected)').remove();
$('#store_categories_id option:not(:selected)').remove();

if(edit_action) {

    brand_code = old_brand+'';
    category_code = old_category+'';
    model = old_model+'';
    model_specific = old_model_specific+'';
    size_value = old_size_value+'';
    size_code = old_size_code+'',
    actual_color = old_actual_color+'';
    
    $('#vendors_id').prepend('<option value="">** Please select a VENDOR</option>');
    $('#classes_id').prepend('<option value="">** Please select a CLASS DESCRIPTION</option>');
    $('#subclasses_id').prepend('<option value="">** Please select a SUBCLASS</option>');
    $('#margin_categories_id').prepend('<option value="">** Please select a MARGIN CATEGORY</option>');
    $('#store_categories_id').prepend('<option value="">** Please select a STORE CATEGORY</option>');
    
    $("[name='margin_category']").val($("#margin_categories_id :selected").text());
    var vendor_type = ["LOCAL-CONSIGNMENT","LOCAL-OUTRIGHT","LIGHTROOM-CONSIGNMENT","LIGHTROOM-OUTRIGHT"];
    var EcomStoreMargin = parseFloat(0).toFixed(2);
    
    if($("#margin_categories_id :selected").text() === "ACCESSORIES")
    {   
        $("#add_to_ecom_landed_cost").empty();
        $("#add_to_ecom_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a (ECOM) % ADD TO LC"));
        $("#add_to_ecom_landed_cost").append($("<option></option>").attr("value", parseFloat(0.1).toFixed(2)).text(parseFloat(0.1).toFixed(2)));
    }else if($("#margin_categories_id :selected").text() === "OTHER ACCESSORIES"){
        $("#ecom_deduct_from_percent_landed_cost").empty();
        $("#ecom_deduct_from_percent_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a Percentage"));
        $("#ecom_deduct_from_percent_landed_cost").append($("<option></option>").attr("value", parseFloat(0.1).toFixed(2)).text(parseFloat(0.01).toFixed(2)));
    }
                        
    // Edited by Lewie
    function GetEcomSC() {
        var computePercent = parseFloat(0).toFixed(2);
        var computed_deduct_percent = parseFloat(0).toFixed(2);
        
        if($("#margin_categories_id :selected").text() !== "ACCESSORIES" && $("#margin_categories_id :selected").text() !== "OTHER ACCESSORIES")
        {
            computePercent = parseFloat($('#landed_cost').val()) * parseFloat($('#add_to_ecom_landed_cost :selected').val());
            EcomStoreMargin = parseFloat($('#landed_cost').val()) + computePercent;
            
            var EcomSCPercentage = ($('#current_srp').val() - $('#ecom_store_margin').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                EcomSCPercentage = ($('#promo_srp').val() - $('#ecom_store_margin').val())/$('#promo_srp').val();
            }
            
            $('#ecom_store_margin').val(parseFloat(EcomStoreMargin).toFixed(2));
            $('#ecom_store_margin_percentage').val(parseFloat(EcomSCPercentage).toFixed(4));
            
        }else if($("#margin_categories_id :selected").text() === "OTHER ACCESSORIES")
        {
            computed_deduct_percent = parseFloat($('#ecom_deduct_from_percent_landed_cost :selected').text());
            
            var csm2 = ($('#current_srp').val() - $('#landed_cost').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm2 = ($('#promo_srp').val() - $('#landed_cost').val())/$('#promo_srp').val();
            }
            
            var csm3 = csm2.toFixed(7) - computed_deduct_percent;
            $('#ecom_store_margin_percentage').val(csm3.toFixed(4));
            console.log('HERE?????');
            setTimeout(function(){ 
                $('#ecom_store_margin').attr("readonly", true);
                var csm4 = $('#current_srp').val() * (1 - csm3.toFixed(7));
                $('#ecom_store_margin').val(csm4.toFixed(2));
            },1000);
        }
        
        var vendor_value = $("#vendor_types_id :selected").val();
        var brand_value = $("#brands_id :selected").val();
        var mc_value = $("#margin_categories_id :selected").text();
        var lc_value = parseFloat($('#landed_cost').val());
        var srp_value = parseFloat($('#current_srp').val());
        
        $.ajax({
            url: ADMIN_PATH + "/EcomMarginPercentage",
            dataType: "json",
            type: "POST",
            data: {
                "_token": $('#token').val(),
                "vendor_type": vendor_value,
                "brand": brand_value,
                "margin_category": mc_value,
                "landed_cost": lc_value,
                "current_srp": srp_value
            },
            success: function(data) {
                console.log(data);
                if(data !== '' || data !== null || data.alldata !== null){
                     if($("#margin_categories_id :selected").text() === "ACCESSORIES")
                    {
                        // if(data.alldata.max && data.alldata.max)
                        computePercent = parseFloat($('#landed_cost').val()) * parseFloat(data.alldata.store_margin_percentage).toFixed(2);
                        EcomStoreMargin = parseFloat($('#landed_cost').val()) + computePercent;
                        
                        var EcomSCPercentage = ($('#current_srp').val() - parseFloat(EcomStoreMargin))/$('#current_srp').val();
                        if($('#promo_srp').val().length !== 0){
                            EcomSCPercentage = ($('#promo_srp').val() - parseFloat(EcomStoreMargin))/$('#promo_srp').val();
                        }
                        
                        $('#ecom_store_margin').val(parseFloat(EcomStoreMargin).toFixed(2));
                        $('#ecom_store_margin_percentage').val(parseFloat(EcomSCPercentage).toFixed(4));
                    }
                
                    if(data.alldata.store_margin_percentage !== '' || data.alldata.store_margin_percentage !== null){
                        if(parseFloat($('#add_to_ecom_landed_cost').val()).toFixed(2) !== parseFloat(data.alldata.store_margin_percentage).toFixed(2)){
                            if($("#margin_categories_id :selected").text() === "ACCESSORIES")
                            {   
                                $("#add_to_ecom_landed_cost").empty();
                                $("#add_to_ecom_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a (ECOM) % ADD TO LC"));
                                $("#add_to_ecom_landed_cost").append($("<option></option>").attr("value", parseFloat(0.1).toFixed(2)).text(parseFloat(0.1).toFixed(2)));
                            }else{
                                $("#add_to_ecom_landed_cost").empty();
                                $("#add_to_ecom_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a (ECOM) % ADD TO LC"));
                                $("#add_to_ecom_landed_cost").append($("<option></option>").attr("value", parseFloat(data.alldata.store_margin_percentage).toFixed(2)).text(parseFloat(data.alldata.store_margin_percentage).toFixed(2)));
                            }
                        }
    
                        $('#add_to_ecom_landed_cost').attr("disabled", false);
                    }else{
                           
                    }
                }
               
            },
            error: function(e) {
                console.log(JSON.stringify(e.statusText));
            }
        });
    }
    
    $('#ecom_store_margin').on('keyup', function() {
        var csm = ($('#current_srp').val() - $('#ecom_store_margin').val())/$('#current_srp').val();
        if($('#promo_srp').val().length !== 0){
            csm = ($('#promo_srp').val() - $('#ecom_store_margin').val())/$('#promo_srp').val();
        }
        $('#ecom_store_margin_percentage').val(csm.toFixed(4));
    });
    
    $('#add_to_ecom_landed_cost').on('change', function() {
        if($('#add_to_ecom_landed_cost :selected').val() !== '' && $("#margin_categories_id :selected").text() == "UNITS"){
            var computed_percent = parseFloat($('#add_to_ecom_landed_cost :selected').text())+1;
            var computed_dtp = computed_percent * $('#landed_cost').val();
            $('#ecom_store_margin').val(computed_dtp.toFixed(2));
        }
        GetEcomSC();
    });
    
    $('#ecom_deduct_from_percent_landed_cost').on('change', function() {
        if($('#ecom_deduct_from_percent_landed_cost :selected').val() !== '' && $("#margin_categories_id :selected").text() == "OTHER ACCESSORIES"){
            var computed_percent = parseFloat($('#ecom_deduct_from_percent_landed_cost :selected').text())+1;
            var computed_dtp = computed_percent * $('#landed_cost').val();
            $('#ecom_store_margin_percentage').val(computed_dtp.toFixed(2));
        }
        
        var computed_deduct_percent = parseFloat($('#ecom_deduct_from_percent_landed_cost :selected').text());
        
        var csm2 = ($('#current_srp').val() - $('#landed_cost').val())/$('#current_srp').val();
        if($('#promo_srp').val().length !== 0){
            csm2 = ($('#promo_srp').val() - $('#landed_cost').val())/$('#promo_srp').val();
        }
        
        var csm3 = csm2.toFixed(7) - computed_deduct_percent;
        $('#ecom_store_margin_percentage').val(csm3.toFixed(4));
        
        setTimeout(function(){ 
            $('#ecom_store_margin').attr("readonly", true);
            var csm4 = $('#current_srp').val() * (1 - csm3.toFixed(7));
            $('#ecom_store_margin').val(csm4.toFixed(2));
        },1000);
            
        GetEcomSC();
    });
    
    //---------------------------------------------------------------------------------------------------------------------
    
    if(jQuery.inArray($("#vendor_types_id :selected").text(), vendor_type) != -1){
        loadNewAddtoLc($("#margin_categories_id :selected").text(),$("#brands_id :selected").val(),$("#vendor_types_id :selected").val());
        $("#form-group-deduct_from_percent_landed_cost").remove();
    }
    
    else if($("#margin_categories_id :selected").text() == "UNITS"){
        //autocompute store margin
        //get brand then compare to matrix
        $("#form-group-deduct_from_percent_landed_cost").remove();
        $("#form-group-ecom_deduct_from_percent_landed_cost").remove();
        
        $(function() {
            var csm = ($('#current_srp').val() - $('#dtp_rf').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm = ($('#promo_srp').val() - $('#dtp_rf').val())/$('#promo_srp').val();
            }
            $('#dtp_rf_percentage').val(csm.toFixed(4));
            
            // Edited by Lewie
            var ecomcs1 = ($('#current_srp').val() - $('#ecom_store_margin').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                ecomcs1 = ($('#promo_srp').val() - $('#ecom_store_margin').val())/$('#promo_srp').val();
            }
            $('#ecom_store_margin_percentage').val(ecomcs1.toFixed(4));
            // ------------------------------------------------------------

            loadNewAddtoLc($("#margin_categories_id :selected").text(),$("#brands_id :selected").val(),$("#vendor_types_id :selected").val());
        });
    }
    
    else if($("#margin_categories_id :selected").text() == "ACCESSORIES"){
        //autocompute store margin
        //get brand then compare to matrix
        
        $("#form-group-add_to_landed_cost").remove();
        $("#form-group-deduct_from_percent_landed_cost").remove();
        $("#form-group-ecom_deduct_from_percent_landed_cost").remove();
        
        $(function() {
            var csm = ($('#current_srp').val() - $('#dtp_rf').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm = ($('#promo_srp').val() - $('#dtp_rf').val())/$('#promo_srp').val();
            }
            $('#dtp_rf_percentage').val(csm.toFixed(4));
                
            // Edited by Lewie
            var ecomcs2 = ($('#current_srp').val() - $('#ecom_store_margin').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                ecomcs2 = ($('#promo_srp').val() - $('#ecom_store_margin').val())/$('#promo_srp').val();
            }
            
            console.log(ecomcs2);
            $('#ecom_store_margin_percentage').val(ecomcs2.toFixed(4));
            // ------------------------------------------------------------
        });
                
        GetEcomSC();
    }
    
    else if($("#margin_categories_id :selected").text() == "UNIT ACCESSORIES"){
        loadNewDeductFromMLc($("#margin_categories_id :selected").text());
        $("#form-group-add_to_landed_cost").remove();

        // Edited by Lewie
        $("#form-group-add_to_ecom_landed_cost").remove();
        
        $(function() {
            var csm = ($('#current_srp').val() - $('#dtp_rf').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm = ($('#promo_srp').val() - $('#dtp_rf').val())/$('#promo_srp').val();
            }
            $('#dtp_rf_percentage').val(csm.toFixed(4));
                        
            // Edited by Lewie
            var ecomcs3 = ($('#current_srp').val() - $('#ecom_store_margin').val())/$('#current_srp').val();  
            if($('#promo_srp').val().length !== 0){
                ecomcs3 = ($('#promo_srp').val() - $('#ecom_store_margin').val())/$('#promo_srp').val();
            }
            $('#ecom_store_margin_percentage').val(ecomcs3.toFixed(4));
            // ------------------------------------------------------------
        });
    }else{
        $("#form-group-deduct_from_percent_landed_cost").remove();
    }
    
    $('#dtp_rf').on('keyup', function() {
        var csm = ($('#current_srp').val() - $('#dtp_rf').val())/$('#current_srp').val();
        if($('#promo_srp').val().length !== 0){
            csm = ($('#promo_srp').val() - $('#dtp_rf').val())/$('#promo_srp').val();
        }
        
        $('#dtp_rf_percentage').val(csm.toFixed(4));
        
        
    });
    
    $('#add_to_landed_cost').on('change', function() {
        if($('#add_to_landed_cost :selected').val() !== '' && $("#margin_categories_id :selected").text() == "UNITS"){
            var computed_percent = parseFloat($('#add_to_landed_cost :selected').text())+1;
            var computed_dtp = computed_percent * $('#landed_cost').val();
            $('#dtp_rf').val(computed_dtp.toFixed(2));
        }
        
    });
    
    $('#landed_cost').on('keyup', function() {
        if($("#margin_categories_id :selected").text() == "UNITS" || jQuery.inArray($("#vendor_types_id :selected").text(), vendor_type) != -1){
            if($('#add_to_landed_cost').val() === ''){
                swal('Warning !', '**Please select % add to landed cost first.');
            }else if($('#add_to_ecom_landed_cost').val() === ''){
                swal('Warning !', '**Please select ECOM % add to landed cost first.');
            }
                
            var computed_percent = parseFloat($('#add_to_landed_cost :selected').text())+1;
            var computed_dtp = computed_percent * $('#landed_cost').val();
            
            $('#dtp_rf').attr("readonly", true);
            $('#dtp_rf').val(computed_dtp.toFixed(2));
            
            var csm = ($('#current_srp').val() - $('#dtp_rf').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm = ($('#promo_srp').val() - $('#dtp_rf').val())/$('#promo_srp').val();
            }
            $('#dtp_rf_percentage').val(csm.toFixed(4));
        }
        else if($("#margin_categories_id :selected").text() == "UNIT ACCESSORIES"){
            if($('#deduct_from_percent_landed_cost').val() === ''){
                swal('Warning !', '**Please select deduct from margin % at landed cost first.');
            }else if($('#ecom_deduct_from_percent_landed_cost').val() === ''){
                swal('Warning !', '**Please select ECOM deduct from margin % at landed cost first.');
            }

                
            var computed_deduct_percent = parseFloat($('#deduct_from_percent_landed_cost :selected').text());
            
            var csm2 = ($('#current_srp').val() - $('#landed_cost').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm2 = ($('#promo_srp').val() - $('#landed_cost').val())/$('#promo_srp').val();
            }
            
            var csm3 = csm2.toFixed(7) - computed_deduct_percent;
            $('#dtp_rf_percentage').val(csm3.toFixed(4));
            
            setTimeout(function(){ 
                $('#dtp_rf').attr("readonly", true);
                var csm4 = $('#current_srp').val() * (1 - csm3.toFixed(7));
                $('#dtp_rf').val(csm4.toFixed(2));
            },1000);
        }

        GetEcomSC();
    });
    
    $('#working_dtp_rf').on('keyup', function() {
        var csm = ($('#current_srp').val() - $('#working_dtp_rf').val())/$('#current_srp').val();
        if($('#promo_srp').val().length !== 0){
            csm = ($('#promo_srp').val() - $('#working_dtp_rf').val())/$('#promo_srp').val();
        }
        if($("#margin_categories_id :selected").text() == "ACCESSORIES"){
            $.ajax({
                url: ADMIN_PATH + "/getAccessoriesMarginPercentage",
                dataType: "json",
                type: "POST",
                data: {
                    "_token": $('#token').val(),
                    "margin_percentage": csm,
                },
                success: function(data) {
                    $('#working_dtp_rf_percentage').val(csm.toFixed(4));
                    
                },
                error: function(e) {
                    console.log(JSON.stringify(e.statusText));
                }
            });
        }
    });
    
    $('#working_landed_cost').on('keyup', function() {
        
        if($("#margin_categories_id :selected").text() == "UNITS"){
            var computed_percent = parseFloat($('#add_to_landed_cost :selected').text());
            var wlc = $(this).val();
            var computed_dtp = (computed_percent+1) * wlc;
            $('#working_dtp_rf').attr("readonly", true);
            $('#working_dtp_rf').val(computed_dtp.toFixed(2));
            
            var csm = ($('#current_srp').val() - $('#working_dtp_rf').val())/$('#current_srp').val();
            if($('#promo_srp').val().length !== 0){
                csm = ($('#promo_srp').val() - $('#working_dtp_rf').val())/$('#promo_srp').val();
            }
            $('#working_dtp_rf_percentage').val(csm.toFixed(4));
        }
    });
}

function itemDescriptionConcat() {

    let temp_item_description = brand_code.concat(category_code,model,model_specific,size_value,size_code,actual_color);
    $('#item_description').val($.trim(temp_item_description.toUpperCase().replace(/N\/A|NMS/g, "").replace(/\s\s+/g, " ")));
    itemDescriptionCount();
}

function itemDescriptionCount() {
    var count = $('#item_description').val().length;
    $("#id_item_description").html('Character Count: '+ count);

    if(count > 60) {
        
        $("#id_item_description").css("color", "red");
        $('#item_description').css('border-color', 'red');
        $('#item_description').focus();
    }
    else if(count === 0) {
        $("#id_item_description").html('');
        $('#item_description').css('border-color', 'gray');
    }
    else {
        $("#id_item_description").css("color", "black");
        $('#item_description').css('border-color', 'gray');
    }
}

$(':input[type="number"]').on('wheel', function() {
    $(this).blur();
});

$(':input[type="number"]').on("paste",function(event) {
});

$('#upc_code').focusout( function() {
    if(add_action){
        //check upc code if existing
        $.ajax({
            url: ADMIN_PATH + "/getExistingUPC",
            dataType: "json",
            type: "POST",
            data: {
                "_token": $('#token').val(),
                "upc_code": $(this).val(),
            },
            success: function(data) {
                if (data.status_no == 1) {
                    swal('Information !', "Existing item found!\n\nDo you want to Reclass the item?\nPlease CHANGE:\n1. Category\n2. Add \"D\" to UPC Code.");
                    is_reclass = true;        
                    $("#supplier_item_code").val(data.item.supplier_item_code);
                    
                    setTimeout(function(){ 
                        $('#brands_id').val(data.item.brands_id).trigger('change');
                    },2000);
                    
                    setTimeout(function(){ 
                        $('#vendors_id').val(data.item.vendors_id).trigger('change');
                    },3000);

                    $('#categories_id').val(data.item.categories_id).trigger('change');

                    setTimeout(function(){ 
                        $('#classes_id').val(data.item.classes_id).trigger('change');
                    },4000);

                    setTimeout(function(){ 
                        $('#subclasses_id').val(data.item.subclasses_id).trigger('change');
                    },5000);

                    setTimeout(function(){ 
                        $('#margin_categories_id').val(data.item.margin_categories_id).trigger('change');
                    },6000);

                    $('#warehouse_categories_id').val(data.item.warehouse_categories_id).trigger('change');
                    $("#model").val(data.item.model).trigger('keyup');
                    $('#model_specifics_id').val(data.item.model_specifics_id).trigger('change');
                    $("#actual_color").val(data.item.actual_color).trigger('keyup');
                    $('#colors_id').val(data.item.colors_id).trigger('change');
                    $("#size_value").val(data.item.size_value).trigger('keyup');
                    $('#sizes_id').val(data.item.sizes_id).trigger('change');
                    
                    setTimeout(function(){ 
                        $("#item_description").val(data.item.item_description).trigger('keyup');
                    },5000);
                    
                    $('#uoms_id').val(data.item.uoms_id).trigger('change');
                    $('#inventory_types_id').val(data.item.inventory_types_id).trigger('change');
                    $('#original_srp').val(data.item.original_srp);
                    $('#promo_srp').val(data.item.promo_srp);
                    $('#dtp_rf').val(data.item.dtp_rf);
                    $('#moq').val(data.item.moq);
                    $('#currencies_id').val(data.item.currencies_id).trigger('change');
                    $('#purchase_price').val(data.item.purchase_price);
                    $('#sku_legends_id').val(data.item.sku_legends_id).trigger('change');
                    
                    // var selectedSerial = data.item.serialized.split(";");
                    
                    $('#baseus_segmentation').val(data.item.baseus_segmentation).trigger('change');
                    $('#btb_segmentation').val(data.item.btb_segmentation).trigger('change');
                    $('#dcon_segmentation').val(data.item.dcon_segmentation).trigger('change');
                    $('#dout_segmentation').val(data.item.dout_segmentation).trigger('change');
                    $('#dw_segmentation').val(data.item.dw_segmentation).trigger('change');
                    $('#dwmachine_segmentation').val(data.item.dwmachine_segmentation).trigger('change');
                    $('#franchise_segmentation').val(data.item.franchise_segmentation).trigger('change');
                    $('#guam_segmentation').val(data.item.guam_segmentation).trigger('change');
                    $('#omg_segmentation').val(data.item.omg_segmentation).trigger('change');
                    $('#online_segmentation').val(data.item.online_segmentation).trigger('change');
                }
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
});

$('#brands_id').on('change', function() {
    if(add_action || edit_action){
        var brand_id = this.value;
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getBrandCode/" + brand_id,
            data: '',
            success: function(data) {
                if(add_action) {
                    brand_code = data + ' ';
                    itemDescriptionConcat();
                }
                loadNewVendorNames(brand_id);
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
});

$('#categories_id').on('change', function() {
    var category_id = this.value;
    if(add_action || edit_action){
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getCategoryCode/" + category_id,
            data: '',
            success: function(data) {
                if(add_action) {
                    category_code = data + ' ';
                    itemDescriptionConcat();
                }
                loadNewClass(category_id);
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
    
});

$('#classes_id').on('change', function() {
    if((add_action || edit_action) && this.value != ""){
        $('#margin_categories_id').val(null).trigger('change');
        $('#store_categories_id').val(null).trigger('change');
        loadNewSubClass(this.value);
    }
});

$('#subclasses_id').on('change', function() {
    if((add_action || edit_action) && this.value != ""){
        loadNewMarginCategory(this.value);
        loadNewStoreCategory(this.value);
    }
});

$('#model_specifics_id').on('change', function() {
    if(add_action){
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getModelSpecificCode/" + this.value,
            data: '',
            success: function(data) {
                model_specific = data + ' ';
                itemDescriptionConcat();
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
});

$('#sizes_id').on('change', function() {
    if(add_action){
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getSizeCode/" + this.value,
            data: '',
            success: function(data) {
                size_code = data + ' ';
                itemDescriptionConcat();
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
});

$('#vendors_id').on('change', function() {
    /*alert('isNotNull: '+(this.value != "")+' value: '+this.value);*/
    if((add_action || edit_action) && this.value != ""){
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getVendorTypeCode/" + this.value,
            data: '',
            success: function(data) {
                $('#vendor_types_id').val(data).trigger('change');
                $('#vendor_types_id option:not(:selected)').prop('disabled', true);
            },
            error: function(e) {
                console.log(e);
            }
        });

        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getVendorIncoterms/" + this.value,
            data: '',
            success: function(data) {
                $('#incoterms_id').val(data).trigger('change');
                $('#incoterms_id option:not(:selected)').prop('disabled', true);
            },
            error: function(e) {
                console.log(e);
            }
        });
        
        loadNewVendorGroup(this.value);
    }
});

$('#model').on('keyup', function() {
    model='';
    if(add_action){
        model = this.value + ' ';
        itemDescriptionConcat();
    }
});

$('#actual_color').on('keyup', function() {
    actual_color='';
    if(add_action){
        actual_color = this.value;
        itemDescriptionConcat();
    }
});

$('#size_value').on('keyup', function() {
    size_value='';
    if(add_action && this.value != 0){
        size_value = this.value + ' ';
        itemDescriptionConcat();
    }
});

$('#item_description').on('keyup', function(event) {
    itemDescriptionCount();
    var count = this.value.length;
    if(count > 60){
        if(event.which != 8){
            swal('Warning !', '**Please limit the Item Description to 60 characters.');
        }
    }
});

$('form').submit(function(event) {

    if(add_action){
        let description_count = $('#item_description').val().length;
        let original_srp = $('#original_srp').val();
        let vendor_type = $('#vendor_types_id option:selected').text();

        let srp_end1 = original_srp.substr(-2);
        let srp_end2 = original_srp.substr(-5);

        if(description_count <= 60) {
            if(srp_end2.indexOf('.') >= 0 && original_srp != '90.00' && vendor_type != 'LOCAL CONSIGNMENT' && !is_reclass){
                $('#original_srp').focus();
                swal('Warning !', "**Please check the Original SRP.\nPrice of regular item  must be ending in 90.");
            }
            else if(srp_end1 != '90' && vendor_type != 'LOCAL CONSIGNMENT' && !is_reclass) {
                $('#original_srp').focus();
                swal('Warning !', "**Please check the Original SRP.\nPrice of regular item  must be ending in 90.");
            }
            else{
                return;
            }
        }
        else {
            $('#item_description').focus();
            swal('Warning !', '**Please limit the Item Description to 60 characters.');
        }

        event.preventDefault();
    }

    if(edit_action){
        let description_count = $('#item_description').val().length;
        if(description_count > 60) {
            $('#item_description').focus();
            swal('Warning !', '**Please limit the Item Description to 60 characters.');
        }
        // if(document.getElementById("landed_cost")){
            
        //     if($('#dtp_rf').val().length == 0){
        //         $('#dtp_rf').focus();
        //         swal('Warning !', '**Please fill up store cost');
        //         event.preventDefault();
        //     }
        //     if($('#dtp_rf_percentage').val().length == 0 && $('#dtp_rf').val().length != 0){
        //         $('#dtp_rf_percentage').focus();
        //         swal('Warning !', '**Please fill up store margin');
        //         event.preventDefault();
        //     }
        //     if($('#landed_cost').val().length == 0 && $('#dtp_rf').val().length != 0 && $('#dtp_rf_percentage').val().length != 0){
        //         $('#landed_cost').focus();
        //         swal('Warning !', '**Please fill up landed cost');
        //         event.preventDefault();
        //     }
            
        // }
        // if(document.getElementById("working_landed_cost")){
            
        //     if($('#working_dtp_rf').val().length == 0 && $('#working_dtp_rf_percentage').val().length != 0 && $('#working_landed_cost').val().length != 0){
        //         $('#working_dtp_rf').focus();
        //         swal('Warning !', '**Please fill up working store cost');
        //         event.preventDefault();                
        //     }
        //     if($('#working_dtp_rf_percentage').val().length == 0 && $('#working_dtp_rf').val().length != 0){
        //         $('#working_dtp_rf_percentage').focus();
        //         swal('Warning !', '**Please fill up working store margin');
        //         event.preventDefault();
        //     }
        //     if($('#working_landed_cost').val().length == 0 && $('#working_dtp_rf').val().length != 0 && $('#working_dtp_rf_percentage').val().length != 0){
        //         $('#working_landed_cost').focus();
        //         swal('Warning !', '**Please fill up working landed cost');
        //         event.preventDefault();
        //     }
            
        // }
    }
});

function loadNewVendorNames(brand_id) {
    $('#vendors_id').val(null).trigger('change');
    $('#vendor_types_id').val(null).trigger('change');
    $('#vendors_id').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getVendorByBrand/" + brand_id,
        data: '',
        success: function(vendor) {
            /*console.log(JSON.stringify(vendor));*/
            $("#vendors_id").empty();
            $("#vendors_id").append($("<option></option>").attr("value", "").text("** Please select a VENDOR"));
            
            $.each(vendor, function(value, key) {
                $("#vendors_id").append($("<option></option>").attr("value", value).text(key));
            });
            $('#vendors_id').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function loadNewClass(category_id) {
    $('#classes_id').val(null).trigger('change');
    $('#classes_id').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getClassByCategory/" + category_id,
        data: '',
        success: function(new_class) {
            /*console.log(JSON.stringify(new_class));*/
            $("#classes_id").empty();
            $("#classes_id").append($("<option></option>").attr("value", "").text("** Please select a CLASS DESCRIPTION"));
            
            $.each(new_class, function(value, key) {
                $("#classes_id").append($("<option></option>").attr("value", value).text(key));
            });
            $('#classes_id').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function loadNewSubClass(class_id) {
    $('#subclasses_id').val(null).trigger('change');
    $('#subclasses_id').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getSubclassByClass/" + class_id,
        data: '',
        success: function(new_subclass) {
            /*console.log(JSON.stringify(new_subclass));*/
            $("#subclasses_id").empty();
            $("#subclasses_id").append($("<option></option>").attr("value", "").text("** Please select a SUBCLASS"));
            
            $.each(new_subclass, function(value, key) {
                $("#subclasses_id").append($("<option></option>").attr("value", value).text(key));
            });
            $('#subclasses_id').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function loadNewMarginCategory(subclass_id) {
    $('#margin_categories_id').val(null).trigger('change');
    $('#margin_categories_id').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getMarginCategoryBySubclass/" + subclass_id,
        data: '',
        success: function(new_margin_category) {
            /*console.log(JSON.stringify(new_margin_category));*/
            $("#margin_categories_id").empty();
            $("#margin_categories_id").append($("<option></option>").attr("value", "").text("** Please select a MARGIN CATEGORY"));
            
            $.each(new_margin_category, function(value, key) {
                $("#margin_categories_id").append($("<option></option>").attr("value", value).text(key));
            });
            $('#margin_categories_id').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function loadNewStoreCategory(subclass_id) {
    $('#store_categories_id').val(null).trigger('change');
    $('#store_categories_id').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getStoreCategoryBySubclass/" + subclass_id,
        data: '',
        success: function(new_store_category) {
            /*console.log(JSON.stringify(new_store_category));*/
            $("#store_categories_id").empty();
            $("#store_categories_id").append($("<option></option>").attr("value", "").text("** Please select a STORE CATEGORY"));
            
            $.each(new_store_category, function(value, key) {
                $("#store_categories_id").append($("<option></option>").attr("value", value).text(key));
            });
            $('#store_categories_id').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function loadNewVendorGroup(vendor_id) {
    $('#vendor_groups_id').val(null).trigger('change');
    $('#vendor_groups_id').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getVendorGroupByVendor/" + vendor_id,
        data: '',
        success: function(new_vendor_group) {
            /*console.log(JSON.stringify(new_vendor_group));*/
            $("#vendor_groups_id").empty();
            $("#vendor_groups_id").append($("<option></option>").attr("value", "").text("** Please select a VENDOR GROUP"));
            
            $.each(new_vendor_group, function(value, key) {
                $("#vendor_groups_id").append($("<option></option>").attr("value", value).text(key));
            });
            $('#vendor_groups_id').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}

function loadNewAddtoLc(margin_category, brand_id, vendor_type_id) {
    $('#add_to_landed_cost').val(null).trigger('change');
    $('#add_to_landed_cost').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getMarginMatrixByMarginCategory/" + margin_category + "/" + brand_id + "/" + vendor_type_id,
        data: '',
        success: function(new_margin_percentage) {
            console.log(JSON.stringify(new_margin_percentage));
            $("#add_to_landed_cost").empty();
            $("#add_to_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a Percentage"));
            
            $.each(new_margin_percentage, function(value, key) {
                $("#add_to_landed_cost").append($("<option></option>").attr("value", key).text(key));
            });
            $('#add_to_landed_cost').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
    
    if($('#add_to_landed_cost option').length == 1){
        $("#form-group-add_to_landed_cost").remove();
    }
        
    GetEcomSC()
}

function loadNewDeductFromMLc(margin_category) {
    $('#deduct_from_percent_landed_cost').val(null).trigger('change');
    $('#deduct_from_percent_landed_cost').attr("disabled", true);

    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getMarginMatrixByOtherMarginCategory/" + margin_category,
        data: '',
        success: function(new_margin_percentage) {
            console.log(JSON.stringify(new_margin_percentage));
            $("#deduct_from_percent_landed_cost").empty();
            $("#deduct_from_percent_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a Percentage"));
            
            $.each(new_margin_percentage, function(value, key) {
                $("#deduct_from_percent_landed_cost").append($("<option></option>").attr("value", key).text(key));
            });
            $('#deduct_from_percent_landed_cost').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
    
    if($('#deduct_from_percent_landed_cost option').length == 1){
        $("#form-group-deduct_from_percent_landed_cost").remove();
    }
     
    if($('#ecom_deduct_from_percent_landed_cost option').length == 1){
        $("#form-group-ecom_deduct_from_percent_landed_cost").remove();
    }

}