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
<title>Reporte de Producto Surtido</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $status = $_GET['status'];

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
                        <?php if($status == 'S') echo "Lista de Surtido";else echo "Reporte de Producto Surtido"; ?>
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
      <th scope="col">LP</th>
      <th scope="col">Clave</th>
      <th scope="col">Descripción</th>
      <th scope="col">Lote</th>
      <th scope="col">Caducidad</th>
      <th scope="col" width="150" align="center">BL</th>
      <th scope="col">Cantidad Solicitada</th>
      <th scope="col">Cantidad Surtida</th>
      <th scope="col">Usuario</th>
    </tr>
  </thead>
  <tbody>
  <?php 

    $folio  = $_GET['folio'];
    $sufijo = $_GET['sufijo'];
/*
    $sql = "SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo) AS cr WHERE cr.con_recorrido != -1";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $con_recorrido = mysqli_fetch_array($res)['con_recorrido'];
*/
    $sql = "SELECT COUNT(*) as con_recorrido FROM t_registro_surtido WHERE fol_folio = '$folio' AND Sufijo = '$sufijo'";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $con_recorrido = mysqli_fetch_array($res)['con_recorrido'];


    $sql = "";
    
    //if($status != 'S')
    if($con_recorrido == 0)
        $sql = "
            SELECT DISTINCT tc.id, tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                            IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                            #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                            u.CodigoCSD AS BL, 
                            TRUNCATE(tds.Num_cantidad, 3) AS Cantidad_Solicitada, TRUNCATE(tc.Cantidad, 3) AS Cantidad_Surtida, 
                            c.nombre_completo AS Usuario
            FROM td_surtidopiezas ts
            #LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo
            LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo AND ts.cve_articulo = tds.Cve_articulo AND ts.LOTE = tds.Cve_Lote
            LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
            LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
            LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
            LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN c_serie S ON S.cve_articulo = ts.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%' AND tc.cve_almac = ths.cve_almac
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
            WHERE ts.fol_folio = '{$folio}' AND tds.Cve_articulo = ts.Cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(ts.LOTE, '') #AND IFNULL(ts.LOTE, '') = IFNULL(tds.Cve_Lote, '') 
            AND tc.destino = ts.fol_folio AND u.AreaProduccion = 'N'

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

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
      <th scope="row"><?php echo $LP; ?></th>
      <th scope="row"><?php echo $Clave; ?></th>
      <td><?php echo $Descripcion; ?></td>
      <td><?php echo $Lote; ?></td>
      <td><?php echo $Caducidad; ?></td>
      <td width="150" align="center"><?php echo $BL; ?></td>
      <td align="right"><?php echo $Cantidad_Solicitada; ?></td>
      <td align="right"><?php echo $Cantidad_Surtida; ?></td>
      <td><?php echo $Usuario; ?></td>
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

