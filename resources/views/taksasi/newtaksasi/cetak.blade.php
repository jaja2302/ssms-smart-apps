<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="{{ asset('vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="{{ asset('css/tabletaksasi.css') }}"> -->
    <style>
        table.table-bordered>thead>tr>th {
            border: 1px solid rgb(0, 0, 0);
        }

        /* Centering the image */
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            border: 1px solid black;
            height: 80vh;
            /* margin: 20px 0; */
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        table.table-bordered>tbody>tr>td {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-active>thead>tr>th {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-active>tbody>tr>td {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-primary>thead>tr>th {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-primary>tbody>tr>td {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-bordered>tbody>tr>th {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-warning>thead>tr>th {
            border: 1px solid rgb(0, 0, 0);
        }

        table.table-warning>tbody>tr>td {
            border: 1px solid rgb(0, 0, 0);
        }

        @font-face {
            font-family: "Roboto Regular";
            src: url('fonts/Roboto-Regular.ttf') format('truetype');
        }

        @font-face {
            font-family: "Roboto Bold";
            src: url('fonts/Roboto-Bold.ttf') format('truetype');
        }

        body {
            font-family: "Roboto Regular", sans-serif;
        }

        h1,
        h2 {
            font-family: "Roboto Bold", sans-serif;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>


<body>
    <table class="table table-bordered " style="font-size: 11px;">
        <thead>
            <tr>
                <th colspan="17" class="text-center"
                    style="padding:10px;background: #D9E1F2;border: 1px solid black;font-size:16px">
                    LAPORAN TAKSASI PANEN
                </th>
            </tr>


            <tr>
                <th style=" color: white;border-left:1px solid white;border-right:1px solid white" colspan="17">|
                </th>
            </tr>
            <tr>
                <th colspan="3" style="font-size: 13px;
               padding:8px;text-align: left;background:#D9E1F2;">
                    ESTATE : {{$namaEstate}}
                </th>

                <th colspan="8" style="border-top: 2px solid white;border-bottom:1px solid white"></th>

                <th colspan="6" style="padding:8px;font-size: 13px;text-align: left;background:#D9E1F2;">
                    TANGGAL TAKSASI : {{$today}}</th>
            </tr>
            <tr>
                <th colspan="3" style="font-size: 13px;
               padding:8px;text-align: left;background:#D9E1F2;border:1px solid black">
                    WILAYAH : {{$wil}}
                </th>

                <th colspan="8" style="border-right: 1px solid black;border-bottom: 1px solid white"></th>


                <th colspan="6"
                    style="padding:8px;font-size: 13px;text-align: left;background:#D9E1F2;border:1px solid black">
                    TANGGAL PANEN : {{$besok}}</th>
            </tr>
            <tr>
                <th style=" color: white;border-left:1px solid white;border-right:1px solid white" colspan="17">|
                </th>
            </tr>
        </thead>
        <thead class="text-center" style="border: 1px solid black">
            <tr style="background: #D9E1F2;height:40px;">
                <th style="border:1px solid black;padding:10px;width:5%;">AFDELING</th>
                <th style="border:1px solid black;padding:10px;width:5%;">BLOK</th>
                <th style="border:1px solid black;padding:10px;width:5%;">LUAS (HA)</th>
                <th style="border:1px solid black;padding:10px;width:5%;">POKOK PRODUKTIF</th>
                <th style="border:1px solid black;padding:10px;width:5%;">INTERVAL PANEN</th>
                <th style="border:1px solid black;padding:10px;width:5%;">SPH (Pkk/Ha)</th>
                <th style="border:1px solid black;padding:10px;width:5%;">BJR (Kg/Jjg)</th>
                <th style="border:1px solid black;padding:10px;width:7%;">NO. BARIS</th>
                <th style="border:1px solid black;padding:10px;width:5%;">SAMPEL PATH</th>
                <th style="border:1px solid black;padding:10px;width:5%;">POKOK TAKSASI</th>
                <th style="border:1px solid black;padding:10px;width:5%;">JANJANG TAKSASI</th>
                <th style="border:1px solid black;padding:10px;width:5%">AKP</th>
                <th style="border:1px solid black;padding:10px;width:5%">JUMLAH JANJANG TAKSASI</th>
                <th style="border:1px solid black;padding:10px;">TAKSASI (Kg)</th>
                <th style="border:1px solid black;padding:10px;">KEB. PEMANEN (Ha/HK)</th>
                <th style="border:1px solid black;padding:10px;">KEB. PEMANEN (Kg/HK)</th>
                <th style="border-right:1px solid black;padding:10px;">RITASE</th>
            </tr>
        </thead>
        <tbody style="font-size: 12px;font-weight: 400">
            @foreach($new_tak as $key => $value)
            @foreach($afd_tak as $afd => $valuex)
            @if($key === $afd)
            @foreach ($value as $key2 => $item)
            <tr style="background:white; vertical-align:middle; text-align:center;">
                <td style="border:1px solid black;padding:7px;">{{$key}}</td>
                <td style="border:1px solid black;padding:7px;">{{$key2}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['luas']}}</td>
                <td style="border:1px solid black;padding:7px;">
                    {{ $item['pokok_produktif'] != 0 ? $item['pokok_produktif'] : '-' }}
                </td>
                <td style="border:1px solid black;padding:7px;">{{$item['interval_panen']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['sph']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['bjr']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['br_kiri']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['jumlah_path']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['jumlah_pokok']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['jumlah_janjang']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['akp']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['jjg_taksasi']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$item['taksasi']}}</td>
                <td style="border:1px solid black;padding:7px;">-</td>
                <td style="border:1px solid black;padding:7px;">
                    {{ $item['keb_pemanen_kg_per_hk'] != 0 ? $item['keb_pemanen_kg_per_hk'] : '-' }}
                </td>
                <td style="border:1px solid black;padding:7px;">{{$item['ritase']}}</td>
            </tr>
            @endforeach
            <tr style="background: #E2EFDA;font-weight:bold; vertical-align:middle; text-align:center;">
                <td style="border:1px solid black;padding:7px;" colspan="2">Total</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['luas']}}</td>
                <td style="border:1px solid black;padding:7px;">
                    {{ $valuex['pokok_produktif'] != 0 ? $valuex['pokok_produktif'] : '-' }}
                </td>
                <td style="border:1px solid black;padding:7px;">
                    {{ $valuex['interval_panen'] != 0 ? $valuex['interval_panen'] : '-' }}
                </td>
                <td style="border: 1px solid black; padding: 7px;">{{ intval($valuex['sph']) }}</td>
                <td style="border: 1px solid black; padding: 7px;">{{ intval($valuex['bjr']) }}</td>
                <td style="border:1px solid black;padding:7px;">-</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['jumlah_path']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['jumlah_pokok']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['jumlah_janjang']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['akp']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['jjg_taksasi']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['taksasi']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['keb_pemanen_ha_per_hk']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['keb_pemanen_kg_per_hk']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$valuex['ritase']}}</td>
            </tr>
            @endif
            @endforeach
            @endforeach

            <tr style="background: #D9E1F2;font-weight: bold; vertical-align:middle; text-align:center;">
                <td style="border:1px solid black;padding:7px;" colspan="2">Estate</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['luas']}}</td>
                <td style="border:1px solid black;padding:7px;">
                    {{ $takest['pokok_produktif'] != 0 ? $takest['pokok_produktif'] : '-' }}
                </td>
                <td style="border:1px solid black;padding:7px;">
                    {{ $takest['interval_panen'] != 0 ? $takest['interval_panen'] : '-' }}
                </td>
                <td style="border:1px solid black;padding:7px;">{{ intval($takest['sph']) }}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['bjr']}}</td>
                <td style="border:1px solid black;padding:7px;">-</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['jumlah_path']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['jumlah_pokok']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['jumlah_janjang']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['akp']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['jjg_taksasi']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['taksasi']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['keb_pemanen_ha_per_hk']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['keb_pemanen_kg_per_hk']}}</td>
                <td style="border:1px solid black;padding:7px;">{{$takest['ritase']}}</td>
            </tr>
        </tbody>

    </table>

    <div style="font-style: italic;">* Asumsi 1 rit = 6500 kg</div>
    <div style="font-style: italic;">
        <div class="square" style="height: 15px;
  width: 15px;display: inline-block;
  background-color: #E6625F;margin-right:8px"></div>User melakukan taksasi kurang dari 100 m & kurang dari 4 min
    </div>
    <div style="font-style: italic;">
        <div class="square" style="height: 15px;
  width: 15px;display: inline-block;
  background-color: #F28B44;margin-right:8px"></div>User melakukan taksasi kurang dari 4 min
    </div>
    <div style="font-style: italic;">
        <div class="square" style="height: 15px;
  width: 15px;display: inline-block;
  background-color: #F2E97D;margin-right:8px"></div>User melakukan taksasi kurang dari 100 m
    </div>

    <div class="page-break"></div>

    <h2 class="text-center">Maps User Taksasi</h2>
    <br>
    <div class="image-container">
        <img src="{{$image}}">
    </div>


</body>

</html>