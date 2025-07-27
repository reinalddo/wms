<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Reporte de Cobranza";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Cliente</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Cliente</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Abono</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Saldo Final</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $criterio  = $_GET['criterio'];
    $ruta      = $_GET['ruta'];
    $diao      = $_GET['diao'];
    $operacion = $_GET['operacion'];
    $fecha_inicio  = $_GET['fechaini'];
    $fecha_fin  = $_GET['fechafin'];
    $status_list   = $_GET['status_list'];
    $almacen   = $_GET['almacen'];

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
    //if($status == '') 
        $status = 0;

    //$sql = "CALL SPAD_ReporteCobranzaVtasH('$cve_almacen',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','',$diao,'', $status)";
    $sql = "CALL SPAD_ReporteCobranzaPagos('$cve_almacen',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','',$diao,'', $status)";
    if($operacion == 'PreVenta' || $operacion == 'Entrega')
        $sql = "CALL SPAD_ReportePedidosEntregasH('$cve_almacen',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','',$diao,'')";

    if($criterio != '')
    {
        //$sql = "CALL SPAD_ReporteConCobranzaVtas('$cve_almacen', '$criterio')";
        $sql = "CALL SPAD_ReporteConCobranzaPagos('$cve_almacen', '$criterio')";
    }

    if (!($res = mysqli_query($conn, $sql))) echo "Falló la preparación SP: (" . mysqli_error($conn) . ") ";

    $i = 2;

    while ($row = mysqli_fetch_array($res)) {

        //extract($row);
/*
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $CveCliente; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $NombreComercial; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Abono; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Fecha; ?></div>
        <?php 
*/

        $Cliente = $row['Cliente'];
        $nombreComercial = $row['nombreComercial'];
        $Folio = $row['Folio'];
        $Total = ($row['Importe']+$row['IVA']);
        $Abono = $row['Abono'];
        $SaldoFinal = $row['Saldo Final'];
        $Fecha = $row['Fecha'];
            
            
            

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $nombreComercial; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Total; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Abono; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $SaldoFinal; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $Fecha; ?></div>

        <?php 
        $i++;

    }
/*
    $sql1 = "SELECT DISTINCT
      Venta.ID,
      DetalleCob.DiaO AS DiaOperativoCob,
      'Venta' AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      Venta.Documento AS Folio,
      c_cliente.Cve_Clte as CveCliente,
      DetalleCob.Fecha AS FechaBusq,
      DATE_FORMAT(DetalleCob.Fecha, '%d-%m-%Y') as Fecha,
      c_cliente.RazonSocial AS NombreComercial,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor as vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      SUM(IFNULL((DetalleCob.Abono),0)) AS Abono

      FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND Venta.RutaId = t_ruta.ID_Ruta 
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '1'
      LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
      LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
      LEFT JOIN DetalleCob ON DetalleCob.Documento = Cobranza.Documento
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      WHERE Venta.TipoVta = 'Credito' and Venta.Cancelada = 0
       {$sql_ruta}    
       {$sql_diao} 
       {$sql_fecha} 
       {$sql_operacion} 
       {$sql_search} 
      GROUP BY ID";

    $sql2 = "
UNION

SELECT DISTINCT
      CONCAT(td_pedido.IdEmpresa, td_pedido.Pedido) as ID,
        DetalleCob.DiaO AS DiaOperativoCob,
      IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta') AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      th_pedido.Pedido AS Folio,
      c_cliente.Cve_Clte as CveCliente,
      RelOperaciones.Fecha AS FechaBusq,
      DATE_FORMAT(RelOperaciones.Fecha, '%d-%m-%Y') as Fecha,
      c_cliente.RazonSocial AS nombreComercial,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor as vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      SUM(IFNULL(DetalleCob.Abono,0)) AS Abono
      FROM V_Cabecera_Pedido th_pedido
      LEFT JOIN V_Detalle_Pedido td_pedido ON td_pedido.Pedido= th_pedido.Pedido
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th_pedido.Ruta
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th_pedido.cve_Vendedor
      LEFT JOIN FormasPag ON FormasPag.IdFpag = th_pedido.FormaPag 
       LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo 
      INNER JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN Cobranza ON CONCAT(Cobranza.RutaId,'_', Cobranza.Documento) = th_pedido.Pedido
      INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th_pedido.Pedido
      LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th_pedido.Cod_Cliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN V_Detalle_Pedido td_pedido_art ON td_pedido_art.Pedido= th_pedido.Pedido AND td_pedido_art.Articulo = c_articulo.cve_articulo AND td_pedido_art.Ruta = th_pedido.Ruta
      #LEFT JOIN PRegalado pr ON td_pedido.Pedido IN (CONCAT(pr.RutaId, '_', pr.Docto),pr.Docto) AND pr.SKU = td_pedido.Articulo AND pr.IdEmpresa = th_pedido.IdEmpresa AND pr.RutaId = td_pedido.Ruta AND pr.Cliente = th_pedido.Cod_Cliente
      LEFT JOIN PRegalado pr ON td_pedido_art.Pedido = pr.Docto #and pr.SKU = td_pedido_art.Articulo and pr.IdEmpresa = th_pedido.IdEmpresa and pr.RutaId = td_pedido.Ruta and pr.Cliente = th_pedido.Cod_Cliente
      WHERE th_pedido.TipoPedido='Credito' and th_pedido.Cancelada = 0 AND th_pedido.Pedido NOT IN (SELECT Documento FROM Venta WHERE IdEmpresa = c_almacenp.clave)
       {$sql_ruta} 
       {$sql_diao} 
       {$sql_fecha2} 
       {$sql_operacion2} 
       {$sql_search2} 
      GROUP BY ID
      ORDER BY FechaBusq DESC";
*/
  ?>
    <?php /* ?><div cell="B<?php echo $i+2; ?>"><?php echo $sql; ?></div> <?php */ ?>
    <?php /* ?><div cell="B<?php echo $i+2; ?>"><?php echo $sql1; ?></div> <?php */ ?>
    <?php /* ?><div cell="B<?php echo $i+4; ?>"><?php echo $sql2; ?></div> <?php */ ?>
    
</div>