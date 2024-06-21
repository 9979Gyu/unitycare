$(document).ready(function(){

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#apply').click(function() {
        $('#applyModal').modal('show');
    });

    $('#approve').click(function() {
        $('#approveModal').modal('show');
    });

    $('#decline').click(function() {
        $('#declineModal').modal('show');
    });

    $('#dismiss').click(function() {
        $('#dismissModal').modal('show');
    });
});