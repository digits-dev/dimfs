$("[name='margin_category']").val($("#margin_categories_id :selected").text());
$(".open-datetimepicker").css("pointer-events","none");
$("[name='submit']").remove();

    if(edit_action){
        
        if($("#margin_categories_id :selected").text() == "UNITS"){
            //autocompute store margin
            //get brand then compare to matrix
            $(function() {
                
                var csm = ($('#current_srp').val() - $('#store_cost').val())/$('#current_srp').val();
                if($('#promo_srp').val().length != 0){
                    csm = ($('#promo_srp').val() - $('#store_cost').val())/$('#promo_srp').val();
                }
                $('#store_cost_percentage').val(csm.toFixed(4));
                
            });
        }
        if($("#margin_categories_id :selected").text() == "ACCESSORIES"){
            //autocompute store margin
            //get brand then compare to matrix
            $(function() {
                var csm = ($('#current_srp').val() - $('#store_cost').val())/$('#current_srp').val();
                if($('#promo_srp').val().length != 0){
                    csm = ($('#promo_srp').val() - $('#store_cost').val())/$('#promo_srp').val();
                }
                $('#store_cost_percentage').val(csm.toFixed(4));
                
            });
        }
        
        $('#store_cost').on('keyup', function() {
            var csm = ($('#current_srp').val() - $('#store_cost').val())/$('#current_srp').val();
            if($('#promo_srp').val().length != 0){
                csm = ($('#promo_srp').val() - $('#store_cost').val())/$('#promo_srp').val();
            }
            $('#store_cost_percentage').val(csm.toFixed(4));
            
        });
        
        $('#working_store_cost').on('keyup', function() {
            var csm = ($('#current_srp').val() - $('#working_store_cost').val())/$('#current_srp').val();
            if($('#promo_srp').val().length != 0){
                csm = ($('#promo_srp').val() - $('#working_store_cost').val())/$('#promo_srp').val();
            }
            $('#working_store_cost_percentage').val(csm.toFixed(4));
            
        });
    }