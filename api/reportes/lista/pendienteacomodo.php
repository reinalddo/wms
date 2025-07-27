<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && !empty($_GET) and !isset($_GET['action']) ){
    $page = $_GET['start'];
    $limit = $_GET['length']; 
    $search = $_GET['search']['value'];
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = "SELECT COUNT(fol_folio) AS total FROM td_entalmacen INNER JOIN th_entalmacen ON th_entalmacen.Fol_Folio = td_entalmacen.fol_folio LEFT JOIN t_pendienteacomodo pa ON pa.cve_articulo = td_entalmacen.cve_articulo AND pa.cve_lote = td_entalmacen.cve_lote WHERE pa.Cantidad > 0 AND th_entalmacen.Cve_Almac = '{$almacen}'";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query)
    {
      $count = mysqli_fetch_array($query)['total'];
    }
    /*Pedidos pendiente de acomodo*/
    $sql = "SELECT  
            eh.Fol_Folio AS folio,
            COALESCE(eh.fol_oep, '--') AS orden,
            a.nombre AS almacen,
            u.desc_ubicacion AS area,
            e.cve_articulo AS clave_producto,
            p.des_articulo AS des_producto,
            COALESCE(l.LOTE, '--') AS lote,
            COALESCE(l.CADUCIDAD, '--') AS caducidad,
            COALESCE(e.numero_serie, '--') AS serie,
            pa.Cantidad AS cantidad,
            CONCAT(eh.Fec_Entrada, ' ',eh.HoraInicio) AS hora_inicio
        FROM `td_entalmacen` e 
        INNER JOIN th_entalmacen eh ON eh.Fol_Folio = e.fol_folio
        LEFT JOIN c_almacenp a ON eh.Cve_Almac = a.clave
        LEFT JOIN tubicacionesretencion u ON e.cve_ubicacion = u.cve_ubicacion
        LEFT JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
        LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.LOTE = e.cve_lote
        LEFT JOIN t_pendienteacomodo pa ON pa.cve_articulo = e.cve_articulo AND pa.cve_lote = e.cve_lote
        WHERE pa.Cantidad > 0 AND eh.Cve_Almac = '{$almacen}'
        
        UNION ALL
  
        SELECT
            '' folio,
            tha.num_pedimento orden,
            tha.Cve_Almac AS almacen,
            '' AS area,
            a.cve_articulo AS clave_producto,
            a.des_articulo AS des_producto,
            COALESCE(tda.cve_lote, '--') AS lote,
            COALESCE(tda.caducidad, '--') AS caducidad,
            '' AS serie,
            tda.cantidad,
            tha.fech_llegPed AS hora_inicio
        FROM td_aduana tda
            LEFT JOIN c_articulo a ON tda.cve_articulo = a.cve_articulo
            LEFT JOIN th_aduana tha ON tha.num_pedimento = tda.num_orden
        WHERE tha.status = 'T'
        
        ;
    ";
  
    
    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map('utf8_encode', $row);
      $data[] = $row;
      $i++;
    } 
    mysqli_close();
    header('Content-type: application/json');
    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $data
    ); 
    echo json_encode($output);
}


if (isset($_POST) && !empty($_POST) and isset($_POST['action']) and $_POST['action'] == 'cabecera' ) 
{
    $almacen   = $_POST['almacen'];
    $proveedor = $_POST['proveedor'];
    $criterio  = $_POST['criterio'];
   // $where_sql = " WHERE status = 'T' ";
    $where_sql = "";
    if($almacen !="")
    {
      $where_sql .= " AND th_entalmacen.Cve_Almac = '{$almacen}' ";
    }

    if($proveedor !="")
    {
      $where_sql .= " AND c_proveedores.ID_Proveedor = '{$proveedor}' ";
    }

    if($criterio != "")
    {
       $where_sql .= " AND (th_aduana.num_pedimento LIKE '%{$criterio}%' OR th_aduana.Factura LIKE '%{$criterio}%' OR th_entalmacen.fol_folio LIKE '%{$criterio}%' OR th_entalmacen.Fol_OEP LIKE '%{$criterio}%' OR td_entalmacen.cve_articulo LIKE '%{$criterio}%' OR IFNULL(td_entalmacen.cve_lote, '') LIKE '%{$criterio}%' OR 
      td_entalmacenxtarima.ClaveEtiqueta LIKE '%{$criterio}%' OR c_proveedores.Nombre LIKE '%{$criterio}%' OR tubicacionesretencion.desc_ubicacion LIKE '%{$criterio}%' OR tubicacionesretencion.cve_ubicacion LIKE '%{$criterio}%' OR ch.CveLP LIKE '%{$criterio}%') ";
    }

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sql = "SELECT * FROM (
        SELECT 
           ifnull(th_entalmacen.fol_folio,'') as numero_oc,
            th_aduana.num_pedimento AS folio_oc,
            th_aduana.factura AS folio_erp,
            th_entalmacen.tipo AS tipo,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = th_entalmacen.Cve_Usuario) AS usuario_activo,
            Ifnull(DATE_FORMAT(th_aduana.fech_pedimento,'%d-%m-%Y %I:%i:%s %p'), '') AS fecha_entrega,
            (SELECT MIN(DATE_FORMAT(td_entalmacen.fecha_inicio,'%d-%m-%Y %H:%i:%s %p')))  as fecha_recepcion,
            (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %H:%i:%s %p'))  as fecha_fin_recepcion,
            sum(td_entalmacen.CantidadRecibida) - SUM(IFNULL(td_entalmacen.CantidadUbicada,0)) AS total_pedido,     
            #IFNULL(sum(c_articulo.peso * td_entalmacen.CantidadDisponible), 0) AS peso_estimado,
            #TRUNCATE(IFNULL(SUM(c_articulo.peso * td_entalmacen.CantidadRecibida), 0), 4) AS peso_estimado,
            TRUNCATE(IFNULL(SUM(c_articulo.peso * (td_entalmacen.CantidadRecibida - IFNULL(td_entalmacen.CantidadUbicada,0))), 0), 4) AS peso_estimado,
            c_proveedores.Nombre AS proveedor,
            '0' as  cantidad_recibida,
            th_entalmacen.Fact_Prov as facprov,
            th_entalmacen.Cve_Almac as clave_almacen,
            th_entalmacen.Cve_Almac as almacen,
            tubicacionesretencion.desc_ubicacion as retencion
        FROM th_entalmacen
			      LEFT JOIN td_entalmacen ON td_entalmacen.fol_folio = th_entalmacen.fol_folio
          LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.fol_folio = th_entalmacen.fol_folio AND td_entalmacenxtarima.Ubicada != 'S'
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = td_entalmacenxtarima.ClaveEtiqueta
            LEFT JOIN c_usuario ON c_usuario.id_user = th_entalmacen.Cve_Usuario
            LEFT JOIN c_almacenp ON c_almacenp.clave = th_entalmacen.Cve_Almac
            LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = th_entalmacen.Cve_Proveedor
            LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_entalmacen.cve_articulo
            LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = td_entalmacen.cve_ubicacion
            INNER JOIN t_pendienteacomodo t ON t.cve_articulo = td_entalmacen.cve_articulo AND IFNULL(t.cve_lote, '') = IFNULL(td_entalmacen.cve_lote, '') AND t.cve_ubicacion = td_entalmacen.cve_ubicacion AND t.Cantidad > 0
        WHERE tubicacionesretencion.activo = '1' AND IFNULL(td_entalmacenxtarima.Ubicada, 'N') != 'S' 
        #AND (td_entalmacen.CantidadRecibida - IFNULL(td_entalmacen.CantidadUbicada,0)) > 0
        $where_sql
        group by th_entalmacen.Fol_Folio
        order by  th_entalmacen.Fol_Folio desc) tabla WHERE tabla.total_pedido > 0
      ";//th_entalmacen.Cve_Usuario AS usuario_activo,
    //echo var_dump($sql);
    //die();
    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $response = [];

    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map('utf8_encode', $row);
      $response['data'][] = [
        'numero_oc'            => $row['numero_oc'],
        'folio_oc'             => $row['folio_oc'],
        'folio_erp'            => $row['folio_erp'],
        'tipo'                 => $row['tipo'],
        'usuario_activo'       => $row['usuario_activo'],
        'fecha_entrega'        => $row['fecha_entrega'],
        'fecha_recepcion'      => $row['fecha_recepcion'],
        'fecha_fin_recepcion'  => $row['fecha_fin_recepcion'],
        'total_pedido'         => $row['total_pedido'],
        'peso_estimado'        => $row['peso_estimado'],
        'proveedor'            => $row['proveedor'],
        'cantidad_recibida'    => $row['cantidad_recibida'],
        'facprov'              => $row['facprov'],
        'almacen'              => $row['almacen'],
        'ERP'                  => $row['ERP'],
        'retecion'             => $row['retencion']
      ];
    }
    //var_dump(json_encode($response)); exit;
    echo json_encode($response);
}




if (isset($_POST) && !empty($_POST) and isset($_POST['action']) and $_POST['action'] == 'detalles' )
{
  $folio = $_POST['folio'];
  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

  $sql = "SELECT COUNT(cve_articulo) as total from td_entalmacen where Fol_Folio = '$folio'";
  
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $count = $row['total'];
  $sql = "SELECT DISTINCT cve_articulo from td_entalmacen WHERE Fol_Folio = '$folio'";
  
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  
  $lineas = [];
  while($row = mysqli_fetch_array($res))
  {
    $lineas [] = $row['cve_articulo'];
  }
  
  $sql = "SELECT
              IFNULL(ch_caja.CveLP, '') AS EtiquetaCaja,
              tde.cve_articulo as clave,
              a.des_articulo as descripcion,
              #tda.cantidad as cantidad_pedida,
              #IFNULL(ta.cantidad, tde.CantidadRecibida) AS cantidad_pedida,
              #tde.CantidadRecibida AS cantidad_pedida,
              IFNULL(tda.cantidad, '') AS cantidad_pedida,
              tde.status as status,
              tde.fol_folio as folio_entrada,
			        ta.ClaveEtiqueta as contenedor,
              IF(IF(a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '') LIKE '%0000%', '', IF(a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '')) AS caducidad,
              IF(a.control_lotes = 'S', c_lotes.LOTE,'') as lote,
              IF(a.control_numero_series = 'S', tde.cve_lote,'') as numero_serie,
              #IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}' AND Ubicada = 'N'), tde.CantidadRecibida, ta.Cantidad) AS cantidad_recibida,

              #IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}'), 
               #IF(CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT CONCAT(Cve_Articulo, Cve_Lote) FROM td_entalmacencaja WHERE Fol_Folio = '{$folio}' AND Id_Caja = ch_caja.IDContenedor), (SELECT PzsXCaja FROM td_entalmacencaja WHERE Fol_Folio = '{$folio}' AND Id_Caja = ch_caja.IDContenedor), tde.CantidadRecibida),
               #IF(CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}') AND CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT CONCAT(Cve_Articulo, Cve_Lote) FROM td_entalmacencaja WHERE Fol_Folio = '{$folio}' AND Id_Caja = ch_caja.IDContenedor), (SELECT PzsXCaja FROM td_entalmacencaja WHERE Fol_Folio = '{$folio}' AND Id_Caja = ch_caja.IDContenedor),ta.Cantidad)
               #) AS cantidad_recibida,
              IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT DISTINCT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$folio'), 
               IF(CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT DISTINCT CONCAT(Cve_Articulo, Cve_Lote) FROM td_entalmacencaja WHERE Fol_Folio = '$folio' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote), (SELECT DISTINCT SUM(PzsXCaja) FROM td_entalmacencaja WHERE Fol_Folio = '$folio' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote), tde.CantidadRecibida), 
               IF(CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT DISTINCT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$folio') AND CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT DISTINCT CONCAT(Cve_Articulo, Cve_Lote) FROM td_entalmacencaja WHERE Fol_Folio = '$folio' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote), (SELECT DISTINCT SUM(PzsXCaja) FROM td_entalmacencaja WHERE Fol_Folio = '$folio' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote),ta.Cantidad)
               ) AS cantidad_recibida,

              IFNULL(tc.Ubicada, '') AS Ubicada,

              date_format(fecha_inicio, '%d-%m-%Y') as fecha_recepcion,
              #(SELECT MIN(DATE_FORMAT(fecha_inicio, '%d-%m-%Y %h:%i:%s %p')) FROM td_entalmacen WHERE tde.num_orden = td_entalmacen.num_orden) AS fecha_inicio,
              MIN(DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p')) AS fecha_inicio,
              #(SELECT MAX(DATE_FORMAT(fecha_fin, '%d-%m-%Y %h:%i:%s %p')) FROM td_entalmacen WHERE tde.num_orden = td_entalmacen.num_orden) AS fecha_fin,
              MIN(DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p')) AS fecha_fin,
              tda.cantidad - tde.CantidadRecibida as cantidad_faltante,
              IF(tde.CantidadDisponible-tde.CantidadRecibida<0, '0', tde.CantidadDisponible-tde.CantidadRecibida) as cantidad_danada,    
              #IFNULL((tde.CantidadRecibida - tde.CantidadUbicada), tde.CantidadRecibida) AS CantidadDisponible,
              IFNULL(((IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}' AND Ubicada = 'N'), tde.CantidadRecibida, ta.Cantidad)) - (IF(IFNULL(ta.Ubicada, 'S') = 'S', IFNULL(IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}'  AND fol_folio = tde.fol_folio AND tde.cve_articulo = cve_articulo AND IFNULL(tde.cve_lote, '') = IFNULL(cve_lote, '') AND Ubicada = 'N'), tde.CantidadUbicada, ta.Cantidad), 0), 0))), tde.CantidadRecibida) AS CantidadDisponible,
              #IFNULL(tde.CantidadUbicada, 0) as CantidadUbicada,
              IF(IFNULL(ta.Ubicada, 'S') = 'S', IFNULL(IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}'  AND fol_folio = tde.fol_folio AND tde.cve_articulo = cve_articulo AND IFNULL(tde.cve_lote, '') = IFNULL(cve_lote, '') AND Ubicada = 'N'), tde.CantidadUbicada, ta.Cantidad), 0), 0) AS CantidadUbicada,
              u.cve_usuario as usuario,
              tde.id,
							IF(c_charolas.CveLP != '',c_charolas.CveLP, '') as pallet
      FROM td_entalmacen tde
          LEFT JOIN td_aduana tda on tda.cve_articulo = tde.cve_articulo and tda.num_orden = tde.num_orden
          LEFT JOIN c_articulo a on a.cve_articulo = tde.cve_articulo
          LEFT JOIN c_usuario u on u.cve_usuario = tde.cve_usuario
          LEFT JOIN th_entalmacen on th_entalmacen.Fol_Folio = tde.fol_folio
          LEFT JOIN c_lotes on c_lotes.LOTE = tde.cve_lote AND c_lotes.cve_articulo = tde.cve_articulo
          LEFT JOIN c_serie ON c_serie.numero_serie = tde.cve_lote AND c_serie.cve_articulo = tde.cve_articulo
          LEFT JOIN td_entalmacenxtarima ta on ta.fol_folio = tde.fol_folio and tde.cve_articulo = ta.cve_articulo and tde.cve_lote = ta.cve_lote #and tde.CantidadRecibida = ta.Cantidad
					LEFT JOIN c_charolas on c_charolas.clave_contenedor = ta.ClaveEtiqueta
          LEFT JOIN c_tipocaja cc ON cc.id_tipocaja = a.tipo_caja
          LEFT JOIN td_entalmacencaja tc ON tc.Fol_Folio = tde.fol_folio AND tc.Cve_Articulo = a.cve_articulo AND IFNULL(tc.Cve_Lote, '') = IFNULL(tde.cve_lote, '')
          LEFT JOIN c_charolas ch_caja ON ch_caja.IDContenedor = tc.Id_Caja
      WHERE tde.fol_folio = '{$folio}'
      GROUP BY EtiquetaCaja, contenedor, clave, lote, numero_serie
      #ORDER BY clave ASC
  ";
  // hace una llamada previa al procedimiento almacenado Lis_Facturas
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $response = ['success' => true, 'data'=>[]];

  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map("utf8_encode", $row );
    extract($row);

    if($EtiquetaCaja != "") 
    {
        //$Caja = 1; 
        //$CantidadUbicada = 0;
        if($Ubicada == 'N') {$CantidadUbicada = 0;$CantidadDisponible = $cantidad_recibida;} else {$CantidadUbicada = $cantidad_recibida; $CantidadDisponible = 0;}
        //$cantidad_acomodada = 0; 
        //$Piezas = 0;
    }

    $linea = array_search($clave, $lineas) + 1;
    $response['data'][] = array(
      'folio_entrada'      => $folio_entrada,
      'linea'              => $linea,
      'contenedor'         => $contenedor,
      'pallet'             => $pallet,
      'cj'                 => $EtiquetaCaja,
      'clave'              => $clave,
      'descripcion'        => $descripcion,
      'lote'               => $lote,
      'caducidad'          => $caducidad,
      'numero_serie'       => $numero_serie,
      'cantidad_pedida'    => $cantidad_pedida, 
      'cantidad_recibida'  => $cantidad_recibida,
      'fecha_recepcion'    => $fecha_recepcion,
      'cantidad_danada'    => $cantidad_danada,
      'fecha_fin'          => $fecha_fin,
      'CantidadDisponible' => $CantidadDisponible,
      'CantidadUbicada'    => $CantidadUbicada,
      'cantidad_faltante'  => $cantidad_faltante,
      'id'                 => $id
    );
  }
  echo json_encode($response);
}