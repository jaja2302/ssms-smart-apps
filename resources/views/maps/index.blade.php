@include('layout.header')

<div class="content-wrapper">
    <style>
        /* CSS for the legend icons */
        .pucat-icon {
            display: inline-block;
            width: 14px;
            height: 21px;
            background: url(https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png) no-repeat;
            background-size: contain;
            vertical-align: middle;
        }

        .ringan-icon {
            display: inline-block;
            width: 14px;
            height: 21px;
            background: url(https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png) no-repeat;
            background-size: contain;
            vertical-align: middle;
        }

        .berat-icon {
            display: inline-block;
            width: 14px;
            height: 21px;
            background: url(https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png) no-repeat;
            background-size: contain;
            vertical-align: middle;
        }

        /* CSS for the legend container */
        .legend-container {
            background-color: #fff;
            /* White background */
            opacity: 0.8;
            /* Set opacity to make it semi-transparent */
            padding: 10px;
            /* Add some padding for better readability */
            border-radius: 5px;
            /* Add border radius for rounded corners */
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .loading-text {
            animation: fadeInOut 2s ease-in-out infinite;
        }
    </style>

    <div class="d-flex justify-content-end mt-3 mb-2 ml-3 mr-3" style="padding-top: 20px;">
        <div class="row w-100">
            <div class="col-md-2 offset-md-8">
                {{csrf_field()}}
                <select class="form-control" id="Estate">
                    <option value="" disabled>Pilih EST</option>
                    @foreach($estate as $item)

                    <option value={{$item}} selected>{{$item}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                {{ csrf_field() }}
                <select class="form-control" id="tahun_tanam">
                    <option value="" disabled>Pilih Tahun</option>
                    @foreach($tahun as $item)

                    <option value={{$item}} selected>{{$item}}</option>
                    @endforeach
                </select>

            </div>
        </div>
        <button class="btn btn-primary mb-3 ml-3" id="showEstMap">Show</button>
    </div>

    <div class="card p-4">
        <h4 class="text-center mt-2" style="font-weight: bold">Tracking Plot Pokok - </h4>
        <hr>
        <div id="map" style="height: 650px;"></div>
    </div>

</div>
@include('layout.footer')

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />

<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var map; // Define the map variable globally

    $(document).ready(function() {
        map = L.map('map').setView([-2.2745234, 111.61404248], 13);

        var googleStreet = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png");
        var googleSatellite = L.tileLayer('http://{s}.google.com/vt?lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });

        googleStreet.addTo(map); // Add "Google Street" as the default base map

        var baseMaps = {
            "Google Street": googleStreet,
            "Google Satellite": googleSatellite
        };

        L.control.layers(baseMaps).addTo(map);

        map.addControl(new L.Control.Fullscreen());
    });

    $('#showEstMap').click(function() {
        getPlotBlok();

        Swal.fire({
            title: 'Loading',
            html: '<span class="loading-text">Mohon Tunggu...</span>',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            }
        });
    });

    function drawBlokPlot(blok) {
        if (blok.length === 0) {
            const errorAnimationPath = 'https://assets1.lottiefiles.com/packages/lf20_no386ede.json';
            showLottieAlert(errorAnimationPath);
            return;
        }

        var getPlotStr = '{"type":"FeatureCollection","features":[';

        for (let i = 0; i < blok.length; i++) {
            getPlotStr +=
                '{"type":"Feature","properties":{"blok":"' +
                blok[i][1]['blok'] +
                '","estate":"' +
                blok[i][1]['estate'] +
                '","afdeling":"' +
                blok[i][1]['afdeling'] +
                '","nilai":"' +
                blok[i][1]['nilai'] +
                '"},"geometry":{"coordinates":[[' +
                blok[i][1]['latln'] +
                ']],"type":"Polygon"}}';

            if (i < blok.length - 1) {
                getPlotStr += ',';
            }
        }

        getPlotStr += ']}';

        var blok = JSON.parse(getPlotStr);

        // Remove the previous legend if it exists


        test = L.geoJSON(blok, {

            onEachFeature: function(feature, layer) {
                layer.myTag = 'BlokMarker';
                // layer.bindPopup(
                //     "<p><b>Blok</b>: " +
                //     feature.properties.blok +
                //     '</p> ' +
                //     "<p><b>Afdeling</b>: " +
                //     feature.properties.afdeling +
                //     '</p>'
                // );

                var label = L.marker(layer.getBounds().getCenter(), {
                    icon: L.divIcon({
                        className: 'label-blok',
                        html: feature.properties.blok,
                        iconSize: [50, 10],
                    }),
                }).addTo(map);


            },
        }).addTo(map);

        if (test.getBounds().isValid()) {
            map.fitBounds(test.getBounds());
        } else {
            console.error('Invalid bounds:', test.getBounds());
        }
    }

    var legend;

    function draw_pokok(pokok_data) {
        if (legend) {
            legend.remove(); // Remove the existing legend if it exists
        }
        if (document.getElementsByClassName('legend')[0]) {
            document.getElementsByClassName('legend')[0].remove();
        }

        var transGroup = L.layerGroup();

        var transicon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png",
            shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
            iconSize: [14, 21],
            iconAnchor: [7, 22],
            popupAnchor: [1, -34],
            shadowSize: [28, 20],
        });
        var pucat = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png",
            shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
            iconSize: [14, 21],
            iconAnchor: [7, 22],
            popupAnchor: [1, -34],
            shadowSize: [28, 20],
        });
        var ringan = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png",
            shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
            iconSize: [14, 21],
            iconAnchor: [7, 22],
            popupAnchor: [1, -34],
            shadowSize: [28, 20],
        });
        var berat = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png",
            shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
            iconSize: [14, 21],
            iconAnchor: [7, 22],
            popupAnchor: [1, -34],
            shadowSize: [28, 20],
        });

        var pucatCount = 0;
        var ringanCount = 0;
        var beratCount = 0;
        for (var i = 0; i < pokok_data.length; i++) {
            var key = pokok_data[i][0];
            var data = pokok_data[i][1];

            // Loop through each data item under the current key
            for (var j = 0; j < data.length; j++) {
                var lat = data[j].lat;
                var lon = data[j].lon;
                var keterangan = data[j].keterangan.trim().toLowerCase(); // Convert to lowercase and remove leading/trailing spaces
                var tahun = data[j].tahun_tanam;
                var blok = data[j].blok;
                // Check if lat and lon are not undefined before creating the marker
                // console.log(lat);

                // Create a marker with the given latitude, longitude, and appropriate icon based on keterangan
                var marker;


                switch (keterangan) {
                    case "pucat":
                        pucatCount++;
                        marker = L.marker([lat, lon], {
                            icon: pucat
                        });
                        break;
                    case "ringan":
                        ringanCount++;
                        marker = L.marker([lat, lon], {
                            icon: ringan
                        });
                        break;
                    case "berat":
                        beratCount++;
                        marker = L.marker([lat, lon], {
                            icon: berat
                        });
                        break;
                    default:
                        marker = L.marker([lat, lon], {
                            icon: transicon
                        });
                }

                // Set the marker's popup content to the keterangan value
                marker.bindPopup('Keterangan: ' + keterangan + '<br>Tahun Tanam: ' + tahun + '<br>Blok: ' + blok);


                // Add the marker to the layer group
                transGroup.addLayer(marker);
            }
        }


        // Add the Layer Group to the map (assuming you have already initialized the Leaflet map)
        transGroup.addTo(map);
        // ... Your existing code ...

        // Create a custom legend control
        var legend = L.control({
            position: 'bottomright'
        });

        legend.onAdd = function(map) {
            var div = L.DomUtil.create('div', 'legend legend-container'); // Add 'legend-container' class here
            div.innerHTML +=
                '<b>Legend</b><br>' +
                '<input type="checkbox" id="pucat-checkbox" checked><label for="pucat-checkbox" class="pucat-icon"></label> Pucat<br>' +
                '<input type="checkbox" id="ringan-checkbox" checked><label for="ringan-checkbox" class="ringan-icon"></label> Ringan<br>' +
                '<input type="checkbox" id="berat-checkbox" checked><label for="berat-checkbox" class="berat-icon"></label> Berat<br>';
            return div;
        };


        legend.addTo(map);

        // Event listeners for checkboxes
        document.getElementById('pucat-checkbox').addEventListener('change', filterMarkers);
        document.getElementById('ringan-checkbox').addEventListener('change', filterMarkers);
        document.getElementById('berat-checkbox').addEventListener('change', filterMarkers);

        function filterMarkers() {
            // Get the checkbox states
            var showPucat = document.getElementById('pucat-checkbox').checked;
            var showRingan = document.getElementById('ringan-checkbox').checked;
            var showBerat = document.getElementById('berat-checkbox').checked;

            // Loop through all markers in the transGroup and set their visibility based on the checkbox states
            transGroup.eachLayer(function(marker) {
                var keterangan = marker.getPopup().getContent().split('<br>')[0].split(': ')[1].toLowerCase(); // Get keterangan value from the marker's popup content
                var isVisible =
                    (keterangan === 'pucat' && showPucat) ||
                    (keterangan === 'ringan' && showRingan) ||
                    (keterangan === 'berat' && showBerat);
                if (isVisible) {
                    marker.addTo(map); // Show the marker if it should be visible
                } else {
                    marker.removeFrom(map); // Hide the marker if it should not be visible
                }
            });
        }

    }




    function getPlotBlok() {
        var _token = $('input[name="_token"]').val();
        var estData = $("#Estate").val();
        var tahunData = $("#tahun_tanam").val();
        var afd = 'OC';
        $.ajax({
            url: "{{ route('plotBlok') }}",
            method: "get",
            data: {
                estData: estData,
                tahunData: tahunData,
                afd: afd,
                _token: _token
            },
            success: function(result) {
                var plot = JSON.parse(result);
                Swal.close();
                const blokResult = Object.entries(plot['blok']);
                const polygonCoords = Object.entries(plot['coords']);
                const pokok_data = Object.entries(plot['pokok_data']);


                // Remove existing layers before updating the map
                if (map) {
                    map.eachLayer(function(layer) {
                        map.removeLayer(layer);
                    });
                }


                drawBlokPlot(blokResult)
                draw_pokok(pokok_data)

            }
        });
    }
</script>