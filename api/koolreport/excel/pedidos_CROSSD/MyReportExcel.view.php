<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Pedidos Cross Docking";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Factura Madre</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Factura Hija</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Punto de Venta</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Unidades</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Cajas</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen       = $_GET['almacen'];
    $clave_almacen = $_GET['clave_almacen'];
    $criterio      = $_GET['criterio'];
    $fecha_inicio   = $_GET['fechaInicio'];
    $fecha_fin      = $_GET['fechaFin'];


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

        $sql = "SELECT  P.Fol_PedidoCon AS FacturaMadre,P.Fol_Folio AS FacturaHija,
                P.Cod_PV,C.RazonSocial AS PuntoDeVenta,
                SUM(Cant_Pedida) AS Total_Unidades,
                TRUNCATE(SUM(P.Cant_pedida/A.Num_multiplo),0)+CASE WHEN SUM(P.Cant_pedida%A.Num_multiplo)>0 THEN 1 ELSE 0 END  AS Total_Cajas
                FROM  td_consolidado P 
                Join c_cliente C On P.Cve_Clte=C.Cve_Clte And P.Cve_CteProv=C.Cve_CteProv
                Join c_articulo A On P.Cve_Articulo=A.Cve_Articulo
                Join th_pedido H On H.Fol_Folio=P.Fol_Folio
                WHERE H.Cve_Almac=1 And P.Fec_OrdCom Between '$fecha_inicio' And '$fecha_fin' 
                GROUP BY P.Fol_PedidoCon,P.Fol_Folio,P.Cod_PV,C.RazonSocial";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $FacturaMadre; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $FacturaHija; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $PuntoDeVenta; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Total_Unidades; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Total_Cajas; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>