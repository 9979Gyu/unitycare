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

    <form action="/storeapplication" method="post" class="container" id="addForm">
        @csrf
        <table class="table table-hover">
            <tr>
                <th colspan="4" class="text-center">Maklumat Pekerjaan</th>
            </tr>
            <tr>
                <th scope="row">Nama</th>
                <td colspan="3">{{ $offer->job->name }} - {{ $offer->job->position }}</td>
            </tr>
            <tr>
                <th scope="row">Tempat</th>
                <td colspan="3">{{ $offer->city }}, {{ $offer->state }}</td>
            </tr>
            <tr>
                <th scope="row">Jenis</th>
                <td colspan="3">{{ $offer->jobType->name }}</td>
            </tr>
            <tr>
                <th scope="row">Syif</th>
                <td colspan="3">{{ $offer->shiftType->name }}</td>
            </tr>
            <tr>
                <th scope="row">Purata Gaji</th>
                <td colspan="3">RM {{ $offer->min_salary }} - RM {{ $offer->max_salary }}</td>
            </tr>
            <tr>
                <th scope="row">Penerangan</th>
                <td colspan="3">{{ json_decode($offer->description, true)['description'] }}</td>
            </tr>
            <tr>
                <th scope="row" rowspan="2">Pengurus</th>
                <th>Nama</th>
                <th>Telefon</th>
                <th>Emel</th>
            </tr>
            <tr>
                <td>{{ $offer->organization->name }}</td>
                <td>+60{{ $offer->organization->contactNo }}</td>
                <td>{{ $offer->organization->email }}</td>
            </tr>
            <tr>
                <th scope="row">Pilihan</th>
                <input type="number" name="offer_id" value="{{ $offer->offer_id }}" hidden>
                <td colspan="3">
                @if($offer->user_id != Auth::user()->id && $applicationExist == 0)
                    @if(Auth::user()->roleID == 5)
                        <button type="submit" class="btn btn-success" name="button_id" value="mohon"><b>Mohon</b></button>
                    @endif
                @endif
                <button type="button" class="btn btn-secondary" onclick="window.location='/viewoffer'"><b>Tutup</b></button>
                </td>
            </tr>
        </table>
    </form>

@endsection