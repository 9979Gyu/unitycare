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

    <form method="POST" action="/export-programs" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ Auth::user()->roleID }}" hidden>
        <input type="text" id="actionName" name="actionName" value="/export-programs" hidden>
        <div class="row mb-3">
            <div class="col">
                <select name="type" name="type" id="type" class="form-control">
                    <option value="3" selected>Semua Programs</option>
                    <option value="1">Sukarelawan</option>
                    <option value="2">Pembangunan Kemahiran</option>
                </select>
            </div>
            <div class="col">
                <button class="btn btn-outline-primary float-end" type="button" id="excelBtn">Excel</button>
            </div>
        </div>

        <div class="pb-2">
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
                    <th>Nama</th>
                    <th>Tempat</th>
                    <th>Bermula</th>
                    <th>Tamat</th>
                    <th>Penerangan</th>
                    <th>Pengurus</th>
                    <th>Tarikh Tutup Permohonan</th>
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
                    <h5 class="modal-title" id="approveModalLabel">Lulus Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk meluluskan program?

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
                    <h5 class="modal-title" id="declineModalLabel">Tolak Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Adakah anda pasti untuk menolakkan program? </p>
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
                            <input type="text" name="explain" class="form-control" id="explain" placeholder="Tidak sesuai untuk peserta" required>
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
                    <h5 class="modal-title" id="deleteModalLabel">Padam Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk memadam program?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete">Padam</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the date modal
    @include('exports.datemodal') -->

    <script src="{{ asset('js/indexProgramScript.js') }}"></script>
    <script src="{{ asset('js/modalScript.js') }}"></script>


@endsection