<?php
include '../../../config.php';

error_reporting(0);

if((isset($_GET) && !empty($_GET)) || (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')){

    if(isset($_GET) && !empty($_GET))
    {
        $page = $_GET['start'];
        $limit = $_GET['length'];
        //$search = $_GET['search']['value'];
        $search = $_GET['search'];

        $articulo = $_GET["articulo"];
        $contenedor = $_GET["contenedor"];
        $almacen = $_GET["almacen"];
        $zona = $_GET["zona"];
        $proveedor = $_GET["proveedor"];
        $cve_proveedor = $_GET["cve_proveedor"];
        $bl = $_GET["bl"];
    }

    if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')
    {
        //$page = $_POST['start'];
        //$limit = $_POST['length'];
        //$search = $_POST['search']['value'];
        $search = $_POST['search'];

        $articulo = $_POST["articulo"];
        $contenedor = $_POST["contenedor"];
        $almacen = $_POST["almacen"];
        $zona = $_POST["zona"];
        $proveedor = $_POST["proveedor"];
        $cve_proveedor = $_POST["cve_proveedor"];
        $bl = $_POST["bl"];
    }


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,"utf8");
  

    $zona_produccion = "";

    if(!empty($zona))
    {
        $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona}' AND AreaProduccion = 'S'";
        $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
        if($query_zona_produccion){
            $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
        }
    }


    $sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];

    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    if($zona_produccion == 'S')
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.idy_ubica IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    $zona_rts = "";
    $zona_rtm_tipo = "ubicacion";
    if($zona == "RTS")
    {
        $zona_rts = " AND IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE id = '".$almacen."') AND tds.Cve_articulo = a.cve_articulo), 0) != 0";
    }
    else if($zona == "RTM")
    {
        $zona_rtm_tipo = "area";
    }

    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";
    //$sqlProveedor = !empty($proveedor) ? "AND x.id_proveedor = '{$proveedor}'" : "";
    $sqlProveedor = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
  
    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";

    $sqlproveedor_tipo = !empty($cve_proveedor) ? "AND x.id_proveedor = {$cve_proveedor}" : "";

    $sqlCount = "SELECT
                    count(e.cve_articulo) as total
                  FROM V_ExistenciaGral e
                    LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                  WHERE e.cve_almac = '{$almacen}' AND e.tipo = 'ubicacion' AND e.Existencia > 0 {$sqlArticulo}  {$sqlZona} {$sqlProveedor} {$sqlbl}";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }

    $sql = "
      SELECT * FROM(
         SELECT DISTINCT 
            '<input class=\"column-asignar\" type=\"checkbox\">' as acciones, 
            ap.clave AS cve_almacen,
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            if(e.Cuarentena = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            ch.CveLP AS LP,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            #COALESCE(if(a.control_lotes ='S',l.LOTE,''), '--') as lote,
            IF(a.control_lotes ='S',IFNULL(l.Lote, ''),'') AS lote,
            COALESCE(if(a.caduca = 'S',if(l.Caducidad = '0000-00-00','',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
            COALESCE(if(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') as nserie,
            e.Existencia as cantidad,
            #(SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            #ent.fol_folio AS folio,
            '' AS folio,
            #COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
            IFNULL(
                (select nombre from c_proveedores where ID_Proveedor = (
                    IFNULL(
                        (select ID_Proveedor from ts_existenciapiezas where ts_existenciapiezas.idy_ubica = e.cve_ubicacion and ts_existenciapiezas.cve_articulo = e.cve_articulo limit 1),
                        IFNULL(
                            (select ID_Proveedor from ts_existenciacajas where ts_existenciacajas.idy_ubica = e.cve_ubicacion and ts_existenciacajas.cve_articulo = e.cve_articulo limit 1),
                            IFNULL(
                                (select ID_Proveedor from ts_existenciatarima where ts_existenciatarima.idy_ubica = e.cve_ubicacion and ts_existenciatarima.cve_articulo = e.cve_articulo limit 1),
                                0
                            )
                        )
                    )
                )),'--'
            )as proveedor,
            #IFNULL(ent.num_orden, '') AS folio_oc,
            '' AS folio_oc,
            COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_peso,
            IFNULL(e.Existencia - IFNULL((SELECT SUM(DISTINCT t.Cantidad) AS pedidas FROM td_subpedido tsb LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo AND t.cve_lote = e.cve_lote), 0) - IFNULL(IF(IFNULL((SELECT COUNT(DISTINCT vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(DISTINCT vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.cve_ubicacion GROUP BY vp.Cve_Articulo)), 0) - IF(a.caduca = 'S', IFNULL((SELECT SUM(DISTINCT vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion), 0), 0), 0) AS Libre,
            IFNULL((SELECT SUM(DISTINCT t.Cantidad) AS pedidas 
                    FROM td_subpedido tsb 
                    LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo 
                    WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo AND t.cve_lote = e.cve_lote), 0) AS RP,
            IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.cve_ubicacion GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            IFNULL(IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion), 0), 0), 0) AS Obsoletos,
            truncate(a.costoPromedio,2) as costoPromedio,
            truncate(a.costoPromedio*e.Existencia,2) as subtotalPromedio
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
                WHERE e.cve_almac = '{$almacen}'  AND e.tipo = '{$zona_rtm_tipo}' AND e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona}
                {$sqlProveedor}
                $zona_rts
            order by l.CADUCIDAD ASC
                )x
            #where x.lote != '--'
            WHERE 1 
            {$sqlbl}
            {$sqlproveedor_tipo}";



    if($zona_produccion == 'S')
    {
        $sql = "SELECT DISTINCT * FROM(
         SELECT DISTINCT
         '<input class=\"column-asignar\" type=\"checkbox\">' as acciones, 
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD AS codigo,
            e.idy_ubica AS cve_ubicacion,
            '' AS IDContenedor,
            IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = '{$almacen}' AND tds.Cve_articulo = a.cve_articulo), 0) AS RTS,
            IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            '' as contenedor,
            '' AS LP,
            a.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            '' AS tipo,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            COALESCE(IF(a.control_lotes ='S',l.LOTE,''), '--') AS lote,
            COALESCE(IF(IFNULL(a.caduca, 'N') = 'S',IF(l.Caducidad = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            e.Existencia AS cantidad,
            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            e.ID_Proveedor AS id_proveedor,
            pv.Nombre AS proveedor,
            #IFNULL(ent.num_orden, '') AS folio_oc,
            '' AS folio_oc,
            IF(IFNULL(a.caduca, 'N') = 'S', COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--'), DATE_FORMAT(l.Caducidad,'%d-%m-%Y')) AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_peso,
            IFNULL(e.Existencia - IFNULL((SELECT SUM(tsb.Num_cantidad) AS pedidas FROM td_subpedido tsb LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo), 0) - IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.idy_ubica GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.idy_ubica GROUP BY vp.Cve_Articulo)), 0) - IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.idy_ubica), 0), 0), 0) AS Libre,
            IFNULL((SELECT SUM(tsb.Num_cantidad) AS pedidas FROM td_subpedido tsb LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo), 0) AS RP,
            #IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.idy_ubica GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 GROUP BY vp.cve_articulo)), 0) AS Prod_QA,
            IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.idy_ubica GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.idy_ubica GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            IFNULL(IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.idy_ubica), 0), 0), 0) AS Obsoletos,
            TRUNCATE(a.costoPromedio,2) AS costoPromedio,
            TRUNCATE(a.costoPromedio*e.Existencia,2) AS subtotalPromedio
            FROM
                ts_existenciapiezas e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_proveedores pv ON pv.ID_Proveedor = e.ID_Proveedor
            #LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote
            WHERE e.cve_almac = '{$almacen}'   AND e.Existencia > 0 {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlProveedor}
            ORDER BY l.CADUCIDAD ASC
                )x
           # WHERE X.lote != '--' 
            WHERE 1 
            {$sqlbl}";
    }


    $l = " LIMIT $page,$limit; ";

    //if($search) $l = "";
    
    $sql .= $l;
/*
            CONCAT(
                CASE
                    WHEN u.Tipo = 'L' THEN 'Libre'
                    WHEN u.Tipo = 'R' THEN 'Reservada'
                    WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                END, '| Picking ',
                CASE 
                    WHEN u.Picking = 'S' THEN '<i class=\"fa fa-check text-success\"></i>'
                    WHEN u.Picking = 'N' THEN '<i class=\"fa fa-times text-danger\"></i>'
                END
            ) AS tipo_ubicacion,
*/
  
    //echo var_dump($sql);
    //die();
  
    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
    $data = array();
    $i = 0; $suma = 0;
    $productos = array();
    $productos_i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
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
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['cantidad']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['cantidad'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['cantidad']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1) 
        {
            $valor2 = 0; 
            $row['Caja'] = $cantidad_restante;
            $row['Piezas'] = 0;
        }
        else if($row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "")
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $row['Caja'] = 0;
            $row['Piezas'] = $cantidad_restante;
        }

        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
           //$row['Piezas'] = ($row['Caja']*$cantidad_restante) - $row['piezasxcajas'];
            $row['Piezas'] = $cantidad_restante;
        }
        //**************************************************

        if($row["control_peso"] == 'S')
        {
            $row["RP"] = number_format($row["RP"], 4);
            $row["Libre"] = number_format($row["Libre"], 4);
        }
        else 
        {
            $row["RP"] = number_format($row["RP"], 0);
            $row["Libre"] = number_format($row["Libre"], 0);
        }


        $data[] = $row;
        //$suma += $row['Libre']+$row['RP'];
        $suma += $row['cantidad'];
        $i++;

        if(!in_array($row['clave'], $productos)) 
        {
            $productos_i++;
            array_push($productos, $row['clave']);
        }

    }

    $draw = "";
    if(isset($_GET) && !empty($_GET))
    {
        $draw = $_GET["draw"];
    }
    if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')
    {
        $draw = $_POST["draw"];
    }

    mysqli_close();
    header('Content-type: application/json');
    $output = array(
        "draw" => $draw,
        "recordsTotal" => $i,
        "recordsFiltered" => $i,
        "data" => $data,
        "sql" => $sql,
        "productos" => $productos_i,
        "unidades" => $suma,
        "bl" => $responce
    );
    echo json_encode($output);exit;
}

if($_POST['action'] == "savemotivo")
{
    $procesos = "";
    $motivos = $_POST['motivos'];
    $registros = $_POST['registros'];
    $almacen = $_POST['almacen'];
    $fecha = $_POST['fecha'];
    $id_usuario = $_POST['id_usuario'];

		if($registros[0][1] != "")
   	{
				$sql1 ="
						SELECT  
								c_charolas.IDContenedor AS id
						FROM c_charolas 
						WHERE clave_contenedor = '{$registros[0][1]}'";
						$res = getArraySQL($sql1);
						$id = $res[0]["id"];

                $procesos .= $sql1;
  	 }
  
    $sql1 ="
				SELECT  
						c_ubicacion.idy_ubica AS idy_ubica
				FROM c_ubicacion 
				WHERE CodigoCSD = '{$registros[0][0]}'";
				$res = getArraySQL($sql1);
				$idy_ubica = $res[0]["idy_ubica"];

                $procesos .= $sql1;
	
    $sql1 ="
          SELECT
            count(Fol_Folio) as conteo
          FROM t_movcuarentena";
          $res = getArraySQL($sql1);
          $res[0]["conteo"] ++;
          $num = str_pad($res[0]["conteo"],3,0,STR_PAD_LEFT);
          $nume = $fecha.$num;
          $folio =  'QA'.$nume;

          $procesos .= $sql1;
  
    //asignados.push([bl,contenedor,cve_articulo,lote,serie,cantidad]);
    foreach($registros as $registro)
    {
        $lote = $registro[3];
        if($lote== ""){$lote = $registro[4];}
        if($registro[1] == "Si")
        {
          $sql2 ="
              UPDATE ts_existenciatarima
              SET Cuarentena = 1
              where cve_almac = '{$almacen}'
                and cve_articulo = '{$registro[2]}'
                and lote = '{$lote}'
                and existencia = '{$registro[5]}'";
                $res = getArraySQL($sql2);

                $procesos .= $sql2;
        }
        else  
        {
          $sql2 ="
              UPDATE ts_existenciapiezas
              SET Cuarentena = 1
              where cve_almac = '{$almacen}'
                and cve_articulo = '{$registro[2]}'
                and cve_lote = '{$lote}'
                and Existencia = '{$registro[5]}'";
                $res = getArraySQL($sql2);

                $procesos .= $sql2;
        }
    
        $sql ="
            INSERT INTO t_movcuarentena 
        (Fol_Folio, Idy_Ubica, IdContenedor, Cve_Articulo, Cve_Lote, Cantidad, PzsXCaja, Fec_Ingreso, Id_MotivoIng, Tipo_Cat_Ing, Usuario_Ing) 
            VALUES ('$folio', '$idy_ubica', '$id', '$registro[2]', '$lote', '$registro[6]', '', NOW(), '$motivos', 'Q', '$id_usuario')";

        $procesos .= $sql;

        $res = getArraySQL($sql);
        $result = array(
        "success" => true,
            "sql" => $res,
           "sql2" => $res,
       "procesos" => $procesos
         );
    }
   echo json_encode($result);exit;
}

if($_POST['action'] == "traermotivos")
{
    $status = 'Q';
    if(isset($_POST['status']))
        $status = $_POST['status'];

    $sql = "
     SELECT
          c_motivo.id,
          c_motivo.Tipo_Cat,
          c_motivo.Des_Motivo as descri
     FROM c_motivo
     WHERE c_motivo.Tipo_Cat = '".$status."' AND Activo = 1
   ";
   $res = getArraySQL($sql);
   $result = array(
     "sql" => $res
   );
  
   echo json_encode($result);exit;
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === "exportExcelExistenciaUbica"){

    $almacen = $_POST['almacen'];
    $zona = $_POST['zona'];
    $articulo = $_POST['articulo'];

    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de existencia por ubicación.xlsx";

    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '$zona')" : "";
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '$articulo'" : "";
    $sql = "
        SELECT
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            COALESCE(l.LOTE, '--') as lote,
            COALESCE(l.CADUCIDAD, '--') as caducidad,
            COALESCE(s.numero_serie, '--') as nserie,
            e.Existencia as cantidad,
            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
            CONCAT(CASE
                        WHEN u.Tipo = 'L' THEN 'Libre'
                        WHEN u.Tipo = 'R' THEN 'Reservada'
                        WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                    END, '| Picking ',
                    CASE 
                        WHEN u.Picking = 'S' THEN '✓'
                        WHEN u.Picking = 'N' THEN '✕'
                    END
             ) AS tipo_ubicacion,
             a.costoPromedio as costoPromedio,
             a.costoPromedio*e.Existencia as subtotalPromedio,
            (SELECT SUM(a.costoPromedio*e.Existencia) FROM V_ExistenciaGralProduccion e LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo) as importeTotalPromedio,
            (SELECT BL FROM c_almacenp WHERE id = '$almacen' LIMIT 1) AS codigo_BL
      FROM V_ExistenciaGralProduccion e
          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
          LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
          LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
          LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
          LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
          LEFT JOIN c_serie s ON s.cve_articulo = e.cve_articulo
      WHERE e.cve_almac = '$almacen' AND e.tipo = 'ubicacion' AND e.Existencia > 0  {$sqlArticulo}  {$sqlZona}
";
  
    //echo var_dump($sql);
    //die();
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
  
    $bl = 0;
    foreach($data as $d)
    {
       $bl = $d['codigo_BL'];
    }
  
    $bl1 = $bl;

    $header = array(
        'Almacén',
        'Zona de Almacenaje',
        'Codigo BL'." ".$bl1.'',
        'Clave',
        'Descripción',
        'Lote',
        'Caducidad',
        'N. Serie',
        'Cantidad',
        'Proveedor',
        'Fecha de Ingreso',
        'Tipo de Ubicación',
        'Costo Promedio',
        'Subtotal',
        'Importe'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header );

    $sum = 0;
    foreach($data as $d)
    {
       $sum+= $d['subtotalPromedio'];
    }
  
    $sum1 = $sum;
  
    foreach($data as $d){
      
        $row = array(
            $d['almacen'],
            $d['zona'],
            $d['codigo'],
            $d['clave'],
            $d['descripcion'],
            $d['lote'],
            $d['caducidad'],
            $d['nserie'],
            $d['cantidad'],
            $d['proveedor'],
            $d['fecha_ingreso'],
            $d['tipo_ubicacion'],
            $d['costoPromedio'],
            $d['subtotalPromedio'],
            $sum1
          
        );
        $sum1 = "";
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}

function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

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

if( $_POST['action'] == 'traer_BL' ) 
{
    $almacen = $_POST["almacen"];
    $responce = "";
    $sql = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'" and Activo = 1';
    $result = getArraySQL($sql);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];
 
    echo json_encode($responce["bl"]);
}

/*
if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')
{
    $almacen = $_POST["almacen"];
    $articulo = $_POST["articulo"];
    $contenedor = $_POST["contenedor"];
    $zona = $_POST["zona"];
    $bl = $_POST["bl"];
    
    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";
  
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";
  
    $sqlbl = !empty($bl) ? "AND u.codigoCSD like '%{$bl}%'" : "";
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
     
    $sqlTotales ="
        SELECT
            count(distinct(e.cve_articulo)) as productos,
            truncate(sum(e.Existencia),4) as unidades
        FROM V_ExistenciaGralProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
		    WHERE e.tipo = 'ubicacion' 
            AND e.Existencia > 0 
            AND e.cve_almac = '".$almacen."'
            {$sqlZona}
            {$sqlArticulo}
            {$sqlContenedor}
            {$sqlbl}
    ";
    
    if (!($res = mysqli_query($conn, $sqlTotales))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  
    //echo var_dump($sqlTotales);
    
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();
    header('Content-type: application/json');
    $output = array("data" => $data); 
    echo json_encode($output);
}
*/