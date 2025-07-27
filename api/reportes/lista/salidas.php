<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'load') 
{
    $page         = $_POST['page']; // get the requested page
    $limit        = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx         = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord         = $_POST['sord']; // get the direction
    $almacen      = $_POST['almacen'];
    $search       = $_POST['search'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = $_POST['fecha_fin'];
    $tipo_salida  = $_POST['tipo_salida'];


    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $and ="";

    if(!$sidx) $sidx =1;
    if($search != "")
    {
        $and = " AND (m.Des_Motivo LIKE '%{$search}%' OR md.MOT_DESC LIKE '%{$search}%' OR pr.Nombre  LIKE '%{$search}%' OR c.RazonSocial LIKE '%{$search}%' OR cp.RazonSocial LIKE '%{$search}%' OR c.Cve_Clte LIKE '%{$search}%' OR cp.Cve_CteProv LIKE '%{$search}%' OR ts.Fol_Folio LIKE '%{$search}%' OR ts.RefFolio LIKE '%{$search}%' OR ts.cve_usuario LIKE '%{$search}%') ";
    }

    $sql_tipo_salida = "";
    if($tipo_salida != 'todos')
    {
        $tipo = "";
        if($tipo_salida == 'devolucion') $tipo = 1;
        if($tipo_salida == 'muestras') $tipo = 2;
        if($tipo_salida == 'ajustes') $tipo = 3;
        if($tipo_salida == 'merma') $tipo = 4;

        $sql_tipo_salida = " AND ts.Tipo_Salida = $tipo ";
    }


    $sql_fecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        $sql_fecha = " AND IFNULL(ts.fec_salida, '0000-00-00 00:00:00') BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $sql_fecha = " AND IFNULL(ts.fec_salida, '0000-00-00 00:00:00') >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        $sql_fecha = " AND IFNULL(ts.fec_salida, '0000-00-00 00:00:00') <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      }


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');

    $sql = "
              SELECT DISTINCT ts.Fol_Folio AS folio, 
              DATE_FORMAT(ts.fec_salida, '%d-%m-%Y | %H:%i:%s') AS fecha_salida, 
              CASE 
                  WHEN ts.Tipo_Salida = 1 THEN 'Devolución a Proveedor'
                  WHEN ts.Tipo_Salida = 2 THEN 'Muestras, Obsequios, Promociones'
                  WHEN ts.Tipo_Salida = 3 THEN 'Ajuste por Pedido o por OT'
                  WHEN ts.Tipo_Salida = 4 THEN 'Merma o Destrucción'
              END AS Tipo_Salida,
              CASE 
                  WHEN ts.Tipo_Salida = 1 THEN IFNULL(md.MOT_DESC, '')
                  WHEN ts.Tipo_Salida >= 2 THEN IFNULL(m.Des_Motivo, '')
              END AS motivo,
              ts.cve_usuario,
              #ce.DESCRIPCION AS estatus,
              CASE 
                  WHEN ts.Status = 'S' THEN 'Abierto'
                  WHEN ts.Status IN ('P', 'T') THEN 'Cerrado'
                  WHEN ts.Status = 'K' THEN 'Cancelado'
              END AS estatus,
              IF(ts.Tipo_Salida = 1, IFNULL(ts.RefFolio, ''), '') AS folio_ref,
              IF(ts.Tipo_Salida = 1, IFNULL(pr.Nombre, ''), '') AS proveedor,
              IF(ts.Tipo_Salida IN (2,3) AND (IFNULL(ts.Cve_Ref1, '') != '' OR IFNULL(ts.Cve_Ref2, '') != ''), 
                CONCAT('(',
                  IFNULL(IF(
                    IFNULL(c.Cve_Clte, '') = '', 
                    IFNULL(c.Cve_Clte, ''),
                    IFNULL(cp.Cve_CteProv, '')
                    
                     ), ''
                         ), ') ',
                  IFNULL(IFNULL(c.RazonSocial, cp.RazonSocial), '')
                  ), '') AS cliente
      FROM th_salalmacen ts 
            LEFT JOIN c_almacenp al ON al.id = ts.Cve_Almac
            LEFT JOIN c_motivo m ON m.id = ts.Cve_Razon
            LEFT JOIN motivos_devolucion md ON md.MOT_ID = ts.Cve_Razon
            #LEFT JOIN cat_estados ce ON ce.ESTADO = ts.Status
            LEFT JOIN c_proveedores pr ON pr.ID_Proveedor = ts.Cve_Ref1 AND ts.Tipo_Salida = 1
            LEFT JOIN c_cliente c ON c.Cve_Clte = ts.Cve_Ref1 AND ts.Tipo_Salida IN (2,3) AND IFNULL(ts.Cve_Ref1, '') != ''
            LEFT JOIN c_cliente cp ON cp.Cve_CteProv = ts.Cve_Ref2 AND ts.Tipo_Salida IN (2,3) AND IFNULL(ts.Cve_Ref2, '') != ''
      WHERE ts.Cve_Almac = {$almacen} 
      {$sql_fecha} 
      {$sql_tipo_salida} 
      {$and}
    ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit; ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //mysqli_close($conn);
    $responce = new stdClass();

    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->query = $sql;
    $i = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);
        //$responce->rows[$i]['id']=$row['id'];
        $responce->rows[$i]['cell'] = ['',
                                       $folio,
                                       $fecha_salida,
                                       $Tipo_Salida,
                                       $motivo,
                                       $cve_usuario,
                                       $estatus,
                                       $folio_ref,
                                       $proveedor,
                                       $cliente
                                       ];
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'detalle') 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['folio'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql="
        SELECT DISTINCT ts.cve_articulo AS clave, 
            a.des_articulo AS articulo,
            IFNULL(ts.Cve_Lote, '') AS lote_serie, 
            IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(ts.Cve_Lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
            ts.num_cantsurt AS cantidad, 
            #ce.DESCRIPCION AS estatus
          CASE 
              WHEN ts.Status = 'S' THEN 'Abierto'
              WHEN ts.Status IN ('P', 'T') THEN 'Cerrado'
              WHEN ts.Status = 'K' THEN 'Cancelado'
          END AS estatus,
          ch.CveLP AS LP
        FROM td_salalmacen ts
        LEFT JOIN c_articulo a ON a.cve_articulo = ts.cve_articulo
        LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = ts.Cve_Lote
        LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.cve_articulo AND ts.Cve_Lote = tc.cve_lote AND ts.cve_almac = tc.cve_almac AND ts.fol_folio = tc.destino
        LEFT JOIN t_MovCharolas tm ON tm.id_kardex = tc.id 
        LEFT JOIN c_charolas ch ON tm.ID_Contenedor = ch.IDContenedor
        #LEFT JOIN cat_estados ce ON ce.ESTADO = ts.Status
        WHERE ts.fol_folio = '{$folio}' AND ts.Activo = 1";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit; ";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //mysqli_close($conn);
    $responce = new stdClass();

    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $i = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

/*
        $Nserie = "";
        $NLote = "";
        //**************************************************
        //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
        //**************************************************
        //Archivos donde se encuentra esta función:
        //api\embarques\lista\index.php
        //api\reportes\lista\existenciaubica.php
        //api\reportes\lista\concentradoexistencia.php
        //app\template\page\reportes\existenciaubica.php
        //\Application\Controllers\EmbarquesController.php
        //\Application\Controllers\InventarioController.php
        //**************************************************
        //clave busqueda: COLPCP
        //**************************************************
        $valor1 = 0;
        if($piezasxcajas > 0)
           $valor1 = $cantidad/$piezasxcajas;

        if($cajasxpallets > 0)
           $valor1 = $valor1/$cajasxpallets;
       else
           $valor1 = 0;

        $Pallet = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $cantidad - ($Pallet*$piezasxcajas*$cajasxpallets);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($piezasxcajas > 0)
               $valor2 = ($cantidad_restante/$piezasxcajas);// - ($Pallet*$cantidad);
        }
        $Cajas = intval($valor2);

        if($piezasxcajas == 1 || $piezasxcajas == 0 || $piezasxcajas == "") $valor2 = 0;
        $Piezas = $cantidad_restante;

        $Piezas = 0;
        if($piezasxcajas == 1) 
        {
            $valor2 = 0; 
            $Cajas = 0;
            $Piezas = $cantidad_restante;
        }
        else if($piezasxcajas == 0 || $piezasxcajas == "")
        {
            if($piezasxcajas == "") $piezasxcajas = 0;
            $valor2 = 0; 
            $Cajas = 0;
            $Piezas = $cantidad_restante;
        }
        $cantidad_restante = $cantidad_restante - ($Cajas*$piezasxcajas);

        if(!is_int($valor2))
        {
           //$Piezas = ($Cajas*$cantidad_restante) - $piezasxcajas;
            $Piezas = $cantidad_restante;
        }

        if($piezasxcajas == 1 && $cajasxpallets == 1)
        {
            $Pallet = 0;
            $Cajas = 0;
            $Piezas = $cantidad;
        }


        //**************************************************
        if($control_serie == "S") $Nserie = $LOTE;
        else if($control_lote == "S") $NLote = $LOTE;
*/
        $responce->rows[$i]['id']=$row['clave'];
        $responce->rows[$i]['cell']=array($clave, $articulo, $lote_serie, $caducidad, $cantidad, $estatus, $LP);
        $i++;
    }
    mysqli_close($conn);
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'get_gps') 
{
    $id = $_POST['folio'];

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

/*
SELECT * FROM (
    SELECT DISTINCT
        (SELECT clave FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte)) AS id_destinatario,
        (SELECT des_cia FROM c_compania WHERE cve_cia = (SELECT cve_cia FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte))) AS razonsocial,
        (SELECT latitud FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte)) AS latitud,
        (SELECT longitud FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte)) AS longitud,
        'Almacen-Data' AS fol_folio,
        '' AS Estatus
    FROM th_cajamixta caja
        LEFT JOIN c_tipocaja t ON t.id_tipocaja = caja.cve_tipocaja 
        LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id'))
        LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo
        LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo
        LEFT JOIN c_destinatarios d ON d.id_destinatario IN (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE Rel_PedidoDest.Fol_folio = caja.fol_folio)
    WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id') AND caja.Activo = 1
   ) AS g WHERE IFNULL(g.latitud, '') != '' AND IFNULL(g.longitud, '') != ''*/
    $sql="
        SELECT DISTINCT
        (SELECT clave FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte)) AS id_destinatario,
        (SELECT des_cia FROM c_compania WHERE cve_cia = (SELECT cve_cia FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte))) AS razonsocial,
        (SELECT latitud FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte)) AS latitud,
        (SELECT longitud FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte)) AS longitud,
        'Almacen-Data' AS fol_folio,
        '' AS Estatus
    FROM c_almacenp a
        LEFT JOIN c_destinatarios d ON d.id_destinatario IN (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE Rel_PedidoDest.Fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id'))
    WHERE d.id_destinatario IN (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE Rel_PedidoDest.Fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id'))


    UNION

        SELECT DISTINCT
            d.id_destinatario,
            d.razonsocial,
            d.latitud,
            d.longitud,
            caja.fol_folio,
            p.status AS Estatus
        FROM th_cajamixta caja
            LEFT JOIN c_tipocaja t ON t.id_tipocaja = caja.cve_tipocaja 
            LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id'))
            LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.id_destinatario IN (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE Rel_PedidoDest.Fol_folio = caja.fol_folio)
            LEFT JOIN th_pedido p ON p.Fol_folio = caja.fol_folio
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id') AND caja.Activo = 1 AND IFNULL(d.latitud, '') != '' AND IFNULL(d.longitud, '') != ''

        UNION

        SELECT DISTINCT
            d.id_destinatario,
            d.razonsocial,
            d.latitud,
            d.longitud,
            t.fol_folio,
            p.status AS Estatus
        FROM t_tarima t
            LEFT JOIN c_articulo ar ON ar.cve_articulo = t.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.lote = t.lote
            LEFT JOIN c_destinatarios d ON d.id_destinatario in (SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE Rel_PedidoDest.Fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id'))
            LEFT JOIN th_pedido p ON p.Fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id')
        WHERE t.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '$id') AND t.Activo = 1 AND IFNULL(d.latitud, '') != '' AND IFNULL(d.longitud, '') != '';
    ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    mysqli_close($conn);
//    $responce = new stdClass();
//
//    if( $count >0 ) 
//    {
//        $total_pages = ceil($count/$limit);
//    } 
//    else 
//    {
//        $total_pages = 0;
//    } 
//    if ($page > $total_pages)
//        $page=$total_pages;
//
//    $responce->page = $page;
//    $responce->total = $total_pages;
//    $responce->records = $count;
    $responce->sql = $sql;
//
    $i = 0;

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

        $responce->rows[$i]=array($id_destinatario, $razonsocial, $latitud, $longitud, $fol_folio, $Estatus);
        $i++;
    }

    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'getDataPDF')
{
    $id = $_POST['id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
  
    $sqlHeader1 = "
        SELECT  
            COALESCE(e.descripcion,'--') as ubicacion,
            COALESCE(u.nombre_completo,'--')as usuario
        FROM th_ordenembarque o        
            LEFT join c_usuario u on u.id_user = o.cve_usuario
            left join t_ubicacionembarque e on e.ID_Embarque = o.t_ubicacionembarque_id
        WHERE o.ID_OEmbarque = {$id};
    ";
    $queryHeader1 = mysqli_query($conn, $sqlHeader1);

    $sqlHeader = "
        SELECT  
            o.ID_OEmbarque AS id,
            COALESCE(DATE_FORMAT(o.fecha, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_embarque,
            '--' AS fecha_entrega,
            COALESCE(o.destino, '--') AS destino,
            COALESCE(o.comentarios, '--') AS comentarios,
            '--' AS chofer,
            COALESCE(t.Nombre,'--') AS transporte,
            COALESCE(o.status, '--') AS status,
            (SELECT COALESCE(SUM(peso), 0) FROM c_articulo WHERE cve_articulo IN (SELECT Cve_articulo FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque))) AS peso,
            TRUNCATE((SELECT COALESCE(SUM((alto/1000) * (ancho/1000) * (fondo/1000)), 0) FROM c_articulo WHERE cve_articulo IN (SELECT Cve_articulo FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque))),4) AS volumen,
            (SELECT COALESCE(SUM(1), 0) FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)) AS total_cajas,
            (SELECT COALESCE(SUM(Cantidad), 0) FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)) AS total_piezas
        FROM th_ordenembarque o        
            LEFT JOIN t_transporte t ON t.id = o.ID_Transporte
        WHERE o.ID_OEmbarque = {$id};
    ";
  
    $queryHeader = mysqli_query($conn, $sqlHeader);
    $sqlBody = "
        SELECT
            caja.fol_folio as folio,
            caja.NCaja as no_partida,
            t.clave as tipo_caja,
            t.descripcion descripcion,
            caja.Guia as guia, 
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
            END) AS volumen,
            COALESCE(TRUNCATE(caja.Peso,4),0) as peso, 
            (select RazonSocial from c_cliente inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte where th_pedido.Fol_folio = caja.fol_folio) as cliente,
            (select Cve_Clte from th_pedido where th_pedido.Fol_folio = caja.fol_folio) as cve_cliente
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id});
    ";
    $queryBody = mysqli_query($conn, $sqlBody);
  
    $sqlToal = "
        SELECT
            COALESCE(COUNT(DISTINCT(caja.fol_folio)),0) as pedidos,
            COALESCE(COUNT(DISTINCT(caja.Guia)),0) as guia
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id});
    ";
    $queryTotal = mysqli_query($conn, $sqlToal);
  
    $header1 = mysqli_fetch_all($queryHeader1, MYSQLI_ASSOC)[0];
    $header = mysqli_fetch_all($queryHeader, MYSQLI_ASSOC)[0];
    $body = mysqli_fetch_all($queryBody, MYSQLI_ASSOC);
    $total = mysqli_fetch_all($queryTotal, MYSQLI_ASSOC);
    mysqli_close($conn);
  
    echo json_encode(array(
        "header1"    => $header1,
        "header"    => $header,
        "body"    => $body,
        "total" => $total[0]
    ));
}




if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'marcar_entregado')
{
    $folio = $_POST['folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
  
    $sql = "UPDATE th_ordenembarque SET status = 'F' WHERE ID_OEmbarque = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE td_ordenembarque SET status = 'F', fecha_entrega = NOW() WHERE ID_OEmbarque = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    mysqli_close($conn);
  
    echo json_encode(array(
        "success" => true
    ));
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'aviso_despacho')
{
    $folio = $_POST['folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
  
    $sql = "SELECT DISTINCT
               IFNULL(CONCAT(thc.CodB_Prov,'|',thc.NIT_Prov,'|', thc.Nom_Prov,'|', thc.Cve_CteCon,'|', thc.CodB_CteCon,'|', thc.Nom_CteCon,'|', thc.Dir_CteCon,'|'), '') AS txt1, 
               IFNULL(CONCAT(thc.Cd_CteCon,'|', thc.NIT_CteCon,'|', thc.Cod_CteCon,'|', thc.CodB_CteEnv,'|', thc.Nom_CteEnv,'|', thc.Dir_CteEnv,'|', thc.Cd_CteEnv,'|'), '') AS txt2, 
               IFNULL(CONCAT(thc.Tel_CteEnv,'|', thc.Fec_Entrega,'|', thc.Tot_Cajas,'|', thc.Tot_Pzs,'|', thc.Placa_Trans,'|', thc.Sellos,'|', tdc.No_OrdComp,'|'), '') AS txt3, 
               IFNULL(CONCAT(tdc.Fec_OrdCom,'|', tdc.Cve_Articulo,'|', a.des_articulo,'|', (IF((SELECT COUNT(*) FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '{$folio}'))) <= 1, 0, 1)),'|', a.cve_codprov,'|', a.barras2,'|', a.num_multiplo,'|', tdc.Cant_Pedida,'|'), '') AS txt4, 
               IFNULL(CONCAT(tds.Cantidad,'|', 0,'|', tdc.Unid_Empaque,'|', if(a.num_multiplo > 0, TRUNCATE(tds.Cantidad/a.num_multiplo, 0), tds.Cantidad),'|', tdc.CodB_Cte,'|', c.RazonSocial,'|', tdc.Fol_PedidoCon,'|', tds.LOTE,'|', c_lotes.Caducidad,'|', ''), '') AS txt5
        FROM td_consolidado tdc
        LEFT JOIN th_consolidado thc ON tdc.Fol_PedidoCon = thc.Fol_PedidoCon
        LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_Articulo
        LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = tdc.Fol_Folio AND tdc.Cve_Articulo = tds.Cve_articulo 
        LEFT JOIN th_pedido thp ON thp.Fol_folio = tds.fol_folio
        LEFT JOIN c_cliente c ON c.Cve_Clte = thp.Cve_clte AND c.Cve_CteProv = thp.Cve_CteProv
        LEFT JOIN c_lotes ON c_lotes.Lote = tds.LOTE AND c_lotes.cve_articulo = tds.Cve_articulo
        INNER JOIN td_ordenembarque toe ON toe.Fol_folio = tds.fol_folio AND toe.ID_OEmbarque = '{$folio}'";
    $query = mysqli_query($conn, $sql);
    $contenido = "";
    while($row_contenido = mysqli_fetch_array($query))
    $contenido .= $row_contenido["txt1"].$row_contenido["txt2"].$row_contenido["txt3"].$row_contenido["txt4"].$row_contenido["txt5"]."\n";

    //$contenido = "1|2|3|4|5|";

    //$archivo = fopen('../../../uploads/archivo.txt','w+');
    //fputs($archivo,$contenido);
    //fclose($archivo);

    mysqli_close($conn);

    echo json_encode(array(
        "success" => true,
        "text" => $contenido
    ));
}



if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelEmbarque')
{
    include_once('../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php');
//******************************************************************
/*
    $id = $_POST['id'];
    $title = "Reporte Embarque #{$id}.xlsx";

    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($title).'"');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');   
*/
//******************************************************************
/*
    $sqlHeader = "
        SELECT  
            o.ID_OEmbarque AS id,
            COALESCE(DATE_FORMAT(o.fecha, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_embarque,
            '--' AS fecha_entrega,
            COALESCE(o.destino, '--') AS destino,
            COALESCE(o.comentarios, '--') AS comentarios,
            '--' AS chofer,
            t.Nombre AS transporte,
            COALESCE(o.status, '--') AS status,
            TRUNCATE((SELECT (COALESCE(sum(c_articulo.peso*td_surtidopiezas.Cantidad),0)) FROM c_articulo INNER JOIN td_surtidopiezas on td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo where td_surtidopiezas.fol_folio in ( SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),4) AS peso,
            TRUNCATE((SELECT (COALESCE(SUM(((alto/1000) * (ancho/1000) * (fondo/1000))*td_surtidopiezas.Cantidad), 0))       FROM c_articulo INNER JOIN td_surtidopiezas on td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo where td_surtidopiezas.fol_folio in ( SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),4) AS volumen,
            (SELECT COALESCE(SUM(1), 0) FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)) AS total_cajas,
            TRUNCATE((SELECT COALESCE(SUM(Cantidad), 0) FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = o.ID_OEmbarque)),0) AS total_piezas
        FROM th_ordenembarque o        
            LEFT JOIN t_transporte t ON t.ID_Transporte = o.ID_Transporte
        WHERE o.ID_OEmbarque = {$id};
    ";

    $sqlBody = "
        SELECT
            caja.fol_folio as folio,
            caja.NCaja as no_partida,
            t.clave as tipo_caja,
            t.descripcion descripcion,
            caja.Guia as guia, 
            TRUNCATE((CASE WHEN caja.cve_tipocaja = 1 THEN
                    (
                        SELECT
                        IFNULL(ROUND(SUM(td_cajamixta.Cantidad * ((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000))),3), 0) AS volumentotal
                        FROM td_cajamixta
                        LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                        WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix
                    )
                    ELSE
                    (
                        SELECT ROUND((largo/1000)*(alto/1000)*(ancho/1000),3) FROM c_tipocaja WHERE id_tipocaja = caja.cve_tipocaja
                    ) 
                    END),4) AS volumen,
            (SELECT
                    IFNULL(ROUND(SUM(td_cajamixta.Cantidad * a.peso),3), 0) 
                    FROM td_cajamixta
                    LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
                    WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix)  as peso, 
            (select RazonSocial from c_cliente inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte where th_pedido.Fol_folio = caja.fol_folio) as cliente,
            (select Cve_Clte from th_pedido where th_pedido.Fol_folio = caja.fol_folio) as cve_cliente
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id});
    ";
    $sqlTotal = "
        SELECT
            COALESCE(COUNT(DISTINCT(caja.fol_folio)),0) as pedidos,
            COALESCE(COUNT(DISTINCT(caja.Guia)),0) as guia
        FROM th_cajamixta caja
            left join c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id});
    ";
*/
    //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    //mysqli_set_charset($conn, 'utf8');
    //$queryCabecera = mysqli_query($conn, $sqlHeader);
    //$dataCabecera = mysqli_fetch_all($queryCabecera, MYSQLI_ASSOC)[0];
    //$query = mysqli_query($conn, $sqlBody);
    //$data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    //$queryTotal = mysqli_query($conn, $sqlTotal);
    //$dataTotal = mysqli_fetch_all($queryTotal, MYSQLI_ASSOC);

//******************************************************************
    //$header_head = array('Folio','Fecha Embarque','Fecha Entrega','Destino','Comentarios','Chofer','Transporte','Status','Peso','Volumen','Total Cajas','Total Piezas');
//******************************************************************
    //$header_head = array(
    //  'ID'=>'integer',
    //  'Subject'=>'string',
    //  'Content'=>'string',
    //);

    //$body_head = array($dataCabecera['id'],$dataCabecera['fecha_embarque'],$dataCabecera['fecha_entrega'],$dataCabecera['destino'],$dataCabecera['comentarios'],$dataCabecera['chofer'],$dataCabecera['transporte'],$dataCabecera['status'],$dataCabecera['peso'],$dataCabecera['volumen'],$dataCabecera['total_cajas'],$dataCabecera['total_piezas'],);
    //$header_body = array('Pedido','Partida','Clave','Tipo Caja','Guia','Volumen','Peso');
    //$header_total = array('Total Pedidos','Total Guias');

//******************************************************************
    //$excel = new XLSXWriter();
    //$excel->writeSheetRow('Sheet1', $header_head );
//******************************************************************
    //$excel->writeSheetRow('Sheet1', $header_head );
/*
    $excel->writeSheetRow('Sheet1', $body_head );
    $excel->writeSheetRow('Sheet1', $header_body );
    foreach($data as $d)
    {
        $row = array($d['folio'],$d['no_partida'],$d['tipo_caja'],$d['descripcion'],$d['guia'],$d['volumen'], $d['peso']);
        $excel->writeSheetRow('Sheet1', $row );
        $var_float_de_row = floatval($d['volumen']);
    }
    $excel->writeSheetRow('Sheet1', $header_total);
    foreach($dataTotal as $dt)
    {
        $row = array($dt['pedidos'],$dt['guia']);
        $excel->writeSheetRow('Sheet1', $row );
    }
*/
//    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//    header('Content-Disposition: attachment;filename="' . $title . '"');
//    header('Cache-Control: max-age=0');

//    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($title).'"');
//    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//    header('Content-Transfer-Encoding: binary');
//    header('Cache-Control: must-revalidate');
//    header('Pragma: public');   

//******************************************************************
    //$excel->writeToStdOut($title);
//******************************************************************

    $filename = "example.xlsx";
    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');   
    //$query="my query here";
    //$result = mysql_query($query); 
    //$rows = mysql_fetch_assoc($result); 
    $header = array(
      'ID'=>'integer',
      'Subject'=>'string',
      'Content'=>'string',
    );
    $writer = new XLSXWriter();
    $writer->writeSheetHeader('Sheet1', $header);

    $header_total = array('Total Pedidos','Total Guias');
    $excel->writeSheetRow('Sheet1', $header_total);
/*
    $array = array();
    while ($row=mysql_fetch_row($result))
    {
        for ($i=0; $i<mysql_num_fields($result); $i++ )
        {
        $array[$i] = $row[$i];
        //$array[$i] = strip_tag($row[$i],"<p> <b> <br> <a> <img>");
        }
        $writer->writeSheetRow('Sheet1', $array);
    };
*/
    //$writer->writeSheet($array,'Sheet1', $header);//or write the whole sheet in 1 call    

    $writer->writeToStdOut();

    //$excel->writeToStdOut();
}