// Using
$(document).ready(function() {

    // Initialize select2
    $('#organization').select2({
        placeholder: 'Pilih Penganjur',
    });

    $('#program').select2({
        placeholder: 'Pilih Program',
    });

    var requestParticipantTable;
    var selectedState = 1;
    var selectedUser = "all";
    var selectedProgram = "all";
    var startDate = '';
    var endDate = '';

    $("#organization").on('change', function(){

        selectedUser = $(this).val();

        $.ajax({
            method: 'GET',
            dataType: 'json',
            data: {selectedUser : selectedUser},
            url: "/getProgramsByUserID",
            success: function(response) {

                $("#program").empty();
                $("#program").append('<option value="all">Semua Program</option>');
    
                response.forEach(function(item){
                    $("#program").append('<option value="' + item.program_id + '">' + item.name + '</option>');
                });
    

            },
            error: function (data) {
                $('.condition-message').html(data);
            }
        });

        fetch_data(selectedState, selectedUser, selectedProgram, startDate, endDate);
    });

    $("#startDate1, #endDate1").change(function(){

        startDate = $("#startDate1").val();
        endDate = $("#endDate1").val();
        if(endDate == ""){
            endDate = startDate;
        }

        // Fetch data based on the selected position
        fetch_data(selectedState, selectedUser, selectedProgram, startDate, endDate);
    });
    
    $("#program").on('change', function(){
        // Get the selected program
        selectedProgram = $(this).val();

        // Call fetch_data() with the selected program
        fetch_data(selectedState, selectedUser, selectedProgram, startDate, endDate);
    });

    $('#allRadio, #volunteerRadio, #poorRadio, #deleteRadio').change(function() {
        if ($('#allRadio').is(':checked')) {
            selectedState = 1;
        }
        else if ($('#volunteerRadio').is(':checked')) {
            selectedState = 2;
        } 
        else if ($('#poorRadio').is(':checked')) {
            selectedState = 3;
        } 
        else if ($('#deleteRadio').is(':checked')) {
            selectedState = 0;
        }
        
        // Fetch data based on the selected position
        fetch_data(selectedState, selectedUser, selectedProgram, startDate, endDate);
    });


    $("#organization").prop('selectedIndex', 0).trigger('change');
    $("#program").prop('selectedIndex', 0);


    function fetch_data(selectedState, selectedUser, selectedProgram, startDate, endDate) {

        updateParticipantPieChart(selectedState, selectedUser, selectedProgram, startDate, endDate); 
        updateParticipantBarChart(selectedState, selectedUser, selectedProgram, startDate, endDate);

        // Make AJAX request to fetch data based on the selected program
        if ($.fn.DataTable.isDataTable('#requestParticipantTable')) {
            // If DataTable already initialized, destroy it
            $('#requestParticipantTable').DataTable().destroy();
        }

        requestParticipantTable = $('#requestParticipantTable').DataTable({
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
                url: "/getParticipantsDatatable",
                data: {
                    programID: selectedProgram,
                    userID : selectedUser,
                    state: selectedState,
                    startDate: startDate, 
                    endDate: endDate
                },
                type: 'GET',

            },
            'columnDefs': [{
                "targets": [0],
                "className": "text-center",
                "width": "2%"
            }, {
                "targets": [1, 2, 3, 4, 5, 6, 7, 8],
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
                    return row.joined_username + ' <br>' + row.joined_useremail;
                },
                name: 'user',
                orderable: true,
                searchable: true,
            }, {
                data: "category",
                name: 'category',
                orderable: true,
                searchable: true,
            }, {
                data: 'typename',
                name: 'typename',
                orderable: true,
                searchable: true
            }, {
                data: 'applied_date',
                name: 'applied_date',
                orderable: true,
                searchable: true
            }, {
                data: "program_name",
                name: 'programName',
                orderable: true,
                searchable: true
            }, {
                data: "programtype",
                name: 'programType',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return row.program_creator_name + ' <br>' + row.program_creator_email;
                },
                name: 'creator',
                orderable: true,
                searchable: true,
            }, {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
            },]
            
        });
    }

    var selectedID;
    $(document).on('click', '.printAnchor', function() {
        selectedID = $(this).attr('id');
        $('#participantID').val(selectedID);
    });

    $(document).on('click', '.dismissAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $('#dismiss').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/dismissprogram",
                data: { 
                    selectedID : selectedID,
                },
                success: function(data) {
                    $('#dismissModal').modal('hide');
                    $('.condition-message').html("Berjaya tarik diri");
                    requestParticipantTable.ajax.reload();

                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        }
    });

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

});