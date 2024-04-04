@extends('layouts.app')
@section('title')
    UnityCare-User
@endsection
@section('content')

    <h2>User</h2>
    <br>
    <form action="/updateuser/{{ $user->id }}" method="post" class="container">
        @csrf

        <div class="mb-3">
            <h5>Personal Information</h5>
        </div>
        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label required">Name</label>
            <div class="col-sm-10">
                <input type="text" name="name" class="form-control touppercase" id="fname" value="{{ $user->name }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="ICNo" class="col-sm-2 col-form-label">IC No</label>
            <div class="col-sm-4">
                <input type="number" name="ICNo" class="form-control" id="ICNo" value="{{ $user->ICNo }}">
            </div>
            <label for="contactNo" class="col-sm-2 col-form-label required">Contact No (60+)</label>
            <div class="col-sm-4">
                <input type="number" name="contactNo" class="form-control" id="contactNo" value="{{ $user->contactNo }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Address</label>
            <div class="col-sm-10">
                <input type="text" name="address" class="form-control touppercase" id="address" value="{{ $user->address }}" required>
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
                <input type="number" name="postalCode" class="form-control" id="postalCode" value="{{ $user->postalCode }}" required>
            </div>
            <label for="officeNo" class="col-sm-2 col-form-label">Office No</label>
            <div class="col-sm-4">
                <input type="number" name="officeNo" class="form-control" id="officeNo" value="{{ $user->officeNo }}">
            </div>
        </div>

        <div class="mb-3">
            <h5>Account Information</h5>
        </div>
        <div class="row mb-3">
            <label for="username" class="col-sm-2 col-form-label required">Username</label>
            <div class="col-sm-10">
                <input type="text" name="username" class="form-control" id="username" pattern=".{3,}" maxlength="25" title="Three or more characters" value="{{ $user->username }}">
            </div>
        </div>

        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label required">Email</label>
            <div class="col-sm-10">
                <input type="email" name="email" class="form-control touppercase" id="email" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$" value="{{ $user->email }}">
                <input type="number" name="roleID" value="{{ $user->roleID }}" hidden>

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
    <script>
        $(document).ready(function(){
            displayStateAndCity();

            function displayStateAndCity(){

                // array of state and cities
                var states = ["Kelantan", "Melaka", 'Negeri Sembilan'];
                var cities = {"kelantan": [
                        "Gua Musang",
                        "Jeli",
                        "Ketereh",
                        "Kota Bharu",
                        "Kuala Krai",
                        "Pasir Mas",
                        "Machang",
                        "Tanah Merah",
                        "Tumpat"
                    ],
                    "melaka": [
                        "Alor Gajah",
                        "Ayer Keroh",
                        "Ayer Molek",
                        "Batu Berendam",
                        "Bemban",
                        "Bukit Baru",
                        "Bukit Rambai",
                        "Jasin",
                        "Klebang Besar",
                        "Kuala Sungai Baru",
                        "Masjid Tanah",
                        "Melaka",
                        "Pulau Sebang",
                        "Sungai Udang"
                    ],
                    "negeri sembilan": [
                        "Bahau",
                        "Seremban",
                        "Kuala Pilah",
                        "Nilai",
                        "Port Dickson"
                    ],
                };
                
                // clear the dropdown and create database options
                $('#state').empty().append("<option value='{{$user->state}}' selected>{{$user->state}}</option>");
                $('#city').empty().append("<option value='{{$user->city}}' selected>{{$user->city}}</option>");

                $.each(states, function(index, stateName){
                    if(stateName != $("#state").val())
                        $("#state").append("<option value='"+ stateName + "'>" + stateName + "</option>");
                });

                $.each(cities[$("#state").val().toLowerCase()], function(index, cityName) {
                    if(cityName != $("#city").val())
                        $("#city").append("<option value='" + cityName + "'>" + cityName + "</option>");
                });

                function updateCity(selectedState){
                    if(cities.hasOwnProperty(selectedState.toLowerCase())){
                        $('#city').empty().append("<option value='' disabled selected>Select city</option>");
                        $.each(cities[selectedState.toLowerCase()], function(index, cityName) {
                            $("#city").append("<option value='" + cityName + "'>" + cityName + "</option>");
                        });
                    }
                    else{
                        console.error("Invalid state:", selectedState);
                    }
                }   

                $("#state").change(function(){
                    var selectedState = $(this).val();
                    updateCity(selectedState);
                });
            }
        });
    </script>
    
@endsection