$(document).ready(function() {
    
    var requestTable;

    fetch_data();
    function fetch_data() {
        requestTable = $('#requestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/getoffers",
                data: {
                    rid: $("#roleID").val(),
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
                data: "job_id",
                name: 'name',
                orderable: true,
                searchable: true,
            },
            {
                data: "offer_id",
                name: 'position',
                orderable: true,
                searchable: true,
            }, {
                data: "description",
                name: 'description',
                orderable: true,
                searchable: true
            }, {
                data: "salary_range",
                name: 'salary',
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

});