 <?php
 include '../../../config.php';

 error_reporting(0);

if(isset($_POST) && !empty($_POST) && isset($_POST['action']) && $_POST['action'] === 'loadPedidosDashboard'){
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $almacen = $_POST['almacen'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) {
        $sidx =1;
    }
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sqlCount = "SELECT     COUNT(DISTINCT o.Fol_folio) AS cuenta 
                FROM th_pedido o 
                LEFT JOIN c_usuario u ON u.cve_usuario = o.Cve_Usuario
               WHERE o.Activo = 1 AND o.status = 'S' AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') = DATE_FORMAT(CURDATE(), '%d-%m-%Y')";
    if (!empty($almacen)) {
        $sqlCount .= " AND o.cve_almac = '$almacen'";
    }
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

          $row = mysqli_fetch_array($res);
          $count = $row['cuenta'];
*/
          $sql = "SELECT
            o.Fol_folio AS orden,
            CONVERT(IFNULL(u.nombre_completo, '--'), CHAR) AS usuario,
            COALESCE(SUM(od.Num_cantidad), 0) AS cantidad_solicitada,
            IFNULL(DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_inicio,
            IF(o.Fec_Entrega < CURDATE(), CONCAT('<span style=color:red>',IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--'), '</span>'), IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--')) AS fecha_fin,
            COALESCE((SELECT SUM(FLOOR(Cantidad)) FROM td_surtidopiezas WHERE fol_folio = o.Fol_folio AND cve_almac = o.cve_almac), 0) AS cantidad_surtida
        FROM th_pedido o
        LEFT JOIN th_subpedido sub ON sub.Fol_folio = o.Fol_folio
        LEFT JOIN cat_estados e ON e.ESTADO = o.status
        LEFT JOIN c_usuario u ON u.cve_usuario = sub.Cve_Usuario
        LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
        WHERE o.Activo = 1 AND o.status = 'S' 
    ";
    #AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') = DATE_FORMAT(CURDATE(), '%d-%m-%Y')

    if (!empty($almacen)) {
        $sql .= " AND o.cve_almac = '$almacen'";
    }

    $sql .= " GROUP BY o.Fol_folio ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);    

    $sql .= " ORDER BY o.Fec_Entrada DESC LIMIT {$start}, {$limit}; ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count >0) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages) {
        $page=$total_pages;
    }

    $responce = new stdClass();
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        $sqlMore = "SELECT PorcentajeSurtidoOrden('$orden') AS porcentaje_surtido";
        $queryMore = mysqli_query($conn, $sqlMore);
        $extraData = mysqli_fetch_assoc($queryMore);
        extract($extraData);
        $responce->rows[$i]['id']= $i;
        $responce->rows[$i]['cell']=array($orden, $usuario, $fecha_inicio, $fecha_fin, number_format($cantidad_solicitada, 2), number_format($cantidad_surtida, 2), number_format($porcentaje_surtido, 2));
        $i++;
    }
    echo json_encode($responce);
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'VerificarTiposPedidosPlanificar'){

     $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

     $folios = $_POST['folios'];
     //$folio = '';
     //$total = intval(count($folios) -1);
     $tipo_ot = false; $tipo_ov = false; $tipo_ws = false; $tipo_pedido = false; $tipo_rb = false;
     foreach ($folios as $folio) 
     {
        $sql = "SELECT IF((SELECT TipoPedido FROM th_pedido WHERE Fol_folio = '{$folio}') in ('W', 'W2'), 'tipo_ws', IF((SELECT COUNT(*) AS ot FROM t_ordenprod WHERE Folio_Pro = '{$folio}') > 0, 'tipo_ot', IF((SELECT COUNT(*) AS ov FROM th_pedido WHERE '{$folio}' LIKE 'OV%') > 0, 'tipo_ov', IF((SELECT COUNT(*) AS rb FROM th_pedido WHERE '{$folio}' LIKE 'RB%') > 0, 'tipo_reabasto','tipo_pedido')))) AS tipo FROM DUAL";
        $query = mysqli_query($conn, $sql);
        $result = mysqli_fetch_assoc($query);

        if($result['tipo'] == 'tipo_ws') $tipo_ws = true;
        else if($result['tipo'] == 'tipo_ot') $tipo_ot = true;
        else if($result['tipo'] == 'tipo_ov') $tipo_ov = true;
        else if($result['tipo'] == 'tipo_reabasto') $tipo_reabasto = true;
        else $tipo_pedido = true;
     }

     $mismo_tipo = 0;
     $tipo_select = "";

     if($tipo_ws) {$mismo_tipo++; $tipo_select = "tipo_ws";}
     if($tipo_ot) {$mismo_tipo++; $tipo_select = "tipo_ot";}
     if($tipo_ov) {$mismo_tipo++; $tipo_select = "tipo_ov";}
     if($tipo_reabasto) {$mismo_tipo++; $tipo_select = "tipo_reabasto";}
     if($tipo_pedido) {$mismo_tipo++; $tipo_select = "tipo_pedido";}


  echo json_encode(array(
        "data" => $mismo_tipo,
        "tipo" => $tipo_select
    ));exit;
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'destinatarioDelPedido'){
    $folio = $_POST['folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT 
                rel.Id_Destinatario,
                des.razonsocial,
                des.direccion,
                des.colonia,
                des.postal,
                des.ciudad,
                des.estado,
                des.contacto,
                des.telefono
            FROM Rel_PedidoDest rel 
                LEFT JOIN c_destinatarios des ON rel.Id_Destinatario = des.id_destinatario
            WHERE rel.Fol_folio = '{$folio}'
            GROUP by rel.Id_Destinatario
            order by rel.Id_Destinatario asc";
    
    $query = mysqli_query($conn, $sql);
    $data = [];
    
    if($query->num_rows > 0){
      for($i = 1; $i<= $query->num_rows;$i++){
        $result = mysqli_fetch_assoc($query);
        if($i==$query->num_rows){
          $data['razonsocial'] = utf8_encode($result['razonsocial']);
          $data['direccion'] = utf8_encode($result['direccion']);
          $data['colonia'] = utf8_encode($result['colonia']);
          $data['postal'] = $result['postal'];
          $data['ciudad'] = utf8_encode($result['ciudad']);
          $data['estado'] = utf8_encode($result['estado']);
          $data['contacto'] = $result['contacto'];
          $data['telefono'] = $result['telefono'];
        }
        else{
          $sql="DELETE FROM `Rel_PedidoDest` WHERE Id_Destinatario = ".$result["Id_Destinatario"];
          mysqli_query($conn, $sql);
        }
      }
    }

  echo json_encode(array(
        "data"    => $data
    ));exit;
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'getDataPDF') {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    $criterio = $_POST['criterio'];
    $fecha_inicio = $_POST['fechaInicio'];
    $fecha_fin = $_POST['fechaFin'];
   // $filtro = $_POST['filtro'];
    $status = $_POST['status'];
    $almacen = $_POST['almacen'];
    $factura_inicio = $_POST['facturaInicio'];
    $factura_fin = $_POST['facturaFin'];

    if (!empty($fecha_inicio)) {
        $fecha_inicio = date("Y-m-d", strtotime($fecha_inicio));
    }
    if (!empty($fecha_fin)) {
        $fecha_fin = date("Y-m-d", strtotime($fecha_fin));
    }

    $sqlHeader = "SELECT
                    o.Fol_folio AS orden,
                    IFNULL(o.Pick_Num, '--') AS orden_cliente,
                    IFNULL(p.Descripcion, '--') AS prioridad,
                    IFNULL(e.DESCRIPCION, '--') AS status,
                    IFNULL(c.RazonSocial, '--') AS cliente,
                    IFNULL(c.CalleNumero, '--') AS direccion,
                    IFNULL(c.CodigoPostal, '--') AS dane,
                    IFNULL(c.Ciudad, '--') AS ciudad,
                    IFNULL(c.Estado, '--') AS estado,
                    COALESCE(SUM(od.Num_cantidad), 0) AS cantidad,
                    IFNULL(DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido,
                    IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
                    IFNULL(u.nombre_completo, '--') AS usuario,
                    o.id_pedido
                FROM th_pedido o
                LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad
                LEFT JOIN cat_estados e ON e.ESTADO = o.status
                LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte
                LEFT JOIN c_usuario u ON u.cve_usuario = o.Cve_Usuario
                LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
                WHERE o.Activo = 1";
    if (!empty($almacen)) {
        $sqlHeader .= " AND o.cve_almac = '$almacen'";
    }
    if (!empty($status)) {
        $sqlHeader .= " AND o.status = '$status'";
    }
    if(!empty($criterio)) 
    {
      $sqlHeader .= "AND o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR p.Descripcion like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%'";
    }
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sqlHeader .= " AND o.Fec_Pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    } elseif (!empty($fecha_inicio)) {
        $sqlHeader .= " AND o.Fec_Pedido >= '$fecha_inicio'";
    } elseif (!empty($fecha_fin)) {
        $sqlHeader .= " AND o.Fec_Pedido <= '$fecha_fin'";
    }
    if (!empty($factura_inicio) && !empty($factura_fin)) {
        $sqlHeader .= " AND o.Fol_folio BETWEEN '$factura_inicio' AND '$factura_fin'";
    } elseif (!empty($factura_inicio)) {
        $sqlHeader .= " AND o.Fol_folio >= $factura_inicio";
    } elseif (!empty($factura_fin)) {
        $sqlHeader .= " AND o.Fol_folio <= $factura_fin";
    }

    $sqlHeader .= " GROUP BY o.Fol_folio ORDER BY o.Fec_Entrada DESC;";
    $header = array();
    $queryHeader = mysqli_query($conn, $sqlHeader);
    while ($row = mysqli_fetch_assoc($queryHeader)) {
        extract($row);
        $sqlMore = "SELECT * FROM (
            (SELECT COALESCE(SUM((alto/1000) * (fondo/1000) * (ancho/1000)), 0) AS volumen FROM c_articulo WHERE cve_articulo IN (SELECT cve_articulo FROM td_pedido WHERE Fol_folio = '$orden')) AS volumen,
            (SELECT IFNULL(SUM(a.peso), 0) AS peso FROM td_pedido tdp LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo WHERE tdp.Fol_folio = '$orden') AS peso,
            (SELECT PorcentajeSurtidoOrden('$orden') AS surtido) AS surtido
        )";
        $queryMore = mysqli_query($conn, $sqlMore);
        $extraData = mysqli_fetch_assoc($queryMore);
        extract($extraData);
        $row['peso'] = $peso;
        $row['volumen'] = $volumen;
        $row['surtido'] = $surtido;
        array_push($header, $row);
    }

    mysqli_close($conn);
    echo json_encode(array(
        "header"    => $header
    ));
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'obtenerPesoVolumen') {
     $pesototal = 0;
     $volumentotal = 0;
     $fecha="";
     $folios = $_POST['folios'];
     $tipo  = $_POST['tipo'];
     $folio = '';
     $total = intval(count($folios) -1);
     foreach ($folios as $key => $value) {
         $folio .= "'{$value}'";
         if ($key !== $total) {
             $folio .= ",";
         }
    }
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql="
          SELECT
          concat(LPAD(DAY(th_pedido.Fec_Entrega),2,'0'),'-',LPAD(MONTH(th_pedido.Fec_Entrega),2,'0'),'-',YEAR(th_pedido.Fec_Entrega)) as Fec_Entrega,
          SUM(c_articulo.peso* td_pedido.Num_cantidad) as pesototal,
          Sum(((c_articulo.alto/1000)*(c_articulo.fondo/1000)*(c_articulo.ancho/1000))*td_pedido.Num_cantidad) as volumentotal
          from td_pedido 
          INNER join th_pedido on th_pedido.Fol_folio = td_pedido.Fol_folio
          INNER JOIN c_articulo on c_articulo.cve_articulo = td_pedido.Cve_articulo
          where td_pedido.Fol_folio in({$folio})
          ORDER by th_pedido.Fec_Entrega asc
        ";
        /*$sql = "SELECT  SUM(peso) as pesototal,
                  SUM((alto/1000) * (ancho/1000) * (fondo/1000)) as volumentotal
          FROM c_articulo
          WHERE cve_articulo
          IN (SELECT Cve_articulo FROM td_pedido WHERE Fol_folio IN({$folio}))
          ";*/
          $query = mysqli_query($conn, $sql);


          $sql_ws="SELECT IFNULL(MAX(id_consolidado), 0) AS contador FROM th_consolidado";
/*
            $sql_ws = "SELECT (MAX(id)) AS contador FROM (
                      SELECT IFNULL((MAX(id_consolidado)), 1) AS id FROM th_consolidado
                      UNION
                      SELECT IFNULL((MAX(RIGHT(Fol_Consolidado, 2))), 1) AS id FROM t_consolidado
                    ) AS c";
*/
          if($_POST['tipopedido'] == 'tipo_ws')
            $sql_ws = "SELECT IFNULL((MAX(REPLACE(Fol_Consolidado, 'W2S', ''))+0), 1) AS contador FROM t_consolidado";

          if($tipo == 'XD') 
            $sql_ws="SELECT COUNT(*) AS contador FROM th_pedido WHERE Fol_folio LIKE 'XD%'";

          $query_sw = mysqli_query($conn, $sql_ws);
        
          //if ($query_sw->num_rows >0) {
            $result_sw = mysqli_fetch_assoc($query_sw);
            if(($result_sw["contador"]+1) < 10)
            {
                $folio="WS0".($result_sw["contador"]+1);
                if($_POST['tipopedido'] == 'tipo_ws')
                    $folio="W2S0".($result_sw["contador"]+1);
                if($tipo == 'XD') 
                    $folio="XD0".($result_sw["contador"]+1);
            }
            else
            {
                $folio="WS0".($result_sw["contador"]+1);
                if($_POST['tipopedido'] == 'tipo_ws')
                    $folio="W2S0".($result_sw["contador"]+1);
                if($tipo == 'XD') 
                    $folio="XD0".($result_sw["contador"]+1);
            }
          //}
          
        if ($query->num_rows >0) {
            $result = mysqli_fetch_assoc($query);
            $pesototal = $result['pesototal'];
            $volumentotal = $result['volumentotal'];
            $fecha = $result['Fec_Entrega'];
        }
        mysqli_close($conn);
        echo json_encode(array(
        "pesototal"     => $pesototal,
        "volumentotal"  => $volumentotal,
        "fecha_entrega" => $fecha,
        "folio"         => $folio,
        "sql"           => $sql,
        "res ws"        => $result_sw
        ));
 }



else if (isset($_POST) && !empty($_POST) && !isset($_POST['action'])) {
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $sord = $_POST['sord']; // get the direction

        //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
        $criterio = $_POST['criterio'];
        $fecha_inicio = $_POST['fechaInicio'];
        $fecha_fin = $_POST['fechaFin'];
       // $filtro = $_POST['filtro'];
        $status = $_POST['status'];
        $ciudad = $_POST['ciudad'];
        $almacen = $_POST['almacen'];
        $factura_inicio = $_POST['facturaInicio'];
        $factura_fin = $_POST['facturaFin'];
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (!empty($fecha_inicio)) {
            $fecha_inicio = date("Y-m-d", strtotime($fecha_inicio));
        }
        if (!empty($fecha_fin)) {
            $fecha_fin = date("Y-m-d", strtotime($fecha_fin));
        }

        $start = $limit*$page - $limit; // do not put $limit*($page - 1)

        if (!$sidx) {
            $sidx =1;
        }
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
              $sql = "SELECT
                o.Fol_folio AS orden,
                IFNULL(o.Pick_Num, '--') AS orden_cliente,
                IFNULL(p.Descripcion, '--') AS prioridad,
                CASE LEFT(o.Fol_folio,2)
                    WHEN 'WS' THEN CONCAT(e.DESCRIPCION, ' Consolidado de Ola')
                    ELSE IFNULL(e.DESCRIPCION, '--')
                END AS status,
                IFNULL(c.RazonSocial, '--') AS cliente,
                IFNULL(c.CalleNumero, '--') AS direccion,
                IFNULL(c.CodigoPostal, '--') AS dane,
                IFNULL(c.Ciudad, '--') AS ciudad,
                IFNULL(c.Estado, '--') AS estado,
                COALESCE(SUM(od.Num_cantidad), 0) AS cantidad,
                SUM(od.Num_cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))) AS volumen,
                SUM(od.Num_cantidad *  a.peso) AS peso,
                IFNULL(DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido,
                -- IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
                IFNULL(DATE_FORMAT(thsub.Hora_inicio, '%d-%m-%Y'), '--') AS fecha_surtido,
                --IFNULL(u.nombre_completo, '--') AS usuario,
                PorcentajeSurtidoOrden(o.Fol_folio) AS surtido,
                thsub.cve_usuario AS asignado,
                o.id_pedido                
            FROM th_pedido o
                LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad
                LEFT JOIN cat_estados e ON e.ESTADO = o.status
                LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte
                LEFT JOIN c_usuario u ON u.cve_usuario = o.Cve_Usuario
                LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
                LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio
                LEFT JOIN c_articulo a ON a.cve_articulo = od.Cve_articulo
            WHERE o.Activo = 1";

        if (!empty($almacen)) {
            $sql .= " AND o.cve_almac = '$almacen'";
        }
        if (!empty($status)) {
            $sql .= " AND o.status = '$status'";
        }
        if (!empty($criterio) ) 
        {
            $sql .= "AND o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR p.Descripcion like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%'";
        }


        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $sql .= " AND o.Fec_Pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        }
        elseif (!empty($fecha_inicio)) {
            $sql .= " AND o.Fec_Pedido >= '$fecha_inicio'";
        }
        elseif (!empty($fecha_fin)) {
            $sql .= " AND o.Fec_Pedido <= '$fecha_fin'";
        }


        if (!empty($factura_inicio) && !empty($factura_fin)) {
            $sql .= " AND o.Fol_folio BETWEEN '$factura_inicio' AND '$factura_fin'";
        }
        elseif (!empty($factura_inicio)) {
            $sql .= " AND o.Fol_folio >= $factura_inicio";
        }
        elseif (!empty($factura_fin)) {
            $sql .= " AND o.Fol_folio <= $factura_fin";
        }

        $sql .= " AND o.status <> 'O'";


        $sql .= " GROUP BY o.Fol_folio ORDER BY o.Fec_Entrada DESC;";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }



        $asignar = '';
        $planear = '';

        $i = 0;

        $responce ;
        while ($row = mysqli_fetch_array($res)) {
            $row = array_map("utf8_encode", $row);
            extract($row);
            $sqlMore = "SELECT * FROM (
                -- (SELECT COALESCE(SUM((alto/1000) * (fondo/1000) * (ancho/1000)), 0) AS volumen FROM c_articulo WHERE cve_articulo IN (SELECT cve_articulo FROM td_pedido WHERE Fol_folio = '$orden')) AS volumen,
                -- (SELECT IFNULL(SUM(a.peso), 0) AS peso FROM td_pedido tdp LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo WHERE tdp.Fol_folio = '$orden') AS peso,
                (SELECT PorcentajeSurtidoOrden('$orden') AS surtido) AS surtido
            )";
            $queryMore = mysqli_query($conn, $sqlMore);
            $extraData = mysqli_fetch_assoc($queryMore);
            extract($extraData);
            $responce->rows[$i]['id']= $id_pedido;
            $responce->rows[$i]['cell']=array($id_pedido, $asignar, $planear,$orden, $orden_cliente, $prioridad, $status, $cliente, $direccion, $dane, $ciudad, $estado, $cantidad, $volumen, $peso, $fecha_pedido, $fecha_surtido, $asignado, $surtido);
            $i++;
        }

        $count = $i;
        if ($count >0) {
            $total_pages = ceil($count/$limit);
        }
        else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page=$total_pages;
        }

        $responce->page = $page;
        $responce->total = $total_pages;
        $responce->records = $i;

        echo json_encode($responce);



    } 



    else if(isset($_POST['action']) && $_POST['action'] === 'getArticuloSurtido'){
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $sord = $_POST['sord']; // get the direction

        list($folio, $articulo) = explode(' | ',$_POST['folio']);

        $start = $limit*$page - $limit; // do not put $limit*($page - 1)

        if (!$sidx) {
            $sidx =1;
        }
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sqlCount = "SELECT COUNT(cve_ubicacion) AS cuenta FROM V_ExistenciaGral WHERE cve_articulo = '$articulo' AND cve_ubicacion IN (SELECT idy_ubica FROM c_ubicacion WHERE picking = 'S');";

        $res = mysqli_query($conn, $sqlCount);
        $row = mysqli_fetch_array($res);
        $count = $row['cuenta'];

        $sql = "SELECT  V.cve_articulo AS clave,
                        c.des_articulo AS articulo,
                        l.LOTE AS lote,
                        MAX(l.CADUCIDAD) AS caducidad,
                        (SELECT COALESCE(DATE_FORMAT(fecha_fin, '%d-%m-%Y'), '--') FROM td_entalmacen WHERE cve_articulo = V.cve_articulo AND cve_lote = V.cve_lote ORDER BY id DESC LIMIT 1) AS fecha,
                        u.CodigoCSD AS ubicacion,
                        V.Existencia,
                        p.Num_cantidad AS pedidas,
                        p.Fol_folio AS folio,
                        0 AS surtidas
                FROM V_ExistenciaGral V 
                LEFT JOIN c_articulo c ON c.cve_articulo = V.cve_articulo
                LEFT JOIN c_lotes l ON l.cve_articulo = V.cve_articulo
                LEFT JOIN c_ubicacion u ON u.idy_ubica = V.cve_ubicacion
                LEFT JOIN td_pedido p ON p.Fol_folio = '$folio' AND p.cve_articulo = V.cve_articulo
                WHERE V.cve_articulo = '$articulo' 
                AND V.tipo = CONVERT('ubicacion' USING utf8)
                AND cve_ubicacion IN (SELECT idy_ubica FROM c_ubicacion WHERE picking = 'S')
                GROUP BY V.cve_ubicacion
                ORDER BY fecha DESC
                LIMIT {$start}, {$limit};";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        if ($count >0) {
            $total_pages = ceil($count/$limit);
            //$total_pages = ceil($count/1);
        } else {
            $total_pages = 0;
        } if ($page > $total_pages) {
            $page=$total_pages;
        }

      $responce->page = $page;
      $responce->total = $total_pages;
      $responce->records = $count;

      $asignar = '';
      $planear = '';

      $arr = array();
      $i = 0;
        while ($row = mysqli_fetch_array($res)) {
            $row = array_map("utf8_encode", $row);
            $arr[] = $row;
            extract($row);
            $responce->rows[$i]['id']= $i;
            $responce->rows[$i]['cell']=array($folio, $clave, $articulo, $lote, $caducidad, $fecha, $ubicacion, $Existencia, $pedidas, $surtidas);
            $i++;
        }
        echo json_encode($responce);
}



if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'obtenerZonasEmbarque') {
    $almacen = $_POST['almacen'];
    $folio   = $_POST['folio'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if(isset($_POST['modulo_ot']) || isset($_POST['modulo_areaembarque']))
    {
        $sql = "SELECT id FROM c_almacenp WHERE clave = '$almacen';";
        $query = mysqli_query($conn, $sql);
        $almacen = mysqli_fetch_assoc($query)['id'];
    }

    //$sql = "SELECT COUNT(*) as existe FROM th_consolidado WHERE No_OrdComp = '$folio';
    $sql = "SELECT MAX(existe) AS existe FROM (
                SELECT COUNT(*) AS existe FROM th_consolidado WHERE No_OrdComp = '$folio'
                UNION 
                SELECT COUNT(*) AS existe FROM t_consolidado WHERE Fol_Consolidado = '$folio'
            ) AS c";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
    $consolidado = $row["existe"];

    $sql = "";
    if($consolidado >= 1)
        $sql = "SELECT cve_ubicacion, descripcion FROM t_ubicacionembarque WHERE cve_almac = '$almacen' AND AreaStagging = 'S';";
    else
        $sql = "
    SELECT cve_ubicacion, descripcion FROM t_ubicacionembarque WHERE cve_almac = '$almacen' AND AreaStagging = 'N'
    UNION
    SELECT CONCAT('ESBLSALIDA-', idy_ubica) AS cve_ubicacion, CodigoCSD AS descripcion FROM c_ubicacion WHERE cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen') AND IFNULL(STATUS, 'N') = 'S';";
    $query = mysqli_query($conn, $sql);

    $result = [];
    while($rows = mysqli_fetch_assoc($query) ){
        $result[] = ['id' => $rows['cve_ubicacion'], 'nombre'=>utf8_encode($rows['descripcion'])];
    }

    echo json_encode(array(
        'success' => true,
        "data"    => $result
    ));
    exit;
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'verificarSiTieneOla') {
    $folio = $_POST['folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql2 = "SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = '$folio'";
      $res2  = mysqli_query($conn, $sql2);
      $Fol_PedidoCon = mysqli_fetch_array($res2, MYSQLI_ASSOC);
      $folio = $Fol_PedidoCon['Fol_PedidoCon'];

      $tieneOla = false;
      if($folio) $tieneOla = true;

    echo json_encode(array(
        "tieneOla" => $tieneOla
    ));
    exit;
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'obtenerAreasRecepcion') {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $fol_folio = $_POST['folio'];
    $sql = "SELECT cve_ubicacion, desc_ubicacion FROM tubicacionesretencion WHERE cve_almacp = (SELECT statusaurora FROM th_pedido WHERE Fol_folio = '{$fol_folio}');";
    $query = mysqli_query($conn, $sql);

    $result = [];
    while($rows = mysqli_fetch_assoc($query) ){
        $result[] = ['id' => $rows['cve_ubicacion'], 'nombre'=>$rows['desc_ubicacion']];
    }

    echo json_encode(array(
        'success' => true,
        "data"    => $result
    ));
    exit;
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'obtenerUbicacionManufactura') {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $id_almacen = $_POST['almacen'];
    $sql = "SELECT idy_ubica, CodigoCSD FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $id_almacen);";
    $query = mysqli_query($conn, $sql);

    $result = [];
    while($rows = mysqli_fetch_assoc($query) ){
        $result[] = ['id' => $rows['idy_ubica'], 'nombre'=>$rows['CodigoCSD']];
    }

    echo json_encode(array(
        'success' => true,
        "data"    => $result
    ));
    exit;
}


if (isset($_GET) && !empty($_GET) && isset($_GET['action']) && $_GET['action'] == '') {
    $criterio = $_GET['criterio'];
    $fecha_inicio = $_GET['fechaInicio'];
    $fecha_fin = $_GET['fechaFin'];
    //$filtro = $_GET['filtro'];
    $status = $_GET['status'];
    $ciudad = $_GET['ciudad'];
    $almacen = $_GET['almacen'];
    $factura_inicio = $_GET['facturaInicio'];
    $factura_fin = $_GET['facturaFin'];
    $totalpedidos = 0;
    $pesototal = 0;
    $volumentotal = 0;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT  ped.Fol_folio AS totalpedidos,
                    SUM(item.Num_cantidad * a.peso) AS pesototal,
                    SUM(item.Num_cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))) AS volumentotal
            FROM th_pedido ped
                LEFT JOIN td_pedido item ON item.Fol_folio = ped.Fol_folio
                LEFT JOIN c_articulo a ON a.cve_articulo = item.Cve_articulo
            WHERE ped.Activo = 1";

    if (!empty($almacen)) {
        $sql .= " AND ped.cve_almac = '$almacen'";
    }


    if (!empty($status)) {
        $sql .= " AND ped.status = '$status'";
    }


    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sql .= " AND ped.Fec_Pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    }
    elseif (!empty($fecha_inicio)) {
        $sql .= " AND ped.Fec_Pedido = '$fecha_inicio'";
    }
    elseif (!empty($fecha_fin)) {
        $sql .= " AND ped.Fec_Pedido = '$fecha_fin'";
    }


    if (!empty($factura_inicio) && !empty($factura_fin)) {
        $sql .= " AND ped.Fol_folio BETWEEN '$factura_inicio' AND '$factura_fin'";
    }
    elseif (!empty($factura_inicio)) {
        $sql .= " AND ped.Fol_folio > $factura_inicio";
    }
    elseif (!empty($factura_fin)) {
        $sql .= " AND ped.Fol_folio < $factura_fin";
    }

    $sql .= " AND ped.status != 'O'";
    $sql .= " GROUP BY ped.Fol_folio ";
    
    $query = mysqli_query($conn, $sql);
    if ($query->num_rows > 0) {
        $result = mysqli_fetch_all($query);
        $totalpedidos = $query->num_rows;
        foreach ($result as $datos) {
            $pesototal += $datos[1];
            $volumentotal += $datos[2];
        }
    }
    mysqli_close($conn);

    echo json_encode([
     "totalpedidos"  => $totalpedidos,
     "pesototal"     => $pesototal,
     "volumentotal"  => $volumentotal
    ]);
    exit;
}



if(isset($_GET) && !empty($_GET) && $_GET['action'] == 'obtenerPrioridad'){
        $folio = $_GET['folio'];
        $status = "";
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT ID_Tipoprioridad FROM th_pedido WHERE Fol_folio = '$folio';";
        $query = mysqli_query($conn, $sql);
        //if($query->num_rows > 0){
            $row = mysqli_fetch_array($query);
            $prioridad = $row["ID_Tipoprioridad"];
        //}
        echo json_encode(array(
            "prioridad" => $prioridad,
            "sql" => $sql
        ));exit;
    }


if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'obtenerStatus'){
        $folio = $_GET['folio'];
        $status = "";
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT status, (SELECT COUNT(*) FROM td_surtidopiezas WHERE fol_folio = '$folio') AS surtiendo FROM th_pedido WHERE Fol_folio = '$folio';";
        $query = mysqli_query($conn, $sql);
        if($query->num_rows > 0){
            $row = mysqli_fetch_row($query);
            $status = $row[0];
            $surtiendo = $row[1];
        }
        echo json_encode(array(
            "status"    => $status,
            "surtiendo" => $surtiendo
        ));exit;
    }


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'subirImagen') {
  $result=array();
  $result["status"]=false;
  if(!empty($_POST['numeroImagen']) || !empty($_POST['idPedido']) || !empty($_FILES['file']['name'])){
    $uploadedFile = '';
    if(!empty($_FILES["file"]["type"])){
        $fileName = time().'_'.$_FILES['file']['name'];
        $valid_extensions = array("jpeg", "jpg", "png");
        $temporary = explode(".", $_FILES["file"]["name"]);
        $file_extension = end($temporary);
        if((($_FILES["file"]["type"] == "image/png") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/jpeg")) && in_array($file_extension, $valid_extensions)){
            $sourcePath = $_FILES['file']['tmp_name'];
            $targetPath = "../../../img/embarques/".$fileName;
            if(move_uploaded_file($sourcePath,$targetPath)){
                $uploadedFile = $fileName;
                $numFoto = $_POST['numeroImagen'];
                $folio = $_POST['idPedido'];
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sql = "UPDATE th_pedido SET foto".$numFoto." = '".$fileName."' WHERE Fol_folio = '".$folio."' ;";
                $query = mysqli_query($conn, $sql);
                $result["nameFile"]=$fileName;
                $result["numeroImagen"]=$numFoto;
                $result["status"]=true;
            }
        }
    }
  }
  echo json_encode($result);
}


if( $_POST['action'] == 'detalle_monitoreo' ) {

    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $Folio_Pro = $_POST['Folio_Pro'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sql = "SELECT COUNT(Cve_Articulo) as total from td_ordenprod where Folio_Pro = '$Folio_Pro'";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];
*/
/*
    $sql = "SELECT op.Folio_Pro, op.Cve_Articulo AS clave, a.des_articulo AS descripcion, op.Cve_Lote, op.Fecha_Prod, op.Cantidad, op.Cantidad_Producida, op.Cantidad_Faltante,
    (SELECT nombre_completo FROM c_usuario WHERE id_user = op.Usr_Armo) AS usuario 
        FROM c_articulo a, td_ordenprod op 
         where op.Cve_Articulo = a.cve_articulo and op.Folio_Pro = '$Folio_Pro'
        limit $start, $limit";
*/
            $sql = "SELECT DISTINCT
                        p.Fol_folio AS Fol_folio,
                        p.Cve_articulo AS Cve_articulo,
                        a.des_articulo AS des_articulo,
                        IFNULL(IF(a.control_lotes = 'S', L.Lote, IF(a.control_numero_series = 'S', ser.numero_serie, '')), '') AS Lote,
                        IFNULL(IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad,
                        p.Num_cantidad AS Cantidad
                    FROM td_pedido p
                    LEFT JOIN td_surtidopiezas s ON s.fol_folio = p.Fol_folio AND p.Cve_articulo = s.Cve_articulo
                    LEFT JOIN c_articulo a ON p.Cve_articulo = a.cve_articulo
                    LEFT JOIN c_lotes L ON L.cve_articulo = s.Cve_articulo AND s.LOTE = L.Lote
                    LEFT JOIN c_serie ser ON ser.cve_articulo = s.Cve_articulo AND ser.numero_serie = s.LOTE
                    WHERE p.Fol_folio = '$Folio_Pro'
                    ";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " limit $start, $limit ";
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

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row );
        extract($row);
     //   $linea = array_search($clave,$lineas) + 1;
        $arr[] = $row;
        $responce->rows[$i]['id']=$i;
        $responce->rows[$i]['cell']=array($Fol_folio, $Cve_articulo, $des_articulo, $Lote, $Caducidad,  $Cantidad);
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'verificar_folio')
{
        $folio = $_POST['folio'];

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT * FROM th_pedido WHERE Fol_folio = '$folio';";
        $query = mysqli_query($conn, $sql);
        $existe = 0;
        if($query->num_rows > 0){
            $existe = 1;
        }
        echo json_encode(array(
            "existe"    => $existe
        ));exit;
    }
