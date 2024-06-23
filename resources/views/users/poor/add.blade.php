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
                <li><b>orang yang menghadapi masalah kewangan untuk kelangsungan hidup</b></li>
                <li><b>orang kurang upaya</b></li>
            </ul>
            untuk mencari pekerjaan dan membangunkan kemahiran baharu untuk meningkatkan kebolehpasaran seseorang individu.
        </p>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmBox">
            Pengesahan Nombor Pengenalan
        </button>

        <!-- Modal -->
        <form method="post" action="check-user" id="checkForm">
            @csrf
            <div class="modal fade" id="confirmBox" tabindex="-1" aria-labelledby="confirmBoxLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmBoxLabel">Pengesahan Nombor Pengenalan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="ic" class="col-form-label required">Nombor Pengenalan</label>
                                <div class="col-sm-12">
                                    <input type="number" name="roleID" value="5" class="form-control" id="roleID" hidden>
                                    <input type="text" name="ic" class="form-control" id="ic" pattern="\d{12}" title="Sila berikan nombor IC yang betul" required placeholder="Contoh: 021221041234">
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