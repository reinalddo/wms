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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha De Registro</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha De Vencimiento</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Cliente</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Cliente</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Saldo Inicial</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Abono</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Saldo Actual</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $criterio  = $_GET['criterio'];
    $ruta      = $_GET['ruta'];
    $diao      = $_GET['diao'];
    $operacion = $_GET['operacion'];
    $fecha_inicio  = $_GET['fechaini'];
    $fecha_fin  = $_GET['fechafin'];
    $almacen   = $_GET['almacen'];


      $sql_fecha = "";
      $sql_fecha2 = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

          $sql_fecha = " AND DATE(Venta.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(Venta.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
          if($fecha_inicio == $fecha_fin)
          $sql_fecha = " AND DATE(Venta.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

          $sql_fecha2 = " AND DATE(RelOperaciones.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(RelOperaciones.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
          if($fecha_inicio == $fecha_fin)
          $sql_fecha2 = " AND DATE(RelOperaciones.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $sql_fecha = " AND Venta.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
        $sql_fecha2 = " AND RelOperaciones.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        $sql_fecha = " AND Venta.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
        $sql_fecha2 = " AND RelOperaciones.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
      }

      $sql_search = "";
      $sql_search2 = "";
      if (!empty($search) ) 
      {
            $sql_search = " AND (t_ruta.cve_ruta like '%$search%' OR Venta.Documento like '%$search%' OR c_cliente.Cve_Clte like '%$search%' OR c_cliente.RazonSocial like '%$search%' OR t_vendedores.Nombre like '%$search%' OR t_vendedores.Cve_Vendedor like '%$search%') ";
            $sql_search2 = " AND (t_ruta.cve_ruta like '%$search%' OR th_pedido.Pedido like '%$search%' OR c_cliente.Cve_Clte like '%$search%' OR c_cliente.RazonSocial like '%$search%' OR t_vendedores.Nombre like '%$search%' OR t_vendedores.Cve_Vendedor like '%$search%') ";
      }

      $sql_diao = "";
      $sql_diao2 = "";
      if($diao != "")
      {
            //$sql_diao = " AND (Venta.DiaO = '$diao' OR Cobranza.DiaO = '$diao') ";
            $sql_diao = " AND (Cob.DiaO = '$diao') ";
            $sql_diao2 = " AND RelOperaciones.DiaO = '$diao' ";
      }

      $sql_ruta = "";
      if($ruta)
      {
        $sql_ruta = " AND t_ruta.cve_ruta = '$ruta' ";

        if($ruta == 'todas')
        {
            $sql_ruta = " AND t_ruta.cve_ruta != '' ";
        }
      }

      $sql_operacion = "";$sql_operacion2 = "";
      if($operacion)
      {
        if($operacion == 'venta')
        {
            $sql_operacion = "";
            $sql_operacion2 = " AND 0 ";
        }

        if($operacion == 'entrega')
        {
            $sql_operacion = " AND 0 ";
            $sql_operacion2 = " AND RelOperaciones.Tipo = 'Entrega' ";
        }

      }




    $sql = "
SELECT cob.FechaReg, cob.FechaVence, cob.Folio, cob.CveCliente, cob.NombreComercial, cob.SaldoInicial, SUM(cob.Abono) AS Abono, cob.saldoActual FROM (
    SELECT DISTINCT
      Venta.ID,
      Venta.DiaO AS DiaOperativo,
      Cob.DiaO AS DiaOperativoCobranza,
      'Venta' AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      Venta.Documento AS Folio,
      c_cliente.Cve_Clte AS CveCliente,
      Venta.Fecha AS FechaBusq,
      c_cliente.RazonSocial AS NombreComercial,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor AS vendedorID,
      t_vendedores.Cve_Vendedor AS cveVendedor, 
      Cob.Saldo AS SaldoInicial,
      IFNULL((DetalleCob.Abono),0) AS Abono,
      Cob.Saldo-IFNULL((DetalleCob.Abono),0) saldoActual,
      DATE_FORMAT(Cob.FechaReg, '%d-%m-%Y') as FechaReg ,
      DATE_FORMAT(Cob.FechaVence, '%d-%m-%Y') as FechaVence 
      FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
      INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
       LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo 
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '{$almacen}'
      #LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
      LEFT JOIN Vw_Cobranza Cob ON Cob.Documento = Venta.Documento AND Cob.Ruta = Venta.RutaId
      LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cob.IdCobranza
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
      WHERE Venta.TipoVta = 'Credito' AND Venta.Cancelada = 0 #AND Cob.Status = 1 
       {$sql_ruta}    
       {$sql_diao} 
       {$sql_fecha} 
       {$sql_operacion} 
       {$sql_search} 
      #GROUP BY ID
      
UNION

SELECT DISTINCT
      CONCAT(td_pedido.IdEmpresa, td_pedido.Pedido) AS ID,
      RelOperaciones.DiaO AS DiaOperativo,
      '' AS DiaOperativoCobranza,
      'PreVenta' AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      th_pedido.Pedido AS Folio,
      c_cliente.Cve_Clte AS CveCliente,
      RelOperaciones.Fecha AS FechaBusq,
      c_cliente.RazonSocial AS nombreComercial,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor AS vendedorID,
      t_vendedores.Cve_Vendedor AS cveVendedor, 
      Cobranza.Saldo AS SaldoInicial,
      IFNULL(DetalleCob.Abono,0) AS Abono,
      Cobranza.Saldo-IFNULL(DetalleCob.Abono,0) saldoActual,
      DATE_FORMAT(Cobranza.FechaReg, '%d-%m-%Y') as FechaReg ,
      DATE_FORMAT(Cobranza.FechaVence, '%d-%m-%Y') as FechaVence 
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
      WHERE th_pedido.TipoPedido='Credito' AND th_pedido.Cancelada = 0 #AND Cobranza.Status = 1 
      AND th_pedido.Pedido NOT IN (SELECT Documento FROM Venta WHERE IdEmpresa = c_almacenp.clave)
       {$sql_ruta} 
       {$sql_diao2} 
       {$sql_fecha2} 
       {$sql_operacion2} 
       {$sql_search2} 
      #GROUP BY ID
      ORDER BY FechaBusq DESC
      ) AS cob 
    GROUP BY Folio
    ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $FechaReg; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $FechaVence; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $CveCliente; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $NombreComercial; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $SaldoInicial; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $Abono; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $SaldoInicial-$Abono; ?></div>
        <?php 
        $i++;
    }
  ?>

    <?php if($criterio == 'SQL0000WMS') { ?><div cell="B<?php echo $i+2; ?>"><?php echo $sql; ?></div> <?php } ?>

</div>