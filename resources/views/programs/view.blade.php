@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/offerStyle.css') }}" rel="stylesheet">
@endpush

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

    <input type="number" id="roleID" value="{{ Auth::user()->roleID }}" hidden>
    <input type="number" id="uid" value="{{ Auth::user()->id }}" hidden>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-12 search-bar">
                <div class="input-group-prepend tooltip-container m-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar2-week-fill" viewBox="0 0 16 16" data-bs-toggle="tooltip" data-placement="top"
                        title="Nama Program (Contoh: Kerjaya)
                        Nama Penganjur (Contoh: ABC)
                        Tarikh Mula (Contoh: 24-06-2023)">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5m9.954 3H2.545c-.3 0-.545.224-.545.5v1c0 .276.244.5.545.5h10.91c.3 0 .545-.224.545-.5v-1c0-.276-.244-.5-.546-.5M8.5 7a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm3 0a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM3 10.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                    </svg>
                </div>
                <input type="text" name="keyword" id="keyword" class="form-control flex-grow-1 mb-2 mb-lg-0" placeholder="Kata kunci">
                <div class="input-group-prepend tooltip-container m-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16" data-bs-toggle="tooltip" 
                        title="Kawasan program diadakan">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                    </svg>
                </div>
                <select name="citystate" id="citystate" class="form-control select2 flex-grow-1 mb-2 mb-lg-0">
                    
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-primary" id="searchBtn">Cari</button>
                </div>
            </div>
        </div>
    </div>

    <br>

    <div class="pb-2">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="voluntaryCheckBox" value="1" checked>
            <label class="form-check-label" for="voluntary">Sukarelawan</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="skillDevCheckBox" value="2" checked>
            <label class="form-check-label" for="skillDev">Pembangunan Kemahiran</label>
        </div>
    </div>

    <div class="card-container">

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

    <!-- Dismiss Modal -->
    <div class="modal fade" id="dismissModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dismissModalLabel">Tarik Diri Daripada Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk berhenti menyertai program?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="dismiss">Tarik Diri</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/viewProgramScript.js') }}"></script>
    <script src="{{ asset('js/dateScript.js') }}"></script>

@endsection