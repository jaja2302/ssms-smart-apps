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
                <select class="form-control" id="Estate" onchange="populateEstateOptions(this.value)">
                    @foreach($estate as $item)

                    <option value={{$item}} selected>{{$item}}</option>
                    @endforeach
                </select>

                <select class="form-control" id="afdling">

                </select>
            </div>

        </div>
        <button class="btn btn-primary mb-3 ml-3" id="showEstMap">Show</button>
    </div>
    <button id="saveButton">Save</button>
    <div class="card p-4">
        <h4 class="text-center mt-2" style="font-weight: bold">Last EST = {{$lastest}} </h4>
        <h4 class="text-center mt-2" style="font-weight: bold">Last afd = {{$lastafd}} </h4>
        <hr>
        <div id="map" style="height: 650px;"></div>
    </div>


</div>
@include('layout.footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
<script type="text/javascript" src="{{ asset('js/Leaflet.Editable.js') }}"></script>
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>


<script>
    var afd = <?php echo json_encode($afd); ?>;
    var afdeling = document.getElementById('afdling');

    function populateEstateOptions(estateSelcted) {
        // Clear existing options

        // console.log(selectedWilIdx);
        afdeling.innerHTML = '';

        // Filter the opt_est array based on the selectedWilIdx
        var filteredEstates = afd.filter(function(estate) {
            return estate.est == estateSelcted;
        });
        filteredEstates.forEach(function(estate) {
            var optionElement = document.createElement('option');
            optionElement.value = estate.id;
            optionElement.textContent = estate.id;
            afdeling.appendChild(optionElement);
        });



        afdeling.dispatchEvent(new Event('change'));
    }
    $(document).ready(function() {
        // Initialize the map
        var map = L.map('map', {
            editable: true,
            center: [0, 0], // Adjust this to the desired initial map center
            zoom: 10, // Adjust the initial zoom level
        });

        // Add a base layer (Google Street) to the map
        var googleSatellite = L.tileLayer('http://{s}.google.com/vt?lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        });

        // googleSatellite, addtomap
        googleSatellite.addTo(map); // Add "Google Street" as the default base map

        // Create a layer group for markers
        var markerBlok = L.layerGroup().addTo(map);


        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems,
                poly: {
                    allowIntersection: false
                }
            },
            draw: {
                polygon: {
                    allowIntersection: false,
                    showArea: true
                }
            }
        });


        map.addControl(drawControl);
        var est = "est"; // Define your 'est' prefix
        var afd = "afd"; // Define your 'afd' prefix

        map.on('draw:created', function(e) {
            var layer = e.layer;
            drawnItems.addLayer(layer);

            // Access the polygon's coordinates
            var polygonCoordinates = layer.getLatLngs().flat(); // Flatten the nested arrays
            console.log(polygonCoordinates);

            $('#saveButton').click(function() {
                var coordinates = polygonCoordinates.map(function(latLng) {
                    return [latLng.lat, latLng.lng]; // Create an array with lat and lng
                });
                var estData = $("#Estate").val();
                var afdling = $("#afdling").val();
                // Send the coordinates to your Laravel application using AJAX
                console.log(estData);
                console.log(afdling);
                var _token = $('input[name="_token"]').val();
                $.ajax({
                    type: 'POST', // Adjust the HTTP method if needed
                    url: "{{ route('inputquery') }}",
                    data: JSON.stringify({
                        coordinates: coordinates,
                        estData: estData,
                        afdling: afdling,
                        _token: _token
                    }), // Send as JSON
                    contentType: 'application/json', // Set content type to JSON
                    success: function(response) {
                        // Handle the response from the server
                        console.log(response);
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });


            });

        });

        // Define the drawMap function
        function drawMap(newData) {
            markerBlok.clearLayers(); // Clear area maps layer only

            var bounds = new L.LatLngBounds(); // Create a bounds object to store the coordinates

            for (var key in newData) {
                if (newData.hasOwnProperty(key)) {
                    var regionData = newData[key];

                    for (var i = 0; i < regionData.length; i++) {
                        var data = regionData[i].lat_lon;

                        if (Array.isArray(data)) {
                            // Initialize the coordinates array for each polygon
                            var coordinates = [];

                            for (var j = 0; j < data.length; j++) {
                                var latLon = data[j].split(';'); // Split the lat_lon string by ';'
                                var lat = parseFloat(latLon[0]);
                                var lon = parseFloat(latLon[1]);

                                if (!isNaN(lat) && !isNaN(lon)) {
                                    var latLng = new L.LatLng(lat, lon);

                                    // Extend the bounds with the new LatLng object
                                    bounds.extend(latLng);
                                    coordinates.push(latLng);
                                }
                            }
                            var polygonStyle = {
                                fillOpacity: 0.05,
                                opacity: 0.5
                            };


                            var polygon = L.polygon(coordinates, polygonStyle).addTo(markerBlok);

                            var polygonCenter = polygon.getBounds().getCenter();


                        }
                    }
                }
            }

            // Fit the map to the calculated bounds
            map.fitBounds(bounds);
        }


        $('#showEstMap').on('click', function() {
            var _token = $('input[name="_token"]').val();
            var estData = $("#Estate").val();
            var afdling = $("#afdling").val();

            console.log(estData);
            console.log(afdling);

            $.ajax({
                url: "{{ route('mapsestatePlot') }}",
                method: "get",
                data: {
                    estData: estData,
                    afdling: afdling,
                    _token: _token
                },
                success: function(result) {
                    var plot = JSON.parse(result);
                    // Swal.close();
                    const newData = Object.entries(plot['blok']);


                    drawMap(newData)
                    // draw_pokok(blokResult)

                }
            });
        });

    });
</script>