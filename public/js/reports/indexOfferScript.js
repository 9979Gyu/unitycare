$(document).ready(function() {

    // Disabled the Tolak button in modal
    $("#decline").prop("disabled", true);
    // Hide the explaination input field
    $("#more").hide();

    // Initialize select2
    $('#organization').select2({
        placeholder: 'Pilih Organisasi',
    });

    $('#jobname').select2({
        placeholder: 'Pilih Pekerjaan',
    });

    $('#position').select2({
        placeholder: 'Pilih Jawatan',
    });

    // Define and declare variables
    var requestTable;
    var selectedState = 3;
    var selectedUser = "all";
    var selectedJob = "all";
    var selectedPosition = "all";
    var status = 1;
    var roleID = $("#roleID").val();
    var startDate = "";
    var endDate = "";

    $("#organization").change(function(){
        // set value
        selectedUser = $("#organization option:selected").val();

        // get job list for dropdown
        getJob(selectedUser, "#jobname");

        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);

    });
    
    $("#organization").prop('selectedIndex', 0).trigger('change');

    $("#jobname").on('change', function(){
        // set value
        selectedJob = $("#jobname option:selected").val();
        // get job list for dropdown
        getPosition(selectedJob, selectedUser, "#position");
    });

    $("#position").on('change', function(){
        // set value
        selectedPosition = $("#position option:selected").val();

        // get job list for dropdown
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);
    });

    $("#startDate1, #endDate1").change(function(){

        startDate = $("#startDate1").val();
        endDate = $("#endDate1").val();

        if(endDate == ""){
            endDate = startDate;
        }
        // Fetch data
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);
    });
    
    // Function to handle radio button value
    $('#allRadio, #pendingRadio, #approveRadio, #declineRadio, #deleteRadio').change(function() {

        if ($('#allRadio').is(':checked')) {
            selectedState = 3;
        }
        else if ($('#pendingRadio').is(':checked')) {
            selectedState = 1;
        } 
        else if ($('#approveRadio').is(':checked')) {
            selectedState = 2;
        } 
        else if ($('#declineRadio').is(':checked')) {
            selectedState = 0;
        }
        else if ($('#deleteRadio').is(':checked')) {
            selectedState = 4;
        }

        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);

    });
    
    function fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate) {

        updateOfferBarChart(selectedUser, selectedPosition, selectedState, startDate, endDate);

        // Make AJAX request to fetch data based on the selected position
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
                url: "/getoffersbyposition",
                data: {
                    selectedUser : selectedUser, 
                    selectedPosition : selectedPosition, 
                    selectedState : selectedState, 
                    status : status,
                    startDate: startDate,
                    endDate: endDate,
                },
                type: 'GET',

            },
            'columnDefs': [{
                "targets": [0],
                "className": "text-center",
                "width": "2%"
            }, {
                "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
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
                data: function(row){
                    return row.jobname + ' - ' + row.jobposition
                },
                name: 'name',
                orderable: true,
                searchable: true,
            }, {
                data: "typename",
                name: 'type',
                orderable: true,
                searchable: true,
            }, {
                data: "shiftname",
                name: 'shift',
                orderable: true,
                searchable: true,
            }, {
                data: 'address',
                name: 'location',
                orderable: true,
                searchable: true
            }, {
                data: 'start',
                name: 'startdatetime',
                orderable: true,
                searchable: true
            }, {
                data: 'end',
                name: 'enddatetime',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return 'RM ' + numberWithCommas(row.min_salary) + ' - RM ' + numberWithCommas(row.max_salary);
                },
                name: 'salary',
                orderable: true,
                searchable: true
            }, {
                data: "people",
                name: 'quantity',
                orderable: true,
                searchable: true
            }, {
                data: "description",
                name: 'description',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return row.username + 
                    '<br>' + row.useremail + 
                    '<br>+60' + row.usercontact;
                },
                name: 'contact',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    if(row.approval_status == 0){
                        return '<span class="text-danger"><b>' + row.approval + '</b></span>';
                    }
                    else if(row.approval_status == 2){
                        return '<span class="text-success"><b>' + row.approval + '</b></span>';
                    }
                    else{
                        return '<span><b>' + row.approval + '</b></span>';
                    }
                },
                name: 'approval',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    if(row.processedname != null){
                        return row.processedname + 
                        '<br>' + row.processedemail +
                        '<br> Pada: ' + row.approved_at;
                    }
                    else{
                        return " ";
                    }
                },
                name: 'processed',
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

    // Set interval to refresh data every 10 minutes
    setInterval(function() {
        fetch_data(selectedUser, selectedPosition, selectedState, startDate, endDate);
    }, 10 * 60 * 1000);
    
    $("#resetBtn").click(function(){
        $("#organization").trigger('change');
        $("#jobname").trigger('change');

        $('#startDate1').val('');
        $('#endDate1').val('').trigger('change');
    });

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
                url: "/deleteoffer",
                data: { offerID : selectedID },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    $('.condition-message').html(response);

                    requestTable.ajax.reload();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error(xhr.responseText);
                }
            })
        }
    });

    // Function to approve job offer
    $(document).on('click', '.approveAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#approve').click(function() {

        $.ajax({
            type: 'POST',
            dataType: 'html',
            data: {
                approval_status : 2,
                offerID : selectedID
            },
            url: "/approval",
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
            url: "/approval",
            data: {
                approval_status : 0,
                reason: declineReason,
                offerID: selectedID
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

    // Function to approve program
    $(document).on('click', '.boostAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#boost').click(function() {
        
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: "/boostOffer",
            data: { offerID : selectedID },
            success: function(data) {
                $('#boostModal').modal('hide');
                $('.condition-message').html(data);

                requestTable.ajax.reload();
            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });
    });

});