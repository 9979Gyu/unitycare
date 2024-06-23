$(document).ready(function() {
    
    $("input").attr("readonly", true);
    $("#image").attr("readonly", false);

    $("#submitBtn").prop('disabled', true);
    $("#submitBtn").hide();

    // Show submit button and hide edit button
    $("#editBtn").click(function(e){
        e.preventDefault();
        $(this).prop('disabled', true);
        $(this).hide();

        $("input").attr("readonly", false);
        $("#name").attr("readonly", true);
        $("#ICNo").attr("readonly", true);
        $("#eduName").attr("readonly", true);

        $("#submitBtn").show();
        $("#submitBtn").prop('disabled', false);
    });

    $("#image").change(function(){
        var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        var file = this.files[0];

        if(this.value != null && file != null){
            if (!allowedTypes.includes(file.type)) {
                alert('Sila pilih fail imej yang sah (JPEG, PNG, atau GIF).');
                this.value = '';
            }
        }
        else{
            this.value = '';
        }

    });

});