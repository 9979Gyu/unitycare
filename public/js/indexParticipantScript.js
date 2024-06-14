$(document).ready(function() {

    $('.select2').select2();

    var requestTable;
    var selectedState = 4;
    var selectedID = $('#program').val();

    $("#program").on('change', function(){
        // Get the selected program
        selectedID = $(this).val();

        // Call fetch_data() with the selected program
        fetch_data(selectedState, selectedID); 
    });

    $('#allRadio, #volunteerRadio, #poorRadio, #deleteRadio').change(function() {
        if ($('#allRadio').is(':checked')) {
            selectedState = 4;
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
        fetch_data(selectedState, selectedID);
    });

    function fetch_data(selectedState, selectedID) {

        // Make AJAX request to fetch data based on the selected program
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
                url: "/getparticipants",
                data: {
                    rid: $("#roleID").val(),
                    programID: selectedID,
                    selectedState: selectedState
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
                    return row.username + ' <br>' + row.useremail + ' <br>+60' + row.usercontact;
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
                data: function(row) {
                    return parseDate(row.created_at);
                },
                name: 'applied_date',
                orderable: true,
                searchable: true
            }, {
                data: "name",
                name: 'name',
                orderable: true,
                searchable: true
            },  ]
            
        });
    }

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

});