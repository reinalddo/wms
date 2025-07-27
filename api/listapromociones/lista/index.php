<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
   $page = $_POST['page']; // get the requested page
   $limit = $_POST['rows']; // get how many rows we want to have into the grid
   $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
   $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $search = $_POST['criterio'];
    $almacen = $_POST['almacen'];
    $filtro = $_POST['filtro'];
    $fechai= !empty($_POST['fechai']) ? date('Y-m-d', strtotime($_POST['fechai'])) : '';
    $fechaf= !empty($_POST['fechaf']) ? date('Y-m-d', strtotime($_POST['fechaf'])) : '';
    $presupuesto = $_POST['presupuesto'];
    $aditionalSearch = '';

    if(!empty($search) && !empty($filtro)){
        if($filtro === "th_aduana.status" ){
            $realStatus = "";
            if(stripos("Recibiendo", $search) !== false && stripos("Recibiendo", $search) >= 0){
                $realStatus = "I";
            }
            elseif(stripos("Pendiente de Recibir", $search) !== false && stripos("Pendiente de Recibir", $search) >= 0){
                $realStatus = "C";
            }
            elseif(stripos("Editando", $search) !== false && stripos("Editando", $search) >= 0){
                $realStatus = "A";
            }
            elseif(stripos("Cerrada", $search) !== false && stripos("Cerrada", $search) >= 0){
                $realStatus = "T";
            }else{
                $realStatus = "NULL";
            }
            $search = $realStatus;
        }

        $aditionalSearch .= " AND {$filtro} LIKE '%$search%'";
    }

    if(!empty($fechai) && !empty($fechaf)){
        if($fechai === $fechaf){
          $aditionalSearch .= " AND DATE(th_aduana.fech_pedimento) = '$fechai'";
        }else{
          $aditionalSearch .= " AND th_aduana.fech_pedimento BETWEEN STR_TO_DATE('$fechai','%Y-%m-%d') AND STR_TO_DATE('$fechaf','%Y-%m-%d')";
        }
    }
    else{
        if(!empty($fechai)){
            //buscar por fecha mayor
            $aditionalSearch .= " AND th_aduana.fech_pedimento > STR_TO_DATE('$fechai','%Y-%m-%d')";
        }
        if(!empty($fechaf)){
            //buscar por fecha menor
            $aditionalSearch .= " AND th_aduana.fech_pedimento <  STR_TO_DATE('$fechaf','%Y-%m-%d')";
        }
    }
  
     //$prep = "";
    if(!empty($presupuesto)){
      $aditionalSearch .= " and presupuesto='$presupuesto'";
    }

  $_page = 0;
  
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(*) AS cuenta FROM listap l LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac WHERE a.clave = '{$almacen}'";

    //$sqlCount .= $aditionalSearch;

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //$row = mysqli_fetch_array($res);
    //$count = $row['cuenta'];

    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;
//<i class="fa fa-circle '+color+'" aria-hidden="true"></i>
        $sql = "SELECT  l.id AS id, 
              IF(STR_TO_DATE(l.FechaF, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d') OR l.Caduca = 0, '<i class=\'fa fa-circle\' style=\'color:green;\' aria-hidden=\'true\'></i>', '<i class=\'fa fa-circle\' style=\'color:red;\' aria-hidden=\'true\'></i>') AS status_fecha,
              IF(STR_TO_DATE(l.FechaF, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d') OR l.Caduca = 0, '1', '0') AS status_res,
              l.Lista AS Clave, 
              l.Descripcion AS Lista,
              IF(l.Tipo = 'volumen', 'Unidad', IF(l.Tipo = 'monto', 'Monto','Grupo')) AS Tipo,
              #IF(l.Tipo = 'unidad', CONCAT('(',ar.cve_articulo, ') - ' ,ar.des_articulo), CONCAT('(', g.cve_sgpoart,') - ' ,g.des_sgpoart)) AS articulo_grupo,
              '' AS articulo_grupo,
              DATE_FORMAT(l.FechaI, '%d-%m-%Y') AS FechaIni,
              DATE_FORMAT(l.FechaF, '%d-%m-%Y') AS FechaFin,
              COUNT(DISTINCT r.Id_Destinatario) AS total_clientes,
              a.nombre AS Almacen
            FROM ListaPromo l 
            LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac 
            LEFT JOIN c_articulo ar ON ar.cve_articulo = l.Grupo
            LEFT JOIN c_sgpoarticulo g ON g.cve_gpoart = l.Grupo
            LEFT JOIN RelCliLis r ON r.ListaPromo = l.id 
            WHERE a.clave = '{$almacen}'
            GROUP BY id
            ORDER BY id DESC";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);
    //$sql .= $aditionalSearch;
    //$sql .= "GROUP BY num_pedimento ORDER BY th_aduana.num_pedimento DESC LIMIT $start, $limit;";
        $sql .= " LIMIT $start, $limit;";

    //echo var_dump($sql);
    //die();
  
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
    $row=array_map('utf8_encode', $row);
    $arr[] = $row;
        $responce->rows[$i]['id']=$row['id'];
        $responce->rows[$i]['cell']=array
                                    (
                                      $row[''],
                                      $row[''],
                                      $row['status_res'],
                                      $row['status_fecha'],
                                      $row['id'],
                                      $row['Clave'],
                                      $row['Lista'],
                                      $row['Tipo'],
                                      $row['FechaIni'],
                                      $row['FechaFin'], 
                                      $row['articulo_grupo'],
                                      $row['total_clientes'],
                                      $row['Almacen']
                                    );
        $i++;
    }
    echo json_encode($responce);
}


if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getListadoClientes") 
{
   $page = $_GET['page']; // get the requested page
   $limit = $_GET['rows']; // get how many rows we want to have into the grid
   $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
   $sord = $_GET['sord']; // get the direction
   $id = $_GET['id'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(*) AS total FROM RelCliLis WHERE ListaD = {$id};";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_fetch_array($res)['total'];

    $sql = "SELECT DISTINCT d.id_destinatario AS Cve_Destinatario,
                d.Cve_Clte AS Cve_Cliente,
                d.razonsocial AS destinatario,
                d.direccion AS direccion,
                d.colonia AS colonia,
                d.postal AS postal,
                d.ciudad AS ciudad,
                d.estado AS estado,
                d.telefono AS telefono
            FROM c_destinatarios d
            LEFT JOIN RelCliLis r ON r.Id_Destinatario = d.id_destinatario 
            WHERE r.ListaPromo = {$id} AND r.Id_Destinatario = d.id_destinatario";

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

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['ListaId'];
        $responce->rows[$i]['cell']=array('',
                                          $row['Cve_Cliente'], 
                                          $row['Cve_Destinatario'], 
                                          $row['destinatario'], 
                                          $row['direccion'],
                                          $row['colonia'], 
                                          $row['postal'], 
                                          $row['ciudad'], 
                                          $row['estado'], 
                                          $row['telefono']
                                          );
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getDetallesFolio") 
{
   $page = $_GET['page']; // get the requested page
   $limit = $_GET['rows']; // get how many rows we want to have into the grid
   $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
   $sord = $_GET['sord']; // get the direction
   $id = $_GET['id'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    $sql_count = " AND Tipo = 1";
    $sql_detalle = "AND l.Tipo = 1";
    $sql_base = "AND 0";
    if(isset($_GET['base']))
    {
        $sql_count = " AND Tipo = 0";
        $sql_detalle = "AND l.Tipo = 0";
        $sql_base = "";
    }

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(*) AS total FROM DetallePromo WHERE Id = {$id} {$sql_count};";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_fetch_array($res)['total'];

    $sql = "
        SELECT IF(IFNULL(l.Articulo, '') = '', l.cve_gpoart, l.Articulo) AS Articulo, IF(IFNULL(l.Articulo, '') = '', g.des_gpoart,a.des_articulo) AS des_articulo, 
            '' AS Monto, l.Cantidad AS Cantidad, CONCAT('( ',u.cve_umed, ' ) ', u.des_umed) AS unimed, '' AS Nivel
            FROM DetalleGpoPromo l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Articulo
            LEFT JOIN c_unimed u ON u.id_umed = l.TipMed
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = l.cve_gpoart
            WHERE l.PromoId = {$id} {$sql_base}

    UNION

    SELECT IF(IFNULL(l.Articulo, '') != '', l.Articulo, l.Grupo_Art) as Articulo, 
    IF(IFNULL(a.des_articulo, '') != '', a.des_articulo, g.des_gpoart) AS des_articulo, 
    l.Monto AS Monto, l.Cantidad AS Cantidad, CONCAT('( ',u.cve_umed, ' ) ', u.des_umed) AS unimed, l.Nivel 
            FROM DetallePromo l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Articulo
            LEFT JOIN c_unimed u ON u.id_umed = l.UniMed
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = IF(IFNULL(l.Articulo, '') != '', l.Articulo, l.Grupo_Art)
            WHERE l.PromoId = {$id} {$sql_detalle}";

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

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['PromoId'];
        $responce->rows[$i]['cell']=array($row['Articulo'], 
                                          $row['des_articulo'], 
                                          $row['Cantidad'], 
                                          $row['Monto'],
                                          $row['unimed'], 
                                          $row['Nivel']
                                          );
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getDetallesLista") 
{
   $page = $_GET['page']; // get the requested page
   $limit = $_GET['rows']; // get how many rows we want to have into the grid
   $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
   $sord = $_GET['sord']; // get the direction
   $id = $_GET['id'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(*) AS total FROM detallelp WHERE ListaId = {$id};";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_fetch_array($res)['total'];

    $sql = "SELECT l.Cve_Articulo, a.des_articulo, l.PrecioMin, l.PrecioMax, a.costo, l.ComisionPor, l.ComisionMon 
            FROM detallelp l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Cve_Articulo
            WHERE l.ListaId = {$id}";

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

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['ListaId'];
        $responce->rows[$i]['cell']=array(
                                          '',
                                          $row['Cve_Articulo'], 
                                          $row['des_articulo'], 
                                          $row['PrecioMin'], 
                                          $row['PrecioMax'],
                                          $row['costo'],
                                          $row['ComisionPor'], 
                                          $row['ComisionMon']
                                          );
        $i++;
    }
    echo json_encode($responce);
}

