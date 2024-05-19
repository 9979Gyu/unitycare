$(document).ready(function(){

    $('.select2').select2();

    // To get list of job name
    $.ajax({
        url: '/getJobs',
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

    $("#postalCode").on('change', function(){
        var postcode = $(this).val();
        if(postcode){
            $.ajax({
                url: '/search',
                type: 'GET',
                data: {postcode: postcode},
                success: function(data){
                    $('#state').empty();
                    $("#city").empty();
                    data.forEach(function(item){
                        $("#state").append('<option>' + item.state + '</option>');
                        $("#city").append('<option>' + item.city + '</option>');
                    });
                }
            });
        }
        else{
            $('#state').empty();
            $("#city").empty();
            $("#state").append('<option selected>Pilih Negeri</option>');
            $("#city").append('<option selected>Pilih Bandar</option>');
        }
    });

    
});