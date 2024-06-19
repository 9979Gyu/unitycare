@extends('layouts.master')
@section('title')
    UnityCare-Registration
@endsection
@section('content')

    <h2>Registration</h2>
    <br>
    <form action="/storeadmin" method="post" class="container">
        @csrf
        <div class="mb-3">
            <h5>Personal Information</h5>
        </div>
        <div class="row mb-3">
            <label for="fname" class="col-sm-2 col-form-label required">First Name</label>
            <div class="col-sm-4">
                <input type="text" name="fname" class="form-control touppercase" id="fname" required>
            </div>
            <label for="lname" class="col-sm-2 col-form-label required">Surname</label>
            <div class="col-sm-4">
                <input type="text" name="lname" class="form-control touppercase" id="lname" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="ICNo" class="col-sm-2 col-form-label">IC No</label>
            <div class="col-sm-4">
                <input type="number" name="ICNo" class="form-control" id="ICNo">
            </div>
            <label for="contactNo" class="col-sm-2 col-form-label required">Contact No (60+)</label>
            <div class="col-sm-4">
                <input type="number" name="contactNo" class="form-control" id="contactNo" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Address</label>
            <div class="col-sm-10">
                <input type="text" name="address" class="form-control touppercase" id="address" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="state" class="col-sm-2 col-form-label required">State</label>
            <div class="col-sm-4">
                <select name="state" id="state" class="form-select">
                    <option selected>Select state</option>
                </select>
            </div>
            <label for="city" class="col-sm-2 col-form-label required">City</label>
            <div class="col-sm-4">
                <select name="city" id="city" class="form-select">
                    <option selected>Select city</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Postal Code</label>
            <div class="col-sm-4">
                <input type="number" name="postalCode" class="form-control" id="postalCode" required>
            </div>
            <label for="officeNo" class="col-sm-2 col-form-label">Office No</label>
            <div class="col-sm-4">
                <input type="number" name="officeNo" class="form-control" id="officeNo">
            </div>
        </div>

        <div class="mb-3">
            <h5>Account Information</h5>
        </div>
        <div class="row mb-3">
            <label for="username" class="col-sm-2 col-form-label required">Username</label>
            <div class="col-sm-10">
                <input type="text" name="username" class="form-control" id="username" pattern=".{3,}" maxlength="25" title="Three or more characters">
            </div>
        </div>

        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label required">Email</label>
            <div class="col-sm-10">
                <input type="email" name="email" class="form-control touppercase" id="email" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$">
            </div>
        </div>

        <div class="row mb-3">
            <label for="password" class="col-sm-2 col-form-label required">Password</label>
            <div class="col-sm-10">
                <input type="password" name="password" class="form-control" id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one  number and one uppercase and lowercase letter, and at least 8 or more characters">
                <input type="number" name="roleID" value="1" hidden>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary">Submit</button>
                <button type="button" class="btn btn-secondary">Close</button>
            </div>
        </div>

    </form>
    <br>
    <script src="{{ asset('js/general/postcodeScript.js') }}"></script>
    
@endsection