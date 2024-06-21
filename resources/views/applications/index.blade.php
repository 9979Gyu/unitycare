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

    <form method="POST" action="/export-applications" class="container" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ $roleNo }}" hidden>
        <div class="row mb-3">
            <div class="col-sm-12">
                <select name="organization" id="organization" class="form-control select2 ">
                    @if($roleNo == 1 || $roleNo == 2)
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
                <select name="job" id="job" class="form-control select2 ">
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
                    <label class="form-check-label" for="confirm">Telah Terima</label>
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

    <div>
        <!-- Tab for program and job -->
        <ul class="nav nav-tabs" id="tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="offer-tab" data-bs-toggle="tab" data-bs-target="#offer" type="button" role="tab" aria-controls="offer" aria-selected="true">Jadual</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart" type="button" role="tab" aria-controls="chart" aria-selected="false">Carta</button>
            </li>
        </ul>

        <!-- Content for tab -->
        <div class="tab-content m-3" id="tabContent">
            <!-- request -->
            <div class="tab-pane fade show active" id="offer" role="tabpanel" aria-labelledby="offer-tab">
                <div class="table-responsive">
                    <table id="requestTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr style="text-align:center">
                                <th> No. </th>
                                <th>Pemohon</th>
                                <th>Alamat</th>
                                <th>Peringkat Pendidikan</th>
                                <th>Kategori</th>
                                <th>Sebab Mohon</th>
                                <th>Tarikh Mohon</th>
                                <th>Jawatan</th>
                                <th>Status</th>
                                <th>Diproses</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- chart -->
            <div class="tab-pane fade" id="chart" role="tabpanel" aria-labelledby="chart-tab">
                <div class="justify-content-center d-flex m-2">
                    <div class="barCharts">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Lulus Permohonan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk meluluskan permohonan?

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
                    <h5 class="modal-title" id="declineModalLabel">Tolak Permohonan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Adakah anda pasti untuk menolakkan permohonan? </p>
                    <div>
                        <label for="reason" class="required">Sebab</label>
                        <select name="reason" id="reason" class="form-select">
                            <option value="0" selected>Pilih Sebab</option>
                            <option value="unclear">Penerangan tidak jelas</option>
                            <option value="others">Lain-lain</option>
                        </select>
                        <br>
                        <div id="more">
                            <label for="explain" class="required">Penerangan</label>
                            <input type="text" name="explain" class="form-control" id="explain" placeholder="Tidak sesuai untuk pemohon" required>
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

    <script src="{{ asset('js/general/offerScript.js') }}"></script>
    <script src="{{ asset('js/offers/indexApplicationScript.js') }}"></script>
    <script src="{{ asset('js/general/modalScript.js') }}"></script>


@endsection