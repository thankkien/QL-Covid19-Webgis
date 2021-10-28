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
                <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
            </td>
            <td>
                <!-- <form method="POST" action=""> -->
                <label for="hoten">Họ và tên</label><br>
                <input type="text" id="hoten" name="hoten" require><br>
                <label for="ngaysinh">Năm sinh</label><br>
                <input type="date" id="ngaysinh" name="ngaysinh" min="1900-01-01" max="2030-12-31" require><br>
                <label for="cccd">Số căn cước công dân</label><br>
                <input type="number" id="cccd" name="cccd" min="0" max="999999999999" require><br>
                <label for="diachi">Địa chỉ chi tiết</label><br>
                <input type="text" id="diachi" name="diachi" require><br>

                <label for="xa">Xã/Phường</label><br>
                <input type="text" id="xa" name="xa" readonly require><br>
                <label for="huyen">Huyện/Quận</label><br>
                <input type="text" id="huyen" name="huyen" readonly require><br>
                <label for="tinh">Tỉnh/Thành Phố</label><br>
                <input type="text" id="tinh" name="tinh" readonly require><br>
                <label for="lon">Kinh độ</label><br>
                <input type="text" id="lon" name="lon" readonly require><br>
                <label for="lat">Vĩ độ</label><br>
                <input type="text" id="lat" name="lat" readonly require>

                <div>
                    <button type="submit" id="submit" onclick="insertData()">Thêm</button>
                </div>
                <!-- </form> -->
            </td>
        </tr>
    </table>
    <?php include 'VNM_pgsqlAPI.php' ?>
    <script>
        //$("#document").ready(function () {
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



        function insertData() {
            var gthoten = document.getElementById("hoten").value;
            var gtngaysinh = document.getElementById("ngaysinh").value;
            var gtdiachi = document.getElementById("diachi").value;
            var gtcccd = document.getElementById("cccd").value;
            var gtlon = document.getElementById("lon").value;
            var gtlat = document.getElementById("lat").value;
            var myPoint = 'POINT(' + gtlon + ' ' + gtlat + ')';
            //alert(gtngaysinh);
            $.ajax({
                type: "POST",
                url: "VNM_pgsqlAPI.php",
                //dataType: 'json',
                //data: {functionname: 'reponseGeoToAjax', paPoint: myPoint},
                data: {
                    functionname: 'insertToDB',
                    hoten: gthoten,
                    ngaysinh: gtngaysinh,
                    diachi: gtdiachi,
                    cccd: gtcccd,
                    paPoint: myPoint
                },
                success: function(result, status, erro) {
                    alert("Đã thêm thành công bênh nhân số: " + result);
                },
                error: function(req, status, error) {
                    alert(req + " " + status + " " + error);
                }
            });
        }

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

            function displayObjInfo(result, coordinate) {
                //alert("result: " + result);
                //alert("coordinate des: " + coordinate);
                
                // const text = '{"name":"John", "birth":"1986-12-14", "city":"New York"}';
                const obj = JSON.parse(result);

                document.getElementById("xa").value = obj.xa;
                document.getElementById("huyen").value = obj.huyen;
                document.getElementById("tinh").value = obj.tinh;
                //$("#info").html(result);
            }
            
            map.on('singleclick', function(evt) {
                //alert("coordinate org: " + evt.coordinate);
                //var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                document.getElementById("lon").value = lon;
                document.getElementById("lat").value = lat;

                
                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    //dataType: 'json',
                    //data: {functionname: 'reponseGeoToAjax', paPoint: myPoint},
                    data: {
                        functionname: 'getInfoVNMToAjax',
                        paPoint: myPoint,
                        paType: 3
                    },
                    success: function(result, status, erro) {
                        displayObjInfo(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });
                //alert("myPoint: " + myPoint);
                //*
                //*/
            });
        };
        //});
    </script>
</body>

</html>