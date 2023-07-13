let brand_code = old_brand+' ',
category_code = old_category+' ',
model = old_model+' ',
model_specific = old_model_specific+' ',
size_value = old_size_value+' ',
size_code = old_size_code+' ',
actual_color = old_actual_color;

$('#vendors_id option:not(:selected)').remove();
$('#classes_id option:not(:selected)').remove();
$('#subclasses_id option:not(:selected)').remove();
$('#margin_categories_id option:not(:selected)').remove();
$('#store_categories_id option:not(:selected)').remove();

$('#vendors_id').prepend('<option value="">** Please select a VENDOR</option>');
$('#classes_id').prepend('<option value="">** Please select a CLASS DESCRIPTION</option>');
$('#subclasses_id').prepend('<option value="">** Please select a SUBCLASS</option>');
$('#margin_categories_id').prepend('<option value="">** Please select a MARGIN CATEGORY</option>');
$('#store_categories_id').prepend('<option value="">** Please select a STORE CATEGORY</option>');

function itemDescriptionConcat() {

    let temp_item_description = brand_code.concat(category_code,model,model_specific,size_value,size_code,actual_color);
    $('#item_description').val($.trim(temp_item_description.toUpperCase().replace(/N\/A|NMS/g, "").replace(/\s\s+/g, " ")));
    itemDescriptionCount();
}

function itemDescriptionCount() {
    var count = $('#item_description').val().length;
    $("#id_item_description").html('Character Count: '+ count);

    if(count > 50) {
        
        $("#id_item_description").css("color", "red");
        $('#item_description').css('border-color', 'red');
        $('#item_description').focus();
    }
    else if(count == 0) {
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
/*force to 2 decimal places & 1 decimal point*/
// $(':input[type="number"]').on('keypress', function(event) {

//     if(this.value.indexOf(".") > -1 && (this.value.split('.')[1].length > 1)) {
//         swal('Warning !', "**Two (2) decimal places only!");
//         event.preventDefault();
//     }
//     if((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)){
//         event.preventDefault();
//     }
//     this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
// });

$(':input[type="number"]').on("paste",function(event) {
    /*event.preventDefault();*/
    /*this.value = parseFloat(this.value).toFixed(2);*/
});

$('#brands_id').on('change', function() {
    if(edit_action){
        var brand_id = this.value;
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getBrandCode/" + brand_id,
            data: '',
            success: function(data) {
                brand_code = data + ' ';
                itemDescriptionConcat();
                loadNewVendorNames(brand_id);
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
});

$('#categories_id').on('change', function() {
    if(edit_action){
        var category_id = this.value;
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getCategoryCode/" + category_id,
            data: '',
            success: function(data) {
                category_code = data + ' ';
                itemDescriptionConcat();
                loadNewClass(category_id);
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
});

$('#classes_id').on('change', function() {
    if((edit_action) && this.value != ""){
        $('#margin_categories_id').val(null).trigger('change');
        $('#store_categories_id').val(null).trigger('change');
        loadNewSubClass(this.value);
    }
});

$('#subclasses_id').on('change', function() {
    if((edit_action) && this.value != ""){
        loadNewMarginCategory(this.value);
        loadNewStoreCategory(this.value);
    }
});

$('#model_specifics_id').on('change', function() {
    if(edit_action){
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
    if(edit_action){
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
    
    if((edit_action) && this.value != ""){
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
    if(edit_action){
        model = this.value + ' ';
        itemDescriptionConcat();
    }
});

$('#actual_color').on('keyup', function() {
    actual_color='';
    if(edit_action){
        actual_color = this.value;
        itemDescriptionConcat();
    }
});

$('#size_value').on('keyup', function() {
    size_value='';
    if(edit_action && this.value != 0){
        size_value = this.value + ' ';
        itemDescriptionConcat();
    }
});

$('#item_description').on('keyup', function(event) {
    itemDescriptionCount();
    var count = this.value.length;
    if(count > 50){
        if(event.which != 8){
            swal('Warning !', '**Please limit the Item Description to 50 characters.');
        }
    }
});

$('form').submit(function(event) {
    if(edit_action){
        let description_count = $('#item_description').val().length;
        
        if(description_count > 50) {
            $('#item_description').focus();
            swal('Warning !', '**Please limit the Item Description to 50 characters.');
        }

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
    $('#subclasses_id').val(null).trigger('change');
    $('#margin_categories_id').val(null).trigger('change');
    $('#store_categories_id').val(null).trigger('change');
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

function loadNewAddtoLc(margin_category, brand_id) {
    $('#add_to_landed_cost').val(null).trigger('change');
    $('#add_to_landed_cost').attr("disabled", true);
    $.ajax({
        type: 'GET',
        url: ADMIN_PATH + "/getMarginMatrixByMarginCategory/" + margin_category + "/" + brand_id,
        data: '',
        success: function(new_margin_percentage) {
            /*console.log(JSON.stringify(new_margin_percentage));*/
            $("#add_to_landed_cost").empty();
            $("#add_to_landed_cost").append($("<option></option>").attr("value", "").text("** Please select a Percentage"));
            
            $.each(new_margin_percentage, function(value, key) {
                $("#add_to_landed_cost").append($("<option></option>").attr("value", value).text(key));
            });
            $('#add_to_landed_cost').attr("disabled", false);
        },
        error: function(e) {
            console.log(e);
        }
    });
}