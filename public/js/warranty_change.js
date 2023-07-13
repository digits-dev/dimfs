jQuery(function() {

    $('#digits_code').focusout( function() {
        if(add_action){
            //check digits code if existing
            $.ajax({
                type: 'GET',
                url: ADMIN_PATH + "/getExistingDigitsCode",
                dataType: "json",
                type: "POST",
                data: {
                    "_token": $('#token').val(),
                    "digits_code": $(this).val(),
                },
                success: function(data) {
                    if (data.status_no == 0) {
                        $('#digits_code').css('border-color', 'red');
                        $('#id_digits_code').text('*Please change your item.');
                        $('#id_digits_code').css('color', 'red');
                        swal('Information !', "Item not found in Item Master!");
                    }
                },
                error: function(e) {
                    console.log(e);
                }
            });
        }
    });
});