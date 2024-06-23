$(document).ready(function() {
    
    $("input").attr("readonly", true);

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

});