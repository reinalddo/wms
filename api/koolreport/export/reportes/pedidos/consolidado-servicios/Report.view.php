<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Reporte de Venta</title>
</head>
<body style="margin: 30px;">
<style>
    .encabezado
    {
        font-size: 14px;
        float: right;
        text-align: right;
        right: 0px;
        position: absolute;
        top: 0;
    }

    .under_line
    {
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
    }

    .datos_cliente_entrega
    {
        margin-top: 50px;
        font-size: 18px;
    }

</style>
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];

    $almacen = "";
    if(isset($_GET['almacen']))
        $almacen = $_GET['almacen'];

    $sql = "SET NAMES 'utf8mb4';";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
/*
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn, $charset);
*/
    $sql = "SELECT imagen, des_cia, des_direcc, distrito, des_telef, des_email FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $cliente = ""; $usuario = ""; $fecha = "";
    if(isset($_GET['pedido_venta']))
    {
        $sql = "SELECT Cve_clte, Cve_Usuario, DATE_FORMAT(Fec_Pedido, '%d-%m-%Y') AS Fec_Pedido FROM th_pedido WHERE Fol_folio = '{$folio}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }

        $row_cliente = mysqli_fetch_array($res);
        extract($row_cliente);
        $cliente = $Cve_clte;
        $usuario = $Cve_Usuario;
        $fecha = $Fec_Pedido;
    }


    ?>
    <div class="row under_line">
        <div class="col-4 text-center encabezado_logo">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>

        <?php  
                $sql = "SELECT IFNULL(Valor, '0') AS SFA FROM t_configuraciongeneral WHERE cve_conf = 'SFA' LIMIT 1";
                if (!($res = mysqli_query($conn, $sql))){
                    echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
                }
                $sfa = mysqli_fetch_array($res)["SFA"];


                if($sfa == 1)
                {
                    $sql = "SELECT * FROM CTiket WHERE IdEmpresa = '{$almacen}';";
                    $query = mysqli_query($conn, $sql);

                    if (!($res = mysqli_query($conn, $sql))){
                        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
                    }

                if(mysqli_num_rows($res) > 0)
                {
                    $row = mysqli_fetch_array($res);
                    extract($row);
            ?>
                    <div class="col-8 encabezado">
                    <span><?php echo /*utf8_encode*/($Linea1); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Linea2); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Linea3); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Linea4); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Mensaje); ?></span><br>
                    </div>
            <?php 
                }
                }
                else
                {
            ?>
                    <div class="col-8 encabezado">
                    <span><?php echo /*utf8_encode*/($des_cia); ?></span><br>
                    <span><?php echo /*utf8_encode*/($des_direcc); ?></span><br>
                    <span><?php echo /*utf8_encode*/($distrito); ?></span><br>
                    <span><?php echo /*utf8_encode*/($des_telef); ?></span><br>
                    <span><?php echo /*utf8_encode*/($des_email); ?></span><br>
                    <span><?php if($fecha) echo ($fecha); ?></span><br>
                    </div>
            <?php 
                }
        ?>

    </div>

    <?php 
    $cliente = "";
        if(isset($_GET['cliente']))
            $cliente = $_GET['cliente'];


        $sql = "SELECT RazonSocial, CalleNumero, Estado, Ciudad, CodigoPostal, Pais, Contacto, Telefono1, Telefono2, Telefono3, email_cliente FROM c_cliente WHERE Cve_Clte = '{$cliente}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }

        if(mysqli_num_rows($res))
        {
            $row = mysqli_fetch_array($res);
            extract($row);
    ?>

    <div class="row datos_cliente_entrega" style="font-size: 10pt;">
        <div class="col-xs-6">
            <b>Datos del cliente</b><br>
            <span><?php echo /*utf8_decode*/($RazonSocial); ?></span><br>
            <span><?php echo /*utf8_decode*/($CalleNumero); ?></span><br>
            <?php /* ?><span>Transito</span><br><?php */ ?>
            <span><?php echo /*utf8_decode*/($Estado).", "./*utf8_decode*/($Ciudad); ?></span><br>
            <span><?php echo $CodigoPostal.", "./*utf8_decode*/($Pais); ?></span><br>
            <span>Contacto: <?php echo /*utf8_decode*/($Contacto); ?></span><br>
            <span>Tel. de Contacto:</span><br>
            <?php if($Telefono1){ ?>
            <span><?php echo $Telefono1; ?></span><br>
            <?php } ?>
            <?php if($Telefono2){ ?>
            <span><?php echo $Telefono2; ?></span><br>
            <?php } ?>
            <?php if($Telefono3){ ?>
            <span><?php echo $Telefono3; ?></span><br>
            <?php } ?>
            <?php if($email_cliente){ ?>
            <span><?php echo /*utf8_decode*/($email_cliente); ?></span><br>
            <?php } ?>
        </div>

        <div class="col-xs-1">&nbsp;</div>

    <?php 
    
        $destinatario = "";
        $sql = "SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '{$cliente}' AND dir_principal = 1";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }
        if(mysqli_num_rows($res) > 0)
        {
            $row_destinatario = mysqli_fetch_array($res);
            extract($row_destinatario);
            $destinatario = $id_destinatario;
            $sql2 = $sql;
        }

        $sql = "SELECT razonsocial, direccion, estado, ciudad, postal, contacto, telefono  FROM c_destinatarios WHERE id_destinatario = '{$destinatario}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }

        if(mysqli_num_rows($res) > 0)
        {
            $row = mysqli_fetch_array($res);
            extract($row);
    ?>

        <div class="col-xs-5" style="font-size: 10pt;">
            <b>Dirección de Entrega</b><br>
            <span><?php echo ($razonsocial); ?></span><br>
            <span><?php echo ($direccion); ?></span><br>
            <span><?php echo ($estado).", ".($ciudad); ?></span><br>
            <span><?php echo $postal; ?></span><br>
            <?php if($contacto){ ?>
            <span><?php echo ($contacto); ?></span><br>
            <?php } ?>
            <?php if($telefono){ ?>
            <span><?php echo ($telefono); ?></span><br>
            <?php } ?>
        </div>
    <?php 
        }
    ?>
    </div>
    <?php 
    }
    ?>

<style>
    #datos_venta, #precios_venta
    {
        margin-top: 50px;
    }

    #datos_venta .num
    {
        width: 50px;
        text-align: center;
        background-color: #cccccc !important;
    }

    #datos_venta thead tr th
    {
        background-color: #cccccc !important;
    }

    #datos_venta td
    {
        background-color: #e6e6e6 !important;
        padding: 10px;
    }

    #datos_venta td, th
    {
        border: 1px solid #fff;
    }

    #datos_venta .desc
    {
        padding: 10px;
        text-align: left;
        width: 600px;
    }

    #datos_venta .desc2
    {
        padding: 10px;
        text-align: left;
        width: 100px;
    }

    #datos_venta .precios
    {
        padding: 10px;
        text-align: center;
        width: 150px;
    }

    #datos_venta .precios, #datos_venta .num, #datos_venta .desc
    {
        font-size: 10pt;
    }

    #precios_venta .titulo_precio_venta, #precios_venta .titulo_precio_venta_last
    {
        width: 950px;
        text-align: right;
        padding: 15px;
    }

    #precios_venta .titulo_precio_venta
    {
        border-bottom: 1px solid #cccccc;
    }

    #precios_venta .valor_precio_venta, #precios_venta .valor_precio_venta_last
    {
        width: 150;
        text-align: right;
        padding: 15px;
    }

    #precios_venta .valor_precio_venta
    {
        background-color: #cccccc !important;
        border-bottom: 1px solid #fff;
    }
</style>


<table id="datos_venta">
  <thead>
    <tr>
      <th class="num"></th>
      <th class="desc">Servicio</th>
      <th class="precios">Cantidad</th>
      <th class="precios">UM</th>
      <th class="precios">P.U.</th>
      <th class="precios">Desc</th>
      <th class="precios">IVA</th>
      <th class="precios">Total</th>
    </tr>
  </thead>
  <tbody>

    <?php 
    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];

    $fecha_inicio = $_GET['fechaini'];
    $fecha_fin = $_GET['fechafin'];

    $sqlBusqueda = "";
    if($criterio!= '')
       $sqlBusqueda = " AND (th.Fol_Folio LIKE '%$criterio%' OR th.Docto_Ref LIKE '%$criterio%' OR th.Cve_clte LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%') ";

    $SQLFecha = "";
    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        $SQLFecha = " AND DATE(th.Fec_Pedido) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(th.Fec_Pedido) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if ($fecha_inicio == $fecha_fin) {
            $SQLFecha = " AND DATE(th.Fec_Pedido) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
        }
    } else if (!empty($fecha_inicio)) {
        $SQLFecha = " AND th.Fec_Pedido >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
    } else if (!empty($fecha_fin)) {
        $SQLFecha = " AND th.Fec_Pedido <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
    }
    $folio = $_GET['folio'];

        $sql = "SELECT DISTINCT td.Cve_Servicio AS Cve_articulo, a.Des_Servicio AS des_articulo, '' AS des_detallada, '' AS cve_lote, 
                                TRUNCATE(SUM(td.Num_cantidad), $decimales_cantidad) AS Num_cantidad, TRUNCATE(td.Precio_unitario, $decimales_costo) as Precio_unitario, TRUNCATE(td.Desc_Importe, $decimales_costo) as Desc_Importe, TRUNCATE(td.IVA, $decimales_costo) as IVA, IFNULL(u.cve_umed, '') AS um
                FROM td_pedservicios td 
                LEFT JOIN th_pedservicios th ON th.Fol_folio = td.Fol_folio
                LEFT JOIN c_servicios a ON a.Cve_Servicio = td.Cve_Servicio
                LEFT JOIN c_unimed u ON u.id_umed = td.id_unimed
                LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_Clte
                INNER JOIN td_proforma pr ON pr.Fol_Folio = th.Fol_Folio AND pr.Fol_Proform = '$folio'
                #WHERE td.Cve_Almac = $almacen {$sqlBusqueda} {$SQLFecha} AND c.Cve_Clte = '$cliente' 
                GROUP BY Cve_articulo";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }
$subtotal = 0;
$descuento = 0;
$iva_s = 0;
        if(mysqli_num_rows($res)>0)
        {
             $i = 0; $subtotal = 0; $descuento = 0; $total = 0; $iva = 0;
            while($row = mysqli_fetch_array($res))
            {
                extract($row);
                $i++;
            ?>
            <tr>
              <td class="num"><?php echo $i; ?></td>
              <td class="desc">
                <b><?php echo $Cve_articulo; ?></b><br>
                <b><?php echo utf8_decode($des_articulo); ?></b><br>
              </td>
              <td class="precios"><?php echo $Num_cantidad; ?></td>
              <td class="precios"><?php echo $um; ?></td>
              <td class="precios"><?php echo $Precio_unitario; ?></td>
              <td class="precios"><?php echo $Desc_Importe; ?></td>
              <td class="precios"><?php echo $IVA; ?></td>
              <td class="precios"><b>$ <?php echo ($Precio_unitario*$Num_cantidad); ?></b></td>
            </tr>
            <?php 
            $subtotal += $Precio_unitario*$Num_cantidad;
            $descuento += $Desc_Importe;
            $iva_s += $IVA;
            }

        }
    ?>

  </tbody>
</table>

<?php 
?>
<table id="precios_venta">
    <tbody>
        <tr>
            <td class="titulo_precio_venta">Sub Total</td>
            <td class="valor_precio_venta">$ <?php echo number_format(($subtotal+$descuento), 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta">Descuentos</td>
            <td class="valor_precio_venta">$ <?php echo number_format($descuento, 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta">Total</td>
            <td class="valor_precio_venta">$ <?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta">Impuesto 16%</td>
            <td class="valor_precio_venta">$ <?php echo number_format($iva, 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta_last"><b>Total</b> </td>
            <td class="valor_precio_venta_last">$ <?php echo number_format(($subtotal+$iva), 2); ?></td>
        </tr>
    </tbody>
</table>

<div style="padding: 10px 100px;">

<table class="table">
  <thead>
    <tr>
      <th style="text-align:left;">Observaciones</th>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
<?php 
$sql = "SELECT s.Fol_Folio AS folioPr, IFNULL(s.Docto_Ref, '') AS referencia, IFNULL(s.Docto_Ped, '') AS pedimento
        FROM th_pedservicios s
        WHERE s.Fol_Folio IN (SELECT Fol_Folio FROM td_proforma WHERE Fol_Proform = '$folio')";

if (!($res = mysqli_query($conn, $sql))){
    echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
}
    while($row = mysqli_fetch_array($res))
    {
        extract($row);

?>
    <tr>
      <td><?php echo $folioPr; ?></td>
      <td><?php echo $referencia; ?></td>
      <td><?php echo $pedimento; ?></td>
    </tr>
<?php 
    }
?>
  </tbody>
</table>
<br><br>
<table class="table">
  <tbody>
<?php 
$sql = "SELECT * FROM th_proforma WHERE Fol_Proform = '$folio'";

if (!($res = mysqli_query($conn, $sql))){
    echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
}
    while($rowp = mysqli_fetch_array($res))
    {
?>
    <tr>
      <td><b>Referencia</b></td><td><?php echo $rowp['Docto_Ref']; ?></td>
    </tr>
    <tr>
      <td><b>Tipo Operación</b></td><td><?php echo $rowp['Tipo_Operacion']; ?></td>
    </tr>
    <tr>
      <td><b>Observaciones</b></td><td><?php echo $rowp['Observaciones']; ?></td>
    </tr>
<?php 
    }   
?>
  </tbody>
</table>
<?php 

/*
?>
<table class="table">
  <thead>
    <tr>
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

    $folio = $_GET['folio'];

    $sql = "
        SELECT DISTINCT td.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                        IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        u.CodigoCSD AS BL, TRUNCATE(td.Num_cantidad, 3) AS Cantidad_Solicitada, TRUNCATE(ts.Cantidad, 3) AS Cantidad_Surtida, 
                        c.nombre_completo as Usuario
        FROM th_pedido th 
        LEFT JOIN td_pedido td ON td.Fol_folio = th.Fol_folio
        LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = th.Fol_folio
        LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
        LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
        LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
        LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
        LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%'
        LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
        WHERE th.Fol_folio = '{$folio}' AND td.Cve_articulo = ts.Cve_articulo AND L.Lote = ts.LOTE
    ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
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
<?php 
*/
?>
</div>

</div>
</body>
</html>

