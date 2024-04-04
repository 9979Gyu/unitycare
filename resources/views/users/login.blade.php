@extends('layouts.master')
@section('title')
    UnityCare-Login
@endsection
@section('content')

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h2>Login</h2>
    <br>
    <form action="/auth" method="post" class="container">
        @csrf
        <div class="row mb-3">
            <label for="fname" class="col-sm-2 col-form-label required">Username</label>
            <div class="col-sm-4">
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="lname" class="col-sm-2 col-form-label required">Password</label>
            <div class="col-sm-4">
                <input type="password" name="password" class="form-control" id="pwd" required>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-10 center">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </div>

    </form>
    
@endsection