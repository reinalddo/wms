<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
   $page = $_POST['page']; // get the requested page
   $limit = $_POST['rows']; // get how many rows we want to have into the grid
   $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
   $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
  $almacen = $_POST['almacen'];
  $cliente = $_POST['cliente'];
  $pedido  = $_POST['pedido'];
  $diao    = $_POST['diao'];
  $id_ruta = $_POST['id_ruta'];
  $cve_proveedor = $_POST['cve_proveedor'];

  $fecha_inicio = $_POST['fecha_inicio'];
  $fecha_fin = $_POST['fecha_fin'];
	$_page = 0;
  
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	  $_page = 0;

	  if (intval($page)>0) $_page = ($page-1)*$limit;

      $sql_diao = "";
      if($diao)
      {
            $sql_diao = " AND d.Diao = '$diao' ";
      }

      $sql_pedido = "";
      if($pedido)
      {
            $sql_pedido = " AND d.Folio = '$pedido' ";
      }


      $sql_fecha = "";
      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha = " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') >= '{$fecha_inicio}' ";

      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') <= '{$fecha_fin}'";
      }


        $sql = "SELECT * FROM (
                SELECT DISTINCT 
                        ub.cve_ubicacion AS clave_zona, ub.desc_ubicacion AS zona_recepcion, 
                        r.cve_ruta AS cliente_ruta, e.Cve_Proveedor as ID_Proveedor,
                        e.Fol_Folio AS Folio_Entrada,
                        a.cve_articulo AS clave, a.des_articulo AS descripcion,
                        IF(a.control_lotes = 'S' OR a.control_numero_series = 'S' , IFNULL(tde.cve_lote, ''), '') AS lote_serie, 
                        IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(tde.cve_lote, '') != '' , DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        IFNULL(tdt.Cantidad, tde.CantidadRecibida) AS Cantidad,
                        um.cve_umed AS UM,
                        d.Diao,
                        d.ID as id_descarga,
                        DATE_FORMAT(d.Fecha, '%d-%m-%Y') as Fecha,
                        IFNULL(tdt.ClaveEtiqueta, '') AS LP,
                        IF(tde.status = 'M', 'Enviado a Merma',IF(IFNULL(tde.cve_lote, '') = '', 'Devolución', IF(IFNULL(tde.CantidadUbicada, 0) > 0, 'Ubicado', 'Pendiente Acomodo'))) AS STATUS,
                        tde.id AS id_item,
                        IFNULL(a.control_lotes, 'N') AS control_lotes,
                        IFNULL(a.Caduca, 'N') AS Caduca,
                        IFNULL(a.control_numero_series, 'N') AS control_numero_series,
                        DATE_FORMAT(e.HoraInicio, '%H:%m:%i') AS Hora,
                        e.Cve_Usuario
                FROM Descarga d
                LEFT JOIN c_almacenp alm ON alm.clave = d.IdEmpresa
                LEFT JOIN c_articulo a ON a.cve_articulo = d.Articulo
                INNER JOIN th_entalmacen e ON e.Fact_Prov = d.Folio
                INNER JOIN td_entalmacen tde ON tde.cve_articulo = d.Articulo AND tde.fol_folio = e.Fol_Folio
                LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = d.Articulo AND tdt.fol_folio = e.Fol_Folio AND tdt.cve_lote = tde.cve_lote
                LEFT JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
                LEFT JOIN tubicacionesretencion ub ON ub.cve_ubicacion = e.cve_ubicacion
                LEFT JOIN c_lotes l ON l.cve_articulo = d.Articulo AND tde.cve_lote = l.Lote
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE d.IdRuta = '$id_ruta' AND d.IdEmpresa = '$almacen' AND d.Cantidad > 0 AND (IFNULL(a.control_lotes, 'N') = 'S' OR IFNULL(a.control_numero_series, 'N') = 'S')
                #AND tde.status = 'Q' 
                {$sql_pedido} {$sql_diao} {$sql_fecha} 
                ORDER BY d.Diao DESC
                ) AS qa WHERE qa.STATUS != 'Ubicado' AND qa.STATUS != 'Enviado a Merma' AND qa.STATUS != 'Pendiente Acomodo'
";

    //echo var_dump($sql);
    //die();
      // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);


    $sql .= " LIMIT $start, $limit; ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

        $sql_diao = "
                SELECT DISTINCT qa.Diao FROM (
                SELECT DISTINCT 
                        IF(tde.status = 'M', 'Enviado a Merma',IF(IFNULL(tde.cve_lote, '') = '', 'Devolución', IF(IFNULL(tde.CantidadUbicada, 0) > 0, 'Ubicado', 'Pendiente Acomodo'))) AS STATUS,
                        d.Diao
                FROM Descarga d
                LEFT JOIN c_almacenp alm ON alm.clave = d.IdEmpresa
                LEFT JOIN c_articulo a ON a.cve_articulo = d.Articulo
                INNER JOIN th_entalmacen e ON e.Fact_Prov = d.Folio
                INNER JOIN td_entalmacen tde ON tde.cve_articulo = d.Articulo AND tde.fol_folio = e.Fol_Folio
                LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = d.Articulo AND tdt.fol_folio = e.Fol_Folio AND tdt.cve_lote = tde.cve_lote
                LEFT JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
                LEFT JOIN tubicacionesretencion ub ON ub.cve_ubicacion = e.cve_ubicacion
                LEFT JOIN c_lotes l ON l.cve_articulo = d.Articulo AND tde.cve_lote = l.Lote
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE d.IdRuta = '$id_ruta' AND d.IdEmpresa = '$almacen' AND d.Cantidad > 0 AND (IFNULL(a.control_lotes, 'N') = 'S' OR IFNULL(a.control_numero_series, 'N') = 'S')
                #AND tde.status = 'Q' 
                {$sql_pedido}
                ORDER BY Diao DESC
                ) AS qa WHERE qa.STATUS != 'Ubicado' AND qa.STATUS != 'Enviado a Merma' AND qa.STATUS != 'Pendiente Acomodo'

";
    if (!($res_diao = mysqli_query($conn, $sql_diao))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $options = "<option value=''>Seleccione DiaO</option>";
    while ($row_diao = mysqli_fetch_array($res_diao)) 
    {
        $dia_o = $row_diao['Diao'];
        $selected = "";
        if($diao == $dia_o) $selected = "selected";
        $options .= "<option $selected value='".$dia_o."'>".$dia_o."</option>";
    }


    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->sql = $sql;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->options_diao = $options;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
		//$row=array_map('utf8_encode', $row);
		$arr[] = $row;
        //$responce->rows[$i]['id']=$row['ID_Aduana'];
        $responce->rows[$i]['cell']=array
                                    (
                                      $row[''],
                                      utf8_decode($row['cliente_ruta']),
                                      $row['Folio_Entrada'], 
                                      utf8_decode($row['clave']),
                                      utf8_decode($row['descripcion']),
                                      $row['lote_serie'], 
                                      $row['Caducidad'],
                                      $row['Cantidad'],
                                      $row['UM'],
                                      $row['LP'],
                                      $row['STATUS'],
                                      $row['Diao'],
                                      $row['Fecha'],
                                      $row['Hora'],
                                      $row['Cve_Usuario'],
                                      $row['id_item'],
                                      $row['control_lotes'],
                                      $row['Caduca'],
                                      $row['control_numero_series'],
                                      $row['clave_zona'],
                                      $row['ID_Proveedor'],
                                      $row['zona_recepcion'],
                                      $row['id_descarga']
                                    );
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "verificar_caducidad_lote_varios") 
{
  $almacen = $_GET['almacen'];
  $cliente = $_GET['cliente'];
  $pedido  = $_GET['pedido'];
  $diao    = $_GET['diao'];
  $id_ruta = $_GET['id_ruta'];
  $cve_proveedor = $_GET['cve_proveedor'];
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_fin = $_GET['fecha_fin'];
  $asignar_todos = $_GET['asignar_todos'];
  $arr_entradas = $_GET['arr_entradas'];
  
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_diao = "";
      if($diao)
      {
            $sql_diao = " AND d.Diao = '$diao' ";
      }

      $sql_pedido = "";
      if($pedido)
      {
            $sql_pedido = " AND d.Folio = '$pedido' ";
      }


      $sql_fecha = "";
      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha = " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') >= '{$fecha_inicio}' ";

      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') <= '{$fecha_fin}'";
      }

      $sqlTodos = ""; $caducidad = "";
    if($asignar_todos == 0)
    {
        $entradas = implode(",", $arr_entradas);
        $sqlTodos = " AND tde.id IN ($entradas) ";
    }
        $sql = "SELECT 
                    GROUP_CONCAT(DISTINCT IFNULL(caducidad.Caduca, 'N') SEPARATOR '') AS Caduca
                FROM (
                SELECT * FROM (
                SELECT DISTINCT 
                #GROUP_CONCAT(DISTINCT IFNULL(a.Caduca, 'N') SEPARATOR '') AS Caduca,
                IFNULL(a.Caduca, 'N') AS Caduca,
                tde.id,
                IF(tde.status = 'M', 'EM',IF(IFNULL(tde.cve_lote, '') = '', 'PL', IF(IFNULL(tde.CantidadUbicada, 0) > 0, 'U', 'PA'))) AS estatus
                FROM Descarga d
                LEFT JOIN c_almacenp alm ON alm.clave = d.IdEmpresa
                LEFT JOIN c_articulo a ON a.cve_articulo = d.Articulo
                INNER JOIN th_entalmacen e ON e.Fact_Prov = d.Folio
                INNER JOIN td_entalmacen tde ON tde.cve_articulo = d.Articulo AND tde.fol_folio = e.Fol_Folio
                LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = d.Articulo AND tdt.fol_folio = e.Fol_Folio AND tdt.cve_lote = tde.cve_lote
                LEFT JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
                LEFT JOIN tubicacionesretencion ub ON ub.cve_ubicacion = e.cve_ubicacion
                LEFT JOIN c_lotes l ON l.cve_articulo = d.Articulo AND tde.cve_lote = l.Lote
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE d.IdRuta = '$id_ruta' AND d.IdEmpresa = '$almacen' AND d.Cantidad > 0 AND (IFNULL(a.control_lotes, 'N') = 'S' OR IFNULL(a.control_numero_series, 'N') = 'S')
                {$sql_pedido} {$sql_diao} {$sql_fecha} {$sqlTodos} 
                ) AS cambio WHERE cambio.estatus = 'PL'
                ) AS caducidad 
                ";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
        }
        $row = mysqli_fetch_array($res);
        $caducidad = $row['Caduca'];

    $responce->sql = $sql;
    $responce->caducidad = $caducidad;


    echo json_encode($responce);

}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getDetallesFolio") 
{
   $page = $_GET['page']; // get the requested page
   $limit = $_GET['rows']; // get how many rows we want to have into the grid
   $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
   $sord = $_GET['sord']; // get the direction
   $folio = $_GET['folio'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(cve_articulo) AS total FROM td_aduana WHERE num_orden = '{$folio}';";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_fetch_array($res)['total'];
    $sqlCount = "SELECT COUNT(*) total FROM td_entalmacen WHERE num_orden = '{$folio}'";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $total = mysqli_fetch_array($res)['total'];

if($total == 0)
{
    $sql = "SELECT DISTINCT
td.cve_articulo AS clave,
(SELECT des_articulo FROM c_articulo WHERE cve_articulo = td.cve_articulo) AS descripcion,
COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = td.cve_articulo AND num_orden = td.num_orden), 0) AS surtidas,
(ar.peso * (SELECT surtidas))AS peso,
((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') = 0, td.cve_lote, '') AS lote,
IF(td.caducidad = '0000-00-00 00:00:00', '', DATE_FORMAT(td.caducidad, '%d-%m-%Y')) AS caducidad,
IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') > 0, td.cve_lote, '') AS serie,
SUM(td.cantidad) AS pedidas,
td.costo AS precioU,
(td.costo*td.cantidad) AS importeTotal
FROM td_aduana td, c_articulo ar, th_aduana th
WHERE ar.cve_articulo = td.cve_articulo AND td.num_orden = th.num_pedimento AND td.num_orden = '{$folio}'
GROUP BY lote,serie, clave";
}
else
{

    $sql = "SELECT DISTINCT
td.cve_articulo AS clave,
(SELECT des_articulo FROM c_articulo WHERE cve_articulo = td.cve_articulo) AS descripcion,
COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = td.cve_articulo AND num_orden = td.num_orden), 0) AS surtidas,
(ar.peso * (SELECT surtidas))AS peso,
((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') = 0, (SELECT cve_lote FROM td_entalmacen WHERE num_orden = '{$folio}' AND cve_articulo = ar.cve_articulo), '') AS lote,
IF(td.caducidad = '0000-00-00 00:00:00', '', DATE_FORMAT(td.caducidad, '%d-%m-%Y')) AS caducidad,
IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') > 0, (SELECT cve_lote FROM td_entalmacen WHERE num_orden = '{$folio}' AND cve_articulo = ar.cve_articulo), '') AS serie,
SUM(td.cantidad) * (SELECT surtidas) AS pedidas,
ROUND(td.costo * (SELECT surtidas),2) AS precioU,
ROUND(((SELECT precioU) * (SELECT surtidas)),2) AS importeTotal
FROM td_aduana td, c_articulo ar, th_aduana th, td_entalmacen tde
WHERE ar.cve_articulo = td.cve_articulo AND td.num_orden = th.num_pedimento AND td.num_orden = '{$folio}' AND tde.num_orden = '{$folio}'
GROUP BY lote";
}
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
        $responce->rows[$i]['id']=$row['clave'];
        $responce->rows[$i]['cell']=array($row['clave'], 
                                          $row['descripcion'], 
                                          $row['lote'],
                                          $row['caducidad'], 
                                          $row['serie'],
                                          $row['pedidas'], 
                                          $row['surtidas'],
                                          $row['peso'],
                                          $row['volumen'],
                                          $row['precioU'],
                                          round($row['importeTotal'], 2)
                                          );
        $i++;
    }
    echo json_encode($responce);
}
