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

    $('#submit').click(function() {
        var reason = $("#reason").val();

        if (selectedID && reason) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/storeapplication",
                data: { 
                    reason: reason,
                    selectedID : selectedID 
                },
                success: function(data) {
                    $('#applyModal').modal('hide');
                    $('.condition-message').html(data);
                    location.reload(true);
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

    
});