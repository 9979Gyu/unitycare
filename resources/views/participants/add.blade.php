@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/poorTableStyle.css') }}" rel="stylesheet">
@endpush

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

    <form action="/storeparticipant" method="post" class="container" id="addForm">
        @csrf
        <div class="table-responsive">
            <table class="table table-hover">
                <tbody>
                    <tr>
                        <th colspan="2" class="text-center">Maklumat Program</th>
                    </tr>
                    <tr>
                        <th scope="row">Nama</th>
                        <td>{{ $program->name }}</td>
                    </tr>
                    @if($roleID == 5)
                    <tr>
                        <th scope="row">Yuran Pendaftaran</th>
                        <td>{{ $program->fee }} MYR <br>
                        (Nota: Yuran pendaftaran tidak dibayar balik)</td>
                    </tr>
                    @endif
                    <tr>
                        <th scope="row">Tempat</th>
                        <td id="address">
                            {{ $program->venue }}, {{ $program->postal_code }}, {{ $program->city }}, {{ $program->state }}
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Jenis</th>
                        <td>
                            @if($program->type_id == 1)
                                Sukarelawan
                            @elseif($program->type_id == 2)
                                Pembangunan Kemahiran
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Mula</th>
                        <td>{{ $program->start_date }} {{ $program->start_time }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Tamat</th>
                        <td>{{ $program->end_date }} {{ $program->end_time }}</td>
                    </tr>
                    @if (!empty(json_decode($program->description, true)))
                        <tr>
                            <th scope="row">Penerangan</th>
                            <td>{!! nl2br(e(json_decode($program->description, true)['desc'])) !!}</td>
                        </tr>
                    @endif
                    <tr>
                        <th scope="row">Tarikh Tutup Permohonan</th>
                        <td>{{ $program->close_date }}</td>
                    </tr>
                    <tr>
                        <th scope="row" rowspan="2">Kekosongan (kekosongan / diperlukan)</th>
                        <td>Sukarelawan: {{ $volRemain }} / {{ $volLimit->programSpecs[0]->qty_limit }}</td>
                    </tr>
                    <tr>
                        <td>Peserta: {{ $poorRemain }} / {{ $poorLimit->programSpecs[0]->qty_limit }}</td>
                    </tr>
                    <tr>
                        <th scope="row" rowspan="3">Pengurus</th>
                        <td>Nama: {{ $program->organization->name }}</td>
                    </tr>
                    <tr>
                        <td>Emel: {{ $program->organization->email }}</td>
                    </tr>
                    <tr>
                        <td>Telefon: +60{{ $program->organization->contactNo }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Peta</th>
                        <td>
                            <div id="map"></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Pilihan</th>
                        <input type="number" name="programID" value="{{ $program->program_id }}" hidden>
                        <input type="text" name="amount" value="{{ $program->fee }}" hidden>
                        <input type="text" name="organizerID" value="{{ $program->user_id }}" hidden>
                        <input type="text" name="programName" value="{{ $program->name }}" hidden>
                        <td>
                            <!-- Did not apply, close date not reach, is not creator, is not enterprise -->
                            @if(($action == "nc1" || $participantExist == 0) && $program->close_date >= today() && $program->user_id != $userID && $roleID != 3 && $type == 'true')
                                <!-- is poor, can be perserta -->
                                @if($poorRemain > 0 && $roleID == 5)
                                    <button type="submit" class="btn btn-success" name="button_id" value="3"><b>Jadi Peserta</b></button>
                                @endif

                                @if($volRemain > 0)
                                    <button type="submit" class="btn btn-info" name="button_id" value="2"><b>Jadi Sukarelawan</b></button>
                                @endif
                            @endif

                            @if($type == 'true')
                                <button type="button" class="btn btn-secondary" onclick="window.location='/viewallprograms'"><b>Tutup</b></button>
                            @elseif($type == 'sertai')
                                <button type="button" class="btn btn-secondary" onclick="window.location='/indexparticipated'"><b>Tutup</b></button>
                            @elseif($type == 'peserta')
                                <button type="button" class="btn btn-secondary" onclick="window.location='/indexparticipant'"><b>Tutup</b></button>
                            @elseif($type == 'permohonan')
                                <button type="button" class="btn btn-secondary" onclick="window.location='/viewprogram'"><b>Tutup</b></button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </form>

    <!-- Leaflet initialization script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    <script src="{{ asset('js/general/mapScript.js') }}"></script>

@endsection