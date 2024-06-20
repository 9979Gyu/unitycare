@extends('layouts.app')
@section('title')
    UnityCare-Program
@endsection

@section('content')
    
    <h2>Program - Sertai</h2>
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

    <form method="POST" action="/export-participated" class="container" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ $roleID }}" hidden>

        <div class="row mb-3">
            <div class="col-sm-6">
                <select name="organization" id="organization" class="form-control select2 ">
                    @if($roleID == 1 || $roleID == 2)
                        <option value="all" selected>Semua Penganjur</option>
                    @endif
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <select name="program" id="program" class="form-control select2">
                    <option value="all" selected>Semua Program</option>
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
                    <input class="form-check-input" type="radio" name="statusFilter" id="allRadio" value="1" checked>
                    <label class="form-check-label" for="all">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="volunteerRadio" value="2">
                    <label class="form-check-label" for="volunteer">Sukarelawan</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="statusFilter" id="poorRadio" value="3">
                    <label class="form-check-label" for="poor">Peserta</label>
                </div>
                @if(Auth::user()->roleID == 1)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="statusFilter" id="deleteRadio" value="0">
                        <label class="form-check-label" for="delete">Dipadam</label>
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
                <button class="nav-link active" id="participate-tab" data-bs-toggle="tab" data-bs-target="#participate" type="button" role="tab" aria-controls="participate" aria-selected="true">Jadual</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart" type="button" role="tab" aria-controls="chart" aria-selected="false">Carta</button>
            </li>
        </ul>

        <!-- Content for tab -->
        <div class="tab-content m-3" id="tabContent">
            <!-- view participated program -->
            <div class="tab-pane fade show active" id="participate" role="tabpanel" aria-labelledby="participate-tab">
                <div class="table-responsive">
                    <table id="requestParticipatedTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr style="text-align:center">
                                <th> No. </th>
                                <th>Program</th>
                                <th>Jenis</th>
                                <th>Penerangan</th>
                                <th>Lokasi</th>
                                <th>Tarikh Mula</th>
                                <th>Tarikh Tamat</th>
                                <th>Penganjur</th>
                                <th>Tarikh Mohon</th>
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
                    <div class="pieCharts">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dismiss Modal -->
    <div class="modal fade" id="dismissModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dismissModalLabel">Tarik Diri Daripada Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk berhenti menyertai program?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="dismiss">Tarik Diri</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/programs/indexParticipatedScript.js') }}"></script>
    <script src="{{ asset('js/general/dateScript.js') }}"></script>
    <script src="{{ asset('js/general/modalScript.js') }}"></script>
    <script src="{{ asset('js/general/programScript.js') }}"></script>


@endsection