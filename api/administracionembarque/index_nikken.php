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
//   if($_POST["ruta"] != "")
//   {
//     $sql="
//         select 
//             t_clientexruta.clave_cliente, 
//             th_pedido.Fol_folio, 
//             th_pedido.status,
//             rel_uembarquepedido.cve_ubicacion,
//             t_ubicacionembarque.ID_Embarque as id,
//             t_ubicacionembarque.descripcion
//         from t_clientexruta 
//             INNER join th_pedido on th_pedido.Cve_clte = t_clientexruta.clave_cliente
//             INNER join rel_uembarquepedido on rel_uembarquepedido.fol_folio = th_pedido.Fol_folio
//             INNER JOIN t_ubicacionembarque on t_ubicacionembarque.cve_ubicacion = rel_uembarquepedido.cve_ubicacion
//         WHERE th_pedido.status = 'C'
//             and t_clientexruta.clave_ruta = 1;
//     ";
//   }
//   else
//   {
    $sql = "
        SELECT 
            embarq.ID_Embarque id,
            embarq.descripcion
        FROM t_ubicacionembarque embarq
            LEFT JOIN rel_uembarquepedido rel ON rel.cve_ubicacion = embarq.cve_ubicacion
            LEFT JOIN th_pedido ped ON rel.fol_folio = ped.Fol_folio
        WHERE ped.status = 'C'
            AND embarq.Activo = 1  
            #AND (embarq.status = 2 or embarq.status = 3)  
            AND embarq.cve_ubicacion IN (SELECT cve_ubicacion FROM rel_uembarquepedido) 
            AND embarq.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '".$_POST['almacen']."')
        GROUP BY embarq.ID_Embarque;
    ";
//   }
//   echo var_dump($sql);
//   die();
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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
  echo json_encode(['status'=>true,'data'=>$islas]);
  exit;
}

if (isset($_POST) && ! empty($_POST) && $_POST['action'] == 'traer_contenedores') 
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

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
    /*
      "
        SELECT 
            ID_Ruta as id, 
            descripcion 
        FROM t_ruta 
            inner join c_almacenp on c_almacenp.id= t_ruta.cve_almacenp
        WHERE c_almacenp.clave = '{$_POST['almacen']}';
    ";
    */
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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

if (isset($_POST) && ! empty($_POST) && $_POST['action'] == 'cargarTransportes') 
{
    $sql = "
            SELECT * FROM t_transporte
            inner join tipo_transporte on t_transporte.tipo_transporte = tipo_transporte.clave_ttransporte;
    ";
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $transportes = [];
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $transportes[] = [
          'id'=>$row['id'],
          'nombre' =>$row['Nombre'],
          'descripcion' =>$row['desc_ttransporte']
        ];
        
    }

    echo json_encode(['status'=>true,'data'=>$transportes, 'query'=>$sql]);
    exit;
}
if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'verificarOrdenDeEmbarque')
{
  $almacen = $_POST['almacen'];
  $isla = $_POST['isla'];
  $ruta = $_POST['ruta'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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
    mysqli_set_charset($conn, 'utf8');

    $sqlHeader = "
        SELECT  
            p.Fol_folio AS id,
            COALESCE(DATE_FORMAT(p.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_embarque,
            '--' AS fecha_entrega,
            COALESCE(concat(d.direccion,' ',d.colonia,' ',d.postal,' ',d.ciudad,' ',d.estado), '--') AS destino,
            (COALESCE(p.Observaciones, '--') AS comentarios,
            '--' AS chofer,
            '--' AS transporte,
            COALESCE(cat_estados.DESCRIPCION, '--') AS status,
            (SELECT TRUNCATE(COALESCE(SUM(peso), 0),4) FROM c_articulo WHERE cve_articulo IN (select Cve_articulo from td_pedido where td_pedido.Fol_folio = p.Fol_folio)) AS peso,
            TRUNCATE((SELECT COALESCE(SUM((alto/1000) * (ancho/1000) * (fondo/1000)), 0) FROM c_articulo WHERE cve_articulo IN (select Cve_articulo from td_pedido where td_pedido.Fol_folio = p.Fol_folio)),4) AS volumen,
            (SELECT COALESCE(COUNT(NCaja), 0) FROM th_cajamixta WHERE fol_folio =p.Fol_folio) AS total_cajas,
            (SELECT TRUNCATE(COALESCE(SUM(Cantidad), 0),0) FROM td_surtidopiezas WHERE fol_folio = p.Fol_folio) AS total_piezas
        FROM th_pedido p      
            left JOIN Rel_PedidoDest on Rel_PedidoDest.Fol_Folio = p.Fol_folio
            left JOIN c_destinatarios d on d.id_destinatario = Rel_PedidoDest.Id_Destinatario
            LEFT JOIN cat_estados on cat_estados.ESTADO = p.status
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
    $almacen = $_POST['almacen'];
    $isla = $_POST['isla'];
    $texto = $_POST['texto'];
    $ruta = $_POST['ruta'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$sidx) {$sidx =1;}
    $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $cve_almac = $row["id"];
    
    if (intval($page)>0) 
    {
      $_page = ($page-1)*$limit;
    }
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
    "
      SELECT 
        th_pedido.Fol_folio AS folio, 
        th_pedido.Cve_clte AS cliente, 
        Rel_PedidoDest.Id_Destinatario AS id_destinatario, 
        c_destinatarios.razonsocial AS destinatario,
        c_cliente.RazonSocial AS razonsocial,
        c_destinatarios.direccion as Direccion_Cliente,
        c_cliente.id_cliente AS id_cliente,
        c_destinatarios.cve_Clte AS clave_sucursal,
        t_clientexruta.clave_ruta AS id_ruta,
        t_ruta.descripcion AS ruta,
        rel_uembarquepedido.cve_ubicacion AS cve_ubica_embarque,
        t_ubicacionembarque.descripcion AS isla,

        IFNULL((SELECT COUNT(Guia) FROM th_cajamixta WHERE fol_folio = th_pedido.Fol_folio),0) guias,
        IFNULL((SELECT SUM(Peso) FROM th_cajamixta WHERE fol_folio = th_pedido.Fol_folio),0) peso,
        TRUNCATE(max(th_cajamixta.NCaja)*((c_tipocaja.largo/1000)*(c_tipocaja.alto/1000)*(c_tipocaja.ancho/1000)),3) AS volumen,
        (select sum(revisadas) as x from td_surtidopiezas where td_surtidopiezas.fol_folio = th_pedido.Fol_folio) AS piezas,
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
          AND rel_uembarquepedido.cve_almac = '{$cve_almac}' 
      GROUP by th_pedido.Fol_folio
    ";
//     echo var_dump($sql);
//     die();
    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
  
    
    
    $responce;
    $responce->page = $page;
    $folios = '';
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
                                      $row['destinatario'],
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

    $responce->total = $i;
    $responce->records = $i;
    $responce->guias_totales = $total_guias;
    //$responce->sql = $sql;
    echo json_encode($responce);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'obtenerVolumenCajas') 
{
  $folio = $_POST['folio'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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

    if (!$sidx) {$sidx =1;}
    if (intval($page)>0) 
    {
        $_page = ($page-1) * $limit;
    }
    
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
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['NCaja'];
        $responce->rows[$i]['cell'] = [$i+1,$row['clave'],$row['descripcion'],$row['Guia'],$row['volumen'],$row['Peso'],];
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
    $sqlCount = " SELECT  count(ped.Fol_folio) cuenta FROM  td_pedido ped WHERE Fol_folio = '{$folio}' ";
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
    mysqli_close($conn);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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
  $folios = $_POST['folios'];
  $isla = $_POST['isla'];
//   $orden = $_POST['orden'];
  $almacen = $_POST['almacen'];
  $user = $_SESSION['name'];
  
  $sql = "SELECT MAX(ID_OEmbarque) as x FROM th_ordenembarque";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($res);
  $orden = strval($row['x'] +1);

  if($isla == "")
  {
    $sql = "SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio = '{$folios[0]}'";
    $res=mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $isla= $row["cve_ubicacion"];
  }

  $sql = "INSERT INTO th_ordenembarque (cve_usuario, t_ubicacionembarque_id, fecha, Ban_CrossDock, Status, Activo)
          VALUES ('".$_SESSION['id_user']."', '{$isla}', NOW(), 'N','T', 1)";
  mysqli_query($conn, $sql);

  foreach ($folios as $key => $value) 
  {
    $sql = "SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio = '{$value}'";
    $res=mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $isla= $row["cve_ubicacion"];

    $sql = "INSERT INTO td_ordenembarque (ID_OEmbarque, Fol_folio, Status) VALUES ('{$orden}', '{$value}', 'T')";
    mysqli_query($conn, $sql);

    $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$value}' AND cve_ubicacion = '{$isla}'";
    mysqli_query($conn, $sql);

    $sql = "UPDATE th_pedido SET status = 'T' 
            WHERE fol_folio = '{$value}' 
            AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";
    mysqli_query($conn, $sql);

    $sql = "UPDATE th_subpedido SET status = 'T',HIE = NOW(),HFE = NOW(),Embarco = '{$user}'
            WHERE fol_folio = '{$value}' AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";
    mysqli_query($conn, $sql);

    $sql = "UPDATE th_cajamixta SET embarcada = 'S' WHERE fol_folio = '{$value}'";
    mysqli_query($conn, $sql);
  }

  $sql = "SELECT count(*) as contador from rel_uembarquepedido where cve_ubicacion = (SELECT cve_ubicacion FROM t_ubicacionembarque WHERE ID_Embarque = '{$isla}')";
  $res = mysqli_query($conn, $sql);
  $row = mysqli_fetch_array($res);
  $count = $row["contador"];

  if($count == 0)
  {
    $sql = "UPDATE t_ubicacionembarque SET status = 1 WHERE ID_Embarque = '{$isla}'";
  }
  echo json_encode(['status' => 200]);exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'guardarTransporte') 
{
    $transporte = $_POST['transporte'];
    $orden = $_POST['orden'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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

