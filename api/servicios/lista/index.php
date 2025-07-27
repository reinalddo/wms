<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////

    $sql_where = "";
    $id_grupo = "";
    if(isset($_POST['id_grupo']))
    {
        $id_grupo = $_POST['id_grupo'];
        $sql_where = " AND Gpo_Servicio = {$id_grupo} ";
    }

    $_criterio = $_POST['criterio'];
    

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if($_criterio != '')
        $sql_where = " AND s.Cve_Servicio LIKE '%$_criterio%' OR s.Des_Servicio LIKE '%$_criterio%' ";

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);


    $sql = "SELECT s.*, IFNULL(g.Des_GpoServicio, '') AS grupo, IFNULL(u.des_umed, '') AS unimed, IFNULL(s.IVA, '') as iva
            FROM c_servicios s
            LEFT JOIN c_gposervicios g ON g.Id_GpoServicio = s.Gpo_Servicio
            LEFT JOIN c_unimed u ON u.id_umed = s.UniMedida 
            WHERE 1 {$sql_where}"; //Gpo_Servicio = {$id_grupo}

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$start},{$limit}";
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
        $arr[] = $row;
        $responce->rows[$i]['Id_Servicio']=$row['Id_Servicio'];
        $responce->rows[$i]['cell']=array('', $row['Id_Servicio'], $row['Cve_Servicio'], utf8_encode($row['Des_Servicio']), utf8_encode($row['unimed']), utf8_encode($row['grupo']), $row['iva'], $row['UniMedida']);
        $i++;
    }
    echo json_encode($responce);
}
else if(isset($_GET) && !empty($_GET)){
    $page = 1; // get the requested page
    $limit = 100; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = "asc"; // get the direction
    $almacen = $_GET['almacen'];
    $search = "";//$_GET['search'];
    $searchSQL = "";
    $SQL_SFA = "";
    if(!empty($search)){
        $searchSQL = " AND CONCAT_WS(' ', c_articulo.cve_articulo, c_articulo.des_articulo) like '%$search%' ";
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas

    $id_almacen = $almacen;

/*
    $sqlCount = "SELECT COUNT(*) AS total FROM c_articulo WHERE cve_almac IN (SELECT id FROM c_almacenp WHERE clave = '$almacen') {$SQL_Surtibles} {$searchSQL} {$SQL_ArticulosConExistencia} {$SQL_LP} {$SQL_SFA}";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ".$sql;
    }

    $count = mysqli_fetch_array($res)['total'];
    */
    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT s.Id_Servicio AS id, s.Cve_Servicio AS cve_articulo, s.Des_Servicio AS des_articulo, 0 AS volumen, s.UniMedida FROM c_servicios s";
    // hace una llamada previa al procedimiento
  
   // echo var_dump($sql);
    //die();
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 5: (" . mysqli_error($conn) . ") ".$sql;
    }

    $count = mysqli_num_rows($res);

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;
    $i = 0;
    $arr = array();

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_articulo'];
        $responce->rows[$i]['cell']=array($row['cve_articulo'], $row['des_articulo'], $row['volumen'], $row['id'], $row['UniMedida']);
        $i++;
    }
    $responce->arr = $arr;
    echo json_encode($responce);
}