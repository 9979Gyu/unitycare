$(document).ready(function() {

    var requestTable;

    fetch_data();
    function fetch_data() {
        requestTable = $('#requestTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/getstaff",
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
            
            order: [
                [1, 'asc']
            ],
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
                data: "email",
                name: 'email',
                orderable: true,
                searchable: true
            }, {
                data: "username",
                name: 'username',
                orderable: true,
                searchable: true
            }, {
                data: "contactNo",
                name: 'contactNo',
                orderable: true,
                searchable: true,
            },{
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

    var idToDelete;
    $(document).on('click', '.deleteAnchor', function() {
        idToDelete = $(this).attr('id');
        console.log(idToDelete);
    });

    $('#delete').click(function() {
        if (idToDelete) {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/deleteuser/" + idToDelete,
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