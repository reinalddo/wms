<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $_fecha = $_POST['_fecha'];
    $_fechaFin = $_POST['_fechaFin'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_fecha)) $_fecha = date("Y-m-d", strtotime($_fecha));
    if (!empty($_fechaFin)) $_fechaFin = date("Y-m-d", strtotime($_fechaFin));


    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT count(d.ID_PLAN) AS ORDEN,a.des_articulo as ARTICULO,d.cve_articulo  AS CLAVE,d.status AS ESTADO from det_planifica_inventario d,c_articulo a where d.FECHA_APLICA>='$_fecha' and d.FECHA_APLICA<='$_fechaFin' and d.cve_articulo=a.cve_articulo;";
            
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["ORDEN"];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;
    
    if (intval($page)>0) $_page = ($page-1)*$limit;
    
     $sql = "SELECT DISTINCT cab.ID_PLAN AS ORDEN, ap.nombre, (select u.cve_usuario from c_usuario u, t_conteoinventariocicl cic where cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN order by cic.NConteo desc limit 1) as usuario, DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y %H:%i:%s') as fechaIni, DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y %H:%i:%s') as fechaFin, d.status AS ESTADO from det_planifica_inventario d,c_articulo a, c_almacenp ap, cab_planifica_inventario cab where d.cve_articulo=a.cve_articulo and a.cve_almac = ap.id and cab.ID_PLAN = d.ID_PLAN ORDER BY $sidx LIMIT $_page, $limit;";
    
    /* $sql = "Select cab.ID_PLAN AS ORDEN, ap.nombre, (select d.Cve_Usuario where cic.cve_usuario = d.Cve_Usuario) as usuario, cab.FECHA_INI as fechaIni, cab.FECHA_FIN as fechaFin, cab.diferencia, d.status AS ESTADO from det_planifica_inventario d,c_articulo a, c_almacenp ap, cab_planifica_inventario cab, t_conteoinventariocicl cic where d.FECHA_APLICA>='$_fecha' and d.FECHA_APLICA<='$_fechaFin' and d.cve_articulo=a.cve_articulo and a.cve_almac = ap.id and cab.ID_PLAN = d.ID_PLAN ORDER BY $sidx LIMIT $_page, $limit;";*/

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
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
        $row = array_map('utf8_encode', $row);
        $arr[] = $row;
        if($row['ESTADO'] == "A")
            $row['ESTADO'] = "Abierto";
        $responce->rows[$i]['id']=$row['ORDEN'];
        if($row['ESTADO']=='Cerrado'){
            $fec=$row['fechaFin'];
        }
        else
        {
            $fec='';
        }
        $responce->rows[$i]['cell']=array($row['ORDEN'], $row['nombre'], $row['usuario'], $row['fechaIni'], $fec , $row['diferencia'], $row['ESTADO']);
        $i++;
    }
    echo json_encode($responce);
}
if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getAvailableUsers'){
    $id = $_GET['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $usuarios = '';
    $query = mysqli_query($conn, "SELECT cve_usuario, nombre_completo FROM c_usuario WHERE Activo = 1 AND cve_usuario NOT IN (SELECT cve_usuario FROM t_conteoinventariocicl WHERE ID_PLAN = {$id});");

    
    if($query->num_rows > 0){
        $usuarios = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    echo json_encode(array(
        "usuarios" => $usuarios
    ));
}


if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'getPendingCount'){
    $id = $_GET['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $total = '';
    $query = mysqli_query($conn, "SELECT COUNT(cve_articulo) AS total FROM `t_invpiezasciclico` WHERE Cantidad = 0 AND ID_PLAN = {$id} AND NConteo = (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$id} AND NConteo <> 0)");

    if($query->num_rows > 0){
        $total = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['total'];
    }
    echo json_encode(array(
        "total" => $total
    ));
}