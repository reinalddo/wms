<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $folio = $_GET['folio'];
    $sheet1 = "RTM #{$folio}";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Contenedor</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pallet</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripcion</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Numero Serie</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Recibida</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Recepcion</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Dañada</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Fin</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Disponible</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Ubicada</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT
              tde.cve_articulo as clave,
              a.des_articulo as descripcion,
              tde.CantidadRecibida AS cantidad_pedida,
              tde.status as status,
              tde.fol_folio as folio_entrada,
                    ta.ClaveEtiqueta as contenedor,
              IF(IF(a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '') LIKE '%0000%', '', IF(a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '')) AS caducidad,
              IF(a.control_lotes = 'S', c_lotes.LOTE,'') as lote,
              IF(a.control_numero_series = 'S', tde.cve_lote,'') as numero_serie,
              IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}' AND Ubicada = 'N'), tde.CantidadRecibida, ta.Cantidad) AS cantidad_recibida,
              date_format(fecha_inicio, '%d-%m-%Y') as fecha_recepcion,
              (SELECT MIN(date_format(fecha_inicio, '%d-%m-%Y %h:%i:%s %p')) FROM td_entalmacen WHERE tde.num_orden = td_entalmacen.num_orden) as fecha_inicio,
              (SELECT MAX(date_format(fecha_fin, '%d-%m-%Y %h:%i:%s %p')) FROM td_entalmacen WHERE tde.num_orden = td_entalmacen.num_orden) as fecha_fin,
              tda.cantidad - tde.CantidadRecibida as cantidad_faltante,
              IF(tde.CantidadDisponible-tde.CantidadRecibida<0, '0', tde.CantidadDisponible-tde.CantidadRecibida) as cantidad_danada,    
              IFNULL(((IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}' AND Ubicada = 'N'), tde.CantidadRecibida, ta.Cantidad)) - (IF(IFNULL(ta.Ubicada, 'S') = 'S', IFNULL(IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}'  AND fol_folio = tde.fol_folio AND tde.cve_articulo = cve_articulo AND IFNULL(tde.cve_lote, '') = IFNULL(cve_lote, '') AND Ubicada = 'N'), tde.CantidadUbicada, ta.Cantidad), 0), 0))), tde.CantidadRecibida) AS CantidadDisponible,
              IF(IFNULL(ta.Ubicada, 'S') = 'S', IFNULL(IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}'  AND fol_folio = tde.fol_folio AND tde.cve_articulo = cve_articulo AND IFNULL(tde.cve_lote, '') = IFNULL(cve_lote, '') AND Ubicada = 'N'), tde.CantidadUbicada, ta.Cantidad), 0), 0) AS CantidadUbicada,
              u.cve_usuario as usuario,
              IF(c_charolas.CveLP != '',c_charolas.CveLP, '') as pallet
      FROM td_entalmacen tde
          LEFT JOIN td_aduana tda on tda.cve_articulo = tde.cve_articulo and tda.num_orden = tde.num_orden
          LEFT JOIN c_articulo a on a.cve_articulo = tde.cve_articulo
          LEFT JOIN c_usuario u on u.cve_usuario = tde.cve_usuario
          LEFT JOIN th_entalmacen on th_entalmacen.Fol_Folio = tde.fol_folio
          LEFT JOIN c_lotes on c_lotes.LOTE = tde.cve_lote AND c_lotes.cve_articulo = tde.cve_articulo
          LEFT JOIN td_entalmacenxtarima ta on ta.fol_folio = tde.fol_folio and tde.cve_articulo = ta.cve_articulo and tde.cve_lote = ta.cve_lote #and tde.CantidadRecibida = ta.Cantidad
        LEFT JOIN c_charolas on c_charolas.clave_contenedor = ta.ClaveEtiqueta
      WHERE tde.fol_folio = '{$folio}' 
      GROUP BY pallet, clave, lote
      ORDER BY CantidadDisponible DESC";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        //$color_fila = "";
        //if($CantidadDisponible == 0)
        //    $color_fila = 'style="background-color: green;"';

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $contenedor; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $pallet; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $descripcion; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $lote; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $caducidad; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $numero_serie; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $cantidad_recibida; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $fecha_recepcion; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $cantidad_danada; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $fecha_fin; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo $CantidadDisponible; ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo $CantidadUbicada; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>