<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadGrid') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $_page = 0;

    $almacen = $_POST['almacen'];
    $search = isset($_POST['search']) ? $_POST['search'] : '';

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(cve_ubicacion) as cuenta FROM tubicacionesretencion WHERE cve_almacp = '$almacen'";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;

	if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "
                SELECT
                        r.desc_ubicacion AS area,
                        (SELECT COUNT(cve_articulo) FROM td_entalmacen WHERE cve_ubicacion = r.cve_ubicacion ) AS total_productos,
                        (SELECT SUM(CantidadRecibida - IFNULL(CantidadUbicada,0)) FROM td_entalmacen WHERE cve_ubicacion = r.cve_ubicacion) AS existencia_total,
                        r.cve_ubicacion
                FROM tubicacionesretencion r
                WHERE r.cve_almacp = '$almacen' AND r.Activo = 1 and (SELECT SUM(CantidadRecibida - IFNULL(CantidadUbicada,0)) FROM td_entalmacen WHERE cve_ubicacion = r.cve_ubicacion) > 0
    ";

    if(!empty($search)){
        $sql .= " AND r.desc_ubicacion like '%$search%' ";
    }

    $sql .= " LIMIT {$_page},{$limit};";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array($area, $total_productos, $existencia_total, $cve_ubicacion);
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadDetails') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $_page = 0;

    $ubicacion = $_POST['ubicacion'];
    $almacen = $_POST['almacen'];

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(cve_articulo) as cuenta FROM td_entalmacen WHERE cve_ubicacion = '$ubicacion'";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;

	if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "
            SELECT
                    r.desc_ubicacion AS area,
                    tde.fol_folio AS folio,
                    (SELECT DATE_FORMAT(Fec_Entrada, '%d-%m-%Y %H:%i:%s') FROM th_entalmacen WHERE Fol_Folio = tde.fol_folio) AS fecha_entrada,
                    tde.cve_articulo AS clave,
                    a.des_articulo AS descripcion,
                    tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0) AS cantidad,
                    l.LOTE as lote,
                    l.CADUCIDAD as caducidad,
                    tde.numero_serie as serie
            FROM td_entalmacen tde
            LEFT JOIN tubicacionesretencion r ON r.cve_ubicacion = tde.cve_ubicacion
            LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo
            LEFT JOIN c_lotes l ON l.LOTE = tde.cve_lote AND l.cve_articulo = tde.cve_articulo
            WHERE tde.cve_ubicacion = '$ubicacion' and ( tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0)) > 0
            LIMIT {$_page},{$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array($area,$folio,$fecha_entrada,$clave,$descripcion,$cantidad,$lote,$serie,$caducidad);
        $i++;
    }
    echo json_encode($responce);
}
