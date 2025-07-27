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
    //$sqlCount = "SELECT COUNT(*) AS cuenta FROM listap l LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac WHERE a.clave = '{$almacen}'";
    ////$sqlCount .= $aditionalSearch;
    //if (!($res = mysqli_query($conn, $sqlCount))) {
    //    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    //}
    //$row = mysqli_fetch_array($res);
    //$count = $row['cuenta'];
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;
//<i class="fa fa-circle '+color+'" aria-hidden="true"></i>
        $sql = "SELECT  l.id AS id, 
                  IF(STR_TO_DATE(l.FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d'), '<i class=\'fa fa-circle\' style=\'color:green;\' aria-hidden=\'true\'></i>', '<i class=\'fa fa-circle\' style=\'color:red;\' aria-hidden=\'true\'></i>') AS status_fecha,
                  IF(STR_TO_DATE(l.FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d'), '1', '0') AS status_res,
                  l.Lista AS Lista, 
                  m.Cve_Moneda as Moneda,
                  IF(l.Tipo = 1, 'Precio Normal', 'Precio por Rango') AS Tipo,
                  DATE_FORMAT(l.FechaIni, '%d-%m-%Y') AS FechaIni,
                  DATE_FORMAT(l.FechaFin, '%d-%m-%Y') AS FechaFin,
                  a.nombre AS Almacen,
                  COUNT(DISTINCT d.Cve_Articulo) AS total_productos,
                  COUNT(DISTINCT r.Id_Destinatario) AS total_clientes
                FROM listap l 
                LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac 
                LEFT JOIN detallelp d ON l.id = d.ListaId
                LEFT JOIN RelCliLis r ON r.ListaP = l.id 
                LEFT JOIN c_monedas m ON m.Id_Moneda = l.id_moneda
                WHERE a.clave = '{$almacen}'
                GROUP BY id";


    //$sql .= $aditionalSearch;
    //$sql .= "GROUP BY num_pedimento ORDER BY th_aduana.num_pedimento DESC LIMIT $start, $limit;";
        //$sql .= "LIMIT $start, $limit;";

    //echo var_dump($sql);
    //die();
  
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";

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
                                      $row['status_res'],
                                      $row['status_fecha'],
                                      $row['id'],
                                      $row['Lista'],
                                      $row['Tipo'],
                                      $row['Moneda'],
                                      $row['FechaIni'],
                                      $row['FechaFin'], 
                                      $row['total_productos'],
                                      $row['total_clientes'],
                                      $row['Almacen']
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

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

   $tipo_servicio = 'N';
    $sql_tiposervicio = "SELECT TipoServ FROM listap WHERE id = '$id'";
    if (!($res_servicio = mysqli_query($conn, $sql_tiposervicio)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $tipo_servicio = mysqli_fetch_array($res_servicio)['TipoServ'];

//    $sqlCount = "SELECT COUNT(*) AS total FROM detallelp WHERE ListaId = {$id};";
//    if (!($res = mysqli_query($conn, $sqlCount))) {
//        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
//    }
//    $count = mysqli_fetch_array($res)['total'];
/*
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
    $sql = "SELECT l.Cve_Articulo, a.des_articulo, l.PrecioMin, l.PrecioMax, l.ComisionPor, l.ComisionMon 
            FROM detallelp l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Cve_Articulo
            WHERE l.ListaId = $id";

    if($tipo_servicio == 'S')
    $sql = "SELECT l.Cve_Articulo, a.Des_Servicio AS des_articulo, l.PrecioMin, l.PrecioMax, l.ComisionPor, l.ComisionMon 
            FROM detallelp l 
            LEFT JOIN c_servicios a ON a.Cve_Servicio = l.Cve_Articulo
            WHERE l.ListaId = $id";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    
    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";



    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $rwt = $id;
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
    //$responce->sql = $sql;
    //$responce->rw = $rwt;

    $i = 0;
    //$rwt = array();

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['ListaId'];
        $responce->rows[$i]['cell']=array(utf8_decode($row['Cve_Articulo']), 
                                          utf8_decode($row['des_articulo']), 
                                          utf8_decode($row['PrecioMin']), 
                                          utf8_decode($row['PrecioMax']),
                                          utf8_decode($row['ComisionPor']), 
                                          utf8_decode($row['ComisionMon'])
                                          );
        //$rwt[$i] = $responce->rows[$i]['cell'];
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
    //$sqlCount = "SELECT COUNT(*) AS total FROM RelCliLis WHERE ListaP = {$id};";
    //if (!($res = mysqli_query($conn, $sqlCount))) {
    //    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    //}
    //$count = mysqli_fetch_array($res)['total'];

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
            WHERE r.ListaP = {$id} AND r.Id_Destinatario = d.id_destinatario";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";

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
    //$sqlCount = "SELECT COUNT(*) AS total FROM detallelp WHERE ListaId = {$id};";
    //if (!($res = mysqli_query($conn, $sqlCount))) {
    //    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    //}
    //$count = mysqli_fetch_array($res)['total'];

    $sql = "SELECT l.Cve_Articulo, a.des_articulo, l.PrecioMin, l.PrecioMax, a.costo, l.ComisionPor, l.ComisionMon 
            FROM detallelp l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Cve_Articulo
            WHERE l.ListaId = {$id}";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";

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
