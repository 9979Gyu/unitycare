@extends('layouts.app')
@section('title')
    UnityCare-Program
@endsection

@section('content')
    
    <h2>Program - Permohonan</h2>
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

    <form method="POST" action="/export-programs" class="container" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ $roleID }}" hidden>

        <div class="row mb-3">
            <div class="col-sm-6">
                <select name="organization" id="organization" class="form-control select2 ">
                    @if($roleID == 1 || $roleID == 2)
                        <option value="all">Semua Penganjur</option>
                    @endif
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <select name="type" name="type" id="type" class="form-control select2">
                    <option value="all" selected>Semua Jenis</option>
                    <option value="vol">Sukarelawan</option>
                    <option value="skill">Pembangunan Kemahiran</option>
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
                @if($roleID == 1 || $roleID == 2)
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
                    <th>Program</th>
                    <th>Jenis</th>
                    <th>Penerangan</th>
                    <th>Lokasi</th>
                    <th>Tarikh Mula</th>
                    <th>Tarikh Tamat</th>
                    <th>Sukarelawan Diperlukan</th>
                    <th>B40 / OKU Diperlukan</th>
                    <th>Tarikh Tutup Permohonan</th>
                    <th>Penganjur</th>
                    <th>Status</th>
                    <th>Diproses</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
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

    <!-- Boost Modal -->
    <div class="modal fade" id="boostModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="boostModalLabel">Meningkatkan Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk meningkatkan program?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="boost">Galak</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/general/programScript.js') }}"></script>
    <script src="{{ asset('js/reports/indexProgramScript.js') }}"></script>
    <script src="{{ asset('js/general/dateScript.js') }}"></script>
    <script src="{{ asset('js/general/modalScript.js') }}"></script>


@endsection