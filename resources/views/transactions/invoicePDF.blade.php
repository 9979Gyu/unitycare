@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pdfStyle.css') }}" rel="stylesheet">
@endpush

@section('title')
    UnityCare-Resit
@endsection

@section('content')

    <div class="container">

        <div class="receipt-wrapper">
            <table class="full-width">
                <tr>
                    <td class="half-width">
                        <img src="{{ asset('images/webicon-trans.png') }}" alt="unity care" width="100" />
                    </td>
                    <td class="half-width">
                        <h2><b>RESIT<b></h2>
                    </td>
                    <td class="float-end">
                        <p><b>No. Resit</b>: {{ $data['receiptNo'] }}</p>
                    </td>
                </tr>
            </table>
        
            <div class="margin-top">
                <table class="full-width">
                    <tr>
                        <td class="half-width">
                            <div><h5>Kepada:</h5></div>
                            <div>{{ $data['payerName'] }}</div>
                        </td>
                        <td class="half-width">
                            <div><h5>Daripada:</h5></div>
                            <div>Unity Care</div>
                            <div>info.unitycare@gmail.com</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="margin-top">
                <p>Transaksi ID: <b>{{ $data['transactionID'] }}</b></p>
            </div>
        
            <div class="margin-top">
                <table class="products">
                    <tr>
                        <th>Item</th>
                        <th>Nilai ({{ $data['currency'] }})</th>
                    </tr>
                    <tr class="items">
                        <td>
                            {{ $data['description'] }}
                        </td>
                        <td>
                            {{ $data['price'] }}
                        </td>
                    </tr>
                </table>
            </div>
        
            <div class="total">
                Jumlah: {{ $data['price'] }} {{ $data['currency'] }}
            </div>
        
            <hr>

            <div class="footer margin-top">
                <div>&copy; UnityCare</div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-sm-auto">
                <button type="button" class="btn btn-primary btn-block mb-2" onclick="window.location.href='print-invoice'">Cetak</button>
            </div>
            <div class="col-sm-auto">
                <button type="button" class="btn btn-danger btn-block mb-2" onclick="window.location.href='/'">Tutup</button>
            </div>
        </div>
    </div>

@endsection
