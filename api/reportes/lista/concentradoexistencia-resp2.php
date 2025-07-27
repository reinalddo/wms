<?php
include '../../../config.php';

error_reporting(0);
 
if(isset($_GET) && !empty($_GET)){

    $page = "";
    if(isset($_GET['start']))
    {
        if($_GET['start'])
        {
            $page = $_GET['start'];
        }
    }
    $limit = "";
    if(isset($_GET['length']))
    {
        if($_GET['length'])
        {
            $limit = $_GET['length']; 
        }
    }
    $search = "";
    if(isset($_GET['search']['value']))
    {
        if($_GET['search']['value'])
        {
            $search = $_GET['search']['value'];
        }
    }


    $almacen = $_GET['almacen'];

    $filtro_where_concentrado = "WHERE 1 ";
    if(isset($_GET['filtro_concentrado']))
    {
        if($_GET['filtro_concentrado'])
            $filtro_where_concentrado = $_GET['filtro_concentrado'];
    }
    else if(isset($_POST['filtro_concentrado']))
    {
        if($_POST['filtro_concentrado'])
            $filtro_where_concentrado = $_POST['filtro_concentrado'];
    }
 
    $ands = ""; $ands2 = "";
    if (!empty($search)){
        $ands.=" and a.cve_articulo like '%".$search."%' ";
        $ands2.=" and ar.cve_articulo like '%".$search."%' ";
    }
    
    //$sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    //$result = getArraySQL($sql1);  
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql = "SELECT SUM(Existencia) AS cantidad, COUNT(DISTINCT cve_articulo) AS total FROM V_ExistenciaGralProduccion WHERE Existencia > 0;";


    $filtro_clientes = "";
    if(isset($_GET['cve_proveedor']))
    {
      if($_GET['cve_proveedor'])
      {
          $proveedor = $_GET['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $proveedor = $_POST['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        WHERE adz.Cve_Almac = '{$almacen}') AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    $sql = "";
    if($almacen != '')
    {
    $sql = "
    SELECT * FROM (
    SELECT  DISTINCT
            ad.num_orden AS folio,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            IFNULL((SELECT SUM(td.cantidad) FROM td_aduana td, th_aduana th WHERE th.num_pedimento = td.num_orden AND th.Cve_Almac = '{$almacen}' AND td.cve_articulo = a.cve_articulo AND th.status = 'C'), 0) AS Prod_OC,
            IFNULL((SELECT SUM(ve.Existencia) FROM V_ExistenciaGralProduccion ve WHERE ve.cve_articulo = a.cve_articulo AND ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  AND ve.tipo = 'area'), 0) AS Prod_RTM, 
            #AND ve.cve_lote = ad.cve_lote
            IFNULL((SELECT SUM(Cantidad) FROM t_recorrido_surtido WHERE Cve_articulo = a.cve_articulo), 0) AS Res_Pick,
            IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0), 0) AS Obsoletos,
            IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND tds.Cve_articulo = a.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS Prod_kit, 

            #(SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
            #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
            #AND e.tipo = 'ubicacion' AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            #) AS existencia

            #(SELECT IFNULL(SUM(e.Existencia), 0) FROM ts_existenciapiezas e 
            #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
            #AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            #) AS existencia

        #IFNULL((SELECT SUM(ex.Existencia) FROM (
        #SELECT DISTINCT e.cve_almac, e.cve_ubicacion, e.cve_articulo, e.cve_lote, e.Existencia, a.des_articulo 
        #FROM V_ExistenciaGral e
        #LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
        #LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
        #LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
        #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
        #LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
        #LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
        #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND e.Existencia > 0 AND e.tipo = 'ubicacion' AND e.cve_lote != ''
        #) AS ex WHERE ex.cve_articulo = a.cve_articulo), 
        #(SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
        #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
        #AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion'
        #)) AS existencia
        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
        WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '100') 
        AND e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion'
        ) AS existencia

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo
            LEFT JOIN c_almacenp alm ON alm.clave = '{$almacen}'
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        $ands
        GROUP BY a.cve_articulo, p.ID_Proveedor
#v.Cve_Almac = '{$almacen}' AND
        UNION

        SELECT  
            '' AS folio,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            0 AS Prod_OC,
            0 AS Prod_RTM,
            0 AS Res_Pick,
            0 AS Prod_QA,
            IF(ar.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = ar.cve_articulo AND ar.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0), 0) AS Obsoletos,
            IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND tds.Cve_articulo = ar.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            art.Cantidad_Producida AS Prod_kit,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE c_almacenp.clave = '{$almacen}' $sql_in 
            $ands2
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado {$filtro_where_concentrado} #AND concentrado.existencia > 0
        ";
    }
    else
    {
        $sql = "
        SELECT  
            ad.num_orden AS folio,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            IFNULL((SELECT SUM(td.cantidad) FROM td_aduana td, th_aduana th WHERE th.num_pedimento = td.num_orden AND  td.cve_articulo = a.cve_articulo AND th.status = 'C'), 0) AS Prod_OC,
            IFNULL((SELECT SUM(ve.Existencia) FROM V_ExistenciaGralProduccion ve WHERE ve.cve_articulo = a.cve_articulo AND ve.cve_lote = ad.cve_lote AND ve.tipo = 'area'), 0) AS Prod_RTM,
            IFNULL((SELECT SUM(Cantidad) FROM t_recorrido_surtido WHERE Cve_articulo = a.cve_articulo), 0) AS Res_Pick,
            IFNULL((SELECT SUM(Cantidad) FROM t_movcuarentena WHERE cve_articulo = a.cve_articulo), 0) AS Prod_QA,
            IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0) AS Obsoletos,
            IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.Cve_articulo = a.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS Prod_kit, 
            (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
            WHERE e.tipo = 'ubicacion' AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            ) AS existencia
        FROM c_articulo a
        LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
        LEFT JOIN th_aduana v  ON v.Cve_Almac != '100ABCDEFG_JK'
        LEFT JOIN c_almacenp alm ON alm.clave = v.Cve_Almac
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = v.ID_Proveedor
        WHERE a.cve_articulo != '' AND a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion)
        $ands
         
        GROUP BY a.cve_articulo

        UNION

        SELECT  
            '' AS folio,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' AS id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            0 AS Prod_OC,
            0 AS Prod_RTM,
            0 AS Res_Pick,
            0 AS Prod_QA,
            IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = ar.cve_articulo AND ar.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0) AS Obsoletos,
            IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.Cve_articulo = ar.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            art.Cantidad_Producida AS Prod_kit,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        ) AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)
        $ands2
         
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen        
        
    ";
    }
/*
1 #v.Existencia > 0
            #AND v.`tipo`='ubicacion'
            AND


1 
            AND #v.Existencia > 0
            #AND v.`tipo`='ubicacion'
*/
    if($page != '' && $limit != '')
        $sql.=' LIMIT {$page}, {$limit};';

    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "))."--- ".$sql;}
  
    $data = array();
    $i = 0;
    $num_unidades = 0;
    while ($row = mysqli_fetch_array($res)) {

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
           $valor1 = $row['existencia']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['existencia'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['existencia']);
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

        //**************************************************
        //Existencia no debe considerar productos en área de Kitting
        //**************************************************
        //if($row['existencia'])
        //$row['existencia'] -= $row['Prod_kit'];
        //**************************************************

        $num_unidades += $row['existencia'];
        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  

    $sql = "
      SELECT * FROM(
         SELECT DISTINCT 
            COUNT(DISTINCT e.cve_articulo) AS total,
            SUM(e.Existencia) AS cantidad
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
                WHERE ap.clave = '{$almacen}'  AND e.tipo = 'ubicacion' AND e.Existencia > 0  
                )x
            #where x.lote != '--'
            WHERE 1 ";
//{$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlbl_search} {$sqlProveedor} $zona_rts {$sqlproveedor_tipo}

    $query = mysqli_query($conn, $sql);   
    $row = mysqli_fetch_array($query);
    $cantidad = $row["cantidad"];
    //$count = $row[1];


    mysqli_close();
    header('Content-type: application/json');
    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $data,
        "productos" => $i,
        "unidades" => $cantidad,
        "cantidadTotal" => $cantidad
    ); 
    echo json_encode($output);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'concentrado_pdf')
{
    $almacen = $_POST['almacen'];
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "
        SELECT  
            (SELECT 
                fol_folio 
            FROM td_entalmacen 
            WHERE cve_articulo = v.cve_articulo 
                AND cve_lote = v.cve_lote 
            LIMIT 1) AS folio,
            IFNULL(
                (SELECT 
                    Nombre 
                 FROM c_proveedores 
                 WHERE ID_Proveedor = (
                    SELECT 
                        ID_Proveedor 
                    FROM th_aduana 
                    WHERE num_pedimento = (SELECT folio))),'') AS proveedor,
            v.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            SUM(v.Existencia) AS existencia
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = a.ID_Proveedor
            LEFT JOIN c_almacenp on c_almacenp.id = v.cve_almac
        WHERE v.Existencia > 0
            AND v.`tipo`='ubicacion'
            AND c_almacenp.clave = '{$almacen}'
        GROUP BY v.cve_articulo
    ";

    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {

        $valor1 = 0;
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['existencia']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['existencia'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['existencia']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1 || $row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "") 
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $valor2 = 0; 
            $row['Caja'] = $cantidad_restante;
            $row['Piezas'] = 0;
        }
        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
            $row['Piezas'] = $cantidad_restante;
        }

        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();
    header('Content-type: application/json');
    $output = array(
        "productos" => $data,
    ); 
    echo json_encode($output);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'concentrado_excel')
{
    $almacen = $_POST['almacen'];
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "
        SELECT  
            (SELECT 
                fol_folio 
            FROM td_entalmacen 
            WHERE cve_articulo = v.cve_articulo 
                AND cve_lote = v.cve_lote 
            LIMIT 1) AS folio,
            IFNULL(
                (SELECT 
                    Nombre 
                 FROM c_proveedores 
                 WHERE ID_Proveedor = (
                    SELECT 
                        ID_Proveedor 
                    FROM th_aduana 
                    WHERE num_pedimento = (SELECT folio))),'') AS proveedor,
            v.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            SUM(v.Existencia) AS existencia
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = a.ID_Proveedor
            LEFT JOIN c_almacenp on c_almacenp.id = v.cve_almac
        WHERE v.Existencia > 0
            AND v.`tipo`='ubicacion'
            AND c_almacenp.clave = '{$almacen}'
        GROUP BY v.cve_articulo
    ";

    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {

        $valor1 = 0;
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['existencia']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['existencia'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['existencia']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1 || $row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "") 
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $valor2 = 0; 
            $row['Caja'] = $cantidad_restante;
            $row['Piezas'] = 0;
        }
        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
            $row['Piezas'] = $cantidad_restante;
        }

        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();

    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de Concentrado de existencias.xls";

    $header = array(
        'Proveedor',
        'Clave',
        'Descripción',
        'Pallet',
        'Caja',
        'Piezas',
        'Existencia'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header );

    $sum = 0;
    foreach($data as $d)
    {
       $sum+= $d['existencia'];
    }
  
    $sum1 = $sum;
  
    foreach($data as $d){
      
        $row = array(
            $d['proveedor'],
            $d['clave'],
            $d['nombre'],
            $d['Pallet'],
            $d['Caja'],
            $d['Piezas'],
            $d['existencia'],
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

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')
{
    $almacen = $_POST['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
     
    $sqlTotales ="
        SELECT  
            (SELECT COUNT(*) FROM c_articulo WHERE cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')) AS productos,
            IFNULL(TRUNCATE(SUM(v.Existencia),2), 0) AS unidades
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
        WHERE v.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}');

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

?>
