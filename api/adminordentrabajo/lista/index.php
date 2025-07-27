<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'busqueda') {
      $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
      $criterio = $_POST['criterio'];
      $criterioLP = $_POST['criterioLP'];
      $fecha_inicio = $_POST['fechaInicio'];
      $fecha_fin = $_POST['fechaFin'];
      $almacen = $_POST['almacen'];
      $Proveedor = $_POST['Proveedor'];
      $status_OT = $_POST['statusOT'];
      //$filtro = $_POST['filtro'];
      //$folio_inicio = $_POST['folioInicio'];
      //$folio_fin = $_POST['folioFin'];
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!empty($fecha_inicio)) $fecha_inicio = date("Y-m-d", strtotime($fecha_inicio));
    if (!empty($fecha_fin)) $fecha_fin = date("Y-m-d", strtotime($fecha_fin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sqlCount = "SELECT COUNT(DISTINCT c.Folio_Pro) AS cuenta FROM t_ordenprod c 
                 LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
                 LEFT JOIN c_almacen ca ON ca.cve_almacenp = c.cve_almac
                 LEFT JOIN c_almacenp al ON al.id = ca.cve_almacenp
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
                 WHERE 1";


    $sql_semana = "";
    if(!empty($criterio)){
        $sqlCount .= " AND (c.Folio_Pro LIKE '%$criterio%' OR c.Cve_Lote LIKE '%$criterio%' OR a.des_articulo LIKE '%$criterio%' OR c.Cve_Usuario LIKE '%$criterio%' OR c.Cve_Articulo LIKE '%$criterio%') ";
    }
    else if($status_OT == 'T')
        $sql_semana = " AND c.Fecha BETWEEN CURDATE() AND (CURDATE() + INTERVAL 7 DAY) ";

    if(!empty($almacen)){
        $sqlCount .= " AND al.clave = '{$almacen}' ";
    }
    if(!empty($Proveedor)){
        $sqlCount .= " AND p.ID_Proveedor = '{$Proveedor}' ";
    }

    if(!empty($status_OT) && empty($criterio)){
        $sqlCount .= " AND c.Status = '{$status_OT}' ";
    }
    else if(empty($criterio))
        $sqlCount .= " AND c.Status = '{$status_OT}' ";

    if(!empty($fecha_inicio) && !empty($fecha_fin)){
        $sqlCount .= " AND c.Fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $sql_semana = "";
    }
    elseif(!empty($fecha_inicio)){
        $sqlCount .= " AND c.Fecha >= '$fecha_inicio'";
        $sql_semana = "";
    }
    elseif(!empty($fecha_fin)){
        $sqlCount .= " AND c.Fecha <= '$fecha_fin'";
        $sql_semana = "";
    }
*/
    /*
    if(!empty($folio_inicio) && !empty($folio_fin)){
        $sqlCount .= " AND Folio_Pro BETWEEN '$folio_inicio' AND '$folio_fin'";
    }elseif(!empty($folio_inicio)){
        $sqlCount .= " AND Folio_Pro > $folio_inicio";
    }elseif(!empty($folio_fin)){
        $sqlCount .= " AND Folio_Pro < $folio_fin";
    }
    

    if ((!$res = mysqli_query($conn, $sql_semana." ".$sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
*/
    $sql_count = "
        SELECT DISTINCT
            c.Folio_Pro
        from t_ordenprod c 
        LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
        LEFT JOIN c_lotes l ON c.Cve_Articulo = l.cve_articulo AND c.Cve_Lote = l.Lote
        LEFT JOIN th_pedido pd ON pd.Ship_Num = c.Folio_Pro
        LEFT JOIN c_almacen ca ON ca.cve_almacenp = c.cve_almac
        LEFT JOIN c_almacenp al ON al.id = ca.cve_almacenp
        LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
        LEFT JOIN c_usuario u_clave ON c.Cve_Usuario = u_clave.cve_usuario
        LEFT JOIN c_usuario u_id ON c.Cve_Usuario = u_id.id_user
        #LEFT JOIN t_tarima tt ON tt.Fol_Folio = c.Folio_Pro
        #LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        LEFT JOIN c_articulo_documento ad ON CONVERT(ad.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
        WHERE 1  
    ";

    $sql = "
        SELECT DISTINCT
            c.Folio_Pro, 
            c.Cve_Articulo, 
            a.control_peso,
            DATE_FORMAT(c.FechaReg, '%d-%m-%Y') AS Fecha,
            DATE_FORMAT(c.FechaReg, '%h:%i:%s %p') AS Hora_OT, 
            DATE_FORMAT(c.Fecha, '%d-%m-%Y') AS FechaCompromiso, 
            (select des_articulo from c_articulo where c.Cve_Articulo = cve_articulo) as descripcion, 
            pd.Fol_folio AS folio_rel,
            IF(a.control_lotes = 'S', IFNULL(l.Lote, ''), '') AS Cve_Lote, 
            IFNULL(IF(a.Caduca = 'S', IF(DATE_FORMAT(l.Caducidad, '%Y-%m-%d')='0000-00-00','', DATE_FORMAT(l.Caducidad, '%d-%m-%Y')), ''), '') AS Caducidad,
            #IF(a.control_peso = 'S', CONCAT(TRUNCATE((SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = c.Folio_Pro), 3), ''), (SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = c.Folio_Pro)) AS Cantidad, 
            c.Cantidad,
            c.Cant_Prod, 
            #IFNULL((SELECT nombre_completo FROM c_usuario WHERE c.Cve_Usuario = id_user), (SELECT nombre_completo FROM c_usuario WHERE c.Cve_Usuario = cve_usuario)) AS usuario, 
            IFNULL(u_clave.nombre_completo, u_id.nombre_completo) AS usuario, 
            DATE_FORMAT(c.Hora_Ini, '%d-%m-%Y') AS Fecha_Ini, 
            DATE_FORMAT(c.Hora_Ini, '%h:%i:%s %p') AS Hora_Ini, 
            DATE_FORMAT(c.Hora_Fin, '%d-%m-%Y') AS Fecha_Fin, 
            DATE_FORMAT(c.Hora_Fin, '%h:%i:%s %p') AS Hora_Fin, 
            c.Status as StatusOT,
            (CASE 
                WHEN IFNULL(pd.Fol_folio, '') != '' THEN 'Env&iacute;o Relacionado PV'
                #WHEN (SELECT COUNT(*) FROM V_ExistenciaGralProduccion WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_ubicacion = (SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'N' AND V_ExistenciaGralProduccion.cve_ubicacion = idy_ubica)) > 0 THEN 'Envio a Almac&eacute;n'
                WHEN (SELECT COUNT(*) FROM V_ExistenciaGralProduccion WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_ubicacion = c.idy_ubica) > 0 THEN 'Envio a Almac&eacute;n'
                WHEN c.Status = 'P' THEN 'Pendiente' 
                WHEN c.Status = 'I' THEN 'En Producci&oacute;n' 
                WHEN c.Status = 'T' THEN 'Terminado' 
                WHEN c.Status = 'B' THEN 'BackOrder' 
            END) as status, 
            #IFNULL((SELECT des_almac FROM c_almacen WHERE cve_almacenp = c.cve_almac), '--') AS zona,
            '' AS zona,
            p.Nombre as proveedor,
            al.nombre AS almacen,
            (SELECT COUNT(*) FROM ts_existenciapiezas WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_almac = c.cve_almac) AS traslado,
            IFNULL((SELECT GROUP_CONCAT(AreaProduccion SEPARATOR '' ) FROM c_ubicacion WHERE idy_ubica IN (SELECT DISTINCT idy_ubica FROM ts_existenciapiezas WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_almac = c.cve_almac)), 0) AS ubicacion_produccion,

            IF(IFNULL(ad.cve_articulo, '') = '', 'N', 'S') AS tiene_documentos,

            IFNULL(c.Tipo, '') as TipoOT,
            (SELECT COUNT(*) FROM ts_existenciatarima WHERE cve_articulo = c.Cve_Articulo AND IFNULL(lote, '') = IFNULL(c.Cve_Lote, '') AND cve_almac = c.cve_almac) AS palletizado
            #'' AS traslado,
            #'' AS ubicacion_produccion,
            #'' AS palletizado
        from t_ordenprod c 
        LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
        LEFT JOIN c_lotes l ON c.Cve_Articulo = l.cve_articulo AND c.Cve_Lote = l.Lote
        LEFT JOIN th_pedido pd ON pd.Ship_Num = c.Folio_Pro
        LEFT JOIN c_almacen ca ON ca.cve_almacenp = c.cve_almac
        LEFT JOIN c_almacenp al ON al.id = ca.cve_almacenp
        LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
        LEFT JOIN c_usuario u_clave ON c.Cve_Usuario = u_clave.cve_usuario
        LEFT JOIN c_usuario u_id ON c.Cve_Usuario = u_id.id_user
        #LEFT JOIN t_tarima tt ON tt.Fol_Folio = c.Folio_Pro
        #LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        LEFT JOIN c_articulo_documento ad ON CONVERT(ad.cve_articulo, CHAR) = CONVERT(a.cve_articulo, CHAR)
        WHERE 1  
    ";

/*
    if(!empty($criterio) && !empty($filtro)){
        if($filtro == "c.Cve_Usuario"){
            $sql .= " AND {$filtro} like (select us.id_user from c_usuario us where us.nombre_completo like '%$criterio%') ";
        }
        else{
            $sql .= " AND {$filtro} like '%$criterio%' ";
        }
    }
*/

    $sql_semana = "";
    if(!empty($criterio)){
        $sql .= " AND (c.Folio_Pro LIKE '%$criterio%' OR c.Cve_Lote LIKE '%$criterio%' OR a.des_articulo LIKE '%$criterio%' OR c.Cve_Usuario LIKE '%$criterio%' OR c.Cve_Articulo LIKE '%$criterio%') ";
        $fecha_inicio = "";
        $fecha_fin = "";
        $sql_count .= " AND (c.Folio_Pro LIKE '%$criterio%' OR c.Cve_Lote LIKE '%$criterio%' OR a.des_articulo LIKE '%$criterio%' OR c.Cve_Usuario LIKE '%$criterio%' OR c.Cve_Articulo LIKE '%$criterio%') ";
    }
    else //if($status_OT == 'T')
        $sql_semana = " AND (DATE_FORMAT(c.FechaReg, '%Y-%m-%d') BETWEEN (DATE_FORMAT(CURDATE(), '%Y-%m-%d') - INTERVAL 7 DAY) AND DATE_FORMAT(CURDATE(), '%Y-%m-%d') OR DATE_FORMAT(c.Fecha, '%Y-%m-%d') BETWEEN (DATE_FORMAT(CURDATE(), '%Y-%m-%d') - INTERVAL 7 DAY) AND DATE_FORMAT(CURDATE(), '%Y-%m-%d')) ";

    if(!empty($criterioLP)){
        $sql .= " AND (ch.CveLP LIKE '%$criterioLP%') ";
        $sql_count .= " AND (ch.CveLP LIKE '%$criterioLP%') ";
    }

    if(!empty($almacen)){
        $sql .= " AND al.clave = '{$almacen}'";
        $sql_count .= " AND al.clave = '{$almacen}'";
    }

    if(!empty($Proveedor)){
        $sql .= " AND p.ID_Proveedor = '{$Proveedor}' ";
        $sql_count .= " AND p.ID_Proveedor = '{$Proveedor}' ";
    }

    if(!empty($status_OT) && empty($criterio) && empty($criterioLP)){
        $sql .= " AND c.Status = '{$status_OT}' ";
        $sql_count .= " AND c.Status = '{$status_OT}' ";
    }
    else if(empty($criterio) && empty($criterioLP))
        $sql .= " AND c.Status = 'P' ";
    $sql_count .= " AND c.Status = 'P' ";

    if(!empty($fecha_inicio) && !empty($fecha_fin)){
        $sql .= " AND c.Fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $sql_count .= " AND c.Fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $sql_semana = "";
    }
    elseif(!empty($fecha_inicio)){
        $sql .= " AND c.Fecha >= '$fecha_inicio'";
        $sql_count .= " AND c.Fecha >= '$fecha_inicio'";
        $sql_semana = "";
    }
    elseif(!empty($fecha_fin)){
        $sql .= " AND c.Fecha <= '$fecha_fin'";
        $sql_count .= " AND c.Fecha <= '$fecha_fin'";
        $sql_semana = "";
    }
/*
    if(!empty($folio_inicio) && !empty($folio_fin)){
        $sql .= " AND c.Folio_Pro BETWEEN '$folio_inicio' AND '$folio_fin'";
    }
    elseif(!empty($folio_inicio)){
        $sql .= " AND c.Folio_Pro > $folio_inicio";
    }
    elseif(!empty($folio_fin)){
        $sql .= " AND c.Folio_Pro < $folio_fin";
    }
*/

    $sql .= " {$sql_semana} GROUP BY c.Folio_Pro ";
    $sql_count .= " {$sql_semana} GROUP BY c.Folio_Pro ";

    if (!($res = mysqli_query($conn, $sql_count))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " ORDER BY c.FechaReg DESC LIMIT {$start}, {$limit}; ";

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
    $responce->sql = $sql;


    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        if($control_peso == 'S')
            $Cantidad = number_format($Cantidad, 2);
        $responce->rows[$i]['id']= $Folio_Pro;
        $responce->rows[$i]['cell']=array(  
                                          "",
                                          $Fecha,
                                          $Hora_OT,
                                          $Folio_Pro, 
                                          $folio_rel,
                                          $Cve_Articulo, 
                                          $descripcion, 
                                          $Cve_Lote, 
                                          $Caducidad,
                                          $Cantidad, 
                                          $Cant_Prod, 
                                          $usuario, 
                                          $FechaCompromiso,
                                          $status, 
                                          $Fecha_Ini,
                                          $Hora_Ini, 
                                          $Fecha_Fin,
                                          $Hora_Fin, 
                                          $proveedor,
                                          $StatusOT,
                                          $almacen,
                                          $zona,
                                          $traslado,
                                          $ubicacion_produccion,
                                          $palletizado,
                                          $tiene_documentos, 
                                          $TipoOT
                                        );
        $i++;
    }
   
  
    //echo var_dump($sql);
    //die();
  
    echo json_encode($responce);
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'lotes') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['Folio_Pro'];
    $Tipo_OT = $_POST['Tipo_OT'];
    $producido = $_POST['producido'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res = mysqli_query($conn, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        $charset = mysqli_fetch_array($res)['charset'];
        mysqli_set_charset($conn , $charset);
/*
    $sqlCount = "SELECT
                		COUNT(o.Cve_Articulo) AS total
                FROM td_ordenprod o
                LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro))
                LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.LOTE = e.cve_lote
                WHERE o.Folio_Pro = '$folio'";

    $res = mysqli_query($conn, $sqlCount);
    $row = mysqli_fetch_array($res);
    $count = $row['total'];
*/
/*
    $sql = "SELECT
                IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = o.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS cantidad,
                    o.Cve_Articulo AS clave,
                IFNULL(a.des_articulo, '--') AS descripcion,
                IFNULL(l.Lote, '--') AS lote,
                IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '--') AS caducidad,
                o.Folio_Pro AS folio
            FROM td_ordenprod  o
            LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almacenp FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro))
            LEFT JOIN td_surtidopiezas t ON t.fol_folio = o.Folio_Pro AND t.Cve_articulo = o.Cve_Articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.Lote = t.LOTE
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            WHERE o.Folio_Pro = '$folio'
*/

    $sql_zona_almac = "SELECT IFNULL(idy_ubica, '') as idy_ubica, Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = '$folio'";
    $res_zona_almac = mysqli_query($conn, $sql_zona_almac);
    $row_zona_almac = mysqli_fetch_array($res_zona_almac);
    $idy_ubica   = $row_zona_almac['idy_ubica'];
    $Cve_Usuario = $row_zona_almac['Cve_Usuario'];

//*********************************************************************************************
    // Procedo a despalletizar los LP que están en producción
//*********************************************************************************************
    $realizo_fusion = false;

        $sql = "
            SELECT DISTINCT 
                    e.Cve_Contenedor
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                    #LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = td.Cve_Articulo AND ts.fol_folio = t.Folio_Pro
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(ts.LOTE, '')
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                WHERE t.Folio_Pro = '$folio' AND e.cve_almac = t.cve_almac
                #AND e.cve_lote = td.Cve_Lote AND e.cve_articulo = td.Cve_Articulo
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo AND e.Cve_Contenedor != '' #AND e.cve_lote = td.Cve_Lote
                ";


        if($Tipo_OT == 'IMP_LP')
        $sql = "
                SELECT DISTINCT 
                    e.Cve_Contenedor
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                WHERE t.Folio_Pro = '{$folio}' AND e.cve_almac = t.cve_almac 
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo AND ch.CveLP = e.Cve_Contenedor
            ";

        if (!($res_art = mysqli_query($conn, $sql)))
        {
            echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
        }

        while($row_art_1 = mysqli_fetch_array($res_art))
        {
            $Cve_Contenedor = $row_art_1['Cve_Contenedor'];

            if($Cve_Contenedor != '' && $idy_ubica != '')
            {
                $realizo_fusion = true;

                $sql_tarimas = "SELECT cve_almac, idy_ubica, cve_articulo, lote, existencia, ID_Proveedor, IFNULL(Cuarentena, 0) as Cuarentena FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";


                if (!($res_tarimas = mysqli_query($conn, $sql_tarimas)))
                    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

                while($row_tarimas = mysqli_fetch_array($res_tarimas))
                {
                    extract($row_tarimas);
                    $sql_despalletizar = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena) VALUES ($cve_almac, $idy_ubica, '$cve_articulo', '$lote', $existencia, $ID_Proveedor, $Cuarentena) ON DUPLICATE KEY UPDATE Existencia = Existencia + $existencia";

                    if (!($res_despalletizar = mysqli_query($conn, $sql_despalletizar)))
                        echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

                    //-------------------------------------------------------
                    // 1.- Entrada a piezas
                    //-------------------------------------------------------
                    $sql_despalletizar = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES('$cve_articulo', '$lote', NOW(), '$Cve_Contenedor', '$idy_ubica', $existencia, $existencia, 0, 1, (SELECT cve_usuario FROM c_usuario WHERE id_user = '$Cve_Usuario'), $cve_almac)";

                    if (!($res_despalletizar = mysqli_query($conn, $sql_despalletizar)))
                        echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

                    //-------------------------------------------------------
                    // 2.- Salida de Tarimas
                    //-------------------------------------------------------
                    $sql_despalletizar = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES('$cve_articulo', '$lote', NOW(), '$Cve_Contenedor', '$idy_ubica', $existencia, $existencia, 0, 8, (SELECT cve_usuario FROM c_usuario WHERE id_user = '$Cve_Usuario'), $cve_almac)";

                    if (!($res_despalletizar = mysqli_query($conn, $sql_despalletizar)))
                        echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

                    $sql_despalletizar = "INSERT INTO t_MovCharolas (id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES((SELECT (IFNULL(MAX(id), 0)) FROM t_cardex),$cve_almac, (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), NOW(), '$Cve_Contenedor', '$idy_ubica', 8, (SELECT cve_usuario FROM c_usuario WHERE id_user = '$Cve_Usuario'), 'O')";

                    if (!($res_despalletizar = mysqli_query($conn, $sql_despalletizar)))
                        echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
                }

                $sql_tarimas = "DELETE FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";
                if (!($res_tarimas = mysqli_query($conn, $sql_tarimas)))
                    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
            }

        }



//*********************************************************************************************
//*********************************************************************************************

    //-------------------------------------------------
    //1.- Consulto cual artículo es apto para fusionar
    //-------------------------------------------------


    $SQLzona = " AND e.cve_ubicacion IN (SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND Activo = 1 AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = (SELECT Cve_Almac FROM t_ordenprod WHERE Folio_Pro = '$folio'))) ";

    if($idy_ubica != '')    
        $SQLzona = " AND e.cve_ubicacion = {$idy_ubica} ";

    $sql_fusionar = "
SELECT * FROM (
SELECT 
    f_lote.clave AS clave,
    SUM(f_lote.cantidad) AS cantidad,
    COUNT(f_lote.N) AS N
FROM (
    SELECT DISTINCT
        o.Cve_Articulo AS clave,
        e.Existencia AS cantidad,
        o.Cve_Articulo AS N
    FROM td_ordenprod  o
    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) #AND e.cve_lote = o.Cve_Lote
    LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND e.cve_lote = l.Lote
    LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
    WHERE o.Folio_Pro = '$folio' AND e.Cve_Contenedor = '' #AND a.Compuesto != 'S' 
    {$SQLzona}
) AS f_lote
GROUP BY clave
) AS fusionar WHERE fusionar.N > 1";

        $res_fusionar = mysqli_query($conn, $sql_fusionar);
        if(mysqli_num_rows($res_fusionar))
        {
            //------------------------------------------------------
            //2.- Si es apto para fusionar, consulto los artículos
            //------------------------------------------------------
            while($row_fusionar = mysqli_fetch_array($res_fusionar))
            {
                $clave_fusionar = $row_fusionar["clave"];
                $cantidad_fusionar = $row_fusionar["cantidad"];

                $sql_fusion = "
                    SELECT DISTINCT
                        o.Cve_Articulo AS clave,
                        IFNULL(e.cve_lote, '') AS lote,
                        IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                        e.Existencia AS cantidad
                    FROM td_ordenprod  o
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) #AND e.cve_lote = o.Cve_Lote
                    LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND e.cve_lote = l.Lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
                    WHERE o.Folio_Pro = '$folio' AND e.Cve_Contenedor = '' #AND a.Compuesto != 'S' 
                    AND o.Cve_Articulo = '$clave_fusionar'
                    {$SQLzona}
                    ORDER BY clave, DATE_FORMAT(caducidad, '%Y%m%d') ASC";

                $res_fusion = mysqli_query($conn, $sql_fusion);

                $primera_fusion = true;
                $articulo_fusionar = ""; $lote_fusionar = "";
                while($row_fusion = mysqli_fetch_array($res_fusion))
                {
                    //------------------------------------------------------
                    //3.- aparto el artículo a fusionar
                    //------------------------------------------------------
                    $cantidad = $row_fusion["cantidad"];

                    if($primera_fusion == true)
                    {
                        $articulo_fusionar = $row_fusion["clave"]; 
                        $lote_fusionar = $row_fusion["lote"];

                        //---------------------------------------------------------
                        //4.- Registro la Entrada con la cantidad de los demás 
                        //    artículos, verificando si está en pallet o en piezas
                        //---------------------------------------------------------

                        $cantidad_fusionar -= $cantidad;

                        $sql_pallet_piezas = "
                            SELECT e.Cve_Contenedor, e.cve_ubicacion, e.cve_almac, e.Existencia FROM V_ExistenciaProduccion e WHERE e.cve_articulo = '$articulo_fusionar' AND e.cve_lote = '$lote_fusionar' {$SQLzona}
                        ";

                        $res_pallet_piezas = mysqli_query($conn, $sql_pallet_piezas);
                        $row_pallet_piezas = mysqli_fetch_array($res_pallet_piezas);

                        $Cve_Contenedor = $row_pallet_piezas['Cve_Contenedor'];
                        $cve_ubicacion  = $row_pallet_piezas['cve_ubicacion'];
                        $cve_almac      = $row_pallet_piezas['cve_almac'];
                        $Existencia     = $row_pallet_piezas['Existencia'];

                        if($Cve_Contenedor == '')
                        {
                            $sql_piezas = "
                                UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_fusionar WHERE idy_ubica = $cve_ubicacion AND cve_articulo = '$articulo_fusionar' AND cve_lote = '$lote_fusionar'
                            ";
                            $res_piezas = mysqli_query($conn, $sql_piezas);
                        }
                        else
                        {
                            $sql_pallet = "
                                UPDATE ts_existenciatarima SET existencia = existencia + $cantidad_fusionar WHERE idy_ubica = $cve_ubicacion AND cve_articulo = '$articulo_fusionar' AND lote = '$lote_fusionar' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')
                            ";
                            $res_pallet = mysqli_query($conn, $sql_pallet);

                        }

                        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$articulo_fusionar', '$lote_fusionar', NOW(), '$folio', '$cve_ubicacion', $Existencia, $cantidad_fusionar, ($Existencia-$cantidad_fusionar), 1, (SELECT Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = '$folio'), $cve_almac, 1)";
                        $res_kardex = mysqli_query($conn, $sql_kardex);

                        $primera_fusion = false;
                        continue;
                    }

                        //---------------------------------------------------------
                        //5.- Registro la Salida con la cantidad de los demás 
                        //    artículos, verificando si está en pallet o en piezas
                        //---------------------------------------------------------
                        $articulo_fusionar = $row_fusion["clave"]; 
                        $lote_fusionar = $row_fusion["lote"];

                        $sql_pallet_piezas = "
                            SELECT e.Cve_Contenedor, e.cve_ubicacion, e.cve_almac, e.Existencia FROM V_ExistenciaProduccion e WHERE e.cve_articulo = '$articulo_fusionar' AND e.cve_lote = '$lote_fusionar' {$SQLzona}
                        ";

                        $res_pallet_piezas = mysqli_query($conn, $sql_pallet_piezas);
                        $row_pallet_piezas = mysqli_fetch_array($res_pallet_piezas);

                        $Cve_Contenedor = $row_pallet_piezas['Cve_Contenedor'];
                        $cve_ubicacion  = $row_pallet_piezas['cve_ubicacion'];
                        $cve_almac      = $row_pallet_piezas['cve_almac'];
                        $Existencia     = $row_pallet_piezas['Existencia'];

                        if($Cve_Contenedor == '')
                        {
                            $sql_piezas = "
                                UPDATE ts_existenciapiezas SET Existencia = 0 WHERE idy_ubica = $cve_ubicacion AND cve_articulo = '$articulo_fusionar' AND cve_lote = '$lote_fusionar'
                            ";
                            $res_piezas = mysqli_query($conn, $sql_piezas);
                        }
                        else
                        {
                            $sql_pallet = "
                                UPDATE ts_existenciatarima SET existencia = 0 WHERE idy_ubica = $cve_ubicacion AND cve_articulo = '$articulo_fusionar' AND lote = '$lote_fusionar' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')
                            ";
                            $res_pallet = mysqli_query($conn, $sql_pallet);

                        }

                        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$articulo_fusionar', '$lote_fusionar', NOW(), '$cve_ubicacion', '$folio', $Existencia, $cantidad, ($Existencia-$cantidad), 8, (SELECT Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = '$folio'), $cve_almac, 1)";
                        $res_kardex = mysqli_query($conn, $sql_kardex);


                }

                $realizo_fusion = true;
            }

        }

//*********************************************************************************************
//*********************************************************************************************
/*
    $sql = "SELECT DISTINCT
                TRUNCATE(IFNULL(e.Existencia, 0), 4) AS cantidad,                    
                o.Cve_Articulo AS clave,
                IFNULL(a.des_articulo, '') AS descripcion,
                IFNULL(l.Lote, '') AS lote,
                IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                u.CodigoCSD as BL,
                IFNULL(e.Cve_Contenedor, '') as LP,
                o.Folio_Pro AS folio
            FROM td_ordenprod  o
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) #AND e.cve_lote = o.Cve_Lote
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND e.cve_lote = l.Lote
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            INNER JOIN td_surtidopiezas s ON s.fol_folio = o.Folio_Pro AND s.cve_articulo = e.cve_articulo and s.LOTE = e.cve_lote
            WHERE o.Folio_Pro = '{$folio}' #AND a.Compuesto != 'S' #AND e.Cve_Contenedor = ''
            {$SQLzona}
            ORDER BY BL, clave, caducidad ASC
            ";
*/
    if(!$producido) $producido = 0;
    $sql = "SELECT DISTINCT
                #TRUNCATE(IFNULL(e.Existencia, 0), 4) AS cantidad,
                #TRUNCATE(IFNULL(s.Cantidad, 0), 4) AS cantidad,
                #ROUND((TRUNCATE(IFNULL(s.Cantidad, 0), 4) - (($producido)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 4)))), 4) AS cantidad,
                IF(s.Cve_articulo = op.Cve_Articulo, e.Existencia, IF(um.mav_cveunimed = 'H87' AND a.control_peso = 'S', ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 5)*IFNULL(a.peso, 0) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 5)))), 4),
                    IF(ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 5) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 5)))), 4) = '-0', 0, 
                    ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 5) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 5)))), 4)
                    ))) AS cantidad,
                #o.Cve_Articulo AS clave,
                s.Cve_articulo AS clave,
                IFNULL(a.des_articulo, '') AS descripcion,
                IFNULL(s.Lote, '') AS lote,
                IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                u.CodigoCSD AS BL,
                IFNULL(e.Cve_Contenedor, '') AS LP,
                o.Folio_Pro AS folio
            FROM td_ordenprod  o
            LEFT JOIN t_ordenprod op ON op.Folio_Pro = o.Folio_Pro
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(o.Cve_Lote, '')
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            INNER JOIN td_surtidopiezas s ON s.fol_folio = o.Folio_Pro AND s.cve_articulo = o.Cve_Articulo #AND s.LOTE = IFNULL(o.Cve_Lote, '')
            LEFT JOIN c_lotes l ON l.cve_articulo = s.cve_articulo AND l.Lote = s.Lote
            LEFT JOIN t_artcompuesto ac ON ac.Cve_Articulo = s.Cve_articulo AND ac.Cve_ArtComponente = op.Cve_Articulo
            WHERE o.Folio_Pro = '{$folio}' #AND a.Compuesto != 'S' #AND e.Cve_Contenedor = ''
            {$SQLzona}
            ORDER BY BL, clave, caducidad ASC
        ";

            //echo $sql;
/*
            UNION 

            SELECT DISTINCT 
            o.Existencia AS cantidad,
            o.Cve_Articulo AS clave,
            IFNULL(a.des_articulo, '--') AS descripcion,
            IFNULL(l.Lote, '--') AS lote,
            IF(IFNULL(DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y'), '--') = DATE_FORMAT('00-00-0000', '%d-%m-%Y'), IFNULL(DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y'), '--'), '') AS caducidad,
            '' as LP,
            '{$folio}' AS folio
            FROM V_ExistenciaGralProduccion o 
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_Articulo AND l.Lote = o.cve_lote
            WHERE o.cve_articulo = a.cve_articulo AND a.Compuesto != 'S' AND l.Lote = '{$folio}'

    if($Tipo_OT == 'IMP_LP')
    {

        $sql = "SELECT DISTINCT
                tt.cantidad AS cantidad,
                o.Cve_Articulo AS clave,
                IFNULL(a.des_articulo, '--') AS descripcion,
                IFNULL(l.Lote, '--') AS lote,
                IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '--') AS caducidad,
                ch.CveLP AS LP,
                o.Folio_Pro AS folio
            FROM td_ordenprod  o
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almacenp FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro)) AND e.cve_lote = o.Cve_Lote
            LEFT JOIN td_surtidopiezas t ON t.fol_folio = o.Folio_Pro AND t.Cve_articulo = o.Cve_Articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.Lote = t.LOTE AND o.Cve_Lote = l.Lote
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = '{$folio}'
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
            WHERE o.Folio_Pro = '{$folio}'";

        $sql = "SELECT DISTINCT
                tt.cantidad AS cantidad,
                o.Cve_Articulo AS clave,
                IFNULL(a.des_articulo, '--') AS descripcion,
                IFNULL(l.Lote, '--') AS lote,
                IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '--') AS caducidad,
                ch.CveLP AS LP,
                o.Folio_Pro AS folio
            FROM t_ordenprod o
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almacenp FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro)) AND e.cve_lote = o.Cve_Lote
            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND e.cve_lote = IFNULL(l.Lote, '') AND IFNULL(o.Cve_Lote, '') = IFNULL(l.Lote, '')
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = '{$folio}' 
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
            WHERE o.Folio_Pro = '{$folio}' 
            GROUP BY LP, clave, lote, folio";
    }
*/
    $res = mysqli_query($conn, $sql);
    $count = mysqli_num_rows($res);
    //$sql .= " LIMIT {$start}, {$limit} ";
    //$res = mysqli_query($conn, $sql);

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
    $responce->sql = $sql;
    $responce->rows = array();
    $responce->rows[]['cell'] = array();


    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        //$row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        if($cantidad < 0) $cantidad = 0;
        $responce->rows[$i]['id']= $i;
        $responce->rows[$i]['cell']=array($LP, $BL,$clave, $descripcion, $lote, $caducidad, $folio, $cantidad);
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'lotes_kit') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['Folio_Pro'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = "SELECT
                        COUNT(o.Cve_Articulo) AS total
                FROM td_ordenprod o
                LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro))
                LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.LOTE = e.cve_lote
                WHERE o.Folio_Pro = '$folio'";

    $res = mysqli_query($conn, $sqlCount);
    $row = mysqli_fetch_array($res);
    $count = $row['total'];
/*
    $sql = "SELECT
                IFNULL(DATE_FORMAT(MIN(l.CADUCIDAD), '%d-%m-%Y'), '--') AS caducidad
            FROM td_ordenprod  o
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almacenp FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro))
            LEFT JOIN td_surtidopiezas t ON t.fol_folio = o.Folio_Pro AND t.Cve_articulo = e.cve_articulo 
            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.LOTE = t.LOTE
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            WHERE o.Folio_Pro = '$folio'
            ";

    $res_cad = mysqli_query($conn, $sql);

    $row_cad = mysqli_fetch_array($res_cad);
    $cad = $row_cad["caducidad"];
*/
    $sql_lote = "SELECT Cve_Articulo, Cve_Lote, Tipo, IFNULL(idy_ubica, '') as idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$folio'";
    $res = mysqli_query($conn, $sql_lote);
    $row = mysqli_fetch_array($res);
    $cve_articulo = $row['Cve_Articulo'];
    $cve_lote = $row['Cve_Lote'];
    $TipoOT = $row['Tipo'];

    if($cve_lote)
    {
        $sql_lote = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = '{$cve_lote}'";
        $res = mysqli_query($conn, $sql_lote);
        $row = mysqli_fetch_array($res);
        $existe = $row['existe'];

        if(!$existe)
        {
            $sql_lote = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cve_articulo}', '{$cve_lote}', CURDATE())";
            $res = mysqli_query($conn, $sql_lote);
        }
    }


    $sql = "SELECT DISTINCT
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = o.cve_articulo AND vp.tipo = 'ubicacion' AND vp.cve_lote = o.Cve_Lote), 0) AS cantidad,
            #IFNULL((SELECT SUM(vp.Existencia) FROM ts_existenciapiezas vp, c_ubicacion cu WHERE cu.idy_ubica = vp.idy_ubica AND vp.cve_articulo = o.cve_articulo AND vp.cve_lote = o.Cve_Lote), 0) AS cantidad,
            ROUND(TRUNCATE(IFNULL(e.Existencia, 0), 5), 4) AS cantidad,
            u.CodigoCSD as BL,
            #(SELECT SUM(Cantidad) FROM td_surtidopiezas WHERE fol_folio = o.Folio_Pro AND cve_articulo = o.Cve_Articulo AND LOTE = o.Cve_Lote) AS cantidad,
            o.Cve_Articulo AS clave,
            IFNULL(a.des_articulo, '') AS descripcion,
            IFNULL(o.Cve_Lote, '') AS lote,
            IFNULL(DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y'), '') AS caducidad,
            IF(IFNULL(e.Cve_Contenedor, '') = '', '', e.Cve_Contenedor) AS Cve_Contenedor,
            o.Folio_Pro AS folio
            FROM t_ordenprod o
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = o.Folio_Pro AND o.Cve_Articulo = tt.cve_articulo 
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
            #LEFT JOIN ts_existenciapiezas e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) 
            LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) AND IFNULL(o.Cve_Lote, '') = IFNULL(e.cve_lote, '')
            #LEFT JOIN c_ubicacion u ON u.idy_ubica = IFNULL(o.idy_ubica_dest, o.idy_ubica) #AND u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_ubicacion u ON  u.idy_ubica = e.cve_ubicacion AND u.idy_ubica in (o.idy_ubica_dest, o.idy_ubica)
            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.Lote = IFNULL(o.Cve_Lote, '')
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo 
            WHERE o.Folio_Pro = '$folio' AND a.cve_articulo != '' #AND e.cve_lote != '' AND e.cve_lote = o.Cve_Lote
            LIMIT {$start}, {$limit}
            ";

            if($TipoOT == 'IMP_LP')
            {
            $sql = "SELECT * FROM (
                SELECT DISTINCT

                            IFNULL(e.Existencia, 0) AS cantidad,
                            u.CodigoCSD as BL,
                            o.Cve_Articulo AS clave,
                            IFNULL(a.des_articulo, '') AS descripcion,
                            IFNULL(o.Cve_Lote, '') AS lote,
                            IFNULL(DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y'), '') AS caducidad,
                            IFNULL(e.Cve_Contenedor, '') AS Cve_Contenedor,
                            o.Folio_Pro AS folio
                            FROM t_ordenprod o
                            LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) AND IFNULL(o.Cve_Lote, '') = IFNULL(e.cve_lote, '') 
                            AND IFNULL(e.Cve_Contenedor, '') IN (SELECT clave_contenedor FROM c_charolas WHERE IDContenedor IN (SELECT ntarima FROM t_tarima WHERE fol_folio = '$folio'))
                            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.Lote = o.Cve_Lote
                            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo 
                            LEFT JOIN c_ubicacion u ON u.idy_ubica = IFNULL(o.idy_ubica_dest, o.idy_ubica) AND u.idy_ubica = e.cve_ubicacion
                            WHERE o.Folio_Pro = '$folio' AND IFNULL(e.Cve_Contenedor, '') != ''

                UNION 

                SELECT DISTINCT
                            0 AS cantidad,
                            '' as BL,
                            o.Cve_Articulo AS clave,
                            IFNULL(a.des_articulo, '') AS descripcion,
                            IFNULL(o.Cve_Lote, '') AS lote,
                            IFNULL(DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y'), '') AS caducidad,
                            ch.CveLP AS Cve_Contenedor,
                            o.Folio_Pro AS folio
                            FROM t_ordenprod o
                            INNER JOIN t_tarima tt ON tt.Fol_Folio = o.Folio_Pro AND o.Cve_Articulo = tt.cve_articulo 
                            INNER JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
                            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.Lote = o.Cve_Lote
                            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo 
                            WHERE o.Folio_Pro = '$folio' AND a.cve_articulo != '' 
                ) AS prod
                GROUP BY folio, Cve_Contenedor, clave, lote 
                 LIMIT {$start}, {$limit} ";
            }

    $res = mysqli_query($conn, $sql);


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
    $responce->sql = $sql;
    //$responce->realizo_fusion = $realizo_fusion;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        //$caducidad = $cad;
        $responce->rows[$i]['id']= $i;
        $responce->rows[$i]['cell']=array($Cve_Contenedor, $BL, $clave, $descripcion, $lote, $caducidad, $folio, $cantidad);

        $responce->lote_ot = $lote;
        $responce->caducidad = $caducidad;

        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'derivados_kit') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['Folio_Pro'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT u.CodigoCSD AS BL, a.cve_articulo AS Clave, a.des_articulo AS Descripcion, l.Lote, 
                   IF(a.Caduca = 'S' AND a.control_lotes = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad, 
                   e.Existencia AS Stock
            FROM ts_existenciapiezas e 
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND IFNULL(l.Lote, '') = IFNULL(e.cve_lote, '')
            LEFT JOIN c_ubicacion u ON e.idy_ubica = u.idy_ubica
            WHERE e.ClaveEtiqueta = '$folio' #AND IF(a.Caduca = 'S' AND a.control_lotes = 'S', IF(l.Caducidad < CURDATE(), 0, 1), 1) = 1

            UNION 

            SELECT u.CodigoCSD AS BL, k.cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(l.Lote, '') AS Lote, 
                   IF(a.Caduca = 'S' AND a.control_lotes = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad, 
                   CONCAT('<div style=\"color:red;\">-', SUM(k.cantidad), '</div>') AS Stock
            FROM t_cardex k
            LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = k.origen
            LEFT JOIN c_lotes l ON l.cve_articulo = k.cve_articulo AND IFNULL(l.Lote, '') = IFNULL(k.cve_lote, '')
            WHERE k.destino LIKE '%$folio%' AND k.Id_TipoMovimiento = 8 AND k.cve_usuario = ''
            GROUP BY BL, Clave, Lote
            ";
    $res = mysqli_query($conn, $sql);

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$start}, {$limit} ";
    $res = mysqli_query($conn, $sql);

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
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']= $i;
        $responce->rows[$i]['cell']=array($BL, $Clave, $Descripcion, $Lote, $Caducidad, $Stock);
        $i++;
    }
    echo json_encode($responce);
}


if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'articulos') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['Folio_Pro'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sqlCount = "SELECT
                		COUNT(Cve_Articulo) AS total
                FROM td_ordenprod
                WHERE Folio_Pro = '$folio'";

    $res = mysqli_query($conn, $sqlCount);
    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    $sql = "SELECT
            		    o.Cve_Articulo AS clave,
                    IFNULL(a.des_articulo, '') AS descripcion,
                    o.Cve_Lote AS lote,
                    l.CADUCIDAD As caducidad,
                    IFNULL(o.Cantidad, 0)AS cantidad,
                    o.Folio_Pro AS folio
            FROM td_ordenprod  o
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.LOTE = o.Cve_Lote
            WHERE o.Folio_Pro = '$folio'
            LIMIT {$start}, {$limit}
            ";
    $res = mysqli_query($conn, $sql);

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
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']= $i;
        $responce->rows[$i]['cell']=array($clave, $descripcion, $lote, $caducidad, $cantidad, $folio);
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'componentes') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['Folio_Pro'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sqlCount = "SELECT
                		COUNT(Cve_Articulo) AS total
                FROM t_artcompuesto  o
                WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$folio')";

    $res = mysqli_query($conn, $sqlCount);
    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    $sql = "
    SELECT DISTINCT 
    a.cve_articulo AS clave,
        a.des_articulo AS articulo,
    u.des_umed AS unidad_medida,
    IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$folio')) AS unidades_producto,
    a.control_peso, 
    TRUNCATE((t.Cant_Prod*ac.Cantidad), 5) AS Cantidad_Producida,
    #TRUNCATE(IF(IFNULL(th.Fol_folio, '') = '', (SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$folio')*ac.Cantidad, ac.Cantidad), 5) AS cantidad,
    TRUNCATE(IF(IFNULL(th.Fol_folio, '') = '', td.Cantidad, ac.Cantidad), 5) AS cantidad,
    #TRUNCATE(IF(IFNULL(th.Fol_folio, '') = '', ((SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$folio')*ac.Cantidad - (t.Cant_Prod*ac.Cantidad)), ac.Cantidad), 5) AS Cantidad_Faltante 
    TRUNCATE(IF(IFNULL(th.Fol_folio, '') = '', (td.Cantidad - (t.Cant_Prod*ac.Cantidad)), ac.Cantidad), 5) AS Cantidad_Faltante 
    #IF(IFNULL(th.Fol_folio, '') = '', IF(a.control_peso = 'S', CONCAT(TRUNCATE((SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = t.Folio_Pro), 3), ''), (SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = t.Folio_Pro))*ac.Cantidad, ac.Cantidad) AS cantidad,
    #IF(IFNULL(th.Fol_folio, '') = '', (IF(a.control_peso = 'S', CONCAT(TRUNCATE((SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = t.Folio_Pro), 3), ''), (SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = t.Folio_Pro))*ac.Cantidad - (t.Cant_Prod*ac.Cantidad)), ac.Cantidad) AS Cantidad_Faltante 
    FROM t_artcompuesto ac
    #LEFT JOIN c_almacen z On z.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = '$folio')
    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro AND td.Cve_Articulo = ac.Cve_Articulo
    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
    LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
    WHERE ac.Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$folio') AND ac.Cve_Articulo = td.Cve_Articulo 
    AND t.Folio_Pro = '$folio' AND IFNULL(a.cve_articulo, '') != ''  
    LIMIT {$start}, {$limit}
            ";
    $res = mysqli_query($conn, $sql);

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
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']= $i;
        $responce->rows[$i]['cell']=array($clave, $articulo, $unidad_medida, $unidades_producto, $cantidad, $Cantidad_Producida, $Cantidad_Faltante);
        $i++;
    }
    //echo var_dump($sql);
    //die();
  
    echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'obtenerLoteProduccion'){
  $folio = $_GET['folio'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $lote = '';
  $articulos = '';

  $sql = "SELECT IFNULL(Cve_Lote, 0) FROM t_ordenprod WHERE Folio_Pro = '$folio'";
  $query = mysqli_query($conn, $sql);
  $lote = $query->num_rows > 0 ? mysqli_fetch_row($query)[0] : 0;

  if($lote == "0"){
    $sql = "SELECT IFNULL(MAX(Cve_Lote), 0) as lote FROM `t_ordenprod`";
    $query = mysqli_query($conn, $sql);
    $lote = $query->num_rows > 0 ? mysqli_fetch_row($query)[0] : 0;
    if($lote == "0" || empty($lote)){
        $sql = "SELECT IFNULL(CONCAT(REPEAT('0',  6 - LENGTH(Trim(numero))), Trim(numero)), 0) FROM t_consecutivos_documentos WHERE nombre = 'lotes_produccion'";
        $query = mysqli_query($conn, $sql);
        if($query->num_rows > 0){
            $lote = "LPO".mysqli_fetch_row($query)[0];
        }else{
            $sql = "SELECT `fct_consecutivo_documentos`('lotes_produccion', 6)";
            $query = mysqli_query($conn, $sql);
            if($query->num_rows > 0){
                $lote = "LPO".mysqli_fetch_row($query)[0];
            }
        }
    }else{
      $lote = "LPO".str_pad((intval(substr($lote, 3, 6)) + 1), 6, "0", STR_PAD_LEFT);
    }
  }

  $sql = "SELECT  o.Cve_Articulo AS clave,
                  (SELECT @caducidad := DATE_FORMAT(MIN(STR_TO_DATE(CADUCIDAD, '%d-%m-%Y')), '%d-%m-%Y') FROM c_lotes WHERE cve_articulo = o.Cve_Articulo AND LOTE IN (SELECT cve_lote FROM V_ExistenciaProduccion WHERE cve_articulo = o.Cve_Articulo AND cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro))) AS caducidad,
                  (SELECT LOTE FROM c_lotes WHERE cve_articulo = o.Cve_Articulo AND CADUCIDAD = (SELECT @caducidad) ) AS lote
          FROM td_ordenprod o
          WHERE o.Folio_pro = '$folio'
  ";
  $query = mysqli_query($conn, $sql);

  if($query->num_rows > 0){
      $articulos = mysqli_fetch_all($query, MYSQLI_ASSOC);
  }

  $sql = "SELECT  DATE_FORMAT(MIN(STR_TO_DATE(l.CADUCIDAD, '%d-%m-%Y')), '%d-%m-%Y') AS caducidad
          FROM td_ordenprod o
          LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro)
          LEFT JOIN c_lotes l ON l.cve_articulo = o.Cve_Articulo AND l.LOTE = e.cve_lote
          WHERE o.Folio_pro = '$folio';
  ";
  $query = mysqli_query($conn, $sql);

  if($query->num_rows > 0){
      $caducidad = mysqli_fetch_row($query)[0];
  }

  $produccion = array(
    'lote' => $lote,
    'caducidad' => $caducidad
  );

  mysqli_close($conn);

  echo json_encode(
    array(
      "produccion" => $produccion,
      "articulos" => $articulos
    )
  );
}
if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'dataParaImprimir'){
  $folio = $_GET['folio'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $data = [];
  $sql = "SELECT    a.des_articulo AS articulo,
                    a.cve_articulo AS clave,
                    a.barras2 AS barras2,
                    a.barras3 AS barras3,
                    o.Folio_Pro AS ordenp,
                    c.Cantidad AS unidades_caja,
                    o.Cve_Lote As lote
          FROM t_ordenprod o
          LEFT JOIN t_artcompuesto c ON c.Cve_ArtComponente = o.Cve_Articulo
          LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
          WHERE o.Folio_Pro = '$folio'
          GROUP BY o.Cve_Articulo
                    ";
  $query = mysqli_query($conn, $sql);
  if($query->num_rows > 0){
      $data = mysqli_fetch_assoc($query);
  }

  mysqli_close($conn);

  echo json_encode(
      array(
          "compuesto" => $data
      )
  );
}


if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'nuevospedidos'){
    $folio = $_GET['folio'];
    $almacen = $_GET['almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT ch.CveLP 
            FROM ts_existenciatarima ts
            LEFT JOIN c_charolas ch ON ts.ntarima = ch.IDContenedor
            WHERE ts.cve_almac = {$almacen} 
            AND CONCAT(ts.cve_articulo, ts.lote) IN (SELECT CONCAT(Cve_Articulo, Cve_Lote) FROM t_ordenprod WHERE Status = 'T' AND cve_almac = {$almacen} AND Folio_Pro = '{$folio}')";
    // hace una llamada previa al procedimiento
  
   // echo var_dump($sql);
    //die();
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 5: (" . mysqli_error($conn) . ") ".$sql;
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
    $i = 0;
    $arr = array();

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['CveLP'];
        $responce->rows[$i]['cell']=array($row['CveLP']);
        $i++;
    }
    echo json_encode($arr);
}
