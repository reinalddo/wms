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

    $folio = $_GET['folio'];
    $cve_cia = $_GET['cve_cia'];

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $sql = "SELECT * FROM th_pedido WHERE Fol_folio = '{$folio}'";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $usuario = mysqli_fetch_array($res)['Cve_Usuario'];
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>
        <div class="col">
                <div class="text-center">

                    <h1><span lang="th">
                        Reporte de Traslado
                        </span></h1>
                    <p class="lead">
                        Pedido: <span lang="th"><?php echo $folio; ?></span>
                    </p>
                    <p class="lead">
                        Fecha Pedido: <span lang="th"><?php echo $_GET['fecha_pedido']; ?></span>
                    </p>
                    <p class="lead">
                        Usuario: <span lang="th"><?php echo $usuario; ?></span>
                    </p>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table table-bordered">
  <thead>
    <tr>
    <?php 
    /*
    ?>
      <th scope="col">Almacén Origen</th>
      <th scope="col">LP</th>
      <th scope="col">Artículo</th>
      <th scope="col">Descripción</th>
      <th scope="col">Lote</th>
      <th scope="col">Caducidad</th>
      <th scope="col">Cantidad</th>
      <th scope="col">Almacén Destino</th>
      <?php  
      */
      ?>
      <th scope="col">Clave</th>
      <th scope="col">Descripción</th>
      <th scope="col">Movimiento</th>
      <th scope="col">Almacén Origen</th>
      <th scope="col">Almacén Destino</th>
      <th scope="col">Pallet/Contenedor</th>
      <th scope="col">QTY UNITS</th>
      <th scope="col">QTY CAJAS</th>
      <th scope="col">Usuario</th>
    </tr>
  </thead>
  <tbody>
  <?php 

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

    while ($row = mysqli_fetch_array($res)) {

        extract($row);


/*
Almacén Origen
LP
Artículo
Lote
Caducidad
Cantidad
Almacén Destino
*/
  ?>

    <tr>
    <?php 
    /*
    ?>
      <th align="left"><?php echo $Almacen_Destino; ?></th>
      <td><?php echo $LP; ?></td>
      <td><?php echo $Cve_articulo; ?></td>
      <td><?php echo $des_articulo; ?></td>
      <td><?php echo $lote; ?></td>
      <td><?php echo $Caducidad; ?></td>
      <td align="right"><?php echo $cantidad; ?></td>
      <td align="left"><?php echo $Almacen_Origen; ?></td>
    <?php 
    */
    ?>
      <th align="left"><?php echo $Cve_articulo; ?></th>
      <td><?php echo $des_articulo; ?></td>
      <td><?php echo $movimiento; ?></td>
      <td><?php echo $Almacen_Origen; ?></td>
      <td><?php echo $Almacen_Destino; ?></td>
      <td><?php echo $LP; ?></td>
      <td align="right"><?php echo $cantidad; ?></td>
      <td align="left"><?php echo $cantidad_cajas; ?></td>
      <td align="left"><?php echo $cve_usuario; ?></td>

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

