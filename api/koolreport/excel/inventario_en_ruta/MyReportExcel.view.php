<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';
    $ruta    = $_GET['ruta'];
    $almacen = $_GET['almacen'];
    $search  = $_GET['search'];

    $sheet1 = "Inv_Ruta_".$ruta;
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
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Stock Cajas</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Stock Piezas</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Stock Final</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $where = "WHERE 1 ";

    if($search)
        $where .= " AND (rutas.clave LIKE '%{$search}%' OR rutas.descripcion_producto LIKE '%{$search}%' OR rutas.cve_ruta LIKE '%{$search}%')";
//        $where .= " AND (rutas.ruta LIKE '%{$search}%' OR rutas.razonsocial LIKE '%{$search}%' OR rutas.clave LIKE '%{$search}%' OR rutas.descripcion_producto LIKE '%{$search}%' OR rutas.folio LIKE '%{$search}%' OR rutas.pedido LIKE '%{$search}%' OR rutas.cve_ruta LIKE '%{$search}%')";

    //if($ruta)
    //    $where .= " AND rutas.cve_ruta = '{$ruta}' ";

    $sql_venta_preventa = ""; $sql_left_join_ruta = "";
    if($ruta != '')
    {
      //$and .= "AND t_clientexruta.clave_ruta = '{$ruta}' ";
        /*
        $sql = "SELECT venta_preventa, ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $venta_preventa = $row["venta_preventa"];
        
        if($venta_preventa == 2)
        {
            $ruta = $row["ID_Ruta"];
            $where .= " AND rutas.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = '{$ruta}') ";
        }
        else
            */
            $where .= " AND rutas.cve_ruta = '{$ruta}' ";
    }

      $sql = "SELECT  rutas.clave, rutas.descripcion_producto, 
                    SUM(rutas.cantidad_sin_conversion) AS cantidad_sin_conversion, 
                    SUM(rutas.cantidad_final_sin_conversion) AS cantidad_final_sin_conversion, 
                    SUM(rutas.cantidad) AS cantidad, SUM(rutas.cantidad_final) AS cantidad_final, 
                    rutas.ID_Ruta, rutas.cve_ruta,
                    SUM(IFNULL(IF(rutas.mav_cveunimed = 'XBX', IF(rutas.num_multiplo = 1, 0, rutas.cantidad_final_sin_conversion), IF(rutas.num_multiplo > 1, TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0), 0)), 0)) AS cajas_total,
                    SUM(IFNULL(IF(rutas.mav_cveunimed != 'XBX' AND rutas.num_multiplo > 1, (rutas.cantidad_final_sin_conversion - (rutas.num_multiplo*TRUNCATE((rutas.cantidad_final_sin_conversion/rutas.num_multiplo), 0))), IF(rutas.num_multiplo = 1, rutas.cantidad_final_sin_conversion, 0)), 0)) AS piezas_total
            FROM (

                SELECT DISTINCT
                    st.IdStock,
                    art.num_multiplo,
                    um.mav_cveunimed,
                    tr.ID_Ruta AS ID_Ruta,
                    tr.cve_ruta,
                    art.cve_articulo AS clave,
                    st.IdEmpresa,
                    IFNULL((SELECT des_articulo FROM c_articulo WHERE cve_articulo = art.cve_articulo), '') AS descripcion_producto,
                    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)) AS cantidad_sin_conversion,
                    IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)) AS cantidad_final_sin_conversion,
                    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock)*art.num_multiplo, 0),SUM(st.Stock)*art.num_multiplo)) AS cantidad, 
                    IF(um.mav_cveunimed != 'XBX', IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock), 0),SUM(st.Stock)), IF(art.control_peso = 'N',TRUNCATE(SUM(st.Stock)*art.num_multiplo, 0),SUM(st.Stock)*art.num_multiplo)) AS cantidad_final
                FROM Stock st
                    INNER JOIN c_almacenp a ON a.clave = st.IdEmpresa AND a.id = '{$almacen}'
                    LEFT JOIN t_ruta tr ON tr.ID_Ruta = st.Ruta
                    LEFT JOIN c_articulo art ON art.cve_articulo = st.Articulo 
                    LEFT JOIN c_unimed um ON um.id_umed = art.unidadMedida
                    
                GROUP BY clave, ID_Ruta, IdEmpresa, IdStock


            ) AS rutas {$where}
            AND cantidad_final > 0
            GROUP BY clave 
            ORDER BY cantidad_final DESC
    ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $cve_ruta; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $descripcion_producto; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $cajas_total; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $piezas_total; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $cantidad_final; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>