@extends('layouts.app')
@section('title')
    UnityCare-Bayaran
@endsection

@section('content')
    
    <h2>Bayaran Terima</h2>
    <br>

    @if (session()->has('success'))
        <div class="alert alert-success condition-message">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger condition-message">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <br>

    <form method="POST" action="/export-payments" id="excel">
        @csrf
        <input type="number" name="roleID" id="roleID" value="{{ $roleNo }}" hidden>
        <input type="text" id="checkPoint" name="checkPoint" value="receive" hidden>
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
            <div class="col">
                <button class="btn btn-outline-secondary float-end ml-2" type="button" id="resetBtn">Padam</button>
                <button class="btn btn-outline-primary float-end mx-1" type="submit" id="excelBtn">Excel</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table id="requestTable" class="table table-bordered table-striped dt-responsive display datatable" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead>
                <tr style="text-align:center">
                    <th> No. </th>
                    <th>No Rujukan</th>
                    <th>Nama Pembayar (Nama UnityCare)</th>
                    <th>Tujuan</th>
                    <th>Nilai ({{ $paypalCurrency }})</th>
                    <th>Tarikh</th>
                    <th>Nama Penerima</th>
                    <th>Status Bayaran</th>
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
                    <h5 class="modal-title" id="deleteModalLabel">Padam Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Adakah anda pasti untuk memadam data?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete">Padam</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <form method="post" action="/print-invoice">
        @csrf
        <div class="modal fade" id="printModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printModalLabel">Cetak Resit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Adakah anda pasti untuk mencetak resit transaksi?
                    </div>
                    <input type="text" id="referenceNo" name="referenceNo" value="" hidden>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="print">Cetak</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="{{ asset('js/general/modalScript.js') }}"></script>
    <script src="{{ asset('js/transactions/indexScript.js') }}"></script>

@endsection