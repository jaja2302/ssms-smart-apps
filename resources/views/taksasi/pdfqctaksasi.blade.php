<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" media="screen" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Document</title>
</head>

<style>
    table.table-bordered>thead>tr>th {
        border: 1px solid rgb(0, 0, 0);
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

<body>
    <table class="table table-bordered " style="font-size: 11px;">
        <thead>
            <tr>
                <th colspan="12" class="text-center" style="padding:10px;background: #D9E1F2;border: 2px solid black;font-size:16px">
                    LAPORAN TAKSASI PANEN
                </th>
            </tr>
            <tr>
                <th style=" color: white;border-left:1px solid white;border-bottom:1px solid black;border-right:1px solid white" colspan="3">|</th>
                <th style=" color: white;border-bottom:1px solid white;border-right:1px solid white" colspan="6">|</th>
                <th style=" color: white;border-left:1px solid white;border-bottom:1px solid black;border-right:1px solid white" colspan="3">|</th>
            </tr>
            <tr>
                <th colspan="3" style="font-size: 13px;
               padding:8px;text-align: left;background:#D9E1F2;">
                    ESTATE : {{$namaEstate}}
                </th>

                <th colspan="6" style="border-right: 1px solid black;border-bottom:1px solid white"></th>

                <th colspan="3" style="padding:8px;font-size: 13px;text-align: left;background:#D9E1F2;border:1px solid black">
                    TANGGAL TAKSASI : {{$today}}</th>
            </tr>
            <tr>
                <th colspan="3" style="font-size: 13px;
               padding:8px;text-align: left;background:#D9E1F2;border:1px solid black">
                    WILAYAH : {{$wil}}
                </th>

                <th colspan="6" style="border-right: 1px solid black;border-bottom: 1px solid white"></th>


                <th colspan="3" style="padding:8px;font-size: 13px;text-align: left;background:#D9E1F2;border:1px solid black">
                    TANGGAL PANEN : {{$besok}}</th>
            </tr>

        </thead>

    </table>

    <table class="table  " style="font-size: 11px;">
        <thead class="text-center">
            <tr style="background: #D9E1F2;">
                <th style="border:1px solid black;">AFDELING</th>
                <th style="border:1px solid black;">BLOK</th>
                <th style="border:1px solid black;width:10%;">SPH (Pkk/Ha)</th>
                <th style="border:1px solid black;width:10%;">BJR (Kg/Jjg)</th>
                <th style="border:1px solid black;width:10%;">SAMPEL PATH</th>
                <th style="border:1px solid black;width:10%;">LUAS (HA)</th>
                <th style="border:1px solid black;width:10%;">Baris Kiri</th>
                <th style="border:1px solid black;width:10%;">Baris Kanan</th>
                <th style="border:1px solid black;width:20%;">POKOK SAMPEL</th>
                <th style="border:1px solid black;width:10%;">JANJANG</th>


            </tr>
        </thead>

        <tbody style="font-size: 12px;font-weight: 400">
            @foreach($taksasi as $key => $value)
            @foreach($value as $key1 =>$value1)


            @if(is_array($value1))
            <tr style="background: #E2EFDA;font-weight:bold">
                <td style="border:1px solid black;text-align:center;padding:7px">{{$key}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['blok']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['sph']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['bjr']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['jumlah_path']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['luas']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['kiri']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['kanan']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['jumlah_pokok']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value1['total_kiri'] + $value1['total_kanan'] }}</td>
            </tr>
            @endif
            @endforeach

            <tr style="background: #bea925;font-weight: bold">
                <td style="border:1px solid black;text-align:center;padding:7px" colspan="2">Afdeling</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{intval($value['sph'])}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value['bjr']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value['jumlah_path']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value['luas_ha']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">-</td>
                <td style="border:1px solid black;text-align:center;padding:7px">-</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value['jumlah_pokok']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$value['jumlah_janjang']}}</td>


            </tr>
            @endforeach


            <tr style="background: #D9E1F2;font-weight: bold">
                <td style="border:1px solid black;text-align:center;padding:7px" colspan="2">ESTATE</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{intval($rekap['sph'])}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$rekap['bjr']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$rekap['total_path']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$rekap['luas']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">-</td>
                <td style="border:1px solid black;text-align:center;padding:7px">-</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$rekap['total_pokok']}}</td>
                <td style="border:1px solid black;text-align:center;padding:7px">{{$rekap['total_janjang']}}</td>



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
    <img src="https://mobilepro.srs-ssms.com/storage/app/public/taksasi/{{$est}}_{{$tgl}}.png" style="width:1040px;height:640px;">
</body>

</html>