$(document).ready(function(){

    today = todayDate();
    $("#start_date").attr("min", today);
    $("#end_date").attr("min", today);
    $("#close_date").attr("min", today);

    // To ensure end date always after start date
    $("#start_date").change(function(){
        start = $(this).val();
        end = $("#end_date").val();
        $("#end_date").attr("min", start);

        if(end){
            if(end < start){
                $("#end_date").val($(this).val());
            }
        }

        startT = $("#start_time").val();
        endT = $("#end_time").val();

        if(start == end && startT > endT){
            alert("Masa mula tidak boleh melebihi masa tamat");
            $("#end_time").val("");
        }
    });

    // To ensure end time always after start time if the date is same
    $("#end_time").change(function(){
        start = $("#start_time").val();
        end = $("#end_time").val();

        if(start > end){
            alert("Masa mula tidak boleh melebihi masa tamat");
            $("#end_time").val("");
        }
    });

});