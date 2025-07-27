<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Resumen Pedidos por Ruta";
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
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote|Serie</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Status</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio   = $_GET['folio'];
    $cve_cia = $_GET['cve_cia'];

    $sql="
        SELECT DISTINCT ts.cve_articulo AS clave, 
            a.des_articulo AS articulo,
            IFNULL(ts.Cve_Lote, '') AS lote_serie, 
            IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(ts.Cve_Lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
            ts.num_cantsurt AS cantidad, 
            #ce.DESCRIPCION AS estatus
          CASE 
              WHEN ts.Status = 'S' THEN 'Abierto'
              WHEN ts.Status IN ('P', 'T') THEN 'Cerrado'
              WHEN ts.Status = 'K' THEN 'Cancelado'
          END AS estatus,
          ch.CveLP AS LP
        FROM td_salalmacen ts
        LEFT JOIN c_articulo a ON a.cve_articulo = ts.cve_articulo
        LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = ts.Cve_Lote
        LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.cve_articulo AND ts.Cve_Lote = tc.cve_lote AND ts.cve_almac = tc.cve_almac AND ts.fol_folio = tc.destino
        LEFT JOIN t_MovCharolas tm ON tm.id_kardex = tc.id 
        LEFT JOIN c_charolas ch ON tm.ID_Contenedor = ch.IDContenedor
        #LEFT JOIN cat_estados ce ON ce.ESTADO = ts.Status
        WHERE ts.fol_folio = '{$folio}' AND ts.Activo = 1";


    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $articulo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $lote_serie; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $caducidad; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $cantidad; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $estatus; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $LP; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>