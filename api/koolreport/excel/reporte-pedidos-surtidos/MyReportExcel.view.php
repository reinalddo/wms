<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $cve_cia = $_GET['cve_cia'];
    $status = $_GET['status'];

    $sheet1 = $_GET['folio']."|".$_GET['fecha_pedido'];
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
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>BL</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Solicitada</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Surtida</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario</div>
<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio  = $_GET['folio'];
    $sufijo = $_GET['sufijo'];

    $sql = "SELECT COUNT(*) as con_recorrido FROM t_registro_surtido WHERE fol_folio = '$folio' AND Sufijo = '$sufijo'";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $con_recorrido = mysqli_fetch_array($res)['con_recorrido'];


    $sql = "";
    /*
    //if($status != 'S')
    if($con_recorrido == 0)
        $sql = "
            SELECT DISTINCT IFNULL(ch.CveLP, '') AS LP, tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                            IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                            #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                            u.CodigoCSD AS BL, 
                            TRUNCATE(tds.Num_cantidad, 3) AS Cantidad_Solicitada, IFNULL(SUM(TRUNCATE(k.ajuste, 3)), 0) AS Cantidad_Surtida, 
                            c.nombre_completo AS Usuario
            FROM td_surtidopiezas ts
            #LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo
            LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo AND ts.cve_articulo = tds.Cve_articulo AND ts.LOTE = tds.Cve_Lote
            LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
            LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
            LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
            LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN c_serie S ON S.cve_articulo = ts.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%' AND tc.cve_almac = ths.cve_almac AND tc.id_TipoMovimiento = 8
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
            LEFT JOIN t_MovCharolas kc ON kc.id_kardex = tc.id AND kc.Id_TipoMovimiento = 8
            LEFT JOIN c_charolas ch ON ch.IDContenedor = kc.ID_Contenedor
            WHERE ts.fol_folio = '{$folio}' AND tds.Cve_articulo = ts.Cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(ts.LOTE, '') #AND IFNULL(ts.LOTE, '') = IFNULL(tds.Cve_Lote, '') 
            AND tc.destino = ts.fol_folio AND u.AreaProduccion = 'N'
            GROUP BY LP, Clave, Lote, BL
            #GROUP BY Clave, Lote, Usuario
        ";
    else
        $sql = "SELECT DISTINCT tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(tds.cve_lote, '') AS Lote, 
                        IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                        u.CodigoCSD AS BL, 
                        IFNULL(ch.CveLP, '') AS LP,
                        TRUNCATE(tds.Cantidad, 3) AS Cantidad_Solicitada, 
                        IFNULL(ts.Cantidad, 0) AS Cantidad_Surtida, 
                        c.nombre_completo AS Usuario
        FROM t_registro_surtido tds
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tds.ClaveEtiqueta
        LEFT JOIN th_subpedido ths ON ths.fol_folio = tds.fol_folio AND ths.Sufijo = tds.Sufijo
        LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
        LEFT JOIN c_articulo a ON a.cve_articulo = tds.Cve_articulo
        LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = tds.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tds.cve_lote, '') AND tds.fol_folio = ts.fol_folio
        LEFT JOIN c_lotes L ON L.cve_articulo = tds.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN c_serie S ON S.cve_articulo = tds.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN c_ubicacion u ON u.idy_ubica = tds.idy_ubica
        WHERE tds.fol_folio = '{$folio}' AND tds.Sufijo = '$sufijo' AND tds.Cve_articulo = a.cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(tds.cve_lote, '') #AND IFNULL(tds.cve_lote, '') = IFNULL(tds.Cve_Lote, '') 
        AND u.AreaProduccion = 'N';";
*/
        $sql = "
            SELECT DISTINCT IFNULL(ch.CveLP, '') AS LP, tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                            IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                            #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                            u.CodigoCSD AS BL, 
                            TRUNCATE(tds.Num_cantidad, 3) AS Cantidad_Solicitada, IFNULL(SUM(TRUNCATE(tc.ajuste, 3)), 0) AS Cantidad_Surtida, 
                            c.nombre_completo AS Usuario
            FROM td_surtidopiezas ts
            #LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo
            LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo AND ts.cve_articulo = tds.Cve_articulo AND ts.LOTE = tds.Cve_Lote
            LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
            LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
            LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
            LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN c_serie S ON S.cve_articulo = ts.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%' AND tc.cve_almac = ths.cve_almac AND tc.id_TipoMovimiento = 8
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
            LEFT JOIN t_MovCharolas kc ON kc.id_kardex = tc.id AND kc.Id_TipoMovimiento = 8
            LEFT JOIN c_charolas ch ON ch.IDContenedor = kc.ID_Contenedor
            WHERE ts.fol_folio = '{$folio}' AND tds.Cve_articulo = ts.Cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(ts.LOTE, '') #AND IFNULL(ts.LOTE, '') = IFNULL(tds.Cve_Lote, '') 
            AND tc.destino = ts.fol_folio AND u.AreaProduccion = 'N'
            GROUP BY LP, Clave, Lote, BL
            #GROUP BY Clave, Lote, Usuario
        ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $i = 2;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Clave; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Descripcion; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Lote; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Caducidad; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $BL; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $Cantidad_Solicitada; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $Cantidad_Surtida; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $Usuario; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>