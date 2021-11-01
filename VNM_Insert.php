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
        .map {
            border: 1px solid #000;
        }
    </style>
</head>

<body onload="initialize_map();">
    <?php include 'navbar.php' ?>
    <div class="row my-2">
        <div class="col-4 px-4">
            <h3 class="text-center">Chọn Bản Đồ </h3>
            <form action="" method="GET" class="my-2">
                <div class="input-group">
                    <select name="style" class="custom-select ">
                        <option <?php echo (isset($_GET['style']) &&  $_GET['style'] == 0) ? "selected" : " " ?> value="0">polygon</option>
                        <option <?php echo (isset($_GET['style']) &&  $_GET['style'] == 1) ? "selected" : " " ?> value="1">line</option>
                    </select>

                    <select name="layerview" class="custom-select ">
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 0) ? "selected" : " " ?> value="0">Việt Nam</option>
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 1) ? "selected" : " " ?> value="1">Tỉnh/Thành Phố</option>
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 2) ? "selected" : " " ?> value="2">Quận/Huyện</option>
                        <option <?php echo (isset($_GET['layerview']) &&  $_GET['layerview'] == 3) ? "selected" : " " ?> value="3">Xã/Phường</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-danger input-group-append" type="submit">Chọn</button>
                    </div>
                </div>
            </form>
            <h3 class="text-center">Thông Tin Người Bệnh </h3>
            <form class="my-2">
                <div class="row">
                    <div class="form-group col">
                        <label for="hoten">Họ và tên</label><br>
                        <input type="text" class="form-control" id="hoten" placeholder="Nhập họ và tên" name="hoten"><br>
                    </div>
                    <div class="form-group col">
                        <label for="ngaysinh">Ngày sinh</label><br>
                        <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" min="1900-01-01" max="2030-12-31"><br>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="cccd">Số căn cước công dân</label><br>
                        <input type="number" class="form-control" id="cccd" name="cccd" placeholder="Nhập số căn cước công dân" min="0" max="999999999999" maxlength="12"><br>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="diachi">Số nhà/ngõ/đường..</label><br>
                        <input type="text" class="form-control" id="diachi" placeholder="Nhập số nhà/ngõ/đường" name="diachi"><br>
                    </div>
                    <div class="form-group col">
                        <label for="xa">Xã/Phường</label><br>
                        <input type="text" class="form-control" id="xa" name="xa" placeholder="Xã..." readonly><br>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="huyen">Huyện/Quận</label><br>
                        <input type="text" class="form-control" id="huyen" name="huyen" placeholder="Huyện..." readonly><br>
                    </div>
                    <div class="form-group col">
                        <label for="tinh">Tỉnh/Thành Phố</label><br>
                        <input type="text" class="form-control" id="tinh" name="tinh" placeholder="Tỉnh..." readonly><br>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col">
                        <label for="lon">Kinh độ</label><br>
                        <input type="text" class="form-control" id="lon" name="lon" placeholder="Kinh độ điểm được chọn" readonly><br>
                    </div>
                    <div class="form-group col">
                        <label for="lat">Vĩ độ</label><br>
                        <input type="text" class="form-control" id="lat" name="lat" placeholder="Vĩ độ điểm được chọn" readonly>
                    </div>
                </div>
            </form>
            <button class="btn btn-danger" style="float:right" type="add" id="add" onclick="insertData()">Thêm bệnh nhân</button>
        </div>
        <div class="col-8">
            <div id="map" class="map"></div>
        </div>
    </div>
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
            if (gthoten != "" && gtngaysinh != "" && gtdiachi != "" && gtcccd != "" && gtlon != "" && gtlat != "") {
                var myPoint = 'POINT(' + gtlon + ' ' + gtlat + ')';
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
                        //console.log(result);
                        alert(result);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });
            } else alert("Hãy nhập đủ các trường thông tin");
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

            var myPoint;
            var geoJsonPoint;
            function hienThiDiem(checker) {
                if (checker == "1") {
                    var geoJson = '{"type": "Feature","geometry": ' + geoJsonPoint + '}';
                    var vectorSource = new ol.source.Vector({
                        features: (new ol.format.GeoJSON()).readFeatures(geoJson, {
                            dataProjection: 'EPSG:4326',
                            featureProjection: 'EPSG:3857',
                        })
                    });
                    vectorLayer.setSource(vectorSource);
                    objJson = JSON.parse(geoJsonPoint);
                    document.getElementById("lon").value = objJson.coordinates[0];
                    document.getElementById("lat").value = objJson.coordinates[1];
                } else {
                    vectorLayer.setSource(vectorSource);
                    document.getElementById("lon").value = '';
                    document.getElementById("lat").value = '';
                }
            }

            function hienThiTTDiaDiem(checker) {
                if (checker == 1) {
                    $.ajax({
                        type: "POST",
                        url: "VNM_pgsqlAPI.php",
                        data: {
                            functionname: 'layTenVung',
                            paPoint: myPoint,
                            paType: 3
                        },
                        success: function(result, status, erro) {
                            if (result != "null") {
                                const obj = JSON.parse(result);
                                document.getElementById("xa").value = obj.xa;
                                document.getElementById("huyen").value = obj.huyen;
                                document.getElementById("tinh").value = obj.tinh;
                            }
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                } else {
                    document.getElementById("xa").value = "";
                    document.getElementById("huyen").value = "";
                    document.getElementById("tinh").value = "";
                }
            }

            function hienThiThongTin() {
                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    data: {
                        functionname: 'kiemTraTrongVietNam',
                        paPoint: myPoint
                    },
                    success: function(result, status, erro) {
                        if (result != "null") {
                            hienThiTTDiaDiem(result, myPoint);
                            hienThiDiem(result, geoJsonPoint);
                        }
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });
            }

            map.on('singleclick', function(evt) {
                //alert("coordinate org: " + evt.coordinate);
                //var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                myPoint = 'POINT(' + lon + ' ' + lat + ')';
                geoJsonPoint = '{"type":"Point", "coordinates":[' + lon + ',' + lat + ']}';
                hienThiThongTin(lon, lat)
            });
        };
        //});
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>