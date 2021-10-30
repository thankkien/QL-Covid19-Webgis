<?php
if (isset($_POST['functionname'])) {
    $paPDO = initDB();
    $paSRID = '4326';
    $functionname = $_POST['functionname'];
    $paPoint = isset($_POST['paPoint']) ? $_POST['paPoint'] : '';

    $aResult = "null";

    if ($functionname == 'getGeoVNMToAjax') {
        $paType = isset($_POST['paType']) ? $_POST['paType'] : '';

        $aResult = getGeoVNMToAjax($paPDO, $paSRID, $paPoint, $paType);
    } else if ($functionname == 'layTenVung') {
        $paType = isset($_POST['paType']) ? $_POST['paType'] : '';

        $aResult = layTenVung($paPDO, $paSRID, $paPoint, $paType);
    } else if ($functionname == 'laySoLuongBenhNhan') {
        $paType = isset($_POST['paType']) ? $_POST['paType'] : '';

        $aResult = laySoLuongBenhNhan($paPDO, $paSRID, $paPoint, $paType);
    } else if ($functionname == 'themVaoCSDL') {
        $hoten = isset($_POST['hoten']) ? $_POST['hoten'] : '';
        $ngaysinh = isset($_POST['ngaysinh']) ? $_POST['ngaysinh'] : '';
        $diachi = isset($_POST['diachi']) ? $_POST['diachi'] : '';
        $cccd = isset($_POST['cccd']) ? $_POST['cccd'] : '';
        //$tinhtrang = isset($_POST['tinhtrang']) ? $_POST['tinhtrang'] : '';

        $aResult = themVaoCSDL($paPDO, $paSRID, $hoten, $ngaysinh, $diachi, $cccd, $paPoint);
    }

    echo $aResult;

    closeDB($paPDO);
}

function initDB()
{
    // Kết nối CSDL
    $dbname = 'th3_lan2';
    $port = '5432';
    $username = 'postgres';
    $password = '022235';
    $paPDO = new PDO('pgsql:host=localhost; dbname=' . $dbname . '; port=' . $port, $username, $password);
    return $paPDO;
}
function query($paPDO, $paSQLStr)
{
    try {
        // Khai báo exception
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sử đụng Prepare 
        $stmt = $paPDO->prepare($paSQLStr);
        // Thực thi câu truy vấn
        $stmt->execute();

        // Khai báo fetch kiểu mảng kết hợp
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        // Lấy danh sách kết quả
        $paResult = $stmt->fetchAll();
        return $paResult;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return null;
    }
}
function insert($paPDO, $paSQLStr)
{
    try {
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = $paPDO->prepare($paSQLStr);
        $result->execute();

        $inserted_id = $paPDO->lastInsertId();
        return $inserted_id;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return null;
    }
}
function closeDB($paPDO)
{
    // Ngắt kết nối
    $paPDO = null;
}
function getGeoVNMToAjax($paPDO, $paSRID, $paPoint, $paType)
{
    //echo $paPoint;
    //echo "<br>";
    $paPoint = str_replace(',', ' ', $paPoint);
    //echo $paPoint;
    //echo "<br>";
    //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"VNM_adm1\" where ST_Within('SRID=4326;POINT(12 5)'::geometry,geom)";
    if ($paType == 0)
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_0\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    else if ($paType == 1)
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    else if ($paType == 2)
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_2\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    else if ($paType == 3)
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_3\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    //echo $mySQLStr;
    //echo "<br><br>";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}

function laySoLuongBenhNhan($paPDO, $paSRID, $paPoint, $paType)
{
    //echo $paPoint;
    $paPoint = str_replace(',', ' ', $paPoint);
    if ($paType == 0)
        $mySQLStr = "SELECT Count(benhnhan.id) as soluong ".
                    "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_0\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung ".
                    "WHERE benhnhan.tinhtrang = 1 AND ".
                        "ST_Within(benhnhan.geom, vung.geom);";
    else if ($paType == 1)
        $mySQLStr = "SELECT Count(benhnhan.id) as soluong ".
                    "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_1\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung ".
                    "WHERE benhnhan.tinhtrang = 1 AND ".
                        "ST_Within(benhnhan.geom, vung.geom);";
    else if ($paType == 2)
        $mySQLStr = "SELECT Count(benhnhan.id) as soluong ".
                    "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_2\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung ".
                    "WHERE benhnhan.tinhtrang = 1 AND ".
                        "ST_Within(benhnhan.geom, vung.geom);";
    else if ($paType == 3)
        $mySQLStr = "SELECT Count(benhnhan.id) as soluong ".
                    "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_3\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung ".
                    "WHERE benhnhan.tinhtrang = 1 AND ".
                        "ST_Within(benhnhan.geom, vung.geom);";
    // echo $mySQLStr;
    $result = query($paPDO, $mySQLStr);
    // var_dump($result);
    if ($result != null)
    {
        $textJson='';
        // Lặp kết quả
        foreach ($result as $item){
            $textJson = '{"soLuong":"' . $item['soluong'] . '"}';
            return $textJson;
            break;
        }
    }
    else
        return "null";
    // return $mySQLStr;
}

function layTenVung($paPDO, $paSRID, $paPoint, $paType)
{
    //echo $paPoint;
    $paPoint = str_replace(',', ' ', $paPoint);
    if ($paType == 0)
        $mySQLStr = "SELECT name_0 from \"gadm36_vnm_0\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)";
    else if ($paType == 1)
        $mySQLStr = "SELECT name_0, name_1 from \"gadm36_vnm_1\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)";
    else if ($paType == 2)
        $mySQLStr = "SELECT name_0, name_1, name_2 from \"gadm36_vnm_2\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)";
    else if ($paType == 3)
        $mySQLStr = "SELECT name_0, name_1, name_2, name_3 from \"gadm36_vnm_3\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)";
    //echo $mySQLStr;
    $result = query($paPDO, $mySQLStr);
    // var_dump($result);
    if ($result != null) {
        $textJson = '';
        // Lặp kết quả
        foreach ($result as $item) {
            if ($paType == 0) $textJson = '{"nuoc":"' . $item['name_0'] . '"}';
            if ($paType == 1) $textJson = '{"tinh":"' . $item['name_1'] . '", "nuoc":"' . $item['name_0'] . '"}';
            if ($paType == 2) $textJson = '{"huyen":"' . $item['name_2'] . '", "tinh":"' . $item['name_1'] . '", "nuoc":"' . $item['name_0'] . '"}';
            if ($paType == 3) $textJson = '{"xa":"' . $item['name_3'] . '", "huyen":"' . $item['name_2'] . '", "tinh":"' . $item['name_1'] . '", "nuoc":"' . $item['name_0'] . '"}';
            return $textJson;
            break;
        }
    } else
        return "null";
}

function themVaoCSDL($paPDO, $paSRID, $hoten, $ngaysinh, $diachi, $cccd, $paPoint)
{
    //echo $paPoint;
    $mySQLStr = "INSERT INTO public.benhnhan(
            hoten, ngaysinh, diachi, cccd, tinhtrang, geom)
            VALUES ('" . $hoten . "', '" . $ngaysinh . "', '" . $diachi . "', '" . $cccd . "', 1, 'SRID=" . $paSRID . ";" . $paPoint . "'::geometry);";
    //echo $mySQLStr;
    $result = insert($paPDO, $mySQLStr);
    // var_dump($result);
    if ($result != null) {
        return $result;
    } else
        return "null";
}
