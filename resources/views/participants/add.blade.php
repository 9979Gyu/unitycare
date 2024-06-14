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

    @if (session()->has('error'))
        <div class="alert alert-danger condition-message">
            {{ session('error') }}
        </div>
    @endif

    <form action="/storeparticipant" method="post" class="container" id="addForm">
        @csrf
        <div class="table-responsive">
            <table class="table table-hover">
                <tr>
                    <th colspan="4" class="text-center">Maklumat Program</th>
                </tr>
                <tr>
                    <th scope="row">Nama</th>
                    <td colspan="3">{{ $program->name }}</td>
                </tr>
                <tr>
                    <th scope="row">Jenis</th>
                    @if($program->type_id == 1)
                        <td colspan="3">Sukarelawan</td>
                    @elseif($program->type_id == 2)
                        <td colspan="3">Pembangunan Kemahiran</td>
                    @endif
                </tr>
                <tr>
                    <th scope="row">Tempat</th>
                    <td colspan="3" id="address">{{ $program->venue }}, {{ $program->postal_code }}, {{ $program->state }}, {{ $program->city }}</td>
                </tr>
                <tr>
                    <th scope="row">Mula</th>
                    <td colspan="3">{{ $program->start_date }} {{ $program->start_time }}</td>
                </tr>
                <tr>
                    <th scope="row">Tamat</th>
                    <td colspan="3">{{ $program->end_date }} {{ $program->end_time }}</td>
                </tr>
                <tr>
                    <th scope="row">Penerangan</th>
                    <td colspan="3">{{ $program->description }}</td>
                </tr>
                <tr>
                    <th scope="row">Tarikh Tutup Permohonan</th>
                    <td colspan="3">{{ $program->close_date }}</td>
                </tr>
                <tr>
                    <th scope="row" rowspan="2">Kekosongan</th>
                    <th>Sukarelawan</th>
                    <th colspan="2">Peserta</th>
                </tr>
                <tr>
                    <td>{{ $volRemain }} / {{ $volLimit->programSpecs[0]->qty_limit }}</td>
                    <td colspan="2">{{ $poorRemain }} / {{ $poorLimit->programSpecs[0]->qty_limit }}</td>
                </tr>
                <tr>
                    <th scope="row" rowspan="2">Pengurus</th>
                    <th>Nama</th>
                    <th>Telefon</th>
                    <th>Emel</th>
                </tr>
                <tr>
                    <td>{{ $program->organization->name }}</td>
                    <td>+60{{ $program->organization->contactNo }}</td>
                    <td>{{ $program->organization->email }}</td>
                </tr>
                <tr>
                    <th scope="row">Pilihan</th>
                    <input type="number" name="program_id" value="{{ $program->program_id }}" hidden>
                    <td colspan="3">
                    @if($participantExist == 0 && $program->close_date >= today() && $program->user_id != Auth::user()->id)
                        @if(Auth::user()->roleID == 5)
                            @if($poorRemain > 0)
                                <button type="submit" class="btn btn-success" name="button_id" value="3"><b>Jadi Peserta</b></button>
                            @endif
                            @if($volRemain > 0)
                                <button type="submit" class="btn btn-info" name="button_id" value="2"><b>Jadi Sukarelawan</b></button>
                            @endif
                        @else
                            @if($volRemain > 0)
                                <button type="submit" class="btn btn-info" name="button_id" value="2"><b>Jadi Sukarelawan</b></button>
                            @endif
                        @endif
                    @endif
                    <button type="button" class="btn btn-secondary" onclick="window.location='/viewprogram'"><b>Tutup</b></button>
                    </td>
                </tr>
                <tr>
                    <th>Map</th>
                    <td colspan="3">
                        <div id="map"></div>
                    </td>
                </tr>
            </table>
        </div>
    </form>

    <!-- Leaflet initialization script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    <script src="{{ asset('js/mapScript.js') }}"></script>

@endsection