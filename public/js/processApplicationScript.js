$(document).ready(function(){

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var selectedID;
    $('#apply').click(function() {
        selectedID = $("#offerId").val();
        $('#applyModal').modal('show');
    });


    
});