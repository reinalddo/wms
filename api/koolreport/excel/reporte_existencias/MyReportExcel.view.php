<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $articulo = $_GET["articulo"];
    $contenedor = $_GET["contenedor"];
    $almacen = $_GET["almacen"];
    $zona = $_GET["zona"];
    $cve_cliente = $_GET["cve_cliente"];
    $cve_proveedor = $_GET["cve_proveedor"];
    $proveedor = $_GET["proveedor"];
    $bl = $_GET["bl"];
    $lp = $_GET["lp"];
    $grupo = $_GET["grupo"];
    $clasificacion = $_GET["clasificacion"];
    $art_obsoletos = $_GET["art_obsoletos"];
    $refWell = $_GET['refWell'];
    $pedimentoW = $_GET['pedimentoW'];
    $picking = $_GET['picking'];
    $mostrar_folios_excel_existencias = $_GET['mostrar_folios_excel_existencias'];
    $existencia_cajas = $_GET['existencia_cajas'];
    $lote = $_GET['lote'];
    $factura = $_GET['factura'];
    $proyecto_existencias = $_GET['proyecto_existencias'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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


    $sql_existencia_cajas = "LEFT";
    if($existencia_cajas == 1)
        $sql_existencia_cajas = "INNER";

    $sqlPicking = ($picking != "") ? "AND IFNULL(u.picking, 'N') = '{$picking}'" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = ' WHERE id = "'.$almacen.'" ';
/*
    $sql1 = 'SELECT * FROM c_almacenp $sqlAlmacen';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];
*/
    $sql_folios = ""; $sql_foliox = ""; $group_mostrar_folios = "";$left_join_folios = ""; 
    $field_folios = " a.cve_articulo as clave, ";
    if($mostrar_folios_excel_existencias) 
    {
        //$sql_folios = " IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')))), '')  AS folio, ";

        $field_folios = " IF((tdt.fol_folio IS NOT NULL AND IFNULL(e.Cve_Contenedor, '') != '') OR (tdt.fol_folio IS NULL AND IFNULL(e.Cve_Contenedor, '') = ''), a.cve_articulo, e.cve_articulo) AS clave, ";
        $left_join_folios = " LEFT JOIN td_entalmacenxtarima tdt ON IFNULL(tdt.fol_folio, '') = IFNULL(th.Fol_folio, '') AND IFNULL(tdt.ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(tdt.Cve_Articulo, '') = IFNULL(td.cve_articulo, '') AND IFNULL(ta.Cve_Almac, '') = IFNULL(ap.clave, '') AND IFNULL(tdt.Cve_Lote, '') = IFNULL(e.cve_lote, '') AND IFNULL(th.Fol_OEP, '') =  IFNULL(ta.Factura, '') "; 
        //$sql_folios = " IFNULL(ta.Factura, '')  AS folio, ";
        $sql_folios = " IFNULL(tr.factura_ent, '')  AS folio, ";
        //$sql_foliox = ", x.folio";
        $sql_foliox = " , IF(IFNULL(x.folio, '') = '', '', GROUP_CONCAT(DISTINCT NULLIF(x.folio, '') SEPARATOR ', ')) AS folio ";
        $group_mostrar_folios = ", folio";
    }
 
    $sql_proyecto = " IFNULL(tr.proyecto, '') as proyecto, ";
    $sql_proyectox = ", IF(IFNULL(x.proyecto, '') = '', '', GROUP_CONCAT(DISTINCT NULLIF(x.proyecto, '') SEPARATOR ', ')) AS proyecto";
    $group_mostrar_proyecto = ", proyecto";

    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    if($zona_produccion == 'S')
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    $sqlFactura = !empty($factura) ? " AND IFNULL(tr.factura_ent, '' ) LIKE '%$factura%'" : "";
    $sqlProyecto = !empty($proyecto_existencias) ? " AND IFNULL(tr.proyecto, '' ) LIKE '%$proyecto_existencias%'" : "";

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


    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";

    $sqlProveedor = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
    $sqlCliente = !empty($cve_cliente) ? "INNER JOIN c_cliente c ON c.ID_Proveedor = p.ID_Proveedor AND e.ID_Proveedor = c.ID_Proveedor AND c.Cve_Clte = '{$cve_cliente}'" : "";
    $sqlProveedor2 = !empty($proveedor) ? "AND x.id_proveedor = '{$proveedor}'" : "";
  
    $sqlLotes = !empty($lotes) ? "AND x.lote like '%{$lotes}%'" : "";

    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";
    $sqlLP = !empty($lp) ? "AND x.LP like '%{$lp}%'" : "";

    $sqlGrupo = !empty($grupo) ? "AND gr.cve_gpoart = '{$grupo}'" : "";
    $sqlClasif = !empty($clasificacion) ? "AND cl.cve_sgpoart = '{$clasificacion}'" : "";


    $sqlbl_search = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : "";
    $sqlLP_search = !empty($lp) ? "AND ch.CveLP like '%{$lp}%'" : "";
    

    $sqlproveedor_tipo = !empty($cve_proveedor) ? "AND x.id_proveedor = {$cve_proveedor}" : "";
    $sqlproveedor_tipo2 = !empty($cve_proveedor) ? "AND e.ID_Proveedor = {$cve_proveedor}" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];


    $SQLrefWell = "";
    if($refWell && $instancia == 'welldex')
        $SQLrefWell = " AND ta.recurso LIKE '%$refWell%' ";

    $SQLpedimentoW = "";
    if($pedimentoW && $instancia == 'welldex')
        $SQLpedimentoW = " AND ta.Pedimento LIKE '%$pedimentoW%' ";


    $sqlCollation = "";$sqlEliminaraduanaTemporalmente = "";
    if($instancia == 'foam')
    {
        $sqlCollation = " COLLATE utf8mb4_unicode_ci ";
        $sqlEliminaraduanaTemporalmente = " AND 0 ";
    }

    $field_bl = " u.CodigoCSD AS codigo, ";
    if($instancia == 'asl' || $instancia == 'dicoisa')// || $instancia == 'oslo'
        $field_bl = " REPLACE(u.CodigoCSD, '-', '_') AS codigo, ";


   $tabla_from = "V_ExistenciaGral";
   $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
   $field_folio_ot = "''";
   $field_NCaja = "''";
   $SQL_FolioOT = "";
   if($zona_produccion == 'S' && $num_produccion < 2)
   {
        $tabla_from = "V_ExistenciaProduccion";
        $tipo_prod = "";

       $field_folio_ot = "IFNULL(op.Folio_Pro, '')";
       $field_NCaja = "IFNULL(cm.NCaja, '')";
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

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " (e.cve_almac = '{$almacen}')  AND ";//OR zona.cve_almacp = '{$almacen}'

$sql = "SET NAMES utf8mb4;";
mysqli_query($conn, $sql);

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];

    $sql = "
      SELECT x.cve_almacen, x.id_almacen, x.almacen, x.folio_OT, x.NCaja, x.fecha_ingreso, x.zona, x.codigo, x.RP, x.cve_ubicacion, x.zona_recepcion, x.QA, x.contenedor, x.LP, x.clave, x.clave_alterna, x.descripcion, x.des_grupo, x.des_clasif, x.cajasxpallets, x.piezasxcajas, x.Pallet, x.Caja, x.Piezas, x.control_lotes, x.control_numero_series, x.lote, x.caducidad, x.nserie, x.peso, (x.cantidad) AS cantidad, (x.cantidad_kg) AS cantidad_kg, x.id_proveedor, (x.proveedor) AS proveedor, (x.empresa_proveedor) AS empresa_proveedor, x.tipo_ubicacion, x.control_abc, x.clasif_abc, x.um, x.control_peso, x.codigo_barras_pieza, 
            x.codigo_barras_caja, x.codigo_barras_pallet, x.referencia_well, x.pedimento_well {$sql_foliox} {$sql_proyectox} FROM(
         SELECT DISTINCT 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            {$field_folio_ot} AS folio_OT,
            {$field_NCaja} AS NCaja,
            z.des_almac as zona,
            {$field_bl}
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(IFNULL(e.Cuarentena, 0) = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            {$field_folios} 
            IFNULL(a.cve_alt, '') as clave_alterna,
            IFNULL(a.cve_codprov, '') as codigo_barras_pieza, 
            IFNULL(a.barras2, '') as codigo_barras_caja, 
            IFNULL(a.barras3, '') as codigo_barras_pallet, 
            IFNULL(a.des_articulo, '') as descripcion,
            IFNULL(trs.Cantidad, 0) AS RP,
            IFNULL(gr.des_gpoart, '') as des_grupo,
            IFNULL(cl.cve_sgpoart, '') as des_clasif,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            ta.recurso as referencia_well,
            ta.Pedimento as pedimento_well,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,
            IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', TRUNCATE(e.Existencia, $decimales_cantidad), e.Existencia) as cantidad,
            TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), $decimales_cantidad) AS cantidad_kg,
            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,
            IFNULL(DATE_FORMAT(td.fecha_fin, '%d-%m-%Y'), '') AS fecha_ingreso,
            IFNULL(poc.Nombre, '') AS empresa_proveedor,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,
            {$sql_folios}
            {$sql_proyecto}
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
            LEFT JOIN tubicacionesretencion zona ON zona.cve_ubicacion = e.cve_ubicacion {$sqlCollation}
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(e.cve_almac, z.cve_almacenp) #OR ap.id = zona.cve_almacp
            LEFT JOIN t_recorrido_surtido trs ON trs.Cve_articulo = e.cve_articulo AND trs.cve_lote = e.cve_lote AND trs.cve_almac = z.cve_almac AND e.cve_ubicacion = trs.idy_ubica 
            {$sql_existencia_cajas} JOIN ts_existenciacajas ec ON ec.idy_ubica = e.cve_ubicacion AND e.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(e.cve_lote, '')  AND e.cve_almac = ec.cve_almac
            #AND IFNULL(ec.nTarima, '') = IFNULL(ch.IDContenedor, '')
             {$SQL_FolioOT} 
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor)
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.cve_lote $sqlEliminaraduanaTemporalmente
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio $sqlEliminaraduanaTemporalmente #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra $sqlEliminaraduanaTemporalmente
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor
            LEFT JOIN t_trazabilidad_existencias tr ON CONVERT(tr.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(IFNULL(tr.cve_lote, ''), CHAR) = CONVERT(IFNULL(e.cve_lote, ''), CHAR) AND e.cve_ubicacion = tr.idy_ubica AND tr.cve_almac = e.cve_almac AND tr.idy_ubica IS NOT NULL AND tr.id_proveedor = e.Id_Proveedor AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '')
            {$left_join_folios}
            {$sqlCliente}
            #LEFT JOIN td_entalmacenxtarima ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.ClaveEtiqueta = ch.CveLP AND ent.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = ap.clave AND (tipo = 'RL' OR tipo = 'OC'))
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlFactura} {$sqlProyecto}
                {$sqlProveedor} {$sqlGrupo} {$sqlClasif} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} {$sqlPicking}
                $zona_rts

            #GROUP BY id_proveedor
            #GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie

            ORDER BY a.des_articulo, folio_OT, (NCaja+0) ASC

                )x
            #where x.lote != '--'
            WHERE 1 AND x.id_almacen = '{$almacen}' #AND x.id_proveedor IS NOT NULL
            {$sqlbl} 
            {$sqlLP} 
            {$sqlLotes} 
            {$sqlproveedor_tipo} 
            {$sqlProveedor2}
            GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie, id_proveedor #{$group_mostrar_folios} {$group_mostrar_proyecto}
            ";

    $sheet1 = "Existencias Ubicación";
?>
<meta charset="UTF-8">
<meta name="description" content="Free Web tutorials">
<meta name="keywords" content="Excel,HTML,CSS,XML,JavaScript">
<meta name="creator" content="John Doe">
<meta name="subject" content="subject1">
<meta name="title" content="title1">
<meta name="category" content="category1">

<div sheet-name="<?php echo $sheet1; ?>">



    <?php
    $styleArray = [
        'font' => [
            'name' => 'Calibri', //'Verdana', 'Arial'
            'size' => 14,
            'bold' => true
        ]
    ];

if($instancia == 'welldex')
{
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Codigo BL</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pallet|Cont</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>License Plate (LP)</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Alterna</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>CB Pieza</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clasificacion</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripcion</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote | Serie</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad Medida</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>RP</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Prod QA</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Disponible</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Ingreso</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio OC</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proyecto</div>
    <div cell="S1" excelStyle='<?php echo json_encode($styleArray); ?>'>Grupo</div>
    <div cell="T1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proveedor</div>
    <div cell="U1" excelStyle='<?php echo json_encode($styleArray); ?>'>Referencia Well</div>
    <div cell="V1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pedimento Wel</div>
<?php
}
else if($mostrar_folios_excel_existencias)
{
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Codigo BL</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pallet|Cont</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>License Plate (LP)</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Alterna</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>CB Pieza</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clasificacion</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripcion</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote | Serie</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad Medida</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>RP</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Prod QA</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Disponible</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Ingreso</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio OC</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proyecto</div>
    <div cell="S1" excelStyle='<?php echo json_encode($styleArray); ?>'>Grupo</div>
    <div cell="T1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proveedor</div>
<?php
}
else 
{
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Codigo BL</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pallet|Cont</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>License Plate (LP)</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Alterna</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>CB Pieza</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clasificacion</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripcion</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote | Serie</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad Medida</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>RP</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Prod QA</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Disponible</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Ingreso</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proyecto</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Grupo</div>
    <div cell="S1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proveedor</div>
<?php 
}

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    $articulo_anterior = ""; $imprimir = true;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);
if($instancia == 'welldex')
{
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $codigo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $contenedor; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $clave_alterna; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $codigo_barras_pieza; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $des_clasif; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $descripcion; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $lote; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $caducidad; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $um; ?></div>
        <div cell="L<?php echo $i; ?>"><?php if($QA == 'No') echo $cantidad; else echo ""; ?></div>
        <div cell="M<?php echo $i; ?>"><?php if($RP != 0) echo $RP; else echo ""; ?></div>
        <div cell="N<?php echo $i; ?>"><?php if($QA == 'Si') echo $cantidad; else echo ""; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo $cantidad-$RP; ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $fecha_ingreso; ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo $folio; ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo $proyecto; ?></div>
        <div cell="S<?php echo $i; ?>"><?php echo $des_grupo; ?></div>
        <div cell="T<?php echo $i; ?>"><?php echo $proveedor; ?></div>
        <div cell="U<?php echo $i; ?>"><?php echo $referencia_well; ?></div>
        <div cell="V<?php echo $i; ?>"><?php echo $pedimento_well; ?></div>
        <?php 
}
else if($mostrar_folios_excel_existencias)
{
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $codigo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $contenedor; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $clave_alterna; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $codigo_barras_pieza; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $des_clasif; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $descripcion; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $lote; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $caducidad; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $um; ?></div>
        <div cell="L<?php echo $i; ?>"><?php if($QA == 'No') echo $cantidad; else echo ""; ?></div>
        <div cell="M<?php echo $i; ?>"><?php if($RP != 0) echo $RP; else echo ""; ?></div>
        <div cell="N<?php echo $i; ?>"><?php if($QA == 'Si') echo $cantidad; else echo ""; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo $cantidad-$RP; ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $fecha_ingreso; ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo $folio; ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo $proyecto; ?></div>
        <div cell="S<?php echo $i; ?>"><?php echo $des_grupo; ?></div>
        <div cell="T<?php echo $i; ?>"><?php echo $proveedor; ?></div>
        <?php 
}
else
{
?>
        <div cell="A<?php echo $i; ?>"><?php echo $codigo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $contenedor; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $clave_alterna; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $codigo_barras_pieza; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $des_clasif; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $descripcion; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $lote; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $caducidad; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $um; ?></div>
        <div cell="L<?php echo $i; ?>"><?php if($QA == 'No') echo $cantidad; else echo ""; ?></div>
        <div cell="M<?php echo $i; ?>"><?php if($RP != 0) echo $RP; else echo ""; ?></div>
        <div cell="N<?php echo $i; ?>"><?php if($QA == 'Si') echo $cantidad; else echo ""; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo $cantidad-$RP; ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $fecha_ingreso; ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo $proyecto; ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo $des_grupo; ?></div>
        <div cell="S<?php echo $i; ?>"><?php echo $proveedor; ?></div>
<?php 
}
        $i++;

    }
  ?>
        <?php /* ?><div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>

    
</div>