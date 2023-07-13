$('#classes_id').on('change', function() {
    
    if(add_action){
        $.ajax({
            type: 'GET',
            url: ADMIN_PATH + "/getCategoryClassCode/" + this.value,
            data: '',
            success: function(data) {
                $('#subclass_description').val(data);
                $('#subclass_description').focus();
            },
            error: function(e) {
                console.log(e);
            }
        });
    }
    else{
        $('#classes_id option:not(:selected)').prop('disabled', true);
    }
        
});