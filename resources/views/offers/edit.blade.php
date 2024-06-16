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

    @if ($errors->any())
        <div class="alert alert-danger condition-message"">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
            <label for="address" class="col-sm-2 col-form-label required">Tempat</label>
            <div class="col-sm-10">
                <input type="text" value="{{ $offer->venue }}" name="address" class="form-control" id="address" required>
            </div>
        </div>
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

        <div class="row mb-3" id="start">
            <label for="start_date" class="col-sm-2 col-form-label required">Tarikh Bermula</label>
            <div class="col-sm-4">
                <input type="date" value="{{ $offer->start_date }}" name="start_date" class="form-control" id="start_date">
            </div>

            <label for="start_time" class="col-sm-2 col-form-label required">Masa Bermula</label>
            <div class="col-sm-4">
                <input type="time" value="{{ $offer->start_time }}" name="start_time" class="form-control" id="start_time">
            </div>
        </div>

        <div class="row mb-3" id="end">
            <label for="end_date" class="col-sm-2 col-form-label required">Tarikh Tamat</label>
            <div class="col-sm-4">
                <input type="date" value="{{$offer->end_date }}" name="end_date" class="form-control" id="end_date">
            </div>

            <label for="end_time" class="col-sm-2 col-form-label required">Masa Tamat</label>
            <div class="col-sm-4">
                <input type="time" value="{{ $offer->end_time }}" name="end_time" class="form-control" id="end_time">
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

        <div class="row mb-3">
            <label for="quantity" class="col-sm-2 col-form-label required">Bilangan Pekerja Diperlukan</label>
            <div class="col-sm-10">
                <input type="number" value="{{ $offer->quantity }}" min="1" name="quantity" class="form-control" id="quantity" required>
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