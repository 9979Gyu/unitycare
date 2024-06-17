@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/offerStyle.css') }}" rel="stylesheet">
@endpush

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

    <input type="number" id="roleID" value="{{ $roleID }}" hidden>
    <input type="number" id="uid" value="{{ $userID }}" hidden>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-12 search-bar">
                <div class="input-group-prepend tooltip-container m-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-briefcase-fill" viewBox="0 0 16 16" data-bs-toggle="tooltip" data-placement="top"
                        title="Nama Pekerjaan (Contoh: Kerjaya)
                        Nama Penganjur (Contoh: ABC)
                        Tarikh Mula (Contoh: 24-06-2023)">
                        <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5"/>
                        <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85z"/>
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

    <div class="card-container">

    </div>

    <!-- Dismiss Modal -->
    <div class="modal fade" id="dismissModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dismissModalLabel">Tarik Diri Daripada Tawaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk berhenti memohon pekerjaan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="dismiss">Tarik Diri</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/reports/indexOfferScript.js') }}"></script>
    <script src="{{ asset('js/dateScript.js') }}"></script>

@endsection