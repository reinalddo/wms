<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';
/*
    $category_amount = array(
        array("category"=>"Books","sale"=>32000,"cost"=>20000,"profit"=>12000),
        array("category"=>"Accessories","sale"=>43000,"cost"=>36000,"profit"=>7000),
        array("category"=>"Phones","sale"=>54000,"cost"=>39000,"profit"=>15000),
        array("category"=>"Movies","sale"=>23000,"cost"=>18000,"profit"=>5000),
        array("category"=>"Others","sale"=>12000,"cost"=>6000,"profit"=>6000),
    );

    $category_sale_month = array(
        array("category"=>"Books","January"=>32000,"February"=>20000,"March"=>12000),
        array("category"=>"Accessories","January"=>43000,"February"=>36000,"March"=>7000),
        array("category"=>"Phones","January"=>54000,"February"=>39000,"March"=>15000),
        array("category"=>"Others","January"=>12000,"February"=>6000,"March"=>6000),
    );
    */
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Reporte Detalle de Olas</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $folio = $_GET['id'];
    $primera_fila = false;

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-lg-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>

<?php 
    $sql = "SELECT cve_usuario, DATE_FORMAT(Fec_Pedido, '%d-%m-%Y') as fecha_pedido FROM th_pedido WHERE Fol_folio = '{$folio}'";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);
?>
        <div class="col-lg-4 text-center">
                  
                    <h1><span lang="th"><b>Reporte Detalle de Ola <?php echo $folio; ?></b><br></span></h1>
                    <p style="font-size: 20px;">
                        Usuario: <span><?php echo $cve_usuario; ?></span><br>
                        Fecha: <span><?php echo $fecha_pedido; ?></span>
                    </p>
        </div>
    </div>

<br><br>
<div style="padding: 10px 100px;">

<?php 
    $sql_w2 = "SELECT DISTINCT Fol_PedidoCon FROM t_consolidado WHERE Fol_Consolidado = '{$folio}'";
    if (!($res_w2 = mysqli_query($conn, $sql_w2))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row_w2 = mysqli_fetch_array($res_w2)) 
    {
?>
    <div class="row">
        <div class="col-6 text-left">
            <?php $folio_w1 = $row_w2['Fol_PedidoCon']; ?>
            <p style="font-size: 18px;"><b>Ola <?php echo $folio_w1; ?></b></p>
        </div>
    </div>
<?php 

        $sql_w1 = " SELECT DISTINCT t.Fol_Folio, c.Cve_CteProv, c.RazonSocial
                    FROM t_consolidado t
                    LEFT JOIN th_pedido th ON t.Fol_Folio = th.Fol_folio
                    LEFT JOIN c_cliente c ON c.Cve_CteProv = IFNULL(th.Cve_CteProv, th.Cve_clte) 
                    WHERE t.Fol_PedidoCon =  '{$folio_w1}'";
        if (!($res_w1 = mysqli_query($conn, $sql_w1))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }

        while ($row_w1 = mysqli_fetch_array($res_w1)) 
        {
?>
            <div class="row">
                <div class="col-6 text-left">
                    <?php 
                        $folio_w0 = $row_w1['Fol_Folio']; 
                        $Cliente = $row_w1['RazonSocial'];
                    ?>
                    <p style="font-size: 18px;"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pedido <?php echo $folio_w0; ?> | Cliente: <?php echo $Cliente; ?></b></p>
                </div>
            </div>

            <table class="table">

              <thead>
                <tr>
                  <th scope="col" width="200">Clave</th>
                  <th scope="col" width="200">Artículo</th>
                  <th scope="col" width="200">Lote|Serie</th>
                  <th scope="col" width="200">Caducidad</th>
                  <th scope="col" width="100">Cantidad</th>
                  <th scope="col" width="100">UM</th>
                </tr>
              </thead>
<?php 
            $primera_fila = true;
            $sql_w0 = "SELECT td.Cve_articulo, a.des_articulo,
                        IF((a.control_lotes = 'S' OR a.control_numero_series = 'S') AND IFNULL(td.cve_lote, '') != '', td.cve_lote, '') AS cve_lote,
                        IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(td.cve_lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        td.Num_cantidad,
                        um.des_umed
                    FROM td_pedido td 
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_articulo AND l.Lote = IFNULL(td.cve_lote, '')
                    LEFT JOIN c_serie s ON s.cve_articulo = td.Cve_articulo AND s.numero_serie = IFNULL(td.cve_lote, '')
                    LEFT JOIN c_unimed um ON um.id_umed = IFNULL(td.id_unimed, a.UnidadMedida)
                    WHERE td.Fol_folio = '{$folio_w0}'";
            if (!($res_w0 = mysqli_query($conn, $sql_w0))){
                echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
            }

            while ($row_w0 = mysqli_fetch_array($res_w0)) 
            {
                extract($row_w0);
    ?>
              <tbody>
                <tr>
                  <td width="200"><?php echo $Cve_articulo; ?></td>
                  <td width="200"><?php echo $des_articulo; ?></td>
                  <td width="200"><?php echo $cve_lote; ?></td>
                  <td width="200" align="center"><?php echo $Caducidad; ?></td>
                  <td width="100" align="right"><?php echo $Num_cantidad; ?></td>
                  <td width="100" align="right"><?php echo $des_umed; ?></td>
                </tr>
              </tbody>
    <?php 
            }
    ?>
            </table>

<?php 
        }
    }
?>


</div>

</div>
</body>
</html>

