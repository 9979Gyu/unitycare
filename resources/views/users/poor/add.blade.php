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

    <div id="confirmModal">
        <p>
            Halaman ini digunakan khas oleh 
            <ul>
                <li><b>orang yang menghadapi masalah kewangan untuk kelangsungan hidup</b></li>
                <li><b>orang kurang upaya</b></li>
            </ul>
            untuk mencari pekerjaan dan membangunkan kemahiran baharu untuk meningkatkan kebolehpasaran seseorang individu.
        </p>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmBox">
            Pengesahan IC
        </button>

        <!-- Modal -->
        <div class="modal fade" id="confirmBox" tabindex="-1" aria-labelledby="confirmBoxLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmBoxLabel">Pengesahan IC</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <label for="ic" class="col-sm-2 col-form-label required">IC</label>
                            <div class="col-sm-10">
                                <input type="text" name="ic" class="form-control touppercase" id="ic" pattern="\d{12}" title="Sila berikan nombor IC yang betul" required placeholder="Contoh: 021221041234">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Hantar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="/store" method="post" class="container" id="addForm">
        @csrf
        <div class="mb-3">
            <h5>Maklumat Peribadi</h5>
        </div>
        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label required">Nama</label>
            <div class="col-sm-10">
                <input type="text" name="name" class="form-control touppercase" id="name" value="{{ old('name') }}" required readonly>
            </div>
        </div>

        <div class="row mb-3">
            <label for="ICNo" class="col-sm-2 col-form-label required">IC No</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('ICNo') }}" name="ICNo" class="form-control" id="ICNo" pattern="\d{12}" title="Sila berikan nombor IC yang betul" required placeholder="Contoh: 021221041234" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <label for="contactNo" class="col-sm-2 col-form-label required">Nombor Telefon (60+)</label>
            <div class="col-sm-4">
                <input type="text" value="{{ old('contactNo') }}" name="contactNo" class="form-control" id="contactNo" pattern="\d{9,10}" title="Sila berikan nombor telefon yang betul" required readonly>
            </div>

            <label for="officeNo" class="col-sm-2 col-form-label">Nombor Telefon Pejabat</label>
            <div class="col-sm-4">
                <input type="number" value="{{ old('officeNo') }}" name="officeNo" class="form-control" id="officeNo" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label required">Alamat</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('address') }}" name="address" class="form-control touppercase" id="address" required readonly>
            </div>
        </div>

        <!-- auto sort state and city based on postal code -->
        <div class="row mb-3">
            <label for="postalCode" class="col-sm-2 col-form-label required">Poskod</label>
            <div class="col-sm-4">
                <input type="number" name="postalCode" class="form-control" id="postalCode" required readonly>
            </div>
            <label for="state" class="col-sm-2 col-form-label required">Negeri</label>
            <div class="col-sm-4">
                <select name="state" id="state" class="form-select">
                    <option selected>Pilih Negeri</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            
            <label for="city" class="col-sm-2 col-form-label required">Bandar</label>
            <div class="col-sm-10">
                <select name="city" id="city" class="form-select">
                    <option selected>Pilih Bandar</option>
                </select>
            </div>
        </div>

        <br>

        <div class="mb-3">
            <h5>Maklumat Akaun</h5>
        </div>
        <div class="row mb-3">
            <label for="username" class="col-sm-2 col-form-label required">Nama Pengguna</label>
            <div class="col-sm-10">
                <input type="text" value="{{ old('username') }}" name="username" class="form-control" id="username" pattern=".{3,}" maxlength="25" title="Three or more characters">
            </div>
        </div>

        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label required">Emel</label>
            <div class="col-sm-10">
                <input type="email" value="{{ old('email') }}" name="email" class="form-control touppercase" id="email" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <label for="password" class="col-sm-2 col-form-label required">Kata Laluan</label>
            <div class="col-sm-10">
                <input type="password" name="password" class="form-control" id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one  number and one uppercase and lowercase letter, and at least 8 or more characters">
                <input type="number" name="roleID" value="5" hidden>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary">Hantar</button>
                <button type="button" onclick="window.location='/'" class="btn btn-danger">Tutup</button>
            </div>
        </div>

    </form>

    <br>

    <script type="text/javascript">
        $(document).ready(function(){
            $("#addForm").hide();

            $('#confirmBox .btn-primary').click(function(e) {

                $.post('/checkUser', {
                    ic: $('#ic').val(), 
                    _token: '{{ csrf_token() }}'
                }, 
                function(data) {
                    // The server should return a JSON object with a 'success' property
                    if (data.success) {
                        $("#confirmModal").hide();
                        $('#confirmBox').modal('hide');
                        $('#addForm').show();
                        document.body.style.overflow = 'auto';

                        $('#name').val(data.user.name);
                        $('#ICNo').val(data.user.ICNo);
                        $('#contactNo').val(data.user.contactNo);
                        $('#address').val(data.user.address);
                        $('#postalCode').val(data.user.postcode);
                        $('#state').empty();
                        $('#city').empty();
                        $("#state").append('<option>' + data.user.state + '</option>');
                        $("#city").append('<option>' + data.user.city + '</option>');
                        $('#email').val(data.user.email);
                    } else {
                        alert('IC not found');
                    }
                });
            });


            $("#postalCode").on('change', function(){
                var postcode = $(this).val();
                if(postcode){
                    $.ajax({
                        url: '/search',
                        type: 'GET',
                        data: {postcode: postcode},
                        success: function(data){
                            $('#state').empty();
                            $("#city").empty();
                            data.forEach(function(item){
                                $("#state").append('<option>' + item.state + '</option>');
                                $("#city").append('<option>' + item.city + '</option>');
                            });
                        }
                    });
                }
                else{
                    $('#state').empty();
                    $("#city").empty();
                    $("#state").append('<option selected>Pilih Negeri</option>');
                    $("#city").append('<option selected>Pilih Bandar</option>');
                }
            });
        });
    </script>

@endsection