$(document).ready(function(){
    // Initialize select2
    $('.select2').select2();

    // Declare variables
    var requestTable;
    var selectedState = 3;
    var selectedUser;
    var selectedType = $("#type option:selected").val();
    var status = 1;

    $("#organization").change(function(){
        selectedUser = $("#organization option:selected").val();

        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedState, selectedType, status);
    });

    $("#type").change(function(){
        selectedType = $("#type option:selected").val();

        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedState, selectedType, status);
    });

    $('#allRadio, #pendingRadio, #approveRadio, #declineRadio, #deleteRadio').change(function() {
        status = 1;

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
            status = 0
        }
        
        // Fetch data based on the selected position
        fetch_data(selectedUser, selectedState, selectedType, status);
    });

    function fetch_data(selectedUser, selectedState, selectedType, status){

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
                url: "/getProgramsWithSpecs",
                data: {
                    selectedUser: selectedUser,
                    selectedState: selectedState,
                    selectedType: selectedType,
                    status: status
                },
                type: 'GET',
            },
            'columnDefs': [{
                "targets": [0],
                "className": "text-center",
                "width": "2%"
            }, {
                "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
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
                data: "name",
                name: 'name',
                orderable: true,
                searchable: true,
            }, {
                data: 'typename',
                name: 'typename',
                orderable: true,
                searchable: true,
            }, {
                data: 'description',
                name: 'description',
                orderable: true,
                searchable: true,
            }, {
                data: 'address',
                name: 'address',
                orderable: true,
                searchable: true,
            }, {
                data: 'start',
                name: 'start_datetime',
                orderable: true,
                searchable: true
            }, {
                data: 'end',
                name: 'end_datetime',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    if(row.user_type_id == 2){
                        return row.participant + ' (Sukarelawan)';
                    }
                    else if(row.user_type_id == 3){
                        return row.participant + ' (B40/OKU)';
                    }
                },
                name: 'participants',
                orderable: true,
                searchable: true
            }, 
            // {
            //     data: 'poor',
            //     name: 'poor',
            //     orderable: true,
            //     searchable: true
            // }, 
            {
                data: 'close_date',
                name: 'close_date',
                orderable: true,
                searchable: true
            }, {
                data: function(row) {
                    return row.username.toUpperCase() + 
                    '<br>' + row.useremail + 
                    '<br>+60' + row.usercontact;
                },
                name: 'creator',
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

    // Function to remove program
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

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

});