@extends('layouts.app')
@section('title')
    UnityCare-Pekerjaan
@endsection

@section('content')
    
    <h2>Pekerjaan - Permohonan</h2>
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

    <br>

    <form method="POST" action="/export-applies" class="container" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ $roleNo }}" hidden>
        <div class="row mb-3">
            <div class="col-sm-12">
                <select name="organization" id="organization" class="form-control select2 ">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6">
                <select name="jobname" id="jobname" class="form-control select2 ">
                    <!-- <option value="all">Semua Pekerjaan</option> -->
                    @foreach($applications as $application)
                        <option value="{{ $application }}">{{ $application }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <select name="position" id="position" class="form-control select2">
                    <option value="all">Semua Jawatan</option>
                </select>
            </div> 
        </div>

        <div class="row mb-3">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="startDate">Dari</label>
                    <input type="date" class="form-control" id="startDate1" name="startDate" placeholder="Dari">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="endDate">Hingga</label>
                    <input type="date" class="form-control" id="endDate1" name="endDate" placeholder="Hingga">
                </div>
            </div> 
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="allRadio" value="3" checked>
                    <label class="form-check-label" for="all">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="pendingRadio" value="1">
                    <label class="form-check-label" for="pending">Belum selesai</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="approveRadio" value="2">
                    <label class="form-check-label" for="approve">Diterima</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="declineRadio" value="0">
                    <label class="form-check-label" for="decline">Ditolak</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="confirmRadio" value="is_selected">
                    <label class="form-check-label" for="confirm">Terima Kerja</label>
                </div>
                @if(Auth::user()->roleID == 1)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="statusFilter" id="deleteRadio" value="4">
                        <label class="form-check-label" for="decline">Dipadam</label>
                    </div>
                @endif
            </div>
            <div class="col">
                <button class="btn btn-outline-secondary float-end ml-2" type="button" id="resetBtn">Padam</button>
                <button class="btn btn-outline-primary float-end" type="button" id="excelBtn">Excel</button>
            </div>
        </div>

        <div class="modal fade" id="dateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dateModalLabel">Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">        
                        <div class="form-group">
                            <label for="startDate required">Dari</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate required">Hingga</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" required>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="applyDates">Eksport</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </form>


    <div class="table-responsive">
        <table id="requestTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead>
                <tr style="text-align:center">
                    <th> No. </th>
                    <th>Pekerjaan</th>
                    <th>Jenis</th>
                    <th>Syif</th>
                    <th>Lokasi</th>
                    <th>Tarikh</th>
                    <th>Masa</th>
                    <th>Purata Gaji</th>
                    <th>Sebab Mohon</th>
                    <th>Tarikh Mohon</th>
                    <th>Pengurus</th>
                    <th>Status</th>
                    <th>Diproses Pada</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <!-- Confirm job -->
    <div class="modal fade" id="approveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Terima Pekerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Adakah anda pasti untuk menerima perkerjaan ini? </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="approve">Terima</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject job -->
    <div class="modal fade" id="declineModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="declineModalLabel">Tolak Pekerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Adakah anda pasti untuk menolak perkerjaan ini? </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="decline">Tolak</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/general/dateScript.js') }}"></script>
    <script src="{{ asset('js/general/offerScript.js') }}"></script>
    <script src="{{ asset('js/offers/viewApplicationScript.js') }}"></script>
    <script src="{{ asset('js/general/modalScript.js') }}"></script>


@endsection