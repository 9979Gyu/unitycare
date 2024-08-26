@extends('layouts.app')

@section('title')
    UnityCare-Resit
@endsection

@section('content')

    <br>

    <div class="container">

        <form method="POST" action="/print-invoice" class="container" id="pdf">
            @csrf
            <div class="row mb-4 justify-content-between align-items-center">
                <div class="col-auto d-flex justify-content-start">
                    <img src="{{ asset('images/webicon-trans.png') }}" alt="unity care" width="100" class="img-fluid" />
                </div>
                <div class="col text-center">
                    <h2><b>RESIT</b></h2>
                </div>
                <div class="col-auto d-flex justify-content-end">
                    <div>No. Resit: {{ $data['receiptNo'] }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-12">
                    <p>Tarikh: {{ $data['createdAt'] }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-group">
                        <div><h5>Kepada:</h5></div>
                        <div>{{ $data['payerName'] }}</div>
                        <div>{{ $data['payerEmail'] }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <div><h5>Daripada:</h5></div>
                        <div>Unity Care</div>
                        <div>info.unitycare@gmail.com</div>
                    </div>
                </div> 
            </div>

            <div class="row mb-3">
                <div class="col-sm-12">
                    <p>Transaksi ID: <b>{{ $data['transactionID'] }}</b></p>
                </div>
            </div>

            <div class="table-responsive">
                <table id="requestTable" class="table table-bordered table-striped dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr style="text-align:center">
                            <th>Item</th>
                            <th>Nilai ({{ $data['currency'] }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="text-align:center">
                            <td>{{ $data['description'] }}</td>
                            <td>{{ $data['price'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mb-3">
                <div class="col-sm-12 float-end">
                    <p>Jumlah: <b>{{ $data['price'] }}</b> {{ $data['currency'] }}</p>
                </div>
            </div>

            <hr>

            <div class="mb-4 footer">
                <div>&copy; UnityCare</div>
            </div>

            <div class="row justify-content-center">
                <div class="col-sm-auto">
                    <button type="submit" class="btn btn-primary btn-block mb-2">Cetak</button>
                </div>
                <div class="col-sm-auto">
                    <button type="button" class="btn btn-danger btn-block mb-2" onclick="window.location.href='/'">Tutup</button>
                </div>
            </div>
            
        </form>

    </div>

@endsection
