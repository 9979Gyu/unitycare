@extends('layouts.app')
@section('title')
    UnityCare-Add
@endsection

@section('content')

    @if (session()->has('success'))
        <div class="alert alert-success">
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

    <form action="/store" method="post" class="container">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Peribadi</h5>
        </div>
        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label required">Nama</label>
            <div class="col-sm-10">
                <input type="text" name="name" class="form-control touppercase" id="name" value="{{ old('name') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="ICNo" class="col-sm-2 col-form-label required">Nombor Pengenalan</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('ICNo') }}" name="ICNo" class="form-control" id="ICNo" pattern="\d{12}" title="Sila berikan nombor IC yang betul" required placeholder="Contoh: 021221041234">
            </div>
        </div>

        <div class="row mb-3">
            <label for="contactNo" class="col-sm-2 col-form-label required">Nombor Telefon (60+)</label>
            <div class="col-sm-4">
                <input type="text" value="{{ old('contactNo') }}" name="contactNo" class="form-control" id="contactNo" pattern="\d{9,10}" title="Sila berikan nombor telefon yang betul" required>
            </div>

            <label for="officeNo" class="col-sm-2 col-form-label">Nombor Telefon Pejabat</label>
            <div class="col-sm-4">
                <input type="number" value="{{ old('officeNo') }}" name="officeNo" class="form-control" id="officeNo">
            </div>
        </div>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Alamat</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('address') }}" name="address" class="form-control touppercase" id="address" required>
            </div>
        </div>

        <!-- auto sort state and city based on postal code -->
        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Poskod</label>
            <div class="col-sm-4">
                <input type="number" name="postalCode" class="form-control" id="postalCode" required>
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

        <div class="mb-3">
            <h5>Maklumat Akaun</h5>
        </div>
        <div class="row mb-3">
            <label for="username" class="col-sm-2 col-form-label required">Nama Pengguna</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('username') }}" name="username" class="form-control" id="username" pattern=".{3,}" maxlength="25" title="Three or more characters">
            </div>
        </div>

        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label required">Emel</label>
            <div class="col-sm-10">
                <input type="email" value="{{ old('email') }}" name="email" class="form-control" id="email" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$">
            </div>
        </div>
        <input type="number" name="roleID" value="{{ $roleNo }}" hidden>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary">Hantar</button>
                <button type="button" onclick="window.location='/'" class="btn btn-danger">Tutup</button>
            </div>
        </div>

    </form>

    <br>

    <script src="{{ asset('js/general/postcodeScript.js') }}"></script>

@endsection