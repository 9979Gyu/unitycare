@extends('layouts.app')
@section('title')
    UnityCare-Pekerjaan
@endsection

@section('content')
    
    <h2>Pekerjaan</h2>
    <br>

    @if (session()->has('success'))
        <div class="alert alert-success condition-message">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger condition-message"">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <br>

    <form method="POST" action="/export-jobs" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ $roleNo }}" hidden>
        <div class="row mb-3">
            <div class="col">
                <select name="type" name="type" id="type" class="form-control">
                    <option value="type" selected>Jenis Pekerjaan</option>
                    <option value="shift">Jenis Syif</option>
                    <option value="job">Jenis Jawatan</option>
                </select>
            </div>
            <div class="col">
                <button class="btn btn-outline-primary float-end" type="submit" id="excelBtn">Excel</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table id="requestTable" class="table table-bordered table-striped dt-responsive display datatable" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead>
                <tr style="text-align:center">
                    <th> No. </th>
                    <th>Nama</th>
                    <th>Penerangan</th>
                    <th>Bilangan Pengguna</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Padam Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk memadam data?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete">Padam</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/indexJobScript.js') }}"></script>


@endsection