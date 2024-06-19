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

    $('#startDate1').on('change', function(){
        var startDate = $('#startDate1').val();
        var endDate = $('#endDate1').val();

        if (startDate > endDate) {
            $('#endDate1').attr('min', startDate);
            $('#endDate1').val(startDate);
        }
        else{
            $('#endDate1').attr('min', '');
        }
    });

    $('#endDate1').on('change', function(){
        var startDate = $('#startDate1').val();
        var endDate = $('#endDate1').val();
  
        if (startDate > endDate) {
            $('#endDate1').attr('min', startDate);
            $('#endDate1').val(startDate);
        }
        else{
            $('#endDate1').attr('min', '');
        }
    });

});