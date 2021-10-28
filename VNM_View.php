<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>QL-Covid19 | Quản lý bênh nhân Covid-19</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <!-- <link rel="stylesheet" href="http://localhost:8081/libs/openlayers/css/ol.css" type="text/css" />
        <script src="http://localhost:8081/libs/openlayers/build/ol.js" type="text/javascript"></script> -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
    <!-- <script src="http://localhost:8081/libs/jquery/jquery-3.4.1.min.js" type="text/javascript"></script> -->
    <style>
        .map,
        .righ-panel {
            height: 500px;
            width: 80%;
            float: left;
        }

        .map,
        .righ-panel {
            height: 98vh;
            width: 80vw;
            float: left;
        }

        .map {
            border: 1px solid #000;
        }
    </style>
</head>

<body onload="initialize_map();">
    <table>
        <tr>
            <td>
                <div id="map" class="map"></div>
            </td>
            <td>
                <div id="info"></div>
            </td>
        </tr>
    </table>
    <?php include 'VNM_pgsqlAPI.php' ?>
    <script>
        var format = 'image/png';
        var map;
        var minX = 102.14458465576172;
        var minY = 8.381355285644531;
        var maxX = 109.46917724609375;
        var maxY = 23.3926944732666;
        var cenX = (minX + maxX) / 2;
        var cenY = (minY + maxY) / 2;
        var mapLat = cenY;
        var mapLng = cenX;
        var mapDefaultZoom = 6;
        var kieulop = <?php if (isset($_GET['layerview'])) echo $_GET['layerview'];
                        else echo "0"; ?>;
        var lop = 'gadm36_vnm_0';
        if (kieulop == 1) lop = 'gadm36_vnm_1';
        else if (kieulop == 2) lop = 'gadm36_vnm_2';
        else if (kieulop == 3) lop = 'gadm36_vnm_3';

        function initialize_map() {
            layerBG = new ol.layer.Tile({
                source: new ol.source.OSM({})
            });

            var workspaces = 'chaythu';
            var layerGADM_VNM = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/' + workspaces + '/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: lop,
                    }
                })
            });

            var viewMap = new ol.View({
                center: ol.proj.fromLonLat([mapLng, mapLat]),
                zoom: mapDefaultZoom
                //projection: projection
            });
            map = new ol.Map({
                target: "map",
                //layers: [layerGADM_VNM],
                layers: [layerBG, layerGADM_VNM],
                view: viewMap
            });
            //map.getView().fit(bounds, map.getSize());

            var styles = {
                'MultiPolygon': new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'orange'
                    }),
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 2
                    })
                })
            };
            var styleFunction = function(feature) {
                return styles[feature.getGeometry().getType()];
            };
            var vectorLayer = new ol.layer.Vector({
                //source: vectorSource,
                style: styleFunction
            });
            map.addLayer(vectorLayer);

            function createJsonObj(result) {
                var geojsonObject = '{' +
                    '"type": "FeatureCollection",' +
                    '"crs": {' +
                    '"type": "name",' +
                    '"properties": {' +
                    '"name": "EPSG:4326"' +
                    '}' +
                    '},' +
                    '"features": [{' +
                    '"type": "Feature",' +
                    '"geometry": ' + result +
                    '}]' +
                    '}';
                return geojsonObject;
            }

            // function drawGeoJsonObj(paObjJson) {
            //     var vectorSource = new ol.source.Vector({
            //         features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
            //             dataProjection: 'EPSG:4236',
            //             featureProjection: 'EPSG:3857'
            //         })
            //     });
            //     var vectorLayer = new ol.layer.Vector({
            //         source: vectorSource
            //     });
            //     map.addLayer(vectorLayer);
            // }

            function displayObjInfo(result, coordinate) {
                //alert("result: " + result);
                //alert("coordinate des: " + coordinate);

                
                $("#info").html(result);
            }

            function highLightGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                vectorLayer.setSource(vectorSource);

                // var vectorLayer = new ol.layer.Vector({
                //     source: vectorSource
                // });
                // map.addLayer(vectorLayer);

            }

            function highLightObj(result) {
                // console.log("result: " + result);

                var strObjJson = createJsonObj(result);
                // alert(strObjJson);
                var objJson = JSON.parse(strObjJson);
                // alert(JSON.stringify(objJson));
                //drawGeoJsonObj(objJson);
                highLightGeoJsonObj(objJson);
            }

            map.on('singleclick', function(evt) {
                //alert("coordinate: " + evt.coordinate);
                //var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                // alert("myPoint: " + myPoint);

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    //dataType: 'json',
                    //data: {functionname: 'reponseGeoToAjax', paPoint: myPoint},
                    data: {
                        functionname: 'getInfoVNMToAjax',
                        paPoint: myPoint,
                        paType: kieulop
                    },
                    success: function(result, status, erro) {
                        displayObjInfo(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    // dataType: 'json',
                    // highLightObj(result);
                    data: {
                        functionname: 'getGeoVNMToAjax',
                        paPoint: myPoint,
                        paType: kieulop
                    },
                    success: function(result, status, erro) {
                        highLightObj(result);
                    },
                    error: function(req, status, error) {
                        alert("lỗI:" + req + " " + status + " " + error);
                    }
                });
            });
        };
    </script>
</body>

</html>