@extends('layouts.app')
@section('title')
    UnityCare-Profil
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

    <br>

    <form action="/updateuser" method="post" class="container" id="profileForm">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Peribadi</h5>
        </div>
        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label required">Nama</label>
            <div class="col-sm-10">
                <input type="text" name="name" class="form-control" id="name" value="{{ $user->name }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="ICNo" class="col-sm-2 col-form-label required">Nombor Pengenalan</label>
            <div class="col-sm-10">
                <input type="text" value="{{ $user->ICNo }}" name="ICNo" class="form-control" id="ICNo" 
                    pattern="([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])([0-9]{2})([0-9]{4})"
                    title="Sila berikan nombor IC yang betul (tiada symbol -)"  
                    placeholder="Contoh: 021221041234" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="contactNo" class="col-sm-2 col-form-label required">Nombor Telefon (60+)</label>
            <div class="col-sm-4">
                <input type="tel" value="{{ $user->contactNo }}" name="contactNo" class="form-control" id="contactNo" pattern="\d{9,10}" title="Sila berikan nombor telefon yang betul" required>
            </div>

            <label for="officeNo" class="col-sm-2 col-form-label">Nombor Telefon Pejabat</label>
            <div class="col-sm-4">
                <input type="tel" value="{{ $user->officeNo }}" name="officeNo" class="form-control" id="officeNo">
            </div>
        </div>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Alamat</label>
            <div class="col-sm-10">
                <input type="text" value="{{ $user->address }}" name="address" class="form-control" id="address" required>
            </div>
        </div>

        <!-- auto sort state and city based on postal code -->
        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Poskod</label>
            <div class="col-sm-4">
                <input type="number" name="postalCode" class="form-control" id="postalCode" value="{{ $user->postalCode }}" required>
            </div>
            <label for="state" class="col-sm-2 col-form-label required">Negeri</label>
            <div class="col-sm-4">
                <select name="state" id="state" class="form-select">
                    <option selected>{{ $user->state  }}</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="city" class="col-sm-2 col-form-label required">Bandar</label>
            <div class="col-sm-10">
                <select name="city" id="city" class="form-select">
                    <option selected>{{ $user->city }}</option>
                </select>
            </div>
        </div>

        @if($user->roleID == 5)
            <div class="row mb-3">
                <label for="eduLevel" class="col-sm-2 col-form-label required">Peringkat Pendidikan</label>
                <div class="col-sm-4">
                    <select name="eduLevel" id="eduLevel" class="form-select">
                        <option selected>{{ $user->poor->educationLevel->name }}</option>
                    </select>
                </div>
                <label for="eduName" class="col-sm-2 col-form-label required">Nama Institusi</label>
                <div class="col-sm-4">
                    <input type="text" value="{{ $user->poor->instituition_name }}" name="eduName" class="form-control" id="eduName" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="disType" class="col-sm-2 col-form-label required">Kategori</label>
                <div class="col-sm-10">
                    <select name="disType" id="disType" class="form-select">
                        <option selected>{{ $user->poor->disabilityType->name }}</option>
                    </select>
                </div>
            </div>
        @endif

        <br>

        <div class="mb-3">
            <h5>Maklumat Akaun</h5>
        </div>
        <div class="row mb-3">
            <label for="username" class="col-sm-2 col-form-label required">Nama Pengguna</label>
            <div class="col-sm-10">
                <input type="text" value="{{ $user->username }}" name="username" class="form-control" id="username" 
                pattern=".{3,}" maxlength="25" title="Tiga huruf ke atas">
            </div>
        </div>

        <input type="number" value="{{ $user->id }}" name="uid" class="form-control" id="uid" hidden>
        <input type="number" value="{{ $user->roleID }}" name="roleID" class="form-control" id="roleID" hidden>

        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label required">Emel</label>
            <div class="col-sm-10">
                <input type="email" value="{{ $user->email }}" name="email" class="form-control" id="email" 
                pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" title="Sila berikan email yang betul">
            </div>
        </div>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary" name="submitBtn" id="submitBtn">Hantar</button>
                <button type="button" class="btn btn-primary" name="editBtn" id="editBtn">Kemaskini</button>
                <button type="button" onclick="window.location='/'" class="btn btn-danger">Tutup</button>
            </div>
        </div>

    </form>

    <br>
    
    <script src="{{ asset('js/profileScript.js') }}"></script>
    <script src="{{ asset('js/general/postcodeScript.js') }}"></script>
@endsection