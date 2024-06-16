$(document).ready(function(){

    $('.select2').select2();
    var selectedShift = $("#shiftType option:selected").val();

    today = todayDate();
    $("#start_date").attr("min", today);
    $("#end_date").attr("min", today);

    $('#start_date').on('change', function() {
        var startDate = $('#start_date').val();
        $("#end_date").attr("min", startDate);
    });

    // Event handler for input changes
    $('#end_date, #end_time').on('change', function() {

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();

        var isbool = validateDateTime(startDate, endDate, startTime, endTime);
        if (!isbool) {
            alert('Sila pilih masa dengan betul');
            $("#end_date").val('');
            $("#end_time").val('');
        }
        else{
            // Validate based on selectedShift value
            // Is not Waktu Flexible
            if (selectedShift != 6) {
                var start = moment(startTime, 'HH:mm');
                var end = moment(endTime, 'HH:mm');

                // Calculate duration in hours
                var durationMs = end.diff(start); // Difference in milliseconds
                var hours = durationMs / (1000 * 60 * 60); // Convert milliseconds to hours

                // Adjust for cases crossing midnight
                if (hours <= 0) {
                    hours += 24; // Add 24 hours to handle crossing midnight
                }

                // Validate duration
                if (hours > 9) {
                    alert('Untuk waktu biasa, syif malam dan syif petang, waktu bekerja tidak boleh melebihi 9 jam (8 jam kerja + 1 jam rehat).');
                    $('#end_time').val(''); // Clear end time field
                }
            }
        }

    });

    // To get list of job name
    $.ajax({
        url: '/getJobsFromDB',
        type: 'GET',
        success: function(data){
            $('#job').empty();

            data.forEach(function(item){
                $("#job").append('<option value="' + item.job_id + '">' + item.name + '</option>');
            });

            $('#job').trigger("change");
            $("#jobType").trigger('change');
            $("#shiftType").trigger('change');
        }
    });

    $('#jobType').on('change', function() {
        var selectedValue = $('#jobType option:selected').val();
        $('[data-toggle="tooltip' + selectedValue + '"]').tooltip();

        if(selectedValue == 1){
            $("#start").hide();
            $("#end").hide();
        }
        else{
            $("#start").show();
            $("#end").show();
        }
    });

    $("#job").on('change', function(){
        var jobName = $("#job option:selected").text();

        if(jobName){
            $.ajax({
                url: '/getAllPositions',
                type: 'GET',
                data: {jobName: jobName},
                success: function(data){
                    $('#position').empty();
                    data.forEach(function(item){
                        $("#position").append('<option value="' + item.job_id + '">' + item.position + '</option>');
                    });
                }
            });
        }
        else{
            $('#position').empty();
            $("#position").append('<option selected>Pilih Jawatan</option>');
        }
    });

    $("#shiftType").on('change', function(){
        selectedShift = $("#shiftType option:selected").val();

        // Reset min and max attributes initially
        $("#start_time").val("");
        $("#end_time").val("");

        // is Waktu biasa
        if(selectedShift == 1){
            $("#start_time").val('08:00');
            $("#end_time").val("17:00");
        }
        // is Syif Malam
        else if(selectedShift == 2){
            $("#start_time").val('22:00');
            $("#end_time").val("07:00");
        }
        // is Syif Petang
        else if(selectedShift == 3){
            $("#start_time").val('16:00');
            $("#end_time").val("01:00");
        }

        $('[data-toggle="tooltip' + selectedShift + '"]').tooltip();
    });
    
});