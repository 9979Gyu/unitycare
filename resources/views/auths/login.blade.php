@extends('layouts.app')

@section('title')
    UnityCare-Log Masuk
@endsection

@section('content')

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        Terima Kasih Mengguna UnityCare
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

                        <form method="POST" action="/auth">
                            @csrf
                            <div class="form-group">
                                <label for="username" class="required">Nama Pengguna</label>
                                <input type="text" value="{{ old('username') }}" name="username" class="form-control" id="username" required>
                            </div>
                            <div class="form-group position-relative">
                                <label for="password" class="required">Kata Laluan</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control password" id="password" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text toggle-password" id="toggle-password">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                                                <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                                                <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block mb-2">Log Masuk</button>
                            <a href="#" id="reset-link">Lupa Kata Laluan</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="resetModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetModalLabel">Tukar Kata Laluan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="/reset" id="resetForm">
                        @csrf
                        <div class="modal-body">
                            <div id="more">
                                <label for="reset-username" class="required">Sila masukkan nama pengguna:</label>
                                <input type="text" name="reset-username" class="form-control lowercase" id="reset-username" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" id="reset">Hantar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <br>

    <script src="{{ asset('js/loginScript.js') }}"></script>

@endsection
