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
</head>

<body>
    <?php include 'navbar.php' ?>

    <div class="container-fluid">
        <table class="table table-hover table-light table-bordered">
            <thead>
                <tr>
                    <th scope="col">Mã Bệnh Nhân</th>
                    <th scope="col">Họ Và Tên</th>
                    <th scope="col">Ngày Sinh</th>
                    <th scope="col">Địa Chỉ</th>
                    <th scope="col">Tình Trạng</th>
                    <th colspan="2">Hoạt Động</th>
                </tr>
            </thead>
            <tbody id="hienthi">


            </tbody>
        </table>
    </div>
    <script>
        function hienThiDanhSach(result) {
            $("#hienthi").append(result);
        };
        $(document).ready(function() {
            // alert("oke");
            $.ajax({
                type: "POST",
                url: "VNM_pgsqlAPI.php",

                data: {
                    functionname: 'layDsBenhNhan'
                },
                success: function(result, status, erro) {
                    hienThiDanhSach(result);
                },
                error: function(req, status, error) {
                    alert(req + " " + status + " " + error);
                }
            });
        })
        

        function xoaBenhNhan(id) {
                $.ajax({
                    type: "POST",
                    url: "VNM_pgsqlAPI.php",

                    data: {
                        functionname: 'xoaBenhNhan',
                        maBenhNhan: id
                    },
                    success: function(result, status, erro) {
                        alert(result);
                        location.reload();
                    },
                    error: function(req, status, error) {
                        alert(req + " " + status + " " + error);
                    }
                });
            }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>

</html>