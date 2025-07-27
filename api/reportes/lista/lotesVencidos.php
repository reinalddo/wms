<?php
include '../../../config.php';
include '../../../app/load.php';
$app = new \Slim\Slim();

error_reporting(0);

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['start'];
    $limit = $_GET['length']; 
    $search = $_GET['search']['value'];
    $almacen = $_GET['almacen'];
    $sqlAlmacen = '' ;

    if(!empty($almacen)){
        $sqlAlmacen = " AND c_almacenp.id='{$almacen}' ";
    }
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sqlCount = "SELECT COUNT(*) AS total FROM c_lotes l LEFT JOIN c_articulo a ON a.cve_articulo = l.cve_articulo LEFT JOIN V_ExistenciaGralProduccion v ON l.cve_articulo = v.cve_articulo  AND l.LOTE = v.cve_lote AND v.tipo COLLATE utf8mb4_general_ci = 'ubicacion' WHERE l.Activo = '1 {$sqlAlmacen}' AND STR_TO_DATE(CADUCIDAD,'%d-%m-%Y') < NOW() AND v.Existencia > 0";

    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }  
*/
    $sql = "SELECT 
                c_articulo.cve_articulo AS cve_articulo,
                c_articulo.des_articulo AS articulo,
                c_lotes.LOTE AS lote,
                DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y') AS caducidad,
                c_ubicacion.CodigoCSD AS ubicacion,
                vp.Existencia AS existencia,
                (SELECT IFNULL(DATE_FORMAT(fecha_fin, '%d-%m-%Y'), '--') FROM td_entalmacen WHERE cve_articulo = c_lotes.cve_articulo ORDER BY id DESC LIMIT 1) AS fecha_ingreso,
                p.Nombre AS Proveedor
            FROM V_ExistenciaGral vp
            LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND c_lotes.cve_articulo = vp.cve_articulo
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
            LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
            LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
            LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vp.ID_Proveedor
            WHERE DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') < DATE_FORMAT(CURDATE(), '%d-%m-%Y') 
            AND DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') != DATE_FORMAT('00-00-0000', '%d-%m-%Y')
            AND vp.tipo = 'ubicacion'
            AND Existencia > 0
            AND c_ubicacion.CodigoCSD != ''
            {$sqlAlmacen}
            ORDER BY vp.cve_articulo, vp.cve_lote
            ";
            //." LIMIT $page, $limit; ";

    //$res = "";
    $res = mysqli_query($conn, $sql);
    //if (!($res = mysqli_query($conn, $sql))) {
    //    echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    //}

    //$count = mysqli_num_rows($res);

    //$sql .= " LIMIT $page, $limit; ";
    //if (!($res = mysqli_query($conn, $sql))) {
    //    echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    //}


    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        //$row = array_map('utf8_encode', $row);
        $data[$i] = $row;
        $i++;
    } 

    //header('Content-type: application/json');
    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $i,
        "recordsFiltered" => $i,
        "sql" => $sql,
        "error"=>$row,
        "data" => $data
    ); 
    //mysqli_close();
    echo json_encode($output);
}