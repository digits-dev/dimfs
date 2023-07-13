jQuery(function() {

    $('#digits_code').focusout( function() {
        if(add_action){
            //check digits code if existing
            $.ajax({
                url: ADMIN_PATH + "/getExistingDigitsCode",
                dataType: "json",
                type: "POST",
                data: {
                    "_token": $('#token').val(),
                    "digits_code": $(this).val(),
                },
                success: function(data) {
                    if (data.status_no === 0) {
                        $('#digits_code').css('border-color', 'red');
                        $('#id_digits_code').text('*Please change your item.');
                        $('#id_digits_code').css('color', 'red');
                        swal('Warning !', "Item not found in Item Master!");
                    }
                    else{
                        $('#id_digits_code').css('color', 'black');
                        $('#id_digits_code').text('');
                        $('#digits_code').css('border-color', 'black');
                    }
                },
                error: function(e) {
                    console.log(e);
                }
            });
        }
    });
    
    $('#price_change').focusout( function() {
        if(add_action || edit_action){
            //check price change > current srp
            $.ajax({
                url: ADMIN_PATH + "/compareCurrentSRP",
                dataType: "json",
                type: "POST",
                data: {
                    "_token": $('#token').val(),
                    "digits_code": $('#digits_code').val(),
                    "price_change": $(this).val(),
                },
                success: function(data) {
                    if (data.status_no === 0) {
                        $('#price_change').css('border-color', 'red');
                        swal('Warning !', "Price change can't be greater than current srp!");
                    }
                    else{
                        $('#price_change').css('border-color', 'black');
                    }
                },
                error: function(e) {
                    console.log(e);
                }
            });
        }
    });
    
});