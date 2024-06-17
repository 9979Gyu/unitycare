$(document).ready(function(){

    $("#excelBtn").click(function(){
        $("#dateModal").modal('show');
    });

    $('#startDate').on('change', function(){
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();

        if (startDate > endDate) {
            $('#endDate').attr('min', startDate);
            $('#endDate').val(startDate);
        }
        else{
            $('#endDate').attr('min', '');
        }
    });

    $('#endDate').on('change', function(){
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
  
        if (startDate > endDate) {
            $('#endDate').attr('min', startDate);
            $('#endDate').val(startDate);
        }
        else{
            $('#endDate').attr('min', '');
        }
    });

});