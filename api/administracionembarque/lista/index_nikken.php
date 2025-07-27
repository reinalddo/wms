<?php
include '../../../config.php';

error_reporting(0);

include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) 
{
    exit();
}

if (isset($_POST) && ! empty($_POST) && $_POST['action'] == 'cargarIslas') 
{
/*
   if($_POST["ruta"] != "")
   {
    $ruta = $_POST["ruta"];
     $sql="
         SELECT 
             t_clientexruta.clave_cliente, 
             th_pedido.Fol_folio, 
             th_pedido.status,
             rel_uembarquepedido.cve_ubicacion,
             t_ubicacionembarque.ID_Embarque as id,
             t_ubicacionembarque.descripcion
         from t_clientexruta 
             LEFT JOIN th_pedido on th_pedido.Cve_clte = t_clientexruta.clave_cliente
             LEFT JOIN rel_uembarquepedido on rel_uembarquepedido.fol_folio = th_pedido.Fol_folio
             LEFT JOIN t_ubicacionembarque on t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion
         WHERE th_pedido.status = 'C' and (t_clientexruta.clave_ruta = $ruta OR th_pedido.cve_ubicacion = $ruta);
     ";
   }
   else
   {
*/
    $sql = "
        SELECT 
            embarq.ID_Embarque id,
            embarq.descripcion
        FROM t_ubicacionembarque embarq
            LEFT JOIN rel_uembarquepedido rel ON rel.cve_ubicacion = embarq.cve_ubicacion
            LEFT JOIN th_pedido ped ON rel.fol_folio = ped.Fol_folio
            LEFT JOIN th_subpedido sp ON sp.fol_folio = rel.fol_folio
        WHERE ped.status = 'C' 
        #AND sp.status = 'C'
        AND (SELECT GROUP_CONCAT(DISTINCT STATUS SEPARATOR '') FROM th_subpedido WHERE Fol_folio = ped.Fol_folio) = 'C' 
            AND embarq.Activo = 1  AND  AreaStagging = 'N' 
            #AND (embarq.status = 2 or embarq.status = 3)  
            AND embarq.cve_ubicacion IN (SELECT cve_ubicacion FROM rel_uembarquepedido) 
            AND embarq.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '".$_POST['almacen']."')
        GROUP BY embarq.ID_Embarque;
    ";
   //}
//   echo var_dump($sql);
//   die();
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $islas = [];
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map('utf8_encode', $row);
    $islas[] = ['id'=>$row['id'],'descripcion' =>$row['descripcion']];
  }
  echo json_encode(['status'=>true,'data'=>$islas, 'sql'=>$sql]);
  exit;
}

if (isset($_POST) && ! empty($_POST) && $_POST['action'] == 'traer_contenedores') 
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

  $sql = 
  "
      SELECT 
          c_charolas.descripcion as descripcion,
          c_charolas.clave_contenedor as contenedor
      FROM c_charolas 
      WHERE not exists 
      (SELECT NULL
      FROM td_entalmacenxtarima
      WHERE td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor)
  ";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  
  $contenedores = [];
  while ($row = mysqli_fetch_array($res)) 
  {
      $row = array_map('utf8_encode', $row);
      $contenedores[] = ['contenedor'=>$row['contenedor'],'descripcion' =>$row['descripcion']]; 
  }
  echo json_encode(['status'=>true,'data'=>$contenedores]);
  exit;
  /*echo var_dump($sql);
  $responce = mysqli_fetch_array($res);
  $responce->sql = $sql ;
  echo json_encode($responce);exit;*/
}

if (isset($_POST) && ! empty($_POST) && $_POST['action'] == 'cargarRutas') 
{
    $almacen = $_POST['almacen'];
    $transporte = $_POST['transporte'];
    /*
    $sql = //EDG
    "SELECT 
        t_clientexruta.clave_ruta as id,
        t_ruta.descripcion 
      FROM `th_pedido` 
      LEFT JOIN Rel_PedidoDest on Rel_PedidoDest.Fol_Folio = th_pedido.Fol_folio 
      LEFT JOIN c_destinatarios on c_destinatarios.id_destinatario = Rel_PedidoDest.Id_Destinatario
      LEFT JOIN c_cliente on c_cliente.Cve_Clte = th_pedido.Cve_clte
      LEFT JOIN t_clientexruta on t_clientexruta.clave_cliente = c_destinatarios.id_destinatario
      LEFT JOIN t_ruta on t_ruta.ID_Ruta = t_clientexruta.clave_ruta
      LEFT JOIN rel_uembarquepedido on rel_uembarquepedido.fol_folio = th_pedido.Fol_folio
      LEFT JOIN t_ubicacionembarque on t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion
      LEFT JOIN th_cajamixta on th_cajamixta.fol_folio = th_pedido.Fol_folio
      LEFT JOIN c_tipocaja on c_tipocaja.id_tipocaja = th_cajamixta.cve_tipocaja 
      WHERE 1
        AND th_pedido.status = 'C'
        AND t_clientexruta.clave_ruta IS NOT NULL
      GROUP by t_clientexruta.clave_ruta";
*/

      $sqlRutasEntrega = "";
/*
    if($_SERVER['HTTP_HOST'] == 'wms.sctp.assistpro-adl.com' || $_SERVER['HTTP_HOST'] == 'www.wms.sctp.assistpro-adl.com')
    {
        $sqlRutasEntrega = " AND r.venta_preventa = 2 ";
    }
*/
    //OR r.ID_Ruta IN (SELECT id_ruta_entrega FROM rel_RutasEntregas WHERE id_ruta_entrega IN (SELECT ruta FROM th_pedido WHERE STATUS = 'C' AND Fol_folio = p.Fol_folio) OR id_ruta_venta_preventa IN (SELECT re.ID_Ruta FROM th_pedido th INNER JOIN t_ruta re ON re.cve_ruta = th.cve_ubicacion WHERE th.status = 'C' AND th.Fol_folio = p.Fol_folio)) 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $SQLTransporte = "";
    if($transporte != '')
    {
        $SQLTransporte = " AND rt.id_transporte = '{$transporte}' "; 
    }

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    //$sql_rutas_sin_pedidos = "";
    if($instancia == 'dicoisa' || $instancia == 'sumiquim')
        $sql_rutas_sin_pedidos = "

    UNION

SELECT DISTINCT r.ID_Ruta AS id, CONCAT(r.cve_ruta,' | ', r.descripcion) AS descripcion
FROM t_ruta r
WHERE r.cve_almacenp = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND r.ID_Ruta NOT IN (SELECT IFNULL(ruta, '') FROM th_pedido WHERE cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')) AND r.cve_ruta NOT IN (SELECT IFNULL(cve_ubicacion, '') FROM th_pedido WHERE cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')) 
";

    $sql = "SELECT DISTINCT r.ID_Ruta AS id, CONCAT(r.cve_ruta,' | ', r.descripcion) as descripcion
FROM th_pedido p 
LEFT JOIN c_cliente c ON c.Cve_Clte = p.Cve_clte
LEFT JOIN c_destinatarios d ON d.Cve_Clte = c.Cve_Clte
LEFT JOIN t_clientexruta cr ON cr.clave_cliente = d.id_destinatario
LEFT JOIN t_ruta r ON r.ID_Ruta = IFNULL(cr.clave_ruta, IFNULL(p.ruta, p.cve_ubicacion)) OR r.cve_ruta = IFNULL(p.cve_ubicacion, '')
LEFT JOIN Rel_Ruta_Transporte rt ON rt.cve_ruta = r.cve_ruta
WHERE p.status = 'C' AND r.ID_Ruta IS NOT NULL AND p.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')
{$SQLTransporte}

UNION

SELECT DISTINCT r.ID_Ruta AS id, CONCAT(r.cve_ruta,' | ', r.descripcion) AS descripcion
FROM th_pedido p 
LEFT JOIN t_ruta rc ON rc.cve_ruta = p.cve_ubicacion
LEFT JOIN t_ruta r ON r.ID_Ruta IN (SELECT id_ruta_entrega FROM rel_RutasEntregas)
LEFT JOIN rel_RutasEntregas re ON re.id_ruta_entrega = r.ID_Ruta 
LEFT JOIN Rel_Ruta_Transporte rt ON rt.cve_ruta = r.cve_ruta
WHERE p.status = 'C' AND r.ID_Ruta IS NOT NULL AND IFNULL(p.ruta, rc.ID_Ruta) = re.id_ruta_venta_preventa
{$SQLTransporte}

{$sql_rutas_sin_pedidos}
";


#p.Cve_clte IS NOT NULL AND p.Cve_clte != '' AND 

      //ID_Ruta as id, 
      //cve_ruta as id, 
/*
    $sql = "
        SELECT 
            ID_Ruta as id, 
            descripcion 
        FROM t_ruta 
            inner join c_almacenp on c_almacenp.id= t_ruta.cve_almacenp
        WHERE c_almacenp.clave = '{$_POST['almacen']}';
    ";
*/

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $rutas = [];
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $rutas[] = ['clave'=>$row['id'],'descripcion' =>$row['descripcion']];
    }
    echo json_encode(['status'=>true,'data'=>$rutas, 'query'=>$sql]);
    exit;
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'DatosTransporte') 
{
    $folio_orden = $_POST['folio_orden'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '{$folio_orden}'";
    $res = mysqli_query($conn, $sql); 

    $folios  = array();
    $sufijos = array();

    $peso = 0;
    $volumen = 0;
    $folios_storage = "";
    $clientes_storage = "";
    $rango_storage = "";
    $peso_storage = "";
    $volumen_storage = "";

    while ($row = mysqli_fetch_array($res))
    {
        //$folio = explode("-", $row['Fol_folio']);
        //array_push($folios, $folio[0]);
        //array_push($sufijos, $folio[1]);
        $folio = $row['Fol_folio'];
        $fol = $folio;
        //$suf = $folio[1];

        $sql = "SELECT SUM(peso) AS peso, TRUNCATE((alto/1000)*(fondo/1000)*(ancho/1000), 4) AS volumen FROM c_articulo WHERE cve_articulo IN (SELECT Cve_articulo FROM td_subpedido WHERE fol_folio = '{$fol}')";
        $res_while = mysqli_query($conn, $sql); 
        $row_wh1 = mysqli_fetch_array($res_while);

        $sql = "SELECT SUM(Num_Revisda) AS Cantidad FROM td_subpedido WHERE fol_folio = '{$fol}'";
        $res_while = mysqli_query($conn, $sql); 
        $row_wh2 = mysqli_fetch_array($res_while);

        $sql = "SELECT Cve_Clte, rango_hora FROM th_pedido WHERE Fol_folio = '{$fol}'";
        $res_while = mysqli_query($conn, $sql); 
        $row_wh3 = mysqli_fetch_array($res_while);

        $folios_storage .= "" . $row['Fol_folio'] . ",";
        $clientes_storage .= "" . $row_wh3['Cve_Clte'] . ",";
        $rango_storage .= "" . $row_wh3['rango_hora'] . ",";

        $peso += ($row_wh1['peso']*$row_wh2['Cantidad']);
        $volumen += ($row_wh1['volumen']*$row_wh2['Cantidad']);

        $peso_storage .= "" . ($row_wh1['peso']*$row_wh2['Cantidad']) . ",";
        $volumen_storage .= "" . ($row_wh1['volumen']*$row_wh2['Cantidad']) . ",";

    }

    echo json_encode([
      'status' => 200, 
      'folios_storage' => $folios_storage,
      'clientes_storage' => $clientes_storage,
      'rango_storage' => $rango_storage,
      'peso_storage' => $peso_storage,
      'volumen_storage' => $volumen_storage,
      'volumen' => $volumen,
      'peso' => $peso
    ]);
    exit;
}

if (isset($_POST) && ! empty($_POST) && $_POST['action'] == 'cargarTransportes') 
{
/*
    $sql = "
            SELECT * FROM t_transporte
            inner join tipo_transporte on t_transporte.tipo_transporte = tipo_transporte.clave_ttransporte;
    ";
*/

    $almacen = $_POST['almacen'];
    $ruta = $_POST['ruta'];
    $SQLRuta = "";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $control_pallets_cont = "N";
    $sql_pendientes = "0 AS peso_pendiente, 
    0 AS volumen_pendiente 
    ";
    if($ruta) 
    {
        //OR '{$ruta}' IN (SELECT id_ruta_entrega FROM rel_RutasEntregas)
        $SQLRuta = " AND t.id IN (SELECT o.id_transporte FROM Rel_Ruta_Transporte o WHERE o.cve_ruta = (SELECT cve_ruta from t_ruta where ID_Ruta = {$ruta}))";
        $sql_datos_ruta = "SELECT * FROM t_ruta WHERE ID_Ruta = {$ruta}";
        if (!($res = mysqli_query($conn, $sql_datos_ruta))) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row_control = mysqli_fetch_array($res);
        $control_pallets_cont = $row_control['control_pallets_cont'];

        $sql_pendientes = "
    (SELECT SUM(pendiente.peso) AS peso_pendiente
    FROM (
        SELECT
            IFNULL(TRUNCATE(SUM(DISTINCT c_articulo.peso*td_surtidopiezas.Cantidad),4), 0) AS peso
      FROM th_ordenembarque
            LEFT JOIN td_ordenembarque tdo ON tdo.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN td_surtidopiezas ON td_surtidopiezas.fol_folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_surtidopiezas.Cve_articulo
        LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            LEFT JOIN th_consolidado ON th_consolidado.Fol_PedidoCon = th_ordenembarque.ID_OEmbarque
            LEFT JOIN cat_estados ON cat_estados.ESTADO = th_ordenembarque.status
            LEFT JOIN t_transporte ON t_transporte.id = th_ordenembarque.ID_Transporte
            LEFT JOIN tipo_transporte ON tipo_transporte.clave_ttransporte = t_transporte.tipo_transporte
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = rel.Id_Destinatario
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = c_destinatarios.id_destinatario
            LEFT JOIN th_pedido th ON th.status IN ('T','F') AND tdo.Fol_folio = th.Fol_folio
            LEFT JOIN t_ruta r ON r.ID_Ruta = th_ordenembarque.Id_Ruta 
      WHERE th_ordenembarque.Activo = 1 
       AND r.ID_Ruta = '{$ruta}'  AND th_ordenembarque.status = 'T'  AND t_transporte.id = t.id
      AND th.status = 'T' 
      GROUP BY th_ordenembarque.ID_OEmbarque
    ) AS pendiente ) AS peso_pendiente, 

    (SELECT SUM(pendiente.volumen) AS volumen_pendiente
    FROM (
        SELECT
            IFNULL(TRUNCATE(SUM(((c_articulo.alto/1000) * (c_articulo.ancho/1000) * (c_articulo.fondo/1000))*td_surtidopiezas.Cantidad), 4), 0) AS volumen
      FROM th_ordenembarque
            LEFT JOIN td_ordenembarque tdo ON tdo.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN td_surtidopiezas ON td_surtidopiezas.fol_folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_surtidopiezas.Cve_articulo
        LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            LEFT JOIN th_consolidado ON th_consolidado.Fol_PedidoCon = th_ordenembarque.ID_OEmbarque
            LEFT JOIN cat_estados ON cat_estados.ESTADO = th_ordenembarque.status
            LEFT JOIN t_transporte tr ON tr.id = th_ordenembarque.ID_Transporte
            LEFT JOIN tipo_transporte ON tipo_transporte.clave_ttransporte = tr.tipo_transporte
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = rel.Id_Destinatario
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = c_destinatarios.id_destinatario
            LEFT JOIN th_pedido th ON th.status IN ('T','F') AND tdo.Fol_folio = th.Fol_folio
            LEFT JOIN t_ruta r ON r.ID_Ruta = th_ordenembarque.Id_Ruta 
      WHERE th_ordenembarque.Activo = 1 
       AND r.ID_Ruta = '{$ruta}'  AND th_ordenembarque.status = 'T'  AND tr.id = t.id
      AND th.status = 'T' 
      GROUP BY th_ordenembarque.ID_OEmbarque
    ) AS pendiente ) AS volumen_pendiente
        ";

    }

  $sql = "
      SELECT DISTINCT t.id, t.ID_Transporte, LENGTH(t.Nombre) size, t.Nombre, t.Placas, t.cve_cia, t.Activo, t.tipo_transporte, 
             ti.id AS tipo_id, ti.clave_ttransporte, ti.alto, ti.fondo, ti.ancho, ti.capacidad_carga, 
             ti.desc_ttransporte, ti.imagen, ti.Activo, '' AS ID_OEmbarque, t.num_ec,
             {$sql_pendientes}
      FROM t_transporte t
      , tipo_transporte ti
      WHERE t.tipo_transporte = ti.clave_ttransporte AND t.Activo = 1 AND ((t.id NOT IN (SELECT o.ID_Transporte FROM th_ordenembarque o WHERE o.status = 'T') OR t.id IN (SELECT o.ID_Transporte FROM th_ordenembarque o WHERE o.status = 'F')) OR t.transporte_externo = 1 OR t.id IN (SELECT id_transporte FROM Rel_Ruta_Transporte WHERE cve_ruta IN (SELECT cve_ruta FROM t_ruta WHERE venta_preventa = 1))) AND t.id_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') {$SQLRuta}";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $transportes = [];
    $max = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $transportes[] = [
          'id'=>$row['id'],
          'nombre' =>$row['Nombre'],
          'capacidad_carga' =>$row['capacidad_carga'],
          'fondo' =>$row['fondo'],
          'Placas' =>$row['Placas'],
          'ancho' =>$row['ancho'],
          'alto' =>$row['alto'],
          'num_ec' =>$row['num_ec'],
          'ID_OEmbarque' =>$row['ID_OEmbarque'],
          'ID_Transporte' =>$row['ID_Transporte'],
          'descripcion' =>$row['desc_ttransporte'],
          'peso_pendiente' =>number_format(((($row['peso_pendiente'])/(($row['capacidad_carga'] > 0)?($row['capacidad_carga']):1))*100), 2),
          'volumen_pendiente' =>number_format((($row['volumen_pendiente'])/((($row['fondo']/1000)*($row['ancho']/1000)*($row['alto']/1000))?(($row['fondo']/1000)*($row['ancho']/1000)*($row['alto']/1000)):1)*100), 2)
        ];

        if($row['size'] > $max)
            $max = $row['size'];
    }

    echo json_encode([
        'status'=>true,
        'data'=>$transportes, 
        'max'=>$max,
        'query'=>$sql, 
        'control_pallets_cont'=>$control_pallets_cont
    ]);
    exit;
}
if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'verificarOrdenDeEmbarque')
{
  $almacen = $_POST['almacen'];
  $isla = $_POST['isla'];
  $ruta = $_POST['ruta'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

  $sql = "SELECT MAX(ID_OEmbarque) as x FROM th_ordenembarque";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($res);
  $id = $row['x'];
  $debug=0;  

  $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($res);
  $cve_almac = $row["id"];

  $and = "";
  if($texto != '')
  {
    $and .= "AND rel_uembarquepedido.fol_folio like'%{$texto}%' ";
  }
  if($isla != '')
  {
    $and .= "AND t_ubicacionembarque.cve_ubicacion = '{$isla}' ";
  }

  if($ruta != '')
  {
    $and .= "AND t_clientexruta.clave_ruta = '{$ruta}' ";
  }

  $sql = 
  "SELECT 
        th_pedido.Fol_folio as folio, 
        th_pedido.Cve_clte as cliente, 
        Rel_PedidoDest.Id_Destinatario as id_destinatario, 
        c_cliente.RazonSocial as razonsocial,
        c_destinatarios.direccion as Direccion_Cliente,
        c_cliente.id_cliente as id_cliente,
        c_destinatarios.cve_Clte as clave_sucursal,
        t_clientexruta.clave_ruta as id_ruta,
        t_ruta.descripcion as ruta,
        rel_uembarquepedido.cve_ubicacion as cve_ubica_embarque,
        t_ubicacionembarque.descripcion as isla,

        IFNULL((SELECT COUNT(Guia) FROM th_cajamixta WHERE fol_folio = rel_uembarquepedido.fol_folio),0) guias,
        IFNULL((SELECT SUM(Peso) FROM th_cajamixta WHERE fol_folio = rel_uembarquepedido.fol_folio),0) peso,
        TRUNCATE(max(th_cajamixta.NCaja)*((c_tipocaja.largo/1000)*(c_tipocaja.alto/1000)*(c_tipocaja.ancho/1000)),3) as volumen,
        (select sum(revisadas) as x from td_surtidopiezas where td_surtidopiezas.fol_folio = rel_uembarquepedido.fol_folio) as piezas,
        th_pedido.status

      FROM `th_pedido` 
      LEFT JOIN Rel_PedidoDest on Rel_PedidoDest.Fol_Folio = th_pedido.Fol_folio 
      LEFT JOIN c_destinatarios on c_destinatarios.id_destinatario = Rel_PedidoDest.Id_Destinatario
      LEFT JOIN c_cliente on c_cliente.Cve_Clte = th_pedido.Cve_clte
      LEFT JOIN t_clientexruta on t_clientexruta.clave_cliente = c_destinatarios.id_destinatario
      LEFT JOIN t_ruta on t_ruta.ID_Ruta = t_clientexruta.clave_ruta
      LEFT JOIN rel_uembarquepedido on rel_uembarquepedido.fol_folio = th_pedido.Fol_folio
      LEFT JOIN t_ubicacionembarque on t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion

      LEFT JOIN th_cajamixta on th_cajamixta.fol_folio = th_pedido.Fol_folio
      LEFT JOIN c_tipocaja on c_tipocaja.id_tipocaja = th_cajamixta.cve_tipocaja 

      WHERE 1
        {$and}
        AND th_pedido.status = 'C'
      GROUP by th_pedido.Fol_folio";
  /*
  "
    SELECT 	
      sum(guias) as guias, 
      sum(peso) as peso, 
      sum(volumen) as volumen, 
      sum(piezas) as piezas 
    FROM(
      SELECT 
        IFNULL((SELECT COUNT(Guia) FROM th_cajamixta WHERE fol_folio = rel.fol_folio),0) guias,
        IFNULL((SELECT SUM(Peso) FROM th_cajamixta WHERE fol_folio = rel.fol_folio),0) peso,
        TRUNCATE(max(cm.NCaja)*((tc.largo/1000)*(tc.alto/1000)*(tc.ancho/1000)),3) as volumen,
        sum(surp.Cantidad) as piezas, 
        rel.fol_folio
      FROM rel_uembarquepedido rel
      LEFT JOIN th_pedido ped ON rel.fol_folio = ped.Fol_folio
      LEFT JOIN t_ubicacionembarque emb ON rel.cve_ubicacion = emb.cve_ubicacion
      INNER JOIN th_cajamixta cm on cm.fol_folio = ped.Fol_folio
      INNER JOIN c_tipocaja tc on tc.id_tipocaja = cm.cve_tipocaja
      INNER JOIN td_surtidopiezas surp on surp.fol_folio = rel.fol_folio
      WHERE 1
      {$and}
      AND ped.cve_almac = '{$cve_almac}' 
      AND ped.status = 'C'
      GROUP BY rel.fol_folio
    ) x;
  ";
  */
//   echo var_dump($sql);
//   die();
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  
  $responce;
  $total_guias = 0;
  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map('utf8_encode', $row);
    $responce->rows[$i]['id'] = $row['folio'];
    $folios = "'".$row['folio'] ."',";
    $responce->rows[$i]['cell'] = 
                                  [
                                    '',
                                    '',
                                    $row['folio'],
                                    $row["isla"],
                                    $row['cliente'],
                                    $row['razonsocial'],
                                    $row['id_destinatario'],
                                    $row['clave_sucursal'],
                                    $row['Direccion_Cliente'],
                                    $row['ruta'],
                                    $row['guias'],
                                    $row['peso'],
                                    $row['volumen'],
                                    $row['piezas']
                                  ];
    $i++;
    $total_guias = ($total_guias + $row['guias']);
  }
  
  echo json_encode($total_guias);exit;
  
//   $row = mysqli_fetch_array($res);
//   echo json_encode(['orden' => $id, 'debug' => $debug, 'peso' => $row["peso"], 'guias' => $row["guias"], 'volumen' => $row["volumen"], 'piezas' => $row["piezas"] ]);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'getDataPDF')
{
    $id = $_POST['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    //mysqli_set_charset($conn, 'utf8');

    $sqlHeader = "
        SELECT  
            p.Fol_folio AS id,
            COALESCE(DATE_FORMAT(p.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_embarque,
            '--' AS fecha_entrega,
            COALESCE(concat(d.direccion,' ',d.colonia,' ',d.postal,' ',d.ciudad,' ',d.estado), '--') AS destino,
            COALESCE(p.Observaciones, '--') AS comentarios,
            '--' AS chofer,
            '--' AS transporte,
            cli.RazonSocial AS cliente,
            COALESCE(cat_estados.DESCRIPCION, '--') AS status,
            (SELECT TRUNCATE(COALESCE(SUM(peso), 0),4) FROM c_articulo WHERE cve_articulo IN (select Cve_articulo from td_pedido where td_pedido.Fol_folio = p.Fol_folio)) AS peso,
            TRUNCATE((SELECT COALESCE(SUM((alto/1000) * (ancho/1000) * (fondo/1000)), 0) FROM c_articulo WHERE cve_articulo IN (select Cve_articulo from td_pedido where td_pedido.Fol_folio = p.Fol_folio)),4) AS volumen,
            (SELECT COALESCE(COUNT(NCaja), 0) FROM th_cajamixta WHERE fol_folio =p.Fol_folio) AS total_cajas,
            #(SELECT TRUNCATE(COALESCE(SUM(Cantidad), 0),0) FROM td_surtidopiezas WHERE fol_folio = p.Fol_folio) AS total_piezas
            (SELECT TRUNCATE(COALESCE(SUM(Num_cantidad), 0),0) FROM td_pedido WHERE Fol_folio = p.Fol_folio) AS total_piezas
        FROM th_pedido p      
            LEFT JOIN Rel_PedidoDest on Rel_PedidoDest.Fol_Folio = p.Fol_folio
            LEFT JOIN c_destinatarios d on d.id_destinatario = Rel_PedidoDest.Id_Destinatario
            LEFT JOIN cat_estados on cat_estados.ESTADO = p.status
            LEFT JOIN c_cliente cli ON cli.Cve_Clte = p.Cve_clte
        WHERE p.Fol_folio = '{$id}';
    ";
    $queryHeader = mysqli_query($conn, $sqlHeader);
    $sqlBody = "
        SELECT  
            a.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            p.Num_cantidad AS cantidad,
            COALESCE(l.LOTE, '--') AS lote,
            COALESCE(l.CADUCIDAD, '--') AS caducidad,
            COALESCE(s.numero_serie, '--') AS serie,
            TRUNCATE(a.costoPromedio,4) AS costoPromedio,
            TRUNCATE((costoPromedio*p.Num_cantidad),4) subtotal                    
        FROM td_pedido p
            LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
            LEFT JOIN c_lotes l ON l.LOTE = p.cve_lote AND l.cve_articulo = p.Cve_articulo
            LEFT JOIN c_serie s ON s.cve_articulo = p.Cve_articulo
        WHERE p.Fol_folio  = '{$id}';
    ";
    $queryBody = mysqli_query($conn, $sqlBody);
    $sqlToal = "
        SELECT  
            p.Num_cantidad AS cantidad,
            a.costoPromedio AS costoPromedio,
            (costoPromedio*Num_cantidad) subtotal,
            TRUNCATE(sum(costoPromedio*Num_cantidad),4) as Total
        FROM td_pedido p
            LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
            LEFT JOIN c_lotes l ON l.LOTE = p.cve_lote AND l.cve_articulo = p.Cve_articulo
            LEFT JOIN c_serie s ON s.cve_articulo = p.Cve_articulo
        WHERE p.Fol_folio = '{$id}';
    ";
    $queryTotal = mysqli_query($conn, $sqlToal);
  
    $header = mysqli_fetch_all($queryHeader, MYSQLI_ASSOC)[0];
    $body = mysqli_fetch_all($queryBody, MYSQLI_ASSOC);
    $total = mysqli_fetch_all($queryTotal, MYSQLI_ASSOC);
    mysqli_close($conn);
  
    echo json_encode(array(
        "header"  => $header,
        "body"    => $body,
        "total"   => $total[0]["Total"]
    ));
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totalesPesosGuias')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $folios = $_POST['folios'];
    $sql = "
        select 	
            sum(guias) as guias, 
            sum(peso) as peso, 
            sum(volumen) as volumen, 
            sum(piezas) as piezas 
        from(
            SELECT 
                IFNULL((SELECT COUNT(Guia) FROM th_cajamixta WHERE fol_folio = ped.Fol_folio),0) guias,
                IFNULL((SELECT SUM(Peso) FROM th_cajamixta WHERE fol_folio = ped.Fol_folio),0) peso,
                TRUNCATE(max(cm.NCaja)*((tc.largo/1000)*(tc.alto/1000)*(tc.ancho/1000)),3) as volumen,
                (select sum(revisadas) as x from td_surtidopiezas where td_surtidopiezas.fol_folio = ped.fol_folio) as piezas 
            FROM th_pedido ped
                INNER JOIN th_cajamixta cm on cm.fol_folio = ped.Fol_folio
                INNER JOIN c_tipocaja tc on tc.id_tipocaja = cm.cve_tipocaja
            WHERE ped.Fol_folio in ({$folios})
            GROUP by ped.Fol_folio
        )x;
    ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    echo json_encode(['orden' => $id, 'debug' => $debug, 'peso' => $row["peso"], 'guias' => $row["guias"], 'volumen' => $row["volumen"], 'piezas' => $row["piezas"] ]);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargarGridPrincipal')
{
  
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_page = 0;
    $cliente = $_POST['cliente'];
    $almacen = $_POST['almacen'];
    $isla = $_POST['isla'];
    $texto = $_POST['texto'];
    $ruta = $_POST['ruta'];
    $colonia = $_POST['colonia'];
    $cpostal = $_POST['cpostal'];


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if (!$sidx) {$sidx =1;}
    $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $cve_almac = $row["id"];
    
    //if (intval($page)>0) 
    //{
    //  $_page = ($page-1)*$limit;
    //}

    $num_rutas = 0;

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];


    if($instancia == 'dicoisa' || $instancia == 'sumiquim')
    {
        $sql = "SELECT COUNT(*) as num_rutas FROM t_ruta WHERE ID_Ruta NOT IN (SELECT IFNULL(cve_ubicacion, '') FROM th_pedido WHERE cve_almac = $cve_almac AND STATUS = 'C') AND ID_Ruta = '$ruta'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $num_rutas = $row["num_rutas"];
    }


    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    $and = "";
    if($texto != '')
    {
      $and .= "AND rel_uembarquepedido.fol_folio like'%{$texto}%' ";
    }
    if($isla != '')
    {
      $and .= "AND t_ubicacionembarque.ID_Embarque = '{$isla}' ";
    }
  
    $sql_venta_preventa = ""; $sql_left_join_ruta = ""; $sql_sin_ruta = "";
    if($ruta != '' && $num_rutas == 0)
    {
      //$and .= "AND t_clientexruta.clave_ruta = '{$ruta}' ";
        $sql = "SELECT venta_preventa FROM t_ruta WHERE ID_Ruta = '{$ruta}'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $venta_preventa = $row["venta_preventa"];

        
        if($venta_preventa == 2)
        {
            //$sql_left_join_ruta = " LEFT JOIN rel_RutasEntregas re ON re.id_ruta_venta_preventa = IFNULL(t_ruta.ID_Ruta, rclave.ID_Ruta)  ";
            $sql_left_join_ruta = " LEFT JOIN rel_RutasEntregas re ON re.id_ruta_entrega = IFNULL(t_ruta.ID_Ruta, rclave.ID_Ruta)  ";
            $sql_venta_preventa = " OR t_ruta.ID_Ruta IN (SELECT id_ruta_entrega FROM rel_RutasEntregas) ";
            //$sql_venta_preventa = " OR ('{$ruta}' IN (SELECT id_ruta_entrega FROM rel_RutasEntregas) AND (rclave.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas) OR th_pedido.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas) )) ";
            $and .= " AND ( re.id_ruta_entrega = '$ruta') ";
            #IFNULL(th_pedido.ruta, rclave.ID_Ruta) = re.id_ruta_venta_preventa AND
        }
        else
            $and .= "AND (t_ruta.ID_Ruta = '{$ruta}' OR th_pedido.cve_ubicacion = '{$ruta}' OR th_pedido.ruta = '{$ruta}' OR th_pedido.cve_ubicacion IN (SELECT cve_ruta FROM t_ruta WHERE ID_Ruta = '{$ruta}') {$sql_venta_preventa})";
      
    }
    else if($num_rutas > 0)
    {
        //$sql_sin_ruta = " AND t_ruta.ID_Ruta NOT IN (SELECT ruta FROM th_pedido WHERE cve_almac = $cve_almac) AND t_ruta.cve_ruta NOT IN (SELECT IFNULL(cve_ubicacion, '') FROM th_pedido WHERE cve_almac = $cve_almac) ";
        $sql_sin_ruta = "";
    }
    else
        $sql_sin_ruta = " AND (IFNULL(th_pedido.ruta, '') = '' AND IFNULL(th_pedido.cve_ubicacion, '') = '') ";

    if($cliente != '')
    {
      $and .= "AND (th_pedido.Cve_clte LIKE '%{$cliente}%' OR c_cliente.RazonSocial LIKE '%{$cliente}%') ";
    }
   
    if($colonia != '')
    {
      $and .= "AND c_destinatarios.colonia LIKE '%{$colonia}%' ";
    }

    if($cpostal != '')
    {
      $and .= "AND c_destinatarios.postal LIKE '%{$cpostal}%' ";
    }

    $sql = "
      SELECT 
        ths.fol_folio AS folio, 
        DATE_FORMAT(th_pedido.Fec_Pedido, '%d-%m-%Y') AS Fec_Pedido,
        DATE_FORMAT(th_pedido.Fec_Entrega, '%d-%m-%Y') AS Fec_Entrega,
        IF(DATE(th_pedido.Fec_Entrega) <= CURDATE(), 1, DATE_FORMAT(th_pedido.Fec_Entrega, '%d-%m-%Y')) AS puedo_entregar,
        th_pedido.Cve_clte AS cliente, 
        rel_uembarquepedido.fol_folio AS pedido,
        ths.Sufijo AS Sufijo,
        #IF(IFNULL(th_pedido.cve_ubicacion, '') = '', IF(IFNULL(th_pedido.ruta, '') != '', t_ruta.cve_ruta, IFNULL(Rel_PedidoDest.Id_Destinatario, c_cliente.Cve_Clte)) , (SELECT cve_ruta FROM t_ruta WHERE cve_ruta = th_pedido.cve_ubicacion)) AS id_destinatario, 
        IFNULL(IFNULL(Rel_PedidoDest.Id_Destinatario, c_cliente.Cve_Clte), '') AS id_destinatario, 
        #IF(IFNULL(th_pedido.cve_ubicacion, '') = '', IF(IFNULL(th_pedido.ruta, '') != '', t_ruta.descripcion, IFNULL(c_destinatarios.razonsocial, c_cliente.RazonSocial)), (SELECT descripcion FROM t_ruta WHERE cve_ruta = th_pedido.cve_ubicacion)) AS destinatario,
        IFNULL(IFNULL(c_destinatarios.razonsocial, c_cliente.RazonSocial), '') AS destinatario,
        #IF(IFNULL(th_pedido.cve_ubicacion, '') = '', IF(IFNULL(th_pedido.ruta, '') != '', t_ruta.descripcion, c_cliente.RazonSocial), (SELECT des_cia FROM c_compania WHERE cve_cia = (SELECT cve_cia FROM c_almacenp WHERE id = '$cve_almac'))) AS razonsocial,
        c_cliente.RazonSocial AS razonsocial,
        IF(IFNULL(th_pedido.ruta, '') != '', t_ruta.descripcion, c_cliente.RazonSocial) AS razonsocial2,
        #IF(IFNULL(th_pedido.cve_ubicacion, '') = '', IF(IFNULL(th_pedido.ruta, '') != '', t_ruta.descripcion, IFNULL(c_destinatarios.direccion, c_cliente.CalleNumero)), (SELECT des_direcc FROM c_compania WHERE cve_cia = (SELECT cve_cia FROM c_almacenp WHERE id = '$cve_almac'))) AS Direccion_Cliente,
        IFNULL(IFNULL(c_destinatarios.direccion, c_cliente.CalleNumero), '') AS Direccion_Cliente,
        c_cliente.id_cliente AS id_cliente,
        IFNULL(c_destinatarios.cve_Clte, c_cliente.Cve_Clte) AS clave_sucursal,
        t_ruta.cve_ruta AS id_ruta,
        t_ruta.cve_ruta as ruta,
        rel_uembarquepedido.cve_ubicacion AS cve_ubica_embarque,
        GROUP_CONCAT(DISTINCT t_ubicacionembarque.descripcion SEPARATOR ',') AS isla,
        IFNULL(c_destinatarios.colonia, c_cliente.Colonia) AS Colonia,
        IFNULL(c_destinatarios.postal, c_cliente.CodigoPostal) AS CodigoPostal,
        IFNULL((SELECT COUNT(DISTINCT Guia) FROM th_cajamixta WHERE fol_folio = th_pedido.Fol_folio),0) guias,
        IFNULL((SELECT COUNT(NCaja) FROM th_cajamixta WHERE fol_folio = th_pedido.Fol_folio),0) ncajas,
        TRUNCATE((SELECT SUM(art_p.peso*s_p.Cantidad) peso FROM c_articulo art_p, td_surtidopiezas s_p WHERE art_p.cve_articulo = s_p.Cve_articulo AND s_p.fol_folio = th_pedido.Fol_folio), 2) AS peso,
        TRUNCATE((SELECT SUM((art_p.ancho/1000)*(art_p.alto/1000)*(art_p.fondo/1000) *s_p.Cantidad) peso FROM c_articulo art_p, td_surtidopiezas s_p WHERE art_p.cve_articulo = s_p.Cve_articulo AND s_p.fol_folio = th_pedido.Fol_folio), 2) AS volumen,
        (SELECT SUM(Cantidad) AS X FROM td_surtidopiezas WHERE td_surtidopiezas.fol_folio = th_pedido.Fol_folio) AS piezas,
        c_destinatarios.latitud AS latitud,
        c_destinatarios.longitud AS longitud,
        th_pedido.rango_hora,
        #MAX(th_cajamixta.NCaja) AS total_cajas,
        #COUNT(DISTINCT tt.ntarima) AS total_pallets,
        #(SELECT COUNT(DISTINCT ntarima) FROM t_tarima WHERE Fol_Folio = ths.fol_folio) AS total_pallets,
        0 as total_pallets,
        ths.status

      FROM th_subpedido ths
      LEFT JOIN th_pedido ON th_pedido.Fol_folio = ths.fol_folio
      LEFT JOIN Rel_PedidoDest ON Rel_PedidoDest.Fol_Folio = ths.fol_folio 
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Rel_PedidoDest.Id_Destinatario
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
      LEFT JOIN t_clientexruta tc ON tc.clave_cliente = c_destinatarios.id_destinatario

      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(th_pedido.ruta, tc.clave_ruta) OR IFNULL(t_ruta.cve_ruta, '') = IFNULL(th_pedido.cve_ubicacion, '') {$sql_venta_preventa}
      LEFT JOIN t_ruta rclave ON IFNULL(rclave.cve_ruta, '') = IFNULL(th_pedido.cve_ubicacion, '') 
       {$sql_left_join_ruta} 
      LEFT JOIN rel_uembarquepedido ON rel_uembarquepedido.fol_folio = ths.fol_folio #AND IF(rel_uembarquepedido.Sufijo = 0, 1,rel_uembarquepedido.Sufijo) = ths.Sufijo
      LEFT JOIN t_ubicacionembarque ON t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion
      #LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio = ths.fol_folio AND th_cajamixta.Sufijo = ths.Sufijo
      #LEFT JOIN t_tarima tt ON th_cajamixta.fol_folio = tt.Fol_Folio AND th_cajamixta.Sufijo = tt.Sufijo
      #LEFT JOIN c_tipocaja ON c_tipocaja.id_tipocaja = th_cajamixta.cve_tipocaja 

      WHERE 1 AND  t_ubicacionembarque.AreaStagging = 'N'
          {$and}
          {$sql_sin_ruta}
          #AND DATE(th_pedido.Fec_Entrega) <= CURDATE() 
          AND rel_uembarquepedido.cve_almac = '$cve_almac' 
          #AND (SELECT COUNT(Fol_folio) FROM td_pedido WHERE td_pedido.Fol_folio = ths.fol_folio AND td_pedido.status = 'C') = (SELECT COUNT(fol_folio) FROM td_subpedido WHERE td_subpedido.fol_folio = ths.fol_folio AND td_subpedido.Status = 'C') 
          #AND (SELECT GROUP_CONCAT(DISTINCT STATUS SEPARATOR '') FROM td_pedido WHERE Fol_folio = ths.fol_folio) = 'C' 
          #AND (SELECT GROUP_CONCAT(DISTINCT STATUS SEPARATOR '') FROM td_subpedido WHERE Fol_folio = ths.fol_folio) = 'C' 
          #AND (SELECT status FROM t_pedido WHERE Fol_folio = ths.fol_folio) = 'C' 
          AND (SELECT GROUP_CONCAT(DISTINCT status SEPARATOR '') FROM th_subpedido WHERE Fol_folio = ths.fol_folio) = 'C' 
          #AND ths.fol_folio NOT IN (SELECT Fol_folio FROM td_ordenembarque)
          AND (ths.fol_folio NOT IN (SELECT Fol_folio FROM td_ordenembarque) OR (SELECT GROUP_CONCAT(DISTINCT Ban_Embarcado SEPARATOR '') AS Ban_Embarcado FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = ths.fol_folio)) != 'S' OR (SELECT GROUP_CONCAT(DISTINCT Ban_Embarcado SEPARATOR '') AS Ban_Embarcado FROM t_tarima WHERE Fol_Folio = ths.fol_folio) != 'S')
      GROUP BY ths.fol_folio
      ORDER BY ths.Fec_Entrada DESC";



    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$start}, {$limit}; ";

    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    }
  
    
    
    $responce;
    $folios = '';
    $total_guias = 0;
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
      $row = array_map('utf8_encode', $row);
        //$row = array_map('utf8_decode', $row);
        //$row = array_map('$charset', $row);


        $folio_pedidos = $row['folio'];
/*
    $sql_total_cajas_tipo1 = "
        SELECT IF(art.num_multiplo>0, IFNULL(TRUNCATE(SUM(td.Cantidad)/art.num_multiplo,0), 0), COALESCE(SUM(1), 0)) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('".$folio_pedidos."') AND art.tipo_caja = th.cve_tipocaja";
    $query_total_cajas_tipo1 = mysqli_query($conn, $sql_total_cajas_tipo1);
    $total_cajas_tipo1 = mysqli_fetch_array($query_total_cajas_tipo1)['Cantidad'];
*/
/*
    $sql_total_cajas_tipo2 = "
        SELECT COALESCE(SUM(1), 0) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('".$folio_pedidos."') AND art.tipo_caja != th.cve_tipocaja";
    $query_total_cajas_tipo2 = mysqli_query($conn, $sql_total_cajas_tipo2);
    $total_cajas_tipo2 = mysqli_fetch_array($query_total_cajas_tipo2)['Cantidad'];

    $total_cajas = $total_cajas_tipo1 + $total_cajas_tipo2;
*/

      $responce->rows[$i]['id'] = $row['folio'];
      $folios = "'".$row['folio'] ."',";
      $responce->rows[$i]['cell'] = 
                                    [
                                      '',
                                      '',
                                      $row['folio'],
                                      $row['Sufijo'],
                                      $row['folio'],
                                      $row['Fec_Pedido'],
                                      $row['Fec_Entrega'],
                                      $row['rango_hora'],
                                      $row['ruta'],
                                      '',
                                      $row['cliente'],
                                      $row['id_destinatario'],

                                      //$row['destinatario'],
                                      utf8_decode($row['destinatario']),

                                      $row['clave_sucursal'],

                                      //$row['Direccion_Cliente'],
                                      utf8_decode($row['Direccion_Cliente']),

                                      $row['CodigoPostal'],

                                      //$row['Colonia'],
                                      utf8_decode($row['Colonia']),

                                      $row['latitud'],

                                      $row['longitud'],
                                      $row['ncajas'],//$total_cajas,
                                      $row['piezas'],
                                      $row['guias'],
                                      $row['peso'],
                                      $row['volumen'],
                                      $row['total_pallets'],
                                      $row['razonsocial'],
                                      $row['razonsocial2'],
                                      //$row["isla"],
                                      utf8_decode($row["isla"]),
                                      $row['puedo_entregar']
                                    ];
      $i++;
      $total_guias = ($total_guias + $row['guias']);
    }
//     echo var_dump($total_guias);
    //die();
    if ($i >0) 
    {
        $total_pages = ceil($i/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages) 
    {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    //$responce->total = $count;
    //$responce->page = $page;
    //$responce->records = $i;
    $responce->guias_totales = $total_guias;
    //$responce->sql = $sql;
    echo json_encode($responce);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'obtenerVolumenCajas') 
{
  $folio = $_POST['folio'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

  $sql = 
  "
    SELECT 
      TRUNCATE(
        SUM(
          CASE WHEN caja.cve_tipocaja = 1 THEN(
            SELECT
              IFNULL(ROUND(SUM(item.Num_cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0) AS volumentotal
            FROM td_pedido item
            LEFT JOIN c_articulo a ON a.cve_articulo = item.Cve_articulo
            WHERE item.Fol_folio = caja.fol_folio
          )
          ELSE
          (
            SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) FROM c_tipocaja WHERE id_tipocaja = caja.cve_tipocaja
          ) 
          END
        ), 4
      ) AS volumen
    FROM th_cajamixta caja
    WHERE caja.fol_folio = '{$folio}';
  ";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  echo json_encode(['volumen' => $row['volumen']]);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargarDetalleCajas') 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows'] ? $_POST['rows'] : 20; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_page = 0;
    $folio = $_POST['folio'];
    $sufijo = $_POST['sufijo'];
    $id_zona = $_POST['id_zona'];
    

    if (!$sidx) {$sidx =1;}
    //if (intval($page)>0) 
    //{
    //    $_page = ($page-1) * $limit;
    //}

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

/*
    $sql = "
        SELECT 
            caja.NCaja,
            t.clave,
            t.descripcion,
            caja.Guia, 
            TRUNCATE(
                (CASE 
                    WHEN caja.cve_tipocaja = 1 THEN
                    (
                        SELECT
                            IFNULL(ROUND(SUM(item.Num_cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0) AS volumentotal
                        FROM td_pedido item
                            LEFT JOIN c_articulo a ON a.cve_articulo = item.Cve_articulo
                        WHERE item.Fol_folio = caja.fol_folio
                    )
                    ELSE
                    (
                        SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) FROM c_tipocaja WHERE id_tipocaja = caja.cve_tipocaja
                    ) 
                END),4
            ) AS volumen,
            TRUNCATE(IFNULL(caja.Peso,0),4) as Peso
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio = '{$folio}';
    ";
*/
    //$sql = "SELECT b.cve_articulo, b.des_articulo, a.Num_cantidad, a.Num_revisadas, a.status, a.cve_lote, a.Fol_folio FROM td_pedido a, c_articulo b WHERE a.Fol_folio = '{$folio}' and a.Cve_articulo = b.cve_articulo";
/*
    $sql = "SELECT DISTINCT
              b.cve_articulo,
              b.des_articulo,
              a.Num_cantidad,
              a.Num_Revisda AS Num_revisadas,
              a.status,
              ts.LOTE AS cve_lote,
              a.Sufijo,
              a.Fol_folio 
            FROM
              td_subpedido a
              LEFT JOIN td_pedido ON td_pedido.Fol_folio = a.fol_folio AND a.Sufijo = '{$sufijo}'
              LEFT JOIN td_surtidopiezas ts ON td_pedido.Fol_folio = ts.fol_folio AND a.Sufijo = ts.Sufijo AND ts.Sufijo = '{$sufijo}'
              ,c_articulo b 
            WHERE a.Fol_folio = '{$folio}' AND a.Sufijo = '{$sufijo}'
              AND a.Cve_articulo = b.cve_articulo AND a.Cve_articulo = ts.Cve_articulo";
*/

              $sql_zona = "";
              if($id_zona != '')
                $sql_zona = " AND t_ubicacionembarque.ID_Embarque = '{$id_zona}'";

                $sql = "
                SELECT * FROM (
                      SELECT * FROM (
                          SELECT DISTINCT
                              tc.Cve_CajaMixD AS id_emb,
                              'tipo_caja_pallet' AS tipo_embarque,
                              tc.Ban_Embarcado AS ban_embarcado,
                              UPPER(b.cve_articulo) AS cve_articulo,
                              d.Sufijo AS Sufijo,
                              b.des_articulo,
                              a.Num_cantidad,
                              ts.cantidad AS surtidas,
                              IF(tc.Num_Empacados = 0, tc.Cantidad, tc.Num_Empacados) AS Num_Empacados,
                              (SELECT descripcion FROM c_tipocaja WHERE id_tipocaja = th.cve_tipocaja) AS tipo_caja,
                              #IFNULL((SELECT descripcion FROM c_tipocaja WHERE id_tipocaja = b.tipo_caja), '') AS tipo_caja,
                              th.NCaja,
                              a.status,
                              ts.LOTE AS cve_lote,
                              IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                              b.control_lotes,
                              b.control_numero_series,
                              IFNULL(b.Caduca, 'N') AS Caduca,
                              (SELECT c_charolas.clave_contenedor FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima) AS pallet,
                              IFNULL((SELECT c_charolas.CveLP FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima), '' ) AS CveLP,
                              a.Fol_folio 
                          FROM
                          td_pedido a
                          LEFT JOIN th_cajamixta th ON th.fol_folio = a.Fol_folio AND (th.cve_tipocaja IN (SELECT id_tipocaja FROM c_tipocaja ) OR (th.cve_tipocaja = 0))
                          LEFT JOIN td_cajamixta tc ON th.Cve_CajaMix = tc.Cve_CajaMix AND tc.Cve_CajaMix IN (SELECT Caja_ref FROM t_tarima WHERE Fol_Folio = '{$folio}' )
                          LEFT JOIN t_tarima tt ON tt.Fol_Folio = a.Fol_folio AND tt.cve_articulo = a.Cve_articulo  AND tt.Caja_ref = tc.Cve_CajaMix #AND tt.cantidad = tc.Cantidad
                          #AND tt.lote = IFNULL(a.cve_lote, '')
                          LEFT JOIN td_subpedido d ON d.Cve_articulo = tt.Cve_articulo AND d.fol_folio = a.Fol_folio
                          LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = d.fol_folio AND ts.Sufijo = d.Sufijo AND ts.Cve_articulo = a.Cve_articulo
                          LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo = c.cve_articulo
                          LEFT JOIN rel_uembarquepedido ON rel_uembarquepedido.fol_folio = ts.fol_folio AND rel_uembarquepedido.Sufijo = ts.Sufijo AND rel_uembarquepedido.fol_folio  = '{$folio}'
                          LEFT JOIN t_ubicacionembarque ON t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion
                          LEFT JOIN c_articulo b ON a.Cve_articulo = b.cve_articulo
                          #,c_articulo b
                          WHERE ts.Fol_folio = '{$folio}' {$sql_zona}
                          AND ts.cve_articulo = tc.Cve_articulo AND ts.LOTE = tc.Cve_Lote
                          GROUP BY pallet,NCaja, cve_articulo
                          ) AS res_tipo1 #WHERE res_tipo1.Num_Empacados != '' #AND res_tipo1.Sufijo = '{$sufijo}'

                          UNION

                          SELECT * FROM (
                              SELECT DISTINCT
                                  tt.Id AS id_emb,
                                  'tipo_pallet' AS tipo_embarque,
                                  tt.Ban_Embarcado AS ban_embarcado,
                                  UPPER(b.cve_articulo) AS cve_articulo,
                                  d.Sufijo AS Sufijo,
                                  b.des_articulo,
                                  a.Num_cantidad,
                                  ts.cantidad AS surtidas,
                                  IF(IFNULL(tt.Num_Empacados, 0) = 0, IFNULL(tt.cantidad, ts.cantidad), tt.Num_Empacados) AS Num_Empacados,
                                  ch.descripcion AS tipo_caja,
                                  #(@i_pallet:=@i_pallet+1) AS NCaja,
                                  0 AS NCaja,
                                  a.status,
                                  ts.LOTE AS cve_lote,
                                  IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                                  b.control_lotes,
                                  b.control_numero_series,
                                  IFNULL(b.Caduca, 'N') AS Caduca,
                                  ch.clave_contenedor AS pallet,
                                  ch.CveLP AS CveLP,
                                  #(SELECT c_charolas.clave_contenedor FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima ) AS pallet,
                                  #IFNULL((SELECT c_charolas.CveLP FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima), '' ) AS CveLP,
                                  a.Fol_folio 
                              FROM
                              td_pedido a
                              LEFT JOIN t_tarima tt ON tt.Fol_Folio = a.Fol_folio AND tt.cve_articulo = a.Cve_articulo AND tt.lote = IFNULL(a.cve_lote, '') AND tt.Caja_ref = 0
                              LEFT JOIN td_subpedido d ON d.Cve_articulo = IFNULL(tt.Cve_articulo, a.Cve_articulo) AND d.fol_folio = a.Fol_folio
                              LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = d.fol_folio AND ts.Sufijo = d.Sufijo AND ts.Cve_articulo = a.Cve_articulo
                              LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo = c.cve_articulo
                              LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
                              LEFT JOIN c_articulo b ON a.Cve_articulo = b.cve_articulo
                              #,c_articulo b
                              WHERE a.Fol_folio = '{$folio}'
                              AND a.Cve_articulo = b.cve_articulo AND tt.Id IS NOT NULL
                              ) AS res_tipo2 #WHERE res_tipo2.Num_Empacados != '' #AND res_tipo2.Sufijo = '{$sufijo}'

                          UNION

                          SELECT * FROM (
                              SELECT DISTINCT
                                  tc.Cve_CajaMixD AS id_emb,
                                  'tipo_caja' AS tipo_embarque,
                                  tc.Ban_Embarcado AS ban_embarcado,
                                  UPPER(b.cve_articulo) AS cve_articulo,
                                  a.Sufijo AS Sufijo,
                                  b.des_articulo,
                                  a.Num_cantidad,
                                  ts.cantidad AS surtidas,
                                  IFNULL(IF(IFNULL(tc.Num_Empacados, 0) = 0, tc.Cantidad, tc.Num_Empacados), a.Num_cantidad) AS Num_Empacados,
                                  IFNULL((SELECT descripcion FROM c_tipocaja WHERE id_tipocaja = th.cve_tipocaja), '') AS tipo_caja,
                                  th.NCaja,
                                  a.status,
                                  ts.LOTE AS cve_lote,
                                  IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                                  b.control_lotes,
                                  b.control_numero_series,
                                  IFNULL(b.Caduca, 'N') AS Caduca,
                                  '' AS pallet,
                                  '' AS CveLP,
                                  a.Fol_folio 
                              FROM
                              td_subpedido a
                              LEFT JOIN th_cajamixta th ON th.fol_folio = a.Fol_folio 
                              LEFT JOIN td_cajamixta tc ON th.Cve_CajaMix = tc.Cve_CajaMix AND tc.Cve_CajaMix NOT IN (SELECT Caja_ref FROM t_tarima WHERE fol_folio = '{$folio}') AND tc.Cve_articulo = a.Cve_articulo AND IFNULL(tc.Cve_Lote, '') = IFNULL(a.Cve_Lote, '')
                              #LEFT JOIN td_subpedido d ON d.Cve_articulo = a.Cve_articulo AND d.fol_folio = a.Fol_folio AND tc.Cve_articulo = a.Cve_articulo
                              LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = a.fol_folio AND ts.Sufijo = a.Sufijo AND ts.Cve_articulo = a.Cve_articulo
                              LEFT JOIN c_lotes c ON IFNULL(c.LOTE, '') = IFNULL(ts.LOTE, '') AND ts.Cve_articulo = c.cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.Cve_Lote, '')
                              LEFT JOIN c_articulo b ON a.Cve_articulo = b.cve_articulo
                              WHERE a.Fol_folio = '{$folio}' AND tc.Cve_CajaMix IS NOT NULL
                              AND th.fol_folio NOT IN (SELECT fol_folio FROM t_tarima) 
                              AND a.Cve_articulo = b.cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(a.Cve_Lote, '')
                              #GROUP BY id_emb, cve_articulo, cve_lote
                              ) AS res_tipo3 WHERE IFNULL(res_tipo3.Num_Empacados, '') != '' #AND res_tipo3.Sufijo = '{$sufijo}'
                              ) AS p 
                              GROUP BY p.id_emb
                              ORDER BY (p.NCaja+0) ASC";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

//    $sql_set = "SET @i_pallet := 0;";
//    if (!($res = mysqli_query($conn, $sql_set))) {
//        echo "Falló la preparación SET: (" . mysqli_error($conn) . ") ";
//    }


    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    if($count == 0)
    {
        $sql = "
            SELECT * FROM (
              SELECT DISTINCT
                  '' AS id_emb,
                  'sin_caja_pallet' AS tipo_embarque,
                  '' AS ban_embarcado,
                  UPPER(b.cve_articulo) AS cve_articulo,
                  d.Sufijo AS Sufijo,
                  b.des_articulo,
                  a.Num_cantidad,
                  ts.cantidad AS surtidas,
                  ts.cantidad AS Num_Empacados,
                  '' AS tipo_caja,
                  #(@i_pallet:=@i_pallet+1) AS NCaja,
                  0 AS NCaja,
                  a.status,
                  ts.LOTE AS cve_lote,
                  IF(b.Caduca = 'S', IFNULL(DATE_FORMAT(c.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                  b.control_lotes,
                  b.control_numero_series,
                  IFNULL(b.Caduca, 'N') AS Caduca,
                  '' AS pallet,
                  '' AS CveLP,
                  #(SELECT c_charolas.clave_contenedor FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima ) AS pallet,
                  #IFNULL((SELECT c_charolas.CveLP FROM c_charolas WHERE c_charolas.IDContenedor = tt.ntarima), '' ) AS CveLP,
                  a.Fol_folio 
              FROM
              td_pedido a
              LEFT JOIN td_subpedido d ON d.Cve_articulo = a.Cve_articulo AND d.fol_folio = a.Fol_folio
              LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = d.fol_folio AND ts.Sufijo = d.Sufijo AND ts.Cve_articulo = a.Cve_articulo
              LEFT JOIN c_lotes c ON c.LOTE = ts.LOTE AND ts.Cve_articulo = c.cve_articulo
              LEFT JOIN c_articulo b ON a.Cve_articulo = b.cve_articulo
              #,c_articulo b
              WHERE a.Fol_folio = '{$folio}'
              AND a.Cve_articulo = b.cve_articulo #AND tt.Id IS NOT NULL
              ) AS res_tipo2 #WHERE res_tipo2.Num_Empacados != '' #AND res_tipo2.Sufijo = '1'
        ";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

    }

    //$sql .= " LIMIT {$start}, {$limit}; ";

    //if (!($res = mysqli_query($conn, $sql))) {
    //    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    //}

    $sql_num_pallets = "SELECT COUNT(DISTINCT ntarima) AS num_pallets FROM t_tarima WHERE fol_folio = '{$folio}'";
    if (!($res_num_pallets = mysqli_query($conn, $sql_num_pallets)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $num_pallets = mysqli_fetch_array($res_num_pallets)['num_pallets'];


    $i = 0;$n_caja_pallet = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        //$row = array_map('utf8_encode', $row);
        //$responce->rows[$i]['id'] = $row['NCaja'];
        //$responce->rows[$i]['cell'] = ['',$i+1,$row['clave'],$row['descripcion'],$row['Guia'],$row['volumen'],$row['Peso']];
        //"Clave", "Artículo", "Lote / No Serie", "Pedidas", "Revisadas", "Status"
        //$responce->rows[$i]['cell'] = ['',$i+1,$row['cve_articulo'],$row['des_articulo'],$row['cve_lote'],$row['Num_cantidad'],$row['Num_revisadas'],$row['status']];

        $lote_serie = ""; $caducidad = "";
        if($row['control_lotes'] == "S")
        {
            $lote_serie = $row['cve_lote'];
            if($row['Caduca'] == 'S')
              $caducidad = $row['Caducidad'];
        }
        else
        {
            $lote_serie = $row['cve_lote'];
        }

        $n_caja = $row['NCaja'];
        if($row['tipo_embarque'] == 'tipo_pallet' || $row['tipo_embarque'] == 'sin_caja_pallet')
        {
            $n_caja_pallet++;
            $n_caja = $n_caja_pallet;
        }
        $responce->rows[$i]['cell'] = ['',$n_caja, $row['tipo_caja'], $row['cve_articulo'],utf8_encode($row['des_articulo']),$lote_serie,$caducidad,$row['Num_Empacados'],$row['pallet'],$row['CveLP'], '', $row['id_emb'], $count, $row['ban_embarcado'], $row['tipo_embarque']];
        $i++;
    }

    //$count = $i;
    if ($count >0) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages) 
    {
        $page=$total_pages;
    }
        $total_pages = 0;
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->num_pallets = $num_pallets;
    $responce->sql_detalle = $sql;

    echo json_encode($responce);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargarDetallePedido') 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows'] ? $_POST['rows'] : 20; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_page = 0;
    $folio = $_POST['folio'];
    $partida = $_POST['partida'];

    if (!$sidx) {$sidx =1;}

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = " SELECT  count(ped.Fol_folio) cuenta FROM  td_pedido ped WHERE Fol_folio = '{$folio}' ";
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
    mysqli_close($conn);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if (intval($page)>0) 
    {
        $_page = ($page-1) * $limit;
    }
    
    $sql = "
        SELECT 
            item.Cve_articulo clave,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '') descripcion,
            item.Cantidad cantidad,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '0'),4) as peso
        FROM 
            td_cajamixta item
        WHERE item.Cve_CajaMix = (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '{$folio}'  and NCaja = {$partida}) ;
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['clave'];
        $responce->rows[$i]['cell'] = [$row['clave'],$row['descripcion'],$row['cantidad'],$row['peso'],];
        $i++;
    }

    $count = $i;
    if ($count >0) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages) 
    {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql ;

    echo json_encode($responce);exit;
}





if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'embarcar')
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

$traslado_reldaycli = 0;
$borrado_de_planeacion = 0;
$sql_dia_track = "";
  $EntregaCliente = $_POST['EntregaCliente'];
  $folios = $_POST['folios'];
  $folios = explode(",", $folios);

  $sufijos = $_POST['sufijos'];
  $sufijos = explode(",", $sufijos);

  $isla = $_POST['isla'];

  $folio_orden = $_POST['folio_orden'];

  $id_embarquexpartes = $_POST['id_embarque'];

  $tipo_embarque = $_POST['tipo_embarque'];

  $guia_transporte = $_POST['guia_transporte'];

  $contacto_embarque = $_POST['contacto_embarque'];

  $chofer = $_POST['chofer'];

  $idchofer = $_POST['idchofer'];
  $n_unidad = $_POST['n_unidad'];
  $cve_transportadora = $_POST['cve_transportadora'];
  $placa = $_POST['placa'];
  $sello_precinto = $_POST['sello_precinto'];

  $seguro = $_POST['seguro'];

  $flete  = $_POST['flete'];

  $origen  = $_POST['origen'];

  $ruta                    = $_POST['ruta'];
  $n_control_pallets       = $_POST['n_control_pallets'];
  $n_control_contenedores  = $_POST['n_control_contenedores'];


//$orden = $_POST['orden'];
  $almacen = $_POST['almacen'];
  $folio_xpartes = $_POST['folio_xpartes'];

  $status = $_POST['status'];
  //$user = $_SESSION['name'];

  $id_user = $_POST['id_user'];

  $transporte = $_POST['transporte'];

  $sql_usuario = "SELECT cve_usuario FROM c_usuario WHERE id_user = ".$id_user;
  $res=mysqli_query($conn, $sql_usuario);
  $row = mysqli_fetch_array($res);
  $user = $row["cve_usuario"];

  $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
  $res=mysqli_query($conn, $sql_almacen);
  $row = mysqli_fetch_array($res);
  $almacen_id = $row["id"];


  $sql = "SELECT IFNULL(transporte_externo, 0) as transporte_externo FROM t_transporte WHERE id = '{$transporte}'";
  $res=mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($res);
  $transporte_externo= $row["transporte_externo"];

  if($transporte_externo == 1 || $EntregaCliente == 1) $status = 'F';

  if(!$isla)
  {
    $fol = $folios[0];
    $suf = $sufijos[0];
    $sql = "SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio = '{$fol}'";
    $res=mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $cve_isla= $row["cve_ubicacion"];

    $sql = "SELECT ID_Embarque FROM t_ubicacionembarque WHERE cve_ubicacion = '{$cve_isla}'";
    $res=mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $isla= $row["ID_Embarque"];
  }

  if($ruta)
  {
      $sql = "SELECT control_pallets_cont FROM t_ruta WHERE ID_Ruta = '{$ruta}'";
      $res=mysqli_query($conn, $sql);
      $row = mysqli_fetch_array($res);
      $control_pallets_cont= $row["control_pallets_cont"];

      if($control_pallets_cont == 'S')
      {
            if(!$n_control_pallets) $n_control_pallets = 0;
            if(!$n_control_contenedores) $n_control_contenedores = 0;

            $sql_consig = "UPDATE t_ruta SET consig_pallets=consig_pallets+{$n_control_pallets}, consig_cont=consig_cont+{$n_control_contenedores} WHERE ID_Ruta = '{$ruta}'";
            $res=mysqli_query($conn, $sql_consig);

      }
  }

  $orden = "";
  if($folio_orden == '' || $id_embarquexpartes != '')
  {
    //if($id_embarquexpartes != '') $status = 'E';

    $existe = 0;
    if($id_embarquexpartes)
    {
        //si id_embarquexpartes no está vacío, folio_orden tampoco
        $sql = "SELECT COUNT(*) as x FROM td_ordenembarque WHERE Fol_folio = '$folio_orden'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $existe = $row['x'];
    }

    if($folio_xpartes == '' && !$existe)
    {
        $origen = utf8_decode($origen);
        $sql = "INSERT INTO th_ordenembarque (Cve_Almac, cve_usuario, t_ubicacionembarque_id, Id_Ruta, fecha, Ban_Libre, Status, seguro, flete, origen, chofer, guia_transporte, contacto, Activo, id_chofer, num_unidad, cve_transportadora, placa, sello_precinto)
                VALUES (".$almacen_id.", '".$user."', '{$isla}', '".$ruta."', NOW(), 'N','{$status}','{$seguro}','{$flete}', '{$origen}', '{$chofer}', '$guia_transporte', '$contacto', 1, '$idchofer', '$n_unidad', '$cve_transportadora', '$placa', '$sello_precinto')";
        $res = mysqli_query($conn, $sql);

        $sql = "SELECT MAX(ID_OEmbarque) as x FROM th_ordenembarque";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $orden = strval($row['x']);
    }

  }
  else
  {
      $and_sql = "";
      if($status == 'T') 
      {
          $sql = "UPDATE t_transporte SET Activo = 0 WHERE id = '{$transporte}'";
          mysqli_query($conn, $sql);
      }

      $sql = "UPDATE th_ordenembarque SET status = '{$status}' WHERE ID_OEmbarque = '{$folio_orden}'";
      mysqli_query($conn, $sql);
      $orden = $folio_orden;
  }

  $orden_i = 1;
  $embarque_folio_completo = "N";
  if($id_embarquexpartes)
  {
          $sql = "SELECT ID_Embarque FROM t_ubicacionembarque WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio = '{$folio_orden}')";
          $res=mysqli_query($conn, $sql);
          $row = mysqli_fetch_array($res);
          $isla= $row["ID_Embarque"];

          $sql = "INSERT IGNORE INTO td_ordenembarque (ID_OEmbarque, Fol_folio, Status, orden_stop, fecha_envio) VALUES ('{$orden}', '{$folio_orden}', '{$status}', {$orden_i}, NOW())";
          mysqli_query($conn, $sql);

          $cve_articuloxpartes = ""; $cve_lotexpartes = ""; $cantidadxpartes = "";
          $embarque_folio_completo="N";
          if($tipo_embarque == 'tipo_caja')
          {
              $sql = "UPDATE td_cajamixta SET Ban_Embarcado = 'S' WHERE Cve_CajaMixD = '{$id_embarquexpartes}'";
              mysqli_query($conn, $sql);

              $sql = "SELECT GROUP_CONCAT(DISTINCT Ban_Embarcado SEPARATOR '') AS Ban_Embarcado FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '{$folio_orden}')";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $embarque_folio_completo= $row["Ban_Embarcado"];

              $sql = "SELECT Cve_articulo, Cve_Lote, Cantidad FROM td_cajamixta WHERE Cve_CajaMixD = '{$id_embarquexpartes}'";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $cve_articuloxpartes = $row["Cve_articulo"];
              $cve_lotexpartes     = $row["Cve_Lote"];
              $cantidadxpartes     = $row["Cantidad"];
          }
          if($tipo_embarque == 'tipo_pallet')
          {
              $sql = "UPDATE t_tarima SET Ban_Embarcado = 'S' WHERE Id = '{$id_embarquexpartes}'";
              mysqli_query($conn, $sql);

              $sql = "SELECT GROUP_CONCAT(DISTINCT Ban_Embarcado SEPARATOR '') AS Ban_Embarcado FROM t_tarima WHERE Fol_Folio = '{$folio_orden}'";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $embarque_folio_completo= $row["Ban_Embarcado"];

              $sql = "SELECT cve_articulo, lote, cantidad FROM t_tarima WHERE Id = '{$id_embarquexpartes}'";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $cve_articuloxpartes = $row["cve_articulo"];
              $cve_lotexpartes     = $row["lote"];
              $cantidadxpartes     = $row["cantidad"];
          }


          if($embarque_folio_completo == 'S')
          {
              $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio_orden}' AND cve_ubicacion = (SELECT cve_ubicacion FROM t_ubicacionembarque WHERE ID_Embarque = '{$isla}')";
              mysqli_query($conn, $sql);

              $sql = "UPDATE th_pedido SET status = '{$status}' 
                      WHERE fol_folio = '{$folio_orden}' 
                      AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";
              mysqli_query($conn, $sql);

              $sql = "UPDATE th_subpedido SET status = '{$status}',HIE = NOW(),HFE = NOW(),Embarco = '{$user}'
                      WHERE fol_folio = '{$folio_orden}' AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";
              mysqli_query($conn, $sql);

              $sql = "UPDATE th_cajamixta SET embarcada = 'S' WHERE fol_folio = '{$folio_orden}'";
              mysqli_query($conn, $sql);
          }

          if($ruta)
          {
              $sql = "SELECT COUNT(*) as es_rel FROM rel_RutasEntregas WHERE id_ruta_entrega = '{$ruta}'";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $es_rel= $row["es_rel"];

              if($es_rel)
              {

                $sql = "SELECT DISTINCT
                            p.Cve_clte, rpd.Id_Destinatario, p.cve_almac as Cve_Almac, v.Id_Vendedor as Cve_Vendedor, a.clave AS clave_almacen,
                            IF(p.Fec_Entrega < CURDATE(), DAYOFWEEK(CURDATE())+1,DAYOFWEEK(p.Fec_Entrega)) AS dia_semana
                        FROM th_pedido p 
                        LEFT JOIN Rel_PedidoDest rpd ON rpd.Fol_Folio = p.Fol_folio
                        LEFT JOIN c_almacenp a ON a.id = p.Cve_Almac
                        INNER JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = '{$ruta}'
                        LEFT JOIN t_vendedores v ON v.Id_Vendedor = ra.cve_vendedor
                        WHERE p.Fol_folio = '$folio_orden'";
                $res=mysqli_query($conn, $sql);
                $row = mysqli_fetch_array($res);

                extract($row);

                if($Id_Destinatario && $EntregaCliente == 0)
                {
                    $sql = "SELECT COUNT(*) as existe FROM t_clientexruta WHERE clave_ruta = '{$ruta}' AND clave_cliente = $Id_Destinatario";
                    $res=mysqli_query($conn, $sql);
                    $row = mysqli_fetch_array($res);
                    $existe_ruta= $row["existe"];

                    if(!$existe_ruta)
                    {
                        $sql = "INSERT IGNORE INTO t_clientexruta(clave_cliente, clave_ruta) VALUES ({$Id_Destinatario}, {$ruta})";
                        $res = mysqli_query($conn, $sql);
                    }

                    $sql = "SELECT COUNT(*) as existe_rcr FROM RelClirutas WHERE IdRuta = '{$ruta}' AND IdCliente = $Id_Destinatario AND IdEmpresa = '$clave_almacen'";
                    $res=mysqli_query($conn, $sql);
                    $row = mysqli_fetch_array($res);
                    $existe_rcr= $row["existe_rcr"];

                    if(!$existe_rcr)
                    {
                        $sql = "INSERT IGNORE INTO RelClirutas(IdCliente, IdRuta, IdEmpresa, Fecha) VALUES ({$Id_Destinatario}, {$ruta}, '{$almacen}', CURDATE())";
                        $res = mysqli_query($conn, $sql);
                    }
                    /*
                    $sql = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Lu, Ma, Mi, Ju, Vi, Sa, Do, Cve_Almac) (SELECT DISTINCT '{$ruta}', Cve_Cliente, Id_Destinatario, '{$cve_Vendedor}', Lu, Ma, Mi, Ju, Vi, Sa, Do, Cve_Almac FROM RelDayCli WHERE Id_Destinatario = '{$Id_Destinatario}' AND Cve_Cliente = '{$Cve_clte}')";
                    $res = mysqli_query($conn, $sql);
                    */
                    //********************************************************************************
                    //Proceso para insertar en RelDayCli los dias de visita del dia de semana actual
                    //********************************************************************************
/*
                        $sql_dia = "SELECT DAYOFWEEK(CURDATE()) as dia_semana FROM DUAL";
                        $res_dia=mysqli_query($conn, $sql_dia);
                        $row_dia = mysqli_fetch_array($res_dia);
                        extract($row_dia);
*/
                        //****************************************************************
                        //si todavía hay pedidos por entregar no borro los demás, 
                        //caso contrario borro todos los que no sean del día actual
                        //Solo para los casos de ruta de entregas
                        //****************************************************************

                            $sql_pendientes = "SELECT COUNT(*) as hay_pendientes from th_pedido WHERE status = 'T' AND cve_almac = {$Cve_Almac} AND TipoPedido = 'P' AND Fol_Folio IN (SELECT DISTINCT Fol_Folio FROM td_ordenembarque WHERE ID_OEmbarque IN (SELECT DISTINCT ID_OEmbarque FROM th_ordenembarque WHERE Id_Ruta = '$ruta') AND status = 'T')";
                            $res_pendientes=mysqli_query($conn, $sql_pendientes);
                            $row_pendientes = mysqli_fetch_array($res_pendientes);
                            extract($row_pendientes);
                            //$hay_pendientes = $row_pendientes['hay_pendientes'];

                            if($hay_pendientes == 0 && $borrado_de_planeacion == 0)
                            {
                                //if($dia_semana != 1)//domingo
                                //{
                                    //$sql_borrar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    //$sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";


                                    //////$sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta'";


                                    //$sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND (SELECT IF(COUNT(*) = 0, 'NO', 'SI') as hay_pendientes from th_pedido WHERE status = 'T' AND cve_almac = {$Cve_Almac} AND TipoPedido = 'P' AND Fol_Folio IN (SELECT DISTINCT Fol_Folio FROM td_ordenembarque WHERE ID_OEmbarque IN (SELECT DISTINCT ID_OEmbarque FROM th_ordenembarque WHERE Id_Ruta = '$ruta') AND status = 'T')) = 'NO'";
                                    
                                    //////$res_borrar=mysqli_query($conn, $sql_borrar);


                                //}
                                /*
                                if($dia_semana != 2)//lunes
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 3)//martes
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 4)//miercoles
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 5)//jueves
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 6)//viernes
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 7)//sabado
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }
                                */
                            }
                            $borrado_de_planeacion = 1;

//*******************************************************************************************************

                            //$sql_dia_track .= "Dia semana = ".$dia_semana."\n";
                                if($dia_semana == 1)//domingo
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Mi IS NOT NULL, 'Mi', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Do = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Do = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Do = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Do = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Do = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Do = Lu WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Do, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Do), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Do), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Do = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 2)//lunes
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Mi IS NOT NULL, 'Mi', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Lu = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Lu = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Lu = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Lu = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Lu = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Lu = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Lu, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Lu), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Lu), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Lu = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 3)//martes
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Mi IS NOT NULL, 'Mi', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Ma = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Ma = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Ma = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ma = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Ma = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Ma = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Ma, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Ma), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Ma), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Ma = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 4)//miercoles
                                {
                                    //$sql_dia_track .= "Entro en $dia_semana"."\n";
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        //$sql_dia_track .= "Entro en traslado_reldaycli"."\n";
                                        
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Mi = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Mi = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Mi = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Mi = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Mi = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Mi = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Mi, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Mi), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";
                                        //$sql_dia_track .= $sql_reg."\n";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Mi), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Mi = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 5)//jueves
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Mi IS NOT NULL, 'Mi', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Ju = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ju = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Ju = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Ju = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Ju = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Ju = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Ju, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Ju), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Ju), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Ju = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 6)//viernes
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Mi IS NOT NULL, 'Mi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Vi = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Vi = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Vi = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Vi = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Vi = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Vi = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Vi, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Vi), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Vi), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Vi = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 7)//sabado
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_trasladar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Mi IS NOT NULL, 'Mi', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_trasladar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Sa = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Sa = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Sa = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Sa = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Sa = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Sa = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Sa, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Sa), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Sa), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Sa = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                        //****************************************************************
                    //*******************************************************************************

                }
              }

              if($EntregaCliente == 0)
              {
              //**********************************************************
              //                       PROCESO STOCK
              //**********************************************************
                $sql_stock = "INSERT IGNORE INTO t_tipomovimiento(nombre) VALUES('Salida')";
                $res_stock=mysqli_query($conn, $sql_stock);

                $sql_stock = "SELECT id_TipoMovimiento FROM t_tipomovimiento WHERE nombre = 'Salida'";
                $res_stock=mysqli_query($conn, $sql_stock);
                $id_TipoMovimiento = mysqli_fetch_array($res_stock)['id_TipoMovimiento'];

                $sql = "SELECT COUNT(*) as existe 
                        FROM Stock
                        WHERE Articulo = '{$cve_articuloxpartes}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}' ";
                $res=mysqli_query($conn, $sql);
                $existe = mysqli_fetch_array($res)['existe'];


                if(!$existe)
                {
                    $sql = "INSERT INTO Stock (Articulo, Stock, Ruta, IdEmpresa) VALUES ('{$cve_articuloxpartes}', $cantidadxpartes, '{$ruta}', '{$almacen}')";
                    $res=mysqli_query($conn, $sql);
                }
                else
                {
                    $sql = "UPDATE Stock SET Stock = Stock + $cantidadxpartes WHERE Articulo = '{$cve_articuloxpartes}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}'";
                    $res=mysqli_query($conn, $sql);
                }


                $sql = "SELECT COUNT(*) as existe 
                        FROM StockHistorico
                        WHERE Articulo = '{$cve_articuloxpartes}' AND RutaID = '{$ruta}' AND IdEmpresa = '{$almacen}' AND DiaO IS NULL";
                $res=mysqli_query($conn, $sql);
                $existe = mysqli_fetch_array($res)['existe'];


                if(!$existe)
                {
                    $sql = "INSERT INTO StockHistorico (Articulo, Stock, RutaID, Fecha, IdEmpresa) VALUES ('{$cve_articuloxpartes}', (SELECT Stock FROM Stock WHERE Articulo = '{$cve_articuloxpartes}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}'), '{$ruta}', NOW(), '{$almacen}')";
                    $res=mysqli_query($conn, $sql);
                }
                else
                {
                    $sql = "UPDATE StockHistorico SET Stock = (SELECT Stock FROM Stock WHERE Articulo = '{$cve_articuloxpartes}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}') WHERE Articulo = '{$cve_articuloxpartes}' AND RutaID = '{$ruta}' AND IdEmpresa = '{$almacen}' AND DiaO IS NULL";
                    $res=mysqli_query($conn, $sql);
                }

                $sql = "INSERT INTO MvtosInvRuta (IdEmpresa, Id_Ruta, Articulo, Lote, Referencia, Cantidad, id_TipoMovimiento, fecha) VALUES ('{$almacen}', '{$ruta}', '{$cve_articuloxpartes}', '{$cve_lotexpartes}', '{$orden}', $cantidadxpartes, $id_TipoMovimiento, NOW())";
                $res=mysqli_query($conn, $sql);

              //**********************************************************
              //**********************************************************
             }
          }
          $orden_i++;
  }
  else
  {
      foreach ($folios as $key => $value) 
      {
        $sufijo = $sufijos[$orden_i-1];
        if($value)
        {
          $sql = "SELECT ID_Embarque FROM t_ubicacionembarque WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio = '{$value}')";
          $res=mysqli_query($conn, $sql);
          $row = mysqli_fetch_array($res);
          $isla= $row["ID_Embarque"];

          if(!$ruta)
          {
              $sql = "SELECT DISTINCT IFNULL(t_ruta.ID_Ruta, th_pedido.ruta) as ruta 
                      FROM th_pedido 
                      LEFT JOIN t_ruta ON th_pedido.cve_ubicacion = t_ruta.cve_ruta
                      LEFT JOIN t_ruta r ON th_pedido.ruta = t_ruta.ID_Ruta
                      WHERE th_pedido.Fol_folio = '{$value}'";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $ruta= $row["ruta"];
          }

          $sql = "INSERT INTO td_ordenembarque (ID_OEmbarque, Fol_folio, Status, orden_stop, fecha_envio) VALUES ('{$orden}', '{$value}', '{$status}', {$orden_i}, NOW())";
          mysqli_query($conn, $sql);

          $sql = "UPDATE th_embarque_fotos SET th_embarque_folio = '{$orden}' WHERE folio_pedido = '{$value}'";
          mysqli_query($conn, $sql);

          $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$value}' AND cve_ubicacion = (SELECT cve_ubicacion FROM t_ubicacionembarque WHERE ID_Embarque = '{$isla}')";
          mysqli_query($conn, $sql);

          $sql = "UPDATE th_pedido SET status = '{$status}' 
                  WHERE fol_folio = '{$value}' 
                  AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";
          mysqli_query($conn, $sql);

          $sql = "UPDATE th_subpedido SET status = '{$status}',HIE = NOW(),HFE = NOW(),Embarco = '{$user}'
                  WHERE fol_folio = '{$value}' AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";
          mysqli_query($conn, $sql);

          $sql = "UPDATE th_cajamixta SET embarcada = 'S' WHERE fol_folio = '{$value}'";
          mysqli_query($conn, $sql);


          //$sql_dia_track .= "Entro en Ruta = $ruta";
          if($ruta)
          {
            //$sql_dia_track .= "Entro en Ruta = $ruta"."\n";
              $sql = "SELECT COUNT(*) as es_rel FROM rel_RutasEntregas WHERE id_ruta_entrega = '{$ruta}'";
              $res=mysqli_query($conn, $sql);
              $row = mysqli_fetch_array($res);
              $es_rel= $row["es_rel"];

              if($es_rel)
              {
                $sql = "SELECT DISTINCT
                            p.Cve_clte, rpd.Id_Destinatario,  p.cve_almac as Cve_Almac, v.Id_Vendedor as Cve_Vendedor, a.clave AS clave_almacen, 
                            IF(p.Fec_Entrega < CURDATE(), DAYOFWEEK(CURDATE())+1,DAYOFWEEK(p.Fec_Entrega)) AS dia_semana
                        FROM th_pedido p 
                        LEFT JOIN Rel_PedidoDest rpd ON rpd.Fol_Folio = p.Fol_folio
                        LEFT JOIN c_almacenp a ON a.id = p.Cve_Almac
                        INNER JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = '{$ruta}'
                        LEFT JOIN t_vendedores v ON v.Id_Vendedor = ra.cve_vendedor
                        WHERE p.Fol_folio = '$value'";
                $res=mysqli_query($conn, $sql);
                $row = mysqli_fetch_array($res);

                extract($row);

                if($Id_Destinatario && $EntregaCliente == 0)
                {
                    //$sql_dia_track .= "Entro en Id_Destinatario = $Id_Destinatario"."\n";

                    $sql = "SELECT COUNT(*) as existe FROM t_clientexruta WHERE clave_ruta = '{$ruta}' AND clave_cliente = $Id_Destinatario";
                    $res=mysqli_query($conn, $sql);
                    $row = mysqli_fetch_array($res);
                    $existe_ruta= $row["existe"];

                    if(!$existe_ruta)
                    {
                        $sql = "INSERT IGNORE INTO t_clientexruta(clave_cliente, clave_ruta) VALUES ({$Id_Destinatario}, {$ruta})";
                        $res = mysqli_query($conn, $sql);
                    }

                    $sql = "SELECT COUNT(*) as existe_rcr FROM RelClirutas WHERE IdRuta = '{$ruta}' AND IdCliente = $Id_Destinatario AND IdEmpresa = '$clave_almacen'";
                    $res=mysqli_query($conn, $sql);
                    $row = mysqli_fetch_array($res);
                    $existe_rcr= $row["existe_rcr"];

                    if(!$existe_rcr)
                    {
                        $sql = "INSERT IGNORE INTO RelClirutas(IdCliente, IdRuta, IdEmpresa, Fecha) VALUES ({$Id_Destinatario}, {$ruta}, '{$almacen}', CURDATE())";
                        $res = mysqli_query($conn, $sql);
                    }

                    //$sql = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Lu, Ma, Mi, Ju, Vi, Sa, Do, Cve_Almac) (SELECT DISTINCT '{$ruta}', Cve_Cliente, Id_Destinatario, '{$cve_Vendedor}', Lu, Ma, Mi, Ju, Vi, Sa, Do, Cve_Almac FROM RelDayCli WHERE Id_Destinatario = '{$Id_Destinatario}' AND Cve_Cliente = '{$Cve_clte}')";
                    //********************************************************************************
                    //Proceso para insertar en RelDayCli los dias de visita del dia de semana actual
                    //********************************************************************************
/*
                        $sql_dia = "SELECT DAYOFWEEK(CURDATE()) as dia_semana FROM DUAL";
                        $res_dia=mysqli_query($conn, $sql_dia);
                        $row_dia = mysqli_fetch_array($res_dia);
                        extract($row_dia);
*/
                        //****************************************************************
                        //si todavía hay pedidos por entregar no borro los demás, 
                        //caso contrario borro todos los que no sean del día actual
                        //Solo para los casos de ruta de entregas
                        //****************************************************************

                            $sql_pendientes = "SELECT COUNT(*) as hay_pendientes from th_pedido WHERE status = 'T' AND cve_almac = {$Cve_Almac} AND TipoPedido = 'P' AND Fol_Folio IN (SELECT DISTINCT Fol_Folio FROM td_ordenembarque WHERE ID_OEmbarque IN (SELECT DISTINCT ID_OEmbarque FROM th_ordenembarque WHERE Id_Ruta = '$ruta') AND status = 'T')";
                            $res_pendientes=mysqli_query($conn, $sql_pendientes);
                            $row_pendientes = mysqli_fetch_array($res_pendientes);
                            extract($row_pendientes);
                            //$hay_pendientes = $row_pendientes['hay_pendientes'];
                            //$sql_dia_track = "$sql_pendientes \n hay_pendientes = $hay_pendientes \n borrado_de_planeacion = $borrado_de_planeacion \n";
                            if($hay_pendientes == 0 && $borrado_de_planeacion == 0)
                            {
                                //$sql_dia_track .= "Entró en pedientes = 0 \n $sql_dia_track";
                                //if($dia_semana != 1)//domingo
                                //{
                                    //$sql_borrar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    //$sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";


                                    //////$sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta'";


                                    //$sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND (SELECT IF(COUNT(*) = 0, 'NO', 'SI') as hay_pendientes from th_pedido WHERE status = 'T' AND cve_almac = {$Cve_Almac} AND TipoPedido = 'P' AND Fol_Folio IN (SELECT DISTINCT Fol_Folio FROM td_ordenembarque WHERE ID_OEmbarque IN (SELECT DISTINCT ID_OEmbarque FROM th_ordenembarque WHERE Id_Ruta = '$ruta') AND status = 'T')) = 'NO'";
                                    

                                    //////$res_borrar=mysqli_query($conn, $sql_borrar);


                                //}
                                /*
                                if($dia_semana != 2)//lunes
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 3)//martes
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 4)//miercoles
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 5)//jueves
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 6)//viernes
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }

                                if($dia_semana != 7)//sabado
                                {
                                    //$sql_borrar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $sql_borrar = "DELETE FROM RelDayCli WHERE Cve_Ruta = '$ruta' AND Cve_Cliente = '$Cve_clte' AND Id_Destinatario = '{$Id_Destinatario}'";
                                    $res_borrar=mysqli_query($conn, $sql_borrar);
                                }
                                */
                            }
                            $borrado_de_planeacion = 1;

//**********************************************************************************************************
                            //$sql_dia_track .= "Dia semana = ".$dia_semana."\n";
                                if($dia_semana == 1)//domingo
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Mi IS NOT NULL, 'Mi', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Do = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Do = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Do = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Do = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Do = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Do = Lu WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Do, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Do), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Do), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Do = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 2)//lunes
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Mi IS NOT NULL, 'Mi', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Lu = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Lu = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Lu = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Lu = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Lu = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Lu = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Lu, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Lu), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Lu), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Lu = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 3)//martes
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Mi IS NOT NULL, 'Mi', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Ma = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Ma = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Ma = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ma = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Ma = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Ma = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Ma, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Ma), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Ma), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Ma = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 4)//miercoles
                                {
                                    //$sql_dia_track .= "Entro en $dia_semana"."\n";
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        //$sql_dia_track .= "Entro en traslado_reldaycli"."\n";
                                        
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Mi = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Mi = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Mi = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Mi = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Mi = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Mi = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Mi, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Mi), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";
                                        //$sql_dia_track .= $sql_reg."\n";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Mi), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Mi = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 5)//jueves
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Mi IS NOT NULL, 'Mi', IF(Vi IS NOT NULL, 'Vi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Ju = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ju = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Ju = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Ju = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Ju = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Ju = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Ju, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Ju), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Ju), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Ju = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 6)//viernes
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_dia_borrar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Mi IS NOT NULL, 'Mi', IF(Sa IS NOT NULL, 'Sa', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_dia_borrar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Vi = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Vi = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Vi = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Vi = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Vi = Sa WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Vi = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Sa') $sql_trasladar = "UPDATE RelDayCli SET Sa = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Vi, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Vi), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Vi), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Vi = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                                if($dia_semana == 7)//sabado
                                {
/*
                                    if($traslado_reldaycli == 0)
                                    {
                                        //for($i_re = 0; $i_re < $hay_pendientes; $i_re++)Do
                                        if($hay_pendientes)
                                        {
                                            $sql_trasladar = "SELECT DISTINCT IF(Lu IS NOT NULL, 'Lu', IF(Ma IS NOT NULL, 'Ma', IF(Ju IS NOT NULL, 'Ju', IF(Vi IS NOT NULL, 'Vi', IF(Mi IS NOT NULL, 'Mi', IF(Do IS NOT NULL, 'Do', 'NO_TRANSFERIR')))))) AS dia_eliminar FROM RelDayCli WHERE Cve_Ruta = '{$ruta}'";
                                            $res_pp=mysqli_query($conn, $sql_trasladar);
                                            $dia_eliminar = mysqli_fetch_array($res_pp)["dia_eliminar"];

                            if($dia_eliminar != 'NO_TRANSFERIR')
                            {
                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Sa = Lu WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Sa = Ma WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Sa = Mi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Sa = Ju WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Sa = Vi WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Sa = Do WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);

                                if($dia_eliminar == 'Lu') $sql_trasladar = "UPDATE RelDayCli SET Lu = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ma') $sql_trasladar = "UPDATE RelDayCli SET Ma = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Mi') $sql_trasladar = "UPDATE RelDayCli SET Mi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Ju') $sql_trasladar = "UPDATE RelDayCli SET Ju = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Vi') $sql_trasladar = "UPDATE RelDayCli SET Vi = NULL WHERE Cve_Ruta = {$ruta}";
                                if($dia_eliminar == 'Do') $sql_trasladar = "UPDATE RelDayCli SET Do = NULL WHERE Cve_Ruta = {$ruta}";
                                $res_pp=mysqli_query($conn, $sql_trasladar);
                            }
                                        }
                                        $traslado_reldaycli = 1;
                                    }
*/
                                    $sql_insert_rdc = "SELECT COUNT(*) as insert_rdc FROM RelDayCli WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                    $res_rdc=mysqli_query($conn, $sql_insert_rdc);
                                    $insert_rdc = mysqli_fetch_array($res_rdc)["insert_rdc"];

                                    if(!$insert_rdc)
                                    {
                                        $sql_reg = "INSERT IGNORE INTO RelDayCli(Cve_Ruta, Cve_Cliente, Id_Destinatario, Cve_Vendedor, Sa, Cve_Almac) VALUES ('{$ruta}', '$Cve_clte', '$Id_Destinatario', '$Cve_Vendedor', (SELECT (IFNULL(MAX(rds.Sa), 0)+1) AS  secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'), '$clave_almacen')";

                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                    else
                                    {
                                        $sql_sec = "SELECT (IFNULL(MAX(rds.Sa), 0)+1) AS secuencia FROM RelDayCli rds WHERE rds.Cve_Ruta = '{$ruta}' AND rds.Cve_Almac = '$clave_almacen'";
                                        $res_sec=mysqli_query($conn, $sql_sec);
                                        $secuencia = mysqli_fetch_array($res_sec)["secuencia"];

                                        $sql_reg = "UPDATE RelDayCli SET Sa = $secuencia WHERE Cve_Ruta = '{$ruta}' AND Id_Destinatario = '$Id_Destinatario' AND Cve_Cliente = '$Cve_clte'";
                                        $res_reg=mysqli_query($conn, $sql_reg);
                                    }
                                }

                        //****************************************************************
                    //*******************************************************************************
                }
              }

           if($EntregaCliente == 0)
           {
              //**********************************************************
              //                       PROCESO STOCK
              //**********************************************************
                $sql_stock = "INSERT IGNORE INTO t_tipomovimiento(nombre) VALUES('Salida')";
                $res_stock=mysqli_query($conn, $sql_stock);

                $sql_stock = "SELECT id_TipoMovimiento FROM t_tipomovimiento WHERE nombre = 'Salida'";
                $res_stock=mysqli_query($conn, $sql_stock);
                $id_TipoMovimiento = mysqli_fetch_array($res_stock)['id_TipoMovimiento'];

                $sql_stock = "SELECT Cve_articulo, LOTE as cve_lote, Cantidad as Num_cantidad
                        FROM td_surtidopiezas
                        WHERE fol_folio = '$value'";
                $res_stock=mysqli_query($conn, $sql_stock);
                $tracking_stock = "";
                while($row_stock = mysqli_fetch_array($res_stock))
                {
                    $cve_articulo_stock = $row_stock['Cve_articulo'];
                    $cve_lote_stock     = $row_stock['cve_lote'];
                    $cantidad_stock     = $row_stock['Num_cantidad'];

                    //****************************************************************************************
                    //                  VERIFICO SI ESTÁ EN CAJAS PARA PASAR A PIEZAS
                    //****************************************************************************************
                    $sql_stock_in = "SELECT IFNULL(umd.mav_cveunimed, '') AS umed
                                     FROM td_pedido td
                                     LEFT JOIN c_unimed umd ON umd.id_umed = td.id_unimed
                                     WHERE Fol_folio = '$value' AND Cve_articulo = '$cve_articulo_stock' ";
                    $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    $umed = mysqli_fetch_array($res_stock_in)['umed'];

                    $tracking_stock .= $sql_stock_in.";\n\n";
                    if($umed == '')
                    {
                        $sql_stock_in = "SELECT num_multiplo, umd.mav_cveunimed
                                         FROM c_articulo 
                                         LEFT JOIN c_unimed umd ON umd.id_umed = c_articulo.unidadMedida
                                         WHERE cve_articulo = '$cve_articulo_stock' ";
                        $res_stock_in=mysqli_query($conn, $sql_stock_in);
                        $row_stock_in = mysqli_fetch_array($res_stock_in);
                        $num_multiplo = $row_stock_in['num_multiplo'];
                        $umed         = $row_stock_in['mav_cveunimed'];

                    }

                    if($umed == 'XBX')//caja
                    {
                        $sql_stock_in = "SELECT num_multiplo FROM c_articulo WHERE cve_articulo = '$cve_articulo_stock' ";
                        $res_stock_in=mysqli_query($conn, $sql_stock_in);
                        $num_multiplo = mysqli_fetch_array($res_stock_in)['num_multiplo'];

                        $cantidad_stock = $cantidad_stock*$num_multiplo;//pasar a piezas
                    }

                    //****************************************************************************************


                    $sql_stock_in = "SELECT COUNT(*) as existe 
                            FROM Stock
                            WHERE Articulo = '{$cve_articulo_stock}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}'";
                    $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    $existe = mysqli_fetch_array($res_stock_in)['existe'];

                    $tracking_stock .= $sql_stock_in.";\n\n";
                    if(!$existe)
                    {
                        $sql_stock_in = "INSERT INTO Stock (Articulo, Stock, Ruta, IdEmpresa) VALUES ('{$cve_articulo_stock}', $cantidad_stock, '{$ruta}', '{$almacen}')";
                        $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    }
                    else
                    {
                        $sql_stock_in = "UPDATE Stock SET Stock = Stock + $cantidad_stock WHERE Articulo = '{$cve_articulo_stock}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}'";
                        $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    }

                    $tracking_stock .= $sql_stock_in.";\n\n";

                    $sql_stock_in = "SELECT COUNT(*) as existe 
                            FROM StockHistorico
                            WHERE Articulo = '{$cve_articulo_stock}' AND RutaID = '{$ruta}' AND IdEmpresa = '{$almacen}'  AND DiaO IS NULL";
                    $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    $existe = mysqli_fetch_array($res_stock_in)['existe'];

                    $tracking_stock .= $sql_stock_in.";\n\n";

                    if(!$existe)
                    {
                        $sql_stock_in = "INSERT INTO StockHistorico (Articulo, Stock, RutaID, Fecha, IdEmpresa) VALUES ('{$cve_articulo_stock}', (SELECT Stock FROM Stock WHERE Articulo = '{$cve_articulo_stock}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}'), '{$ruta}', NOW(), '{$almacen}')";
                        $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    }
                    else
                    {
                        $sql_stock_in = "UPDATE StockHistorico SET Stock = (SELECT Stock FROM Stock WHERE Articulo = '{$cve_articulo_stock}' AND Ruta = '{$ruta}' AND IdEmpresa = '{$almacen}') WHERE Articulo = '{$cve_articulo_stock}' AND RutaID = '{$ruta}' AND IdEmpresa = '{$almacen}' AND DiaO IS NULL";
                        $res_stock_in=mysqli_query($conn, $sql_stock_in);
                    }
                    $tracking_stock .= $sql_stock_in.";\n\n";

                    $sql_stock_in = "INSERT INTO MvtosInvRuta (IdEmpresa, Id_Ruta, Articulo, Lote, Referencia, Cantidad, id_TipoMovimiento, fecha) VALUES ('{$almacen}', '{$ruta}', '{$cve_articulo_stock}', '{$cve_lote_stock}', '{$orden}', $cantidad_stock, $id_TipoMovimiento, NOW())";
                    $res=mysqli_query($conn, $sql_stock_in);
                    $tracking_stock .= $sql_stock_in.";\n\n";

                }
              //**********************************************************
              //**********************************************************
            }
          }

          $sql = "UPDATE td_cajamixta SET Ban_Embarcado = 'S' WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '$value')";
          mysqli_query($conn, $sql);

          $sql = "UPDATE t_tarima SET Ban_Embarcado = 'S' WHERE Fol_Folio = '{$value}'";
          mysqli_query($conn, $sql);


          $orden_i++;
        }
      }
    }

  $sql = "SELECT count(*) as contador from rel_uembarquepedido where cve_ubicacion = (SELECT cve_ubicacion FROM t_ubicacionembarque WHERE ID_Embarque = '{$isla}')";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($res);
  $count = $row["contador"];

  if($count == 0)
  {
    $sql = "UPDATE t_ubicacionembarque SET status = 1 WHERE ID_Embarque = '{$isla}'";
    mysqli_query($conn, $sql);  

  }

    $transporte = $_POST['transporte'];
//    $orden = $_POST['orden'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "UPDATE th_ordenembarque SET ID_Transporte = '{$transporte}' WHERE ID_OEmbarque = '{$orden}'";
    mysqli_query($conn, $sql);  


  echo json_encode(['status' => 200, 'folio_xpartes' => $folio_orden, 'embarque_folio_completo' => $embarque_folio_completo, 'tracking_stock' => $tracking_stock, "sql_dia_track" => $sql_dia_track]);exit;
}





if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'guardarTransporte') 
{
    $transporte = $_POST['transporte'];
    $orden = $_POST['orden'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "UPDATE th_ordenembarque SET ID_Transporte = '{$transporte}' WHERE ID_OEmbarque = '{$orden}'";
    mysqli_query($conn, $sql);  
    echo json_encode(['status' => 200]);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadDetails') 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows'] ? $_POST['rows'] : 20; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_page = 0;
    $embarque = $_POST['embarque'];
    $almacen = $_POST['almacen'];

    if (!$sidx) {$sidx =1;}

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = "
        SELECT 
            COUNT(cve_articulo) as cuenta 
        FROM td_entalmacen 
        WHERE fol_folio IN (
            SELECT Fol_Folio 
            FROM th_entalmacen 
            WHERE Cve_Almac = '$almacen' 
            AND cve_ubicacion = '$embarque');
    ";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
    mysqli_close();
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $cve_almac = $row["id"];
    
    if (intval($page)>0) 
    {
        $_page = ($page-1) * $limit;
    }
    
    $sql = "
        SELECT 
            embarq.descripcion AS area_descripcion,
            items.fol_folio pedido_folio,
            DATE_FORMAT(ped.Fec_Pedido, '%d/%m/%Y %l:%i %p') pedido_fecha,
            DATE_FORMAT(ped.Fec_Entrega, '%d/%m/%Y %l:%i %p') pedido_entrega,
            (SELECT COUNT(Fol_folio) FROM td_pedido WHERE Fol_folio = items.fol_folio) pedido_total_items
        FROM td_entalmacen items
            INNER JOIN t_ubicacionembarque embarq ON embarq.cve_ubicacion = items.cve_ubicacion
            LEFT JOIN th_pedido ped ON items.fol_folio = items.Fol_folio
        WHERE items.cve_ubicacion = '{$embarque}' AND ped.cve_almac = '{$cve_almac}'
            GROUP BY items.fol_folio 
        LIMIT {$_page}, {$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count >0) 
    {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    }
    else
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages) 
    {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['idy_ubica'];
        $responce->rows[$i]['cell'] = [$row['area_descripcion'],$row['pedido_folio'],$row['pedido_fecha'],$row['pedido_entrega'],$row['pedido_total_items']];
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'detalles') 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows'] ? $_POST['rows'] : 20; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_page = 0;
  
    $folio = $_POST['folio'];
    $fol = implode("','" , $folio);

    if (!$sidx) {$sidx =1;}
    if (intval($page)>0) 
    {
        $_page = ($page-1) * $limit;
    }
    
    //echo var_dump($folio);
    $sql = "
        SELECT
            th_cajamixta.NCaja,
            c_tipocaja.clave,
            c_tipocaja.descripcion,
            th_cajamixta.Guia,
            th_cajamixta.fol_folio as folio,
            TRUNCATE(
                (CASE 
                    WHEN th_cajamixta.cve_tipocaja = 1 THEN
                    (
                        SELECT
                            IFNULL(ROUND(SUM(td_pedido.Num_cantidad * ((c_articulo.alto/1000) * (c_articulo.ancho/1000) * (c_articulo.fondo/1000))),3), 0) AS volumentotal
                        FROM td_pedido
                            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Cve_articulo
                        WHERE td_pedido.Fol_folio = th_cajamixta.fol_folio
                    )
                    ELSE
                    (
                        SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) 
                        FROM c_tipocaja 
                        WHERE id_tipocaja = th_cajamixta.cve_tipocaja
                    )END),4
            ) AS volumen,
            TRUNCATE(IFNULL(th_cajamixta.Peso,0),4) as Peso
        FROM th_cajamixta
            LEFT JOIN c_tipocaja on c_tipocaja.id_tipocaja = th_cajamixta.cve_tipocaja 
        WHERE th_cajamixta.fol_folio  IN('".$fol."');
    ";
  //echo var_dump($sql);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    //echo var_dump($res);
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
      //echo var_dump($row);
      //die();
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['NCaja'];
        $responce->rows[$i]['cell'] = [$i+1,$row['clave'],$row['descripcion'],$row['Guia'],$row['folio'],$row['volumen'],$row['Peso'],];
        $i++;
      
    }
  
     if ($count >0) 
    {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    }
    else
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages) 
    {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    echo json_encode($responce);exit;
}

if($_POST['action'] == 'cargarFotosTH' ) 
{

  $folio = $_POST['folio'];
  $sql = "SELECT * FROM th_embarque_fotos WHERE folio_pedido = '$folio'";

  $query = mysqli_query(\db2(), $sql);

  $imagenes = "";
  while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><br><b>".($row['descripcion'])."</b>&nbsp;&nbsp;<a href='#' onclick='eliminar_foto_th(".$row['id'].")'><i class='fa fa-times' style='color: #F00;' title='eliminar'></i></a></div>";
  }
  echo $imagenes;
}

if($_POST['action'] == 'eliminarFotosTH' ) 
{

  $id = $_POST['id'];
  $sql = "DELETE FROM th_embarque_fotos WHERE id = $id";
  $query = mysqli_query(\db2(), $sql);
}


