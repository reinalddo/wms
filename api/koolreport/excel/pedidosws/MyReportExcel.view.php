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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrCJ</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrPz</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Cajas</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Piezas</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio = $_GET['id'];

    $sql = "SELECT DISTINCT 
                ep.Cve_articulo, ep.des_articulo, SUM(ep.total_cajas) AS total_cajas, 
                SUM(IFNULL((ep.total_piezas%ep.total_cajas), ep.Num_cantidad)) AS total_piezas, 
                ep.ruta, SUM(ep.promocion_cajas) AS promocion_cajas, SUM(ep.promocion_piezas) AS promocion_piezas,
               (SUM(ep.total_cajas)+SUM(ep.promocion_cajas)) AS total_cajas_promo,
               (SUM(IFNULL((ep.total_piezas%ep.total_cajas), ep.Num_cantidad))+SUM(ep.promocion_piezas)) AS total_piezas_promo

        FROM (
            SELECT DISTINCT 
            IFNULL(CONCAT('(', d.id_destinatario,') ' , d.razonsocial), '') AS cliente,
            IFNULL(CONCAT(d.ciudad, ', ', d.colonia, ', ', d.direccion), '') AS direccion,
            td.Cve_articulo,
            a.des_articulo,
            td.Num_cantidad,
            #IFNULL(COUNT(DISTINCT tc.Cve_CajaMix), 0) AS total_cajas,

            IFNULL(((SELECT DISTINCT IFNULL(SUM(TRUNCATE(tdp.Num_cantidad/a.num_multiplo, 0)), 0)
            FROM td_pedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = th.Fol_folio AND um.mav_cveunimed != 'XBX' AND tdp.cve_articulo = a.cve_articulo)
            +
            (SELECT DISTINCT IFNULL(SUM(tdc.Num_cantidad), 0)
            FROM td_pedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = th.Fol_folio AND um.mav_cveunimed = 'XBX' AND tdc.cve_articulo = a.cve_articulo)), 0) AS total_cajas,

            IFNULL(((SELECT IFNULL(SUM(tdp.Num_cantidad), 0)
            FROM td_pedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = th.Fol_folio AND um.mav_cveunimed != 'XBX')
            +
            (SELECT IFNULL(SUM(tdc.Num_cantidad*a.num_multiplo), 0)
            FROM td_pedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = th.Fol_folio AND um.mav_cveunimed = 'XBX')), 0) AS total_piezas,
            IFNULL(t_ruta.cve_ruta, 0) AS ruta, 
            #IFNULL(SUM(prc.Cant), 0) AS promocion_cajas,
            #IFNULL(SUM(prp.Cant), 0) AS promocion_piezas
            IFNULL((SELECT SUM(Cant) FROM PRegalado WHERE th.Fol_folio = Docto AND Tipmed = 'Caja'), 0) AS promocion_cajas,
            IFNULL((SELECT SUM(Cant) FROM PRegalado WHERE th.Fol_folio = Docto AND Tipmed != 'Caja'), 0) AS promocion_piezas
            #GROUP_CONCAT(CONCAT(pr.Cant, ' ', pr.Tipmed, ' ', pr.SKU ) SEPARATOR '<br>') AS promocion
          FROM th_pedido th 
                LEFT JOIN td_pedido td ON td.Fol_folio = th.Fol_folio
                LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = th.Fol_folio 
                LEFT JOIN c_destinatarios d ON d.id_destinatario = rel.Id_Destinatario
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta AND t_ruta.cve_ruta = th.cve_ubicacion AND th.STATUS = 'T'
                LEFT JOIN th_cajamixta tc ON tc.fol_folio = th.Fol_folio
                LEFT JOIN PRegalado prc ON th.Fol_folio = prc.Docto AND prc.Tipmed = 'Caja'
                LEFT JOIN PRegalado prp ON th.Fol_folio = prp.Docto AND prp.Tipmed != 'Caja'
          WHERE th.Fol_folio IN (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '{$folio}')
    ) AS ep GROUP BY ep.Cve_articulo
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cve_articulo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $total_cajas; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $total_piezas; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $promocion_cajas; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $promocion_piezas; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $total_cajas_promo; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $total_piezas_promo; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>