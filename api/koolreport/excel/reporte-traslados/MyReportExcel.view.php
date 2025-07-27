<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Traslados";
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
  

 

 
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Movimiento</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Almacén Origen</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Almacén Destino</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pallet/Contenedor</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>QTY UNITS</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>QTY CAJAS</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio = $_GET['folio'];

    $sql = "SELECT a_origen.clave AS Almacen_Origen, IFNULL(ch.CveLP, '') AS LP, 
                   td.Cve_articulo, a.des_articulo,
                   IF(IFNULL(td.cve_lote, '') = '', IFNULL(s.LOTE, ''),IFNULL(td.cve_lote, '')) AS lote, 
                   IF(IFNULL(td.cve_lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                   #IFNULL(tdt.Num_cantidad, td.Num_cantidad) AS cantidad,
                   #IFNULL(tdt.Num_cantidad, s.Cantidad) AS cantidad,
                   #IFNULL(IFNULL(k.ajuste, 0), s.Cantidad) AS cantidad,
                   IF(k.id_TipoMovimiento = 8 AND (th.TipoPedido = 'R' OR th.TipoPedido = 'RI'), k.cantidad, k.ajuste) AS cantidad,
                   TRUNCATE(IF(k.id_TipoMovimiento = 8 AND (th.TipoPedido = 'R' OR th.TipoPedido = 'RI'), k.cantidad, k.ajuste)/IF(IFNULL(a.num_multiplo, '') = 0, 1, a.num_multiplo), 0) AS cantidad_cajas,
                   k.cve_usuario,
                   m.nombre as movimiento,
                   a_destino.clave AS Almacen_Destino
            FROM th_pedido th
            LEFT JOIN td_pedido td ON th.Fol_folio = td.Fol_folio
            LEFT JOIN td_pedidoxtarima tdt ON td.Fol_folio = tdt.Fol_folio
            LEFT JOIN td_surtidopiezas s ON s.Cve_articulo = td.Cve_articulo AND td.Fol_folio = s.fol_folio
            LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_articulo AND l.Lote = IF(IFNULL(td.cve_lote, '') = '', IFNULL(s.LOTE, ''),IFNULL(td.cve_lote, ''))
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tdt.nTarima
            LEFT JOIN c_almacenp a_origen ON a_origen.id = th.statusaurora
            LEFT JOIN c_almacenp a_destino ON a_destino.id = th.cve_almac
            LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
            LEFT JOIN t_cardex k ON k.cve_articulo = a.cve_articulo AND k.destino = '{$folio}' AND IFNULL(k.cve_lote, '') = IFNULL(s.LOTE, '')
            LEFT JOIN t_tipomovimiento m ON m.id_TipoMovimiento = k.id_TipoMovimiento
            WHERE td.Fol_folio = '{$folio}' AND k.cve_articulo = a.cve_articulo";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cve_articulo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $movimiento; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Almacen_Origen; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Almacen_Destino; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $cantidad; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $cantidad_cajas; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $cve_usuario; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>