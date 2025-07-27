<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Pedidos PTL";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Factura</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidades x Pedido</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pasillo</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tipo de Pedido</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen        = $_GET['almacen'];
    $clave_almacen  = $_GET['clave_almacen'];
    $criterio       = $_GET['criterio'];
    $fecha_inicio   = $_GET['fechaInicio'];
    $fecha_fin      = $_GET['fechaFin'];
    $horai          = $_GET['horai'];
    $horaf          = $_GET['horaf'];


    $fecha_i = explode("-", $fecha_inicio);
    $d = $fecha_i[0];
    $m = $fecha_i[1];
    $Y = $fecha_i[2];
    $fecha_inicio = $Y."-".$m."-".$d;

    $fecha_f = explode("-", $fecha_fin);
    $d = $fecha_f[0];
    $m = $fecha_f[1];
    $Y = $fecha_f[2];
    $fecha_fin = $Y."-".$m."-".$d;

      //$sql = "CALL SPAD_ReportePedsurtPTL('$clave_almacen', '$fecha_inicio', '$fecha_fin')";
      $sql = "CALL SPAD_ReportePedsurtPTL('$clave_almacen', '$fecha_inicio', '$horai', '$fecha_fin', '$horaf', '$criterio', 0, 1, 1)";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Factura; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Total_Unidades; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Pasillo; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $TipoPed; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>