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
<title>Reporte de Existencias de Pedido</title>
<style>
    .red{color: red;}
</style>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio  = $_GET['folio'];

    $sql = "SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = (SELECT Cve_Almac FROM th_pedido WHERE Fol_folio = '$folio')) AS cr WHERE cr.con_recorrido != -1";
    if (!($res = mysqli_query($conn, $sql))){
    echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $con_recorrido = mysqli_fetch_array($res)['con_recorrido'];

    $cve_cia = $_GET['cve_cia'];

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">
                        <?php echo "Reporte de Existencias de Pedido"; ?>
                        </span></h1>
                    <p class="lead">
                        Pedido: <span lang="th"><?php echo $_GET['folio']; ?></span>
                    </p>
                    <p class="lead">
                        Fecha Pedido: <span lang="th"><?php echo $_GET['fecha_pedido']; ?></span>
                    </p>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>
      <th scope="col" width="150">LP</th>
      <th scope="col" width="150">BL</th>
      <th scope="col">Clave</th>
      <th scope="col">Descripción</th>
      <th scope="col">Lote</th>
      <th scope="col">Caducidad</th>
      <th scope="col">Cantidad Solicitada</th>
      <th scope="col">Existencia</th>
    </tr>
  </thead>
  <tbody>
  <?php 
    $sql_tipo_pedido = "SELECT TipoPedido FROM th_pedido where fol_folio = '$folio'";
    if (!($res_tipopedido = mysqli_query($conn, $sql_tipo_pedido)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $TipoPedido = mysqli_fetch_array($res_tipopedido)['TipoPedido'];

    $sql = "
        SELECT DISTINCT IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') AS BL, 
                    IFNULL(ch.CveLP, '') AS LP,
               p.Cve_articulo, a.des_articulo, 
               IFNULL(e.cve_lote, '') AS cve_lote, 
               IFNULL(u.picking, 'N') as es_picking,
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
        WHERE p.Fol_folio = '{$folio}' #AND IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') != '' AND p.cve_lote = IF(IFNULL(p.cve_lote, '') = '', '', e.cve_lote)

        UNION

        SELECT DISTINCT '' AS BL, '' AS LP,
               p.Cve_articulo, a.des_articulo, 
               IFNULL(p.cve_lote, '') AS cve_lote, 
               '' AS es_picking,
               IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(p.cve_lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), ''), '') AS Caducidad, 
               p.Num_Cantidad,
               0 AS Existencia,
               '' AS orden_secuencia
        FROM td_pedido p 
        LEFT JOIN th_pedido th ON th.Fol_folio = p.Fol_folio
        LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
        LEFT JOIN V_ExistenciaGralProduccion eg ON eg.cve_articulo = p.cve_articulo AND th.cve_almac = eg.cve_almac 
        LEFT JOIN c_lotes l ON l.cve_articulo = eg.cve_articulo AND l.Lote = eg.cve_lote
        WHERE p.Fol_folio = '{$folio}' 
        AND (
        p.Cve_articulo NOT IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion WHERE cve_almac = th.cve_almac AND cve_articulo IN (SELECT Cve_articulo FROM td_pedido WHERE Fol_folio = '{$folio}'))
        OR
        CONCAT(p.Cve_articulo, IFNULL(p.cve_lote, '')) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM V_ExistenciaGralProduccion WHERE cve_almac = th.cve_almac AND cve_articulo IN (SELECT Cve_articulo FROM td_pedido WHERE Fol_folio = '{$folio}'))
        )
        ORDER BY BL DESC, orden_secuencia, Cve_articulo
        #, IF(IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '') = '', 9999999999, IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '')) 
    ";

    if($TipoPedido == 'R' || $TipoPedido == 'RI')
    {
    $sql = "
        SELECT DISTINCT IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') AS BL, 
                    IFNULL(ch.CveLP, '') AS LP,
               p.Cve_articulo, a.des_articulo, 
               IFNULL(e.cve_lote, '') AS cve_lote, 
               IFNULL(u.picking, 'N') as es_picking,
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
        WHERE p.Fol_folio = '{$folio}' AND IF(IFNULL(e.Existencia, 0) > 0, IFNULL(u.CodigoCSD, ''), '') != '' AND p.cve_lote = IF(IFNULL(p.cve_lote, '') = '', '', e.cve_lote)
        ORDER BY BL, Cve_articulo, IF(IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '') = '', 9999999999, IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND e.cve_lote != '', l.Caducidad, ''), '')) 
    ";
    }


    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $articulo_anterior = ""; $imprimir = true;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        if($articulo_anterior == $Cve_articulo) $imprimir = false;
        else {$articulo_anterior = $Cve_articulo;$imprimir = true;}

        if($con_recorrido == 1 && $orden_secuencia == '' && $Existencia > 0) $BL .= "<br>*Sin RS*";
        if($es_piking == 'N') $BL .= "<br>*No es Picking";
  ?>
    <tr>
      <th scope="row" width="150"><?php echo $LP; ?></th>
      <th scope="row" width="150"><?php echo $BL; ?></th>
      <td><?php /*if($imprimir == true)*/ echo $Cve_articulo; ?></td>
      <td><?php /*if($imprimir == true)*/ echo $des_articulo; ?></td>
      <td><?php echo $cve_lote; ?></td>
      <td align="center"><?php echo $Caducidad; ?></td>
      <td align="center"><?php /*if($imprimir == true)*/ echo $Num_Cantidad; ?></td>
      <td align="right"><?php echo $Existencia; ?></td>
    </tr>
    <?php 
    }
    ?>
  </tbody>
</table>
</div>

</div>
</body>
</html>

