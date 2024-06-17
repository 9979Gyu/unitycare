function getJob(selectedUser, component){
    $.ajax({
        type: 'GET',
        url: "/getJobsByUser",
        data: { selectedUser: selectedUser },
        success: function(data) {
            $(component).empty();
            $(component).append('<option value="all" selected>Semua Pekerjaan</option>');
            data.forEach(function(item){
                $(component).append('<option value="' + item.name + '">' + item.name + '</option>');

            });
        },
        error: function (data) {
            $('.condition-message').html(data);
        }
    });
}

// Funciton to display list of city and states
function getPosition(selectedJob, selectedUser, component){
    $.ajax({
        type: 'GET',
        url: "/getPositions",
        data:  { 
            jobName : selectedJob,
            userID: selectedUser
        },
        success: function(data) {
            $(component).empty();
            
            if(selectedJob == "all"){
                $(component).append('<option value="all" selected>Semua Jawatan</option>');
            }
            else{
                $(component).append('<option value="0" selected>Pilih Jawatan</option>');
            }

            data.forEach(function(item){
                $(component).append('<option value="' + item.job_id + '">' + item.position + '</option>');
            });

            $(component).trigger('change');

        },
        error: function (data) {
            $('.condition-message').html(data);
        }
    });
}