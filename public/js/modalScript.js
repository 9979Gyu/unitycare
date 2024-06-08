$(document).ready(function(){

    $("#excelBtn").click(function(){
        $("#dateModal").modal('show');
    });

    $('#applyDates').on('click', function(event) {
        var startDate = new Date($('#startData').val());
        var endDate = new Date($('#endDate').val());

        if (startDate > endDate) {
            alert('End date must be greater than start date.');
            event.preventDefault();
        }
    });

});