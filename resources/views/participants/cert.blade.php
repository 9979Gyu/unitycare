<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>Sijil Penyertaan</title>

    <!-- Tab icon -->
    <link rel="icon" href="{{ asset('images/webicon-756px.png') }}" type="image/png">

    <style>
        body {
            background-image: url('public/backgrounds/certBg2.png');
            background-color: rgba(10, 17, 53, 0.904);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 0;
            padding-top: 50px;
            text-align: center;
        }

        table {
            width: 100%;
            max-width: 100%;
            padding: 50px 20px;
        }

        .title-img img {
            width: 130px;
            height: auto;
        }

        h1, h3, p{
            text-align: center;
        }

        h1{
            font-size: 70px;
        }

        h3{
            font-size: 30px;
        }

        .title-img {
            text-align: right;
        }

        .title h2 {
            text-align: left;
            font-size: 50px;
        }

        .signature{
            text-align: center;
        }

        .signature img {
            background-image: url('public/backgrounds/signature.png');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            border-bottom: 1px solid rgba(10, 17, 53, 0.904);
        }
    </style>
</head>
<body>
    <div class="container">
        <table>
            <tr>
                <td>
                    <div class="title-img">
                        <img src="{{ public_path('public/user_images/default_image.png') }}" alt="unity care" />
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
                    <h3>{{ $data->userName }}</h3>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>for successfully completing</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>{{ $data->programName }}</h3>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>on {{ $data->formatted_date }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="signature">
                        <span><img src="{{ public_path('public/backgrounds/signature.png') }}" alt="Unity Care" /></span><br>
                        <span>UnityCare</span>
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
