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

    <br>

    <form method="POST" action="/export-offers" class="container" id="excel">
        @csrf

        <input type="text" id="roleID" name="roleID" value="{{ $roleID }}" hidden>
        <div class="row mb-3">
            <div class="col-sm-12">
                <select name="organization" id="organization" class="form-control select2 ">
                    @if($roleID == 1 || $roleID == 2)
                        <option value="all">Semua Organisasi</option>
                    @endif
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6">
                <select name="jobname" id="jobname" class="form-control select2 ">
                    <option value="all">Semua Pekerjaan</option>
                </select>
            </div>
            <div class="col-sm-6">
                <select name="position" id="position" class="form-control select2">
                    <option value="all">Semua Jawatan</option>
                </select>
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
                @if(Auth::user()->roleID == 1)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="statusFilter" id="deleteRadio" value="4">
                        <label class="form-check-label" for="decline">Dipadam</label>
                    </div>
                @endif
            </div>
            <div class="col">
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
                    <th>Pekerja Diperlukan</th>
                    <th>Penerangan</th>
                    <th>Pengurus</th>
                    <th>Status</th>
                    <th>Diproses</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Lulus Perkerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk meluluskan perkerjaan?

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="approve">Lulus</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Modal -->
    <div class="modal fade" id="declineModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="declineModalLabel">Tolak Perkerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Adakah anda pasti untuk menolakkan perkerjaan? </p>
                    <div>
                        <label for="reason" class="required">Sebab</label>
                        <select name="reason" id="reason" class="form-select">
                            <option value="0" selected>Pilih Sebab</option>
                            <option value="missing">Kekurangan maklumat</option>
                            <option value="unclear">Penerangan tidak jelas</option>
                            <option value="others">Lain-lain</option>
                        </select>
                        <br>
                        <div id="more">
                            <label for="explain" class="required">Penerangan</label>
                            <input type="text" name="explain" class="form-control" id="explain" placeholder="Tidak sesuai untuk B40/OKU" required>
                        </div>
                    </div>      

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="decline">Tolak</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Padam Perkerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk memadam perkerjaan?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete">Padam</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/reports/indexOfferScript.js') }}"></script>
    <script src="{{ asset('js/modalScript.js') }}"></script>
    <script src="{{ asset('js/dateScript.js') }}"></script>


@endsection