@extends('layouts.app')
@section('title')
    UnityCare-{{ $rolename }}
@endsection

@section('content')
    
    <h2>{{ $rolename }}</h2>
    <br>

    @if (session()->has('success'))
        <div class="alert alert-success condition-message">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger condition-message">
            {{ session('error') }}
        </div>
    @endif

    <input type="number" id="roleID" value="{{ $users[0]->roleID }}" hidden>
    <div class="table-responsive">
        <table id="requestTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead>
                <tr style="text-align:center">
                    <th> No. </th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Contact No (60+)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Remove {{ $rolename }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            Are you sure to remove?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" id="delete">Delete</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <script>
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

        $('#delete').click(function() {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/deleteuser/" + $(".deleteAnchor").attr('id'),
                success: function(data) {
                    $('#deleteModal').modal('hide');
                    $('.condition-message').html(data);

                    requestTable.ajax.reload();
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            })
        });

    });
    </script>

@endsection