$(document).ready(function() {

    $('.select2').select2();

    // To get list of job name
    $.ajax({
        url: '/getJobsFromDB',
        type: 'GET',
        success: function(data){
            
            $('#job').empty();

            // Check if data is an array
            // Assuming data is an array of job objects with properties 'job_id' and 'name'
            data.forEach(function(job){
                // Append each job as an option to the job select element
                $("#job").append('<option value="' + job.job_id + '">' + job.name + '</option>');
            });

            $('#job').trigger("change");

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

    $("#position").on('change', function(){
        var selectedPosition = $(this).val(); // Get the selected position
        fetch_data(selectedPosition); // Call fetch_data() with the selected position
    });

    $('#pendingCheckBox, #approveCheckBox, #declineCheckBox').change(function() {
        var selectedState = null;
        if ($('#pendingCheckBox').is(':checked')) {
            selectedState = 1;
        } 
        else if ($('#approveCheckBox').is(':checked')) {
            selectedState = 2;
        } 
        else if ($('#declineCheckBox').is(':checked')) {
            selectedState = 0;
        }
        
        // Fetch data based on the selected position
        fetch_data(selectedState);
    });

    // function fetch_data(selectedState) {
    
    //     console.log("this " + selectedState);

    //     // Make AJAX request to fetch data based on the selected position
    //     $.ajax({
    //         url: '/getapplications',
    //         type: 'GET',
    //         data: {
    //             selectedState: selectedState
    //         },
    //         success: function(data) {
    //             // Reload DataTable with new data
    //             $('#requestTable').DataTable().clear().rows.add(data).draw();
    //         },
    //         error: function(jqXHR, textStatus, errorThrown) {
    //             console.error("AJAX request failed:", textStatus, errorThrown);
    //         }
    //     });
    // }

    // Disabled the Tolak button in modal
    $("#decline").prop("disabled", true);
    // Hide the explaination input field
    $("#more").hide();

    var requestTable;

    fetch_data("");
    function fetch_data(selectedPosition) {

        if ($.fn.DataTable.isDataTable('#requestTable')) {
            // If DataTable already initialized, destroy it
            $('#requestTable').DataTable().destroy();
        }

        requestTable = $('#requestTable').DataTable({
            language: {
                "sEmptyTable":     "Tiada data tersedia dalam jadual",
                "sInfo":           "Memaparkan _START_ hingga _END_ daripada _TOTAL_ rekod",
                "sInfoEmpty":      "Memaparkan 0 hingga 0 daripada 0 rekod",
                "sInfoFiltered":   "(ditapis daripada jumlah _MAX_ rekod)",
                "sInfoPostFix":    "",
                "sInfoThousands":  ",",
                "sLengthMenu":     "Tunjukkan _MENU_ rekod",
                "sLoadingRecords": "Sedang memuatkan...",
                "sProcessing":     "Sedang memproses...",
                "sSearch":         "Cari:",
                "sZeroRecords":    "Tiada padanan rekod yang dijumpai",
                "oPaginate": {
                    "sFirst":    "<<",
                    "sLast":     ">>",
                    "sNext":     ">",
                    "sPrevious": "<"
                },
                "oAria": {
                    "sSortAscending":  ": diaktifkan kepada susunan lajur menaik",
                    "sSortDescending": ": diaktifkan kepada susunan lajur menurun"
                }
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: "/getapplications",
                data: {
                    rid: $("#roleID").val(),
                    position: selectedPosition
                },
                type: 'GET',

            },
            'columnDefs': [{
                "targets": [0],
                "className": "text-center",
                "width": "2%"
            }, {
                "targets": [1, 2, 3, 4, 5],
                "className": "text-center",
            },], 
            columns: [{
                "data": null,
                searchable: false,
                "sortable": true,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            }, {
                data: function(row) {
                    return row.useremail + ' <br>+60' + row.usercontact;
                },
                name: 'user',
                orderable: true,
                searchable: true,
            }, {
                data: "edu_level",
                name: 'edu_level',
                orderable: true,
                searchable: true,
            }, {
                data: "category",
                name: 'category',
                orderable: true,
                searchable: true,
            }, {
                data: function(row) {
                    if(row.reason != "" && row.description != "")
                        return row.description + ' <br><b>Ditolak: ' + row.reason + '</b>';
                    else if(row.reason != "" && row.description == "")
                        return '<b>Ditolak: ' + row.reason + '</b>';
                    else
                        return row.description;
                },
                name: 'description',
                orderable: false,
                searchable: true,
            }, {
                data: "applied_date",
                name: 'applied_date',
                orderable: true,
                searchable: true
            }, {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }, ]
            
        });
    }

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var selectedID;
    $(document).on('click', '.deleteAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#delete').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/deleteprogram",
                data: { selectedID : selectedID },
                success: function(data) {
                    $('#deleteModal').modal('hide');
                    $('.condition-message').html(data);

                    requestTable.ajax.reload();
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

    $(document).on('click', '.approveAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#approve').click(function() {
        
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/approveapplication",
            data: {selectedID : selectedID },
            success: function(data) {
                $('#approveModal').modal('hide');
                $('.condition-message').html(data);

                requestTable.ajax.reload();
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    });

    $(document).on('click', '.declineAnchor', function() {
        selectedID = $(this).attr('id');
    });

    var declineReason = "";
    
    $("#reason").change(function() {

        // Disabled the Tolak button in modal
        $("#decline").prop("disabled", true);
        // Hide the explaination input field
        $("#explain").val("");
        $("#more").hide();

        // If select "lain-lain"
        if($(this).val() == "others"){
            $("#more").show();
            declineReason = "";
        }
        else{
            if($(this).val() !== "0"){
                // Enable button
                $("#decline").prop("disabled", false); 
                
                declineReason = "";
                
                if($(this).val() == "missing")
                    declineReason = "Kekurangan maklumat"; 
                else if($(this).val() == "unclear")
                    declineReason = "Penerangan tidak jelas"; 
                
            }
        }
    });

    $("#explain").change(function(){
        // Check if the field has any value
        if ($(this).val().trim() !== "") {
            // Enable button
            $("#decline").prop("disabled", false); 
            declineReason += $(this).val();
        } 
        else {
            // Disable button
            $("#decline").prop("disabled", true); 
        }
    });

    $('#decline').click(function() {

        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/declineapplication",
            data: {
                reason: declineReason,
                selectedID: selectedID
            },
            success: function(data) {
                $('#declineModal').modal('hide');
                $('.condition-message').html(data);

                requestTable.ajax.reload();
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    });

});