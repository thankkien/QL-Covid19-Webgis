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
            height: 85vh;
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
                    <button class="btn btn-danger" type="submit">Chọn</button>
                </div>
            </form>
            <div>
                <select id="chonBenhNhan" class="custom-select">
                    <option selected value="0">Đã khỏi</option>
                    <option value="1">Bệnh nhân</option>
                    <option value="2">Đã tử vong</option>
                </select>
            </div>
            <form>
                <div class="form-group">
                    <label for="diaDiem">Địa điểm:</label><br>
                    <input type="text" id="diaDiem" name="diaDiem" readonly require><br>
                </div>
                <div class="form-group">
                    <label for="soLuongBenhNhan">Số bệnh nhân đang điều trị</label><br>
                    <input type="text" id="soLuongBenhNhan" name="soLuongBenhNhan" readonly require><br>
                </div>
                <div class="form-group">
                    <label for="soLuongDaKhoi">Số bệnh nhân đã khỏi bệnh</label><br>
                    <input type="text" id="soLuongDaKhoi" name="soLuongDaKhoi" readonly require><br>
                </div>
                <div class="form-group">
                    <label for="soLuongTuVong">Số bệnh nhân đã tử vong</label><br>
                    <input type="text" id="soLuongTuVong" name="soLuongTuVong" readonly require><br>
                </div>
            </form>
        </div>
        <div class="col-9">
            <div id="map" class="map"></div>
        </div>
    </div>

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
            //console.log(makieu + ":" + layKieu(makieu) + "; " + malop + layLop(malop));
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

            var stylePolygon = function(feature) {
                return [new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'orange'
                    }),
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 2
                    }),
                    text: new ol.style.Text({
                        font: '12px Calibri,sans-serif',
                        fill: new ol.style.Fill({
                            color: '#000'
                        }),
                        stroke: new ol.style.Stroke({
                            color: '#fff',
                            width: 2
                        }),
                        // get the text from the feature - `this` is ol.Feature
                        // and show only under certain resolution
                        text: feature.get('name') //'example'//this.get('description')
                    })
                })]
            };
            var vectorPolygon = new ol.layer.Vector({
                //source: vectorSource,
                style: stylePolygon
            });
            map.addLayer(vectorPolygon);

            var stylePoint = function(feature) {
                return [new ol.style.Style({
                    image: new ol.style.Circle({
                        radius: 3,
                        fill: new ol.style.Fill({
                            color: 'red'
                        }),
                        stroke: new ol.style.Stroke({
                            color: "red",
                            width: 1
                        })
                    }),
                })]
            };
            var vectorPoint = new ol.layer.Vector({
                style: stylePoint,
            });
            map.addLayer(vectorPoint);

            function hienThiTenVung(result, coordinate) {
                //alert("result: " + result);
                //alert("coordinate des: " + coordinate);
                if (result != null) {
                    const obj = JSON.parse(result);
                    if (malop == 0) document.getElementById("diaDiem").value = obj.nuoc;
                    else if (malop == 1) document.getElementById("diaDiem").value = obj.tinh + ", " + obj.nuoc;
                    else if (malop == 2) document.getElementById("diaDiem").value = obj.huyen + ", " + obj.tinh + ", " + obj.nuoc;
                    else if (malop == 3) document.getElementById("diaDiem").value = obj.xa + ", " + obj.huyen + ", " + obj.tinh + ", " + obj.nuoc;
                } else
                    document.getElementById("diaDiem").value = "";
            }

            function hienThiSoBenhNhan(result, coordinate) {
                // console.log(result);
                const obj = JSON.parse(result);
                document.getElementById("soLuongBenhNhan").value = obj.soLuong;
            }

            function hienThiSoDaKhoi(result, coordinate) {
                // console.log(result);
                const obj = JSON.parse(result);
                document.getElementById("soLuongDaKhoi").value = obj.soLuong;
            }

            function hienThiSoTuVong(result, coordinate) {
                // console.log(result);
                const obj = JSON.parse(result);
                document.getElementById("soLuongTuVong").value = obj.soLuong;
            }

            function hienThiVung(result) {
                // console.log("result: " + result);\
                if (result != null) {
                    var strObjJson = '{"type": "FeatureCollection", "features": [{"type": "Feature", "properties":' + result + ']}';
                    var objJson = JSON.parse(strObjJson);
                    var vectorSource = new ol.source.Vector({
                        features: (new ol.format.GeoJSON()).readFeatures(objJson, {
                            dataProjection: 'EPSG:4326',
                            featureProjection: 'EPSG:3857',
                        })
                    });
                    vectorPolygon.setSource(vectorSource);
                } else vectorPolygon.setSource(vectorSource);
            }

            function hienThiViTriBenhNhan(result, coordinate) {
                if (result != null) {
                    var geoJson = '{"type": "FeatureCollection", "features": [' + result + ']}';
                    var vectorSource = new ol.source.Vector({
                        features: (new ol.format.GeoJSON()).readFeatures(geoJson, {
                            dataProjection: 'EPSG:4326',
                            featureProjection: 'EPSG:3857',
                        })
                    });
                    vectorPoint.setSource(vectorSource);
                } else vectorPoint.setSource(vectorSource);
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
                    data: {
                        functionname: 'layTenVung',
                        paPoint: myPoint,
                        paType: malop
                    },
                    success: function(result, status, erro) {
                        hienThiTenVung(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    data: {
                        functionname: 'laySoLuongBenhNhan',
                        paPoint: myPoint,
                        paType: malop,
                        tinhTrang: 0
                    },
                    success: function(result, status, erro) {
                        hienThiSoDaKhoi(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    data: {
                        functionname: 'laySoLuongBenhNhan',
                        paPoint: myPoint,
                        paType: malop,
                        tinhTrang: 1
                    },
                    success: function(result, status, erro) {
                        hienThiSoBenhNhan(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    data: {
                        functionname: 'laySoLuongBenhNhan',
                        paPoint: myPoint,
                        paType: malop,
                        tinhTrang: 2
                    },
                    success: function(result, status, erro) {
                        hienThiSoTuVong(result, evt.coordinate);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });

                loaiBenhNhan = document.getElementById("chonBenhNhan").value;
                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    data: {
                        functionname: 'layViTriBenhNhan',
                        paPoint: myPoint,
                        paType: malop,
                        tinhTrang: loaiBenhNhan
                    },
                    success: function(result, status, erro) {
                        hienThiViTriBenhNhan(result);
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });

                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",
                    data: {
                        functionname: 'layVung',
                        paPoint: myPoint,
                        paType: malop
                    },
                    success: function(result, status, erro) {
                        hienThiVung(result);
                    },
                    error: function(req, status, error) {
                        alert("lỗI:" + req + " " + status + " " + error);
                    }
                });
            });
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>