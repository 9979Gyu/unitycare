$(document).ready(function() {

    $("input").attr("readonly", true);
    $("#submitBtn").prop('disabled', true);
    $("#submitBtn").hide();

    // Show submit button and hide edit button
    $("#editBtn").click(function(e){
        e.preventDefault();
        $(this).prop('disabled', true);
        $(this).hide();
        $("#username").attr("readonly", false);
        $("#email").attr("readonly", false);

        $("#submitBtn").show();
        $("#submitBtn").prop('disabled', false);
    });

});