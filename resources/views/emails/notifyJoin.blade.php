<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: black;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #2176e9ff;
        }

        .button-container {
            text-align: center;
            margin: 20px 0;
        }

        .button-container button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        .button-container button a {
            color: white;
            text-decoration: none;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 200px;
        }

        .message {
            padding: 20px;
            background-color: #ffffff;
        }

        .message p {
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>

</head>

<body>
    <div class="container">
        <div class="message m-5 logo">
            <h2>UnityCare</h2>
        </div>
        <div class="message">
            <p>Hi {{ $mailData['name'] }},</p>
            <br>
            <p>Terima kasih mengguna sistem kami. </p>
            <br>
            <p>
                @if($mailData['approval'] == 1)
                    Permohonan anda terhadap {{ $mailData['subject'] }} <b>{{ $mailData['offer'] }}</b> telah dihantar untuk kelulusan. 
                @elseif($mailData['approval'] == 2)
                    Tahniah. Permohonan anda terhadap {{ $mailData['subject'] }} <b>{{ $mailData['offer'] }}</b> telah <b>Dilulus</b> pada {{ $mailData['datetime'] }}. 
                @elseif($mailData['approval'] == 0)
                    Maaf. Permohonan anda terhadap {{ $mailData['subject'] }} <b>{{ $mailData['offer'] }}</b> telah <b>Ditolak</b> pada {{ $mailData['datetime'] }}.
                @endif
            </p>
            <br>
            <p>Sekian,</p>
            <p>UnityCare</p> 
        </div>
    </div>
</body>

</html>
