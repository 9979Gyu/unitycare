@extends('layouts.app')
@section('title')
    UnityCare-Add
@endsection

@section('content')

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

    <br>

    <div id="confirmModal">
        <p>
            Halaman ini digunakan khas untuk mendaftar
            <ul>
                <li><b>syarikat yang telah daftar di Suruhanjaya Syarikat Malaysia (SSM)</b></li>
            </ul>
            untuk menawarkan pekerjaan dan program pembangunan kemahiran baharu kepada individu yang mempunyai masalah kewangan.
        </p>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmBox">
            Pengesahan Nombor Pendaftaran SSM
        </button>

        <!-- Modal -->
        <form method="post" action="/check-user" id="checkform">
            @csrf
            <div class="modal fade" id="confirmBox" tabindex="-1" aria-labelledby="confirmBoxLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmBoxLabel">Pengesahan Nombor Pendaftaran SSM</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="ssmRegNo" class="col-form-label required">Nombor Pendaftaran</label>
                                <div class="col-sm-12">
                                    <input type="text" name="usertype" id="usertype" value="enterprise" hidden>
                                    <input type="number" name="roleID" value="3" class="form-control" id="roleID" hidden>
                                    <input type="text" name="ssmRegNo" class="form-control touppercase" id="ssmRegNo" required placeholder="Contoh: 202005123456">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Hantar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <br>

    <script src="{{ asset('js/general/postcodeScript.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#addForm").hide();
        });
    </script>

@endsection