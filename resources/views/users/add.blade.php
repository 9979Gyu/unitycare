@extends('layouts.app')
@section('content')
<h1>New Staff</h1>    
    @if($errors)
        @foreach($errors->all() as $error)
            <div class="error">{{ $error }}</div>
        @endforeach
    @endif

    <form action="" method="post">
        <table class="center">
            @csrf
            <tr>
                <td><label for="">Name </label></td>
                <td> : </td>
                <td><input type="text" name="name"></td>
            </tr>
            <br>
            <tr>
                <td><label for="">Contact No </label></td>
                <td> : </td>
                <td><input type="number" name="contactNo"></td>
            </tr>
            <tr>
                <td><label for="">Address </label></td>
                <td> : </td>
                <td><input type="text" name="address"></td>
            </tr>
            <tr>
                <td><label for="">State </label></td>
                <td> : </td>
                <td><select name="state" id="state"></select></td>
            </tr>
            <br>
        </table>

        <table>
            <tr>
                <td><label for="">Email </label></td>
                <td> : </td>
                <td><input type="email" name="email"></td>
            </tr>
            <tr>
                <td><label for="">Password </label></td>
                <td> : </td>
                <td><input type="password" name="passowrd"></td>
            </tr>
            <tr>
                <td><label for="">Username </label></td>
                <td> : </td>
                <td><input type="text" name="username"></td>
            </tr>
        </table>
        <div class="center2"><input class="btn-sub center2"" type="submit"></div>
    </form>
    <br>
    <div class="center2"><a class="center2" href=""> Home </a></div>
@endsection