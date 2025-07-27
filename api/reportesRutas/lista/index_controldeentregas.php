<?php
include '../../../config.php';

error_reporting(0);


if (isset($_POST) && !empty($_POST)) {

    $page = $_POST['page'];
    $rows = $_POST['rows'];
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    //$search = $_POST['search'];
    $criterio = $_POST['criterio'];
    $ruta = $_POST['ruta'];
    $diao = $_POST['diao'];
    $fecha_inicio = $_POST['fechaini'];
    $fecha_fin = $_POST['fechafin'];
    $almacen = $_POST['almacen'];

    $responce = "";


    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn, $charset);

    $SQLFecha = "";

    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        $SQLFecha = " AND DATE(d.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(d.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if ($fecha_inicio == $fecha_fin)
            $SQLFecha = " AND DATE(d.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_inicio)) {
        $SQLFecha = " AND d.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_fin)) {
        $SQLFecha = " AND d.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
    }

    $SQLRuta = "";
    if ($ruta) {
        $SQLRuta = " AND r.cve_ruta = '" . $ruta . "' ";

        if ($ruta == 'todas') {
            $SQLRuta = " AND r.cve_ruta != '' ";
        }
    }

    $SQLDiaO = "";
    if ($diao) {
        $SQLDiaO = " AND d.Diao = '" . $diao . "' ";
    }

    $SQLCriterio = ""; 
    if ($criterio) {
        $SQLCriterio = " AND (a.cve_articulo LIKE '%" . $criterio . "%' OR a.des_articulo LIKE '%" . $criterio . "%' OR d.Folio LIKE '%" . $criterio . "%' OR r.cve_ruta LIKE '%" . $criterio . "%' OR r.descripcion LIKE '%" . $criterio . "%' ) ";
    }


    $sql = "SELECT d.Diao, DATE_FORMAT(d.Fecha, '%d-%m-%Y') AS Fecha, a.cve_articulo, a.des_articulo, d.Cantidad, d.Folio
            FROM Descarga d
            INNER JOIN c_articulo a ON a.cve_articulo = d.Articulo
            INNER JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo 
            INNER JOIN c_almacenp alm ON alm.id = ra.Cve_Almac AND alm.clave = d.IdEmpresa
            INNER JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
            WHERE ra.Cve_Almac = {$almacen} 
            {$SQLFecha} 
            {$SQLRuta} 
            {$SQLDiaO} 
            {$SQLCriterio} 
            ORDER BY Diao DESC
            ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "10Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        extract($row);
        //$responce->rows[$i]['Id'] = $Id;
        $responce->rows[$i]['cell'] = array(
            'dia_o'       => $Diao,
            'fecha'       => $Fecha,
            'clave'       => $cve_articulo,
            'descripcion' => $des_articulo,
            'cantidad'    => number_format($Cantidad, 2),
            'folio'       => $Folio
        );
        $i++;
    };

    //mysqli_close();
    //header('Content-type: application/json');
    echo json_encode($responce);
}