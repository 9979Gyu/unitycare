@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/offerAddStyle.css') }}" rel="stylesheet">
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

    @if (session()->has('error'))
        <div class="alert alert-danger condition-message">
            {{ session('error') }}
        </div>
    @endif

    <form action="/updateoffer" method="post" class="container" id="editForm">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Pekerjaan</h5>
        </div>
        
        <!-- Select name and position for the offer -->
        <div class="row mb-3">
            <label for="job" class="col-sm-2 col-form-label required">Pekerjaan</label>
            <div class="col-sm-4">
                <select name="job" id="job" class="form-control select2">
                    <option value="{{ $offer->job_id }}" selected>{{ $offer->jobname }}</option>
                </select>
            </div>
            <label for="position" class="col-sm-2 col-form-label required">Jawatan</label>
            <div class="col-sm-4">
                <select name="position" id="position" class="form-control select2">
                    <option value="{{ $offer->job_id }}" selected>{{ $offer->jobposition }}</option>
                </select>
            </div>
        </div>

        <br>

        <!-- Select job type -->
        <div class="row md-3">
            <label for="jobType" class="col-sm-2 col-form-label required">Jenis Pekerjaan</label>
            <div class="col-sm-4">
                <select name="jobType" id="jobType" class="form-control select2">
                    <option value="{{ $offer->job_type_id }}">{{ $offer->typename }}</option>
                </select>
            </div>

            <label for="schedule" class="col-sm-2 col-form-label required">Jadual Pekerjaan (Shift)</label>
            <div class="col-sm-4">
                <select name="shiftType" id="shiftType" class="form-control select2">
                    <option value="{{ $offer->shift_type_id }}">{{ $offer->shiftname }}</option> 
                </select>
            </div>

        </div>

        <br>

        <!-- Working Location -->
        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Poskod</label>
            <div class="col-sm-4">
                <input type="number" name="postalCode" class="form-control" id="postalCode" value="{{ $offer->postal_code }}" required readonly>
                <input type="number" name="offerID" class="form-control" id="offerID" value="{{ $offer->offer_id }}" required hidden>
            </div>
            <label for="state" class="col-sm-2 col-form-label required">Negeri</label>
            <div class="col-sm-4">
                <select name="state" id="state" class="form-select">
                    <option value="{{ $offer->state }}" selected>{{ $offer->state }}</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">            
            <label for="city" class="col-sm-2 col-form-label required">Bandar</label>
            <div class="col-sm-10">
                <select name="city" id="city" class="form-select">
                    <option value=" {{ $offer->city }} " selected>{{ $offer->city }}</option>
                </select>
            </div>
        </div>
        
        <br>

        <!-- Full Job Description -->
        <div class="row mb-3">
            <label for="description" class="col-sm-2 col-form-label required">Penerangan Penuh Pekerjaan</label>
            <div class="col-sm-10">
                <textarea name="description" class="form-control" id="description" placeholder="Kemahiran diperlukan, Peringkat pendidikan" required>{{ $offer->description }}</textarea>
            </div>
        </div>
        
        <br>

        <div class="mb-3">
            <h5>Purata Gaji Bulanan (RM)</h5>
        </div>

        <!-- Salary Range -->
        <div class="row mb-3">
            <label for="salaryStart" class="col-sm-2 col-form-label required">Minimum</label>
            <div class="col-sm-4">
                <input type="number" value="{{ $offer->min_salary }}" name="salaryStart" class="form-control" id="salaryStart" required>
            </div>

            <label for="salaryEnd" class="col-sm-2 col-form-label required">Maksimum</label>
            <div class="col-sm-4">
                <input type="number" value="{{ $offer->max_salary }}" name="salaryEnd" class="form-control" id="salaryEnd" required>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary">Hantar</button>
                <button type="button" onclick="window.location='/viewoffer'" class="btn btn-danger">Tutup</button>
            </div>
        </div>

    </form>

    <!-- <script src="{{ asset('js/addOfferScript.js') }}"></script> -->

@endsection