<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Resit</title>

    <!-- Tab icon -->
    <link rel="icon" href="{{ asset('images/webicon-756px.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('css/pdfStyle.css') }}" type="text/css"> 
</head>
<body>
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
        <p>Tarikh: {{ $data['createdAt'] }}</p>
    </div>

    <div class="margin-top">
        <table class="full-width">
            <tr>
                <td class="half-width">
                    <div><h5>Kepada:</h5></div>
                    <div>{{ $data['payerName'] }}</div>
                    <div>{{ $data['payerEmail'] }}</div>
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
</body>
</html>
