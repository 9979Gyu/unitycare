$(document).ready(function() {
    $("input").attr("readonly", true);
    $("#submitBtn").hide();

    $("#editBtn").click(function(){
        $("#username").attr("readonly", false);
        $("#email").attr("readonly", false);

        // Show submit button and hide edit button
        $("#submitBtn").show();
        $("#editBtn").hide();
    });

});