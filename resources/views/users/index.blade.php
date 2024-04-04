@extends('layouts.app')
@section('title')
    UnityCare-Staff
@endsection

@section('content')
    
    <h2>Staff</h2>
    <br>

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table id="requestTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead>
                <tr style="text-align:center">
                    <th> No. </th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Contact No</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <script>
    $(document).ready(function() {
        console.log("hi");
        fetch_data();
        function fetch_data() {
            $('#requestTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/getstaff",
                    data: {
                        rid: 2,
                    },
                    type: 'GET',

                },
                'columnDefs': [{
                    "targets": [0], // your case first column
                    "className": "text-center",
                    "width": "2%"
                }, {
                    "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9], // your case first column
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
                    searchable: true
                },{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }, ]
                
            });
            console.log("123");
        }

        // csrf token for ajax
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
    </script>

@endsection