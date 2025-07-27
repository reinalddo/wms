<?php
include '../../../config.php';

error_reporting(0);
 
if(isset($_GET) && !empty($_GET)){


    $search = "";
    if(isset($_GET['search']['value']))
    {
        if($_GET['search']['value'])
        {
            $search = $_GET['search']['value'];
        }
    }


    $almacen = $_GET['almacen'];

    $filtro_where_concentrado = "";
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


    $check_sin_almacen = 0;
    if(isset($_GET['check_almacen']))
    {
        $check_sin_almacen = $_GET['check_almacen'];
    }
    else if(isset($_POST['check_almacen']))
    {
        $check_sin_almacen = $_POST['check_almacen'];
    }

    if($check_sin_almacen == 0)
    {
        if($filtro_where_concentrado)
            $filtro_where_concentrado .= " AND concentrado.clave_alm = '{$almacen}' ";
        else
            $filtro_where_concentrado = " WHERE 1 AND concentrado.clave_alm = '{$almacen}' ";    
    }
    else
    {
        if($filtro_where_concentrado)
            $filtro_where_concentrado .= " ";
        else
            $filtro_where_concentrado = " WHERE 1 ";
    }

    $ands = ""; $ands2 = "";
    if (!empty($search)){
        $ands.=" and a.cve_articulo like '%".$search."%' ";
        $ands2.=" and ar.cve_articulo like '%".$search."%' ";
    }
    
    //$sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    //$result = getArraySQL($sql1);  
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    //if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$charset = mysqli_fetch_array($res_charset)['charset'];
    //mysqli_set_charset($conn , $charset);
    $sql_charset = "SET NAMES 'utf8mb4';";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$sql = "SELECT SUM(Existencia) AS cantidad, COUNT(DISTINCT cve_articulo) AS total FROM V_ExistenciaGralProduccion WHERE Existencia > 0;";

    $page  = $_GET['page']; // get the requested page
    $lim = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx  = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord  = $_GET['sord']; // get the direction

    //$almacen = $_GET["almacen"];
    $articulo = $_GET["articulo"];
    $articulo2 = $_GET["articulo2"];
    $proveedor = $_GET["proveedor"];
    $grupo = $_GET["grupo"];
//echo "OKD";
//return;


    if($check_sin_almacen == 1)
        $articulo = $articulo2;

    $sqlArticulo1 = !empty($articulo) ? " AND a.cve_articulo = '{$articulo}' " : "";
    $sqlArticulo2 = !empty($articulo) ? " AND ar.cve_articulo = '{$articulo}' " : "";

    $sqlProveedor = !empty($proveedor) ? " AND p.ID_Proveedor = '{$proveedor}' " : "";

    $sqlGrupo = !empty($grupo) ? " AND g.id = '{$grupo}' " : "";

    $ands = $sqlArticulo1.$sqlProveedor.$sqlGrupo;
    $ands2 = $sqlArticulo2.$sqlProveedor.$sqlGrupo;

    $lim = $lim+0;//hay que convertirlo a entero
    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$lim;//


    $sql_proveedor2 = "";

    $filtro_clientes = "";
    if(isset($_GET['cve_proveedor']))
    {
      if($_GET['cve_proveedor'])
      {
          $proveedor = $_GET['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
          $sql_proveedor2 = " AND e.ID_Proveedor = {$proveedor} ";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $proveedor = $_POST['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";

          $sql_proveedor2 = " AND e.ID_Proveedor = {$proveedor} ";
      }
    }

    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        WHERE adz.Cve_Almac = '{$almacen}') AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    if($check_sin_almacen == 1)
    {
        $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
                z.cve_articulo
            FROM td_aduana z
                LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
            ) AND ar.cve_articulo NOT IN (SELECT 
            cve_articulo FROM V_ExistenciaGralProduccion)";    
    }

    $sql = "";

    if($almacen != '')
    {
    /*
    $sql = "
    SELECT *, SUM(concentrado.existencia_conc) AS existencia FROM (
    SELECT  DISTINCT
            #ad.num_orden AS folio,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            IFNULL(g.des_gpoart, '') AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') as nombre,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,

        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
        WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') 
        AND e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.Id_Proveedor = p.ID_Proveedor
        ) AS existencia_conc

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            #LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            #LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo AND vg.tipo = 'ubicacion' 
            LEFT JOIN c_almacenp alm ON alm.clave = '{$almacen}' 
            #LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo 
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor
        $ands
        AND alm.clave = '{$almacen}' AND vg.Existencia > 0 AND vg.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')
        GROUP BY a.cve_articulo, p.ID_Proveedor
#v.Cve_Almac = '{$almacen}' AND
        UNION

        SELECT  
            #'' AS folio,
            c_almacenp.clave AS clave_alm,
            c_almacenp.nombre AS Nombre_Almacen,
            IFNULL(g.des_gpoart, '') AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            0 AS existencia_conc
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE c_almacenp.clave = '{$almacen}' $sql_in 
            $ands2
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado {$filtro_where_concentrado} {$filtro_clientes} 
        GROUP BY concentrado.articulo";

        if($check_sin_almacen == 1)
            $sql = "
            SELECT *, SUM(concentrado.existencia_conc) AS existencia FROM (
            SELECT  DISTINCT
                    #ad.num_orden AS folio,
                    alm.clave AS clave_alm,
                    alm.nombre AS Nombre_Almacen,
                    IFNULL(g.des_gpoart, '') AS grupo,
                    p.ID_Proveedor AS id_proveedor,
                    p.Nombre AS proveedor,
                    a.cve_articulo AS articulo,
                    IFNULL(a.des_articulo, '--') as nombre,
                    IFNULL(a.cajas_palet, 0) AS cajasxpallets,
                    IFNULL(a.num_multiplo, 0) AS piezasxcajas,
                    0 AS Pallet,
                    0 AS Caja, 
                    0 AS Piezas,

                (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
                WHERE e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.Id_Proveedor = p.ID_Proveedor
                ) AS existencia_conc

                FROM c_articulo a
                    LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
                    #LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
                    #LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
                    LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo AND vg.tipo = 'ubicacion' 
                    LEFT JOIN c_almacenp alm ON alm.id = vg.cve_almac 
                    #LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo 
                    LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
                WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor
                $ands
                AND vg.Existencia > 0 
                GROUP BY a.cve_articulo, p.ID_Proveedor
        #v.Cve_Almac = '{$almacen}' AND
                UNION

                SELECT  
                    #'' AS folio,
                    c_almacenp.clave AS clave_alm,
                    c_almacenp.nombre AS Nombre_Almacen,
                    IFNULL(g.des_gpoart, '') AS grupo,
                    '' as id_proveedor,
                    '' AS proveedor,
                    ar.cve_articulo AS articulo,
                    IFNULL(ar.des_articulo, '--') AS nombre,
                    IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
                    IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
                    0 AS Pallet,
                    0 AS Caja, 
                    0 AS Piezas,
                    0 AS existencia_conc
                FROM c_articulo ar
                    LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
                    LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
                    LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
                    LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
                WHERE 1 $sql_in 
                    $ands2
                GROUP BY ar.cve_articulo
                ORDER BY Nombre_Almacen
                ) AS concentrado {$filtro_where_concentrado} {$filtro_clientes} 
                GROUP BY concentrado.articulo";
    */

        $sqlAlmacen = " AND alm.id = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') ";
        if($check_sin_almacen == 1)
          $sqlAlmacen = "";

        $sql = "
        SELECT * FROM (
        SELECT 
            a.id AS id_articulo,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            IFNULL(g.des_gpoart, '') AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS cve_articulo,
            IFNULL(a.des_articulo, '--') AS articulo,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,

        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
        WHERE e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.cve_almac = alm.id
        ) AS existencia

        FROM c_articulo a
        LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
        LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo
        LEFT JOIN c_almacenp alm ON alm.id = ra.Cve_Almac AND alm.Activo = 1
        LEFT JOIN V_ExistenciaGralProduccion v ON v.cve_articulo = a.cve_articulo AND v.cve_almac = ra.Cve_Almac AND v.tipo = 'ubicacion'
        LEFT JOIN c_proveedores p ON p.ID_Proveedor = v.Id_Proveedor
        WHERE alm.clave IS NOT NULL {$sqlAlmacen} {$ands} 
        GROUP BY clave_alm, cve_articulo
        ) AS concentrado {$filtro_where_concentrado} {$filtro_clientes} 
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
            #IFNULL((SELECT SUM(td.cantidad) FROM td_aduana td, th_aduana th WHERE th.num_pedimento = td.num_orden AND  td.cve_articulo = a.cve_articulo AND th.status = 'C'), 0) AS Prod_OC,
            #IFNULL((SELECT SUM(ve.Existencia) FROM V_ExistenciaGralProduccion ve WHERE ve.cve_articulo = a.cve_articulo AND ve.cve_lote = ad.cve_lote AND ve.tipo = 'area'), 0) AS Prod_RTM,
            #IFNULL((SELECT SUM(Cantidad) FROM t_recorrido_surtido WHERE Cve_articulo = a.cve_articulo), 0) AS Res_Pick,
            #IFNULL((SELECT SUM(Cantidad) FROM t_movcuarentena WHERE cve_articulo = a.cve_articulo), 0) AS Prod_QA,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.Cve_articulo = a.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS Prod_kit, 
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
            #0 AS Prod_OC,
            #0 AS Prod_RTM,
            #0 AS Res_Pick,
            #0 AS Prod_QA,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = ar.cve_articulo AND ar.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.Cve_articulo = ar.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #art.Cantidad_Producida AS Prod_kit,
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
$sql_consulta = $sql;

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición 1: (" . mysqli_error($conn) . ") "))."--- ".$sql;
    }
    $count = mysqli_num_rows($res);

    $res = "";
    if(!isset($_GET['boton_pdf']))
    $sql.= " LIMIT ".$_page.",".$lim."";

    //$sql2 = $sql;
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición 2: (" . mysqli_error($conn) . ") "))."---".$lim."--- ".$sql;
    }

    $data = array();
    $responce = array();
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
        /*
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
        */
        //**************************************************

        //**************************************************
        //Existencia no debe considerar productos en área de Kitting
        //**************************************************
        //if($row['existencia'])
        //$row['existencia'] -= $row['Prod_kit'];
        //**************************************************

            $responce["rows"][$i]['id']=$row['articulo'];
            $responce["rows"][$i]['cell']=array(
                ($row['cve_articulo']),
                ($row['grupo']),
                ($row['articulo']),
                $row['existencia'],
                $row['Pallet'],
                $row['Caja'],
                $row['Piezas'],
                '', //$row['Prod_OC'],
                '', //$row['Prod_RTM'],
                '', //$row['Res_Pick'],
                '', //$row['Prod_QA'],
                '', //$row['Obsoletos'],
                '', //$row['RTS'],
                '', //$row['Prod_kit'],
                //utf8_decode($row['proveedor']),
                ($row['Nombre_Almacen'])
              );

        //$num_unidades += $row['existencia'];
        //$row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }

    $draw = $_GET["draw"];
    $sql = "
      SELECT * FROM(
         SELECT DISTINCT 
            COUNT(DISTINCT e.cve_articulo) AS total,
            SUM(e.Existencia) AS cantidad
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor
                WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  AND e.tipo = 'ubicacion' AND e.Existencia > 0  
                $ands 
                {$sql_proveedor2}
                )x
            #where x.lote != '--'
            WHERE 1 ";
//{$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlbl_search} {$sqlProveedor} $zona_rts {$sqlproveedor_tipo}

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición 2: (" . mysqli_error($conn) . ") "))."---".$sql;
    }

    $row = mysqli_fetch_array($res);
    $cantidad = $row["cantidad"];
    //$count = $row[1];

    $cantidad = number_format($cantidad, 2);

    mysqli_close();

    if( $count >0 ) {
        $total_pages = ceil($count/$lim);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    if(isset($_GET['boton_pdf']))
    {
/*
        if (!($res = mysqli_query($conn, $sql_consulta)))
        {
            echo json_encode(array( "error" => "Error al procesar la petición PDF: (" . mysqli_error($conn) . ") "))."---".$sql_consulta;
        }

        $data = array();
        //$data = mysqli_fetch_array($res);

        while ($row = mysqli_fetch_array($res)) {

            $data[] = $row;
        }
*/
        header('Content-type: application/json');
        $output = array(
            "draw" => $_GET["draw"],
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "data" => $data,
            "productos" => $i,
            "unidades" => $cantidad,
            "sql_consulta" => $sql_consulta,
            "cantidadTotal" => $cantidad
        ); 
        echo json_encode($output);
    }
    else
    {
        $responce["page"] = $page;
        $responce["total"] = $total_pages;
        $responce["records"] = $count;
        $responce["productos"] = $count;
        $responce["unidades"] = $cantidad;
        $responce["cantidadTotal"] = $cantidad;
        //$responce["data"] = $data;
        $responce["sql_consulta"] = $sql_consulta;
        $responce["sql"] = $sql;
        echo json_encode($responce);
    }
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
