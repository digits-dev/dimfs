$('#category_description').on('keyup mouseenter', function() {

    var count = this.value.length;
    $("#id_category_description").html('Character Count: '+ count);
    
    if(count >= 30) {
        $("#id_category_description").css("color", "red");
        $('#category_description').css('border-color', 'red');
        $('#category_description').focus();
    }
    else if(count == 0) {
        $("#id_category_description").html('');
        $('#category_description').css('border-color', 'gray');
    }
    else {
        $("#id_category_description").css("color", "black");
        $('#category_description').css('border-color', 'gray');
    }
        
});