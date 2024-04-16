@extends('layouts.app')
@section('title')
    UnityCare-Login
@endsection

@include('headers.mainhead')

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

    <h2>Login</h2>
    <br>
    
    <div class="container">
        <form action="/auth" method="post">
            @csrf
            <div class="row mb-3">
                <label for="fname" class="col-sm-3 col-form-label required">Nama Pengguna</label>
                <div class="col-sm-5">
                    <input type="text" value="{{ old('username') }}" name="username" class="form-control" id="username" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="lname" class="col-sm-3 col-form-label required">Kata Laluan</label>
                <div class="col-sm-5">
                    <input type="password" name="password" class="form-control" id="pwd" required>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-10 center">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </div>

        </form>
    </div>
    
@endsection