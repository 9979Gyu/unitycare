<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Resit</title>

    <!-- Tab icon -->
    <link rel="icon" href="{{ public_path('images/webicon-512px.png') }}" type="image/png">

    <style>
        h4 {
            margin: 0;
        }
        
        .full-width {
            width: 100%;
        }
        
        .half-width {
            width: 50%;
        }
        
        .float-end {
            text-align: right;
        }
        
        .margin-top {
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-spacing: 0;
        }
        
        table.products tr {
            text-align: center;
            background-color: rgba(33, 116, 233, 0.808);
        }
        
        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }
        
        table tr.items {
            text-align: center;
            background-color: whitesmoke;
        }
        
        table tr.items td {
            padding: 0.5rem;
        }
        
        .total {
            text-align: right;
            margin: 20px 0;
        }
        
        .footer {
            font-size: 13px;
            padding: 10px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <table class="full-width">
        <tr>
            <td class="half-width">
                <img src="{{ public_path('public/user_images/default_image.png') }}" alt="unity care" width="100" />
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
