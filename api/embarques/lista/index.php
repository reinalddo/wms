<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'load') 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $almacen = $_POST['almacen'];
    $search = $_POST['search'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $ruta = $_POST['ruta'];
    $fecha_inicio = $_POST['fechaini'];
    $fecha_fin = $_POST['fechafin'];
    $status_embarque = $_POST['status_embarque'];

    $and ="";

    if(!$sidx) $sidx =1;
    if($search != "")
    {
        $and = " AND (th_ordenembarque.ID_OEmbarque IN (SELECT ID_OEmbarque FROM td_ordenembarque WHERE Fol_folio LIKE '%".$search."%') OR t_ruta.descripcion LIKE '%".$search."%' OR tipo_transporte.desc_ttransporte LIKE '%".$search."%' OR th_ordenembarque.ID_OEmbarque LIKE '%".$search."%' OR tipo_transporte.clave_ttransporte LIKE '%".$search."%' OR tipo_transporte.desc_ttransporte LIKE '%".$search."%') ";
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
/*
    $sqlCount = "
    SELECT COUNT(*) AS total FROM th_ordenembarque 
        #LEFT JOIN th_consolidado ON th_consolidado.Fol_PedidoCon = th_ordenembarque.ID_OEmbarque
        #LEFT JOIN cat_estados ON cat_estados.ESTADO = th_ordenembarque.status
        #LEFT JOIN t_transporte ON t_transporte.id = th_ordenembarque.ID_Transporte
        #LEFT JOIN tipo_transporte ON tipo_transporte.clave_ttransporte = t_transporte.tipo_transporte
        #LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
        #LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
        #LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = th_pedido.Cve_clte
        #LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta      
    WHERE th_ordenembarque.Activo = 1
    ;";

    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];
*/

    $SQLFecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        /*
        $SQLFecha = " AND th_ordenembarque.fecha BETWEEN STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if($fecha_inicio == $fecha_fin)
            $SQLFecha = " AND th_ordenembarque.fecha = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
    */
          $SQLFecha = " AND DATE(th_ordenembarque.fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(th_ordenembarque.fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
          if($fecha_inicio == $fecha_fin)
          $SQLFecha = " AND DATE(th_ordenembarque.fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_inicio)) 
      {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $SQLFecha = " AND th_ordenembarque.fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_fin)) 
      {
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $SQLFecha = " AND th_ordenembarque.fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
      }


    $filtro_cliente = ""; 
    if(isset($_POST['cve_cliente']))
    {
        if($_POST['cve_cliente'])
        {
            $cve_cliente = $_POST['cve_cliente'];
            //$filtro_cliente = "AND ('$cve_cliente' IN (SELECT pedido.Cve_clte FROM th_pedido pedido WHERE pedido.Fol_folio IN (SELECT orden.Fol_folio FROM td_ordenembarque orden WHERE orden.ID_OEmbarque = th_ordenembarque.ID_OEmbarque)))";
            $filtro_cliente = " AND th.Cve_Clte = '{$cve_cliente}' ";
        }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $cve_proveedor = $_POST['cve_proveedor'];
          $filtro_cliente = "AND ({$cve_proveedor} IN (SELECT IF(IFNULL(pedido.Cve_clte, '') = '', IFNULL(tr.ID_Proveedor, prv.ID_Proveedor), prv.ID_Proveedor)
           FROM th_pedido pedido 
           LEFT JOIN t_ruta tr ON tr.cve_ruta = pedido.cve_ubicacion
           LEFT JOIN c_cliente ct ON ct.Cve_Clte = pedido.Cve_clte
           LEFT JOIN c_proveedores prv ON prv.ID_Proveedor = IFNULL(IFNULL(ct.ID_Proveedor, tr.ID_Proveedor), {$cve_proveedor})
           WHERE pedido.Fol_folio IN (SELECT orden.Fol_folio FROM td_ordenembarque orden WHERE orden.ID_OEmbarque = th_ordenembarque.ID_OEmbarque) AND prv.ID_Proveedor = {$cve_proveedor}))";
/*
            $filtro_cliente = "AND ({$cve_proveedor} IN (SELECT prv.ID_Proveedor
           FROM th_pedido pedido 
           LEFT JOIN c_cliente ct ON ct.Cve_Clte = pedido.Cve_clte
           LEFT JOIN c_proveedores prv ON prv.ID_Proveedor = ct.ID_Proveedor
           WHERE pedido.Fol_folio IN (SELECT orden.Fol_folio FROM td_ordenembarque orden WHERE orden.ID_OEmbarque = th_ordenembarque.ID_OEmbarque) AND prv.ID_Proveedor = {$cve_proveedor}))";
*/
      }
    }

    if($ruta)
    {
        //$and .= " AND '{$ruta}' IN (rre.cve_ruta, t_ruta.cve_ruta) ";
        $and .= " AND r.cve_ruta = '{$ruta}' ";
    }

    $filtro_entregando = "";
    if($status_embarque)
    {
        /*
        if($status_embarque == 'E')
        {
            $and .= " AND th_ordenembarque.status = 'T' ";
            $filtro_entregando = " WHERE ord_emb.status_entregando = 'TF' ";
        }
        else
            */
            $and .= " AND th_ordenembarque.status = '$status_embarque' ";
    }

    $sql = "
    SELECT * FROM (
        SELECT
            th_ordenembarque.ID_OEmbarque AS id,
            COALESCE(DATE_FORMAT(th_ordenembarque.fecha, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_embarque,
            COALESCE(DATE_FORMAT(tdo.fecha_entrega, '%d-%m-%Y %H:%i:%s'), IF(t_transporte.transporte_externo = 1, DATE_FORMAT(th_ordenembarque.fecha, '%d-%m-%Y'), '--')) AS fecha_entrega,
#            TRUNCATE((
#                SELECT (COALESCE(sum(c_articulo.peso*td_surtidopiezas.Cantidad),0)) 
#                    FROM c_articulo INNER JOIN td_surtidopiezas on td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo 
#                    WHERE td_surtidopiezas.fol_folio in 
#                        (SELECT Fol_folio FROM td_ordenembarque 
#                        WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)),4) AS peso,
#            TRUNCATE((
#                SELECT COALESCE(SUM(((alto/1000) * (ancho/1000) * (fondo/1000))*td_surtidopiezas.Cantidad), 0) 
#                FROM c_articulo INNER JOIN td_surtidopiezas on td_surtidopiezas.Cve_articulo = c_articulo.cve_articulo 
#                where td_surtidopiezas.fol_folio in 
#                    (SELECT Fol_folio 
#                        FROM td_ordenembarque 
#                        WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)),4) AS volumen,
            TRUNCATE(SUM(DISTINCT c_articulo.peso*td_surtidopiezas.Cantidad),4) AS peso,
            TRUNCATE(SUM(((c_articulo.alto/1000) * (c_articulo.ancho/1000) * (c_articulo.fondo/1000))*td_surtidopiezas.Cantidad), 4) AS volumen,
            COALESCE(IF(UCASE(cat_estados.DESCRIPCION) = 'ENVIADO', 'EN RUTA', UCASE(cat_estados.DESCRIPCION)), '--') AS status,
            #COALESCE(UCASE(IF(GROUP_CONCAT(DISTINCT td_ordenembarque.status ORDER BY td_ordenembarque.status DESC SEPARATOR '') = 'TF', 'ENTREGANDO', cat_estados.DESCRIPCION)), '--') AS status,
            th_consolidado.Nom_CteCon cliente,
            th_consolidado.Dir_CteCon direccion,
            th_ordenembarque.Num_Guia guia,
            th_consolidado.Fol_PedidoCon factura,
            th_consolidado.Cod_CteCon dane,
            #tipo_transporte.clave_ttransporte as clave,
            #tipo_transporte.desc_ttransporte AS transporte,
            t_transporte.ID_Transporte AS clave,
            t_transporte.Nombre AS transporte,
            t_transporte.transporte_externo AS transporte_externo,
            t_transporte.Placas as placas,
            tipo_transporte.capacidad_carga as pesomax,
            tipo_transporte.alto*tipo_transporte.ancho*tipo_transporte.fondo/1000000000 as volmax,
            IFNULL(MAX(td_ordenembarque.orden_stop), 1) as stops,
            GROUP_CONCAT(DISTINCT td_ordenembarque.Fol_folio SEPARATOR ', ')  as pedidos,
            COUNT(DISTINCT td_ordenembarque.Fol_folio) AS entregas,
            #COUNT(DISTINCT tc.NCaja) AS total_cajas,
            #'' AS total_cajas,
            #COUNT(DISTINCT tc.Cve_CajaMix) AS total_cajas,
            #COUNT(DISTINCT tt.ntarima) AS total_pallets,
            #GROUP_CONCAT(DISTINCT CONCAT('(',t_ruta.cve_ruta, ') ' , t_ruta.descripcion) SEPARATOR ', ') AS ruta
            #GROUP_CONCAT(DISTINCT CONCAT('(',t_ruta.cve_ruta, ') ') SEPARATOR ', ') AS ruta
            #IFNULL(CONCAT('(',rre.cve_ruta, ') '),GROUP_CONCAT(DISTINCT CONCAT('(',t_ruta.cve_ruta, ') ') SEPARATOR ', ')) AS ruta
            GROUP_CONCAT(DISTINCT td_ordenembarque.status ORDER BY td_ordenembarque.status DESC SEPARATOR '') AS status_entregando,
            IF(IFNULL(ef.th_embarque_folio, '') = '', 'N', 'S') AS tiene_foto,
            GROUP_CONCAT(DISTINCT CONCAT('(',r.cve_ruta, ') ') SEPARATOR ', ') AS ruta

      FROM th_ordenembarque
            LEFT JOIN td_ordenembarque tdo ON tdo.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN td_surtidopiezas ON td_surtidopiezas.fol_folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_surtidopiezas.Cve_articulo
            #LEFT JOIN t_tarima tt ON tt.Fol_Folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            #LEFT JOIN th_cajamixta tc ON tc.Fol_Folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
        LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = tdo.Fol_folio #IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = th_ordenembarque.ID_OEmbarque)
            LEFT JOIN th_consolidado ON th_consolidado.Fol_PedidoCon = th_ordenembarque.ID_OEmbarque
            LEFT JOIN cat_estados on cat_estados.ESTADO = th_ordenembarque.status
            LEFT JOIN t_transporte ON t_transporte.id = th_ordenembarque.ID_Transporte
            LEFT JOIN tipo_transporte ON tipo_transporte.clave_ttransporte = t_transporte.tipo_transporte
            LEFT JOIN td_ordenembarque on td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            #LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = rel.Id_Destinatario
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = c_destinatarios.id_destinatario
            LEFT JOIN th_pedido th ON th.status IN ('T','F') AND tdo.Fol_folio = th.Fol_folio
            LEFT JOIN t_ruta r ON r.ID_Ruta = th_ordenembarque.Id_Ruta 
            LEFT JOIN th_embarque_fotos ef ON ef.th_embarque_folio = th_ordenembarque.ID_OEmbarque
            #LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(th.ruta, t_clientexruta.clave_ruta) OR t_ruta.cve_ruta IN (SELECT cve_ubicacion FROM th_pedido WHERE IFNULL(cve_ubicacion, '') != '' AND status IN ('T','F')) 
            #LEFT JOIN rel_RutasEntregas re ON t_ruta.ID_Ruta = re.id_ruta_venta_preventa
            #LEFT JOIN t_ruta rre ON rre.ID_Ruta = re.id_ruta_entrega
            #LEFT JOIN th_pedido th ON t_ruta.cve_ruta = th.cve_ubicacion AND th.STATUS = 'T'
            #LEFT JOIN th_cajamixta tc ON tc.fol_folio = tdo.Fol_folio
      WHERE th_ordenembarque.Activo = 1 {$filtro_cliente}
      {$and} 
      {$SQLFecha} 
      AND th.status IN ('T','F') 
      AND th_ordenembarque.Cve_Almac = {$almacen}
      GROUP BY th_ordenembarque.ID_OEmbarque
      ) AS ord_emb 
        {$filtro_entregando}
      ORDER BY ord_emb.id DESC
    ";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    //mysqli_close($conn);
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit; ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

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
/*
        $folio_pedidos = $pedidos;
    $sql_total_cajas_tipo1 = "
        SELECT IF(art.num_multiplo>0, IFNULL(TRUNCATE(SUM(td.Cantidad)/art.num_multiplo,0), 0), COALESCE(SUM(1), 0)) AS Cantidad
        FROM td_cajamixta td
        LEFT JOIN th_cajamixta th ON th.Cve_CajaMix = td.Cve_CajaMix
        LEFT JOIN c_articulo art ON art.cve_articulo = td.Cve_articulo
        WHERE th.fol_folio IN ('".$folio_pedidos."') AND art.tipo_caja = th.cve_tipocaja";
    $query_total_cajas_tipo1 = mysqli_query($conn, $sql_total_cajas_tipo1);
    $total_cajas_tipo1 = mysqli_fetch_array($query_total_cajas_tipo1)['Cantidad'];


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
        $responce->rows[$i]['id']=$row['id'];

        /*
        if($status_embarque == 'E' && $status_entregando == 'TF')
        {
            $status = 'ENTREGANDO';
        }
        */
        $responce->rows[$i]['cell'] = ['',
                                       $id,
                                       $pedidos,
                                       $ruta,
                                       $stops,
                                       "<div style='width:100%; font-weight: bold; text-align:center;' title='$pedidos'>$entregas</div>",
                                       '',//$total_pallets,
                                       '',//$total_cajas,
                                       $fecha_embarque,
                                       $fecha_entrega,
                                       $status,
                                       $transporte_externo,
                                       $transporte,
                                       $clave,
                                       $placas,
                                       //$entregas,
                                       //$dane,
                                       //$Embarque,
                                       $volmax,
                                       $volumen,
                                       $pesomax,
                                       $peso,
                                       $tiene_foto,
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
    $id = $_POST['id'];
    $folios = $_POST['folio'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    //mysqli_set_charset($conn, 'utf8');
    $sqlCount = "SELECT COUNT(*) AS total FROM td_surtidopiezas WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id});";
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];


    $sqlguia_transporte = "SELECT guia_transporte FROM th_ordenembarque WHERE ID_OEmbarque = {$id};";
    if (!($res = mysqli_query($conn, $sqlguia_transporte))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $guia_transporte = $row['guia_transporte'];

    $filtro_cliente1 = "";
    $filtro_cliente2 = "";
    if(isset($_POST['cve_cliente']))
    {
        $cve_cliente = $_POST['cve_cliente'];
        if($cve_cliente != '')
        {
            $filtro_cliente1 = "AND (SELECT COUNT(Cve_Clte) from th_pedido where th_pedido.Fol_folio = caja.fol_folio AND Cve_Clte = '$cve_cliente')";
            $filtro_cliente2 = "AND (SELECT COUNT(Cve_Clte) FROM th_pedido WHERE th_pedido.Fol_folio = s.fol_folio AND Cve_Clte = '$cve_cliente')";
        }
    }

    if(isset($_POST['cve_proveedor']))
    {
        $cve_proveedor = $_POST['cve_proveedor'];
      if($cve_proveedor != '')
      {
           $filtro_cliente1 = "AND (SELECT prv.ID_Proveedor 
            FROM th_pedido 
            LEFT JOIN c_cliente ct ON ct.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN c_proveedores prv ON prv.ID_Proveedor = IFNULL(ct.ID_Proveedor, IFNULL((SELECT ID_Proveedor FROM t_ruta WHERE cve_ruta = IFNULL(p.cve_ubicacion, '')), $cve_proveedor))
            WHERE th_pedido.Fol_folio = caja.fol_folio) = $cve_proveedor";

           $filtro_cliente2 = "AND (SELECT prv.ID_Proveedor 
            FROM th_pedido 
            LEFT JOIN c_cliente ct ON ct.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN c_proveedores prv ON prv.ID_Proveedor = IFNULL(ct.ID_Proveedor, IFNULL((SELECT ID_Proveedor FROM t_ruta WHERE cve_ruta = IFNULL(p.cve_ubicacion, '')), $cve_proveedor))
            WHERE th_pedido.Fol_folio = s.fol_folio) = $cve_proveedor";

      }
    }

    $sql="
    SELECT * FROM (
        SELECT DISTINCT
            caja.fol_folio,
            #caja.NCaja,
            '' AS NCaja,
            t.clave as claveLPCJ,
            t.descripcion,
            #caja.Guia,
            '' AS ntarima, 
            '' AS Guia,
             TRUNCATE(
                (CASE 
                    WHEN caja.cve_tipocaja = 1 THEN
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
                 IFNULL(ROUND(SUM(td_cajamixta.Cantidad * a.peso),3), 0) AS volumentotal
             FROM td_cajamixta
                 LEFT JOIN c_articulo a ON a.cve_articulo = td_cajamixta.Cve_articulo
             WHERE td_cajamixta.Cve_CajaMix = caja.Cve_CajaMix) as Peso,
            (SELECT DISTINCT GROUP_CONCAT(DISTINCT RazonSocial) FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = caja.fol_folio) AS cliente,
            (SELECT DISTINCT GROUP_CONCAT(DISTINCT Cve_Clte) FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio) AS cve_cliente,

            IF(IFNULL(p.cve_ubicacion, '') = '', IFNULL(drpd.id_destinatario, d.id_destinatario), p.cve_ubicacion) AS id_destinatario,
            IF(IFNULL(p.cve_ubicacion, '') = '', IFNULL(drpd.razonsocial, d.razonsocial), (SELECT descripcion FROM t_ruta WHERE cve_ruta = p.cve_ubicacion)) AS razonsocial,            
            item.Cve_articulo AS clave,
            IFNULL(ar.control_lotes, 'N') AS control_lote,
            IFNULL(ar.control_numero_series, 'N') AS control_serie,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            ar.num_multiplo AS piezasxcajas, 
            IFNULL(l.LOTE, item.cve_lote) AS LOTE,
            DATE_FORMAT(l.Caducidad, '%d-%m-%Y') as Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '') AS descripcion_producto,
            IFNULL(s.Cantidad, item.Cantidad) AS cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            IFNULL(p.Pick_Num, '') AS oc_cliente,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '0') * s.Cantidad,4) AS peso_total
            , r.cve_ruta

        FROM th_cajamixta caja
            LEFT JOIN c_tipocaja t on t.id_tipocaja = caja.cve_tipocaja 
            LEFT JOIN th_pedido p ON p.Fol_folio = caja.fol_folio
            LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN (SELECT orden.Fol_folio FROM td_ordenembarque orden WHERE orden.ID_OEmbarque = {$id})  AND NCaja = caja.NCaja) AND caja.Cve_CajaMix = item.Cve_CajaMix
            LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = item.Cve_Lote AND l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.Cve_Clte=(SELECT Cve_clte FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio LIMIT 1)
            LEFT JOIN td_surtidopiezas s ON s.fol_folio IN (SELECT orden.Fol_folio FROM td_ordenembarque orden WHERE orden.ID_OEmbarque = {$id}) AND s.Cve_articulo = item.Cve_articulo AND IFNULL(s.LOTE, '') = IFNULL(item.Cve_Lote, '')
            LEFT JOIN Rel_PedidoDest rpd ON rpd.Fol_Folio = s.fol_folio AND d.id_destinatario = rpd.Id_Destinatario 
            LEFT JOIN c_destinatarios drpd ON drpd.Id_destinatario = (SELECT Id_Destinatario FROM Rel_PedidoDest WHERE Fol_Folio = caja.fol_folio)
            LEFT JOIN t_ruta r ON r.ID_Ruta = p.ruta
        WHERE caja.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}) AND caja.Activo = 1 
        AND item.Cve_articulo = s.Cve_articulo AND caja.fol_folio = s.fol_folio AND item.Cve_Lote = s.LOTE
        #AND d.id_destinatario = rpd.Id_Destinatario 
        AND caja.Cve_CajaMix NOT IN (SELECT Caja_ref FROM t_tarima WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}))
        {$filtro_cliente1} 

UNION 

SELECT DISTINCT
            tt.Fol_Folio AS fol_folio,
            #caja.NCaja,
            '' AS NCaja,
            ch.CveLP AS claveLPCJ,
            ch.CveLP as descripcion,
            #caja.Guia, 
            tt.ntarima AS ntarima,
            '' AS Guia,
             TRUNCATE(IFNULL(ROUND(SUM(tt.cantidad * ((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000))),3), 0) ,4) AS volumen,
            (IFNULL(ROUND(SUM(tt.cantidad * ar.peso),3), 0)) AS Peso,
            (SELECT DISTINCT GROUP_CONCAT(DISTINCT RazonSocial) FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cliente,
            (SELECT DISTINCT GROUP_CONCAT(DISTINCT Cve_Clte) FROM th_pedido WHERE th_pedido.Fol_folio = tt.Fol_Folio) AS cve_cliente,

            IF(IFNULL(p.cve_ubicacion, '') = '', d.id_destinatario, p.cve_ubicacion) AS id_destinatario,
            IF(IFNULL(p.cve_ubicacion, '') = '', d.razonsocial, (SELECT descripcion FROM t_ruta WHERE cve_ruta = p.cve_ubicacion)) AS razonsocial,            
            tt.cve_articulo AS clave,
            IFNULL(ar.control_lotes, 'N') AS control_lote,
            IFNULL(ar.control_numero_series, 'N') AS control_serie,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            ar.num_multiplo AS piezasxcajas, 
            l.LOTE,
            DATE_FORMAT(l.Caducidad, '%d-%m-%Y') as Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = tt.cve_articulo), '') AS descripcion_producto,
            tt.cantidad AS cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            IFNULL(p.Pick_Num, '') AS oc_cliente,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = tt.cve_articulo), '0') * s.Cantidad,4) AS peso_total
            , r.cve_ruta

        FROM t_tarima tt
            LEFT JOIN c_charolas ch ON tt.ntarima = ch.IDContenedor
            LEFT JOIN th_pedido p ON p.Fol_folio = tt.fol_folio
            LEFT JOIN c_articulo ar ON ar.cve_articulo = tt.cve_articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = tt.lote AND l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.Cve_Clte=(SELECT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = tt.Fol_Folio LIMIT 1)
            LEFT JOIN td_surtidopiezas s ON s.fol_folio IN (SELECT orden.Fol_folio FROM td_ordenembarque orden WHERE orden.ID_OEmbarque = {$id}) AND s.Cve_articulo = tt.cve_articulo
            LEFT JOIN t_ruta r ON r.ID_Ruta = p.ruta
        WHERE tt.Fol_Folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}) AND tt.Ban_Embarcado = 'S'
        {$filtro_cliente2}
    GROUP BY ntarima

UNION 

SELECT DISTINCT
            s.Fol_Folio AS fol_folio,
            #caja.NCaja,
            '' AS NCaja,
            '' AS claveLPCJ,
            '' AS descripcion,
            #caja.Guia, 
            '' AS ntarima,
            '' AS Guia,
             TRUNCATE(IFNULL(ROUND(SUM(s.cantidad * ((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000))),3), 0) ,4) AS volumen,
            (IFNULL(ROUND(SUM(s.cantidad * ar.peso),3), 0)) AS Peso,
            (SELECT DISTINCT GROUP_CONCAT(DISTINCT RazonSocial) FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = s.Fol_Folio) AS cliente,
            (SELECT DISTINCT GROUP_CONCAT(DISTINCT Cve_Clte) FROM th_pedido WHERE th_pedido.Fol_folio = s.Fol_Folio) AS cve_cliente,

            IF(IFNULL(p.cve_ubicacion, '') = '', d.id_destinatario, p.cve_ubicacion) AS id_destinatario,
            IF(IFNULL(p.cve_ubicacion, '') = '', d.razonsocial, (SELECT descripcion FROM t_ruta WHERE cve_ruta = p.cve_ubicacion)) AS razonsocial,            
            s.cve_articulo AS clave,
            IFNULL(ar.control_lotes, 'N') AS control_lote,
            IFNULL(ar.control_numero_series, 'N') AS control_serie,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            ar.num_multiplo AS piezasxcajas, 
            l.LOTE,
            DATE_FORMAT(l.Caducidad, '%d-%m-%Y') AS Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = s.cve_articulo), '') AS descripcion_producto,
            s.cantidad AS cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            IFNULL(p.Pick_Num, '') AS oc_cliente,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = s.cve_articulo), '0') * s.Cantidad,4) AS peso_total
            , r.cve_ruta

        FROM th_pedido p
            LEFT JOIN td_ordenembarque oe ON oe.Fol_Folio = p.Fol_folio
            LEFT JOIN td_pedido dp ON dp.Fol_Folio = oe.Fol_Folio
        LEFT JOIN td_surtidopiezas s ON s.fol_folio = oe.Fol_Folio AND s.Cve_articulo = dp.Cve_Articulo
            LEFT JOIN c_articulo ar ON ar.cve_articulo = s.cve_articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = s.lote AND l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.Cve_Clte=(SELECT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = s.Fol_Folio LIMIT 1)
            LEFT JOIN t_ruta r ON r.ID_Ruta = p.ruta
        WHERE oe.ID_OEmbarque = {$id}
        AND p.Fol_folio NOT IN (SELECT fol_folio FROM th_cajamixta) AND p.Fol_folio NOT IN (SELECT fol_folio FROM t_tarima) AND s.Fol_folio IS NOT NULL
        ) as e WHERE e.fol_folio IS NOT NULL 
    ";
    //echo $sql;
/*
UNION

        SELECT DISTINCT
            s.fol_folio,
            #caja.NCaja,
            '' AS NCaja,
            '' AS clave,
            '' AS descripcion,
            #caja.Guia, 
            '' AS Guia,
            '' AS volumen,
            '' AS Peso,
            (SELECT RazonSocial FROM c_cliente INNER JOIN th_pedido ON th_pedido.Cve_clte = c_cliente.Cve_Clte WHERE th_pedido.Fol_folio = s.fol_folio) AS cliente,
            (SELECT Cve_Clte FROM th_pedido WHERE th_pedido.Fol_folio = s.fol_folio) AS cve_cliente,

            d.id_destinatario,
            d.razonsocial,
            s.Cve_articulo AS clave,
            IFNULL(ar.control_lotes, 'N') AS control_lote,
            IFNULL(ar.control_numero_series, 'N') AS control_serie,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            ar.num_multiplo AS piezasxcajas, 
            l.LOTE,
            l.Caducidad,
            IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = s.Cve_articulo), '') AS descripcion_producto,
            s.Cantidad AS cantidad,
            TRUNCATE(IFNULL(((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000)), '0') * s.Cantidad,4) AS volumen_total,
            TRUNCATE(IFNULL((SELECT peso FROM c_articulo WHERE cve_articulo = s.Cve_articulo), '0') * s.Cantidad,4) AS peso_total

        FROM td_surtidopiezas s
            LEFT JOIN c_articulo ar ON ar.cve_articulo = s.Cve_articulo AND s.fol_folio IN ('{$folios}') AND s.fol_folio NOT IN (SELECT fol_folio FROM th_cajamixta)
            LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = s.LOTE AND l.cve_articulo = ar.cve_articulo
            LEFT JOIN c_destinatarios d ON d.id_destinatario=(SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = s.fol_folio)
        WHERE s.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = {$id}) {$filtro_cliente2}
        #AND CONCAT(s.fol_folio,'-', s.Sufijo) NOT IN (SELECT CONCAT(fol_folio, '-', Sufijo) FROM th_cajamixta WHERE fol_folio=s.fol_folio AND Sufijo = s.Sufijo)*/

//        ORDER BY item.Cve_CajaMix ASC

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    //mysqli_close($conn);
    $responce = new stdClass();
    $count = mysqli_num_rows($res);

//    $sql .= " LIMIT $start, $limit; ";
//    if (!($res = mysqli_query($conn, $sql))) 
//    {
//        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
//    }

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
    $responce->guia_transporte = strtoupper($guia_transporte);

    $i = 0;

    $sql_guias = "SELECT Guia as GuiaEmb FROM th_cajamixta WHERE fol_folio IN ('{$folios}') ORDER BY Cve_CajaMix ASC";
    $res_guias = mysqli_query($conn, $sql_guias);

    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

        $row_guias = mysqli_fetch_array($res_guias);
        extract($row_guias);
        $Guia = $GuiaEmb;

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

        $responce->rows[$i]['id']=$row['clave'];
        $responce->rows[$i]['cell']=array('', $cve_ruta, utf8_decode($fol_folio), utf8_decode($oc_cliente), $cve_cliente, utf8_decode($cliente), $id_destinatario, utf8_decode($razonsocial), $clave, utf8_decode($descripcion_producto), $cantidad, $NLote, $Caducidad, $Nserie, $Pallet, $Cajas, $Piezas, $volumen_total, $Peso, utf8_decode($descripcion), utf8_decode($Guia), $Peso, $volumen, $NCaja,($i+1));
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
   #SELECT DISTINCT Cve_Almacenp FROM c_cliente WHERE Cve_Clte = d.Cve_Clte
    $sql="
        SELECT DISTINCT
        (SELECT clave FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almac FROM th_ordenembarque WHERE ID_OEmbarque = '$id')) AS id_destinatario,
        (SELECT des_cia FROM c_compania WHERE cve_cia = (SELECT cve_cia FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almac FROM th_ordenembarque WHERE ID_OEmbarque = '$id'))) AS razonsocial,
        (SELECT latitud FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almac FROM th_ordenembarque WHERE ID_OEmbarque = '$id')) AS latitud,
        (SELECT longitud FROM c_almacenp WHERE id = (SELECT DISTINCT Cve_Almac FROM th_ordenembarque WHERE ID_OEmbarque = '$id')) AS longitud,
        'Almacen-Data' AS fol_folio,
        '' AS Fecha_Entrega,
        '' AS Horario_Entrega,
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
            DATE_FORMAT(p.Fec_Entrega, '%d-%m-%Y') AS Fecha_Entrega,
            p.rango_hora AS Horario_Entrega,
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
            DATE_FORMAT(p.Fec_Entrega, '%d-%m-%Y') AS Fecha_Entrega,
            p.rango_hora AS Horario_Entrega,
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

        $responce->rows[$i]=array($id_destinatario, $razonsocial, $latitud, $longitud, $fol_folio, $Estatus, $Fecha_Entrega, $Horario_Entrega);
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



if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'traer_destinatarios_pedidos')
{
    $folio = $_POST['folio'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT rd.Id_Destinatario, 
IFNULL(IF(IFNULL(d.razonsocial, '') != '', d.razonsocial, CONCAT('Destinatario', d.Cve_Clte)), 'Destinatario') AS destinatario,
    oe.status, 
    IFNULL(DATE_FORMAT(oe.fecha_entrega, '%d-%m-%Y | %H:%i:%s'), '') AS Fecha_Entrega,
    #GROUP_CONCAT(DISTINCT rd.Fol_Folio) AS folio
    IFNULL(p.Recibio, '') AS Recibio,
    oe.Fol_folio AS folio
    FROM td_ordenembarque oe 
    LEFT JOIN Rel_PedidoDest rd ON oe.Fol_folio = rd.Fol_Folio 
    LEFT JOIN c_destinatarios d ON rd.Id_Destinatario = d.id_destinatario
    LEFT JOIN t_pedentregados p ON p.Fol_folio = oe.Fol_folio
    WHERE oe.ID_OEmbarque = {$folio} 
    GROUP BY rd.Fol_Folio, folio
            ";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    mysqli_close($conn);

    $tabla_usuarios = "";
    while ($row = mysqli_fetch_array($res)) 
    {
        extract($row);

        $color_row = ""; 
        $mostrar_check = " <input style='cursor:pointer;' type='checkbox' class='folios_dest' id='fol_".$folio."' name='fol_".$folio."' data-fol='".$folio."' /> ";
        $mostrar_input = " <input type='text' class='form-control dest_receptores' id='dest_".$Id_Destinatario."' name='dest_".$Id_Destinatario."' data-id='".$Id_Destinatario."' data-status='".$status."' /> ";
        if($status == 'F')
        {
            $color_row = " style='background-color:#00FF00' ";
            $mostrar_check = "";
            $mostrar_input = $Recibio;
        }

        $tabla_usuarios .= "<tr $color_row><td style='text-align: center;'>$mostrar_check</td><td>".$folio."</td><td>".$destinatario."</td><td> $mostrar_input </td><td>".$Fecha_Entrega."</td></tr>";
    }

    echo json_encode(array(
        "table" => $tabla_usuarios
    ));
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'marcar_entregado')
{
    $folio_embarque = $_POST['folio_embarque'];
    $array_folios = $_POST['array_folios'];
    $cve_usuario = $_POST['usuario'];
    $array_receptores = $_POST['array_receptores'];
    $array_valores = $_POST['array_valores'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
  
    for($i = 0; $i < count($array_folios); $i++)
    {
        $folio    = $array_folios[$i];
        $receptor = $array_receptores[$i];
        $usuario  = $array_valores[$i];

        $sql = "SELECT rd.Id_Destinatario, 
                       th.Fec_Entrega,
                       rd.Fol_Folio AS folio_pedido
                FROM Rel_PedidoDest rd
                LEFT JOIN c_destinatarios d ON rd.Id_Destinatario = d.id_destinatario
                LEFT JOIN th_pedido th ON th.Fol_folio = rd.Fol_Folio
                INNER JOIN td_ordenembarque oe ON oe.Fol_folio = rd.Fol_Folio AND oe.Fol_folio = '{$folio}' 
                #AND oe.ID_OEmbarque = {$folio} 
                WHERE rd.Id_Destinatario = {$receptor} 
                ";

        if($receptor == '')
            $sql = "SELECT d.id_destinatario AS Id_Destinatario, 
                       th.Fec_Entrega,
                       oe.Fol_folio AS folio_pedido
                FROM th_pedido th
                LEFT JOIN c_destinatarios d ON th.Cve_clte = d.Cve_Clte
                INNER JOIN td_ordenembarque oe ON oe.Fol_folio = th.Fol_folio AND oe.Fol_folio = '{$folio}'";

        if (!($res = mysqli_query($conn, $sql))) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        while ($row = mysqli_fetch_array($res)) 
        {
            extract($row);

            $sql_insert = "INSERT INTO t_pedentregados(Fol_folio, FechaEntrega, Cve_usuario, Recibio, Fecha) VALUES ('$folio_pedido', '$Fec_Entrega', '$cve_usuario', '$usuario', NOW())";

            if (!($res_recep = mysqli_query($conn, $sql_insert))) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $sql = "UPDATE td_ordenembarque SET status = 'F', fecha_entrega = NOW() WHERE Fol_folio = '{$folio_pedido}'";
            $query = mysqli_query($conn, $sql);

            $sql = "UPDATE th_pedido SET status = 'F' WHERE Fol_folio = '{$folio_pedido}' ";
            $query = mysqli_query($conn, $sql);

            $sql = "UPDATE th_subpedido SET status = 'F' WHERE fol_folio = '{$folio_pedido}'";
            $query = mysqli_query($conn, $sql);

        }
    }
    $sql = "SELECT IF((SELECT COUNT(*) FROM td_ordenembarque WHERE ID_OEmbarque = '{$folio_embarque}' AND status = 'T') = 0, 1, 0) AS marcar_entregado FROM DUAL";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($query);
    $marcar_entregado = $row["marcar_entregado"];

    if($marcar_entregado)
    {
        $sql = "UPDATE th_ordenembarque SET status = 'F' WHERE ID_OEmbarque = '{$folio_embarque}'";
        $query = mysqli_query($conn, $sql);
    }



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
               IFNULL(CONCAT(thc.Tel_CteEnv,'|', DATE_FORMAT(thc.Fec_Entrega, '%Y%m%d%H%i'),'|', CONCAT(thc.Tot_Cajas, '000'),'|', CONCAT(thc.Tot_Pzs, '000'),'|', thc.Placa_Trans,'|', thc.Sellos,'|', tdc.No_OrdComp,'|'), '') AS txt3, 
               IFNULL(CONCAT(DATE_FORMAT(tdc.Fec_OrdCom, '%Y%m%d%H%i'),'|', tdc.Cve_Articulo,'|', a.des_articulo,'|', (IF((SELECT COUNT(*) FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio IN (SELECT Fol_folio FROM td_ordenembarque WHERE ID_OEmbarque = '{$folio}'))) <= 1, 0, 1)),'|', a.cve_codprov,'|', a.barras2,'|', CONCAT(a.num_multiplo, '000'),'|', CONCAT(tdc.Cant_Pedida, '000'),'|'), '') AS txt4, 
               IFNULL(CONCAT(CONCAT(TRUNCATE(tds.Cantidad, 0), '000'),'|', 0,'|', tdc.Unid_Empaque,'|', IF(a.num_multiplo > 0, TRUNCATE(tds.Cantidad/a.num_multiplo, 0), tds.Cantidad),'|', tdc.CodB_Cte,'|', c.RazonSocial,'|', tdc.Fol_PedidoCon,'|', DATE_FORMAT(c_lotes.Caducidad, '%Y%m%d'),'|', tds.LOTE,'|', ''), '') AS txt5
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


if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'archivo_despacho')
{
    $folio = $_POST['folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
  
/*
    $sql = "SELECT O.Fol_folio,C.Guia,P.Fec_Entrada,S.Hora_inicio,S.Hora_Final,Count(C.Cve_CajaMix) Cajas
            From    td_ordenembarque O Join th_cajamixta C On O.Fol_Folio=C.Fol_Folio
            Join th_pedido P On O.Fol_Folio=P.Fol_Folio
            Join th_subpedido S On O.FOl_Folio=S.Fol_Folio
            Where   O.ID_OEmbarque = '{$folio}'
            Group By O.ID_OEmbarque,O.Fol_folio,C.Guia,P.Fec_Entrada,S.Hora_inicio,S.Hora_Final";
*/

    $sql = "SELECT  H.Fol_folio,'|',MAX(IfNull(M.Guia,0)) Guia,'|',date_format(H.Fec_Pedido,'%Y-%m-%d %H:%i') Fec_Pedido,'|',
                    date_format(Min(B.Hora_inicio),'%Y-%m-%d %H:%i') Fec_Pick,'|',date_format(Max(B.Hora_Final),'%Y-%m-%d %H:%i') Fin_Pick,'|',Count(M.Cve_CajaMix) Cajas,'|'
            From    th_pedido H Join th_cajamixta M On H.Fol_folio=M.fol_folio
                    Join th_subpedido B on H.Fol_folio=B.Fol_folio And M.Sufijo=B.Sufijo
                    Join td_ordenembarque E On M.fol_folio=E.Fol_folio
            Where   E.ID_OEmbarque='{$folio}'
            Group By H.Fol_folio,H.Fec_Pedido;";

    $query = mysqli_query($conn, $sql);
    $contenido = "";
    while($row_contenido = mysqli_fetch_array($query))
    {
    //$contenido .= $row_contenido["Fol_folio"]."|".$row_contenido["Guia"]."|".$row_contenido["Fec_Entrada"]."|".$row_contenido["Hora_inicio"]."|".$row_contenido["Hora_Final"]."|".$row_contenido["Cajas"]."|"."\n";
        $contenido .= $row_contenido["Fol_folio"]."|".$row_contenido["Guia"]."|".$row_contenido["Fec_Pedido"]."|".$row_contenido["Fec_Pick"]."|".$row_contenido["Fin_Pick"]."|".$row_contenido["Cajas"]."|"."\n";
    }
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

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargarFotosTHPedido' ) 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $folio = $_POST['folio'];
  $sql = "SELECT * FROM th_embarque_fotos WHERE folio_pedido = '{$folio}'";

  $res = mysqli_query($conn, $sql);

  $imagenes = "<div class='row'>";
  $i = 0;
  while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' /><br><b>".($row['descripcion'])."</b>&nbsp;&nbsp;</div>";

        $i++;
        if($i%3==0) $imagenes .= "<div class='row'></div>";
  }
  $imagenes .= "</div>";
  
  echo $imagenes;

/*
  $res = mysqli_query($conn, $sql);

  $count = mysqli_num_rows($res);

  $imagenes = '<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators">';
  for($i = 0; $i < $count; $i++)
  {
    $class = "";
    if($i == 0) $class = 'class="active"';
    $imagenes .= '<li data-target="#carouselExampleIndicators" data-slide-to="'.$i.'" '.$class.'></li>';
  }
  $imagenes .= '</ol>
  <div class="carousel-inner">';

  
  $i = 0;
  while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        //$imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><br><b>".($row['descripcion'])."</b>&nbsp;&nbsp;</div>";
    $active = "";
    if($i == 0) $active = 'active';
        $imagenes .= '<div class="carousel-item '.$active.'">
                        <img class="d-block w-100" src="../'.$row['ruta'].'" alt="'.$row['descripcion'].'">
                     </div>';
    $i++;
  }

  $imagenes .= '</div>
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>';
*/
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargarFotosTHEmbarque' ) 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $folio = $_POST['folio'];
  $sql = "SELECT * FROM th_embarque_fotos WHERE th_embarque_folio = {$folio}";

  $res = mysqli_query($conn, $sql);

  $imagenes = "<div class='row'>";
  $i = 0;
  while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' /><br><b>".($row['descripcion'])."</b>&nbsp;&nbsp;</div>";

        $i++;
        if($i%3==0) $imagenes .= "<div class='row'></div>";
  }
  $imagenes .= "</div>";
  
  echo $imagenes;

/*
  $res = mysqli_query($conn, $sql);

  $count = mysqli_num_rows($res);

  $imagenes = '<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators">';
  for($i = 0; $i < $count; $i++)
  {
    $class = "";
    if($i == 0) $class = 'class="active"';
    $imagenes .= '<li data-target="#carouselExampleIndicators" data-slide-to="'.$i.'" '.$class.'></li>';
  }
  $imagenes .= '</ol>
  <div class="carousel-inner">';

  
  $i = 0;
  while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        //$imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><br><b>".($row['descripcion'])."</b>&nbsp;&nbsp;</div>";
    $active = "";
    if($i == 0) $active = 'active';
        $imagenes .= '<div class="carousel-item '.$active.'">
                        <img class="d-block w-100" src="../'.$row['ruta'].'" alt="'.$row['descripcion'].'">
                     </div>';
    $i++;
  }

  $imagenes .= '</div>
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>';
*/
}
