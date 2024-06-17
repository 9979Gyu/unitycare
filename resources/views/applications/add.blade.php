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

    <div class="table-responsive">
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
                <td colspan="3" id="address">{{ $offer->venue }}, {{ $offer->postal_code }}, {{ $offer->city }}, {{ $offer->state }}</td>
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
                <th scope="row">Tarikh</th>
                <td colspan="3">{{ $offer->start_date }} Hingga {{ $offer->end_date }}</td>
            </tr>
            <tr>
                <th scope="row">Masa</th>
                <td colspan="3">{{ $offer->start_time }} Hingga {{ $offer->end_time }}</td>
            </tr>
            <tr>
                <th scope="row">Purata Gaji</th>
                <td colspan="3">RM {{ $offer->min_salary }} - RM {{ $offer->max_salary }}</td>
            </tr>
            <tr>
                <th scope="row">Penerangan</th>
                <td colspan="3">{!! nl2br(e(json_decode($offer->description, true)['description'])) !!}</td>
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
                <th>Peta</th>
                <td colspan="3">
                    <div class="border-radius" id="map"></div>
                </td>
            </tr>
            <tr>
                <th scope="row">Pilihan</th>
                <td colspan="3">
                @if(Auth::user()->roleID == 5)
                    @if($offer->user_id != Auth::user()->id && $applicationExist == 0 && $alreadyApply == 0)
                        <button type="button" class="btn btn-success" name="apply" id="apply" value="mohon"><b>Mohon</b></button>
                    @elseif($applicationExist > 0 && $alreadyApply > 0)
                        <button type="button" class="btn btn-success" name="approve" id="approve" value="terima"><b>Terima</b></button>
                        <button type="button" class="btn btn-danger" name="decline" id="decline" value="tolak"><b>Tolak</b></button>
                    @endif
                @endif
                <button type="button" class="btn btn-secondary" onclick="window.location='/viewoffer'"><b>Tutup</b></button>
                </td>
            </tr>
        </table>
    </div>

    <!-- Modal -->
    <form method="POST" action="/storeapplication" id="applyForm">
        @csrf
        <div class="modal fade" id="applyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="applyModalLabel">Mohon Pekerjaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Adakah anda pasti untuk memohon perkerjaan ini? </p>
                        <div>
                            <label for="reason" class="required">Sebab mohon</label>
                            <input type="text" name="reason" class="form-control" id="reason" required>
                            <input type="number" name="offerId" id="offerId" value="{{ $offer->offer_id }}" hidden>
                        </div>    
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="submit">Mohon</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Confirm job -->
    <form method="POST" action="/confirmapplication" id="confirmForm">
        @csrf
        <div class="modal fade" id="approveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Terima Pekerjaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Adakah anda pasti untuk menerima perkerjaan ini? </p>
                        <input type="number" name="offerId" id="offerId" value="{{ $offer->offer_id }}" hidden>  
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="submitApprove">Terima</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Reject job -->
    <form method="POST" action="/rejectapplication" id="rejectForm">
        @csrf
        <div class="modal fade" id="declineModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineModalLabel">Tolak Pekerjaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Adakah anda pasti untuk menolak perkerjaan ini? </p>
                        <input type="number" name="offerId" id="offerId" value="{{ $offer->offer_id }}" hidden>  
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="submitReject">Tolak</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Leaflet initialization script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
    <script src="{{ asset('js/mapScript.js') }}"></script>
    <script src="{{ asset('js/processApplicationScript.js') }}"></script>
    <script src="{{ asset('js/dateScript.js') }}"></script>

@endsection