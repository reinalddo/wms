<?php
include '../../../config.php';

error_reporting(0);

if((isset($_GET) && !empty($_GET)) || (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')){

$page = ""; $limit = ""; $sidx = ""; $sord = ""; $grupo = "";
$search = ""; $articulo = ""; $contenedor = ""; $almacen = ""; $zona = ""; $proveedor = ""; $cve_proveedor = ""; $bl = ""; 
$lp = ""; $art_obsoletos = ""; $cve_cliente = "";$refWell = "";$pedimentoW = ""; $picking = "";$existencia_cajas = "";$factura = ""; $proyecto_existencias = "";
    if(isset($_GET) && !empty($_GET))
    {
        //$page = $_GET['start'];
        //$limit = $_GET['length'];
        //$search = $_GET['search']['value'];
        $search = $_GET['search'];

        $articulo = $_GET["articulo"];
        $contenedor = $_GET["contenedor"];
        $almacen = $_GET["almacen"];
        $zona = $_GET["zona"];
        $proveedor = $_GET["proveedor"];
        $existencia_cajas = $_GET["existencia_cajas"];
        $cve_proveedor = $_GET["cve_proveedor"];
        $cve_cliente   = $_GET["cve_cliente"];
        $bl = $_GET["bl"];
        $lp = $_GET["lp"];
        $factura = $_GET["factura_oc"];
        $proyecto_existencias = $_GET['proyecto_existencias'];
        $lotes = $_GET["lotes"];
        $lote_alterno = $_GET['lote_alterno'];
        $grupo = $_GET["grupo"];
        $clasificacion = $_GET["clasificacion"];
        $art_obsoletos = $_GET["art_obsoletos"];
        $refWell = $_GET['refWell'];
        $pedimentoW = $_GET['pedimentoW'];
        $picking = $_GET['picking'];

    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction

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
        $existencia_cajas = $_POST["existencia_cajas"];
        $cve_proveedor = $_POST["cve_proveedor"];
        $cve_cliente   = $_GET["cve_cliente"];
        $bl = $_POST["bl"];
        $lp = $_POST["lp"];
        $factura = $_POST["factura_oc"];
        $proyecto_existencias = $_POST['proyecto_existencias'];
        $lotes = $_POST["lotes"];
        $lote_alterno = $_POST['lote_alterno'];
        $grupo = $_POST["grupo"];
        $clasificacion = $_POST["clasificacion"];
        $art_obsoletos = $_POST["art_obsoletos"];
        $refWell = $_POST['refWell'];
        $pedimentoW = $_POST['pedimentoW'];
        $picking = $_POST['picking'];

        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $sord = $_POST['sord']; // get the direction

    }




    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn,"utf8mb3");
    $utf8Sql = "SET NAMES 'utf8mb4';";
    $res_charset = mysqli_query($conn, $utf8Sql);
/*
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_obsoletos = "";
    if($art_obsoletos == 1)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad < CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";

    if($art_obsoletos == 0)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad >= CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";


    $zona_produccion = "";
    $num_produccion = 0;
    if(!empty($zona))
    {
        $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona}'";
        //AND AreaProduccion = 'S'
        $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
        $num_produccion = mysqli_num_rows($query_zona_produccion);
        //if($query_zona_produccion){
        $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
        //}
    }

    $_page = 0;

      if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = ' WHERE id = "'.$almacen.'" ';

    $sql1 = 'SELECT * FROM c_almacenp $sqlAlmacen';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];

    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    if($zona_produccion == 'S')
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    $zona_rts = "";
    $zona_rtm_tipo = "ubicacion";
    $zona_rtm_tipo2 = "";
    if($zona == "RTS")
    {
        $sqlAlmacen = "";
        if($almacen)
           $sqlAlmacen = " AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE id = '".$almacen."') ";

        $zona_rts = " AND IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' {$sqlAlmacen} AND tds.Cve_articulo = a.cve_articulo), 0) != 0";
    }
    else if($zona == "RTM")
    {
        $zona_rtm_tipo = "area";
        $zona_rtm_tipo2 = " AND x.tipo_ubicacion = '' ";
    }

    /*
    $sqlFactura = !empty($factura) ? "AND IFNULL(((SELECT GROUP_CONCAT(DISTINCT IFNULL(Fact_Prov, '') SEPARATOR ', ') FROM th_entalmacen WHERE Fol_Folio IN 
                (
                SELECT ef.fol_folio 
                FROM td_entalmacen ef
                INNER JOIN t_cardex kf ON IFNULL(kf.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kf.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.destino = e.cve_ubicacion AND kf.id_TipoMovimiento = 2
                INNER JOIN t_cardex kp ON IFNULL(kp.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kp.cve_lote, '') = IFNULL(e.cve_lote, '') AND kp.destino = ef.cve_ubicacion AND kp.id_TipoMovimiento = 1
                WHERE IFNULL(ef.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(ef.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.origen = ef.cve_ubicacion AND kp.origen = ef.fol_folio AND kp.destino = kf.origen AND kf.destino = e.cve_ubicacion
                ) AND IFNULL(Fact_Prov, '') != '')), '') LIKE '%{$factura}%'" : "";
    */



    $sqlFactura = !empty($factura) ? " AND IFNULL(tr.factura_ent, '' ) LIKE '%$factura%'" : "";
    $sqlProyecto = !empty($proyecto_existencias) ? " AND IFNULL(tr.proyecto, '' ) LIKE '%$proyecto_existencias%'" : "";
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";

    $sqlProveedor = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
    $sqlCliente = !empty($cve_cliente) ? "INNER JOIN c_cliente c ON c.ID_Proveedor = p.ID_Proveedor AND e.ID_Proveedor = c.ID_Proveedor AND c.Cve_Clte = '{$cve_cliente}'" : "";
    //$sqlProveedor2 = !empty($proveedor) ? "AND x.id_proveedor = '{$proveedor}'" : "";
    $sqlProveedor2 = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
  
    //$sqlLotes = !empty($lotes) ? "AND x.lote like '%{$lotes}%'" : "";
    //$sqlLotes_alt = !empty($lote_alterno) ? "AND x.lote_alterno like '%{$lote_alterno}%'" : "";
    $sqlLotes = !empty($lotes) ? "AND e.cve_lote like '%{$lotes}%'" : "";
    $sqlLotes_alt = !empty($lote_alterno) ? "AND l.Lote_Alterno like '%{$lote_alterno}%'" : "";

    //$sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";
    //$sqlLP = !empty($lp) ? "AND x.LP like '%{$lp}%'" : "";
    $sqlbl = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : "";
    $sqlLP = !empty($lp) ? "AND ch.CveLP like '%{$lp}%'" : "";

    $sqlGrupo = !empty($grupo) ? "AND gr.cve_gpoart = '{$grupo}'" : "";
    $sqlClasif = !empty($clasificacion) ? "AND cl.cve_sgpoart = '{$clasificacion}'" : "";


    $sqlbl_search = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : "";
    $sqlLP_search = !empty($lp) ? "AND ch.CveLP like '%{$lp}%'" : "";

    $sqlPicking = ($picking != "") ? "AND IFNULL(u.picking, 'N') = '{$picking}'" : "";

    //$sqlproveedor_tipo = !empty($cve_proveedor) ? "AND x.id_proveedor = {$cve_proveedor}" : "";
    $sqlproveedor_tipo = !empty($cve_proveedor) ? "AND e.ID_Proveedor = {$cve_proveedor}" : "";
    $sqlproveedor_tipo2 = !empty($cve_proveedor) ? "AND e.ID_Proveedor = {$cve_proveedor}" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    $SQLrefWell = "";
    if($refWell)
        $SQLrefWell = " AND ta.recurso LIKE '%$refWell%' ";

    $SQLpedimentoW = "";
    if($pedimentoW)
        $SQLpedimentoW = " AND ta.Pedimento LIKE '%$pedimentoW%' ";


    $sqlCollation = ""; $sqlEliminaraduanaTemporalmente = "";
    if($instancia == 'foam')
    {
        $sqlCollation = " COLLATE utf8mb4_unicode_ci ";
        $sqlEliminaraduanaTemporalmente = " AND 0 ";
    }

   $tabla_from = "V_ExistenciaGral";
   $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
   $field_folio_ot = "''";
   //$field_NCaja = "''";
   $field_NCaja = "IF(ec.Id_Caja IS NOT NULL, CONCAT('(',(SELECT COUNT(*) FROM ts_existenciacajas exc WHERE exc.idy_ubica = e.cve_ubicacion AND e.cve_articulo = exc.cve_articulo AND IFNULL(exc.cve_lote, '') = IFNULL(e.cve_lote, '') AND e.cve_almac = exc.cve_almac AND IFNULL(exc.nTarima, '') = IF(IFNULL(exc.nTarima, '') = '', '', IFNULL(ch.IDContenedor, '')) ), ')'), '')";
   #AND IFNULL(exc.nTarima, '') = IFNULL(ch.IDContenedor, '')
   $SQL_FolioOT = "";

   if($zona_produccion == 'S' && $num_produccion < 2)
   {
        $tabla_from = "V_ExistenciaProduccion";
        $tipo_prod = "";

       $field_folio_ot = "IFNULL(op.Folio_Pro, '')";
       //$field_NCaja = "IFNULL(cm.NCaja, '')";
       $field_NCaja = "''";
       $SQL_FolioOT = "
            LEFT JOIN t_tarima tt ON tt.ntarima = ch.IDContenedor 
            LEFT JOIN t_ordenprod op ON op.Cve_Articulo = IFNULL(e.cve_articulo, tt.cve_articulo ) AND IFNULL(op.Cve_Lote,'') = IFNULL(tt.lote, e.cve_lote) AND op.Folio_Pro = IFNULL(tt.Fol_Folio, op.Folio_Pro) 
            LEFT JOIN th_cajamixta cm ON cm.fol_folio = tt.Fol_Folio AND cm.Cve_CajaMix = tt.Caja_ref 
        ";

   }
   else if($num_produccion == 2)
    {
       $tabla_from = "V_ExistenciaGralProduccion";
       $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
    }
/*
    $sqlCount = "SELECT
                    count(e.cve_articulo) as total
                  FROM {$tabla_from} e
                    LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                  WHERE {$sqlAlmacen} e.tipo = 'ubicacion' AND e.Existencia > 0 {$sqlArticulo}  {$sqlZona} {$sqlProveedor}  {$sqlbl} {$sqlGrupo}";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }
*/

    #if($instancia == 'foam' or $instancia == 'oslo')
        

    //$sql_existencia_cajas = "LEFT";
    $sql_existencia_cajas = "";
    $field_NCaja = "''";
    //$TempMostrarCajas = " AND 0 ";
    if($existencia_cajas == 1)
    {
        //$sql_existencia_cajas = "INNER";
        //$sql_existencia_cajas = "INNER JOIN ts_existenciacajas ec ON ec.idy_ubica = e.cve_ubicacion AND e.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(e.cve_lote, '')  AND e.cve_almac = ec.cve_almac AND IF(IFNULL(ec.nTarima, '') = '', '', IFNULL(ch.IDContenedor, '')) = IFNULL(ec.nTarima, '')";
        $sql_existencia_cajas = "INNER JOIN ts_existenciacajas ec ON IFNULL(ch.IDContenedor, '') = IFNULL(ec.nTarima, '')";
        //$field_NCaja = "IF(ec.Id_Caja IS NOT NULL, CONCAT('(',(SELECT COUNT(*) FROM ts_existenciacajas exc WHERE exc.idy_ubica = e.cve_ubicacion AND e.cve_articulo = exc.cve_articulo AND IFNULL(exc.cve_lote, '') = IFNULL(e.cve_lote, '') AND e.cve_almac = exc.cve_almac AND IFNULL(exc.nTarima, '') = IF(IFNULL(exc.nTarima, '') = '', '', IFNULL(ch.IDContenedor, '')) ), ')'), '')";
        //$field_NCaja = "IF(ec.Id_Caja IS NOT NULL, CONCAT('(',(SELECT COUNT(DISTINCT exc.Id_Caja) FROM ts_existenciacajas exc WHERE IFNULL(exc.nTarima, '') = IF(IFNULL(ec.nTarima, '') = '', '', IFNULL(ch.IDContenedor, '')) ), ')'), '')";
        $field_NCaja = "IF(ec.Id_Caja IS NOT NULL, CONCAT('(',(COUNT(DISTINCT ec.Id_Caja)), ')'), '')";
        //$TempMostrarCajas = "";
    }


    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " (e.cve_almac = '{$almacen}')  AND ";//OR zona.cve_almacp = '{$almacen}'

   //$group_by_existencias = " GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie, id_proveedor ";
   $group_by_existencias = " GROUP BY ap.clave, e.Cve_Contenedor, a.cve_articulo, IFNULL(e.cve_lote,''), COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' '), IFNULL(p.ID_proveedor, ''), u.CodigoCSD";
   if($existencia_cajas == 1)
    $group_by_existencias = " GROUP BY cve_almacen, cve_ubicacion, contenedor ";

    $sql = "
      SELECT #x.acciones, x.cve_almacen, x.id_almacen, x.almacen, x.folio_OT, x.NCaja, x.zona, x.codigo, x.cve_ubicacion, x.zona_recepcion, x.QA, x.contenedor, x.LP, x.clave, x.descripcion, x.des_grupo, x.des_clasif, x.cajasxpallets, x.piezasxcajas, x.Pallet, x.Caja, x.Piezas, x.control_lotes, x.control_numero_series, x.lote, x.lote_alterno, x.caducidad, x.nserie, x.peso, (x.cantidad) AS cantidad, (x.cantidad_kg) AS cantidad_kg, x.id_proveedor, (x.proveedor) AS proveedor, MAX(x.empresa_proveedor) AS empresa_proveedor, x.tipo_ubicacion, x.control_abc, x.clasif_abc, x.um, x.control_peso, x.referencia_well, x.pedimento_well, x.codigo_barras_pieza, x.ntarima, x.codigo_barras_caja, x.codigo_barras_pallet FROM(

         #SELECT 
         DISTINCT 
            IF(IFNULL(e.Cuarentena, '') = 0, '<input class=\"column-asignar\" type=\"checkbox\">', '') as acciones, 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            {$field_folio_ot} AS folio_OT,
            {$field_NCaja} AS NCaja,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            IFNULL(a.cve_codprov, '') as codigo_barras_pieza, 
            IFNULL(a.barras2, '') as codigo_barras_caja, 
            IFNULL(a.barras3, '') as codigo_barras_pallet, 
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(IFNULL(e.Cuarentena, 0) = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            gr.des_gpoart as des_grupo,
            cl.cve_sgpoart as des_clasif,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            ta.recurso as referencia_well,
            ta.Pedimento as pedimento_well,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            #COALESCE(if(a.control_lotes ='S',l.LOTE,''), '--') as lote,

            #IF(a.control_lotes ='S',IFNULL(l.Lote, ''),'') AS lote,
            #COALESCE(if(a.caduca = 'S',if(l.Caducidad = '0000-00-00','',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
            #COALESCE(if(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') as nserie,
            #COALESCE(IF(a.control_lotes ='S',e.cve_lote,''), '--') AS lote,
            IFNULL(e.cve_lote,'') AS lote,
            IFNULL(e.Lote_Alterno,'') AS lote_alterno,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,
            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', (e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo))),e.Existencia) as cantidad,

            #e.Existencia as cantidad,

            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 0) AS cantidad_kg,

            #e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)) AS cantidad_kg,
            #IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', ROUND(TRUNCATE(e.Existencia, 5), 4), e.Existencia) as cantidad,
            IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', TRUNCATE(e.Existencia, $decimales_cantidad), e.Existencia) as cantidad,
            #IF(a.control_peso = 'S', ROUND(e.Existencia/IF(IFNULL(a.peso, 0) = 0, 1, a.peso), 3), e.Existencia) AS cantidad,
            #ROUND(TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 5), 4) AS cantidad_kg,
            TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), $decimales_cantidad) AS cantidad_kg,
            #e.Existencia AS cantidad_kg,

            #(SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            #ent.fol_folio AS folio,
            #IFNULL(ent.fol_folio, '') AS folio,

            #IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND Fol_Folio IN (SELECT fol_folio FROM td_entalmacenxtarima WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '') AND IFNULL(ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '')) AND Cve_Proveedor = e.ID_Proveedor)), '') AS folio,

            #IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, ''))  AND Cve_Proveedor = e.ID_Proveedor)), '') AS folio,

            #COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,
            IFNULL(ch.IDContenedor, '') as ntarima,
            #GROUP_CONCAT(DISTINCT IFNULL(p.Nombre, '')) AS proveedor,


            #IFNULL(IF(p.es_cliente = 1, p.Nombre, ''), '') AS empresa_proveedor,
            IFNULL(poc.Nombre, '') AS empresa_proveedor,
            #GROUP_CONCAT(DISTINCT IFNULL(poc.Nombre, '')) AS empresa_proveedor,

            #IFNULL(ent.num_orden, '') AS folio_oc,
#            IFNULL(
#            (
#        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
#        FROM td_entalmacen 
#        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
#        AND Fol_Folio IN (
#                    SELECT fol_folio 
#                    FROM td_entalmacenxtarima 
#                    WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') 
#                    AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '') 
#                    AND IFNULL(ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '')
#                  ) 
#        ORDER BY id DESC LIMIT 1
#        )
#        , 
#        IFNULL((
#        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
#        FROM td_entalmacen 
#        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
#        ORDER BY id DESC LIMIT 1
#        #), (SELECT DATE_FORMAT(IFNULL(Fec_Ingreso, fecha), '%d-%m-%Y') FROM t_cardex WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1)
#         )
#        ) AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,
            IFNULL(um.cve_umed, '') as um,
            a.control_peso
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON IFNULL(ch.clave_contenedor, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(ch.clave_contenedor, '') != ''
            LEFT JOIN tubicacionesretencion zona ON CONVERT(zona.cve_ubicacion, CHAR) = CONVERT(e.cve_ubicacion, CHAR) {$sqlCollation}
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(e.cve_almac, z.cve_almacenp) #OR ap.id = zona.cve_almacp
            #{$sql_existencia_cajas} JOIN ts_existenciacajas ec ON ec.idy_ubica = e.cve_ubicacion AND e.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(e.cve_lote, '')  AND e.cve_almac = ec.cve_almac AND IF(IFNULL(ec.nTarima, '') = '', '', IFNULL(ch.IDContenedor, '')) = IFNULL(ec.nTarima, '')  {$TempMostrarCajas}
            {$sql_existencia_cajas}
            #AND IFNULL(ec.nTarima, '') = IFNULL(ch.IDContenedor, '')
             {$SQL_FolioOT} 
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0 $sqlEliminaraduanaTemporalmente
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor) 
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.cve_lote $sqlEliminaraduanaTemporalmente
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio $sqlEliminaraduanaTemporalmente #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra $sqlEliminaraduanaTemporalmente
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor $sqlEliminaraduanaTemporalmente
            LEFT JOIN t_trazabilidad_existencias tr ON CONVERT(tr.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(IFNULL(tr.cve_lote, ''), CHAR) = CONVERT(IFNULL(e.cve_lote, ''), CHAR) AND e.cve_ubicacion = tr.idy_ubica AND tr.cve_almac = e.cve_almac AND tr.idy_ubica IS NOT NULL AND tr.id_proveedor = e.Id_Proveedor AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') $sqlEliminaraduanaTemporalmente

            {$sqlCliente}
            #LEFT JOIN td_entalmacenxtarima ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.ClaveEtiqueta = ch.CveLP AND ent.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = ap.clave AND (tipo = 'RL' OR tipo = 'OC'))
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlFactura} {$sqlProyecto} 
                {$sqlProveedor} {$sqlGrupo} {$sqlClasif} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} {$sqlPicking}
                {$zona_rts} 

                {$sqlbl} {$sqlLP} {$sqlLotes} {$sqlLotes_alt} {$sqlproveedor_tipo} 

            #GROUP BY id_proveedor
            #GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie

            #ORDER BY a.des_articulo, folio_OT, (NCaja+0) ASC

                #)x
            #where x.lote != '--'
            #WHERE 1 AND x.id_almacen = '{$almacen}' #AND x.id_proveedor IS NOT NULL
            
            
            
            #{$sqlbl}
            #{$sqlLP}
            #{$sqlLotes}
            #{$sqlLotes_alt}
            #{$sqlproveedor_tipo}
            #{$sqlProveedor2}

        {$group_by_existencias}
        ORDER BY descripcion, folio_OT, (NCaja+0) ASC
            ";
            $sql_conteo = $sql;
/*
##############################################################################################################################
#       UNION DE TS_EXISTENCIASTARIMAS MIENTRAS ESTEBAN REGRESA
##############################################################################################################################
            UNION 
         SELECT DISTINCT 
            IF(IFNULL(e.Cuarentena, '') = 0, '<input class=\"column-asignar\" type=\"checkbox\">', '') as acciones, 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre AS almacen,
            '' AS folio_OT,
            '' AS NCaja,
            z.des_almac AS zona,
            u.CodigoCSD AS codigo,
            e.idy_ubica AS cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(e.Cuarentena = 1, 'Si','No') AS QA,
            ch.clave_contenedor AS contenedor,
            IF(ch.clave_contenedor != '', ch.CveLP, '') AS LP,
            a.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            gr.des_gpoart AS des_grupo,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            ta.recurso AS referencia_well,
            ta.Pedimento AS pedimento_well,
            a.control_lotes AS control_lotes,
            a.control_numero_series AS control_numero_series,
            IFNULL(e.lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.lote,'' ), ' ') AS nserie,
            a.peso,
            IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', ROUND(TRUNCATE(e.Existencia, 5), 4), e.Existencia) AS cantidad,
            ROUND(TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 5), 4) AS cantidad_kg,
            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,
            IFNULL(poc.Nombre, '') AS empresa_proveedor,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,
            IFNULL(um.cve_umed, '') AS um,
            a.control_peso
            FROM
                ts_existenciatarima e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
            LEFT JOIN c_lotes l ON l.LOTE = e.lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            LEFT JOIN c_charolas ch ON ch.IDContenedor = e.ntarima
            LEFT JOIN tubicacionesretencion zona ON zona.cve_ubicacion = e.idy_ubica 
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(z.cve_almacenp, e.cve_almac) #OR ap.id = zona.cve_almacp
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor)
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.lote 
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor
            
                WHERE {$sqlAlmacen}  e.existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona}
                {$sqlProveedor} {$sqlGrupo} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} 
                $zona_rts

##############################################################################################################################
##############################################################################################################################
*/
/*
    $sql = "
      SELECT x.acciones, x.cve_almacen, x.id_almacen, x.almacen, x.folio_OT, x.NCaja, x.zona, x.codigo, x.cve_ubicacion, x.zona_recepcion, x.QA, x.contenedor, x.LP, x.clave, x.descripcion, x.des_grupo, x.cajasxpallets, x.piezasxcajas, x.Pallet, x.Caja, x.Piezas, x.control_lotes, x.control_numero_series, x.lote, x.caducidad, x.nserie, x.peso, SUM(x.cantidad) AS cantidad, SUM(x.cantidad_kg) AS cantidad_kg, x.id_proveedor, GROUP_CONCAT(DISTINCT x.proveedor) AS proveedor, GROUP_CONCAT(DISTINCT x.empresa_proveedor) AS empresa_proveedor, x.tipo_ubicacion, x.control_abc, x.clasif_abc, x.um, x.control_peso FROM(
         SELECT DISTINCT 
            IF(IFNULL(e.Cuarentena, '') = 0, '<input class=\"column-asignar\" type=\"checkbox\">', '') as acciones, 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            {$field_folio_ot} AS folio_OT,
            {$field_NCaja} AS NCaja,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(e.Cuarentena = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            gr.des_gpoart as des_grupo,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            #COALESCE(if(a.control_lotes ='S',l.LOTE,''), '--') as lote,

            #IF(a.control_lotes ='S',IFNULL(l.Lote, ''),'') AS lote,
            #COALESCE(if(a.caduca = 'S',if(l.Caducidad = '0000-00-00','',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
            #COALESCE(if(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') as nserie,
            #COALESCE(IF(a.control_lotes ='S',e.cve_lote,''), '--') AS lote,
            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,
            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', (e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo))),e.Existencia) as cantidad,

            #e.Existencia as cantidad,

            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 0) AS cantidad_kg,

            #e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)) AS cantidad_kg,
            IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', TRUNCATE(e.Existencia, 4), e.Existencia) as cantidad,
            #IF(a.control_peso = 'S', ROUND(e.Existencia/IF(IFNULL(a.peso, 0) = 0, 1, a.peso), 3), e.Existencia) AS cantidad,
            TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 4) AS cantidad_kg,
            #e.Existencia AS cantidad_kg,

            #(SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            #ent.fol_folio AS folio,
            #IFNULL(ent.fol_folio, '') AS folio,

            #IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND Fol_Folio IN (SELECT fol_folio FROM td_entalmacenxtarima WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '') AND IFNULL(ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '')) AND Cve_Proveedor = e.ID_Proveedor)), '') AS folio,

            #IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, ''))  AND Cve_Proveedor = e.ID_Proveedor)), '') AS folio,

            #COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,
            #GROUP_CONCAT(DISTINCT IFNULL(p.Nombre, '')) AS proveedor,


            #IFNULL(IF(p.es_cliente = 1, p.Nombre, ''), '') AS empresa_proveedor,
            IFNULL(poc.Nombre, '') AS empresa_proveedor,
            #GROUP_CONCAT(DISTINCT IFNULL(poc.Nombre, '')) AS empresa_proveedor,

            #IFNULL(ent.num_orden, '') AS folio_oc,
#            IFNULL(
#            (
#        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
#        FROM td_entalmacen 
#        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
#        AND Fol_Folio IN (
#                    SELECT fol_folio 
#                    FROM td_entalmacenxtarima 
#                    WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') 
#                    AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '') 
#                    AND IFNULL(ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '')
#                  ) 
#        ORDER BY id DESC LIMIT 1
#        )
#        , 
#        IFNULL((
#        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
#        FROM td_entalmacen 
#        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
#        ORDER BY id DESC LIMIT 1
#        #), (SELECT DATE_FORMAT(IFNULL(Fec_Ingreso, fecha), '%d-%m-%Y') FROM t_cardex WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1)
#         )
#        ) AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,
            IFNULL(um.cve_umed, '') as um,
            a.control_peso
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN tubicacionesretencion zona ON zona.cve_ubicacion = e.cve_ubicacion
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(z.cve_almacenp, e.cve_almac) #OR ap.id = zona.cve_almacp
             {$SQL_FolioOT} 
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor)
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.cve_lote 
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor

            {$sqlCliente}
            #LEFT JOIN td_entalmacenxtarima ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.ClaveEtiqueta = ch.CveLP AND ent.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = ap.clave AND (tipo = 'RL' OR tipo = 'OC'))
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona}
                {$sqlProveedor} {$sqlGrupo} {$sql_obsoletos} 
                $zona_rts

            #GROUP BY id_proveedor
            GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie
            ORDER BY a.des_articulo, folio_OT, (NCaja+0) ASC
                )x
            #where x.lote != '--'
            WHERE 1 AND x.id_almacen = '{$almacen}' #AND x.id_proveedor IS NOT NULL
            {$sqlbl} 
            {$sqlLP} 
            {$sqlLotes} 
            {$sqlproveedor_tipo} 
            {$sqlProveedor2}
            GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie #, id_proveedor
            ";
            */
/*
    if($zona_produccion == 'S')
    {

            $sqlAlmacen = ""; $sqlAlmacen2 = "";
            if($almacen)
            {
               $sqlAlmacen = " e.cve_almac = '{$almacen}' ";
               $sqlAlmacen2 = " AND tds.cve_almac = '{$almacen}' ";
            }
        //e.cve_ubicacion
        $sql = "SELECT DISTINCT * FROM(
         SELECT DISTINCT
            IF(IFNULL(e.Cuarentena, '') = 0, '<input class=\"column-asignar\" type=\"checkbox\">', '') as acciones, 
            ap.nombre as almacen,
            pv.es_cliente as empresa_proveedor,
            e.cve_almac AS id_almacen,
            z.des_almac as zona,
            u.CodigoCSD AS codigo,
            e.cve_ubicacion AS cve_ubicacion,
            '' AS IDContenedor,
            IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' {$sqlAlmacen2} AND tds.Cve_articulo = a.cve_articulo), 0) AS RTS,
            IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            a.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            gr.des_gpoart as des_grupo,
            '' AS tipo,
            IFNULL(a.cajas_palet, 0) cajasxpallets,
            IFNULL(a.num_multiplo, 0) piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            #COALESCE(IF(a.control_lotes ='S',l.LOTE,''), '--') AS lote,
            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.caduca, 'N') = 'S',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,
            IF(a.control_peso = 'S' AND a.Compuesto != 'S', (e.Existencia*a.peso*a.num_multiplo),e.Existencia) as cantidad,
            IF(a.control_peso = 'S' AND a.Compuesto != 'S', e.Existencia*a.peso*a.num_multiplo, 0) AS cantidad_kg,
            #(SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            e.ID_Proveedor AS id_proveedor,
            pv.Nombre AS proveedor,
            #IFNULL(ent.num_orden, '') AS folio,
            '' AS folio,
            IF(IFNULL(a.caduca, 'N') = 'S', COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--'), DATE_FORMAT(l.Caducidad,'%d-%m-%Y')) AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_peso,
            IFNULL(IF(a.control_peso = 'S' AND a.Compuesto != 'S', (e.Existencia*a.peso*a.num_multiplo),e.Existencia) - IFNULL((SELECT SUM(tsb.Num_cantidad) AS pedidas FROM td_subpedido tsb LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo), 0) - IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.cve_ubicacion GROUP BY vp.Cve_Articulo)), 0) - IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND DATE_FORMAT(lo.Caducidad,'%Y-%m-%d') != '0000-00-00' AND IFNULL(lo.Caducidad, '') != '' AND IFNULL(DATE_FORMAT(lo.Caducidad,'%Y-%m-%d'), '') < CURDATE() AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion), 0), 0), 0) AS Libre,
            IFNULL((SELECT SUM(tsb.Num_cantidad) AS pedidas FROM td_subpedido tsb LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo), 0) AS RP,
            #IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 GROUP BY vp.cve_articulo)), 0) AS Prod_QA,
            IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.cve_ubicacion GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            IFNULL(IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND DATE_FORMAT(l.Caducidad,'%Y-%m-%d') != '0000-00-00' AND IFNULL(lo.Caducidad, '') != '' AND DATE_FORMAT(lo.Caducidad,'%Y-%m-%d') < CURDATE() AND vp.tipo = 'ubicacion' AND vp.cve_ubicacion = e.cve_ubicacion), 0), 0), 0) AS Obsoletos,
            TRUNCATE(a.costoPromedio,2) AS costoPromedio,
            TRUNCATE(a.costoPromedio*e.Existencia,2) AS subtotalPromedio
            FROM
                V_ExistenciaProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp 
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN c_proveedores pv ON pv.ID_Proveedor = e.ID_Proveedor
            #LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote
            WHERE {$sqlAlmacen} AND e.Existencia > 0 {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlProveedor} {$sqlGrupo}
            ORDER BY l.CADUCIDAD ASC
                )x
           # WHERE X.lote != '--' 
            WHERE 1 
            {$sqlbl} 
            {$sqlLotes}";
    }
*/
    $sql2 = $sql;
    //$sql_cuenta = "SELECT COUNT(*) as n from V_ExistenciaGral e where e.cve_almac = 3 and e.tipo = 'ubicacion'";
/*
    $sql_cuenta = "
      SELECT COUNT(*) as n FROM(
               SELECT e.*
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON IFNULL(ch.clave_contenedor, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(ch.clave_contenedor, '') != ''
            LEFT JOIN tubicacionesretencion zona ON CONVERT(zona.cve_ubicacion, CHAR) = CONVERT(e.cve_ubicacion, CHAR) {$sqlCollation}
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(e.cve_almac, z.cve_almacenp) #OR ap.id = zona.cve_almacp
            #{$sql_existencia_cajas} JOIN ts_existenciacajas ec ON ec.idy_ubica = e.cve_ubicacion AND e.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(e.cve_lote, '')  AND e.cve_almac = ec.cve_almac AND IF(IFNULL(ec.nTarima, '') = '', '', IFNULL(ch.IDContenedor, '')) = IFNULL(ec.nTarima, '')  {$TempMostrarCajas}
            {$sql_existencia_cajas} 
            #AND IFNULL(ec.nTarima, '') = IFNULL(ch.IDContenedor, '')
             {$SQL_FolioOT} 
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0 $sqlEliminaraduanaTemporalmente
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor) 
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.cve_lote $sqlEliminaraduanaTemporalmente
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio $sqlEliminaraduanaTemporalmente #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra $sqlEliminaraduanaTemporalmente
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor $sqlEliminaraduanaTemporalmente
            LEFT JOIN t_trazabilidad_existencias tr ON CONVERT(tr.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(IFNULL(tr.cve_lote, ''), CHAR) = CONVERT(IFNULL(e.cve_lote, ''), CHAR) AND e.cve_ubicacion = tr.idy_ubica AND tr.cve_almac = e.cve_almac AND tr.idy_ubica IS NOT NULL AND tr.id_proveedor = e.Id_Proveedor AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') $sqlEliminaraduanaTemporalmente

            {$sqlCliente}
            #LEFT JOIN td_entalmacenxtarima ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.ClaveEtiqueta = ch.CveLP AND ent.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = ap.clave AND (tipo = 'RL' OR tipo = 'OC'))
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlFactura} {$sqlProyecto} 
                {$sqlProveedor} {$sqlGrupo} {$sqlClasif} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} {$sqlPicking}
                $zona_rts

            #GROUP BY id_proveedor
            #GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie
            GROUP BY e.cve_ubicacion, e.Cve_Contenedor, e.cve_articulo, e.cve_lote, e.ID_Proveedor

                ) as n
            #where x.lote != '--'

            ";
*/

    if (!($resc = mysqli_query($conn, $sql2))) {echo json_encode(array( "error" => "2222Error al procesar la petición: (" . mysqli_error($conn) . ") ")).$sql_cuenta;}
    $count = mysqli_num_rows($resc);
    //$count = mysqli_fetch_array($resc)["n"];
    $l = " LIMIT $_page,$limit; ";

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
  
    if($limit)
    {
    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "3Error al procesar la petición: (" . mysqli_error($conn) . ") "))." ------- ". $sql;}
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
/*
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

        $peso_rp = 0; $disponible_kg = 0;
        if($row["control_peso"] == 'S')
        {
            $row["RP"] = number_format($row["RP"], 5);
            $row["Libre"] = number_format($row["Libre"], 2);
            //if($row['piezasxcajas'] > 1)
            $row["cantidad_kg"] = number_format($row["cantidad_kg"], 4);

            if($row['peso'] > 0)
            {
                $peso_rp = $row['RP']/$row['peso'];
                $peso_rp = number_format($peso_rp, 4);

                //if($row['piezasxcajas'] > 1)
                //{
                $disponible_kg = $row['Libre']*$row['peso'];
                $disponible_kg = number_format($disponible_kg, 4);
                //}
            }
        }
        else 
        {
            $row["RP"] = number_format($row["RP"], 0);
            $row["Libre"] = number_format($row["Libre"], 0);
            $row["cantidad_kg"] = number_format($row["cantidad"]*$row["peso"], 2);
        }
*/
/*
        $data[] = $row;
        //$suma += $row['Libre']+$row['RP'];
        //$suma += $row['cantidad'];

        if(!in_array($row['clave'], $productos)) 
        {
            $productos_i++;
            array_push($productos, $row['clave']);
        }

        $lote_serie = "";
            if($row['control_lotes'] == 'S')
                $lote_serie = $row['lote'];

            if($row['control_numero_series'] == 'S')
                $lote_serie = $row['nserie'];

            $proveedor_empresa_proveedor = ($row['empresa_proveedor']==1)?$row['proveedor']:"";
*/
            $responce["rows"][$i]['id']=$row['cve_ubicacion'];
            $responce["rows"][$i]['cell']=array(
                '',
                $row['acciones'],
                $row['folio_OT'],
                $row['referencia_well'],
                $row['pedimento_well'],
                $row['clasif_abc'],
                $row['codigo'],
                $row['contenedor'],
                $row['LP'],
                $row['control_abc'],
                $row['clave'],
                $row['codigo_barras_pieza'],
                $row['codigo_barras_caja'],
                $row['codigo_barras_pallet'],
                $row['des_clasif'],
                $row['descripcion'],
                $row['zona'],
                $row['tipo_ubicacion'],
                $row['QA'],//noob
                $row['des_grupo'],
                $row['lote'],
                $row['caducidad'],
                $row['lote_alterno'],
                $row['cantidad'],
                $row['cantidad_kg'],
                $row['um'],
                $row['NCaja'],
                //$peso_rp,
                //$row['RP'],
                //$row['Prod_QA'],
                //$row['Obsoletos'],
                //$row['Pallet'],
                //$row['Caja'],
                //$row["Piezas"],
                //abs($row['cantidad'] - $peso_rp),
                //abs($row['cantidad_kg'] - $row['RP']),
                '', //$row['fecha_ingreso'],
                '', //$row['folio'],
                //$row['costoPromedio'],
                //$row['subtotalPromedio'],
                $row['empresa_proveedor'], //empresa
                $row['proveedor'], //proveedor
                $row['almacen'],
                $row['zona_recepcion'],
                $row['cve_ubicacion'],
                $row['id_proveedor'],
                $row['ntarima']
              );
              $i++;
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


    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";
/*
    $sql2 = "
      SELECT 
    COUNT(DISTINCT X.cve_articulo) AS total, SUM(X.Existencia) AS cantidad 
      FROM(
         SELECT DISTINCT 
            #COUNT(DISTINCT e.cve_articulo) AS total,
            #SUM(e.Existencia) AS cantidad
            e.*
            FROM
                V_ExistenciaGral e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = rap.Id_Proveedor
             {$sqlCliente} 

                WHERE {$sqlAlmacen} e.tipo = '{$zona_rtm_tipo}' AND e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlbl_search} {$sqlLP_search}
                {$sqlProveedor} {$sqlGrupo} {$sqlClasif} {$sqlPicking} {$sqlFactura}
                {$sqlproveedor_tipo2}
                $zona_rts
                )X
            #where x.lote != '--'
            WHERE 1 
            ";



    if($zona_produccion == 'S')
    {
        $sqlAlmacen = "";
        if($almacen)
           $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

        $sql2 = "SELECT DISTINCT COUNT(x.cve_articulo) AS total, SUM(x.Existencia) AS cantidad FROM(
         SELECT DISTINCT
            e.cve_articulo,
            e.Existencia
            FROM
                V_ExistenciaProduccion e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = rap.Id_Proveedor
             {$sqlCliente} 
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            #LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote
            WHERE {$sqlAlmacen} e.Existencia > 0 {$sqlArticulo} {$sqlFactura} {$sqlContenedor} {$sqlZona} {$sqlProveedor} {$sqlbl_search} {$sqlLP_search} {$sqlPicking} {$sqlGrupo} {$sqlClasif} 
                ) as x

           # WHERE X.lote != '--' 
            WHERE 1 ";
    }
*/
    $sql2 = "SELECT COUNT(DISTINCT conteo.clave) AS total, TRUNCATE(SUM(conteo.cantidad), $decimales_cantidad) AS cantidad FROM ( ".$sql_conteo." ) AS conteo";
    if (!($res = mysqli_query($conn, $sql2))) {echo json_encode(array( "error" => "4Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
    $row = mysqli_fetch_array($res);
    $suma = $row['cantidad'];
    $productos = $row['total'];

    $productos_con_lp_diferentes = 0;
    //buscamos si hay productos con diferentes LP, si hay repetidos, los contamos como uno solo, entoces lo agrupamos por lp y que no sea null ni vacio etc..
    
    $sqllp = "SELECT COUNT(DISTINCT LP) AS total FROM ( ".$sql_conteo." ) AS conteo WHERE LP != '' AND LP IS NOT NULL";
    if (!($res = mysqli_query($conn, $sqllp))) {echo json_encode(array( "error" => "5Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
    $row = mysqli_fetch_array($res);
    
    $productos_con_lp_diferentes = $row['total'];
    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    //$suma_dec = $suma - ((int) $suma);

    //$suma = number_format($suma, 0);
    //if($suma_dec > 0)
        $suma = number_format($suma, $decimales_cantidad);

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;
    $responce["productos"] = $productos;
    $responce["unidades"] = $suma;
    $responce["sql"] = $sql;
    $responce["sql2"] = $sql2;
    $responce["lp"] = $lp;
    $responce["productos_con_lp_diferentes"] = $productos_con_lp_diferentes;


    mysqli_close();
/*
    header('Content-type: application/json');
    $output = array(
        "draw" => $draw,
        "recordsTotal" => $i,
        "page" => $page,
        "total" => $limit,
        "records" => $i,
        "recordsFiltered" => $i,
        "data" => $data,
        "sql" => $sql,
        "productos" => $productos_i,
        "unidades" => $suma,
        "bl" => $responce
    );
    echo json_encode($output);exit;
*/
    }
    echo json_encode($responce);

}


if($_POST['action'] == "savemotivo")
{
    $procesos = "";
    $motivos = $_POST['motivos'];
    $registros = $_POST['registros'];
    $almacen = $_POST['almacen'];
    $fecha = $_POST['fecha'];
    $id_usuario = $_POST['id_usuario'];


    $seleccionar_todo_qa = $_POST['seleccionar_todo_qa'];
    $articulo_qa = $_POST['articulo_qa'];
    $lotes_qa = $_POST['lotes_qa'];
    $grupo_qa = $_POST['grupo_qa'];
    $contenedor_qa = $_POST['contenedor_qa'];
    $zona_qa = $_POST['zona_qa'];
    $bl_qa = $_POST['bl_qa'];
    $lp_qa = $_POST['lp_qa'];
    $art_obsoletos_qa = $_POST["art_obsoletos_qa"];
    $result = "";


    $sql1 ="
          SELECT
            count(Fol_Folio) as conteo
          FROM t_movcuarentena";
          $res = getArraySQL($sql1);
          $res[0]["conteo"] ++;
          $num = str_pad($res[0]["conteo"],3,0,STR_PAD_LEFT);
          $nume = $fecha.$num;
          $folio =  'QA'.$nume;

          $procesos .= $sql1." :::::::::::: ";

    if($seleccionar_todo_qa == 0)
    {
        if($registros[0][1] != "" && $registros[0][1] != " ")
       	{
    				$sql1 ="
    						SELECT  
    								c_charolas.IDContenedor AS id
    						FROM c_charolas 
    						WHERE clave_contenedor = '{$registros[0][1]}'";
    						$res = getArraySQL($sql1);
    						$id = $res[0]["id"];

                    $procesos .= $sql1." :::::::::::: ";
      	 }
      
    	
      
        //asignados.push([bl,contenedor,cve_articulo,lote,serie,cantidad]);
        foreach($registros as $registro)
        {
            $lote = $registro[3];
            //eif($lote== ""){$lote = $registro[4];}
            //eif($lote == " ") $lote = "";

            $sql1 ="SELECT  
                        c_ubicacion.idy_ubica AS idy_ubica
                    FROM c_ubicacion 
                    WHERE CodigoCSD = '{$registro[0]}'";
            $res = getArraySQL($sql1);
            $idy_ubica = $res[0]["idy_ubica"];

            $procesos .= $sql1." :::::::::::: ";

            if($registro[1] != "")
            {
              $sql2 ="
                  UPDATE ts_existenciatarima
                  SET Cuarentena = 1
                  where cve_almac = '{$almacen}'
                    and cve_articulo = '{$registro[2]}'
                    and lote = '{$lote}'
                    AND ntarima = $id 
                    ";
                    #and existencia = '{$registro[5]}'
                    $res = getArraySQL($sql2);

                    $procesos .= $sql2." :::::::::::: ";
            }
            else  
            {
              $sql2 ="
                  UPDATE ts_existenciapiezas
                  SET Cuarentena = 1
                  where cve_almac = '{$almacen}'
                    and cve_articulo = '{$registro[2]}'
                    and cve_lote = '{$lote}'
                    ";
                    #and Existencia = '{$registro[5]}'
                    $res = getArraySQL($sql2);

                    $procesos .= $sql2." :::::::::::: ";
            }
        
            $sql ="
                INSERT INTO t_movcuarentena 
            (Fol_Folio, Idy_Ubica, IdContenedor, Cve_Articulo, Cve_Lote, Cantidad, PzsXCaja, Fec_Ingreso, Id_MotivoIng, Tipo_Cat_Ing, Usuario_Ing) 
                VALUES ('$folio', '$idy_ubica', '$id', '$registro[2]', '$lote', '$registro[4]', '', NOW(), '$motivos', 'Q', '$id_usuario')";

            $procesos .= $sql." :::::::::::: ";
            $res = getArraySQL($sql);
            $result = array(
            "success" => true,
                "sql" => $res,
               "sql2" => $res,
           "procesos" => $procesos
             );
        }
    }
    else
    {

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_obsoletos = "";
        if($art_obsoletos_qa == 1)
           $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad < CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";

        if($art_obsoletos_qa == 0)
           $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad >= CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";


        $sql_articulo_qa = "";
        if($articulo_qa)
            $sql_articulo_qa = " AND e.cve_articulo = '{$articulo_qa}' ";

        $sql_lotes_qa = "";
        if($lotes_qa)
            $sql_lotes_qa = " AND e.cve_lote = '{$lotes_qa}' ";

        $sql_grupo_qa = "";
        if($grupo_qa)
            $sql_grupo_qa = " AND g.cve_gpoart = '{$grupo_qa}' ";

        $sql_contenedor_qa = "";
        if($contenedor_qa)
            $sql_contenedor_qa = " AND ch.clave_contenedor = '{$contenedor_qa}' ";

        $sql_zona_qa = "";
        if($zona_qa)
            $sql_zona_qa = " AND zona.cve_almac = '{$zona_qa}' ";

        $sql_bl_qa = "";
        if($bl_qa)
            $sql_bl_qa = " AND u.CodigoCSD LIKE '%{$bl_qa}%' ";

        $sql_lp_qa = "";
        if($lp_qa)
            $sql_lp_qa = " AND ch.CveLP LIKE '%{$lp_qa}%' ";



        $zona_produccion = "";
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

        if(!empty($zona_qa))
        {
            $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona_qa}' AND AreaProduccion = 'S'";
            $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
            if($query_zona_produccion){
                $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
            }
        }

       $tabla_from = "V_ExistenciaGral";
       $tipo_prod  = " AND e.tipo = 'ubicacion' ";
       if($zona_produccion == 'S')
       {
            $tabla_from = "V_ExistenciaProduccion";
            $tipo_prod = "";
       }


        $sql = "SELECT 
                    e.cve_almac,
                    e.cve_ubicacion,
                    e.cve_articulo,
                    e.cve_lote,
                    e.Existencia,
                    e.Id_Proveedor,
                    ch.IDContenedor,
                    e.Cve_Contenedor
                FROM {$tabla_from} e
                LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
                LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                LEFT JOIN c_almacen zona ON zona.cve_almacenp = alm.id
                LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion AND u.cve_almac = zona.cve_almac
                LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
                LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
                WHERE e.Cuarentena = 0 AND e.cve_almac = {$almacen} {$tipo_prod} 
                {$sql_articulo_qa} 
                {$sql_lotes_qa} 
                {$sql_grupo_qa} 
                {$sql_contenedor_qa} 
                {$sql_zona_qa} 
                {$sql_bl_qa} 
                {$sql_lp_qa} 
                {$sql_obsoletos} 
            ";

            $procesos .= $sql." :::::::::::: ";

            $res = mysqli_query($conn, $sql);

            while($row = mysqli_fetch_array($res))
            {
                extract($row);
                if($Cve_Contenedor != "")
                {
                  $sql2 ="
                      UPDATE ts_existenciatarima
                      SET Cuarentena = 1
                      where cve_almac = '{$cve_almac}'
                        and cve_articulo = '{$cve_articulo}'
                        and lote = '{$cve_lote}'
                        AND ntarima = $IDContenedor 
                        ";
                        $res2 = mysqli_query($conn, $sql2);
                    $procesos .= $sql2." :::::::::::: ";
                }
                else  
                {
                  $sql2 ="
                      UPDATE ts_existenciapiezas
                      SET Cuarentena = 1
                      where cve_almac = '{$cve_almac}'
                        and cve_articulo = '{$cve_articulo}'
                        and cve_lote = '{$cve_lote}'
                        ";
                        #and Existencia = '{$registro[5]}'
                        $res2 = mysqli_query($conn, $sql2);
                    $procesos .= $sql2." :::::::::::: ";
                }
            
                $sql ="
                    INSERT INTO t_movcuarentena 
                (Fol_Folio, Idy_Ubica, IdContenedor, Cve_Articulo, Cve_Lote, Cantidad, PzsXCaja, Fec_Ingreso, Id_MotivoIng, Tipo_Cat_Ing, Usuario_Ing) 
                    VALUES ('$folio', '$cve_ubicacion', '$IDContenedor', '$cve_articulo', '$cve_lote', '$Existencia', '', NOW(), '$motivos', 'Q', '$id_usuario')";
                $res2 = mysqli_query($conn, $sql);
                $procesos .= $sql." :::::::::::: ";
            }

            $result = array(
            "success" => true,
                "sql" => "",
               "sql2" => "",
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
            COALESCE(DATE_FORMAT(l.Caducidad,'%d-%m-%Y'), '--') as caducidad,
            COALESCE(s.numero_serie, '--') as nserie,
            a.peso,
            #e.Existencia as cantidad,
            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', e.Existencia*a.peso, 0) AS cantidad_kg,

            #e.Existencia as cantidad,
            IF(a.control_peso = 'S', ROUND(e.Existencia/IF(IFNULL(a.peso, 0) = 0, 1, a.peso), 3), e.Existencia) AS cantidad,
            #e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)) AS cantidad_kg,
            e.Existencia AS cantidad_kg,


            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            IFNULL(
            (
        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
        FROM td_entalmacen 
        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
        AND Fol_Folio IN (
                    SELECT fol_folio 
                    FROM td_entalmacenxtarima 
                    WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') 
                    AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '') 
                    AND IFNULL(ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '')
                  ) 
        ORDER BY id DESC LIMIT 1
        )
        , 
        IFNULL((
        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
        FROM td_entalmacen 
        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
        ORDER BY id DESC LIMIT 1
        ), (SELECT DATE_FORMAT(IFNULL(Fec_Ingreso, fecha), '%d-%m-%Y') FROM t_cardex WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1)
         )
        ) AS fecha_ingreso,
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

if( $_POST['action'] == 'DetalleExistencia' ) 
{
    $almacen  = $_POST["almacen"];
    $zona     = $_POST["zona"];
    $articulo = $_POST["articulo"];
    $lotes     = $_POST["lote"];
    $bl     = $_POST["bl"];

    $zona_produccion = "";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 


    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];

    if(!empty($zona))
    {
        $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona}' AND AreaProduccion = 'S'";
        $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
        if($query_zona_produccion){
            $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
        }
    }

    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";

    $sqlLotes = !empty($lotes) ? "AND x.lote like '%{$lotes}%'" : "";

    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

    $zona_rtm_tipo = "ubicacion";
    if($zona == "RTS")
    {
        $sqlAlmacen = "";
        if($almacen)
           $sqlAlmacen = " AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE id = '".$almacen."') ";

        $zona_rts = " AND IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' {$sqlAlmacen} AND tds.Cve_articulo = a.cve_articulo), 0) != 0";
    }
    else if($zona == "RTM")
    {
        $zona_rtm_tipo = "area";
    }

   $tabla_from = "V_ExistenciaGral";
   $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
   $sql_tipo = " AND vp.tipo = '{$zona_rtm_tipo}' ";
   if($zona_produccion == 'S')
   {
        $tabla_from = "V_ExistenciaProduccion";
        $tipo_prod = "";$sql_tipo = "";
   }

    $sql = "
      SELECT *, TRUNCATE(SUM(x.dcostoPromedio), $decimales_cantidad) AS costoPromedio, TRUNCATE(SUM(x.dsubtotalPromedio), $decimales_cantidad) AS subtotalPromedio  FROM(
         SELECT DISTINCT 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(e.Cuarentena = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            gr.des_gpoart as des_grupo,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S'  AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,

            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', (e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo))),e.Existencia) as cantidad,
            #IF(a.control_peso = 'S' AND a.Compuesto != 'S', e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), 0) AS cantidad_kg,

            #e.Existencia as cantidad,
            IF(a.control_peso = 'S', TRUNCATE(e.Existencia/IF(IFNULL(a.peso, 0) = 0, 1, a.peso), $decimales_cantidad), e.Existencia) AS cantidad,
            #e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)) AS cantidad_kg,
            TRUNCATE(e.Existencia, $decimales_cantidad) AS cantidad_kg,


            #IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')))), '') AS folio,
/*
             IFNULL(((SELECT GROUP_CONCAT(IFNULL(tp.Fol_OEP, tp.fol_folio) SEPARATOR ', ') FROM th_entalmacen tp WHERE tp.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND IFNULL(tp.Fol_OEP, tp.fol_folio) != ''
                AND IFNULL(((SELECT GROUP_CONCAT(DISTINCT IFNULL(Fol_OEP, fol_folio) SEPARATOR ', ') FROM th_entalmacen WHERE Fol_Folio IN 
                (
                SELECT ef.fol_folio 
                FROM td_entalmacen ef
                INNER JOIN t_cardex kf ON IFNULL(kf.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kf.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.destino = e.cve_ubicacion AND kf.id_TipoMovimiento = 2
                INNER JOIN t_cardex kp ON IFNULL(kp.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kp.cve_lote, '') = IFNULL(e.cve_lote, '') AND kp.destino = ef.cve_ubicacion AND kp.id_TipoMovimiento = 1
                WHERE IFNULL(ef.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(ef.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.origen = ef.cve_ubicacion AND kp.origen = ef.fol_folio
                AND kp.destino = kf.origen AND kf.destino = e.cve_ubicacion
                ) AND IFNULL(Fol_OEP, fol_folio) != '')), '') = IFNULL(tp.Fol_OEP, tp.fol_folio) 
             )), IFNULL((SELECT GROUP_CONCAT(IFNULL(tp.Fol_OEP, tp.fol_folio) SEPARATOR ', ') FROM th_entalmacen tp WHERE tp.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND IFNULL(tp.Fol_OEP, tp.fol_folio) != ''), '')) AS folio,
             
             IFNULL(((SELECT GROUP_CONCAT(IFNULL(tp.Proyecto, '') SEPARATOR ', ') FROM th_entalmacen tp WHERE tp.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND IFNULL(tp.Proyecto, '') != ''
                AND IFNULL(((SELECT GROUP_CONCAT(DISTINCT IFNULL(Proyecto, '') SEPARATOR ', ') FROM th_entalmacen WHERE Fol_Folio IN 
                (
                SELECT ef.fol_folio 
                FROM td_entalmacen ef
                INNER JOIN t_cardex kf ON IFNULL(kf.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kf.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.destino = e.cve_ubicacion AND kf.id_TipoMovimiento = 2
                INNER JOIN t_cardex kp ON IFNULL(kp.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kp.cve_lote, '') = IFNULL(e.cve_lote, '') AND kp.destino = ef.cve_ubicacion AND kp.id_TipoMovimiento = 1
                WHERE IFNULL(ef.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(ef.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.origen = ef.cve_ubicacion AND kp.origen = ef.fol_folio                 AND kp.destino = kf.origen AND kf.destino = e.cve_ubicacion
                ) AND IFNULL(Proyecto, '') != '')), '') = IFNULL(tp.Proyecto, '') 

             )), IFNULL((SELECT GROUP_CONCAT(IFNULL(tp.Proyecto, '') SEPARATOR ', ') FROM th_entalmacen tp WHERE tp.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND IFNULL(tp.Proyecto, '') != ''), '')) AS proyecto,
            
            IFNULL(((SELECT GROUP_CONCAT(DISTINCT IFNULL(tp.Fact_Prov, '') SEPARATOR ', ') FROM th_entalmacen tp WHERE tp.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND IFNULL(tp.Fact_Prov, '') != '' 
                AND IFNULL(((SELECT GROUP_CONCAT(DISTINCT IFNULL(Fact_Prov, '') SEPARATOR ', ') FROM th_entalmacen WHERE Fol_Folio IN 
                (
                SELECT ef.fol_folio 
                FROM td_entalmacen ef
                INNER JOIN t_cardex kf ON IFNULL(kf.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kf.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.destino = e.cve_ubicacion AND kf.id_TipoMovimiento = 2
                INNER JOIN t_cardex kp ON IFNULL(kp.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(kp.cve_lote, '') = IFNULL(e.cve_lote, '') AND kp.destino = ef.cve_ubicacion AND kp.id_TipoMovimiento = 1
                WHERE IFNULL(ef.cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(ef.cve_lote, '') = IFNULL(e.cve_lote, '') AND kf.origen = ef.cve_ubicacion AND kp.origen = ef.fol_folio
                ) AND IFNULL(Fact_Prov, '') != '')), '') = IFNULL(tp.Fact_Prov, ''))), IFNULL((SELECT GROUP_CONCAT(DISTINCT IFNULL(tp.Fact_Prov, '') SEPARATOR ', ') FROM th_entalmacen tp WHERE tp.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')) AND IFNULL(tp.Fact_Prov, '') != '' ), '')) AS factura_oc,
*/

            GROUP_CONCAT(DISTINCT IFNULL((SELECT Fol_OEP FROM th_entalmacen WHERE Fol_Folio = tr.folio_entrada), tr.folio_entrada) SEPARATOR ', ') AS folio_entrada, 
            GROUP_CONCAT(DISTINCT tr.folio_oc SEPARATOR ', ') AS folio_oc, 
            GROUP_CONCAT(DISTINCT tr.factura_ent SEPARATOR ', ') AS factura_ent, 
            GROUP_CONCAT(DISTINCT tr.factura_oc SEPARATOR ', ') AS factura_oc, 
            GROUP_CONCAT(DISTINCT tr.proyecto SEPARATOR ', ') AS proyecto,

            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,
            IFNULL(IF(p.es_cliente = 1, p.Nombre, ''), '') AS empresa_proveedor,

            IFNULL(
            (
        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
        FROM td_entalmacen 
        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
        AND Fol_Folio IN (
                    SELECT fol_folio 
                    FROM td_entalmacenxtarima 
                    WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') 
                    AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '') 
                    AND IFNULL(ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '')
                  ) 
        ORDER BY id DESC LIMIT 1
        )
        , 
        IFNULL((
        SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') 
        FROM td_entalmacen 
        WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote 
        ORDER BY id DESC LIMIT 1
        ), (SELECT DATE_FORMAT(IFNULL(Fec_Ingreso, fecha), '%d-%m-%Y') FROM t_cardex WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1)
         )
        ) AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_peso,
            IFNULL(IF(a.control_peso = 'S' AND a.Compuesto != 'S', (e.Existencia*a.peso*a.num_multiplo),e.Existencia) - IFNULL((SELECT SUM(DISTINCT t.Cantidad) AS pedidas FROM td_subpedido tsb LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo AND t.cve_lote = e.cve_lote), 0) - IFNULL(IF(IFNULL((SELECT COUNT(DISTINCT vp.Cuarentena) FROM {$tabla_from} vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 {$sql_tipo} AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(DISTINCT vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.cve_ubicacion GROUP BY vp.Cve_Articulo)), 0) - IF(a.caduca = 'S', IFNULL((SELECT SUM(DISTINCT IF(a.control_peso = 'S'  AND a.Compuesto != 'S', (vp.Existencia*a.peso*a.num_multiplo),vp.Existencia)) FROM {$tabla_from} vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND DATE_FORMAT(lo.Caducidad,'%Y-%m-%d') != '0000-00-00' AND IFNULL(lo.Caducidad, '') != '' AND DATE_FORMAT(lo.Caducidad,'%Y-%m-%d') < CURDATE() {$sql_tipo} AND vp.cve_ubicacion = e.cve_ubicacion), 0), 0), 0) AS Libre,

            #IFNULL((SELECT SUM(DISTINCT t.Cantidad) AS pedidas 
            #        FROM td_subpedido tsb 
            #        LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = tsb.Sufijo 
            #        WHERE t.claverp != '' AND t.idy_ubica = u.idy_ubica AND tsb.Cve_articulo = e.cve_articulo AND t.cve_lote = e.cve_lote), 0) AS RP,

            #SUM(IFNULL(trs.Cantidad, 0)) AS RP,
            (SELECT SUM(trs2.Cantidad) FROM t_recorrido_surtido trs2 WHERE CONVERT(trs2.Cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(trs2.cve_lote, CHAR) = CONVERT(e.cve_lote, CHAR) AND trs2.cve_almac = z.cve_almac AND e.cve_ubicacion = trs2.idy_ubica) AS RP,


            #IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM {$tabla_from} vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 {$sql_tipo} AND vp.cve_ubicacion = e.cve_ubicacion GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo AND vp.Idy_Ubica = e.cve_ubicacion GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            IFNULL((SELECT SUM(vp.Existencia) FROM {$tabla_from} vp WHERE vp.cve_articulo = e.cve_articulo AND vp.cve_lote = e.cve_lote AND vp.cve_ubicacion = e.cve_ubicacion {$sql_tipo} AND vp.Cuarentena = 1), 0) AS Prod_QA,
            IFNULL(IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM {$tabla_from} vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND vp.cve_lote = e.cve_lote AND DATE_FORMAT(l.Caducidad,'%Y-%m-%d') != '0000-00-00' AND IFNULL(lo.Caducidad, '') != '' AND DATE_FORMAT(lo.Caducidad,'%Y-%m-%d') < CURDATE() {$sql_tipo} AND vp.cve_ubicacion = e.cve_ubicacion), 0), 0), 0) AS Obsoletos,
            #truncate(a.costoPromedio,2) as costoPromedio,
            #truncate(a.costoPromedio*e.Existencia,2) as subtotalPromedio
            #IFNULL((SELECT AVG(TRUNCATE(ad.costo,2)) FROM td_aduana ad WHERE ad.Cve_articulo = a.cve_articulo AND ad.num_orden = cad.num_pedimento), 0) AS dcostoPromedio,
            #IFNULL((SELECT AVG(TRUNCATE(ad.costo*e.Existencia,2)) FROM td_aduana ad WHERE ad.Cve_articulo = a.cve_articulo AND ad.num_orden = cad.num_pedimento), 0) AS dsubtotalPromedio
            IFNULL((SELECT AVG(TRUNCATE(ad.costo,2)) FROM td_aduana ad WHERE ad.Cve_articulo = a.cve_articulo AND ad.num_orden = tr.folio_oc), 0) AS dcostoPromedio,
            IFNULL((SELECT AVG(TRUNCATE(ad.costo*e.Existencia,2)) FROM td_aduana ad WHERE ad.Cve_articulo = a.cve_articulo AND ad.num_orden = tr.folio_oc), 0) AS dsubtotalPromedio
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON CONVERT(a.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR)
            LEFT JOIN c_gpoarticulo gr ON CONVERT(gr.cve_gpoart, CHAR) = CONVERT(a.grupo, CHAR)
            LEFT JOIN c_ubicacion u ON CONCAT(u.idy_ubica, '') = CONCAT(e.cve_ubicacion, '')
            LEFT JOIN c_lotes l ON CONVERT(l.LOTE, CHAR) = CONVERT(e.cve_lote, CHAR) AND CONVERT(l.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR)
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON IFNULL(ch.clave_contenedor, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(ch.clave_contenedor, '') != ''
            LEFT JOIN tubicacionesretencion zona ON CONVERT(zona.cve_ubicacion, CHAR) = CONVERT(e.cve_ubicacion, CHAR) {$sqlCollation}
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(z.cve_almacenp, e.cve_almac) #OR ap.id = zona.cve_almacp
            LEFT JOIN rel_articulo_proveedor rap ON CONVERT(rap.Cve_Articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = rap.Id_Proveedor
            LEFT JOIN th_aduana cad ON cad.Cve_Almac = ap.clave
            LEFT JOIN t_recorrido_surtido trs ON CONVERT(trs.Cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(trs.cve_lote, CHAR) = CONVERT(e.cve_lote, CHAR) AND trs.cve_almac = z.cve_almac AND e.cve_ubicacion = trs.idy_ubica 
            LEFT JOIN t_trazabilidad_existencias tr ON CONVERT(tr.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(IFNULL(tr.cve_lote, ''), CHAR) = CONVERT(IFNULL(e.cve_lote, ''), CHAR) AND e.cve_ubicacion = tr.idy_ubica AND tr.cve_almac = e.cve_almac AND tr.idy_ubica IS NOT NULL AND tr.id_proveedor = e.Id_Proveedor AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '')
             {$sqlCliente} 
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} 
            GROUP BY codigo, clave, lote, ntarima, cve_almacen
            order by l.CADUCIDAD ASC
                )x
            WHERE 1 AND x.id_almacen = '{$almacen}' #AND x.id_proveedor IS NOT NULL
            {$sqlbl} 
            {$sqlLotes}
            ";

    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "02Error al procesar la petición: (" . mysqli_error($conn) . ") "));}


    $data = array();
    $i = 0; $suma = 0;
    $productos = array();
    $productos_i = 0;
    //while ($row = mysqli_fetch_array($res)) 
    $row = mysqli_fetch_array($res);
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
            $row['Caja'] = 0;
            $row['Piezas'] = $cantidad_restante;
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

        $peso_rp = 0; $disponible_kg = 0;
        if($row["control_peso"] == 'S')
        {
            $row["RP"] = number_format($row["RP"], $decimales_cantidad);
            $row["Libre"] = number_format($row["Libre"], $decimales_cantidad);
            //if($row['piezasxcajas'] > 1)
            $row["cantidad_kg"] = number_format($row["cantidad_kg"], $decimales_cantidad);

            if($row['peso'] > 0)
            {
                $peso_rp = $row['RP']/$row['peso'];
                $peso_rp = number_format($peso_rp, $decimales_cantidad);

                //if($row['piezasxcajas'] > 1)
                //{
                $disponible_kg = $row['Libre']*$row['peso'];
                $disponible_kg = number_format($disponible_kg, $decimales_cantidad);
                //}
            }
        }
        else 
        {
            $row["RP"] = number_format($row["RP"], 0);
            $row["Libre"] = number_format($row["Libre"], 0);
            $row["cantidad_kg"] = number_format($row["cantidad"]*$row["peso"], $decimales_cantidad);
        }


        //$data[] = $row;
        //$suma += $row['Libre']+$row['RP'];
        //$suma += $row['cantidad'];

        if(!in_array($row['clave'], $productos)) 
        {
            $productos_i++;
            array_push($productos, $row['clave']);
        }

        $lote_serie = "";
            if($row['control_lotes'] == 'S')
                $lote_serie = $row['lote'];

            if($row['control_numero_series'] == 'S')
                $lote_serie = $row['nserie'];

            $proveedor_empresa_proveedor = ($row['empresa_proveedor']==1)?$row['proveedor']:"";
                $responce = "";
                $responce = array();

                //$responce["codigo"]             = $row['codigo'];
                //$responce["tipo_ubicacion"]     = $row['tipo_ubicacion'];
                //$responce["QA"]                 = $row['QA'];
                //$responce["contenedor"]         = $row['contenedor'];
                //$responce["LP"]                 = $row['LP'];
                //$responce["clave"]              = $row['clave'];
                //$responce["descripcion"]        = $row['descripcion'];
                //$responce["des_grupo"]          = $row['des_grupo'];
                //$responce["lote_serie"]         = $lote_serie;
                //$responce["caducidad"]          = $row['caducidad'];
                $responce["cantidad"]           = $row['cantidad'];
                $responce["cantidad_kg"]        = $row['cantidad_kg'];
                $responce["peso_rp"]            = $peso_rp;
                $responce["RP"]                 = $row['RP'];
                $responce["Prod_QA"]            = $row['Prod_QA'];
                $responce["Obsoletos"]          = $row['Obsoletos'];
                $responce["Pallet"]             = $row['Pallet'];
                $responce["Caja"]               = $row['Caja'];
                $responce["Piezas"]             = $row["Piezas"];
                $responce["disponible_pz"]      = abs($row['cantidad'] - $peso_rp - $row['Prod_QA']);
                $responce["disponible_kg"]      = abs($row['cantidad_kg'] - $row['RP'] - $row['Prod_QA']);
                $responce["fecha_ingreso"]      = $row['fecha_ingreso'];
                $responce["folio"]              = $row['folio_entrada'];
                $responce["proyecto"]              = $row['proyecto'];
                $responce["factura_oc"]              = $row['factura_ent'];
                $responce["costoPromedio"]      = number_format($row['costoPromedio'],$decimales_costo);
                $responce["subtotalPromedio"]   = number_format($row['subtotalPromedio'],$decimales_costo);
                //$responce["zona_recepcion"]     = $row['zona_recepcion'];
                //$responce["proveedor"]          = $row['proveedor'];
                //$responce["empresa_proveedor"]  = $proveedor_empresa_proveedor;
                //$responce["zona"]               = $row['zona'];
                //$responce["almacen"]            = $row['almacen'];

    echo json_encode(array( "cantidad" => $responce["cantidad"], 
                            "cantidad_kg" => $responce["cantidad_kg"], 
                            "peso_rp" => $responce["peso_rp"], 
                            "RP" => $responce["RP"], 
                            "Prod_QA" => $responce["Prod_QA"], 
                            "Obsoletos" => $responce["Obsoletos"], 
                            "Pallet" => $responce["Pallet"], 
                            "Caja" => $responce["Caja"], 
                            "Piezas" => $responce["Piezas"], 
                            "disponible_pz" => $responce["disponible_pz"], 
                            "disponible_kg" => $responce["disponible_kg"], 
                            "costoPromedio" => $responce["costoPromedio"], 
                            "fecha_ingreso" => $responce["fecha_ingreso"], 
                            "folio" => $responce["folio"], 
                            "proyecto" => $responce["proyecto"], 
                            "factura_oc" => $responce["factura_oc"], 
                            "subtotalPromedio" => $responce["subtotalPromedio"],
                            "SQL" => $sql
                        ));
}

if ($_POST['action'] === 'loadDetalle') 
{
  $page = $_POST['page']; // get the requested page
  $limit = $_POST['rows']; // get how many rows we want to have into the grid
  $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
  $sord = $_POST['sord']; // get the direction
  $almacen = $_POST['almacen'];
  $bl      = $_POST['bl'];
  $clave   = $_POST['clave'];
  $lote    = $_POST['lote'];
  $lp      = $_POST['lp'];
  $idy_ubica      = $_POST['idy_ubica'];


  $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  if(!$sidx)
  {
    $sidx =1;
  }

  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


  $sql1 = "
SELECT DISTINCT u.CodigoCSD AS ubicacion, ch.CveLP AS LP, cj.CveLP AS Caja, a.cve_articulo, a.des_articulo, ec.cve_lote, 
       COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(ec.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
       ec.PiezasXCaja
FROM ts_existenciacajas ec
LEFT JOIN c_almacenp al ON al.id = ec.Cve_Almac
LEFT JOIN c_almacen z ON z.cve_almacenp = al.id
LEFT JOIN c_charolas ch ON ec.nTarima = ch.IDContenedor #AND ch.cve_almac = al.id  
LEFT JOIN c_charolas cj ON ec.Id_Caja = cj.IDContenedor #AND cj.cve_almac = al.id  #AND cj.tipo = 'Caja'
LEFT JOIN c_ubicacion u ON ec.idy_ubica = u.idy_ubica   #AND  u.cve_almac = z.cve_almac
LEFT JOIN c_articulo a ON ec.cve_articulo = a.cve_articulo
LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Almac = al.id AND ra.Cve_Articulo = a.cve_articulo
LEFT JOIN c_lotes l ON l.cve_articulo = ra.Cve_Articulo AND l.Lote = ec.cve_lote
WHERE #ec.Cve_Almac = $almacen AND u.idy_ubica = '$idy_ubica' AND 
IFNULL(ch.CveLP, '') = '$lp' AND u.CodigoCSD IS NOT NULL #AND a.Cve_Articulo = '$clave' AND ec.cve_lote = '$lote'
      ";
if($lp == '')
  $sql1 = "
SELECT DISTINCT u.CodigoCSD AS ubicacion, ch.CveLP AS LP, cj.CveLP AS Caja, a.cve_articulo, a.des_articulo, ec.cve_lote, 
       COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(ec.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
       ec.PiezasXCaja
FROM ts_existenciacajas ec
LEFT JOIN c_almacenp al ON al.id = ec.Cve_Almac
LEFT JOIN c_almacen z ON z.cve_almacenp = al.id
LEFT JOIN c_charolas ch ON ch.cve_almac = al.id AND ec.nTarima = ch.IDContenedor 
LEFT JOIN c_charolas cj ON cj.cve_almac = al.id AND ec.Id_Caja = cj.IDContenedor #AND cj.tipo = 'Caja'
LEFT JOIN c_ubicacion u ON u.cve_almac = z.cve_almac AND ec.idy_ubica = u.idy_ubica
LEFT JOIN c_articulo a ON ec.cve_articulo = a.cve_articulo
LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Almac = al.id AND ra.Cve_Articulo = a.cve_articulo
LEFT JOIN c_lotes l ON l.cve_articulo = ra.Cve_Articulo AND l.Lote = ec.cve_lote
WHERE ec.Cve_Almac = $almacen AND u.idy_ubica = '$idy_ubica' AND 
u.CodigoCSD IS NOT NULL AND a.Cve_Articulo = '$clave' AND ec.cve_lote = '$lote'
      ";

  // hace una llamada previa al procedimiento almacenado Lis_Facturas
  if (!($res = mysqli_query($conn, $sql1))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $count = mysqli_num_rows($res);
  $sql1 .= " LIMIT $start, $limit ";
  if (!($res = mysqli_query($conn, $sql1))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }


  if( $count >0 ) 
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
  $responce->sql = $sql1;

  $arr = array();
  $i = 0;
  $clave_actual = ""; $num_rows = 0; 
  $lote_actual = ""; $lote_comp = "";
  $clave_actual_pedido = "";

  $cant_pedida = 0; $cant_recibida = 0; $diferencia = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    //$row = array_map("utf8_encode", $row );
    extract($row);

    $responce->rows[$i]['id']=$i;
    $responce->rows[$i]['cell']=array(
                                      $ubicacion, 
                                      $LP,
                                      $Caja,
                                      $cve_articulo,
                                      $des_articulo, 
                                      $cve_lote,
                                      $caducidad,
                                      $PiezasXCaja
                                     );
    $i++;
  }
  echo json_encode($responce);
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


if( $_POST['action'] == 'EditarProyecto' ) 
{
    $idy_ubica_pry = $_POST['idy_ubica_pry'];
    $articulo_pry = $_POST['articulo_pry'];
    $lote_pry = $_POST['lote_pry'];
    $ntarima_pry = $_POST['ntarima_pry'];
    $id_proveedor_pry = $_POST['id_proveedor_pry'];
    $almacen_pry = $_POST['almacen_pry'];
    $proyecto = $_POST['proyecto'];


  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


  $sql = "SELECT * FROM t_trazabilidad_existencias WHERE cve_almac = '$almacen_pry' AND idy_ubica = '$idy_ubica_pry' AND cve_articulo = '$articulo_pry' AND cve_lote = '$lote_pry' AND IFNULL(ntarima, '') = '$ntarima_pry' AND id_proveedor = '$id_proveedor_pry'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $count = mysqli_num_rows($res);

  $sql_edit = "UPDATE t_trazabilidad_existencias SET proyecto = '$proyecto' WHERE cve_almac = '$almacen_pry' AND idy_ubica = '$idy_ubica_pry' AND cve_articulo = '$articulo_pry' AND cve_lote = '$lote_pry' AND IFNULL(ntarima, '') = '$ntarima_pry' AND id_proveedor = '$id_proveedor_pry'";
  if($count == 0)
  {
    $sql_edit = "INSERT INTO t_trazabilidad_existencias(cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ('$almacen_pry', '$idy_ubica_pry', '$articulo_pry', '$lote_pry', 0, '$id_proveedor_pry', '', '', '', '', '$proyecto', 2)";
    if($ntarima_pry)
       $sql_edit = "INSERT INTO t_trazabilidad_existencias(cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ('$almacen_pry', '$idy_ubica_pry', '$articulo_pry', '$lote_pry', 0, '$ntarima_pry', '$id_proveedor_pry', '', '', '', '', '$proyecto', 2)";
  }

  if (!($res = mysqli_query($conn, $sql_edit))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

    echo "ok";
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