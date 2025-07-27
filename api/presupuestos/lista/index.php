<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
  
    $ands ="";

    $_criterio = $_POST['criterio'];
    if (!empty($_criterio)){
        $ands.=' WHERE 
            nombreDePresupuesto LIKE "%'.$_criterio.'%" 
            OR anoDePresupuesto LIKE "%'.$_criterio.'%" 
            OR claveDePartida LIKE "%'.$_criterio.'%" 
            OR conceptoDePartida LIKE "%'.$_criterio.'%" 
            OR monto LIKE "%'.$_criterio.'%"';
    }
  //echo var_dump($ands);
  //die();

    //$_grupo = $_POST['grupo'];
    //$_clasificacion = $_POST['clasificacion'];
    //$_tipo = $_POST['tipo'];
    //$compuesto = $_POST['compuesto'];
    //$almacen = $_POST['almacen'];

    //if(!empty($almacen)) $ands .= "AND a.cve_almac='{$almacen}' ";
    //if (!empty($_grupo)) $ands .= "AND c_gpoarticulo.cve_gpoart = '{$_grupo}' ";
    //if (!empty($_clasificacion)) $ands .= "AND c_sgpoarticulo.cve_sgpoart = '{$_clasificacion}' ";
    //if (!empty($_tipo)) $ands .= "AND c_ssgpoarticulo.cve_ssgpoart = '{$_tipo}' ";
    //if (!empty($compuesto)) $ands .= "AND a.Compuesto='{$compuesto}' ";

    //if (!empty($_criterio)) $ands = '('.$ands.')';
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    //if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT *  FROM `c_presupuestos`".$ands;
  
  //echo var_dump($sqlCount);

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    
    $row = mysqli_fetch_array($res);
    $count = $row[0];
    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$limit;

    
    $sql = "SELECT id, 
            nombreDePresupuesto, 
            anoDePresupuesto, 
            claveDePartida, 
            conceptoDePartida, 
            monto 
            FROM c_presupuestos";

    // hace una llamada previa al procedimiento
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
        //echo var_dump($row);
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['id'];
        $responce->rows[$i]['cell']=array($row["id"],$row["nombreDePresupuesto"],$row["anoDePresupuesto"],$row["claveDePartida"],$row["conceptoDePartida"],$row['monto']);
        $i++;
    }
    //echo var_dump($responce);
    
    echo json_encode($responce);exit;
}

else if(isset($_GET) && !empty($_GET)){
    $page = 1; // get the requested page
    $limit = 100; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = "asc"; // get the direction
    $almacen = $_GET['almacen'];
    $search = $_GET['search'];
    $searchSQL = "";
    if(!empty($search)){
        $searchSQL = " AND CONCAT_WS(' ', cve_articulo, des_articulo) like '%$search%' ";
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT COUNT(*) AS total FROM c_articulo WHERE cve_almac IN (SELECT id FROM c_almacenp WHERE clave = '$almacen') {$searchSQL}";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_fetch_array($res)['total'];
    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT id, cve_articulo, des_articulo, CONCAT_WS('x', alto, fondo, ancho) AS volumen FROM c_articulo WHERE cve_almac IN (SELECT id FROM c_almacenp WHERE clave = '$almacen') {$searchSQL};";
    // hace una llamada previa al procedimiento
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $i = 0;
    $arr = array();

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_articulo'];
        $responce->rows[$i]['cell']=array($row['cve_articulo'], $row['des_articulo'], $row['volumen'], $row['id']);
        $i++;
    }
    echo json_encode($arr);


}

