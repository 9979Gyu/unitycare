<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Sijil Penyertaan</title>

    <!-- Tab icon -->
    <link rel="icon" href="{{ asset('images/webicon-756px.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('css/certStyle.css') }}" type="text/css"> 
</head>
<body>
    <div class="container">
        <table>
            <tr>
                <td>
                    <div class="title-img">
                        <img src="{{ asset('images/webicon-trans.png') }}" alt="unity care" />
                    </div>
                </td>
                <td>
                    <div class="title">
                        <h2>UnityCare</h2>
                    </div>
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <h1>Certificate</h1>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>has been presented to</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>[Your Name]</h3>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>on [The Program End Date] for successfully completing</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>[Program Name]</h3>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
