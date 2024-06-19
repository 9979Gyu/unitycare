@extends('layouts.app')

@section('title')
    UnityCare-Pekerjaan
@endsection

@section('content')
    
    <h2>Pekerjaan - Tambah</h2>
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

    <form action="/storeoffer" method="post" class="container" id="addForm">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Pekerjaan</h5>
        </div>
        
        <!-- Select name and position for the offer -->
        <div class="row mb-3">
            <label for="job" class="col-sm-2 col-form-label required">Pekerjaan</label>
            <div class="col-sm-4">
                <select name="job" id="job" class="form-control select2">
                    <option value="0" selected>Pilih Perkerjaan</option>
                </select>
            </div>
            <label for="position" class="col-sm-2 col-form-label required">Jawatan</label>
            <div class="col-sm-4">
                <select name="position" id="position" class="form-control select2">
                    <option value="0" selected>Pilih Jawatan</option>
                </select>
            </div>
            <div class="col-sm-10">
            (Tiada jenis pekerjaan dan jawatan yang berkaitan? Tekan <a href="/createjob">sini</a> untuk tambah.)
            </div>
        </div>

        <br>

        <!-- Select job type -->
        <div class="row md-3">
            <label for="jobType" class="col-sm-2 col-form-label required">Jenis Pekerjaan</label>
            <div class="col-sm-4">
                <select name="jobType" id="jobType" class="form-control select2">
                    @foreach($jobTypes as $jobType)
                        <option value="{{ $jobType->job_type_id }}" data-toggle="tooltip{{ $jobType->job_type_id }}" data-placement="top" title="{{ $jobType->description }}">{{ $jobType->name }}</option>
                    @endforeach
                </select>
            </div>

            <label for="schedule" class="col-sm-2 col-form-label required">Jadual Pekerjaan (Shift)</label>
            <div class="col-sm-4">
                <select name="shiftType" id="shiftType" class="form-control select2">
                    @foreach($shiftTypes as $shiftType)
                        <option value="{{ $shiftType->shift_type_id }}" data-toggle="tooltip{{ $shiftType->shift_type_id }}" data-placement="top" title="{{ $shiftType->description }}">{{ $shiftType->name }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <br>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Alamat</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('address') }}" name="address" class="form-control" id="address" placeholder="Alamat tempat kerja" required>
            </div>
        </div>

        <!-- Working Location -->
        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Poskod</label>
            <div class="col-sm-4">
                <input type="number" name="postalCode" class="form-control" id="postalCode" placeholder="Poskod" required>
            </div>
            <label for="state" class="col-sm-2 col-form-label required">Negeri</label>
            <div class="col-sm-4">
                <select name="state" id="state" class="form-select">
                    <option selected>Pilih Negeri</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">            
            <label for="city" class="col-sm-2 col-form-label required">Bandar</label>
            <div class="col-sm-10">
                <select name="city" id="city" class="form-select">
                    <option selected>Pilih Bandar</option>
                </select>
            </div>
        </div>

        <br>

        <div class="row mb-3" id="date">
            <label for="start_date" class="col-sm-2 col-form-label required">Tarikh Bermula</label>
            <div class="col-sm-4">
                <input type="date" value="{{ old('start_date') }}" name="start_date" class="form-control" id="start_date">
            </div>

            <label for="end_date" class="col-sm-2 col-form-label required">Tarikh Tamat</label>
            <div class="col-sm-4">
                <input type="date" value="{{ old('end_date') }}" name="end_date" class="form-control" id="end_date">
            </div>
        </div>

        <div class="row mb-3" id="time">
            
            <label for="start_time" class="col-sm-2 col-form-label required">Masa Bermula</label>
            <div class="col-sm-4">
                <input type="time" value="{{ old('start_time') }}" name="start_time" class="form-control" id="start_time">
            </div>

            <label for="end_time" class="col-sm-2 col-form-label required">Masa Tamat</label>
            <div class="col-sm-4">
                <input type="time" value="{{ old('end_time') }}" name="end_time" class="form-control" id="end_time">
            </div>
        </div>
        
        <br>

        <!-- Full Job Description -->
        <div class="row mb-3">
            <label for="description" class="col-sm-2 col-form-label required">Penerangan Penuh Pekerjaan</label>
            <div class="col-sm-10">
                <textarea value="{{ old('description') }}" name="description" class="form-control" id="description" placeholder="Kemahiran diperlukan, Peringkat pendidikan" required></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <label for="quantity" class="col-sm-2 col-form-label required">Bilangan Pekerja Diperlukan</label>
            <div class="col-sm-10">
                <input type="number" value="{{ old('quantity') }}" min="1" name="quantity" class="form-control" id="quantity" placeholder="Bilangan pekerja diperlukan untuk jawatan ini" required>
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
                <input type="number" value="{{ old('salaryStart') }}" name="salaryStart" class="form-control" id="salaryStart" placeholder="Minimum gaji sebulan" required>
            </div>

            <label for="salaryEnd" class="col-sm-2 col-form-label required">Maksimum</label>
            <div class="col-sm-4">
                <input type="number" value="{{ old('salaryEnd') }}" name="salaryEnd" class="form-control" id="salaryEnd" placeholder="Maximum gaji sebulan" required>
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

    <script src="{{ asset('js/addOfferScript.js') }}"></script>
    <script src="{{ asset('js/general/postcodeScript.js') }}"></script>
    <script src="{{ asset('js/general/dateScript.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

@endsection