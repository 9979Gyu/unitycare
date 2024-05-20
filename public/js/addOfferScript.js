$(document).ready(function(){

    $('.select2').select2();

    // To get list of job name
    $.ajax({
        url: '/getJobsFromDB',
        type: 'GET',
        success: function(data){
            $('#job').empty();

            data.forEach(function(item){
                $("#job").append('<option value="' + item.job_id + '">' + item.name + '</option>');
                $('#job').trigger("change");
            });
        }
    });

    $("#job").on('change', function(){
        var jobName = $("#job option:selected").text();

        console.log(jobName);
        if(jobName){
            $.ajax({
                url: '/getPositions',
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
    
});