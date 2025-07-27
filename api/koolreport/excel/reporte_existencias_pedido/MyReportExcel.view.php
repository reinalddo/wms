<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $folio = $_GET['folio'];

    $sheet1 = "Existencias Pedido ".$folio;
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>BL</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Solicitada</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Existencia</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql_tipo_pedido = "SELECT TipoPedido FROM th_pedido where fol_folio = '$folio'";
    if (!($res_tipopedido = mysqli_query($conn, $sql_tipo_pedido)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $TipoPedido = mysqli_fetch_array($res_tipopedido)['TipoPedido'];

    $sql = "
        SELECT DISTINCT IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') AS BL, 
                    IFNULL(ch.CveLP, '') AS LP,
               p.Cve_articulo, a.des_articulo, 
               IFNULL(e.cve_lote, '') AS cve_lote, 
               IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad, 
               p.Num_Cantidad,
               ROUND(IF(IFNULL(ch.CveLP, '') = '', IFNULL(e.Existencia, 0), IFNULL(eg.Existencia, 0)), 3) AS Existencia,
               IFNULL(tr.orden_secuencia, '') AS orden_secuencia
        FROM td_pedido p 
        LEFT JOIN th_pedido th ON th.Fol_folio = p.Fol_folio
        LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
        LEFT JOIN VS_ExistenciaParaSurtido e ON e.cve_articulo = a.cve_articulo AND e.cve_almac = th.cve_almac #and e.cve_lote = ifnull(p.cve_lote, '') 
        LEFT JOIN V_ExistenciaGralProduccion eg ON eg.cve_articulo = e.cve_articulo AND e.cve_lote = eg.cve_lote AND e.Cve_Almac = eg.cve_almac AND e.Idy_Ubica = eg.cve_ubicacion
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = eg.Cve_Contenedor
        LEFT JOIN c_ubicacion u ON u.idy_ubica = e.Idy_Ubica
        LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.Lote = e.cve_lote
        LEFT JOIN td_ruta_surtido tr ON tr.idy_ubica = e.Idy_Ubica
        WHERE p.Fol_folio = '{$folio}' AND IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') != ''
        ORDER BY BL, orden_secuencia, 
        Cve_articulo, IF(IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '') = '', 9999999999, IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '')) 
    ";

    if($TipoPedido == 'R' || $TipoPedido == 'RI')
    {
    $sql = "
        SELECT DISTINCT IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') AS BL, 
                    IFNULL(ch.CveLP, '') AS LP,
               p.Cve_articulo, a.des_articulo, 
               IFNULL(e.cve_lote, '') AS cve_lote, 
               IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad, 
               p.Num_Cantidad,
               ROUND(IFNULL(e.Existencia, 0), 3) AS Existencia,
               '' AS orden_secuencia
        FROM td_pedido p 
        LEFT JOIN th_pedido th ON th.Fol_folio = p.Fol_folio
        LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
        LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = p.cve_articulo AND IFNULL(p.cve_lote, '') = e.cve_lote AND th.Cve_Almac = e.cve_almac 
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
        LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
        LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.Lote = e.cve_lote
        LEFT JOIN td_ruta_surtido tr ON tr.idy_ubica = e.cve_ubicacion
        WHERE p.Fol_folio = '{$folio}' AND IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') != ''
        ORDER BY BL, Cve_articulo, IF(IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '') = '', 9999999999, IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '')) 
    ";
    }
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    $articulo_anterior = ""; $imprimir = true;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        if($articulo_anterior == $Cve_articulo) $imprimir = false;
        else {$articulo_anterior = $Cve_articulo;$imprimir = true;}
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $BL; ?></div>
        <div cell="C<?php echo $i; ?>"><?php if($imprimir == true) echo $Cve_articulo; ?></div>
        <div cell="D<?php echo $i; ?>"><?php if($imprimir == true) echo $des_articulo; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $cve_lote; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $Caducidad; ?></div>
        <div cell="G<?php echo $i; ?>"><?php if($imprimir == true) echo $Num_Cantidad; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $Existencia; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>