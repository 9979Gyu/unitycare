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

    @if (session()->has('error'))
        <div class="alert alert-danger condition-message">
            {{ session('error') }}
        </div>
    @endif

    <form action="/storejob" method="post" class="container" id="addForm">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Pekerjaan</h5>
        </div>
        <input type="text" name="roleID" id="roleID" value="{{ $roleNo }}" hidden>

        <div class="row mb-3">
            <!-- Job title : software engineer -->
            <label for="name" class="col-sm-2 col-form-label required">Nama</label>
            <div class="col-sm-10">
                <input type="text" name="name" class="form-control capitalize" id="name" value="{{ old('name') }}" required>
            </div>
        </div>

        <br>

        <div class="row mb-3">
            <!-- Job position : cloud software engineer -->
            <label for="position" class="col-sm-2 col-form-label required">Jawatan</label>
            <div class="col-sm-10">
                <input type="text" name="position" class="form-control capitalize" id="position" value="{{ old('position') }}" required>
            </div>
        </div>

        <br>

        <div class="row mb-3">
            <label for="description" class="col-sm-2 col-form-label required">Penerangan</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('description') }}" name="description" class="form-control" id="description" required>
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

@endsection