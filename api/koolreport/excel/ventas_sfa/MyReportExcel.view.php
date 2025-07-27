<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Reporte de Ventas";
set_time_limit(300);
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Comercial</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tipo</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Operación</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>DO</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cve Artículo</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad de Medida</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Precio</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Importe</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>IVA</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descuento</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>
    <div cell="S1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cancelada</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $almacen        = $_GET['almacen'];
    $ruta           = $_GET['ruta'];
    $diao           = $_GET['diao'];
    $operacion      = $_GET['operacion'];
    $fecha_inicio   = $_GET['fechaini'];
    $fecha_fin      = $_GET['fechafin'];
    $cliente        = $_GET['clientes'];
    $tipoV          = $_GET['tipoV'];
    $articulos      = $_GET['articulos'];
    $articulos_obsq = $_GET['articulos_obsq'];
    $criterio       = $_GET['criterio'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $sql_almacen = "SELECT clave FROM c_almacenp WHERE id = '$almacen'";
    if (!($res_almacen = mysqli_query($conn, $sql_almacen))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res_almacen)['clave'];

    if(!$diao) $diao = 0;
    if($fecha_inicio == '') $fecha_inicio = 'NULL';
    else
    {
        $fecha = explode('-', $fecha_inicio);
        $fecha_inicio = $fecha[2]."-".$fecha[1]."-".$fecha[0];
        $fecha_inicio = "'".$fecha_inicio."'";
    }
    if($fecha_fin == '') $fecha_fin = 'NULL';
    else
    {
        $fecha = explode('-', $fecha_fin);
        $fecha_fin = $fecha[2]."-".$fecha[1]."-".$fecha[0];
        $fecha_fin = "'".$fecha_fin."'";
    }

    if($ruta == 'todas') $ruta = "";

    $sql = "CALL SPAD_ReporteVtas('$cve_almacen', '',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','$cliente',$diao,'$articulos')";
    if($operacion == 'PreVenta' || $operacion == 'Entrega')
        $sql = "CALL SPAD_ReportePedidosEntregas('$cve_almacen', '',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','$cliente',$diao,'$articulos')";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }
    //$resP = $res;

    //$count = mysqli_num_rows($res);
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        if(($cajas_total-$PromoC) + ($piezas_total-$PromoP) == 0) continue;
        //if(($cajas_total) + ($piezas_total) == 0) continue;
        if(($Importe+$IVA-$Descuento) == 0) continue;
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Fecha; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Responsable; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Tipo; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $Operacion; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $rutaName; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $DiaO; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo utf8_decode($Articulo); ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $unidadMedida; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo ($cajas_total);//-$PromoC ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo ($piezas_total);//-$PromoP ?></div>
        <div cell="N<?php echo $i; ?>"><?php echo number_format($Precio, 2); ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo number_format($Importe, 2); ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo number_format($IVA, 2); ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo number_format($Descuento, 2); ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo number_format($Importe+$IVA-$Descuento, 2); ?></div>
        <div cell="S<?php echo $i; ?>"><?php echo $Cancelada; ?></div>
        <?php 
        $i++;

    }
  ?>

    <?php if($criterio == 'SQL0000WMS'){ ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php } ?>
</div>

<?php 
$sheet2 = "Promociones";
?>
<div sheet-name="<?php echo $sheet2; ?>">



    <?php
    $styleArray = [
        'font' => [
            'name' => 'Calibri', //'Verdana', 'Arial'
            'size' => 14,
            'bold' => true
        ]
    ];
?>

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>DO</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cve Artículo</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad Medida</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen= $_GET['almacen'];
    $ruta   = $_GET['ruta'];
    $diao   = $_GET['diao'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
/*
    $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo, r.cve_ruta as Ruta,
                        d.Cve_Clte AS Cliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida, pr.Docto AS Folio
                FROM PRegalado pr 
                LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
                LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
                LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
                LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
                WHERE  al.id = '{$almacen}' AND IFNULL(d.razonsocial, '') != '' AND r.cve_ruta = '{$ruta}' AND pr.DiaO = '{$diao}'
                ORDER BY des_articulo
            ";

/*
AND pr.Docto IN (
SELECT ventas.Folio FROM (
            SELECT DISTINCT
          
          DATE_FORMAT(Venta.Fecha, '%d-%m-%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, c_cliente.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
          Venta.DiaO AS DiaO,
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento,
          t_vendedores.Nombre AS Vendedor, 
          'Venta' AS Operacion,
          c_articulo.cve_articulo AS cve_articulo,
          DetalleVet.Descripcion AS Articulo, 
          IF(DetalleVet.Tipo = 0, DetalleVet.Pza, IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, DetalleVet.Pza),TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))) AS cajas_total,
          IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (DetalleVet.Pza - (c_articulo.num_multiplo*TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, DetalleVet.Pza, 0))) AS piezas_total
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId  
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          WHERE 1 {$SQLRuta} {$SQLDiaO1} 

UNION

SELECT DISTINCT
          DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y') AS Fecha, RelOperaciones.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          RelOperaciones.DiaO AS DiaO,
          td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, 
          t_vendedores.Nombre AS Vendedor, 
          IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta') AS Operacion,
           c_articulo.cve_articulo AS cve_articulo,
          td.Descripcion AS Articulo, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0)) AS cajas_total,
          IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0)) AS piezas_total
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta) 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          INNER JOIN t_pedentregados tpe ON tpe.Fol_folio = th.Pedido
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          WHERE 1 {$SQLRuta} {$SQLDiaO2}
          ORDER BY Folio
          ) AS ventas WHERE 1 {$SQLOperacion} {$SQLCliente}
)ORDER BY Folio
*/
/*
    $SQLFecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $SQLFecha = " AND dia.Fecha BETWEEN STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_inicio)) 
      {
        $SQLFecha = " AND dia.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_fin)) 
      {
        $SQLFecha = " AND dia.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      }

    $SQLOperacion = ""; 
    if($operacion)
    {
        $SQLOperacion = " AND pr.Tipo = 'P' ";
        if($operacion == 'Venta')
        {
            $SQLOperacion = " AND pr.Tipo = 'V' ";
        }
    }

    $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo, r.cve_ruta AS Ruta, r.ID_Ruta, 
    d.Cve_Clte AS Cliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida, pr.Docto AS Folio, d.id_destinatario AS CodCliente 
FROM PRegalado pr 
LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
LEFT JOIN DiasO dia ON dia.DiaO = pr.DiaO AND dia.RutaId = pr.RutaId AND dia.IdEmpresa = pr.IdEmpresa
WHERE  al.id = '{$almacen}' {$SQLArticulo_Obseq} {$SQLRutaPr} {$SQLDiaOPr} {$SQLOperacion} {$SQLFecha} 
";
*/

/*
    if($ruta == 'todas') $ruta = "";

    $sql = "CALL SPAD_ReporteVtas('$cve_almacen', '',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','$cliente',$diao,'$articulos')";
    if($operacion == 'PreVenta' || $operacion == 'Entrega')
        $sql = "CALL SPAD_ReportePedidosEntregas('$cve_almacen', '',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','$cliente',$diao,'$articulos')";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    while ($row = mysqli_fetch_array($resP)) {

        extract($row);
        if(($cajas_total-$PromoC) + ($piezas_total-$PromoP) == 0) continue;
        //if(($cajas_total) + ($piezas_total) == 0) continue;
        if(($Importe+$IVA-$Descuento) == 0) continue;
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Fecha; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Responsable; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Tipo; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $Operacion; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $rutaName; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $DiaO; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo utf8_decode($Articulo); ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $unidadMedida; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo ($cajas_total);//-$PromoC ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo ($piezas_total);//-$PromoP ?></div>
        <div cell="N<?php echo $i; ?>"><?php echo number_format($Precio, 2); ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo number_format($Importe, 2); ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo number_format($IVA, 2); ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo number_format($Descuento, 2); ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo number_format($Importe+$IVA-$Descuento, 2); ?></div>
        <div cell="S<?php echo $i; ?>"><?php echo $Cancelada; ?></div>
        <?php 
        $i++;

    }

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

*/
    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        if(($Importe+$IVA-$Descuento) > 0) continue;

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $rutaName; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $DiaO; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo utf8_decode($Articulo); ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo ($PromoC == 0)?($PromoP):($PromoC); ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $unidadMedida; ?></div>
        <?php 
        $i++;

    }
  ?>
    <?php if($criterio == 'SQL0000WMS'){ ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php } ?>

</div>