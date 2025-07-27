<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Inventario";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Articulo</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote|Serie</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pallet|Contenedor</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>License Plate (LP)</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Movimiento</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Origen</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Destino</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>BL</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen= $_GET['almacen'];
    $lote= $_GET['lote'];
    $cve_articulo= $_GET['cve_articulo'];
    $fecha_inicio= $_GET['fechaI'];
    $fecha_final= $_GET['fechaF'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    $SQLCriterio = "";
    if($_criterio)
    {
        $SQLCriterio = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR uo.CodigoCSD LIKE '%".$_criterio."%' OR ch.clave_contenedor LIKE '%".$_criterio."%' OR ch.CveLP LIKE '%".$_criterio."%' OR rd.desc_ubicacion LIKE '%".$_criterio."%' OR m.nombre LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%' OR rd.cve_ubicacion LIKE '%".$_criterio."%') ";
    }

    $SQLArticulo = "";
    if($cve_articulo)
    {
        $SQLArticulo = " AND k.cve_articulo = '".$cve_articulo."' ";
    }

    $SQLLote = "";
    if($lote)
    {
        $SQLLote = " AND k.cve_lote = '".$lote."' ";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $SQLFecha = "";
    if($fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha BETWEEN STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y') AND STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
    }
    else if(!$fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha <= STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
    }
    else if($fecha_inicio && !$fecha_final)
    {
        $SQLFecha = " AND k.fecha >= STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y')";
    }


    $sql = "SELECT DISTINCT k.cve_articulo AS id_articulo, a.des_articulo AS des_articulo, k.cve_lote AS cve_lote, 
                IFNULL(IF(lote.Caducidad != '0000-00-00',DATE_FORMAT(lote.Caducidad, '%d-%m-%Y'), ''),'') AS Caducidad, 
                DATE_FORMAT(k.fecha, '%d-%m-%Y') as fecha, IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), 'RTM') AS origen, 
                IFNULL(ch.clave_contenedor,'') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(uo.CodigoCSD, '') AS bl, k.cantidad,
                IFNULL(ud.CodigoCSD, rd.desc_ubicacion) AS destino, k.cantidad, m.nombre AS movimiento, k.cve_usuario, al.nombre AS almacen 
                FROM t_cardex k 
                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                LEFT JOIN c_ubicacion uo ON uo.idy_ubica = k.origen
                LEFT JOIN tubicacionesretencion ro ON ro.cve_ubicacion = k.origen
                LEFT JOIN c_ubicacion ud ON ud.idy_ubica = k.destino
                LEFT JOIN ts_existenciatarima ext ON ext.cve_articulo = k.cve_articulo AND ext.lote = k.cve_lote AND ext.idy_ubica = k.destino 
                LEFT JOIN c_charolas ch ON ch.IDContenedor = ext.ntarima
                LEFT JOIN tubicacionesretencion rd ON rd.cve_ubicacion = k.destino
                LEFT JOIN t_tipomovimiento m ON m.id_TipoMovimiento = k.id_TipoMovimiento
                LEFT JOIN c_almacenp al ON al.id = k.Cve_Almac
                LEFT JOIN c_lotes lote ON lote.cve_articulo = k.cve_articulo AND lote.Lote = k.cve_lote
                WHERE k.Cve_Almac = {$almacen} {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha}
                GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, CveLP, movimiento
                ORDER BY k.fecha DESC
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $fecha; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $id_articulo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo utf8_decode($des_articulo); ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo utf8_decode($cve_lote); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Caducidad; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo utf8_decode($clave_contenedor); ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo utf8_decode($CveLP); ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo utf8_decode($movimiento); ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $origen; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $destino; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $bl; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo $cantidad; ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo $cve_usuario; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>