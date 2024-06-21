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

    @if ($errors->any())
        <div class="alert alert-danger condition-message"">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
            @if($offer->jobType->job_type_id != 1)
            <tr>
                <th scope="row">Tarikh</th>
                <td colspan="3">{{ $offer->start_date }} Hingga {{ $offer->end_date }}</td>
            </tr>
            @endif
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
                    @if($user->roleID == 5)
                        <!-- is not creator and not yet apply -->
                        @if($offer->user_id != $user->id && $applied == 0)
                            <button type="button" class="btn btn-success" name="apply" id="apply" value="mohon"><b>Mohon</b></button>
                        <!-- applied and approved -->
                        @elseif($applied > 0)
                            @if($approval == 2)
                                <button type="button" class="btn btn-success" name="approve" id="approve" value="terima"><b>Terima</b></button>
                                <button type="button" class="btn btn-danger" name="decline" id="decline" value="tolak"><b>Tolak</b></button>
                            @elseif($approval == 1)
                                <button type="button" class="btn btn-danger" name="dismiss" id="dismiss"><b>Tarik Diri</b></button>
                            @endif
                        @endif
                    @endif

                    @if($type == 'true')
                        <button type="button" class="btn btn-secondary" onclick="window.location='/viewoffer'"><b>Tutup</b></button>
                    @elseif($type == 'permohonan')
                        <button type="button" class="btn btn-secondary" onclick="window.location='/viewapplication'"><b>Tutup</b></button>
                    @endif
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

    <!-- Dismiss Job -->
    <form method="POST" action="/dismissoffer" id="dismissForm">
        @csrf
        <div class="modal fade" id="dismissModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dismissModalLabel">Tarik Diri Daripada Tawaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Adakah anda pasti untuk berhenti memohon pekerjaan ini?
                        <input type="number" name="offerID" id="offerID" value="{{ $offer->offer_id }}" hidden>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="dismiss">Tarik Diri</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Confirm job -->
    <form method="POST" action="/confirmOffer" id="confirmForm">
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
                        <input type="number" name="offerID" id="offerID" value="{{ $offer->offer_id }}" hidden>  
                        <input type="number" name="approval_status" value="2" hidden>  
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
    <form method="POST" action="/confirmOffer" id="rejectForm">
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
                        <input type="number" name="offerID" id="offerID" value="{{ $offer->offer_id }}" hidden>
                        <input type="number" name="approval_status" value="0" hidden>
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
    <script src="{{ asset('js/general/mapScript.js') }}"></script>
    <script src="{{ asset('js/offers/processApplicationScript.js') }}"></script>
    <script src="{{ asset('js/general/dateScript.js') }}"></script>

@endsection