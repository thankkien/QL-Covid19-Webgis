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

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
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
    <?php include 'navbar.php' ?>

    <div class="row">
        <div class="col">
            <form action="" method="GET" class="row">
                <div class="col">
                    <select name="style" class="custom-select">
                        <option <?php echo (isset($_GET['style']) &&  $_GET['style'] == 0) ? "selected" : " " ?> value="0">polygon</option>
                        <option <?php echo (isset($_GET['style']) &&  $_GET['style'] == 1) ? "selected" : " " ?> value="1">line</option>
                    </select>
                </div>
                <div class="col">
                    <select name="layerview" class="custom-select">
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 0) ? "selected" : " " ?> value="0">Việt Nam</option>
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 1) ? "selected" : " " ?> value="1">Tỉnh/Thành Phố</option>
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 2) ? "selected" : " " ?> value="2">Quận/Huyện</option>
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 3) ? "selected" : " " ?> value="3">Xã/Phường</option>
                    </select>
                </div>
                <div class="col">
                    <button class="btn btn-danger" type="changeaddiew">Chọn</button>
                </div>
            </form>

            <form>
                <div class="form-group">
                    <label for="hoten">Họ và tên</label><br>
                    <input type="text" id="hoten" placeholder="Nhập họ và tên" name="hoten" require><br>
                </div>
                <div class="form-group">
                    <label for="ngaysinh">Năm sinh</label><br>
                    <input type="date" id="ngaysinh" name="ngaysinh" min="1900-01-01" max="2030-12-31" require><br>
                </div>
                <div class="form-group">
                    <label for="cccd">Số căn cước công dân</label><br>
                    <input type="number" id="cccd" name="cccd" placeholder="Nhập số căn cước công dân" min="0" max="999999999999" require><br>
                </div>
                <div class="form-group">
                    <label for="diachi">Địa chỉ chi tiết</label><br>
                    <input type="text" id="diachi" placeholder="Nhập số nhà/ngõ/đường" name="diachi" require><br>
                </div>
                <div class="form-group">
                    <label for="xa">Xã/Phường</label><br>
                    <input type="text" id="xa" name="xa" placeholder="Xã..." readonly require><br>
                </div>
                <div class="form-group">
                    <label for="huyen">Huyện/Quận</label><br>
                    <input type="text" id="huyen" name="huyen" placeholder="Huyện..." readonly require><br>
                </div>
                <div class="form-group">
                    <label for="tinh">Tỉnh/Thành Phố</label><br>
                    <input type="text" id="tinh" name="tinh" placeholder="Tỉnh..." readonly require><br>
                </div>
                <div class="form-group">
                    <label for="lon">Kinh độ</label><br>
                    <input type="text" id="lon" name="lon" placeholder="Kinh độ điểm được chọn" readonly require><br>
                </div>
                <div class="form-group">
                    <label for="lat">Vĩ độ</label><br>
                    <input type="text" id="lat" name="lat" placeholder="Vĩ độ điểm được chọn" readonly require>
                </div>
                <button class="btn btn-danger" type="add" id="add" onclick="insertData()">Thêm bệnh nhân</button>
            </form>
        </div>
        <div class="col-9" >
            <div id="map" class="map"></div>
        </div>
    </div>

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
        var makieu = <?php echo (isset($_GET['style'])) ? $_GET['style'] :  "0"; ?>;
        var malop = <?php echo (isset($_GET['layerview'])) ? $_GET['layerview'] :  "0"; ?>;

        function layKieu(makieu) {
            if (makieu == 1) return "line";
            else return "polygon"
        };

        function layLop(malop) {
            if (malop == 1) return "gadm36_vnm_1";
            else if (malop == 2) return "gadm36_vnm_2";
            else if (malop == 3) return "gadm36_vnm_3";
            else return "gadm36_vnm_0"
        };

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
                    functionname: 'themVaoCSDL',
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

            var port = '8080';
            var workspaces = 'chaythu';
            var layerGADM_VNM = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:' + port + '/geoserver/' + workspaces + '/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: layKieu(makieu),
                        LAYERS: layLop(malop),
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
                //layers: [layerBG],
                //layers: [layerGADM_VNM],
                layers: [layerBG, layerGADM_VNM],
                view: viewMap
            });
            //map.getView().fit(bounds, map.getSize());

            var styleFunction = function(feature) {
                return [new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 7,
                        fill: new ol.style.Fill({
                            color: 'yellow'
                        }),
                        stroke: new ol.style.Stroke({
                            color: "orange",
                            width: 1
                        })
                    }),
                    // text: new ol.style.Text({
                    //     font: '12px Calibri,sans-serif',

                    //     fill: new ol.style.Fill({
                    //         color: '#000'
                    //     }),
                    //     stroke: new ol.style.Stroke({
                    //         color: '#fff',
                    //         width: 2
                    //     }),
                    //     // get the text from the feature - this is ol.Feature
                    //     // and show only under certain resolution
                    //     text: feature.get('name') //'example'//this.get('description')
                    // })
                })]
            };
            var vectorLayer = new ol.layer.Vector({
                //source: vectorSource,
                style: styleFunction
            });
            map.addLayer(vectorLayer);

            function hienThiDiem(lon, lat) {
                var geoJson = '{"type": "Feature","geometry": {"type": "Point","coordinates": [' + lon + ', ' + lat + ']},"properties": {"name": "Dinagat Islands"}}'
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(geoJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857',
                    })
                });
                vectorLayer.setSource(vectorSource);
            }

            function hienThiThongTin(result, coordinate) {
                //alert("result: " + result);
                //alert("coordinate des: " + coordinate);
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

                hienThiDiem(lon, lat);
                document.getElementById("lon").value = lon;
                document.getElementById("lat").value = lat;

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    //dataType: 'json',
                    //data: {functionname: 'reponseGeoToAjax', paPoint: myPoint},
                    data: {
                        functionname: 'layTenVung',
                        paPoint: myPoint,
                        paType: 3
                    },
                    success: function(result, status, erro) {
                        hienThiThongTin(result, evt.coordinate);
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>