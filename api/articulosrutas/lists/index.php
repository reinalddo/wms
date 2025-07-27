<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'getStock') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $sucursal = '';
    $fecha_search = $_POST['fecha_search'];
    $almacen = $_POST['almacen'];
    $ruta = $_POST['ruta'];
    $search = $_POST['search'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $fecha = "WHERE 1 ";
    if($fecha_search)
    {
        $originalDate = $fecha_search;
        $fecha_format = date("d-m-Y", strtotime($originalDate));
        $fecha = "WHERE rutas.fecha_embarque = '{$fecha_format}' ";
    }

    $where = $fecha;
    if($search)
        $where .= " AND (rutas.clave LIKE '%{$search}%' OR rutas.descripcion_producto LIKE '%{$search}%' OR rutas.cve_ruta LIKE '%{$search}%')";
//        $where .= " AND (rutas.ruta LIKE '%{$search}%' OR rutas.razonsocial LIKE '%{$search}%' OR rutas.clave LIKE '%{$search}%' OR rutas.descripcion_producto LIKE '%{$search}%' OR rutas.folio LIKE '%{$search}%' OR rutas.pedido LIKE '%{$search}%' OR rutas.cve_ruta LIKE '%{$search}%')";

    //if($ruta)
    //    $where .= " AND rutas.cve_ruta = '{$ruta}' ";

    $sql_venta_preventa = ""; $sql_left_join_ruta = "";
    if($ruta != '')
    {
      //$and .= "AND t_clientexruta.clave_ruta = '{$ruta}' ";
        /*
        $sql = "SELECT venta_preventa, ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $venta_preventa = $row["venta_preventa"];
        
        if($venta_preventa == 2)
        {
            $ruta = $row["ID_Ruta"];
            $where .= " AND rutas.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = '{$ruta}') ";
        }
        else
            */
            $where .= " AND rutas.cve_ruta = '{$ruta}' ";
    }


    // prepara la llamada al procedimiento almacenado Lis_Facturas
/*
    $sqlCount = "SELECT DISTINCT
        COUNT(*) AS total
        FROM th_cajamixta caja
                LEFT JOIN th_ordenembarque oe ON oe.ID_OEmbarque IN (SELECT ID_OEmbarque FROM td_ordenembarque WHERE Fol_folio = caja.fol_folio)
    LEFT JOIN c_tipocaja t ON t.id_tipocaja = caja.cve_tipocaja 
    LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE NCaja = caja.NCaja) AND caja.Cve_CajaMix = item.Cve_CajaMix
    LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo 
    LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = item.Cve_Lote AND l.cve_articulo = ar.cve_articulo
    LEFT JOIN c_destinatarios d ON d.id_destinatario=(SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio)
    LEFT JOIN td_surtidopiezas s ON s.Cve_articulo = item.Cve_articulo
    LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
    LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta      
WHERE caja.Activo = 1 AND item.Cve_articulo != '' AND oe.ID_OEmbarque != ''
GROUP BY oe.ID_OEmbarque, item.Cve_articulo
";

    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];
*/
    /*
    $sql = "
SELECT  rutas.clave, rutas.descripcion_producto, SUM(rutas.cantidad_sin_conversion) AS cantidad_sin_conversion, 
    SUM(rutas.cantidad_final_sin_conversion) AS cantidad_final_sin_conversion, SUM(rutas.cantidad) AS cantidad, SUM(rutas.cantidad_final) AS cantidad_final, rutas.ID_Ruta, rutas.cve_ruta,
SUM(IFNULL(IF(rutas.mav_cveunimed = 'XBX', IF(rutas.num_multiplo = 1, 0, rutas.cantidad_final_sin_conversion),TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0)), 0)) AS cajas_total,
SUM(IFNULL(IF(rutas.mav_cveunimed != 'XBX', (rutas.cantidad_final_sin_conversion - (rutas.num_multiplo*TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0))), IF(rutas.num_multiplo = 1, rutas.cantidad_final_sin_conversion, 0)), 0)) AS piezas_total

    FROM (
    SELECT DISTINCT
    ar.num_multiplo,
    um.mav_cveunimed,
    '' AS id_detventa,
    oe.ID_OEmbarque AS folio,
    IFNULL(t_ruta.ID_Ruta, ruta_pedido.ID_Ruta) AS ID_Ruta,
    IFNULL(IF(pd.cve_ubicacion != '', t_ruta.cve_ruta, pd.cve_ubicacion), ruta_pedido.cve_ruta) AS cve_ruta,
    toe.orden_stop,
    caja.fol_folio as pedido,
    IFNULL(IF(pd.cve_ubicacion != '', t_ruta.descripcion, (SELECT descripcion FROM t_ruta WHERE cve_ruta = pd.cve_ubicacion)), ruta_pedido.descripcion) AS ruta, 
    COALESCE(DATE_FORMAT(toe.fecha_envio, '%d-%m-%Y'), '--') AS fecha_embarque,
    COALESCE(DATE_FORMAT(toe.fecha_entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
    tp.num_ec AS transporte,
    tp.Nombre,
    d.id_destinatario, 
    d.razonsocial, 
    item.Cve_articulo AS clave,
    l.LOTE,
    l.Caducidad,
    IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = item.Cve_articulo), '') AS descripcion_producto,
    IF(ar.control_peso = 'N',TRUNCATE(SUM(s.Cantidad), 0),SUM(s.Cantidad)) as cantidad_sin_conversion,
    IF(ar.control_peso = 'N',TRUNCATE(SUM(s.Cantidad), 0),SUM(s.Cantidad)) AS cantidad_final_sin_conversion,
    IF(um.mav_cveunimed != 'XBX', IF(ar.control_peso = 'N',TRUNCATE(SUM(s.Cantidad), 0),SUM(s.Cantidad)), IF(ar.control_peso = 'N',TRUNCATE(SUM(s.Cantidad)*ar.num_multiplo, 0),SUM(s.Cantidad)*ar.num_multiplo)) AS cantidad, 
    IF(um.mav_cveunimed != 'XBX', IF(oe.status = 'T', IF(ar.control_peso = 'N',TRUNCATE(SUM(s.Cantidad), 0),SUM(s.Cantidad)), 0), IF(oe.status = 'T', IF(ar.control_peso = 'N',TRUNCATE(SUM(s.Cantidad)*ar.num_multiplo, 0),SUM(s.Cantidad)*ar.num_multiplo), 0)) AS cantidad_final
    FROM th_cajamixta caja
    LEFT JOIN td_ordenembarque toe ON toe.Fol_folio = caja.fol_folio
    LEFT JOIN th_ordenembarque oe ON oe.ID_OEmbarque = toe.ID_OEmbarque 
    LEFT JOIN t_transporte tp ON tp.id = oe.ID_Transporte
    LEFT JOIN th_pedido pd ON pd.Fol_folio = toe.Fol_folio
    LEFT JOIN c_tipocaja t ON t.id_tipocaja = caja.cve_tipocaja 
    LEFT JOIN td_cajamixta item ON item.Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE NCaja = caja.NCaja) AND caja.Cve_CajaMix = item.Cve_CajaMix
    LEFT JOIN c_articulo ar ON ar.cve_articulo = item.Cve_articulo 
    LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.LOTE = item.Cve_Lote AND l.cve_articulo = ar.cve_articulo
    LEFT JOIN c_destinatarios d ON d.id_destinatario=(SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = caja.fol_folio)
    INNER JOIN td_surtidopiezas s ON s.Cve_articulo = item.Cve_articulo AND s.fol_folio = caja.fol_folio AND s.LOTE = item.Cve_Lote
    LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario AND t_clientexruta.clave_ruta = IFNULL(pd.ruta, '')
    LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(t_clientexruta.clave_ruta, pd.ruta) 
    LEFT JOIN t_ruta ruta_pedido ON ruta_pedido.ID_Ruta = IFNULL(t_clientexruta.clave_ruta, pd.ruta) OR ruta_pedido.cve_ruta = pd.cve_ubicacion
    LEFT JOIN c_unimed um ON um.id_umed = ar.unidadMedida
WHERE caja.Activo = 1 AND item.Cve_articulo != '' AND oe.ID_OEmbarque != '' AND pd.cve_almac = '{$almacen}'
GROUP BY oe.ID_OEmbarque, pedido, item.Cve_CajaMixD, clave

UNION 


SELECT DISTINCT
    art.num_multiplo,
    um.mav_cveunimed,
    '' AS id_detventa,
    oet.ID_OEmbarque AS folio,
    tr.ID_Ruta AS ID_Ruta,
    IF(pdt.cve_ubicacion != '', tr.cve_ruta, pdt.cve_ubicacion) AS cve_ruta,
    toet.orden_stop,
    st.fol_folio as pedido,
    IF(pdt.cve_ubicacion != '', tr.descripcion, (SELECT descripcion FROM t_ruta WHERE cve_ruta = pdt.cve_ubicacion)) AS ruta, 
    COALESCE(DATE_FORMAT(toet.fecha_envio, '%d-%m-%Y'), '--') AS fecha_embarque,
    COALESCE(DATE_FORMAT(toet.fecha_entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
    tp.num_ec AS transporte,
    tp.Nombre,
    dt.id_destinatario, 
    dt.razonsocial, 
    art.cve_articulo AS clave,
    lt.LOTE,
    lt.Caducidad,
    IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = art.cve_articulo), '') AS descripcion_producto,
    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Cantidad), 0),SUM(st.Cantidad)) as cantidad_sin_conversion,
    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Cantidad), 0),SUM(st.Cantidad)) AS cantidad_final_sin_conversion,
    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Cantidad), 0),SUM(st.Cantidad)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Cantidad)*art.num_multiplo, 0),SUM(st.Cantidad)*art.num_multiplo)) AS cantidad, 
    IF(um.mav_cveunimed != 'XBX', IF(oet.status = 'T', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Cantidad), 0),SUM(st.Cantidad)), 0), IF(oet.status = 'T', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Cantidad)*art.num_multiplo, 0),SUM(st.Cantidad)*art.num_multiplo), 0)) AS cantidad_final
FROM td_surtidopiezas st
    LEFT JOIN td_ordenembarque toet ON toet.Fol_folio = st.fol_folio
    LEFT JOIN th_ordenembarque oet ON oet.ID_OEmbarque = toet.ID_OEmbarque 
    LEFT JOIN t_transporte tp ON tp.id = oet.ID_Transporte
    LEFT JOIN th_pedido pdt ON pdt.Fol_folio = toet.Fol_folio
    LEFT JOIN c_destinatarios dt ON dt.id_destinatario=(SELECT destinatario FROM th_pedido WHERE th_pedido.Fol_folio = st.fol_folio)
    LEFT JOIN t_clientexruta cxr ON cxr.clave_cliente = dt.id_destinatario
    LEFT JOIN t_ruta tr ON tr.ID_Ruta = cxr.clave_ruta      
    LEFT JOIN c_articulo art ON art.cve_articulo = st.Cve_articulo AND st.fol_folio NOT IN (SELECT fol_folio FROM th_cajamixta)
    LEFT JOIN c_lotes lt ON lt.cve_articulo = art.cve_articulo AND lt.LOTE = st.LOTE AND lt.cve_articulo = art.cve_articulo
    LEFT JOIN c_unimed um ON um.id_umed = art.unidadMedida
WHERE st.fol_folio IN (SELECT Fol_folio FROM td_ordenembarque ) AND art.cve_articulo != '' AND oet.ID_OEmbarque != '' AND pdt.cve_almac = '{$almacen}'
GROUP BY oet.ID_OEmbarque, pedido, clave

UNION

    SELECT DISTINCT
    ar.num_multiplo,
    um.mav_cveunimed,
    dv.ID AS id_detventa,
    v.Id AS folio,
    t_ruta.ID_Ruta AS ID_Ruta,
    t_ruta.cve_ruta AS cve_ruta,
    '' as orden_stop,
    v.Documento as pedido,
    '' AS ruta, 
    COALESCE(DATE_FORMAT(v.Fecha, '%d-%m-%Y'), '--') AS fecha_embarque,
    COALESCE(DATE_FORMAT(v.Fecha, '%d-%m-%Y'), '--') AS fecha_entrega,
    tp.Placas AS transporte,
    tp.Nombre,
    d.id_destinatario, 
    d.razonsocial, 
    dv.Articulo AS clave,
    '' as LOTE,
    '' as Caducidad,
    IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = dv.Articulo), '')  AS descripcion_producto,
    (-1)*(IF(ar.control_peso = 'N',TRUNCATE((dv.Pza), 0),(dv.Pza)) + (IFNULL(pr.Cant, 0))) AS cantidad_sin_conversion,
    (-1)*(IF(ar.control_peso = 'N',TRUNCATE((dv.Pza), 0),(dv.Pza)) + (IFNULL(pr.Cant, 0))) AS cantidad_final_sin_conversion,
    (-1)*(IF(um.mav_cveunimed != 'XBX', IF(ar.control_peso = 'N',TRUNCATE((dv.Pza) + (IFNULL(pr.Cant, 0)), 0),((dv.Pza) + (IFNULL(pr.Cant, 0)))), IF(ar.control_peso = 'N',TRUNCATE(((dv.Pza) + (IFNULL(pr.Cant, 0)))*ar.num_multiplo, 0),((dv.Pza) + (IFNULL(pr.Cant, 0)))*ar.num_multiplo))) AS cantidad, 
    (-1)*(IF(um.mav_cveunimed != 'XBX', IF(ar.control_peso = 'N',TRUNCATE(((dv.Pza) + (IFNULL(pr.Cant, 0))), 0),(dv.Pza) + (IFNULL(pr.Cant, 0))), IF(ar.control_peso = 'N',TRUNCATE(((dv.Pza) + (IFNULL(pr.Cant, 0)))*ar.num_multiplo, 0),((dv.Pza) + (IFNULL(pr.Cant, 0)))*ar.num_multiplo))) AS cantidad_final

    FROM Venta v
    LEFT JOIN DetalleVet dv ON v.Documento = dv.Docto
    LEFT JOIN PRegalado pr ON pr.Docto = v.Documento AND pr.SKU = dv.Articulo
    LEFT JOIN BitacoraTiempos bt ON bt.RutaId = v.RutaId AND bt.DiaO = v.DiaO
    LEFT JOIN t_transporte tp ON tp.ID_Transporte = bt.IdVehiculo
    LEFT JOIN c_articulo ar ON ar.cve_articulo = dv.Articulo
    LEFT JOIN c_destinatarios d ON d.id_destinatario=v.CodCliente
    LEFT JOIN t_ruta ON t_ruta.ID_Ruta = v.RutaId
    LEFT JOIN c_unimed um ON um.id_umed = ar.unidadMedida
    LEFT JOIN c_almacenp c ON c.clave = v.IdEmpresa
WHERE c.id = '{$almacen}' AND v.Cancelada = 0
GROUP BY id_detventa
ORDER BY STR_TO_DATE(fecha_embarque, '%d-%m-%Y') DESC, orden_stop ASC 
) AS rutas {$where}
GROUP BY clave 
ORDER BY descripcion_producto
";*/

    $sql = "SELECT  rutas.clave, rutas.descripcion_producto, 
                    SUM(rutas.cantidad_sin_conversion) AS cantidad_sin_conversion, 
                    SUM(rutas.cantidad_final_sin_conversion) AS cantidad_final_sin_conversion, 
                    SUM(rutas.cantidad) AS cantidad, SUM(rutas.cantidad_final) AS cantidad_final, 
                    rutas.ID_Ruta, rutas.cve_ruta,
                    SUM(IFNULL(IF(rutas.mav_cveunimed = 'XBX', IF(rutas.num_multiplo = 1, 0, rutas.cantidad_final_sin_conversion), IF(rutas.num_multiplo > 1, TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0), 0)), 0)) AS cajas_total,
                    SUM(IFNULL(IF(rutas.mav_cveunimed != 'XBX' AND rutas.num_multiplo > 1, (rutas.cantidad_final_sin_conversion - (rutas.num_multiplo*TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0))), IF(rutas.num_multiplo = 1, rutas.cantidad_final_sin_conversion, 0)), 0)) AS piezas_total
            FROM (

                SELECT DISTINCT
                    st.IdStock,
                    art.num_multiplo,
                    um.mav_cveunimed,
                    tr.ID_Ruta AS ID_Ruta,
                    tr.cve_ruta,
                    art.cve_articulo AS clave,
                    st.IdEmpresa,
                    IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = art.cve_articulo), '') AS descripcion_producto,
                    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)) AS cantidad_sin_conversion,
                    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)) AS cantidad_final_sin_conversion,
                    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock)*art.num_multiplo, 0),SUM(st.Stock)*art.num_multiplo)) AS cantidad, 
                    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock)*art.num_multiplo, 0),SUM(st.Stock)*art.num_multiplo)) AS cantidad_final
                FROM Stock st
                    INNER JOIN c_almacenp a ON a.clave = st.IdEmpresa AND a.id = '{$almacen}'
                    LEFT JOIN t_ruta tr ON tr.ID_Ruta = st.Ruta
                    LEFT JOIN c_articulo art ON art.cve_articulo = st.Articulo 
                    LEFT JOIN c_unimed um ON um.id_umed = art.unidadMedida
                    where art.Activo = 1 
                GROUP BY clave, ID_Ruta, IdEmpresa, IdStock


            ) AS rutas {$where} AND rutas.cantidad_final > 0
            GROUP BY clave 
            ORDER BY cantidad_final DESC";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación where: (" . mysqli_error($conn) . ") ---- ".$sql;
    }

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación where: (" . mysqli_error($conn) . ") ---- ".$sql;
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;
    $responce = new stdClass();
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    $total_cajas = 0; $total_piezas = 0;
//**********************************************************************************
$sql_tot = "SELECT  SUM(IFNULL(IF(rutas.mav_cveunimed = 'XBX', IF(rutas.num_multiplo = 1, 0, rutas.cantidad_final_sin_conversion), IF(rutas.num_multiplo > 1, TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0), 0)), 0)) AS cajas_total,
        SUM(IFNULL(IF(rutas.mav_cveunimed != 'XBX' AND rutas.num_multiplo > 1, (rutas.cantidad_final_sin_conversion - (rutas.num_multiplo*TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0))), IF(rutas.num_multiplo = 1, rutas.cantidad_final_sin_conversion, 0)), 0)) AS piezas_total
            FROM (

                SELECT DISTINCT
                    st.IdStock,
                    art.num_multiplo,
                    um.mav_cveunimed,
                    tr.ID_Ruta AS ID_Ruta,
                    tr.cve_ruta,
                    art.cve_articulo AS clave,
                    st.IdEmpresa,
                    IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = art.cve_articulo), '') AS descripcion_producto,
                    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)) AS cantidad_sin_conversion,
                    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)) AS cantidad_final_sin_conversion,
                    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock)*art.num_multiplo, 0),SUM(st.Stock)*art.num_multiplo)) AS cantidad, 
                    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock)*art.num_multiplo, 0),SUM(st.Stock)*art.num_multiplo)) AS cantidad_final
                FROM Stock st
                    INNER JOIN c_almacenp a ON a.clave = st.IdEmpresa AND a.id = '{$almacen}'
                    LEFT JOIN t_ruta tr ON tr.ID_Ruta = st.Ruta
                    LEFT JOIN c_articulo art ON art.cve_articulo = st.Articulo 
                    LEFT JOIN c_unimed um ON um.id_umed = art.unidadMedida
                    WHERE art.Activo = 1 
                GROUP BY clave, ID_Ruta, IdEmpresa, IdStock
            ) AS rutas {$where} AND cantidad_final > 0 ";

    if (!($res_tot = mysqli_query($conn, $sql_tot))) 
    {
        echo "Falló la preparación where: (" . mysqli_error($conn) . ") ---- ".$sql_tot;
    }

    $row_tot = mysqli_fetch_array($res_tot);

    $total_cajas = $row_tot['cajas_total']; 
    $total_piezas = $row_tot['piezas_total'];
//**********************************************************************************

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']= $id;

        //$responce->rows[$i]['cell']= array('', $folio, $cve_ruta, $transporte, $Nombre, $ruta, $orden_stop, $id_destinatario, $razonsocial, $pedido, $fecha_embarque, $fecha_entrega, $clave, $descripcion_producto, $cantidad_sin_conversion, $cantidad, $cantidad_final_sin_conversion, $cantidad_final);
        $responce->rows[$i]['cell']= array('', '', $cve_ruta, '', '', '', '', '', '', '', '', '', $clave, $descripcion_producto, $cajas_total, $piezas_total, $cantidad_final, '');

        //$total_cajas += $cajas_total;
        //$total_piezas += $piezas_total;

         $i++;
    }
    $responce->total_cajas = $total_cajas;
    $responce->total_piezas = $total_piezas;
    $responce->sql_total = $sql_tot;
    $responce->almacen = $almacen;
    echo json_encode($responce);
}