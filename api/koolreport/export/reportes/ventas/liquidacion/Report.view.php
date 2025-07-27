<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Análisis de Ventas</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >


  <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $ruta = $_GET['ruta'];
    $diao = $_GET['diao'];
    $almacen = $_GET['almacen'];
    $cia = $_GET['cve_cia'];


    $sql = "SELECT v.Cve_Vendedor, v.Nombre 
            FROM t_vendedores v
            LEFT JOIN Rel_Ruta_Agentes ra ON v.Id_Vendedor = ra.cve_vendedor
            LEFT JOIN t_ruta r ON r.ID_Ruta = ra.cve_ruta
            WHERE r.cve_ruta = '$ruta'";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $row_vendedor = mysqli_fetch_array($res);
    $Cve_Vendedor    = $row_vendedor['Cve_Vendedor'];
    $Nombre_Vendedor = $row_vendedor['Nombre'];

    $sql = 'SELECT clave, nombre FROM c_almacenp WHERE id = '.$almacen;
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $row_almacen = mysqli_fetch_array($res);
    $clave_almacen = $row_almacen['clave'];
    $nombre_almacen = $row_almacen['nombre'];

    $sql = "SELECT DATE_FORMAT(Fecha, '%d/%m/%Y') AS fecha FROM DiasO WHERE Diao = $diao AND RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '$ruta')";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $row_fecha = mysqli_fetch_array($res);
    $fecha = $row_fecha['fecha'];


    $sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = '.$cia;
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $row_logo = mysqli_fetch_array($res);
    $logo = $row_logo['logo'];
    $nombre = $row_logo['nombre'];

    //if($logo[0] == '/') $logo[0] = "";


?>
    <table border="0">
      <tr>
        <td style="width: 100px;"></td>
        <td style="width: 200px;"><img src="<?php echo ''.$logo; ?>" alt="" height="200"></td>
        <td align="center" style="font-size: 14px;width: 950px; text-align: center; vertical-align: middle;">
            
        <h1><span lang="th"><?php echo $nombre; ?></span></h1>
        <table border="0">
            <tr><td style="width:260px;"></td>
                <td style="font-size: 18px;"><h1><span lang="th">Ticket de Liquidación</span></h1></td>
                <td style="width:160px;"></td>
                <td style="font-size: 18px;font-weight: bold;">Fecha: </td>
                <td style="font-size: 18px;">&nbsp;&nbsp;&nbsp;<?php echo $fecha; ?></td>
            </tr>
            
            <tr>
                <td style="font-size: 18px;text-align: right;"><span lang="th"><b>Ruta:</b></span></td>
                <td style="text-align: left;">&nbsp;&nbsp;&nbsp;<?php echo $ruta; ?></td>
                <td style="font-size: 18px;text-align: right;"><span lang="th"><b>D.O.</b></span></td>
                <td style="text-align: left;">&nbsp;&nbsp;&nbsp;<?php echo $diao; ?></td><td></td>
            </tr>
            <tr>
                <td style="font-size: 18px;text-align: right;"><span lang="th"><b>Agente:</b></span></td>
                <td style="text-align: left;">&nbsp;&nbsp;&nbsp;<?php echo "(".$Cve_Vendedor.") ".$Nombre_Vendedor; ?></td>
                <td></td><td></td><td></td>
            </tr>
            <tr>
                <td style="font-size: 18px;text-align: right;"><span lang="th"><b>Sucursal:</b></span></td>
                <td style="text-align: left;">&nbsp;&nbsp;&nbsp;<?php echo "(".$clave_almacen.") ".$nombre_almacen; ?></td>
                <td></td><td></td><td></td>
            </tr>
        </table>
        
        <br><br>
        
    </td>
      </tr>
    </table>
<br><br><br>
<h1 style="margin-left: 100px;">Análisis de Ventas</h1>
<br>
<div style="padding: 10px 100px;">
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Clave</th>
      <!--<th scope="col">Descripción</th>-->
      <th scope="col" colspan="2">Stock Inicial</th>
      <th scope="col" colspan="2">Ventas</th>
      <th scope="col" colspan="2">Preventa</th>
      <th scope="col" colspan="2">Entrega</th>
      <th scope="col" colspan="2">Rec</th>
      <th scope="col" colspan="2">Dev</th>
      <th scope="col" colspan="2">Prom</th>
      <th scope="col" colspan="2">Prom Prev</th>
      <th scope="col">Total $</th>
      <th scope="col" colspan="2">Stock Final</th>
      <th scope="col" colspan="2">Total Pedido</th>
    </tr>
    <tr>
      <th scope="col"></th>
      <!--<th scope="col"></th>-->

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col"></th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>

      <th scope="col">Cj</th>
      <th scope="col">Pz</th>
    </tr>

  </thead>
  <tbody>

<?php 
$sql = "SELECT 
row_liq.cve_articulo, row_liq.Articulo, row_liq.InvInicial, row_liq.control_peso,
row_liq.inv_inicial_cajas,
row_liq.inv_inicial_piezas,
GROUP_CONCAT(CONCAT(row_liq.TipoOperacion, ';;;;;', row_liq.Importe, ';;;;;', row_liq.IVA, ';;;;;', row_liq.Descuento, ';;;;;', row_liq.Cajas, ';;;;;', IF(row_liq.control_peso = 'S', TRUNCATE(row_liq.Piezas, 2), TRUNCATE(row_liq.Piezas, 0)), ';;;;;', row_liq.Total, ';;;;;', row_liq.PrCajas, ';;;;;', row_liq.PrPiezas) SEPARATOR ':::::') AS fila
FROM ( 
SELECT liq.TipoOperacion, liq.cve_articulo, liq.Articulo, SUM(liq.Importe) AS Importe, SUM(liq.IVA) AS IVA, 
       SUM(liq.Descuento) AS Descuento, SUM(liq.Cajas) AS Cajas, SUM(liq.Piezas) AS Piezas, liq.control_peso, 
       IFNULL(sh.Stock, '0') AS InvInicial,
       liq.DiaOperativo, liq.Ruta,
       IF(liq.mav_cveunimed = 'XBX', IF(liq.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/liq.num_multiplo), 0)) AS inv_inicial_cajas,
       IF(liq.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (liq.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/liq.num_multiplo), 0))), IF(liq.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
       liq.PrCajas, liq.PrPiezas,
       SUM((liq.Importe+liq.IVA-liq.Descuento)) AS Total  
FROM (
SELECT DISTINCT 
    um.mav_cveunimed, c_articulo.num_multiplo,
      IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', 'Venta')) AS TipoOperacion,
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento, DetalleVet.Comisiones AS Comisiones,
          '' AS TotalPedidas, c_articulo.control_peso, 
          #IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          #IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #c_articulo.num_multiplo AS Cajas, DetalleVet.Pza AS Piezas, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(DetalleVet.Pza, '0')),TRUNCATE((IFNULL(DetalleVet.Pza, '0')/c_articulo.num_multiplo), 0)) AS Cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(DetalleVet.Pza, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(DetalleVet.Pza, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(DetalleVet.Pza, '0'), 0)) AS Piezas,
          IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCajas,
          IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPiezas,
          Venta.Cancelada AS Cancelada,
          #IFNULL(sh.Stock, '0') AS InvInicial,

          DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO AND sh.RutaID = Venta.RutaId
          LEFT JOIN PRegalado pr ON pr.SKU = DetalleVet.Articulo AND pr.Docto = Venta.Documento AND pr.DiaO = Venta.DiaO
          WHERE  Venta.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND Venta.Cancelada = 0 

UNION

SELECT DISTINCT 
    um.mav_cveunimed, c_articulo.num_multiplo,
      IF(td.SubTotalPedidas < 0, 'Devoluciones', IF(th.Pedido LIKE 'R%', 'Recarga', IF(th.Pedido IN (SELECT Fol_folio FROM t_pedentregados), 'Entrega', 'PreVenta'))) AS TipoOperacion,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, '' AS Comisiones,
          td.TotalPedidas AS TotalPedidas, c_articulo.control_peso, 
          #IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          #IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(td.Pedidas, '0')),TRUNCATE((IFNULL(td.Pedidas, '0')/c_articulo.num_multiplo), 0)) AS Cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(td.Pedidas, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(td.Pedidas, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(td.Pedidas, '0'), 0)) AS Piezas,
          IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCajas,
          IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPiezas,
          '' AS Cancelada, 
          #IFNULL(sh.Stock, '0') AS InvInicial,
          #IFNULL(td.Pedidas, '0') AS InvInicial,
          '' AS Promociones, RelOperaciones.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
          INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          #LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = t_ruta.ID_Ruta
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND pr.Docto = th.Pedido AND pr.DiaO = RelOperaciones.DiaO
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
) AS liq 
LEFT JOIN StockHistorico sh ON sh.Articulo = liq.cve_articulo AND sh.DiaO = liq.DiaOperativo AND sh.RutaID = liq.Ruta
WHERE liq.Importe >= 0 AND liq.Cajas >= 0 AND liq.Piezas >= 0 AND liq.TipoOperacion != 'Devoluciones' #Mientras se activan las tablas de devoluciones
GROUP BY liq.TipoOperacion, liq.cve_articulo
) AS row_liq
GROUP BY row_liq.cve_articulo
";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $tot_stock_inicial_cajas  = 0;
    $tot_stock_inicial_piezas = 0;
    $tot_ventas_cajas  = 0;
    $tot_ventas_piezas = 0;
    $tot_preventa_cajas  = 0;
    $tot_preventa_piezas = 0;
    $tot_entrega_cajas  = 0;
    $tot_entrega_piezas = 0;
    $tot_recargas_cajas  = 0;
    $tot_recargas_piezas = 0;
    $tot_devoluciones_cajas  = 0;
    $tot_devoluciones_piezas = 0;
    $tot_promociones_cajas  = 0;
    $tot_promociones_piezas = 0;
    $tot_promociones_prev_cajas  = 0;
    $tot_promociones_prev_piezas = 0;
    $total_importe = 0;
    $tot_stock_final_cajas  = 0;
    $tot_stock_final_piezas = 0;
    $tot_pedidos_cajas  = 0;
    $tot_pedidos_piezas = 0;
    $i_filas = 0;
    while($row = mysqli_fetch_array($res))
    {
        extract($row);
        $total = 0;
        $stock_final_cajas = $inv_inicial_cajas;
        $stock_final_piezas = $inv_inicial_piezas;
        $row_promociones_cajas  = 0;
        $row_promociones_piezas = 0;
        $row_promociones_prev_cajas  = 0;
        $row_promociones_prev_piezas = 0;
        $pedidos_cajas  = 0;
        $pedidos_piezas = 0;
        //row_liq.TipoOperacion,';;;;;', row_liq.Importe, ';;;;;', row_liq.IVA, ';;;;;', row_liq.Descuento, ';;;;;', row_liq.Cajas, ';;;;;', row_liq.Piezas, ';;;;;', row_liq.Total) SEPARATOR ':::::'
        $arr_fila = explode(":::::", $fila);
        //Venta;;;;;19846.67000000;;;;;331.0000;;;;;0.0000;;;;;558;;;;;216.0000  
        //Devoluciones;;;;;-228.00000000;;;;;0.0000;;;;;0.0000;;;;;18;;;;;-2.0000  
        //PreVenta;;;;;267952.00000000;;;;;1274.4600;;;;;0.0000;;;;;594;;;;;2414.0000  
        //Entrega;;;;;13860.00000000;;;;;106.2000;;;;;0.0000;;;;;72;;;;;666.0000
    ?>
    <tr>
      <?php /*Clave*/ ?>
      <td align="left"><?php echo $cve_articulo."<br>".$Articulo;; ?></td>
      <?php /*Descripción*/ ?>
      <?php /* ?><td align="left"><?php //echo $Articulo; ?></td><?php */ ?>

      <?php /*Stock Inicial*/ ?>
      <?php /*-----------------*/ ?>
      <?php /*Cajas*/ ?>
      <?php /*-----------------*/ ?>
      <td align="right"><?php echo $stock_final_cajas; ?></td>
      <?php /*-----------------*/ ?>
      <?php /*Piezas*/ ?>
      <?php /*-----------------*/ ?>
      <td align="right"><?php echo $stock_final_piezas; ?></td>
      <?php /*-----------------*/ ?>

      <?php 
          $tot_stock_inicial_cajas  += $stock_final_cajas;
          $tot_stock_inicial_piezas += $stock_final_piezas;
      ?>

      <?php 
      $imprimio = false;
      for($i = 0; $i < count($arr_fila); $i++)
      {
         $arr_res = explode(";;;;;", $arr_fila[$i]);
         if($arr_res[0] == "Venta")
         {
      ?>
              <?php /*Ventas*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[4]; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[5]; ?></td>
              <?php /*-----------------*/ ?>
      <?php 
            $tot_ventas_cajas  += $arr_res[4];
            $tot_ventas_piezas += $arr_res[5];
            $total += $arr_res[6];
            $stock_final_cajas -= $arr_res[4];
            $stock_final_piezas -= $arr_res[5];
            $row_promociones_cajas += $arr_res[7];
            $row_promociones_piezas += $arr_res[8];
            $imprimio = true;
            break;
         }
      }

     if($imprimio == false)
     {
    ?>
          <td align="right">0</td>
          <td align="right">0</td>
    <?php 
     }

      ?>

      <?php 
      $imprimio = false;
      for($i = 0; $i < count($arr_fila); $i++)
      {
         $arr_res = explode(";;;;;", $arr_fila[$i]);
         if($arr_res[0] == "PreVenta")
         {
      ?>
              <?php /*PreVenta*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[4]; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[5]; ?></td>
              <?php /*-----------------*/ ?>
      <?php 
            $pedidos_cajas  = $arr_res[4];
            $pedidos_piezas = $arr_res[5];
            $tot_preventa_cajas  += $arr_res[4];
            $tot_preventa_piezas += $arr_res[5];
            $row_promociones_prev_cajas += $arr_res[7];
            $row_promociones_prev_piezas += $arr_res[8];
            $imprimio = true;
            break;
         }
      }

     if($imprimio == false)
     {
    ?>
          <td align="right">0</td>
          <td align="right">0</td>
    <?php 
     }
      ?>

      <?php 
      $imprimio = false;
      for($i = 0; $i < count($arr_fila); $i++)
      {
         $arr_res = explode(";;;;;", $arr_fila[$i]);
         if($arr_res[0] == "Entrega")
         {
      ?>
              <?php /*Entrega*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[4]; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[5]; ?></td>
              <?php /*-----------------*/ ?>
      <?php 
            $total += $arr_res[6];
            $tot_entrega_cajas  += $arr_res[4];
            $tot_entrega_piezas += $arr_res[5];
            $stock_final_cajas -= $arr_res[4];
            $stock_final_piezas -= $arr_res[5];
            $row_promociones_cajas += $arr_res[7];
            $row_promociones_piezas += $arr_res[8];
            $imprimio = true;
            break;
         }
      }

     if($imprimio == false)
     {
    ?>
          <td align="right">0</td>
          <td align="right">0</td>
    <?php 
     }

      ?>

      <?php 
      $imprimio = false;
      for($i = 0; $i < count($arr_fila); $i++)
      {
         $arr_res = explode(";;;;;", $arr_fila[$i]);
         if($arr_res[0] == "Recarga")
         {
      ?>
              <?php /*Recarga*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[4]; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[5]; ?></td>
              <?php /*-----------------*/ ?>
      <?php 
            $tot_recargas_cajas  += $arr_res[4];
            $tot_recargas_piezas += $arr_res[5];

            $stock_final_cajas += $arr_res[4];
            $stock_final_piezas += $arr_res[5];
            $row_promociones_cajas += $arr_res[7];
            $row_promociones_piezas += $arr_res[8];
            $imprimio = true;
            break;
         }
      }

     if($imprimio == false)
     {
    ?>
          <td align="right">0</td>
          <td align="right">0</td>
    <?php 
     }

      ?>

      <?php 
      $imprimio = false;
      for($i = 0; $i < count($arr_fila); $i++)
      {
         $arr_res = explode(";;;;;", $arr_fila[$i]);
         if($arr_res[0] == "Devoluciones")
         {
      ?>
              <?php /*Devoluciones*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[4]; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $arr_res[5]; ?></td>
              <?php /*-----------------*/ ?>
      <?php 
            $total += $arr_res[6];

            $tot_devoluciones_cajas  += $arr_res[4];
            $tot_devoluciones_piezas += $arr_res[5];

            $stock_final_cajas += $arr_res[4];
            $stock_final_piezas += $arr_res[5];
            $row_promociones_cajas += $arr_res[7];
            $row_promociones_piezas += $arr_res[8];
            $imprimio = true;
            break;
         }
      }

     if($imprimio == false)
     {
    ?>
          <td align="right">0</td>
          <td align="right">0</td>
    <?php 
     }
      ?>

              <?php /*Promociones*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $row_promociones_cajas; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $row_promociones_piezas; ?></td>
              <?php /*-----------------*/ ?>


              <?php /*Promociones Preventa*/ ?>
              <?php /*-----------------*/ ?>
              <?php /*Cajas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $row_promociones_prev_cajas; ?></td>
              <?php /*-----------------*/ ?>
              <?php /*Piezas*/ ?>
              <?php /*-----------------*/ ?>
              <td align="right"><?php echo $row_promociones_prev_piezas; ?></td>
              <?php /*-----------------*/ ?>

      <?php 

            $stock_final_cajas -= $row_promociones_cajas;
            $stock_final_piezas -= $row_promociones_piezas;
            $tot_promociones_cajas += $row_promociones_cajas;
            $tot_promociones_piezas += $row_promociones_piezas;
            $tot_promociones_prev_cajas += $row_promociones_prev_cajas;
            $tot_promociones_prev_piezas +=$row_promociones_prev_piezas; 
        ?>

      <?php /*Total $*/ ?>
      <td align="right"><?php echo $total; ?></td>

      <?php $total_importe += $total; ?>

      <?php /*Stock Final*/ ?>
      <?php /*-----------------*/ ?>
      <?php /*Cajas*/ ?>
      <?php /*-----------------*/ ?>
      <td align="right"><?php echo $stock_final_cajas; ?></td>
      <?php /*-----------------*/ ?>
      <?php /*Piezas*/ ?>
      <?php /*-----------------*/ ?>
      <td align="right"><?php echo $stock_final_piezas; ?></td>
      <?php /*-----------------*/ ?>

      <?php /*Total Pedido*/ ?>
      <?php /*-----------------*/ ?>
      <?php /*Cajas*/ ?>
      <?php /*-----------------*/ ?>
      <td align="right"><?php echo ($pedidos_cajas+$row_promociones_prev_cajas); ?></td>
      <?php /*-----------------*/ ?>
      <?php /*Piezas*/ ?>
      <?php /*-----------------*/ ?>
      <td align="right"><?php echo ($pedidos_piezas+$row_promociones_prev_piezas); ?></td>
      <?php /*-----------------*/ ?>


      <?php 
        $tot_stock_final_cajas  += $stock_final_cajas;
        $tot_stock_final_piezas += $stock_final_piezas;
        $tot_pedidos_cajas += ($pedidos_cajas+$row_promociones_prev_cajas);
        $tot_pedidos_piezas += ($pedidos_piezas+$row_promociones_prev_piezas);
      ?>
    </tr>
<?php 

        /*
        $i_filas++;
        if($i_filas == 5)
        {
            <div class="page-break"></div>
        */
        ?>
        
        <?php 
        /*
            $i_filas = 0;
        }
        */
        

    }
?>
    <tr>
      <th scope="col" style="padding-top: 30px;">Total:</th>
      <!--<th scope="col"></th>-->

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_stock_inicial_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_stock_inicial_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_ventas_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_ventas_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_preventa_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_preventa_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_entrega_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_entrega_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_recargas_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_recargas_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_devoluciones_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_devoluciones_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_promociones_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_promociones_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_promociones_prev_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_promociones_prev_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo number_format($total_importe, 2); ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_stock_final_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_stock_final_piezas; ?></th>

      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_pedidos_cajas; ?></th>
      <th scope="col" style="text-align: right;padding-top: 30px;"><?php echo $tot_pedidos_piezas; ?></th>
    </tr>

  </tbody>
</table>
</div>

<?php  

?>
<?php /* ?><div class="page-break"></div><?php */ ?>

<br><br><br>
<h1 style="margin-left: 100px;">Devoluciones</h1>
<br>
<div style="padding: 10px 100px;">
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Clave</th>
      <th scope="col">Artículo</th>
      <th scope="col">Cajas</th>
      <th scope="col">Piezas</th>
      <th scope="col">Importe $</th>
    </tr>

  </thead>
  <tbody>
<?php 
    $sql = "SELECT liq.TipoOperacion, liq.cve_articulo, liq.Articulo, SUM(liq.Importe) AS Importe, SUM(liq.IVA) AS IVA, 
       SUM(liq.Descuento) AS Descuento, SUM(liq.Cajas) AS Cajas, SUM(liq.Piezas) AS Piezas, liq.control_peso, 
       SUM(liq.InvInicial) AS InvInicial, 
       SUM(liq.inv_inicial_cajas) AS inv_inicial_cajas,
       SUM(liq.inv_inicial_piezas) AS inv_inicial_piezas,
       liq.PrCajas, liq.PrPiezas,
       SUM((liq.Importe+liq.IVA-liq.Descuento)) AS Total  
FROM (
SELECT DISTINCT 
      IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', 'Venta')) AS TipoOperacion,
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento, DetalleVet.Comisiones AS Comisiones,
          '' AS TotalPedidas, c_articulo.control_peso, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #c_articulo.num_multiplo AS Cajas, DetalleVet.Pza AS Piezas, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(DetalleVet.Pza, '0')),TRUNCATE((IFNULL(DetalleVet.Pza, '0')/c_articulo.num_multiplo), 0)) AS Cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(DetalleVet.Pza, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(DetalleVet.Pza, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(DetalleVet.Pza, '0'), 0)) AS Piezas,
          IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCajas,
          IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPiezas,
          Venta.Cancelada AS Cancelada,
          IFNULL(sh.Stock, '0') AS InvInicial,
          DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO
          LEFT JOIN PRegalado pr ON pr.SKU = DetalleVet.Articulo AND pr.Docto = Venta.Documento AND pr.DiaO = Venta.DiaO
          WHERE  Venta.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND Venta.Cancelada = 0 

UNION

SELECT DISTINCT 
      IF(td.SubTotalPedidas < 0, 'Devoluciones', IF(th.Pedido LIKE 'R%', 'Recarga', IF(th.Pedido IN (SELECT Fol_folio FROM t_pedentregados), 'Entrega', 'PreVenta'))) AS TipoOperacion,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, '' AS Comisiones,
          td.TotalPedidas AS TotalPedidas, c_articulo.control_peso, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(td.Pedidas, '0')),TRUNCATE((IFNULL(td.Pedidas, '0')/c_articulo.num_multiplo), 0)) AS Cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(td.Pedidas, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(td.Pedidas, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(td.Pedidas, '0'), 0)) AS Piezas,
          IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCajas,
          IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPiezas,
          '' AS Cancelada, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS Promociones, RelOperaciones.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
          INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND pr.Docto = th.Pedido AND pr.DiaO = RelOperaciones.DiaO
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
) AS liq WHERE liq.TipoOperacion = 'Devoluciones' #Mientras se activan las tablas de devoluciones
GROUP BY liq.TipoOperacion, liq.cve_articulo
";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $tot_cajas    = 0;
    $tot_piezas   = 0;
    //$tot_cajaspr  = 0;
    //$tot_piezaspr = 0;
    $tot_importe  = 0;

    while($row = mysqli_fetch_array($res))
    {
        extract($row);
?>

    <tr>
      <td scope="col"><?php echo $cve_articulo; ?></td>
      <td scope="col"><?php echo $Articulo; ?></td>
      <td scope="col" style="text-align: right;"><?php echo $Cajas; ?></td>
      <td scope="col" style="text-align: right;"><?php echo $Piezas; ?></td>
      <td scope="col" style="text-align: right;"><?php echo number_format($Importe, 2); ?></td>
    </tr>
<?php 
    $tot_cajas    += $Cajas;
    $tot_piezas   += $Piezas;
    //$tot_cajaspr  += $PrCajas;
    //$tot_piezaspr += $PrPiezas;
    $tot_importe  += $Importe;
    }
?>
    <tr>
      <th scope="col"></th>
      <th scope="col" style="text-align: right;">Total:</th>
      <th scope="col" style="text-align: right;"><?php echo number_format($tot_cajas, 2); ?></th>
      <th scope="col" style="text-align: right;"><?php echo number_format($tot_piezas, 2); ?></th>
      <th scope="col" style="text-align: right;"><?php echo number_format($tot_importe, 2); ?></th>
    </tr>

  </tbody>
</table>


</div>

<br><br><br>
<div class="page-break"></div>
<h1 style="margin-left: 100px;">Crédito y Cobranza</h1>
<br>
<div style="padding: 10px 100px;">
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Documento</th>
      <th scope="col">Cliente</th>
      <th scope="col">Crédito $</th>
      <th scope="col">Cobranza $</th>
    </tr>

  </thead>
  <tbody>
<?php 
/*
    $sql = "SELECT 
            #cob.Documento, cte.RazonSocial as Cliente, IFNULL(cob.Saldo, 0) AS limite_credito, 
            #IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = cob.Documento AND RutaId=r.ID_Ruta AND DiaO=$diao),0) AS saldo
            cob.Documento, cte.Cve_Clte, cte.RazonSocial AS Cliente, (IFNULL(cob.Saldo, 0)) AS limite_credito, 
            SUM(IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = cob.Documento AND RutaId=r.ID_Ruta AND DiaO=$diao),0)) AS saldo
            FROM Cobranza cob
            LEFT JOIN DetalleCob dc ON dc.Documento = cob.Documento #AND dc.DiaO = cob.DiaO
            LEFT JOIN c_destinatarios d ON d.id_destinatario = cob.Cliente
            LEFT JOIN c_cliente cte ON cte.Cve_Clte = d.Cve_Clte
            LEFT JOIN t_ruta r ON r.ID_Ruta = cob.RutaId
            WHERE (cob.DiaO = $diao) AND r.cve_ruta = '$ruta' 
            AND cob.RutaId = dc.RutaId
            #AND IFNULL(dc.Abono, 0) > 0  #OR dc.DiaO = $diao
            GROUP BY Cve_Clte, Documento
            ";
*/
    $sql = "SELECT 
             cob.Documento, cte.Cve_Clte, cte.RazonSocial AS Cliente, (IFNULL(cob.Saldo, 0)) AS limite_credito, 
            0 AS saldo
            FROM Cobranza cob
            LEFT JOIN DetalleCob dc ON dc.Documento = cob.Documento and dc.DiaO = cob.DiaO
            LEFT JOIN c_destinatarios d ON d.id_destinatario = cob.Cliente
            LEFT JOIN c_cliente cte ON cte.Cve_Clte = d.Cve_Clte
            LEFT JOIN t_ruta r ON r.ID_Ruta = cob.RutaId
            WHERE (cob.DiaO = $diao) AND r.cve_ruta = '$ruta' 
            AND cob.RutaId = dc.RutaId AND cob.Status = 2
            GROUP BY Cve_Clte, Documento

UNION 

SELECT 
             cob.Documento, cte.Cve_Clte, cte.RazonSocial AS Cliente, (IFNULL(cob.Saldo, 0)) AS limite_credito, 
            SUM(IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = cob.Documento AND RutaId=r.ID_Ruta AND DiaO=$diao AND Documento = cob.Documento),0)) AS saldo
            FROM Cobranza cob
            LEFT JOIN DetalleCob dc ON dc.Documento = cob.Documento
            LEFT JOIN c_destinatarios d ON d.id_destinatario = cob.Cliente
            LEFT JOIN c_cliente cte ON cte.Cve_Clte = d.Cve_Clte
            LEFT JOIN t_ruta r ON r.ID_Ruta = cob.RutaId
            WHERE (cob.DiaO = $diao OR dc.DiaO = $diao) AND r.cve_ruta = '$ruta'  
            #AND cob.Status = 1, EL 12/3/2025, ME DIJERON QUE NO SE DEBE TOMAR EN CUENTA EL STATUS PARA COBRANZAS
            GROUP BY Cve_Clte, Documento

            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $tot_limite_credito = 0;
    $tot_saldo = 0;
    while($row = mysqli_fetch_array($res))
    {
        extract($row);
?>
    <tr>
      <td scope="col"><?php echo $Documento; ?></td>
      <td scope="col"><?php echo $Cliente; ?></td>
      <td scope="col" style="text-align: right;"><?php echo number_format($limite_credito,2); ?></td>
      <td scope="col" style="text-align: right;"><?php echo number_format($saldo,2); ?></td>
    </tr>
<?php 
        $tot_limite_credito += $limite_credito;
        $tot_saldo += $saldo;
    }
?>
    <tr>
      <th scope="col"></th>
      <th scope="col" style="text-align: right;">Total:</th>
      <th scope="col" style="text-align: right;"><?php echo number_format($tot_limite_credito, 2); ?></th>
      <th scope="col" style="text-align: right;"><?php echo number_format($tot_saldo, 2); ?></th>
    </tr>

  </tbody>
</table>


</div>

<?php /* ?><div class="page-break"></div><?php */ ?>

<br><br><br>

<h1 style="margin-left: 100px;">Resumen Financiero</h1>
<br>
<div style="padding: 10px 100px;">
<table class="table table-striped">
  <tbody>
<?php 
/*
SELECT liq.DiaOperativo, liq.rutaName,
liq.TipoOperacion, 
IF(liq.TipoOperacion = 'Cobranza', TRUNCATE(SUM(liq.Abono), 2), TRUNCATE(SUM((liq.Importe+liq.IVA)), 2)) AS Total, 
TRUNCATE(SUM(liq.Descuento), 2) AS Descuento
FROM (
SELECT DISTINCT 
      #IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', IF(Venta.Documento IN (SELECT Documento FROM Cobranza) AND IFNULL(DetalleCob.Abono, 0) > 0, 'Cobranza', IF(Venta.TipoVta = 'Credito', 'Credito', 'Contado')))) AS TipoOperacion,
      IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', IF(Venta.TipoVta = 'Credito', 'Credito', 'Contado'))) AS TipoOperacion,
      
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
          SUM(DetalleVet.Importe) AS Importe, SUM(DetalleVet.IVA) AS IVA, IFNULL(DetalleVet.DescMon, 0) AS Descuento, DetalleVet.Comisiones AS Comisiones,
          '' AS TotalPedidas, c_articulo.control_peso, 0 AS Abono,

          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,

          c_articulo.num_multiplo AS Cajas, DetalleVet.Pza AS Piezas, Venta.Cancelada AS Cancelada,
          IFNULL(sh.Stock, '0') AS InvInicial,
          DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          #LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento AND Cobranza.RutaId = t_ruta.ID_Ruta #AND Cobranza.DiaO = Venta.DiaO
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
          #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id and DetalleCob.DiaO = 31
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO AND sh.RutaID = Venta.RutaId
          WHERE  Venta.DiaO = 31 AND t_ruta.cve_ruta = 'CREMERIA' AND Venta.Cancelada = 0 
          GROUP BY TipoOperacion
UNION
;
SELECT DISTINCT 
    'Cobranza' AS TipoOperacion,
          '' AS Fecha, Cobranza.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Cobranza.Cliente AS Cliente, 
          '' AS Responsable,
          '' AS nombreComercial, Cobranza.Documento AS Folio, 'Credito' AS Tipo, '' AS metodoPago,
          0 AS Importe, 0 AS IVA, 0 AS Descuento, 0 AS Comisiones,
          '' AS TotalPedidas, '' AS control_peso, (IFNULL(DetalleCob.Abono, 0)) AS Abono,

          '' AS inv_inicial_cajas,
          '' AS inv_inicial_piezas,

          1 AS Cajas, '' AS Piezas, 0 AS Cancelada,
          0 AS InvInicial,
          0 AS Promociones, IF(IFNULL(DetalleCob.Abono, 0) = 0, Cobranza.DiaO, DetalleCob.DiaO) AS DiaOperativo, '' AS cve_articulo,
          '' AS Articulo
          FROM Cobranza
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Cobranza.RutaId 
          #LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          #LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          #LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          #INNER JOIN c_almacenp ON c_almacenp.clave = Cobranza.IdEmpresa
          #inner JOIN Cobranza ON Cobranza.Documento = Venta.Documento AND Cobranza.RutaId = t_ruta.ID_Ruta #AND Cobranza.DiaO = Venta.DiaO
          LEFT JOIN DiasO ON DiasO.DiaO = Cobranza.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id AND DetalleCob.RutaId = t_ruta.ID_Ruta AND DetalleCob.DiaO = 31
          #LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          #LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          #LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Cobranza.Cliente
          #LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          #LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          #LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO AND sh.RutaID = Venta.RutaId
          WHERE  DetalleCob.DiaO = 31 AND t_ruta.cve_ruta = 'CREMERIA' 
          GROUP BY TipoOperacion
;
UNION 

SELECT DISTINCT 
      IF(td.SubTotalPedidas < 0, 'Devoluciones', IF(th.Pedido LIKE 'R%', 'Recarga', IF(th.Pedido IN (SELECT Fol_folio FROM t_pedentregados), 'Entrega', 'PreVenta'))) AS TipoOperacion,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          SUM(td.SubTotalPedidas) AS Importe, SUM(td.IVAPedidas) AS IVA, td.DescuentoPedidas AS Descuento, '' AS Comisiones,
          td.TotalPedidas AS TotalPedidas, c_articulo.control_peso, 0 AS Abono,

          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,

          c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, '' AS Cancelada, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS Promociones, RelOperaciones.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido AND Cobranza.RutaId = t_ruta.ID_Ruta #AND Cobranza.DiaO = 31
          INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido AND RelOperaciones.RutaId = t_ruta.ID_Ruta
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO AND DiasO.RutaId = RelOperaciones.RutaId
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id AND DetalleCob.DiaO = 31
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = RelOperaciones.RutaId
          WHERE  RelOperaciones.DiaO = 31 AND t_ruta.cve_ruta = 'CREMERIA' AND th.Cancelada = 0 
          GROUP BY TipoOperacion
) AS liq 
GROUP BY liq.TipoOperacion
*/
    $sql = "
SELECT liq.DiaOperativo, liq.rutaName,
liq.TipoOperacion, 
IF(liq.TipoOperacion = 'Cobranza', TRUNCATE(SUM(liq.Abono), 2), TRUNCATE(SUM((liq.Importe+liq.IVA)), 2)) AS Total, 
TRUNCATE(SUM(liq.Descuento), 2) AS Descuento
FROM (
SELECT DISTINCT 
      #IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', IF(Venta.Documento IN (SELECT Documento FROM Cobranza) AND IFNULL(DetalleCob.Abono, 0) > 0, 'Cobranza', IF(Venta.TipoVta = 'Credito', 'Credito', 'Contado')))) AS TipoOperacion,
     IF(Venta.TipoVta = 'Credito', 'Credito', IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', IF(Venta.Documento IN (SELECT Documento FROM Cobranza) AND IFNULL(DetalleCob.Abono, 0) > 0, 'Cobranza', 'Contado')))) AS TipoOperacion,
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,

          #SUM(DetalleVet.Importe) AS Importe, SUM(DetalleVet.IVA) AS IVA, IFNULL(DetalleVet.DescMon, 0) AS Descuento, DetalleVet.Comisiones AS Comisiones,

          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, IFNULL(DetalleVet.DescMon, 0) AS Descuento, DetalleVet.Comisiones AS Comisiones,

          '' AS TotalPedidas, c_articulo.control_peso, IFNULL(Cobranza.Saldo, '') AS Abono,

          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,

          c_articulo.num_multiplo AS Cajas, DetalleVet.Pza AS Piezas, Venta.Cancelada AS Cancelada,
          IFNULL(sh.Stock, '0') AS InvInicial,
          DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento AND Cobranza.RutaId = t_ruta.ID_Ruta AND Cobranza.DiaO = Venta.DiaO
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO AND sh.RutaID = Venta.RutaId
          WHERE  Venta.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND Venta.Cancelada = 0 
          #GROUP BY TipoOperacion
UNION

SELECT DISTINCT 
      IF(td.SubTotalPedidas < 0, 'Devoluciones', IF(th.Pedido LIKE 'R%', 'Recarga', IF(th.Pedido IN (SELECT Fol_folio FROM t_pedentregados), 'Entrega', 'PreVenta'))) AS TipoOperacion,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          SUM(td.SubTotalPedidas) AS Importe, SUM(td.IVAPedidas) AS IVA, td.DescuentoPedidas AS Descuento, '' AS Comisiones,
          td.TotalPedidas AS TotalPedidas, c_articulo.control_peso, IFNULL(Cobranza.Saldo, '') AS Abono,

          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,

          c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, '' AS Cancelada, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS Promociones, RelOperaciones.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido AND Cobranza.RutaId = t_ruta.ID_Ruta AND Cobranza.DiaO = $diao
          INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido AND RelOperaciones.RutaId = t_ruta.ID_Ruta
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO AND DiasO.RutaId = RelOperaciones.RutaId
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = RelOperaciones.RutaId
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
          GROUP BY TipoOperacion
) AS liq 
GROUP BY liq.TipoOperacion
 ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $preventa = 0;
    $venta_contado = 0;
    $venta_credito = 0;
    $devoluciones = 0;
    $cobranza = 0;
    $descuentos_vp = 0;
    $descuentos_credito = 0;

    while($row = mysqli_fetch_array($res))
    {
        extract($row);
        if($TipoOperacion == 'PreVenta') $preventa += $Total;
        if($TipoOperacion == 'Contado') $venta_contado += $Total;
        if($TipoOperacion == 'Credito') $venta_credito += $Total;
        if($TipoOperacion == 'Devoluciones') $devoluciones += $Total;
        //if($TipoOperacion == 'Cobranza') $cobranza += $Total;
        if($Descuento > 0) $descuentos_vp += $Descuento;
        if($Descuento > 0 && $TipoOperacion == 'Credito') $descuentos_credito += $Descuento;
    }
?>
<?php 
//************************************************************************************
//El 15-06-2023, dijeron que colocara esta fila aquí, aunque no sume a la última
//************************************************************************************
?>
    <tr>
      <th scope="col" style="text-align: left;">Venta Total</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($total_importe, 2); ?></td>
    </tr>
<?php 
///**************************************************************************
 ?>
    <tr>
      <th scope="col" style="text-align: left;">Preventa</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($preventa, 2); ?></td>
    </tr>

    <tr>
      <th scope="col" style="text-align: left;">Ventas Contado</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($venta_contado, 2); ?></td>
    </tr>

    <tr>
      <th scope="col" style="text-align: left;">Ventas Credito</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($venta_credito, 2); ?></td>
    </tr>

    <tr>
      <th scope="col" style="text-align: left;">Devoluciones</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($devoluciones, 2); ?></td>
    </tr>

    <tr>
      <th scope="col" style="text-align: left;">Cobranza</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($tot_saldo, 2); ?></td>
    </tr>

    <tr>
      <th scope="col" style="text-align: left;">Descuentos</th>
      <td scope="col" style="text-align: right;"><?php echo number_format($descuentos_vp, 2); ?></td>
    </tr>

    <tr>
      <td scope="col"></td>
      <td scope="col"></td>
    </tr>

    <tr>
      <th scope="col" style="text-align: left;">Total a Liquidar</th>
      <th scope="col" style="text-align: right;"><?php echo number_format(($preventa+$venta_contado+/*$venta_credito+*/$devoluciones+$tot_saldo-$descuentos_vp+$descuentos_credito), 2); ?></th>
    </tr>

  </tbody>
</table>


</div>


<?php 

?>


</body>
</html>

