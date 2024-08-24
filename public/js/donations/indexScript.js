$(document).ready(function(){

    var requestTable;
    var startDate = '';
    var endDate = '';

    // Initial call
    fetch_data(startDate, endDate);

    $("#startDate1, #endDate1").change(function(){
        startDate = $("#startDate1").val();
        endDate = $("#endDate1").val();

        if(endDate == ""){
            endDate = startDate;
        }
        // Fetch data
        fetch_data(startDate, endDate);
    });

    function fetch_data(startDate, endDate){
        
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
                url: "/getTransactionDatatable",
                data: {
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
                "targets": [1, 2, 3, 4, 5, 6],
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
                data: 'reference_no',
                name: 'reference_no',
                orderable: true,
                searchable: true
            }, {
                data: 'payer_name',
                name: 'name',
                orderable: true,
                searchable: true,
            },  {
                data: 'references',
                name: 'references',
                orderable: true,
                searchable: true,
            }, {
                data: 'formatted_amount',
                name: 'amount',
                orderable: true,
                searchable: true
            }, {
                data: 'formatted_created_at',
                name: 'formatted_created_at',
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

    $("#resetBtn").click(function(){
        $('#startDate1').val('');
        $('#endDate1').val('').trigger('change');
    });

    var selectedID;
    $(document).on('click', '.deleteAnchor', function() {
        selectedID = $(this).attr('id');
    });

    $(document).on('click', '.printAnchor', function() {
        selectedID = $(this).attr('id');
        $('#referenceNo').val(selectedID);
    });

    $('#delete').click(function() {
        if (selectedID) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/delete-transaction",
                data: { payment_id : selectedID },
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

    // csrf token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

});