<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $folio = $_GET['folio'];

    $sheet1 = "Reporte de Entradas #".$folio;
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
?>
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Proveedor</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Proveedor</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio OC</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Factura OC</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio Entrada</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Factura Entrada</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Factura Articulo</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>License Plate</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Articulo</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Alterna</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripcion Articulo</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote|Serie</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tipo de Protocolo</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Grupo de Artículo</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>UM artículo</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha de Registro</div>
    <div cell="S1" excelStyle='<?php echo json_encode($styleArray); ?>'>Hora de Registro</div>
    <div cell="T1" excelStyle='<?php echo json_encode($styleArray); ?>'>Estado Serial</div>
    <div cell="U1" excelStyle='<?php echo json_encode($styleArray); ?>'>Valor Unitario (Costo por Unidad)</div>
    <div cell="V1" excelStyle='<?php echo json_encode($styleArray); ?>'>Valor Total (Costo Total)</div>
    <div cell="W1" excelStyle='<?php echo json_encode($styleArray); ?>'>Proyecto</div>
    <div cell="X1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ubicación</div>
    <div cell="Y1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario</div>
    <div cell="Z1" excelStyle='<?php echo json_encode($styleArray); ?>'>Observaciones</div>
    <div cell="AA1" excelStyle='<?php echo json_encode($styleArray); ?>'>Numero de Unidad</div>
    <div cell="AB1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave transportadora</div>
    <div cell="AC1" excelStyle='<?php echo json_encode($styleArray); ?>'>Placa</div>
    <div cell="AD1" excelStyle='<?php echo json_encode($styleArray); ?>'>Sello/Precinto</div>
    <div cell="AE1" excelStyle='<?php echo json_encode($styleArray); ?>'>ID Chofer</div>
    <div cell="AF1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Conductor</div>
    <div cell="AG1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha de Transporte</div>
    <div cell="AH1" excelStyle='<?php echo json_encode($styleArray); ?>'>Hora de Transporte</div>
    <div cell="AI1" excelStyle='<?php echo json_encode($styleArray); ?>'>Declaración Importación</div>
    <div cell="AJ1" excelStyle='<?php echo json_encode($styleArray); ?>'>Documento de Transporte Internacional</div>
    <div cell="AK1" excelStyle='<?php echo json_encode($styleArray); ?>'>Destino/Dirección</div>
    <div cell="AL1" excelStyle='<?php echo json_encode($styleArray); ?>'>DO</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sql = "
        SELECT 
       p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, tr.folio_oc AS folio_oc, tr.factura_oc AS factura_oc, 
       tr.folio_entrada AS folio_entrada, tr.factura_ent AS factura_entrada, IFNULL(dt.factura_articulo, '') AS factura_articulo, IFNULL(ch.CveLP, '') AS LP, 
       tr.cve_articulo AS clave_articulo, a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, IFNULL(tr.cve_lote, '') AS lote, 
       IFNULL(lt.Caducidad, '') AS Caducidad, prot.descripcion AS tipo_de_protocolo, 
       ga.des_gpoart AS grupo_articulo, (tr.cantidad) AS cantidad, um.des_umed AS um_articulo, 
       DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
       '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*tr.cantidad AS valor_total, tr.proyecto AS Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
       etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
       etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
       '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
FROM t_trazabilidad_existencias tr
#LEFT JOIN td_aduanaxtarima tdt_oc ON tdt_oc.Cve_Articulo = dt_oc.cve_articulo AND IFNULL(tdt_oc.Cve_Lote, '') = IFNULL(dt_oc.Cve_Lote, '') AND tdt_oc.Num_Orden = dt_oc.num_orden
#LEFT JOIN th_aduana oc ON oc.num_pedimento = dt_oc.num_orden
LEFT JOIN c_articulo a ON a.cve_articulo = tr.cve_articulo
LEFT JOIN th_entalmacen ent ON ent.id_ocompra = tr.folio_oc AND tr.folio_entrada = ent.Fol_Folio
LEFT JOIN td_entalmacen dt ON dt.Fol_folio = tr.folio_entrada AND tr.cve_articulo = dt.cve_articulo AND IFNULL(tr.cve_lote, '') = IFNULL(dt.cve_lote, '')
#LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = dt.cve_articulo AND IFNULL(tdt.cve_lote, '') = IFNULL(dt.cve_lote, '') AND dt.fol_folio = tdt.fol_folio
LEFT JOIN c_lotes lt ON lt.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(lt.lote), '') = IFNULL(TRIM(tr.cve_lote), '')
LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = tr.folio_entrada
LEFT JOIN c_charolas ch ON ch.IDContenedor = tr.ntarima
LEFT JOIN td_aduana dt_oc ON tr.folio_entrada = $folio AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL AND IFNULL(dt_oc.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '')
LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
WHERE tr.folio_entrada = $folio 
AND tr.idy_ubica IS NOT NULL
    ";
*/

    $sql = "SELECT DISTINCT p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, oc.num_pedimento AS folio_oc, oc.Factura AS factura_oc, 
       ent.Fol_Folio AS folio_entrada, ent.Fol_OEP AS factura_entrada, dt.factura_articulo AS factura_articulo, IFNULL(tdt_oc.ClaveEtiqueta, '') AS LP, 
       a.cve_articulo AS clave_articulo,
       a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, IFNULL(dt.cve_lote, '') AS lote, lt.Caducidad, prot.descripcion AS tipo_de_protocolo, 
       ga.des_gpoart AS grupo_articulo, IFNULL(tdt_oc.Cantidad, dt_oc.cantidad) AS cantidad, um.des_umed AS um_articulo, 
       DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
       '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*IFNULL(tdt_oc.Cantidad, dt_oc.cantidad) AS valor_total, ent.Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
       etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
       etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
       '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
FROM td_aduana dt_oc 
LEFT JOIN td_aduanaxtarima tdt_oc ON tdt_oc.Cve_Articulo = dt_oc.cve_articulo AND IFNULL(tdt_oc.Cve_Lote, '') = IFNULL(dt_oc.Cve_Lote, '') AND tdt_oc.Num_Orden = dt_oc.num_orden
LEFT JOIN th_aduana oc ON oc.num_pedimento = dt_oc.num_orden
LEFT JOIN c_articulo a ON a.cve_articulo = dt_oc.cve_articulo
LEFT JOIN th_entalmacen ent ON ent.id_ocompra = oc.num_pedimento
LEFT JOIN td_entalmacen dt ON dt.Fol_folio = ent.Fol_folio AND a.cve_articulo = dt.cve_articulo
LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = dt.cve_articulo AND IFNULL(tdt.cve_lote, '') = IFNULL(dt.cve_lote, '') AND dt.fol_folio = tdt.fol_folio
LEFT JOIN c_lotes lt ON lt.cve_articulo = a.cve_articulo AND lt.lote = dt.cve_lote
LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = ent.Fol_Folio
LEFT JOIN c_charolas ch ON ch.CveLP = tdt_oc.ClaveEtiqueta
LEFT JOIN t_trazabilidad_existencias tr ON tr.folio_entrada = ent.fol_folio AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tdt_oc.cve_lote, dt_oc.cve_lote) = IFNULL(tr.cve_lote, '') AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL
LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
WHERE ent.Fol_Folio = $folio 
AND dt_oc.num_orden = oc.num_pedimento AND dt.cve_articulo = dt_oc.cve_articulo 
AND a.cve_articulo = dt_oc.cve_articulo AND IFNULL(tdt.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '') ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $clave_proveedor; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $nombre_proveedor; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $folio_oc; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $factura_oc; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $folio_entrada; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $factura_entrada; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $factura_articulo; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $clave_articulo; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $clave_alterna; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo $lote; ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo $Caducidad; ?></div>
        <div cell="N<?php echo $i; ?>"><?php echo $tipo_de_protocolo; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo $grupo_articulo; ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $cantidad; ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo $um_articulo; ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo $fecha_entrada; ?></div>
        <div cell="S<?php echo $i; ?>"><?php echo $hora_entrada; ?></div>
        <div cell="T<?php echo $i; ?>"><?php echo $estado_serial; ?></div>
        <div cell="U<?php echo $i; ?>"><?php echo $valor_unitario; ?></div>
        <div cell="V<?php echo $i; ?>"><?php echo $valor_total;//($valor_unitario*$cantidad); ?></div>
        <div cell="W<?php echo $i; ?>"><?php echo $Proyecto; ?></div>
        <div cell="X<?php echo $i; ?>"><?php echo $ubicacion; ?></div>
        <div cell="Y<?php echo $i; ?>"><?php echo $usuario; ?></div>
        <div cell="Z<?php echo $i; ?>"><?php echo $Observaciones; ?></div>
        <div cell="AA<?php echo $i; ?>"><?php echo $num_unidad; ?></div>
        <div cell="AB<?php echo $i; ?>"><?php echo $clave_transportadora; ?></div>
        <div cell="AC<?php echo $i; ?>"><?php echo $placa; ?></div>
        <div cell="AD<?php echo $i; ?>"><?php echo $Sello; ?></div>
        <div cell="AE<?php echo $i; ?>"><?php echo $id_chofer; ?></div>
        <div cell="AF<?php echo $i; ?>"><?php echo $nombre_conductor; ?></div>
        <div cell="AG<?php echo $i; ?>"><?php echo $fecha_transporte; ?></div>
        <div cell="AH<?php echo $i; ?>"><?php echo $hora_transporte; ?></div>
        <div cell="AI<?php echo $i; ?>"><?php echo $declaracion_importacion; ?></div>
        <div cell="AJ<?php echo $i; ?>"><?php echo $documento_transporte_internacional; ?></div>
        <div cell="AK<?php echo $i; ?>"><?php echo $destino_direccion; ?></div>
        <div cell="AL<?php echo $i; ?>"><?php echo $DO; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>