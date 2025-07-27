<?php
include '../../config.php';

if(isset($_POST['action']) && !empty($_POST['action'])){ 

    switch ($_POST['action']) {
        case 'enter-view':

                $id_user = $_POST['id_user'];
                $sql = 'SELECT id, clave, nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac WHERE c_almacenp.Activo = 1 AND t_usu_alm_pre.id_user = '.$id_user.'';
                //$sql = 'SELECT id, clave, nombre FROM c_almacenp WHERE Activo = 1';
                $res = getArraySQL($sql);

                $array = [
                    "almacen"=>$res
                ];

                echo json_encode($array);
            break;
        case 'getListTable':

                $sql_almacen = "";
                $sql_producto = "";
                $sql_lote = "";
                $sql_feIn = "";
                $sql_feEn = "";

                if(isset($_POST['idAl'])){
                    $sql_almacen = " and a.Cve_Almac = '".$_POST['idAl']."'";
                }

                if(isset($_POST['idAr'])){
                    $sql_producto = " and a.cve_articulo = '".$_POST['idAr']."'";
                }

                if(isset($_POST['idLo'])){
                    $sql_lote = " and a.cve_lote = '".$_POST['idLo']."'";
                }

                if(isset($_POST['feIn'])){
                    $sql_feIn = " and a.fecha >= '".$_POST['feIn']."'";
                }

                if(isset($_POST['feEn'])){
                    $sql_feEn = " and a.fecha <= '".$_POST['feEn']."'";
                }

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        " " AS CodigoCSD, DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, 
                        a.destino, a.cantidad, a.id_TipoMovimiento, (b.Nombre)prove_clave, (f.desc_ubicacion)ubi_nombre, a.cve_usuario, (e.clave)almacen_clave, 
                        (e.nombre)almacen_nombre, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.clave_contenedor, "") AS contenedor, IFNULL(ch.CveLP, "") AS LP
                    FROM t_cardex a
                    LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                    LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                    , c_proveedores b, c_articulo c, td_entalmacen d
                    LEFT JOIN td_entalmacenxtarima axt ON axt.fol_folio = d.fol_folio
                    LEFT JOIN c_charolas ch ON ch.CveLP = axt.ClaveEtiqueta
                    , tubicacionesretencion f, c_almacenp e, th_entalmacen h 
                    WHERE a.id_TipoMovimiento = 1 AND a.cve_articulo = c.cve_articulo AND d.num_orden = h.id_ocompra AND a.origen = d.fol_folio AND 
                          a.destino = d.cve_ubicacion AND b.ID_Proveedor = h.Cve_Proveedor AND a.cve_articulo = d.cve_articulo AND a.cve_lote = d.cve_lote AND 
                          d.cve_ubicacion = f.cve_ubicacion AND 
                          f.cve_almacp = e.id'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $sql .= ' 
                UNION 

                    SELECT DISTINCT
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        ub.CodigoCSD AS CodigoCSD, DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, 
                        a.destino, a.cantidad, a.id_TipoMovimiento, (b.Nombre)prove_clave, "" AS ubi_nombre, a.cve_usuario, (e.clave)almacen_clave, 
                        (e.nombre)almacen_nombre, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        "" AS contenedor, "" AS LP
                    FROM t_cardex a
                    LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                    LEFT JOIN ts_existenciatarima tst ON tst.idy_ubica = a.destino
                    LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                    LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                    , c_proveedores b, c_articulo c
                    , c_almacenp e
                    WHERE a.id_TipoMovimiento = 1 AND a.cve_articulo = c.cve_articulo AND a.origen = "Inventario Inicial" AND a.cve_articulo = tst.cve_articulo
                    AND (tst.ID_Proveedor = b.ID_Proveedor)
                    AND (e.id = tst.cve_almac) AND a.Cve_Almac = tst.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $sql .= ' 
                UNION 

                SELECT DISTINCT
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        ub.CodigoCSD AS CodigoCSD, DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, 
                        a.destino, a.cantidad, a.id_TipoMovimiento, (b.Nombre)prove_clave, "" AS ubi_nombre, a.cve_usuario, (e.clave)almacen_clave, 
                        (e.nombre)almacen_nombre, 
                    IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        "" AS contenedor, "" AS LP
                    FROM t_cardex a
                    LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                    LEFT JOIN ts_existenciapiezas tsp ON tsp.idy_ubica = a.destino 
                    LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                    LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                    , c_proveedores b, c_articulo c
                    , c_almacenp e
                    WHERE a.id_TipoMovimiento = 1 AND a.cve_articulo = c.cve_articulo AND a.origen = "Inventario Inicial" AND a.cve_articulo = tsp.cve_articulo
                    AND (b.ID_Proveedor = tsp.ID_Proveedor)
                    AND (e.id = tsp.cve_almac) AND a.Cve_Almac = tsp.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $sql .= ' 
                UNION 

                SELECT DISTINCT
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        "" AS CodigoCSD, DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, CONCAT("(", e.clave, ") ", e.nombre) AS origen, 
                        CONCAT("(", ap.clave,") ", ap.nombre, " -> (", a.destino, ") ", tur.desc_ubicacion) AS destino, a.cantidad, a.id_TipoMovimiento, "" AS prove_clave, "" AS ubi_nombre, a.cve_usuario, (e.clave)almacen_clave, 
                        (e.nombre)almacen_nombre,
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        "" AS contenedor, "" AS LP
                    FROM t_cardex a
                    LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                    LEFT JOIN th_entalmacen th ON a.destino = th.cve_ubicacion AND th.STATUS = "E"
                    LEFT JOIN tubicacionesretencion tur ON tur.cve_ubicacion = a.destino
                    LEFT JOIN c_almacenp ap ON ap.id = tur.cve_almacp
                    LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                    , c_proveedores b, c_articulo c, c_almacenp e
                    WHERE a.id_TipoMovimiento = 1 AND a.cve_articulo = c.cve_articulo AND e.id = a.origen AND th.Cve_Proveedor = b.ID_Proveedor'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $entrada = getArraySQL($sql);

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        IFNULL(d.CodigoCSD, "") AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, a.destino, a.cantidad, a.id_TipoMovimiento, 
                        (b.clave)almacen_clave, a.cve_usuario, (b.nombre)almacen_nombre, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        (e.des_almac)almacen_des, g.cve_ubicacion, 
                        (g.desc_ubicacion)ubi_nombre, d.cve_pasillo, d.cve_rack, d.cve_nivel, d.Seccion, (d.Ubicacion)posicion, IFNULL(ch.clave_contenedor, "") AS contenedor, IFNULL(ch.CveLP, "") AS LP
                        FROM t_cardex a
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.destino 
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        , c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e, td_entalmacen f, tubicacionesretencion g 
                        WHERE a.id_TipoMovimiento = 2 AND a.cve_articulo = c.cve_articulo AND 
                              a.Cve_Almac = b.id AND a.cve_articulo = c.cve_articulo AND 
                              a.destino = d.idy_ubica AND d.cve_almac = e.cve_almac AND 
                              a.origen = f.cve_ubicacion AND a.cve_articulo = f.cve_articulo AND 
                              a.cve_lote = f.cve_lote AND 
                              f.cve_ubicacion = g.cve_ubicacion'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $acomodo = getArraySQL($sql);

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, b.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        " " AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, a.destino, 
                        a.cantidad, a.id_TipoMovimiento, a.cve_usuario, IFNULL(ch.clave_contenedor, "") AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.CveLP, "") AS LP
                        FROM t_cardex a
                        LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.destino 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_articulo b 
                        WHERE a.id_TipoMovimiento = 12 AND a.cve_articulo = b.cve_articulo AND a.origen != a.Cve_Almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $sql .= ' 
                UNION 

                        SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, b.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        " " AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, CONCAT("(", ap.clave, ") - ", ap.nombre) AS origen, CONCAT("(", apd.clave, ") - ", apd.nombre) AS destino, 
                        a.cantidad, a.id_TipoMovimiento, a.cve_usuario, "" AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        "" AS LP
                        FROM t_cardex a
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_almacenp ap  ON ap.id = a.origen
                        LEFT JOIN c_almacenp apd ON apd.id = a.destino
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_articulo b 
                        WHERE a.id_TipoMovimiento = 12 AND a.cve_articulo = b.cve_articulo 
                        AND ap.id = a.origen'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $traslado = getArraySQL($sql);

                for($i = 0; $i < count($traslado); $i++){

                    $sql = 'SELECT DISTINCT IFNULL(b.des_almac, "") as des_almac, IFNULL(c.clave, "") as clave, IFNULL(c.nombre, "") as nombre FROM c_ubicacion a, c_almacen b, c_almacenp c WHERE a.idy_ubica = "'.$traslado[$i]["origen"].'" and a.cve_almac = b.cve_almac and b.cve_almacenp = c.id';

                    $res_o = getArraySQL($sql);
                    if($res_o[0]["clave"])
                    {
                        $traslado[$i]["almacen_clave_o"] = $res_o[0]["clave"];
                        $traslado[$i]["almacen_nombre_o"] = $res_o[0]["nombre"];
                        $traslado[$i]["almacen_descrip_o"] = $res_o[0]["des_almac"];
                    }

                    $sql = 'SELECT DISTINCT IFNULL(b.des_almac, "") as des_almac, IFNULL(c.clave, "") as clave, IFNULL(c.nombre, "") as nombre, IFNULL(a.cve_pasillo, "") as cve_pasillo, IFNULL(a.cve_rack, "") as cve_rack, IFNULL(a.cve_nivel, "") as cve_nivel, IFNULL(a.Seccion, "") as Seccion, (IFNULL(a.Ubicacion, ""))posicion FROM c_ubicacion a, c_almacen b, c_almacenp c WHERE a.idy_ubica = "'.$traslado[$i]["destino"].'" and a.cve_almac = b.cve_almac and b.cve_almacenp = c.id';

                    $res_d = getArraySQL($sql);

                    if($res_d[0]["clave"])
                    {
                        $traslado[$i]["almacen_clave_d"] = $res_d[0]["clave"];
                        $traslado[$i]["almacen_nombre_d"] = $res_d[0]["nombre"];
                        $traslado[$i]["almacen_descrip_d"] = $res_d[0]["des_almac"];
                        $traslado[$i]["cve_pasillo"] = $res_d[0]["cve_pasillo"];
                        $traslado[$i]["cve_rack"] = $res_d[0]["cve_rack"];
                        $traslado[$i]["cve_nivel"] = $res_d[0]["cve_nivel"];
                        $traslado[$i]["Seccion"] = $res_d[0]["Seccion"];
                        $traslado[$i]["posicion"] = $res_d[0]["posicion"];
                    }
                }

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        IFNULL(d.CodigoCSD, "") AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, a.destino, a.cantidad, a.id_TipoMovimiento, 
                        (b.clave)almacen_clave, a.cve_usuario, (b.nombre)almacen_nombre, (e.des_almac)almacen_des, d.cve_pasillo, 
                        d.cve_rack, d.cve_nivel, d.Seccion, (d.Ubicacion)posicion, IFNULL(ch.clave_contenedor, "") AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.CveLP, "") AS LP
                        FROM t_cardex a
                        LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.origen
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.origen 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e 
                        WHERE a.id_TipoMovimiento = 8 AND a.cve_articulo = c.cve_articulo AND 
                        a.Cve_Almac = b.id AND a.cve_articulo = c.cve_articulo AND a.origen = d.idy_ubica AND 
                        d.cve_almac = e.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $salida = getArraySQL($sql);

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        IFNULL(d.CodigoCSD, "") AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.destino, a.cantidad, a.id_TipoMovimiento, 
                        (b.clave)almacen_clave, a.cve_usuario, (b.nombre)almacen_nombre, (e.des_almac)almacen_des, d.cve_pasillo, 
                        d.cve_rack, d.cve_nivel, d.Seccion, (d.Ubicacion)posicion, IFNULL(ch.clave_contenedor, "") AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.CveLP, "") AS LP
                        FROM t_cardex a
                        LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.destino 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e 
                        WHERE a.id_TipoMovimiento = 5 AND a.cve_articulo = c.cve_articulo AND a.Cve_Almac = b.id AND 
                        a.cve_articulo = c.cve_articulo AND a.destino = d.idy_ubica AND 
                        d.cve_almac = e.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $entradaR = getArraySQL($sql);

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        IFNULL(d.CodigoCSD, "") AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, a.cantidad, a.id_TipoMovimiento, 
                        (b.clave)almacen_clave, a.cve_usuario, (b.nombre)almacen_nombre, (e.des_almac)almacen_des, d.cve_pasillo, 
                        d.cve_rack, d.cve_nivel, d.Seccion, (d.Ubicacion)posicion, IFNULL(ch.clave_contenedor, "") AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.CveLP, "") AS LP 
                        FROM t_cardex a
                        LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.destino 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e 
                        WHERE a.id_TipoMovimiento = 4 AND a.cve_articulo = c.cve_articulo AND 
                        a.Cve_Almac = b.id AND a.cve_articulo = c.cve_articulo AND 
                        a.origen = d.idy_ubica AND 
                        d.cve_almac = e.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $salidaR = getArraySQL($sql);

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        IFNULL(d.CodigoCSD, "") AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.destino, a.cantidad, a.id_TipoMovimiento, 
                        (b.clave)almacen_clave, a.cve_usuario, (b.nombre)almacen_nombre, (e.des_almac)almacen_des, 
                        d.cve_pasillo, d.cve_rack, d.cve_nivel, d.Seccion, (d.Ubicacion)posicion, IFNULL(ch.clave_contenedor, "") AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.CveLP, "") AS LP 
                        FROM t_cardex a
                        LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.destino 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e 
                        WHERE a.id_TipoMovimiento = 9 AND a.cve_articulo = c.cve_articulo AND 
                        a.Cve_Almac = b.id AND a.cve_articulo = c.cve_articulo AND 
                        a.destino = d.idy_ubica AND 
                        d.cve_almac = e.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $entradaA = getArraySQL($sql);

                $sql = 'SELECT DISTINCT 
                        (a.cve_articulo)id_articulo, c.des_articulo, a.cve_lote, 
                        IFNULL(IF(lote.Caducidad != "0000-00-00",DATE_FORMAT(lote.Caducidad, "%d-%m-%Y"), ""),"") AS Caducidad, 
                        IFNULL(d.CodigoCSD, "") AS CodigoCSD, 
                        DATE_FORMAT(a.fecha, "%d-%m-%Y %H:%m:%s") AS fecha, a.origen, a.cantidad, a.id_TipoMovimiento, 
                        (b.clave)almacen_clave, a.cve_usuario, (b.nombre)almacen_nombre, (e.des_almac)almacen_des, 
                        d.cve_pasillo, d.cve_rack, d.cve_nivel, d.Seccion, (d.Ubicacion)posicion, IFNULL(ch.clave_contenedor, "") AS contenedor, 
                        IFNULL(mot.Des_Motivo, "") AS Des_Motivo,
                        IFNULL(ch.CveLP, "") AS LP 
                        FROM t_cardex a
                        LEFT JOIN c_ubicacion ub ON ub.idy_ubica = a.destino
                        LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = a.cve_articulo AND ext.lote = a.cve_lote AND ext.idy_ubica = a.destino 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                        LEFT JOIN c_lotes lote ON lote.cve_articulo = a.cve_articulo AND a.cve_lote = lote.Lote
                        LEFT JOIN c_motivo mot ON mot.id = a.Id_Motivo
                        , c_almacenp b, c_articulo c, c_ubicacion d, c_almacen e 
                        WHERE a.id_TipoMovimiento = 10 AND a.cve_articulo = c.cve_articulo AND 
                        a.Cve_Almac = b.id AND a.cve_articulo = c.cve_articulo AND 
                        a.origen = d.idy_ubica AND 
                        d.cve_almac = e.cve_almac'.$sql_almacen.$sql_producto.$sql_lote.$sql_feIn.$sql_feEn;

                $salidaA = getArraySQL($sql);

                $array = [
                    "entrada"=>$entrada,
                    "acomodo"=>$acomodo,
                    "traslado"=>$traslado,
                    "salida"=>$salida,
                    "entradaR"=>$entradaR,
                    "salidaR"=>$salidaR,
                    "entradaA"=>$entradaA,
                    "salidaA"=>$salidaA
                ];

                echo json_encode($array);
            break;
        case 'getListProductos':

                $sql_feIn = "";
                $sql_feEn = "";

                if(isset($_POST['feIn'])){
                    $sql_feIn = " and b.fecha >= '".$_POST['feIn']."'";
                }

                if(isset($_POST['feEn'])){
                    $sql_feEn = " and b.fecha <= '".$_POST['feEn']."'";
                }

                $array = [];

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_proveedores c, td_entalmacen d WHERE a.cve_articulo = b.cve_articulo and b.origen = c.ID_Proveedor and b.destino = d.fol_folio and a.activo = 1 and b.id_TipoMovimiento = 1 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $entrada = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, td_entalmacen c, c_ubicacion d WHERE a.cve_articulo = b.cve_articulo and b.origen = c.fol_folio and b.destino = d.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 2 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $acomodo = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and b.destino = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 20 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $traslado = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 5 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $entradaR = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 4 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $salidaR = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 8 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $salida = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.destino = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 9 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $entradaA = getArraySQL($sql);

                $sql = "SELECT a.cve_articulo, a.des_articulo FROM c_articulo a, t_cardex b, c_ubicacion c WHERE a.cve_articulo = b.cve_articulo and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 10 and b.cve_almac = '".$_POST['idAl']."' ".$sql_feIn.$sql_feEn." GROUP by a.cve_articulo";
                $salidaA = getArraySQL($sql);

                array_push($array, $entrada);
                array_push($array, $acomodo);
                array_push($array, $traslado);
                array_push($array, $entradaR);
                array_push($array, $salidaR);
                array_push($array, $salida);
                array_push($array, $entradaA);
                array_push($array, $salidaA);

                $array = [
                    "articulos"=>$array
                ];

                echo json_encode($array);
            break;
        case 'getListLote':

                $sql_feIn = "";
                $sql_feEn = "";

                if(isset($_POST['feIn'])){
                    $sql_feIn = " and b.fecha >= '".$_POST['feIn']."'";
                }

                if(isset($_POST['feEn'])){
                    $sql_feEn = " and b.fecha <= '".$_POST['feEn']."'";
                }

                $array = [];

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_proveedores c, td_entalmacen d WHERE a.lote = b.cve_lote and b.origen = c.ID_Proveedor and b.destino = d.fol_folio and a.activo = 1 and b.id_TipoMovimiento = 1 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $entrada = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, td_entalmacen c, c_ubicacion d WHERE a.lote = b.cve_lote and b.origen = c.fol_folio and b.destino = d.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 2 and  b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $acomodo = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 20 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $traslado = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 5 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $entradaR = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 4 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $salidaR = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 8 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $salida = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.destino = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 9 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $entradaA = getArraySQL($sql);

                $sql = "SELECT a.lote FROM c_lotes a, t_cardex b, c_ubicacion c WHERE a.lote = b.cve_lote and b.origen = c.idy_ubica and a.activo = 1 and b.id_TipoMovimiento = 10 and b.cve_articulo = '".$_POST['articulo']."' ".$sql_feIn.$sql_feEn." GROUP by a.lote";
                $salidaA = getArraySQL($sql);

                array_push($array, $entrada);
                array_push($array, $acomodo);
                array_push($array, $traslado);
                array_push($array, $entradaR);
                array_push($array, $salidaR);
                array_push($array, $salida);
                array_push($array, $entradaA);
                array_push($array, $salidaA);

                $array = [
                    "lote"=>$array
                ];

                echo json_encode($array);
            break;
    }

}

function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ".$sql;

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}