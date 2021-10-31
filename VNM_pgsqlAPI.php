<?php
if (isset($_POST['functionname'])) {
    $paPDO = initDB();
    $functionname = $_POST['functionname'];
    $paSRID = '4326';
    $paPoint = isset($_POST['paPoint']) ? $_POST['paPoint'] : '';
    $paType = isset($_POST['paType']) ? $_POST['paType'] : '';
    $mabenhnhan = isset($_POST['mabenhnhan']) ? $_POST['mabenhnhan'] : '';
    $hoten = isset($_POST['hoten']) ? $_POST['hoten'] : '';
    $ngaysinh = isset($_POST['ngaysinh']) ? $_POST['ngaysinh'] : '';
    $diachi = isset($_POST['diachi']) ? $_POST['diachi'] : '';
    $cccd = isset($_POST['cccd']) ? $_POST['cccd'] : '';
    $tinhtrang = isset($_POST['tinhtrang']) ? $_POST['tinhtrang'] : '';
    $aResult = "null";
    if ($functionname == 'layVung') {
        $aResult = layVung($paPDO, $paSRID, $paPoint, $paType);
    } else if ($functionname == 'kiemTraTrongVietNam') {
        $aResult = kiemTraTrongVietNam($paPDO, $paSRID, $paPoint);
    } else if ($functionname == 'layTenVung') {
        $aResult = layTenVung($paPDO, $paSRID, $paPoint, $paType);
    } else if ($functionname == 'laySoLuongBenhNhan') {
        $aResult = laySoLuongBenhNhan($paPDO, $paSRID, $paPoint, $paType);
    } else if ($functionname == 'layViTriBenhNhan') {
        $aResult = layViTriBenhNhan($paPDO, $paSRID, $paPoint, $paType, $tinhtrang);
    } else if ($functionname == 'layDsBenhNhan') {
        $aResult = layDsBenhNhan($paPDO);
    } else if ($functionname == 'layTTBenhNhan') {
        $aResult = layTTBenhNhan($paPDO, $mabenhnhan);
    } else if ($functionname == 'themVaoCSDL') {
        $aResult = themVaoCSDL($paPDO, $paSRID, $hoten, $ngaysinh, $diachi, $cccd, $paPoint);
    } else if ($functionname == 'suaBenhNhan') {
        $aResult = suaBenhNhan($paPDO, $paSRID, $paPoint, $hoten, $ngaysinh, $diachi, $cccd, $tinhtrang, $mabenhnhan);
    } else if ($functionname == 'xoaBenhNhan') {
        $aResult = xoaBenhNhan($paPDO, $mabenhnhan);
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
function closeDB($paPDO)
{
    // Ngắt kết nối
    $paPDO = null;
}
function layVung($paPDO, $paSRID, $paPoint, $paType)
{
    //echo $paPoint;
    //echo "<br>";
    $paPoint = str_replace(',', ' ', $paPoint);
    //echo $paPoint;
    //echo "<br>";
    //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"VNM_adm1\" where ST_Within('SRID=4326;POINT(12 5)'::geometry,geom)";
    if ($paType == 0)
        $mySQLStr = "SELECT name_0 AS tenvung, ST_AsGeoJson(geom) AS geo from \"gadm36_vnm_0\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    else if ($paType == 1)
        $mySQLStr = "SELECT name_1 AS tenvung, ST_AsGeoJson(geom) AS geo from \"gadm36_vnm_1\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    else if ($paType == 2)
        $mySQLStr = "SELECT name_2 AS tenvung, ST_AsGeoJson(geom) AS geo from \"gadm36_vnm_2\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    else if ($paType == 3)
        $mySQLStr = "SELECT name_3 AS tenvung, ST_AsGeoJson(geom) AS geo from \"gadm36_vnm_3\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)";
    //echo $mySQLStr;
    //echo "<br><br>";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return '{"name":"' . $item["tenvung"] . '"}, "geometry":' . $item["geo"] . '}';
        }
    } else
        return 'null';
}

function laySoLuongBenhNhan($paPDO, $paSRID, $paPoint, $paType)
{
    //echo $paPoint;
    $paPoint = str_replace(',', ' ', $paPoint);
    if ($paType == 0)
        $mySQLStr = "SELECT benhnhan.tinhtrang, Count(benhnhan.id) as soluong " .
            "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_0\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung " .
            "WHERE ST_Intersects(benhnhan.geom::geometry,vung.geom) " .
            "GROUP BY benhnhan.tinhtrang;";
    else if ($paType == 1)
        $mySQLStr = "SELECT benhnhan.tinhtrang, Count(benhnhan.id) as soluong " .
            "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_1\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung " .
            "WHERE ST_Intersects(benhnhan.geom::geometry,vung.geom) " .
            "GROUP BY benhnhan.tinhtrang;";
    else if ($paType == 2)
        $mySQLStr = "SELECT benhnhan.tinhtrang, Count(benhnhan.id) as soluong " .
            "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_2\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung " .
            "WHERE ST_Intersects(benhnhan.geom::geometry,vung.geom) " .
            "GROUP BY benhnhan.tinhtrang;";
    else if ($paType == 3)
        $mySQLStr = "SELECT benhnhan.tinhtrang, Count(benhnhan.id) as soluong " .
            "FROM \"benhnhan\", (SELECT geom FROM \"gadm36_vnm_3\" WHERE ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry, geom)) as vung " .
            "WHERE ST_Intersects( benhnhan.geom::geometry,vung.geom) " .
            "GROUP BY benhnhan.tinhtrang;";
    //echo $mySQLStr;
    $result = query($paPDO, $mySQLStr);
    $row =  count($result);

    if ($result != null) {
        $textJson = '{';
        // Lặp kết quả
        for ($i = 0; $i < $row; $i++) {
            $item = $result[$i];
            //echo $item['tinhtrang'] . " " . $item['soluong'];
            $textJson = $textJson . '"' . $item['tinhtrang'] . '":"' . $item['soluong'] . '"';
            if ($i < $row - 1) $textJson = $textJson . ',';
        }
        return $textJson . '}';
    } else
        return 'null';
    //return $mySQLStr;
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
        return 'null';
}

function layViTriBenhNhan($paPDO, $paSRID, $paPoint, $paType, $tinhtrang)
{
    //echo $paPoint;
    $paPoint = str_replace(',', ' ', $paPoint);
    //echo $paPoint;
    if ($paType == 0)
        $mySQLStr = "SELECT benhnhan.id as id,  ST_AsGeoJson(benhnhan.geom) as vitribenhnhan from benhnhan,  (SELECT gadm36_vnm_0.geom  from \"gadm36_vnm_0\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)) as vung
        where ST_Intersects( benhnhan.geom::geometry,vung.geom) and benhnhan.tinhtrang = " . $tinhtrang;
    else if ($paType == 1)
        $mySQLStr = "SELECT benhnhan.id as id, ST_AsGeoJson(benhnhan.geom) as vitribenhnhan from benhnhan,  (SELECT gadm36_vnm_1.geom  from \"gadm36_vnm_1\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)) as vung
    where ST_Intersects( benhnhan.geom::geometry,vung.geom) and benhnhan.tinhtrang = " . $tinhtrang;
    else if ($paType == 2)
        $mySQLStr = "SELECT benhnhan.id as id,  ST_AsGeoJson(benhnhan.geom) as vitribenhnhan from benhnhan,  (SELECT gadm36_vnm_2.geom  from \"gadm36_vnm_2\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)) as vung
    where ST_Intersects( benhnhan.geom::geometry,vung.geom) and benhnhan.tinhtrang = " . $tinhtrang;
    else if ($paType == 3)
        $mySQLStr = "SELECT benhnhan.id as id,  ST_AsGeoJson(benhnhan.geom) as vitribenhnhan from benhnhan,  (SELECT gadm36_vnm_3.geom  from \"gadm36_vnm_3\" where ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom)) as vung
    where ST_Intersects( benhnhan.geom::geometry,vung.geom) and benhnhan.tinhtrang = " . $tinhtrang;
    //echo $mySQLStr;
    $result = query($paPDO, $mySQLStr);
    $row =  count($result);

    if ($result != null) {
        $kq = "";
        for ($i = 0; $i < $row; $i++) {
            $item = $result[$i];
            $kq = $kq . '{"type": "Feature", "properties": { "id": "' . $item['id'] . '" }, "geometry": ' . $item["vitribenhnhan"] . '}';
            if ($i < $row - 1) $kq = $kq . ',';
        }
        return $kq;
    } else
        return 'null';
}

function kiemTraTrongVietNam($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $mySQLStr = "SELECT ST_Within('SRID=" . $paSRID . ";" . $paPoint . "'::geometry,geom) AS checker from \"gadm36_vnm_0\"";
    $result = query($paPDO, $mySQLStr);
    // echo $mySQLStr;
    //echo $result[0]["checker"];
    if ($result != null) {
        $item = $result[0];
        return $item["checker"];
    } else
        return 'null';
}

function layDsBenhNhan($paPDO)
{
    $mySQLStr =  "SELECT * from public.benhnhan";
    $result = query($paPDO, $mySQLStr);
    // var_dump($result);
    $row =  count($result);
    $res = '';
    if ($result != null) {
        for ($i = 0; $i < $row; $i++) {
            $item = $result[$i];
            $res = $res .
                '<tr>
            <th scope="row">' . $item["id"] . '</th>
             <th scope="row">' . $item["hoten"] . '</th>
             <td>' . $item["ngaysinh"] . '</td>
             <td>' . $item["diachi"] . '</td>
             <td>';
            if ($item["tinhtrang"] == 1) $res = $res . "Bị Bệnh";
            else if ($item["tinhtrang"] == 0) $res = $res . "Khỏi Bệnh";
            else if ($item["tinhtrang"] == 2) $res = $res . "Tử Vong";
            $res = $res . '</td>
             <td><a  href="VNM_Edit.php?mabenhnhan=' . $item['id'] . '"><i class="fas fa-edit"></i> Cập Nhật</a></td>
             <td><a style="color:red"  href="javascript:xoaBenhNhan(' . $item['id'] . ')"><i class="fas fa-trash"></i> Xoá</a></td>
             </tr>';
        }
        return $res;
    }
    return "null";
}

function layTTBenhNhan($paPDO, $mabenhnhan)
{
    $mySQLStr =  "SELECT id, hoten, ngaysinh, diachi, cccd, tinhtrang, ST_AsGeoJSON(geom) AS vitribenhnhan FROM benhnhan WHERE id = " . $mabenhnhan;
    $result = query($paPDO, $mySQLStr);
    // var_dump($result);
    if ($result != null) {
        $item = $result[0];
        return '{"id":"' . $item["id"] .
            '", "hoten":"' . $item["hoten"] .
            '", "ngaysinh":"' . $item["ngaysinh"] .
            '", "diachi":"' . $item["diachi"] .
            '", "cccd":"' . $item["cccd"] .
            '", "tinhtrang":"' . $item["tinhtrang"] .
            '", "vitribenhnhan":' . $item["vitribenhnhan"] . '}';
    }
    return 'null';
}

function themVaoCSDL($paPDO, $paSRID, $hoten, $ngaysinh, $diachi, $cccd, $paPoint)
{
    $mySQLStr = "INSERT INTO public.benhnhan(hoten, ngaysinh, diachi, cccd, tinhtrang, geom) " .
        "VALUES ('" . $hoten . "', '" . $ngaysinh . "', '" . $diachi . "', '" . $cccd . "', 1, 'SRID=" . $paSRID . ";" . $paPoint . "'::geometry);";
    //echo $mySQLStr;
    try {
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = $paPDO->prepare($mySQLStr);
        $result->execute();
        $inserted_id = $paPDO->lastInsertId();
        return "Đã thêm thành công bênh nhân số: " . $inserted_id;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return 'null';
    }
}

function suaBenhNhan($paPDO, $paSRID, $paPoint, $hoten, $ngaysinh, $diachi, $cccd, $tinhtrang, $mabenhnhan)
{
    $mySQLStr = "UPDATE public.benhnhan " .
        "SET hoten='" . $hoten .
        "', ngaysinh='" . $ngaysinh .
        "', diachi='" . $diachi .
        "', cccd='" . $cccd .
        "', tinhtrang='" . $tinhtrang .
        "', geom='SRID=" . $paSRID . ";" . $paPoint . "'::geometry " .
        "WHERE id='" . $mabenhnhan . "';";
    //echo $mySQLStr;
    try {
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = $paPDO->prepare($mySQLStr);
        $result->execute();
        return "Đã sửa thành công bệnh nhân " . $mabenhnhan;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return 'null';
    }
}

function xoaBenhNhan($paPDO, $mabenhnhan)
{
    $mySQLStr = 'DELETE FROM benhnhan WHERE benhnhan.id = ' . $mabenhnhan . ';';
    try {
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = $paPDO->prepare($mySQLStr);
        $result->execute();
        return "Đã xóa thành công bệnh nhân " . $mabenhnhan;
    } catch (PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return 'null';
    }
}
