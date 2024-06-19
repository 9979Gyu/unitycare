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
        <div class="alert alert-danger condition-message"">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/updateprogram" method="post" class="container" id="editForm">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Program</h5>
        </div>

        <input type="text" name="programID" value="{{$program->program_id}}" hidden>

        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label required">Nama</label>
            <div class="col-sm-10">
                <input type="text" name="name" class="form-control" id="name" value="{{ $program->name }}" required>
            </div>
        </div>

        <div class="row md-3">
            <label for="programType" class="col-sm-2 col-form-label required">Jenis</label>
            <div class="col-sm-10">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="programType" value="1" id="voluntary" checked disabled>
                    <label class="form-check-label" for="voluntary">
                        Sukarelawan
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="programType" value="2" id="skilldev" disabled>
                    <label class="form-check-label" for="skilldev">
                        Pembangunan Kemahiran
                    </label>
                </div>
            </div>
        </div>

        <br>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Tempat</label>
            <div class="col-sm-10">
                <input type="text" value="{{ $program->venue }}" name="address" class="form-control" id="address" required>
            </div>
        </div>

        <!-- Working Location -->
        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Poskod</label>
            <div class="col-sm-4">
                <input type="number" value="{{ $program->postal_code }}" name="postalCode" class="form-control" id="postalCode" required>
            </div>
            <label for="state" class="col-sm-2 col-form-label required">Negeri</label>
            <div class="col-sm-4">
                <select name="state" id="state" class="form-select">
                    <option selected>{{ $program->state }}</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">            
            <label for="city" class="col-sm-2 col-form-label required">Bandar</label>
            <div class="col-sm-10">
                <select name="city" id="city" class="form-select">
                    <option selected>{{ $program->city }}</option>
                </select>
            </div>
        </div>

        <br>

        <div class="row mb-3">
            <label for="start_date" class="col-sm-2 col-form-label required">Tarikh Bermula</label>
            <div class="col-sm-4">
                <input type="date" value="{{ $program->start_date }}" name="start_date" class="form-control" id="start_date" required>
            </div>

            <label for="start_time" class="col-sm-2 col-form-label required">Masa Bermula</label>
            <div class="col-sm-4">
                <input type="time" value="{{ $program->start_time }}" name="start_time" class="form-control" id="start_time" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="end_date" class="col-sm-2 col-form-label required">Tarikh Tamat</label>
            <div class="col-sm-4">
                <input type="date" value="{{ $program->end_date }}" name="end_date" class="form-control" id="end_date" required>
            </div>

            <label for="end_time" class="col-sm-2 col-form-label required">Masa Tamat</label>
            <div class="col-sm-4">
                <input type="time" value="{{ $program->end_time }}" name="end_time" class="form-control" id="end_time" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="description" class="col-sm-2 col-form-label required">Penerangan</label>
            <div class="col-sm-10">
                <textarea name="description" class="form-control" id="description" placeholder="Syarat-syarat" required>{{ $program->description }}</textarea>
            </div>
        </div>

        <div class="row mb-3">
            <label for="volunteer" class="col-sm-2 col-form-label required">Bilangan Sukarelawan</label>
            <div class="col-sm-4">
                <input type="number" name="volunteer" min="0" class="form-control" id="volunteer" value="{{ $volNum->qty_limit }}" required>
            </div>
            <label for="poor" class="col-sm-2 col-form-label required">Bilangan Orang Perlu Bantuan</label>
            <div class="col-sm-4">
                <input type="number" name="poor" min="1" class="form-control" id="poor" value="{{ $poorNum->qty_limit }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="close_date" class="col-sm-2 col-form-label required">Tarikh Tutup Permohonan</label>
            <div class="col-sm-10">
                <input type="date" value="{{ $program->close_date }}" name="close_date" class="form-control" id="close_date" required>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary">Hantar</button>
                <button type="button" onclick="window.location='/viewprogram'" class="btn btn-danger">Tutup</button>
            </div>
        </div>

    </form>
    
    <script src="{{ asset('js/general/postcodeScript.js') }}"></script>
    <script src="{{ asset('js/programs/controlScript.js') }}"></script>
    <script src="{{ asset('js/general/dateScript.js')}}"></script>

@endsection