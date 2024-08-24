@extends('layouts.app')

@section('title')
    UnityCare-Sumbangan
@endsection

@section('content')

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        Sumbangan Kepada UnityCare
                    </div>
                    <div class="card-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="/process-transaction">
                            @csrf
                            <div class="form-group position-relative">
                                <label for="password" class="required">Derma ({{ $paypalCurrency }})</label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" id="amount" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary mx-2">Bayar</button>
                                <button type="button" onclick="window.location.href='/'" class="btn btn-danger mx-2">Tutup</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
