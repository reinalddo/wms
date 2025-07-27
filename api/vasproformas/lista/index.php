<?php
include '../../../config.php';

error_reporting(0);

if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'getDetallesFolio') {
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    //$search = $_GET['search'];
    //$id_venta = $_GET['id_venta'];
    $folio = $_GET['folio'];
    //$cve_articulo = $_GET['cve_articulo'];

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];
*/
    $sql = "SELECT td.Fol_Folio, td.Referencia
            FROM td_proforma td
            WHERE td.Fol_Proform = '$folio'";

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
                'Fol_Folio' => $Fol_Folio,
                'Referencia' => $Referencia
            );
        $i++;
    }

    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'getCobranzaCxC') {

    $page = $_POST['page'];  // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx'];  // get index row - i.e. user click to sort
    $sord = $_POST['sord'];  // get the direction
    $criterio = $_POST['criterio'];
    $cliente = $_POST['cliente'];
    $status_servicios = $_POST['status_servicios'];
    //$ruta = $_POST['ruta'];
    //$diao = $_POST['diao'];
    //$operacion = $_POST['operacion'];
    $fecha_inicio = $_POST['fechaini'];
    $fecha_fin = $_POST['fechafin'];
    //$status = $_POST['status'];
    //$credito = $_POST['credito'];
    $almacen = $_POST['almacen'];
    //$agente = $_POST['agente'];


    $_page = 0;

    if (intval($page) > 0) $_page = ($page - 1) * $limit;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sql_almacen = "SELECT clave FROM c_almacenp WHERE id = '$almacen'";
    if (!($res_almacen = mysqli_query($conn, $sql_almacen))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res_almacen)['clave'];
*/
/*
    if($fecha_inicio == '') $fecha_inicio = 'NULL';
    else
    {
        $fecha = explode('-', $fecha_inicio);
        $fecha_inicio = $fecha[2]."-".$fecha[1]."-".$fecha[0];
        $fecha_inicio = "'".$fecha_inicio."'";
    }
    if($fecha_fin == '') $fecha_fin = 'NULL';
    else
    {
        $fecha = explode('-', $fecha_fin);
        $fecha_fin = $fecha[2]."-".$fecha[1]."-".$fecha[0];
        $fecha_fin = "'".$fecha_fin."'";
    }
*/

    $sqlBusqueda = "";
    if($criterio!= '')
       $sqlBusqueda = " AND (th.Fol_Proform LIKE '%$criterio%' OR th.Docto_Ref LIKE '%$criterio%' OR th.Cve_clte LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%' OR th.Tipo_Operacion LIKE '%$criterio%' OR th.Observaciones LIKE '%$criterio%') ";

    $SqlCliente = "";
    if($cliente != '')
        $SqlCliente = " AND c.Cve_Clte = '$cliente' ";

    $SQLFecha = "";
    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        $SQLFecha = " AND DATE(th.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(th.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if ($fecha_inicio == $fecha_fin) {
            $SQLFecha = " AND DATE(th.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
        }
    } else if (!empty($fecha_inicio)) {
        $SQLFecha = " AND th.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
    } else if (!empty($fecha_fin)) {
        $SQLFecha = " AND th.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
    }

    $sqlStatus = "";
    if($status_servicios != '')
        $sqlStatus = " AND th.Status = '$status_servicios' ";

        $sql = "SELECT DATE_FORMAT(th.Fecha, '%d-%m-%Y') AS fecha, th.Fol_Proform AS folio, th.Docto_Ref AS referencia, #th.Docto_Ped AS pedimento, 
                       th.Cve_clte AS clave_cliente, c.RazonSocial AS nombre_de_cliente, th.Tipo_Operacion as tipo_operacion, 
                       th.Observaciones as observaciones, th.Status
                FROM th_proforma th
                LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
                LEFT JOIN c_almacenp a ON a.id = th.Cve_Almac
                WHERE a.id = $almacen {$SQLFecha} {$sqlBusqueda} {$SqlCliente} {$sqlStatus}
                ";

    if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación SP: (" . mysqli_error($conn) . ") ";

    $count = mysqli_num_rows($res);

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

    //if ($dato_inicial_cobranza) {
    //    $responce->page = 1;
    //    $responce->total = 1;
    //    $responce->records = 1;
    //}

    $i = 0;

    while ($row = mysqli_fetch_array($res)) {

        $responce->rows[$i]['cell'] = array('', 
            $row['fecha'],
            $row['folio'],
            $row['referencia'],
            //$row['pedimento'],
            $row['clave_cliente'],
            $row['nombre_de_cliente'],
            $row['tipo_operacion'],
            $row['observaciones'],
            $row['Status']
        );
        $i++;

        //if($tipo_env_clave == 'P') $envplastico += $Cantidad;
        //if($tipo_env_clave == 'C') $envcristal += $Cantidad;
        //if($tipo_env_clave == 'G') $envgarrafon += $Cantidad;
    };

//    $responce->tcredito = $total_pages;
//    $responce->tcobranza = $count;
//    $responce->tadeudo = $sql;

    //$responce->envplastico = $envplastico;
    //$responce->envcristal = $envcristal;
    //$responce->envgarrafon = $envgarrafon;

    //mysqli_close();
    //header('Content-type: application/json');
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'generar_proforma') {

    $nro_pedido      = $_POST['nro_pedido'];
    $tipo_operacion  = $_POST['tipo_operacion'];
    $observaciones   = $_POST['observaciones'];
    $folios          = $_POST['folios'];
    $seleccionarTodo = $_POST['seleccionarTodo'];
    $id_almacen      = $_POST['id_almacen'];
    $cve_usuario     = $_POST['cve_usuario'];
    $cve_cliente     = $_POST['cve_cliente'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//****************************************************************************
//      generar folio de proforma
//****************************************************************************
  $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 10, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())) AS mes, YEAR(CURRENT_DATE()) AS _year FROM DUAL";
    if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";  
    $fecha = mysqli_fetch_array($res);

  $mes  = $fecha['mes'];
  $year = $fecha['_year'];


  $count = 1;
  while(true)
  {
      if($count < 10)
        $count = "0".$count;

      $folio_next = "PRF".$year.$mes.$count;
      $sql = "SELECT COUNT(*) as Consecutivo FROM th_proforma WHERE Fol_Proform = '$folio_next'";
      if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";  
      $data = mysqli_fetch_array($res);

      if($data["Consecutivo"] == 0)
        break;
      else
      {
          $count += 0; //convirtiendo a entero
          $count++;
      }
  }

    if($seleccionarTodo == 1)
    {
        $folios = array();

        $sql = "SELECT Fol_Folio FROM th_pedservicios WHERE Status = 'A' AND Cve_Almac = $id_almacen";
        if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        while($row_folios = mysqli_fetch_array($res))
        {
            $folios[] = $row_folios['Fol_Folio'];
        }
    }

    $sql = "INSERT INTO th_proforma(Fol_Proform, Cve_Almac, Cve_Usuario, Fecha, Cve_clte, Cve_CteProv, Status, Observaciones, Docto_Ref, Tipo_Operacion) VALUES ('$folio_next', $id_almacen, '$cve_usuario', CURDATE(), '$cve_cliente', '$cve_cliente', 'A', '$observaciones', '$nro_pedido', '$tipo_operacion')";
    if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";

//****************************************************************************
//****************************************************************************

    foreach($folios as $folio)
    {
        $sql = "INSERT INTO td_proforma(Fol_Proform, Fol_Folio, status, Referencia) VALUES ('$folio_next', '$folio', 'A', (SELECT IFNULL(Docto_Ref, '') FROM th_pedservicios WHERE Fol_Folio = '$folio'))";
        if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";

        $sql = "UPDATE th_pedservicios SET Status = 'T' WHERE Fol_Folio = '$folio'";
        if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";

    }

/*
    $sql_almacen = "SELECT clave FROM c_almacenp WHERE id = '$almacen'";
    if (!($res_almacen = mysqli_query($conn, $sql_almacen))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res_almacen)['clave'];
*/
    //$responce = array();

    $responce->consecutivo_folio = $folio_next;
    $responce->nro_pedido = $nro_pedido;
    $responce->tipo_operacion = $tipo_operacion;
    $responce->observaciones = $observaciones;
    $responce->folios = $folios;
    $responce->folio_gen = $folio_next;
    $responce->seleccionarTodo = $seleccionarTodo;

    echo json_encode($responce);
    //echo $folios;
}
