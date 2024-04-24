@extends('layouts.app')
@section('title')
    UnityCare-Program
@endsection

@section('content')
    
    <h2>Program</h2>
    <br>

    @if (session()->has('success'))
        <div class="alert alert-success condition-message">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(Auth::user()->roleID != 5)
    <button class="btn btn-info float-end" type="button" id="addBtn" onclick="window.location='/createprogram/{{ Auth::user()->roleID }}'" >
       Tambah
    </button>
    @endif

    <br>

    <input type="number" id="roleID" value="{{ Auth::user()->roleID }}" hidden>

        <div class="card-container">
        @foreach($programs as $row)
            <br>
            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">{{ $row->name }}</h5>
                        <p class="card-text">{{ $row->description }}</p>
                        <p class="card-text">Tempat: {{ $row->venue }}</p>
                        <p class="card-text">Kejadian: {{ $row->start_date }} {{ $row->start_time }}</p>
                        <p class="card-text">Tarikh Tutup: {{ $row->close_date }}</p>
                        <p class="card-text">Hubungi: <br> {{ $row->username }} <br> +60{{ $row->contact_no }} <br> {{ $row->useremail }}</p>
                    </div>

                    <div>  
                        @if(Auth::user()->roleID == 1)
                            @if($row->approved_status == 1)
                            <a class="approveAnchor btn btn-success" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#approveModal">Lulus</a>
                            <a class="deleteAnchor btn btn-danger" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#declineModal">Tolak</a>
                            <a href="/editprogram/{{ $row->program_id }}" class="btn btn-warning">Kemaskini</a>
                            <a class="deleteAnchor btn btn-danger" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a>
                            @elseif($row->approved_status == 2)
                            <a href="/joinprogram/{{ $row->program_id }}" class="btn btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                    <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                Mohon
                            </a>
                            @endif
                        @elseif(Auth::user()->roleID == 2)
                            @if($row->approved_status == 1)
                                <a class="approveAnchor btn btn-success" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#approveModal">Lulus</a>
                                <a class="deleteAnchor btn btn-danger" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#declineModal">Tolak</a>
                                @if($row->user_id == Auth::user()->id)
                                <a href="/editprogram/{{ $row->program_id }}" class="btn btn-warning">Kemaskini</a>
                                <a class="deleteAnchor btn btn-danger" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a>
                                @endif
                            @elseif($row->approved_status == 2)
                            <a href="/joinprogram/{{ $row->program_id }}" class="btn btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                    <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                Mohon
                            </a>
                            @endif
                        @elseif(Auth::user()->roleID == 5)
                            <a href="/joinprogram/{{ $row->program_id }}" class="btn btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                    <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                Mohon
                            </a>
                        @else
                            @if($row->approved_status <= 1 && $row->user_id == Auth::user()->id)
                                <a href="/editprogram/{{ $row->program_id }}" class="btn btn-warning">Kemaskini</a>
                                <a class="deleteAnchor btn btn-danger" href="#" id="{{ $row->program_id }}" data-bs-toggle="modal" data-bs-target="#deleteModal">Padam</a>
                            @else
                                <a href="/joinprogram/{{ $row->program_id }}" class="btn btn-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                        <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                                    </svg>
                                    Mohon
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Lulus Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk meluluskan program?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="approve">Lulus</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Padam Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk memadam program?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete">Padam</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {

        // csrf token for ajax
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var selectedID;
        $(document).on('click', '.deleteAnchor', function() {
            selectedID = $(this).attr('id');
            console.log(selectedID);
        });

        $(document).on('click', '.approveAnchor', function() {
            selectedID = $(this).attr('id');
            console.log("this" + selectedID);
        });

        $('#delete').click(function() {
            if (selectedID) {
                $.ajax({
                    type: 'POST',
                    dataType: 'html',
                    url: "/deleteprogram/" + selectedID,
                    success: function(data) {
                        $('#deleteModal').modal('hide');
                        $('.condition-message').html(data);

                        // Fetch the updated programs
                        // $.ajax({
                        //     type: 'GET',
                        //     url: "/getUpdatedPrograms",
                        //     success: function(response) {
                        //         // Replace the current programs with the updated ones
                        //         $('.card-container').html(response.cardContent);
                        //     },
                        //     error: function(error) {
                        //         console.log(error);
                        //     }
                        // });
                        location.reload();
                    },
                    error: function (data) {
                        $('.condition-message').html(data);
                    }
                })
            }
        });

        $('#approve').click(function() {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: "/approveprogram/" + selectedID,
                success: function(data) {
                    $('#approveModal').modal('hide');
                    $('.condition-message').html(data);

                    location.reload();
                },
                error: function (data) {
                    $('.condition-message').html(data);
                }
            });
        });

    });
    </script>

@endsection