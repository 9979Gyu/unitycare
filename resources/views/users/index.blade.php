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

    <form method="POST" action="/export-users" id="excel">
        @csrf
        <input type="number" id="roleID" name="roleID" value="{{ $roleNo }}" hidden>
        <input type="text" id="roleName" name="roleName" value="{{ $rolename }}" hidden>
        <button class="btn btn-outline-primary float-end ml-2" type="submit" id="excelBtn">Excel</button>
        @if($roleNo == 5)
        <button class="btn btn-outline-success float-end" type="button" id="addBtn" onclick="window.location='/createspecial'" >
            Tambah
        </button>
        @else
        <button class="btn btn-outline-success float-end" type="button" id="addBtn" onclick="window.location='/create?user={{ $rolename }}'" >
            Tambah
        </button>
        @endif
    </form>

    <div class="table-responsive">
        <table id="requestTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead>
                <tr style="text-align:center">
                    <th> No. </th>
                    <th>Nama</th>
                    <th>Emel</th>
                    <th>Nama Pengguna</th>
                    <th>Nombor Telefon (60+)</th>
                    <th>Tindakan</th>
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
                    <h5 class="modal-title" id="deleteModalLabel">Padam {{ $rolename }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk memadam pengguna?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete">Padam</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/userScript.js') }}"></script>

@endsection