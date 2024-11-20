@include('layout.header')
<style>
    @media only screen and (min-width: 992px) {
        .piechart_div {
            height: 590px;
        }

    }

    @media only screen and (min-width: 1366px) {

        .piechart_div {
            height: 800px;
        }
    }

    /* Ensure that the demo table scrolls */
    th,
    td {
        white-space: nowrap;
    }

    div.dataTables_wrapper {
        width: 100%;
        margin: 0 auto;
    }

    #map {
        height: 600px;
    }

    th {
        border-top: 1px solid #dddddd;
        border-bottom: 1px solid #dddddd;
        border-right: 1px solid #dddddd;
    }

    th:first-child {
        border-left: 1px solid #dddddd;
    }

    .legend {
        padding: 6px 8px;
        font: 14px Arial, Helvetica, sans-serif;
        background: white;
        /* background: rgba(255, 255, 255, 0.8); */
        /*box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);*/
        /*border-radius: 5px;*/
        line-height: 24px;
        color: #555;
    }

    .legend h4 {
        text-align: center;
        font-size: 16px;
        margin: 2px 12px 8px;
        color: #777;
    }

    .legend span {
        position: relative;
        bottom: 3px;
    }

    .legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin: 0 8px 0 0;
        opacity: 0.7;
    }

    .legend i.icon {
        background-size: 18px;
        background-color: rgba(255, 255, 255, 1);
    }

    .myCSSClass {
        /* background: green; */
        font-size: 25pt;
        border: 2px solid cyan
    }

    .man-marker {
        /* color: white; */
        filter: invert(35%) sepia(63%) saturate(5614%) hue-rotate(2deg) brightness(102%) contrast(107%);
    }

    .leaflet-tooltip-left.myCSSClass::before {
        border-left-color: cyan;
    }

    .leaflet-tooltip-right.myCSSClass::before {
        border-right-color: cyan;
    }

    .label-bidang {
        font-size: 10pt;
        color: white;
        text-align: center;
        opacity: 0.6;
    }

    .label-estate {
        font-size: 20pt;
        color: white;
        text-align: center;
    }

    .selectCard:hover {
        transform: scale(1.01);
        box-shadow: 0 10px 20px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
    }

    .selectCard {
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 6px 10px rgba(0, 0, 0, .08), 0 0 6px rgba(0, 0, 0, .05);
        transition: .3s transform cubic-bezier(.155, 1.105, .295, 1.12), .3s box-shadow, .3s -webkit-transform cubic-bezier(.155, 1.105, .295, 1.12);
        cursor: pointer;
    }

    a,
    a:hover,
    a:focus,
    a:active {
        text-decoration: none;
        color: inherit;
    }
</style>
<div class="content-wrapper">

    <section class="content-header">
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="container-fluid pl-3 pr-3">
                <div class="row">
                    <div class="col-12 col-lg mb-1 dashboard_div">
                        <h2 style="color:#013C5E;font-weight: 550">Dashboard Taksasi
                        </h2>
                        <p style="color:#6C7C8B">Dashboard ini digunakan untuk mengamati hasil taksasi yang
                            dilakukan
                            disetiap estate secara otomatis update setiap hari.
                        </p>

                    </div>
                </div>

                <div class="row">
                    <div class="col-2">
                        <form class="" action="{{ route('dashboard') }}" method="get">
                            <input class="form-control" type="date" name="tgl" id="tgl">
                        </form>
                    </div>

                    <div class="col-2">
                        {{csrf_field()}}
                        <select id="reg" class="form-control">
                            <option selected disabled>Pilih Regional</option>
                            @foreach($reg as $key => $value)
                            <option value="{{$key}}" {{ $key==0 ? 'selected' : '' }}>{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2">
                        {{csrf_field()}}
                        <select id="wilDropdown" class="form-control">
                            <option selected disabled>Pilih Wilayah</option>
                            @foreach($reg as $key => $value)
                            <option value="{{$key}}" {{ $key==0 ? 'selected' : '' }}>{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2">
                        <select id="est" class="form-control">
                            <option selected disabled>Pilih Estate</option>
                        </select>
                    </div>
                </div>


                <ul class="nav nav-tabs mt-3">
                    <li class="nav-item active"><a data-toggle="tab" href="#regionalTab"
                            class="nav-link tabDashboard">Regional </a>
                    </li>
                    <li class="nav-item"><a href="#wilayahTab" class="nav-link tabDashboard">Wilayah</a>
                    </li>
                    <li class="nav-item"><a href="#estateTab" class="nav-link tabDashboard">Estate</a>
                    </li>
                    <li class="nav-item"><a data-toggle="tab" href="#afdelingTab"
                            class="nav-link tabDashboard">Afdeling</a>
                    </li>
                    <li class="nav-item active"><a href="#realisasiTab" class="nav-link tabDashboard">Realisasi
                            Taksasi</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div id="regionalTab" class="tab-pane fade in">
                        <div class="row">
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Rekap Taksasi Regional
                                    </h4>
                                    <table id="table-regional" class="display hover" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Regional</th>
                                                <th>Luas (Ha)</th>
                                                <th>Jumlah Blok</th>
                                                <th>Ritase</th>
                                                <th>AKP (%)</th>
                                                <th>Taksasi (Kg)</th>
                                                <th>Kebutuhan Pemanen</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Grafik Tonase dan AKP Regional
                                    </h4>
                                    <div id="chartTonaseAKPReg"></div>
                                </div>
                            </div>
                        </div>



                    </div>
                    <div id="wilayahTab" class="tab-pane fade in">
                        <div class="row">
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Rekap Taksasi Wilayah
                                    </h4>
                                    <table id="table-wilayah" class="display" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Wilayah</th>
                                                <th>Luas (Ha)</th>
                                                <th>Jumlah Blok</th>
                                                <th>Ritase</th>
                                                <th>AKP (%)</th>
                                                <th>Taksasi (Kg)</th>
                                                <th>Kebutuhan Pemanen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Grafik Tonase dan AKP Wilayah
                                    </h4>
                                    <div id="chartTonaseAKPWil"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="estateTab" class="tab-pane fade in">

                        <div class="row">
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Rekap Taksasi Estate
                                    </h4>

                                    <table id="table-estate" class="display" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Estate</th>
                                                <th>Nama Wilayah</th>
                                                <th>Luas (Ha)</th>
                                                <th>Jumlah Blok</th>
                                                <th>Ritase</th>
                                                <th>AKP (%)</th>
                                                <th>Taksasi (Kg)</th>
                                                <th>Kebutuhan Pemanen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Grafik Tonase dan AKP Estate
                                    </h4>
                                    <div id="chartTonaseAKPEst"></div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div id="afdelingTab" class="tab-pane fade in">
                        <div class="row">
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Rekap Taksasi Afdeling
                                    </h4>


                                    <div class="mt-3">
                                        <table id="table-afdeling" class="display" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Afdeling</th>
                                                    <th>Luas (Ha)</th>
                                                    <th>Jumlah Blok</th>
                                                    <th>Ritase</th>
                                                    <th>AKP (%)</th>
                                                    <th>Taksasi (Kg)</th>
                                                    <th>Kebutuhan Pemanen</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card mt-3 p-3">
                                    <h4 style="color:#013C5E;font-weight: 550">Chart Tonase dan AKP Estate Per Afdeling
                                    </h4>
                                    <div id="ChartGrafikTonaseAfdeling"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-3 p-3">

                            <h4 style="color:#013C5E;font-weight: 550">Tracking Plot User Taksasi
                            </h4>

                        </div>
                    </div>
                    <div id="realisasiTab" class="tab-pane fade in">
                        <form action="{{ route('import-realisasi-taksasi') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mt-3">
                                <div class="mt-3">
                                    @if(Session::has('success'))
                                    <div class="alert alert-success">
                                        {{ Session::get('success') }}
                                    </div>
                                    @endif

                                    @if(Session::has('errors'))
                                    <div class="alert alert-danger">
                                        {{ Session::get('errors') }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="card mt-2 p-3  col-12">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>Pilih Bulan Import Excel Realisasi</label>
                                                <input type="month" name="month" class="form-control"
                                                    id="monthImportRealisasi" required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group">
                                                <label>PILIH FILE</label>
                                                <input type="file" name="file" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <label>PILIH FILE</label>
                                            <br>
                                            <button type="submit" class="btn btn-success">Import Excel</button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="card mt-3 p-3 col-12">
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="table-realisasi">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row ml-3">
                                        <div class="col-3">
                                            <label>Pilih Perbandingan Chart Taksasi dan Realisasi</label>
                                            <select id="pilihanChartRealisasi" class="form-control">
                                                {{-- <option disabled>Pilih Chart Realisasi</option> --}}
                                                <option value="taksasi_tonase">Tonase Taksasi
                                                </option>
                                                <option value="akp_taksasi">AKP</option>
                                                <option value="ha_panen_taksasi">Ha Panen</option>
                                                <option value="bjr_taksasi">BJR</option>
                                                <option value="keb_hk_taksasi">Keb Pemanen</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col mt-3">
                                            <div id="chartRealisasiRegional">
                                            </div>
                                        </div>
                                        <div class="col mt-3">
                                            <div id="chartRealisasiWilayah">
                                            </div>
                                        </div>
                                        <div class="col mt-3">
                                            <div id="chartRealisasiEstate">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </form>
                    </div>
                </div>




            </div>
            <div id="map"></div>
        </div>
    </section>

</div>
@include('layout.footer')

{{-- <script src="{{ asset('lottie/93121-no-data-preview.json') }}" type="text/javascript"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.4/lottie.min.js"
    integrity="sha512-ilxj730331yM7NbrJAICVJcRmPFErDqQhXJcn+PLbkXdE031JJbcK87Wt4VbAK+YY6/67L+N8p7KdzGoaRjsTg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- jQuery -->

<script src=" https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator@1.6.0/dist/leaflet.polylineDecorator.min.js ">
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ asset('/public/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('/public/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('/public/plugins/chart.js/Chart.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('/public/js/adminlte.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('/public/js/demo.js') }}"></script>

<script src="{{ asset('/public/js/loader.js') }}"></script>

<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzh5V86q6kt8UKJ8YE3oDOW0OexAXmlz8">
</script>

<script>
    date = new Date().toISOString().slice(0, 10)
    var map = L.map('map').setView([-2.27462005615234, 111.61400604248], 13);

    // satelite
    const googleSat = L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
    ).addTo(map);



    var legendVar = ''

    function drawUserTaksasi(arrData) {
        var legendMaps = L.control({
            position: "bottomright"
        });

        const newUserTaksasi = Object.entries(arrData);

        legendMaps.onAdd = function(map) {

            var div = L.DomUtil.create("div", "legend");
            div.innerHTML += "<h4>Keterangan :</h4>";
            div.innerHTML += '<div >';

            var colorAfd = ''
            newUserTaksasi.forEach(element => {
                switch (element[0]) {
                    case 'OA':
                        colorAfd = '#ff1744'
                        break;
                    case 'OB':
                        colorAfd = '#d500f9'
                        break;
                    case 'OC':
                        colorAfd = '#ffa000'
                        break;
                    case 'OD':
                        colorAfd = '#00b0ff'
                        break;
                    case 'OE':
                        colorAfd = '#ff1744'
                        break;
                        case 'OF':
                        colorAfd = '#666666'
                        break;
                    case 'OG':
                        colorAfd = '#666666'
                        break;
                        case 'OH':
                        colorAfd = '#666666'
                        break;
                        case 'OI':
                        colorAfd = '#ba9355'
                        break;
                        case 'OJ':
                        colorAfd = '#ccff00'
                        break;
                        case 'OK':
                        colorAfd = '#8f9e8a'
                        break;
                        case 'OL':
                        colorAfd = '#14011c'
                        break;
                        case 'OM':
                        colorAfd = '#01b9c5'
                        break;
                    default:
                        // code block
                }
                div.innerHTML += '<i style="background: ' + colorAfd + '"></i><span style="font-weight:bold">' + element[0] + '</span>';
                div.innerHTML += '<span> (';
                if (element[1].length != 1) {
                    var inc = 1;
                    var size = element[1].length
                    element[1].forEach(userName => {
                        if (inc == size) {
                            div.innerHTML += '<span > ' + userName + ' </span>';
                        } else {
                            div.innerHTML += '<span > ' + userName + ', </span>';
                        }
                        inc++
                    });
                } else {
                    element[1].forEach(userName => {
                        div.innerHTML += '<span> ' + userName + '</span>';
                    });
                }

                div.innerHTML += '<span> )<br></span>';
            });

            // div.innerHTML += '<br>';
            // div.innerHTML += '<i style="background: #FFFFFF"></i><span>Ice</span><br>';
            // div.innerHTML += '      <img src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png" alt="" style="width:13px"><span>    Titik Start Taksasi</span><br>';
            // div.innerHTML += '      <img src="remove.png" alt="" style="width:15px"><span> Jalur Taksasi</span><br>';
            div.innerHTML += '</div>';



            return div;
        };

        legendMaps.addTo(map);


        legendVar = legendMaps
    }


    var titleEstate = new Array();

    function drawEstatePlot(est, plot) {
        var geoJsonEst = '{"type"'
        geoJsonEst += ":"
        geoJsonEst += '"FeatureCollection",'
        geoJsonEst += '"features"'
        geoJsonEst += ":"
        geoJsonEst += '['

        geoJsonEst += '{"type"'
        geoJsonEst += ":"
        geoJsonEst += '"Feature",'
        geoJsonEst += '"properties"'
        geoJsonEst += ":"
        geoJsonEst += '{"estate"'
        geoJsonEst += ":"
        geoJsonEst += '"' + est + '"},'
        geoJsonEst += '"geometry"'
        geoJsonEst += ":"
        geoJsonEst += '{"coordinates"'
        geoJsonEst += ":"
        geoJsonEst += '[['
        geoJsonEst += plot
        geoJsonEst += ']],"type"'
        geoJsonEst += ":"
        geoJsonEst += '"Polygon"'
        geoJsonEst += '}},'

        geoJsonEst = geoJsonEst.substring(0, geoJsonEst.length - 1);
        geoJsonEst += ']}'

        var estate = JSON.parse(geoJsonEst)

        var estateObj = L.geoJSON(estate, {
                onEachFeature: function(feature, layer) {
                    layer.myTag = 'EstateMarker'
                    var label = L.marker(layer.getBounds().getCenter(), {
                        icon: L.divIcon({
                            className: 'label-estate',
                            html: feature.properties.estate,
                            iconSize: [100, 20]
                        })
                    }).addTo(map);
                    titleEstate.push(label)
                    layer.addTo(map);
                },
                style: function(feature) {
                    switch (feature.properties.estate) {
                        case 'Sulung Estate':
                            return {
                                color: "#003B73",
                                    opacity: 1,
                                    fillOpacity: 0.2,

                            };
                        case 'Rangda Estate':
                            return {
                                color: "#003B73",
                                    opacity: 1,
                                    fillOpacity: 0.4,

                            };
                        case 'Kenambui Estate':
                            return {
                                color: "#003B73",
                                    opacity: 1,
                                    fillOpacity: 0.2,

                            };
                        case 'Pulau Estate':
                            return {
                                color: "#003B73",
                                    opacity: 1,
                                    fillOpacity: 0.4,

                            };
                            default:
                            return {    
                                color: "#003B73",
                                    opacity: 1,
                                    fillOpacity: 0.4,
                            };
                    }
                }
            })
            .addTo(map);

        map.fitBounds(estateObj.getBounds());
    }



    var titleBlok = new Array();

    function drawBlokPlot(blok) {
        var getPlotStr = '{"type"'
        getPlotStr += ":"
        getPlotStr += '"FeatureCollection",'
        getPlotStr += '"features"'
        getPlotStr += ":"
        getPlotStr += '['
        for (let i = 0; i < blok.length; i++) {
            getPlotStr += '{"type"'
            getPlotStr += ":"
            getPlotStr += '"Feature",'
            getPlotStr += '"properties"'
            getPlotStr += ":"
            getPlotStr += '{"blok"'
            getPlotStr += ":"
            getPlotStr += '"' + blok[i]['blok'] + '",'
            getPlotStr += '"estate"'
            getPlotStr += ":"
            getPlotStr += '"' + blok[i]['estate'] + '",'
            getPlotStr += '"afdeling"'
            getPlotStr += ":"
            getPlotStr += '"' + blok[i]['afdeling'] + '"'
            getPlotStr += '},'
            getPlotStr += '"geometry"'
            getPlotStr += ":"
            getPlotStr += '{"coordinates"'
            getPlotStr += ":"
            getPlotStr += '[['
            getPlotStr += blok[i]['latln']
            getPlotStr += ']],"type"'
            getPlotStr += ":"
            getPlotStr += '"Polygon"'
            getPlotStr += '}},'
        }
        getPlotStr = getPlotStr.substring(0, getPlotStr.length - 1);
        getPlotStr += ']}'


        var blok = JSON.parse(getPlotStr)

        
        L.geoJSON(blok, {
                onEachFeature: function(feature, layer) {

                    layer.myTag = 'BlokMarker'
                    var label = L.marker(layer.getBounds().getCenter(), {
                        icon: L.divIcon({
                            className: 'label-bidang',
                            html: feature.properties.blok,
                            iconSize: [50, 10]
                        })
                    }).addTo(map);

                    titleBlok.push(label)
                    layer.addTo(map);
                },
                style: function(feature) {
                    switch (feature.properties.afdeling) {
                        case 'OA':
                            return {
                                fillColor: "#ff1744",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,
                            };
                        case 'OB':
                            return {
                                fillColor: "#d500f9",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,
                            };
                        case 'OC':
                            return {
                                fillColor: "#ffa000",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,
                            };
                        case 'OD':
                            return {
                                fillColor: "#00b0ff",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,
                            };

                        case 'OE':
                            return {
                                fillColor: "#67D98A",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                        case 'OF':
                            return {
                                fillColor: "#666666",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                        case 'OG':
                            return {
                                fillColor: "#666666",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                            case 'OH':
                            return {
                                fillColor: "#666666",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                            case 'OI':
                            return {
                                fillColor: "#ba9355",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                            case 'OJ':
                            return {
                                fillColor: "#ccff00",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                            case 'OK':
                            return {
                                fillColor: "#8f9e8a",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                            case 'OL':
                            return {
                                fillColor: "#14011c",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                            case 'OM':
                            return {
                                fillColor: "#01b9c5",
                                    color: 'white',
                                    fillOpacity: 0.4,
                                    opacity: 0.4,

                            };
                    }
                }
            })
            ;
    }

    function drawLineTaksasi(line) {
    // Create a valid GeoJSON string by fixing the format of the coordinates
    var getLineStr = '{"type":"FeatureCollection","features":[';

    for (let i = 0; i < line.length; i++) {
        getLineStr += '{"type":"Feature","properties":{},"geometry":{"type":"LineString","coordinates":[';

        // Fix the coordinate format by replacing '],[' with '],[' in line[i]
        getLineStr += line[i].replace(/],\[/g, '],[');

        getLineStr += ']}},';
    }
    getLineStr = getLineStr.substring(0, getLineStr.length - 1); // Remove last comma
    getLineStr += ']}';

    // Parse the corrected GeoJSON string
    var lineGeoJSON = JSON.parse(getLineStr);

    // Add the GeoJSON layer to the map
    var geoLayer = L.geoJSON(lineGeoJSON, {
        onEachFeature: function (feature, layer) {
            layer.myTag = 'LineMarker';  // Add a tag to the line
            drawArrowLine(layer);  // Call drawArrowLine with the layer directly
            layer.addTo(map);
        },
        style: function (feature) {
            return {
                weight: 2,
                opacity: 1,
                color: 'yellow',
                fillOpacity: 0.7
            };
        }
    }).addTo(map);
}

// Updated function to add arrowheads without re-creating polylines
function drawArrowLine(layer) {
    // Apply arrowhead decorator to the existing polyline layer
    var decorator = L.polylineDecorator(layer, {
        patterns: [
            {
                offset:'10%',    // Start from the beginning of the line
                repeat: 50,      // Repeat arrow every 50 pixels
                symbol: L.Symbol.arrowHead({
                    pixelSize: 10,  // Arrowhead size
                    polygon: true,  // Full triangle arrowhead
                    pathOptions: {
                        stroke: true,
                        color: '#3be13b', // Arrow color
                        weight: 2,
                        fillOpacity: 1,  // Ensure the triangle is fully filled
                        fill: true  
                    }
                })
            }
        ]
    }).addTo(map);

    decorator.myTag = 'ArrowMarker';  // Add tag 'ArrowMarker' to the arrowhead
}


    var removeMarkers = function() {
        map.eachLayer(function(layer) {

            if (layer.myTag && layer.myTag === "EstateMarker") {
                map.removeLayer(layer)
            }
            if (layer.myTag && layer.myTag === "BlokMarker") {
                map.removeLayer(layer)
            }
            if (layer.myTag && layer.myTag === "LineMarker") {
                map.removeLayer(layer)
            }
            if (layer.myTag && layer.myTag === "ArrowMarker") {
                map.removeLayer(layer)
            }
        });
    }


    var marker = ''
    var layerMarkerMan = new Array();

    function drawMarkerMan(arrData) {

        for (let i = 0; i < arrData.length; i++) {

            switch (arrData[i]['afdeling']) {
                case 'OA':
                    marker = 'manMarkerOA'
                    colorMarker = 'red'
                    break;
                case 'OB':
                    marker = 'manMarkerOB'
                    colorMarker = 'violet'
                    break;
                case 'OC':
                    marker = 'manMarkerOC'
                    colorMarker = 'gold'
                    break;
                case 'OD':
                    marker = 'manMarkerOD'
                    colorMarker = 'blue'
                    break;
                case 'OE':
                    marker = 'manMarkerOE'
                    break;
                case 'OF':
                    marker = 'manMarkerOF'
                    break;
                    case 'OG':
                    marker = 'manMarkerOF'
                    colorMarker = 'grey'
                    break;
                    case 'OH':
                    marker = 'manMarkerOF'
                    colorMarker = 'gold'
                    break;
                    case 'OI':
                    marker = 'manMarkerOF'
                    colorMarker = 'violet'
                    break;
                    case 'OJ':
                    marker = 'manMarkerOF'
                    colorMarker = 'grey'
                    break;
                    case 'OK':
                    marker = 'manMarkerOF'
                    colorMarker = 'red'
                    break;
                    case 'OL':
                    marker = 'manMarkerOF'
                    colorMarker = 'blue'
                    break;
                    case 'OM':
                    marker = 'manMarkerOF'
                    colorMarker = 'grey'
                    break;
                default:
                    // code block
            }

            let start = new L.Icon({
                iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-" + colorMarker + ".png",
                shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
                iconSize: [14, 21],
                iconAnchor: [7, 22],
                popupAnchor: [1, -34],
                shadowSize: [28, 20],
            });

            var latlonFinish = JSON.parse(arrData[i]['plotAwal'])
            marker = L.marker(latlonFinish, {
                icon: start
            }).addTo(map);

            layerMarkerMan.push(marker)
        }


    }

    function markerDelAgain() {
        for (i = 0; i < titleBlok.length; i++) {
            map.removeLayer(titleBlok[i]);
        }
        for (i = 0; i < titleEstate.length; i++) {
            map.removeLayer(titleEstate[i]);
        }
        for (let i = 0; i < layerMarkerMan.length; i++) {
            map.removeLayer(layerMarkerMan[i]);
        }

        map.removeControl(legendVar)
        legendVar = null;
    }

    






  

    function getPlotEstate(est, date) {

        var _token = $('input[name="_token"]').val();

        const params = new URLSearchParams(window.location.search)
        var paramArr = [];
        for (const param of params) {
            paramArr = param
        }

        $.ajax({
            url: "{{ route('plotEstate') }}",
            method: "POST",
            data: {
                est: est,
                _token: _token,
                tgl: date
            },
            success: function(result) {
                var estate = JSON.parse(result);

                drawEstatePlot(estate['est'], estate['plot'])
            }
        })
    }

    function getPlotBlok(est, date) {

        var _token = $('input[name="_token"]').val();

        const params = new URLSearchParams(window.location.search)
        var paramArr = [];
        for (const param of params) {
            paramArr = param
        }

        $.ajax({
            url: "{{ route('plotBlok') }}",
            method: "POST",
            data: {
                est: est,
                _token: _token,
                tgl: date
            },
            success: function(result) {
                
                var blok = JSON.parse(result);

                
                drawBlokPlot(blok)
            }
        })
    }


    function getlineTaksasi(est, date) {
        var _token = $('input[name="_token"]').val();

        const params = new URLSearchParams(window.location.search)
        var paramArr = [];
        for (const param of params) {
            paramArr = param
        }

        $.ajax({
            url: "{{ route('plotLineTaksasi') }}",
            method: "POST",
            data: {
                est: est,
                _token: _token,
                tgl: date
            },
            success: function(result) {
                var line = JSON.parse(result);
                drawLineTaksasi(line)
            }
        })
    }

    function getMarkerMan(est, date) {

        var _token = $('input[name="_token"]').val();

        const params = new URLSearchParams(window.location.search)
        var paramArr = [];
        for (const param of params) {
            paramArr = param
        }

        $.ajax({
            url: "{{ route('plotMarkerMan') }}",
            method: "POST",
            data: {
                est: est,
                _token: _token,
                tgl: date
            },
            success: function(result) {
                var marker = JSON.parse(result);
                drawMarkerMan(marker)

            }
        })
    }

    function getUserTaksasi(est, date) {

        var _token = $('input[name="_token"]').val();

        const params = new URLSearchParams(window.location.search)
        var paramArr = [];
        for (const param of params) {
            paramArr = param
        }

        $.ajax({
            url: "{{ route('plotUserTaksasi') }}",
            method: "POST",
            data: {
                est: est,
                _token: _token,
                tgl: date
            },
            success: function(result) {
                var marker = JSON.parse(result);

                drawUserTaksasi(marker)

            }
        })
    }

    var options = {
            series: [
                {
                    name: 'Taksasi (Kg): ',
                    data: [1]
                },
                {
                    name: 'AKP (%): ',
                    data: [2]
                }
            ],
            chart: {
                type: 'area', // Changed to area chart type
                height: 350
            },
            colors: ['#f5b041', '#1f4d89'],
            plotOptions: {
                area: {  // Use area-specific plot options
                    markers: {
                        size: 5 // Adjust marker size if needed
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth' // Use smooth curve for area chart
            },
            xaxis: {
                categories: ['Wilayah 1'],
            },
            yaxis: [
                {
                    title: {
                        text: 'Taksasi (Kg): '
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'AKP (%): '
                    }
                }
            ],
            fill: {
                opacity: 0.5 // Adjust fill opacity if needed
            },
        
        };

        var options2 = {
            series: [
                {
                    name: 'Taksasi (Kg): ',
                    data: [1]
                },
                {
                    name: 'AKP (%): ',
                    data: [2]
                }
            ],
            chart: {
                type: 'bar', // Changed to column chart type
                height: 350
            },
            colors: ['#f5b041', '#1f4d89'],
            legend:{
                position  : 'top',
            },
            plotOptions: {
                bar: {  // Use bar-specific plot options
                    columnWidth: '50%', // Adjust column width if needed
                    endingShape: 'rounded' // Optional: Adds rounded corners to columns
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['Wilayah 1'],
            },
            yaxis: [
                {
                    title: {
                        text: 'Taksasi '
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Realisasi'
                    }
                }
            ],
            fill: {
                opacity: 1 // Full opacity for column chart
            },
        
        };

    $(document).ready(function(){
        var finalDataReg = []
        var finalDataWil = []
        var finalDataEst = []
        $('#reg').hide();
        $('#wilDropdown').hide();
        $('#est').hide();
        $('#map').hide();
        $('a[href="#regionalTab"]').click();

             $('.tabDashboard').click(function(event) {
                event.preventDefault(); // Prevent default tab behavior
                var targetTab = $(this).attr('href'); // Get the target tab
                var url = window.location.origin + window.location.pathname + '?tab=' + targetTab.substring(1); // Construct the URL
                window.open(url, '_blank'); // Open the new tab
            });

            // Check if a tab parameter is present in the URL
            var urlParams = new URLSearchParams(window.location.search);
            var tab = urlParams.get('tab');
            if (tab) {
                $('.tabDashboard[href="#' + tab + '"]').tab('show'); // Show the tab based on the URL parameter
                if (tab === 'regionalTab') {
                    $('#reg').hide();
                    $('#wilDropdown').hide();
                    $('#est').hide();
                }
                else if(tab ==='wilayahTab'){
                    $('#reg').show();
                    $('#wilDropdown').hide();
                    $('#est').hide();
                }
                else if(tab ==='realisasiTab'){
                    $('#wilDropdown').hide();
                    $('#est').hide();
                    $('#reg').show();
                }
                else if(tab ==='afdelingTab'){
                    $('#reg').show();
                    $('#wilDropdown').show();
                    $('#est').show();
                    $('#map').show();
                }
                else {
                    $('#reg').show();
                    // $('#wilDropdown').show();
                    // $('#est').show();
                }
            }


        var currentDate = new Date();

        // Format the date to YYYY-MM for input month value
        var yearMonth = currentDate.toISOString().slice(0, 7);

        // Set the default value for the input month
        // $('#monthRealisasi').val(yearMonth);
        $('#monthImportRealisasi').val(yearMonth);
        monthImportRealisasi

      
        // Initialize the chart with the options
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
        var chartTonaseAKPReg = new ApexCharts(document.querySelector("#chartTonaseAKPReg"), options);
        chartTonaseAKPReg.render();
        var chartTonaseAKPWil = new ApexCharts(document.querySelector("#chartTonaseAKPWil"), options);
        chartTonaseAKPWil.render();
        var chartTonaseAKPEst = new ApexCharts(document.querySelector("#chartTonaseAKPEst"), options);
        chartTonaseAKPEst.render();
        var ChartGrafikTonaseAfdeling = new ApexCharts(document.querySelector("#ChartGrafikTonaseAfdeling"), options);
        ChartGrafikTonaseAfdeling.render();
        var chartRealisasiRegional = new ApexCharts(document.querySelector("#chartRealisasiRegional"), options2);
        chartRealisasiRegional.render();
        var chartRealisasiWilayah = new ApexCharts(document.querySelector("#chartRealisasiWilayah"), options2);
        chartRealisasiWilayah.render();
        var chartRealisasiEstate = new ApexCharts(document.querySelector("#chartRealisasiEstate"), options2);
        chartRealisasiEstate.render();
        // Set default date to today
        var dateToday = new Date().toISOString().slice(0,10);
        $('#tgl').val(dateToday);

        function loadDataTableRealisasi(dateToday, regionalId) {
            var _token = $('input[name="_token"]').val();
            $.ajax({
                url: "{{ route('get-data-realisasi-taksasi-per-regional') }}",
                method: "GET",
                cache: false,
                data: {
                    _token: _token,
                    date_request: dateToday,
                    id_reg: regionalId,
                    // est_req : estateRequest,
                },
                success: function(result) {
                    var parseResult = JSON.parse(result);  
                    finalDataEst = parseResult['dataEst']
                    finalDataWil = parseResult['dataWil']
                    finalDataReg = parseResult['dataReg']

                     function destroyDataTable(tableId) {
                        if ($.fn.DataTable.isDataTable(`#table-test-${tableId}`)) {
                            $(`#table-test-${tableId}`).DataTable().clear().destroy();
                        }
                    }
    
            function createTable(tableId, data) {
                    destroyDataTable(tableId);
                    var tableHtml = `
                    <h4 class="pl-4 pt-4" style="color:#013C5E;font-weight: 550">Realisasi Vs Taksasi Vs Varian: ${tableId}</h4>
                    <div class="table-container p-4">
                    <table id="table-test-${tableId}" class="stripe hover compact cell-border mt-1" style="width: 100%">
                        <thead >
                            <tr>
                                <th colspan="23"> HI </th>
                                <th colspan="14"> SHI </th>
                                </tr>
                                <tr>
                                            <th rowspan="2">AFD</th>
                                            <th colspan="3">Ha Panen</th>
                                            <th colspan="3">AKP (%)</th>
                                            <th colspan="3">Tonase (Kg)</th>
                                            <th colspan="3">HK</th>
                                            <th colspan="3">Janjang</th>
                                            <th colspan="2">Total Tonase</th>
                                            <th colspan="2">Restan</th>
                                            <th colspan="3">BJR</th>
                                            <th colspan="2">Ha Panen</th>
                                            <th colspan="2">AKP</th>
                                            <th colspan="2">Tonase</th>
                                            <th colspan="2">HK</th>
                                            <th colspan="2">Janjang</th>
                                            <th colspan="2">Total Tonase</th>
                                            <th colspan="2">BJR</th>
                                        </tr>
                                        <tr>
                                            <th>panen  Wilayah</th>
                                            <th>panen Aplikasi</th>
                                            <th>panen Selisih</th>
                                            <th>akp Wilayah</th>
                                            <th>akp Aplikasi</th>
                                            <th>akp </th>
                                            <th>taksasi Wilayah</th>
                                            <th>taksasiAplikasi</th>
                                            <th>taksasi</th>
                                            <th>hk Wilayah</th>
                                            <th>hk Aplikasi</th>
                                            <th>hk Selisih</th>
                                            <th>janjang Wilayah</th>
                                            <th>janjang Aplikasi</th>
                                            <th>janjang Selisih</th>
                                            <th>total tonase Wilayah</th>
                                            <th>total tonase Aplikasi</th>
                                            <th>Restan Kemarin</th>
                                            <th>Restan HI</th>
                                            <th>bjr Wilayah</th>
                                            <th>bjr Aplikasi</th>
                                            <th>bjr Selisih</th>
                                            <th>Ha Panen SHI</th>
                                            <th>Ha Panen SHI</th>
                                            <th>AKP SHI</th>
                                            <th>AKP SHI</th>
                                            <th>Tonase SHI</th>
                                            <th>Tonase SHI</th>
                                            <th>HK SHI</th>
                                            <th>HK SHI</th>
                                            <th>Janjang SHI</th>
                                            <th>Janjang SHI</th>
                                            <th>Total Tonase SHI</th>
                                            <th>Total Tonase SHI</th>
                                            <th>BJR SHI</th>
                                            <th>BJR SHI</th>
                                        </tr>
                        </thead>
                        <tbody>
                        </tbody>

                                </table>
                            </div>
                        `;
            $('#table-realisasi').append(tableHtml);

            $(`#table-test-${tableId}`).DataTable({
                data: data,
                
                fixedColumns: {
                start: 1
                },    
                scrollX: true,

                columns: [
                    { title: "AFD" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Varian" },
                    { title: " Taksasi" },
                    { title: " Realisasi" },
                    { title: " Varian" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Varian" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: " Varian" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: " Varian" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: " Varian" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                    { title: "Taksasi" },
                    { title: "Realisasi" },
                ],
            // "createdRow": function(row, data, dataIndex) {
            //                 $('td', row).eq(0).css({
            //                 'background-color': '#fbd4b4',  // Change this to the desired background color
            //                 'color': 'black'              // Change this to the desired text color
            //             });
            //             },
                    headerCallback: function(thead, data, start, end, display) {
                        $(thead).find('th').css('text-align', 'center');
                    }
                });
            }

            $('#table-realisasi').empty();
            // Loop to create tables for each estate
             // Data for Regional
            var finalDataRegFormatted = finalDataReg.map(data => [
                data.key,
                data.ha_panen_taksasi,
                data.ha_panen_realisasi,
                data.ha_panen_varian,
                data.akp_taksasi,
                data.akp_realisasi,
                data.akp_varian,
                data.taksasi_tonase,
                data.taksasi_realisasi,
                data.taksasi_varian,
                data.keb_hk_taksasi,
                data.keb_hk_realisasi,
                data.keb_hk_varian,
                data.janjang_taksasi,
                data.janjang_realisasi,
                data.janjang_varian,
                data.total_tonase_taksasi,
                data.total_tonase_realisasi,
                data.restan_kemarin,
                data.restan_hi,
                data.bjr_taksasi,
                data.bjr_realisasi,
                data.bjr_varian,
                data.ha_panen_taksasi_shi,
                data.ha_panen_realisasi_shi,
                data.akp_taksasi_shi,
                data.akp_realisasi_shi,
                data.tonase_taksasi_shi,
                data.tonase_realisasi_shi,
                data.keb_hk_taksasi_shi,
                data.keb_hk_realisasi_shi,
                data.janjang_taksasi_shi,
                data.janjang_realisasi_shi,
                data.total_tonase_taksasi,
                data.total_tonase_realisasi_shi,
                data.bjr_taksasi_shi,
                data.bjr_realisasi_shi,
              
            ]);

            // Create table for the Regional data
            createTable('Regional', finalDataRegFormatted);

            var mappedData = finalDataWil.map(data => [
                data.key,
                data.ha_panen_taksasi,
                data.ha_panen_realisasi,
                data.ha_panen_varian,
                data.akp_taksasi,
                data.akp_realisasi,
                data.akp_varian,
                data.taksasi_tonase,
                data.taksasi_realisasi,
                data.taksasi_varian,
                data.keb_hk_taksasi,
                data.keb_hk_realisasi,
                data.keb_hk_varian,
                data.janjang_taksasi,
                data.janjang_realisasi,
                data.janjang_varian,
                data.total_tonase_taksasi,
                data.total_tonase_realisasi,
                data.restan_kemarin,
                data.restan_hi,
                data.bjr_taksasi,
                data.bjr_realisasi,
                data.bjr_varian,
                data.ha_panen_taksasi_shi,
                data.ha_panen_realisasi_shi,
                data.akp_taksasi_shi,
                data.akp_realisasi_shi,
                data.tonase_taksasi_shi,
                data.tonase_realisasi_shi,
                data.keb_hk_taksasi_shi,
                data.keb_hk_realisasi_shi,
                data.janjang_taksasi_shi,
                data.janjang_realisasi_shi,
                data.tonase_taksasi_shi,
                data.total_tonase_realisasi_shi,
                data.bjr_taksasi_shi,
                data.bjr_realisasi_shi,
            ]);

             createTable("Wilayah", mappedData);

            var mappedData = finalDataEst.map(data => [
                data.key,
                    data.ha_panen_taksasi,
                    data.ha_panen_realisasi,
                    data.ha_panen_varian,
                    data.akp_taksasi,
                    data.akp_realisasi,
                    data.akp_varian,
                    data.taksasi_tonase,
                    data.taksasi_realisasi,
                    data.taksasi_varian,
                    data.keb_hk_taksasi,
                    data.keb_hk_realisasi,
                    data.keb_hk_varian,
                    data.janjang_taksasi,
                    data.janjang_realisasi,
                    data.janjang_varian,
                    data.total_tonase_taksasi,
                    data.total_tonase_realisasi,
                    data.restan_kemarin,
                    data.restan_hi,
                    data.bjr_taksasi,
                    data.bjr_realisasi,
                    data.bjr_varian,
                    data.ha_panen_taksasi_shi,
                    data.ha_panen_realisasi_shi,
                    data.akp_taksasi_shi,
                    data.akp_realisasi_shi,
                    data.tonase_taksasi_shi,
                    data.tonase_realisasi_shi,
                    data.keb_hk_taksasi_shi,
                    data.keb_hk_realisasi_shi,
                    data.janjang_taksasi_shi,
                    data.janjang_realisasi_shi,
                    data.tonase_taksasi_shi,
                    data.total_tonase_realisasi_shi,
                    data.bjr_taksasi_shi,
                    data.bjr_realisasi_shi,
            ]);

            createTable("Estate", mappedData);

                    updateChartSeriesRealisasi()
                }
            });
        }

        loadDataTableRegionalWilayah(dateToday, $('#reg').val());
        loadListWilayahDropdown($('#reg').val())

        var pilihanChartRealisasi = $('#pilihanChartRealisasi').val();
        loadDataTableRealisasi(dateToday,$('#reg').val())
        // Event listener for date input change
        $('#tgl').on('change', function() {
            var selectedDate = $(this).val();
            var regionalId =  $('#reg').val()
            loadDataTableRegionalWilayah(selectedDate, regionalId);
            loadDataTableEstate( $('#est').val(), selectedDate)
            loadDataTableRealisasi(selectedDate,regionalId)
            removeMarkers();
            markerDelAgain()
            getPlotEstate($('#est').val(), selectedDate)
            getPlotBlok($('#est').val(), selectedDate)
            getlineTaksasi($('#est').val(), selectedDate)
            getMarkerMan($('#est').val(), selectedDate)
            getUserTaksasi($('#est').val(), selectedDate)
           
        });

        $('#monthRealisasi').on('change', function() {
            var selectedMonth = $(this).val();
            loadDataTableRealisasi(selectedMonth,$('#reg').val())
        });

        if ($('#reg option:selected').length === 0) {
            $('#reg option:first').prop('selected', true);
        }

        function loadEstateDropdown(regionalId, wilId) {
            var _token = $('input[name="_token"]').val();

            $.ajax({
                url: "{{ route('getNameEstate') }}",
                method: "POST",
                data: {
                    _token: _token,
                    id_reg: regionalId,
                    id_wil : wilId, 
                },
                success: function(result) {

                    $('#est').empty().append(result);
                    $('#est option:first').prop('selected', true);
                    // $('#estRealisasi').empty().append(result);
                    // $('#estRealisasi option:first').prop('selected', true);
                    var selectedEstateId = $('#est').val();
                    var selectedDate = $('#tgl').val();
                    loadDataTableEstate(selectedEstateId, selectedDate);
                    // loadDataTableRealisasi($('#monthRealisasi').val(),$('#reg').val(), $('#estRealisasi').val())
                    
                    
                getPlotEstate(selectedEstateId, selectedDate)   
                getPlotBlok(selectedEstateId, selectedDate)
                getlineTaksasi(selectedEstateId, selectedDate)
                getMarkerMan(selectedEstateId, selectedDate)
                getUserTaksasi(selectedEstateId, selectedDate)
            
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred while fetching estates: ", error);
                }
            });
        }

        function loadDataTableRegionalWilayah(date, regionalId) {
            var _token = $('input[name="_token"]').val();
            $.ajax({
                url: "{{ route('get-data-regional-wilayah') }}",
                method: "GET",
                cache: false,
                data: {
                    _token: _token,
                    tgl_request: date,
                    id_reg: regionalId,
                },
                success: function(result) {
                    var parseResult = JSON.parse(result);      
                    var dataReg = [];
                    var dataWil = [];
                    var dataEst = [];

                    $.each(parseResult['data_reg'], function(regional, values) {
                        dataReg.push({
                            "regional": regional,
                            "luas": values.luas,
                            "jumlahBlok": values.jumlahBlok,
                            "akp": values.akp,
                            "taksasi": values.taksasi,
                            "ritase": values.ritase,
                            "keb_pemanen": values.keb_pemanen
                        });
                    });

                    $.each(parseResult['data_wil'], function(wilayah, values) {
                        dataWil.push({
                            "wilayah": wilayah,
                            "luas": values.luas,
                            "jumlahBlok": values.jumlahBlok,
                            "akp": values.akp,
                            "taksasi": values.taksasi,
                            "ritase": values.ritase,
                            "keb_pemanen": values.keb_pemanen
                        });
                    });

                    $.each(parseResult['data_est'], function(estate, values) {
                        dataEst.push({
                            "estate": estate,
                            "nama_wil": values.nama_wil,
                            "luas": values.luas,
                            "jumlahBlok": values.jumlahBlok,
                            "akp": values.akp,
                            "taksasi": values.taksasi,
                            "ritase": values.ritase,
                            "keb_pemanen": values.keb_pemanen
                        });
                    });


                  


                    // Initialize or reload DataTable
                    if ($.fn.dataTable.isDataTable('#table-regional')) {
                        // If DataTable already exists, destroy it and create a new one
                        $('#table-regional').DataTable().clear().destroy();
                    }

                 

                    $('#table-regional').DataTable({
                        "processing": true,
                        "serverSide": false,
                        scrollX: true,
                        "data": dataReg,
                        "columns": [
                            { "data": "regional", "title": "REGIONAL" },
                            { "data": "luas", "title": "LUAS (Ha)" },
                            { "data": "jumlahBlok", "title": "JUMLAH BLOK" },
                            { "data": "akp", "title": "AKP (%)" },
                            { "data": "taksasi", "title": "TAKSASI (Kg)" },
                            { "data": "ritase", "title": "RITASE" },
                            { "data": "keb_pemanen", "title": "KEB. PEMANEN" }
                        ],
                        // "createdRow": function(row, data, dataIndex) {
                        //     $('td', row).eq(0).css({
                        //     'background-color': '#fbd4b4',  // Change this to the desired background color
                        //     'color': 'black'              // Change this to the desired text color
                        // });
                        // }
                    });

                    if ($.fn.dataTable.isDataTable('#table-wilayah')) {
                        // If DataTable already exists, destroy it and create a new one
                        $('#table-wilayah').DataTable().clear().destroy();
                    }
                    $('#table-wilayah').DataTable({
                        "processing": true,
                        "serverSide": false,
                        scrollX: true,
                        "data": dataWil,
                        "pageLength": 25,
                        "columns": [
                            { "data": "wilayah", "title": "WILAYAH" },
                            { "data": "luas", "title": "LUAS (Ha)" },
                            { "data": "jumlahBlok", "title": "JUMLAH BLOK" },
                            { "data": "akp", "title": "AKP (%)" },
                            { "data": "taksasi", "title": "TAKSASI (Kg)" },
                            { "data": "ritase", "title": "RITASE" },
                            { "data": "keb_pemanen", "title": "KEB. PEMANEN" }
                        ],
                        // "createdRow": function(row, data, dataIndex) {
                        //     $('td', row).eq(0).css({
                        //     'background-color': '#fbd4b4',  // Change this to the desired background color
                        //     'color': 'black'              // Change this to the desired text color
                        // });
                        // }
                    });


                    if ($.fn.dataTable.isDataTable('#table-estate')) {
                        // If DataTable already exists, destroy it and create a new one
                        $('#table-estate').DataTable().clear().destroy();
                    }

                    // var wilayah1Exists = dataEst.some(function(item) {
                    //     return item.nama_wil === 'Wilayah 1';
                    // });

                    // // Define a custom sorting plugin
                    // if (wilayah1Exists) {
                    //     $.fn.dataTable.ext.order['custom-name-wilayah'] = function(settings, col) {
                    //         return this.api().column(col, { order: 'index' }).nodes().map(function(td, i) {
                    //             return $(td).text() === 'Wilayah 1' ? '0' : '1';
                    //         });
                    //     };
                    // }
                    $('#table-estate').DataTable({
                        "processing": true,
                        "serverSide": false,
                        scrollX: true,
                        "data": dataEst,
                        "pageLength": 10,
                        "columns": [
                            { "data": "estate", "title": "ESTATE" },
                            { "data": "nama_wil", "title": "NAMA WILAYAH" },
                            { "data": "luas", "title": "LUAS (Ha)" },
                            { "data": "jumlahBlok", "title": "JUMLAH BLOK" },
                            { "data": "akp", "title": "AKP (%)" },
                            { "data": "taksasi", "title": "TAKSASI (Kg)" },
                            { "data": "ritase", "title": "RITASE" },
                            { "data": "keb_pemanen", "title": "KEB. PEMANEN" }
                        ],
                        // "order": [[1, 'asc']], // Default sort by the second column (nama_wil)
                        // "columnDefs": wilayah1Exists ? [
                        //     {
                        //         "targets": 1, // Apply to the second column
                        //         "orderDataType": "custom-name-wilayah"
                        //     }
                        // ] : []
                        // "createdRow": function(row, data, dataIndex) {
                        //     $('td', row).eq(0).css({
                        //     'background-color': '#fbd4b4',  // Change this to the desired background color
                        //     'color': 'black'              // Change this to the desired text color
                        // });
                        // }
                    });

                    var taksasiDataReg = [];
                    var akpDataReg = [];
                    var chartCategoriesReg = [];
                    var taksasiDataWil = [];
                    var akpDataWil = [];
                    var chartCategoriesWil = [];
                    var taksasiDataEst = [];
                    var akpDataEst = [];
                    var chartCategoriesEst = [];


                    $.each(parseResult['data_reg'], function(regional, values) {
                        chartCategoriesReg.push(regional);
                        taksasiDataReg.push(values.taksasi === '-' ? 0 : removeDots(values.taksasi));  // Convert '-' to 0 for the chart
                        akpDataReg.push(values.akp === '-' ? 0 : removeDots(values.akp));  // Convert '-' to 0 for the chart
                    });

                    chartTonaseAKPReg.updateSeries([{
                        name: 'AKP (%): ',
                        data: akpDataReg
                    }, {
                        name: 'Taksasi (Kg): ',
                        data: taksasiDataReg
                    }]);

                    chartTonaseAKPReg.updateOptions({
                        xaxis: {
                            categories: chartCategoriesReg
                        },
                        tooltip: {
                            y: [{
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);
                                        return formattedVal + ' %';
                                }
                            }, {
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);

                                        return formattedVal + ' Kg';
                                }
                            }]
                        }
                    });

                    $.each(parseResult['data_wil'], function(wilayah, values) {
                        chartCategoriesWil.push(wilayah);
                        taksasiDataWil.push(values.taksasi === '-' ? 0 : removeDots(values.taksasi));  // Convert '-' to 0 for the chart
                        akpDataWil.push(values.akp === '-' ? 0 : removeDots(values.akp));  // Convert '-' to 0 for the chart
                    });

                    chartTonaseAKPWil.updateSeries([{
                        name: 'AKP (%): ',
                        data: akpDataWil
                    }, {
                        name: 'Taksasi (Kg): ',
                        data: taksasiDataWil
                    }]);

                    chartTonaseAKPWil.updateOptions({
                        xaxis: {
                            categories: chartCategoriesWil
                        },
                        tooltip: {
                            y: [{
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);
                                        return formattedVal + ' %';
                                }
                            }, {
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);

                                        return formattedVal + ' Kg';
                                }
                            }]
                        }
                    });


                    $.each(parseResult['data_est'], function(estate, values) {
                        chartCategoriesEst.push(estate);
                        taksasiDataEst.push(values.taksasi === '-' ? 0 : removeDots(values.taksasi));  // Convert '-' to 0 for the chart
                        akpDataEst.push(values.akp === '-' ? 0 : removeDots(values.akp));  // Convert '-' to 0 for the chart
                    });

                    chartTonaseAKPEst.updateSeries([{
                        name: 'AKP (%): ',
                        data: akpDataEst
                    }, {
                        name: 'Taksasi (Kg): ',
                        data: taksasiDataEst
                    }]);

                    chartTonaseAKPEst.updateOptions({
                        xaxis: {
                            categories: chartCategoriesEst
                        },
                        tooltip: {
                            y: [{
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);
                                    return formattedVal + ' %';
                                }
                            }, {
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);
                                    return formattedVal + ' Kg';
                                }
                            }]
                        }
                    });
                }
            });
        }

        function loadListWilayahDropdown(regional) {
                    var _token = $('input[name="_token"]').val();
                    $.ajax({
                        url: "{{ route('getNameWilayah') }}",
                        method: "POST",
                        cache: false,
                        data: {
                            _token: _token,
                            regional: regional,
                        },
                        success: function(result) {
                        
                            $('#wilDropdown').empty().append(result);
                            $('#wilDropdown option:first').prop('selected', true);

                            loadEstateDropdown(regional, $('#wilDropdown').val());
                        }
                    });
                }
            
        // Event listener for Regional dropdown change
        $('#reg').on('change', function() {
            var selectedRegionalId = $(this).val();
            removeMarkers();
            markerDelAgain();
            selectedDate = $('#tgl').val()
            loadDataTableRegionalWilayah($('#tgl').val(),  selectedRegionalId)
            loadDataTableRealisasi(selectedDate,selectedRegionalId)
            loadListWilayahDropdown(selectedRegionalId)
            loadEstateDropdown(selectedRegionalId, $('#wilDropdown').val());
            updateChartSeriesRealisasi()
        });

        var defaultRegionalId = $('#reg').val();
        var defaultValue = $('#pilihanChartRealisasi option:first').val();
        function loadDataTableEstate(estate, selectedDate) {
            var _token = $('input[name="_token"]').val();

            $.ajax({
                url: "{{ route('get-data-estate') }}",
                method: "GET",
                data: {
                    _token: _token,
                    estate_request: estate,
                    tgl_request: selectedDate
                },
                success: function(result) {
                    var parseResult = JSON.parse(result);      
                    var dataEst = [];

                    $.each(parseResult['data_estate'], function(afdeling, values) {
                        dataEst.push({
                            "afdeling": afdeling,
                            "luas": values.luas,
                            "jumlahBlok": values.jumlahBlok,
                            "akp": values.akp,
                            "taksasi": values.taksasi,
                            "ritase": values.ritase,
                            "keb_pemanen": values.keb_pemanen
                        });
                    });
                    if ($.fn.dataTable.isDataTable('#table-afdeling')) {
                        // If DataTable already exists, destroy it and create a new one
                        $('#table-afdeling').DataTable().clear().destroy();
                    }


                    $('#table-afdeling').DataTable({
                        "processing": true,
                        "responsive": true,
                        scrollX: true,
                        "serverSide": false,
                        "data": dataEst,
                        "pageLength": 25,
                        "columns": [
                            { "data": "afdeling", "title": "AFDELING" },
                            { "data": "luas", "title": "LUAS (Ha)" },
                            // {
                            //     "data": "luas",
                            //     "title": "LUAS (Ha)",
                            //     "render": function(data, type, row, meta) {
                            //         if (type === 'display' && data !== '-' && data !== 0) {
                            //             return '<a href="your-link-here" style="color:blue; text-decoration: underline">' + data + '</a>';
                            //         }
                            //         return data;
                            //     }
                            // },
                                                { "data": "jumlahBlok", "title": "JUMLAH BLOK" },
                            { "data": "akp", "title": "AKP (%)" },
                            { "data": "taksasi", "title": "TAKSASI (Kg)" },
                            { "data": "ritase", "title": "RITASE" },
                            { "data": "keb_pemanen", "title": "KEB. PEMANEN" }
                        ],
                        // "createdRow": function(row, data, dataIndex) {
                        //     $('td', row).eq(0).css({
                        //     'background-color': '#fbd4b4',  // Change this to the desired background color
                        //     'color': 'black'              // Change this to the desired text color
                        // });
                        // }
                    });

                    var taksasiDataAfd = [];
                    var akpDataAfd = [];
                    var chartCategoriesAfd = [];

                    $.each(parseResult['data_estate'], function(afdeling, values) {
                        chartCategoriesAfd.push(afdeling);
                        taksasiDataAfd.push(values.taksasi === '-' ? 0 : removeDots(values.taksasi));  // Convert '-' to 0 for the chart
                        akpDataAfd.push(values.akp === '-' ? 0 : removeDots(values.akp));  // Convert '-' to 0 for the chart
                    });

                    ChartGrafikTonaseAfdeling.updateSeries([{
                        name: 'AKP (%): ',
                        data: akpDataAfd
                    }, {
                        name: 'Taksasi (Kg): ',
                        data: taksasiDataAfd
                    }]);

                    ChartGrafikTonaseAfdeling.updateOptions({
                        xaxis: {
                            categories: chartCategoriesAfd
                        },
                        tooltip: {
                            y: [{
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);
                                    return formattedVal + ' %';
                                }
                            }, {
                                formatter: function (val) {
                                    let formattedVal = formatNumberForChart(val);
                                    return formattedVal + ' Kg';
                                }
                            }]
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("An error occurred while loading data for another data table: ", error);
                }
            });
        }

        $('#wilDropdown').on('change', function() {
            removeMarkers();
            markerDelAgain();
            loadEstateDropdown($('#reg').val(), $('#wilDropdown').val());
        })
      
        $('#pilihanChartRealisasi').on('change', function() {
            updateChartSeriesRealisasi()
        });
        
        $('#est').on('change', function() {
            var selectedEstateId = $(this).val();
            var selectedDate = $('#tgl').val(); // Get the selected date from #tgl

            loadDataTableEstate(selectedEstateId, selectedDate);
            removeMarkers();
            markerDelAgain();
            getPlotEstate(selectedEstateId, selectedDate)
            getPlotBlok(selectedEstateId, selectedDate)
            getlineTaksasi(selectedEstateId, selectedDate)
            getMarkerMan(selectedEstateId, selectedDate)
            getUserTaksasi(selectedEstateId, selectedDate)
            
        });

        function updateChartSeriesRealisasi() {
            var pilihanChart = $('#pilihanChartRealisasi').val();
            var valueTaksasiReg = [];
            var valueRealisasiReg = [];
            var chartCategoriesXReg = [];
            var valueTaksasiWil = [];
            var valueRealisasiWil = [];
            var chartCategoriesXWil = [];
            var valueTaksasiEst = [];
            var valueRealisasiEst = [];
            var chartCategoriesXEst = [];
            var keyExists = doesKeyExist(finalDataReg, pilihanChart);
            let titleChartRealisasi = ''
            if (keyExists) {
       
            finalDataReg.forEach(obj => {
            chartCategoriesXReg.push(obj.key);
            let tempValueTaksasiReg = obj[pilihanChart];
            valueTaksasiReg.push(tempValueTaksasiReg !== '-' ? removeDots(tempValueTaksasiReg)  || 0 : 0);

            let tempValueRealisasiReg = 0;
            if (pilihanChart === 'taksasi_tonase') {
                tempValueRealisasiReg = obj['taksasi_realisasi'];
                titleChartRealisasi = 'Tonase Taksasi dan Realisasi'
            } else if (pilihanChart === 'akp_taksasi') {
                titleChartRealisasi = 'AKP Taksasi dan Realisasi'
                tempValueRealisasiReg = obj['akp_realisasi'];
            } else if (pilihanChart === 'ha_panen_taksasi') {
                titleChartRealisasi = 'Ha Panen Taksasi dan Realisasi'
                tempValueRealisasiReg = obj['ha_panen_realisasi'];
            } else if (pilihanChart === 'bjr_taksasi') {
                titleChartRealisasi = 'BJR Taksasi dan Realisasi'
                tempValueRealisasiReg = obj['bjr_realisasi'];
            } else if (pilihanChart === 'keb_hk_taksasi') {
                titleChartRealisasi = 'Kebutuhan HK Taksasi dan Realisasi'
                tempValueRealisasiReg = obj['keb_hk_realisasi'];
            }
            valueRealisasiReg.push(tempValueRealisasiReg !== '-' ? removeDots(tempValueRealisasiReg) || 0 : 0);
            });

            finalDataWil.forEach(obj => {
            chartCategoriesXWil.push(obj.key);
            let tempValueTaksasiWil = obj[pilihanChart];
            valueTaksasiWil.push(tempValueTaksasiWil !== '-' ? removeDots(tempValueTaksasiWil) || 0 : 0);

            let tempValueRealisasiWil = 0;
            if (pilihanChart === 'taksasi_tonase') {
                tempValueRealisasiWil = obj['taksasi_realisasi'];
            } else if (pilihanChart === 'akp_taksasi') {
                tempValueRealisasiWil = obj['akp_realisasi'];
            } else if (pilihanChart === 'ha_panen_taksasi') {
                tempValueRealisasiWil = obj['ha_panen_realisasi'];
            } else if (pilihanChart === 'bjr_taksasi') {
                tempValueRealisasiWil = obj['bjr_realisasi'];
            } else if (pilihanChart === 'keb_hk_taksasi') {
                tempValueRealisasiWil = obj['keb_hk_realisasi'];
            }
            valueRealisasiWil.push(tempValueRealisasiWil !== '-' ? removeDots(tempValueRealisasiWil) || 0 : 0);
            });

                finalDataEst.forEach(obj => {
                    chartCategoriesXEst.push(obj.key);
                    let tempValueTaksasiEst = obj[pilihanChart];
                    valueTaksasiEst.push(tempValueTaksasiEst !== '-' ? removeDots(tempValueTaksasiEst) || 0 : 0);

                    let tempValueRealisasiEst = 0;
                    if (pilihanChart === 'taksasi_tonase') {
                        tempValueRealisasiEst = obj['taksasi_realisasi'];
                    } else if (pilihanChart === 'akp_taksasi') {
                        tempValueRealisasiEst = obj['akp_realisasi'];
                    } else if (pilihanChart === 'ha_panen_taksasi') {
                        tempValueRealisasiEst = obj['ha_panen_realisasi'];
                    } else if (pilihanChart === 'bjr_taksasi') {
                        tempValueRealisasiEst = obj['bjr_realisasi'];
                    } else if (pilihanChart === 'keb_hk_taksasi') {
                        tempValueRealisasiEst = obj['keb_hk_realisasi'];
                    }
                    valueRealisasiEst.push(tempValueRealisasiEst !== '-' ? removeDots(tempValueRealisasiEst) || 0 : 0);
                });
        }




            // Update series data
            chartRealisasiRegional.updateSeries([{
                name: 'Taksasi',
                data: valueTaksasiReg
            }, {
                name: 'Realisasi',
                data: valueRealisasiReg
            }]);

            chartRealisasiRegional.updateOptions({
                title: {
                    text: titleChartRealisasi + ' Regional',
                    align: 'center',
                    style: {
                        fontSize: '15px',
                        fontWeight: 'bold',
                        color: '#263238'
                    }
                },
                xaxis: {
                    categories: chartCategoriesXReg
                },
                tooltip: {
                    y: [{
                        formatter: function (val) {
                            let formattedVal = formatNumberForChart(val);
                            if (pilihanChart === 'akp_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'taksasi_tonase') {
                                return formattedVal + " Kg";
                            } else if (pilihanChart === 'ha_panen_taksasi') {
                                return formattedVal + " Ha";
                            } else if (pilihanChart === 'bjr_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'keb_hk_taksasi') {
                                return formattedVal + " org";
                            } else {
                                return formattedVal;
                            }
                        }
                    }, {
                        formatter: function (val) {
                            let formattedVal = formatNumberForChart(val);
                            if (pilihanChart === 'akp_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'taksasi_tonase') {
                                return formattedVal + " Kg";
                            } else if (pilihanChart === 'ha_panen_taksasi') {
                                return formattedVal + " Ha";
                            } else if (pilihanChart === 'bjr_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'keb_hk_taksasi') {
                                return formattedVal + " org";
                            } else {
                                return formattedVal;
                            }
                        }
                    }]
                }
            });

                chartRealisasiWilayah.updateSeries([{
                    name: 'Taksasi',
                    data: valueTaksasiWil
                }, {
                    name: 'Realisasi',
                    data: valueRealisasiWil
                }]);

                chartRealisasiWilayah.updateOptions({
                    title: {
                        text: titleChartRealisasi + ' Wilayah',
                        align: 'center',
                        style: {
                        fontSize:  '15px',
                        fontWeight:  'bold',
                        color:  '#263238'
                        },
                    },
                    xaxis: {
                        categories: chartCategoriesXWil
                    },
                    tooltip: {
                    y: [{
                        formatter: function (val) {
                            let formattedVal = formatNumberForChart(val);
                            if (pilihanChart === 'akp_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'taksasi_tonase') {
                                return formattedVal + " Kg";
                            } else if (pilihanChart === 'ha_panen_taksasi') {
                                return formattedVal + " Ha";
                            } else if (pilihanChart === 'bjr_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'keb_hk_taksasi') {
                                return formattedVal + " org";
                            } else {
                                return formattedVal;
                            }
                        }
                    }, {
                        formatter: function (val) {
                            let formattedVal = formatNumberForChart(val);
                            if (pilihanChart === 'akp_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'taksasi_tonase') {
                                return formattedVal + " Kg";
                            } else if (pilihanChart === 'ha_panen_taksasi') {
                                return formattedVal + " Ha";
                            } else if (pilihanChart === 'bjr_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'keb_hk_taksasi') {
                                return formattedVal + " org";
                            } else {
                                return formattedVal;
                            }
                        }
                    }]
                }
              
                });

                chartRealisasiEstate.updateSeries([{
                    name: 'Taksasi',
                    data: valueTaksasiEst
                }, {
                    name: 'Realisasi',
                    data: valueRealisasiEst
                }]);

                chartRealisasiEstate.updateOptions({
                    title: {
                        text: titleChartRealisasi + ' Estate',
                        align: 'center',
                        style: {
                        fontSize:  '15px',
                        fontWeight:  'bold',
                        color:  '#263238'
                        },
                    },
                    xaxis: {
                        categories: chartCategoriesXEst
                    },
                    tooltip: {
                    y: [{
                        formatter: function (val) {
                            let formattedVal = formatNumberForChart(val);
                            if (pilihanChart === 'akp_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'taksasi_tonase') {
                                return formattedVal + " Kg";
                            } else if (pilihanChart === 'ha_panen_taksasi') {
                                return formattedVal + " Ha";
                            } else if (pilihanChart === 'bjr_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'keb_hk_taksasi') {
                                return formattedVal + " org";
                            } else {
                                return formattedVal;
                            }
                        }
                    }, {
                        formatter: function (val) {
                            let formattedVal = formatNumberForChart(val);
                            if (pilihanChart === 'akp_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'taksasi_tonase') {
                                return formattedVal + " Kg";
                            } else if (pilihanChart === 'ha_panen_taksasi') {
                                return formattedVal + " Ha";
                            } else if (pilihanChart === 'bjr_taksasi') {
                                return formattedVal + " %";
                            } else if (pilihanChart === 'keb_hk_taksasi') {
                                return formattedVal + " org";
                            } else {
                                return formattedVal;
                            }
                        }
                    }]
                }
                
                });
            }
            });

    function doesKeyExist(dataArray, searchString) {
    return dataArray.some(obj => Object.keys(obj).includes(searchString));
    }

    function removeDots(value) {
        return  value.replace(/\./g, '').replace(',', '.');
    }

    function formatNumberForChart(val) {
        // Convert number to string and split integer and fractional parts
        let [integerPart, fractionalPart] = val.toString().split('.');
        // Add thousand separators to the integer part
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        // Join integer and fractional parts with a comma if there's a fractional part
        return fractionalPart ? `${integerPart},${fractionalPart}` : integerPart;
    }
</script>