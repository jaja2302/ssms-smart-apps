<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Taksasi Maps</title>
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <!-- Esri Leaflet -->
    <script src="{{ asset('vendor/esri-leaflet/esri-leaflet.js') }}"></script>
    <script src="{{ asset('vendor/esri-leaflet-vector/esri-leaflet-vector.js') }}"></script>

    <!-- Leaflet Plugins -->
    <script src="{{ asset('vendor/leaflet-awesome-markers/leaflet.awesome-markers.js') }}"></script>
    <script src="{{ asset('vendor/leaflet-polylinedecorator/leaflet.polylineDecorator.min.js') }}"></script>
    <script src="{{ asset('vendor/leaflet-easybutton/easy-button.js') }}"></script>

    <!-- Other Libraries -->
    <script src="{{ asset('vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>
    <script src="{{ asset('vendor/dom-to-image/dom-to-image.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/generatemaps.css') }}">

</head>

<body>
    <div id="map"></div>


</body>

</html>




<script>
    const arrData = @json($arrData);
    const estate_plot = @json($estate_plot);
    const userTaksasi = @json($userTaksasi);
    const blokPerEstate = @json($blokLatLn);
    const datetime = @json($datetime);



    // console.log(arrData)

    document.addEventListener('DOMContentLoaded', function() {
        createMapImage(arrData, estate_plot, userTaksasi, blokPerEstate, datetime)
    });


    function createMapImage() {
        const map = L.map('map', {
            center: [-2.4833826, 112.9721219], // Default coordinates for Central Kalimantan
            zoom: 13,
            attributionControl: false,
            zoomControl: true,
            backgroundColor: 'white' // Add white background color
        });

        const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        const arr = Object.entries(arrData);



        const estateData = arr[0][0];

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
        geoJsonEst += '"' + estate_plot[estateData]['est'] + '"},'
        geoJsonEst += '"geometry"'
        geoJsonEst += ":"
        geoJsonEst += '{"coordinates"'
        geoJsonEst += ":"
        geoJsonEst += '[['
        geoJsonEst += estate_plot[estateData]['plot']
        geoJsonEst += ']],"type"'
        geoJsonEst += ":"
        geoJsonEst += '"Polygon"'
        geoJsonEst += '}},'

        geoJsonEst = geoJsonEst.substring(0, geoJsonEst.length - 1);
        geoJsonEst += ']}'

        // Parse the string into a JSON object
        var estate = JSON.parse(geoJsonEst)

        // Use the parsed GeoJSON object
        var estateLayer = L.geoJSON(estate, {
            onEachFeature: function(feature, layer) {
                layer.myTag = 'EstateMarker'
                var label = L.marker(layer.getBounds().getCenter(), {
                    icon: L.divIcon({
                        className: 'label-estate common-estate-style',
                        html: feature.properties.estate,
                    })
                }).addTo(map);
                layer.options.className = 'estate-All';
                layer.addTo(map);
            },
            style: function(feature) {
                return {
                    color: "#003B73",
                    opacity: 1,
                    fillOpacity: 0.4
                };
            }
        }).addTo(map);

        var getPlotStr = '{"type"'
        getPlotStr += ":"
        getPlotStr += '"FeatureCollection",'
        getPlotStr += '"features"'
        getPlotStr += ":"
        getPlotStr += '['
        for (let i = 0; i < blokPerEstate[estateData].length; i++) {
            getPlotStr += '{"type"'
            getPlotStr += ":"
            getPlotStr += '"Feature",'
            getPlotStr += '"properties"'
            getPlotStr += ":"
            getPlotStr += '{"blok"'
            getPlotStr += ":"
            getPlotStr += '"' + blokPerEstate[estateData][i]['blok'] + '",'
            getPlotStr += '"estate"'
            getPlotStr += ":"
            getPlotStr += '"' + blokPerEstate[estateData][i]['estate'] + '",'
            getPlotStr += '"afdeling"'
            getPlotStr += ":"
            getPlotStr += '"' + blokPerEstate[estateData][i]['afdeling'] + '"'
            getPlotStr += '},'
            getPlotStr += '"geometry"'
            getPlotStr += ":"
            getPlotStr += '{"coordinates"'
            getPlotStr += ":"
            getPlotStr += '[['
            getPlotStr += blokPerEstate[estateData][i]['latln']
            getPlotStr += ']],"type"'
            getPlotStr += ":"
            getPlotStr += '"Polygon"'
            getPlotStr += '}},'
        }
        getPlotStr = getPlotStr.substring(0, getPlotStr.length - 1);
        getPlotStr += ']}'

        var blok = JSON.parse(getPlotStr)

        var blok = JSON.parse(getPlotStr);

        // Create the block layer with styling and labels
        var centerBlok = L.geoJSON(blok, {
            onEachFeature: function(feature, layer) {
                layer.myTag = 'BlokMarker'
                var label = L.marker(layer.getBounds().getCenter(), {
                    icon: L.divIcon({
                        className: 'label-bidang',
                        html: feature.properties.blok,
                        iconSize: [50, 10]
                    })
                }).addTo(map);
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
                            fillColor: "#77543f",
                                color: 'white',
                                fillOpacity: 0.4,
                                opacity: 0.4,
                        };
                    case 'OG':
                        return {
                            fillColor: "#dfd29e",
                                color: 'white',
                                fillOpacity: 0.4,
                                opacity: 0.4,
                        };
                    case 'OH':
                        return {
                            fillColor: "#db423c",
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
        }).addTo(map);


        var geoJsonLine = '{"type":"FeatureCollection","features":[';

        for (let i = 0; i < arrData[estateData].length; i++) {
            geoJsonLine += '{"type":"Feature","properties":{"nama":"' + arrData[estateData][i]['lokasi_kerja'] + '","afdeling":"' + arrData[estateData][i]['afdeling'] + '"},';
            geoJsonLine += '"geometry":{"type":"Polygon","coordinates":[[';
            geoJsonLine += arrData[estateData][i]['plot'];
            geoJsonLine += ']]}}';
            if (i < arrData[estateData].length - 1) geoJsonLine += ',';
        }
        geoJsonLine += ']}';

        var lineTaksasi = JSON.parse(geoJsonLine);

        var customLayer = L.geoJSON(lineTaksasi, {
            onEachFeature: function(feature, layer) {
                if (feature.geometry.type === 'Polygon') {
                    var coords = feature.geometry.coordinates[0];
                    var polyline = L.polyline(coords.map(coord => [coord[1], coord[0]]), {
                        color: 'transparent'
                    }).addTo(map);

                    var decorator = L.polylineDecorator(polyline, {
                        patterns: [{
                            offset: '10%',
                            repeat: 50,
                            symbol: L.Symbol.arrowHead({
                                pixelSize: 10,
                                polygon: true,
                                pathOptions: {
                                    stroke: true,
                                    color: '#90EE90',
                                    weight: 2,
                                    fillOpacity: 1,
                                    fill: true
                                }
                            })
                        }]
                    }).addTo(map);
                }
            },
            style: function(feature) {
                // Combined style function
                const afdelingColors = {
                    'OA': "#d81b60",
                    'OB': "#8e24aa",
                    'OC': "#ffb300",
                    'OD': "#1e88e5",
                    'OE': "#67D98A",
                    'OF': "#c2a856",
                    'OG': "#2a6666",
                    'OH': "#db423c",
                    'OI': "#ba9355",
                    'OJ': "#ccff00",
                    'OK': "#8f9e8a",
                    'OL': "#14011c",
                    'OM': "#01b9c5"
                };

                return {
                    color: afdelingColors[feature.properties.afdeling] || "#003B73",
                    opacity: 1,
                    fillOpacity: 0.4
                };
            }
        }).addTo(map);



        // Add markers for each point
        arrData[estateData].forEach(data => {
            const colorMarker = getMarkerColor(data.afdeling);
            const finish = new L.Icon({
                iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${colorMarker}.png`,
                shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
                iconSize: [14, 21],
                iconAnchor: [7, 22],
                popupAnchor: [1, -34],
                shadowSize: [28, 20],
            });

            const latlonFinish = JSON.parse(data.plotAwal);
            L.marker(latlonFinish, {
                icon: finish
            }).addTo(map);
        });

        const legend = L.control({
            position: "bottomright"
        });
        const newUserTaksasi = Object.entries(userTaksasi);

        legend.onAdd = function(map) {
            var div = L.DomUtil.create("div", "legend");
            div.innerHTML += "<h4>Keterangan :</h4>";
            div.innerHTML += '<div>';

            var colorAfd = '';
            newUserTaksasi.forEach(element => {
                switch (element[0]) {
                    case 'OA':
                        colorAfd = '#d81b60'
                        break;
                    case 'OB':
                        colorAfd = '#8e24aa'
                        break;
                    case 'OC':
                        colorAfd = '#ffb300'
                        break;
                    case 'OD':
                        colorAfd = '#1e88e5'
                        break;
                    case 'OE':
                        colorAfd = '#67D98A'
                        break;
                    case 'OF':
                        colorAfd = '#c2a856'
                        break;
                    case 'OG':
                        colorAfd = '#2a6666'
                        break;
                    case 'OH':
                        colorAfd = '#db423c'
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

            div.innerHTML += '</div>';
            return div;
        };

        legend.addTo(map);

        // Wait for all tiles to load
        map.whenReady(function() {
            setTimeout(() => {
                const mapElement = document.getElementById('map');

                // Set specific dimensions for the capture
                const options = {
                    width: mapElement.offsetWidth,
                    height: mapElement.offsetHeight,
                    quality: 0.95,
                    backgroundColor: '#ffffff' // Ensure white background
                };

                domtoimage.toJpeg(mapElement, options)
                    .then(function(dataUrl) {
                        fetch('/api/save-map-image', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    imageData: dataUrl,
                                    estate: estateData,
                                    datetime: datetime
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Image saved:', data);
                                console.log('Upload successfully gan');
                            })
                            .catch(error => console.error('Error saving image:', error));
                    })
                    .catch(function(error) {
                        console.error('Error generating image:', error);
                    });
            }, 2000); // Increased timeout to 2 seconds
        });

        if (centerBlok.getLayers().length > 0) {
            map.fitBounds(estateLayer.getBounds());
        }


    }

    function getMarkerColor(afdeling) {
        const colors = {
            'OA': 'red',
            'OB': 'violet',
            'OC': 'gold',
            'OD': 'blue',
            'OE': 'green',
            'OF': 'grey',
            'OG': 'red',
            'OH': 'gold',
            'OI': 'violet',
            'OJ': 'grey',
            'OK': 'red',
            'OL': 'blue',
            'OM': 'grey',
        };
        return colors[afdeling] || 'grey';
    }

    function getAfdelingColor(afd) {
        const colors = {
            'OA': '#ff1744',
            'OB': '#d500f9',
            'OC': '#ffa000',
            'OD': '#00b0ff',
            // Add other color mappings
        };
        return colors[afd] || '#003B73';
    }
    // Add your createMapImage function and other JavaScript code here
</script>