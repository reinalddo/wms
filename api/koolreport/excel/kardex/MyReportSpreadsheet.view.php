<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;
    use \koolreport\excel\Text;

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
            'bold' => false,
            'italic' => true,
            'underline' => false,
            'strikethrough' => true,
            'name' => 'Arial',
            'size' => '14',
            'color' => '808080',
        ],
        'border' => [
            // 'color' => '000000',
            'width' => 'thick', //'thin', 'medium', 'thick'
            // 'style' => 'solid', //'none', 'solid', 'dashed', 'dotted', 'double'.
            'top' => [
                'color' => '000000',
                'width' => 'medium', //'thin', 'medium', 'thick'
                'style' => 'solid', //'none', 'solid', 'dashed', 'dotted', 'double'.
            ],
            'right' => [],
            'bottom' => [],
            'left' => [],
        ],
        'backgroundColor' => '00ff00',
        'wrapText' => true,
    ];
/*
?>
    <div translation="2:4">
        <?php 
        Text::create([
            "text" => "Orders List of Sales"
        ]);
<?php 
*/
/*
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

/*
?>
    
    <table>
        <tr>
<td>Fecha</td>
<td>Clave</td>
<td>Articulo</td>
<td>Lote|Serie</td>
<td>Caducidad</td>
<td>Pallet|Contenedor</td>
<td>License Plate (LP)</td>
<td>Movimiento</td>
<td>Origen</td>
<td>Destino</td>
<td>BL</td>
<td>Cantidad</td>
<td>Usuario</td>
</tr>
    </table>
    
<?php 
*/
/*
?>
    <div translation="A1">Fecha</div>
    <div translation="B1">Clave</div>
    <div translation="C1">Articulo</div>
    <div translation="0:1">Lote|Serie</div>
    <div translation="0:0">Caducidad</div>
    <div translation="0:0">Pallet|Contenedor</div>
    <div translation="0:0">License Plate (LP)</div>
    <div translation="0:0">Movimiento</div>
    <div translation="0:0">Origen</div>
    <div translation="0:0">Destino</div>
    <div translation="0:0">BL</div>
    <div translation="0:0">Cantidad</div>
    <div translation="0:0">Usuario</div>
<?php 
/*
?>
    <div translation="0:0">
        <?php 
        Table::create(array(
            'Fecha', 'Clave', 'Articulo', 'Lote|Serie', 'Caducidad', 'Pallet|Contenedor', 'License Plate (LP)', 'Movimiento', 'Origen', 'Destino', 'BL', 'Cantidad', 'Usuario')
        );
        ?>
    </div>
<?php 
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen= $_GET['almacen'];
    $lote= $_GET['lote'];
    $cve_articulo= $_GET['cve_articulo'];
    $fecha_inicio= $_GET['fechaI'];
    $fecha_final= $_GET['fechaF'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_GET['criterio'];

    $SQLCriterio = "";
    if($_criterio)
    {
        $SQLCriterio = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR uo.CodigoCSD LIKE '%".$_criterio."%' OR ud.CodigoCSD LIKE '%".$_criterio."%' OR ch.clave_contenedor LIKE '%".$_criterio."%' OR ch.CveLP LIKE '%".$_criterio."%' OR rd.desc_ubicacion LIKE '%".$_criterio."%' OR m.nombre LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%' OR rd.cve_ubicacion LIKE '%".$_criterio."%' OR k.origen LIKE '%".$_criterio."%' OR k.destino LIKE '%".$_criterio."%') ";
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
                IFNULL(IF(DATE_FORMAT(lote.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d'),DATE_FORMAT(lote.Caducidad, '%d-%m-%Y'), DATE_FORMAT(lote.Caducidad, '%d-%m-%Y')),'') AS Caducidad, 
                DATE_FORMAT(k.fecha, '%d-%m-%Y | %H:%m:%i') AS fecha, 
                IF(LEFT(k.origen, 4) != 'Inv_' AND k.origen != 'Inventario Inicial',IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), IF(LEFT(k.origen, 3) = 'PT_' OR LEFT(k.origen, 2) = 'OT', k.origen, 'RTM')), k.origen) AS origen, 
                IFNULL(ch.clave_contenedor,'') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(uo.CodigoCSD, '') AS bl, k.cantidad,
                IFNULL(ud.CodigoCSD, rd.desc_ubicacion) AS destino, k.cantidad, m.nombre AS movimiento, k.cve_usuario, al.nombre AS almacen 
                FROM t_cardex k 
                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                LEFT JOIN c_almacenp al ON al.id = k.Cve_Almac
                LEFT JOIN t_MovCharolas mch ON k.id = mch.id_kardex #OR (k.origen = mch.Origen AND k.destino = mch.Destino AND k.id_TipoMovimiento = mch.Id_TipoMovimiento AND k.cve_usuario = mch.Cve_Usuario)
                LEFT JOIN c_charolas ch ON ch.IDContenedor = mch.ID_Contenedor 
                LEFT JOIN c_ubicacion uo ON uo.idy_ubica = k.origen 
                LEFT JOIN tubicacionesretencion ro ON ro.cve_ubicacion = k.origen 
                LEFT JOIN c_ubicacion ud ON ud.idy_ubica = k.destino
                LEFT JOIN tubicacionesretencion rd ON rd.cve_ubicacion = k.destino 
                LEFT JOIN t_tipomovimiento m ON m.id_TipoMovimiento = k.id_TipoMovimiento
                LEFT JOIN c_lotes lote ON lote.cve_articulo = k.cve_articulo AND lote.Lote = k.cve_lote
                WHERE k.Cve_Almac = {$almacen} {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha}
                #GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, CveLP, movimiento
                #GROUP BY ext.ID_Contenedor
                ORDER BY k.id DESC
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;
/*
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
    */
  ?>

    <div translation="12">
        <?php
        $datos = array("Fecha", "Clave", "Articulo", "Lote|Serie", "Caducidad", "Pallet|Contenedor", "License Plate (LP)", "Movimiento", "Origen", "Destino", "BL", "Cantidad", "Usuario");
        //Table::create(array("dataStore"=>$this->dataStore($datos),));
        ?>
        <div translation="1"><?php echo $datos[0]; ?></div>
        <div translation="2"><?php echo $datos[1]; ?></div>
    </div>
    <?php 
    /*
    ?>
    <div translation="1:1">
        <?php
        echo $datos[1];
        ?>
    </div>
    <?php 
    */
    ?>
</div>