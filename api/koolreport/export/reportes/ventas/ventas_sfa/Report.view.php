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

    $id_venta = $_GET['id_venta'];
    $cia = $_GET['cve_cia'];
    $folio = $_GET['folio'];
    $ruta = $_GET['ruta'];

?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Análisis de Ventas</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php
/*
    <div style="margin-bottom:50px;">
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT des_articulo  FROM c_articulo WHERE cve_articulo = '5000LC';";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $des_articulo = mysqli_fetch_array($res)['des_articulo'];
    echo $des_articulo;
*/
    /*
    ColumnChart::create(array(
        "title"=>"Sale Report",
        "dataSource"=>$category_amount,
        "columns"=>array(
            "category",
            "sale"=>array("label"=>"Sale","type"=>"number","prefix"=>"$"),
            "cost"=>array("label"=>"Cost","type"=>"number","prefix"=>"$"),
            "profit"=>array("label"=>"Profit","type"=>"number","prefix"=>"$"),
        )
    ));
    </div>
    */
    ?>
    <div style="margin:50px;">
    <?
    /*
    Table::create(array(
        "dataSource"=>$category_amount
    ));
    */
    ?>
    </div>

  <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $split = "";

    $inner_venta = " LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo ";
    $inner_preventa = " LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo ";

    $field_envase_venta_preventa = "";
    $left_envase_venta = "";
    $left_envase_preventa = "";
    $field_cantidad_venta_preventa = "";
    $field_cantdev_venta_preventa = "";
    $field_tipo_venta_preventa = "";

    if(isset($_GET['envase']))
    {
        $field_envase_venta_preventa = " GROUP_CONCAT(CONCAT('(', art_env.cve_articulo, ') ', art_env.des_articulo, '::::::::::',art_env.tipo) SEPARATOR '+++++') AS Envase, ";
        $field_cantidad_venta_preventa = " penv.Cantidad AS Cantidad_Envase, ";
        $field_cantdev_venta_preventa = " penv.Devuelto AS Cantidad_Devuelta, ";
        $field_tipo_venta_preventa = " IF(penv.Devuelto = 0, penv.Tipo, 'Devuelta') AS TipoStatus, ";
        #$left_envase_venta_preventa = " LEFT JOIN ProductoEnvase penv ON penv.Producto = c_articulo.cve_articulo  ";#AND c_almacenp.clave = penv.IdEmpresa
        $left_envase_venta = " LEFT JOIN DevEnvases penv ON penv.Docto = DetalleVet.Docto ";
        $left_envase_preventa = " LEFT JOIN DevEnvases penv ON penv.Docto = th.Pedido ";
        $left_envase_venta_preventa_nombre_env = " LEFT JOIN c_articulo art_env ON penv.Envase = art_env.cve_articulo ";
        $inner_venta = " INNER JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo AND c_articulo.Usa_Envase = 'S' ";
        $inner_preventa = " INNER JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo AND c_articulo.Usa_Envase = 'S' ";
    }

    $sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = '.$cia;
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }
    $row_logo = mysqli_fetch_array($res);
    $logo = $row_logo['logo'];

    //if($logo[0] == '/') $logo[0] = "";

    $sql = "
        SELECT DISTINCT
      DetalleVet.Comisiones AS Comisiones, c_almacenp.nombre AS sucursalNombre, Venta.Id AS idVenta, Venta.IdEmpresa AS Sucursal,
      DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, 
      #Venta.CodCliente AS Cliente, 
      c_cliente.Cve_Clte as Cliente,
      c_cliente.RazonSocial AS Responsable,
      c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
      DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento, Venta.TOTAL AS Total, DetalleVet.Comisiones AS Comisiones,
      '' AS TotalPedidas,
      DetalleVet.Utilidad AS Utilidad, c_articulo.num_multiplo AS Cajas, DetalleVet.Pza AS Piezas, Venta.Cancelada AS Cancelada, Venta.VendedorId AS vendedorID,
      c_articulo.control_peso,
      t_vendedores.Nombre AS Vendedor, Venta.ID_Ayudante1 AS Ayudante1, Venta.ID_Ayudante2 AS Ayudante2, 
      DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, c_articulo.cve_articulo as cve_articulo,
      DetalleVet.Descripcion AS Articulo, Cobranza.Documento AS Documento, Cobranza.Saldo AS saldoInicial, DetalleCob.Abono AS Abono, Venta.Saldo AS saldoActual,
      Cobranza.FechaReg AS fechaRegistro, Cobranza.FechaVence AS fechaVence, Venta.DocSalida AS tipoDoc, Noventas.MotivoId AS idMotivo, MotivosNoVenta.Motivo AS Motivo,
      {$field_envase_venta_preventa}
      {$field_cantidad_venta_preventa}
      {$field_cantdev_venta_preventa}
      {$field_tipo_venta_preventa}
      #IF(IFNULL(th_pedido.Fol_folio, '') = '', 'Venta', 'PreVenta') AS Operacion,
      'Venta' AS Operacion,
      IF(DetalleVet.Tipo = 0, DetalleVet.Pza, IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, DetalleVet.Pza),TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))) AS cajas_total,
      IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (DetalleVet.Pza - (c_articulo.num_multiplo*TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, DetalleVet.Pza, 0))) as piezas_total,
      IFNULL(c_cliente.limite_credito, 0) as limiteCredito,
      IF(pr.Tipmed = 'Caja', pr.Cant, 0) as PromoC,
      IF(pr.Tipmed != 'Caja', pr.Cant, 0) as PromoP
      FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId  
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      {$inner_venta}
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
      {$left_envase_venta}
      {$left_envase_venta_preventa_nombre_env}
      LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
      INNER JOIN DiasO ON DiasO.DiaO = Venta.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = Venta.Documento #AND pr.SKU = DetalleVet.Articulo
      WHERE Venta.Id = (SELECT Id FROM Venta WHERE Documento = '{$folio}' AND RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '$ruta'))
      ORDER BY Articulo
    ";
    //if(!$id_venta)
    if($id_venta == $folio)
    {
        $sql = "SELECT DISTINCT
      '' AS Comisiones, c_almacenp.nombre AS sucursalNombre, th.Pedido AS idVenta, th.IdEmpresa AS Sucursal,
      DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
      c_cliente.RazonSocial AS Responsable,
      c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
      td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, '' AS Comisiones,
      td.TotalPedidas AS TotalPedidas,
      '' AS Utilidad, c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, '' AS Cancelada, th.cve_Vendedor AS vendedorID,
      t_vendedores.Nombre AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
      'PreVenta' AS Operacion,
      '' AS Promociones, th.DiaO AS DiaOperativo, c_articulo.cve_articulo as cve_articulo,
      td.Descripcion AS Articulo, Cobranza.Documento AS Documento, Cobranza.Saldo AS saldoInicial, DetalleCob.Abono AS Abono, '' AS saldoActual,
      IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0)) AS cajas_total,
      IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0)) as piezas_total,
      IFNULL(c_cliente.limite_credito, 0) as limiteCredito,
      Cobranza.FechaReg AS fechaRegistro, Cobranza.FechaVence AS fechaVence, '' AS tipoDoc, Noventas.MotivoId AS idMotivo, MotivosNoVenta.Motivo AS Motivo,
      {$field_envase_venta_preventa}
      {$field_cantidad_venta_preventa}
      {$field_cantdev_venta_preventa}
      {$field_tipo_venta_preventa}
      IF(pr.Tipmed = 'Caja', pr.Cant, 0) as PromoC,
      IF(pr.Tipmed != 'Caja', pr.Cant, 0) as PromoP
      FROM V_Cabecera_Pedido th
      LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
      LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
      {$inner_preventa}
      INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
      {$left_envase_preventa}
      {$left_envase_venta_preventa_nombre_env}
      LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
      LEFT JOIN DiasO ON DiasO.DiaO = th.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = th.Ruta AND Noventas.Cliente = th.Cod_Cliente
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = th.Pedido AND pr.SKU = td.Articulo
      WHERE th.Pedido = '{$folio}'
      ORDER BY Articulo";
    }
    //echo $sql;
    if(isset($_GET['consolidado']))
    {
        $cliente = $_GET['cliente'];
        $almacen = $_GET['almacen'];
/*
        $sql = "
        SELECT ventas.Fecha, ventas.Folio, ventas.Total, SUM(ventas.Abono) as Abono, ventas.saldoFinal FROM (
    SELECT DISTINCT
      Cobranza.Status,
      Venta.CodCliente AS ClienteCod,
      c_cliente.Cve_Clte as Cliente,
      c_almacenp.nombre AS sucursalNombre,
      c_cliente.RazonSocial AS Responsable,
      DATE_FORMAT(Venta.Fecha, '%d-%m-%Y') AS Fecha,
      DATE_FORMAT(Venta.Fvence, '%d-%m-%Y') AS FechaCompromiso,
      Venta.Documento AS Folio,
      Venta.TOTAL AS Total,
      (IFNULL(DetalleCob.Abono, 0)) AS Abono,
      (IFNULL(Cobranza.Saldo, 0) - (IFNULL(DetalleCob.Abono, 0))) AS saldoFinal
      FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId  
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
      LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.SKU = DetalleVet.Articulo AND pr.RutaId = DetalleVet.RutaId AND pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
      WHERE 1 AND Venta.CodCliente IS NOT NULL
      #GROUP BY Folio

UNION

SELECT DISTINCT
      Cobranza.Status,
      th_pedido.Cod_Cliente AS ClienteCod,
      th_pedido.Cve_Clte AS Cliente,
      c_almacenp.nombre AS sucursalNombre,
      c_cliente.RazonSocial AS Responsable,
      DATE_FORMAT(th_pedido.Fec_Pedido, '%d-%m-%Y') AS Fecha,
      DATE_FORMAT(th_pedido.Fec_Entrega, '%d-%m-%Y') AS FechaCompromiso,
      th_pedido.Pedido AS Folio,
      th_pedido.TotPedidas AS Total,
      (IFNULL(DetalleCob.Abono, 0)) AS Abono,
      (IFNULL(Cobranza.Saldo, 0) - (IFNULL(DetalleCob.Abono, 0))) AS saldoFinal
      FROM V_Cabecera_Pedido th_pedido
      LEFT JOIN V_Detalle_Pedido td_pedido ON td_pedido.Pedido= th_pedido.Pedido
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th_pedido.Ruta
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th_pedido.cve_Vendedor
      LEFT JOIN FormasPag ON FormasPag.IdFpag = th_pedido.FormaPag 
      LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo
      INNER JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN Cobranza ON Cobranza.Documento = th_pedido.Pedido
      LEFT JOIN DiasO ON DiasO.DiaO = th_pedido.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th_pedido.Cod_Cliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = td_pedido.Pedido AND pr.SKU = td_pedido.Articulo AND pr.RutaId = th_pedido.Ruta AND pr.IdEmpresa = th_pedido.IdEmpresa AND pr.Cliente = c_destinatarios.Cve_Clte
      WHERE 1 AND th_pedido.Pedido NOT IN (SELECT Documento FROM Venta WHERE IdEmpresa = '{$almacen}') 
       AND th_pedido.Cod_Cliente IS NOT NULL
      GROUP BY Folio
      ) AS ventas WHERE 1 AND ventas.ClienteCod = '{$cliente}' AND ventas.Status = 1
        GROUP BY Folio
      ORDER BY STR_TO_DATE(ventas.Fecha, '%d-%m-%Y') 
      
      ";
*/
      /*
    $sql = "SELECT c_cliente.Cve_Clte AS Cliente, c_cliente.RazonComercial , Cobranza.Documento AS Folio, DATE_FORMAT(Cobranza.Fechareg, '%d-%m-%Y') as Fecha,
Sum(ifnull(Cobranza.Saldo,0)) Total, sum(ifnull(DetalleCob.Abono,0)) Abono,Sum(Cobranza.Saldo)-sum(ifnull(DetalleCob.Abono,0)) Saldo
from Vw_Cobranza Cobranza left join DetalleCob on Cobranza.Documento=DetalleCob.Documento and Cobranza.Ruta=DetalleCob.RutaId
LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Cobranza.Cliente
LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
where Cobranza.Status=1  and Cobranza.Cliente='{$cliente}'
group by c_cliente.Cve_Clte,Cobranza.Documento";
*/
    $sql = "SELECT c_cliente.Cve_Clte AS Cliente, c_cliente.RazonComercial , Cob.Documento AS Folio, DATE_FORMAT(Cob.Fechareg, '%d-%m-%Y') AS Fecha,
SUM(IFNULL(Cob.Saldo,0)) Total, SUM(IFNULL(Cob.Abono,0)) Abono,SUM(Cob.Saldo)-SUM(IFNULL(Cob.Abono,0)) Saldo
FROM Vw_Cobranza Cob #left join DetalleCob on Cob.Documento=DetalleCob.Documento and Cob.Ruta=DetalleCob.RutaId
LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Cob.Cliente
LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
WHERE Cob.Status=1  AND Cob.Cliente='{$cliente}'
GROUP BY c_cliente.Cve_Clte,Cob.Documento";
    }

    $sql2 = "";
    if(isset($_GET['cobranza']))
    {
        $sql_ruta_detalle = "";
        if(isset($_GET['ruta']))
            if($_GET['ruta'] != 'todas' && $_GET['ruta'] != '')
            {
                $ruta_detalle = $_GET['ruta'];
                $sql_ruta_detalle = " AND r.cve_ruta = '{$ruta_detalle}' ";
            }
        $sql2 = "SELECT dc.Id, DATE_FORMAT(dc.Fecha, '%d-%m-%Y') as Fecha, r.cve_ruta AS Ruta, dc.SaldoAnt, dc.Abono, dc.Saldo, IFNULL(c.limite_credito, 0) as limiteCredito
                FROM DetalleCob dc
                LEFT JOIN c_destinatarios d ON d.id_destinatario = dc.Cliente
                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                LEFT JOIN t_ruta r ON r.ID_Ruta = dc.RutaId
                WHERE dc.Documento = '{$folio}' {$sql_ruta_detalle} 

                UNION 

                SELECT cb.id AS Id, DATE_FORMAT(cb.FechaReg, '%d-%m-%Y') AS Fecha, r.cve_ruta AS Ruta, cb.Saldo AS SaldoAnt, 0 AS Abono, cb.Saldo, IFNULL(c.limite_credito, 0) AS limiteCredito
                                FROM Cobranza cb
                                LEFT JOIN c_destinatarios d ON d.id_destinatario = cb.Cliente
                                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                                LEFT JOIN t_ruta r ON r.ID_Ruta = cb.RutaId
                                WHERE cb.Documento = '{$folio}' AND cb.Documento NOT IN (SELECT Documento FROM DetalleCob WHERE Documento = '{$folio}') 
                                {$sql_ruta_detalle} 
                ORDER BY Id";
    }

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ". $sql;
    }

    $row = "";
    if(mysqli_num_rows($res))
    {
        $row = mysqli_fetch_array($res);
        extract($row);
    }

?>

    <table border="0">
      <tr>
        <td style="width: 200px;"></td>
        <td style="width: 200px;"><img src="<?php echo ''.$logo; ?>" alt="" height="200"></td>
        <td align="center" style="font-size: 14px;width: 950px; text-align: center; vertical-align: middle;">
        <?php 
        if(isset($_GET['envase']))
        {
        ?>
        <h1><span lang="th">Control de Envases</span></h1>
        <?php 
        }
        else if(isset($_GET['consolidado']))
        {
        ?>
        <h1><span lang="th">Reporte de Cuentas por Cobrar  | Cobranza | Consolidado</span></h1>
        <?php 
        }
        else if(isset($_GET['cobranza']))
        {
        ?>
        <h1><span lang="th">Reporte de Cuentas por Cobrar  | Cobranza</span></h1>
        <?php 
        }
        else
        {
        ?>
        <h1><span lang="th">Reporte de Ventas <?php if($id_venta) echo "#".$id_venta; ?></span></h1>
        <?php 
        }
        if(!isset($_GET['consolidado']))
        {
        ?>
        <h1>Folio: <?php echo $Folio; ?><br>
        <?php 
        }
        ?>
        <h1>Cliente: <?php echo "(".$Cliente.") - ".$Responsable; ?></h1>
        </h1>
        <br><br>
        <table border="0">
            <?php 
            if(!isset($_GET['consolidado']))
            {
             ?>
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Operación:</b></span></td><td><?php echo $Operacion; ?></td><td></td><td></td></tr>
            <?php 
            }
            ?>
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Sucursal:</b></span></td><td><?php echo $sucursalNombre; ?></td><td></td><td></td></tr>
            <?php 
            if(!isset($_GET['consolidado']))
            {
            ?>
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Día Operativo:</b></span></td><td><?php echo $DiaOperativo; ?></td><td></td><td></td></tr>
            <tr>
                <td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Ruta:</b></span></td><td><?php echo $rutaName; ?></td>
                <td style="font-size: 18px;"><span lang="th"><b>Vendedor:</b></span></td><td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($Vendedor); ?></td>
            </tr>
            <?php 
            if($Ayudante1)
            {
            ?>
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Ayudante 1:</b></span></td><td><?php echo utf8_decode($Ayudante1); ?></td><td></td><td></td></tr>
            <?php 
            }
            if($Ayudante2)
            {
            ?>
            <tr><td style="width:160px;"></td><td style="font-size: 18px;"><span lang="th"><b>Ayudante 2:</b></span></td><td><?php echo utf8_decode($Ayudante2); ?></td><td></td><td></td></tr>
            <?php 
            }
            }
            ?>
    
        </table>
        
            <?php 
            if(isset($_GET['cobranza']))
            {
            ?>
            <br><br>
                <table border="0">
                    <tr>
                        <td style="width:160px;"></td>
                        <td style="font-size: 18px;"><span lang="th"><b>Límite Crédito: </b></span></td>
                        <td><?php echo number_format($limiteCredito, 2); ?></td>
                    </tr>
                </table>
        <?php 
            }
            ?>

        <br><br>
        
    </td>
    <?php 
    if(!isset($_GET['consolidado']))
    {
    ?>
    <td style="font-size: 18px;top: 50px;right: 250px;position: absolute;"><span lang="th"><b>Fecha:</b></span></td>
    <td style="font-size: 18px;top: 50px;right: 130px;position: absolute;"><?php echo $Fecha; ?></td>
    <?php 
    }
    else
    {
?>
    <td></td>
    <td></td>
<?php 
    }
    ?>
      </tr>
    </table>

<div style="padding: 10px 100px;">
<table class="table table-striped">
  <thead>
    <tr>
    <?php 
    if(isset($_GET['cobranza']))
    {
    ?>
      <th scope="col">Fecha</th>
      <th scope="col">Ruta</th>
      <th scope="col">Saldo Anterior</th>
      <th scope="col">Abono</th>
      <th scope="col">Saldo Restante</th>
      <!--<th scope="col">Saldo Disponible</th>-->
    <?php 
    }
    else
    {
        if(isset($_GET['consolidado']))
        {
        ?>
          <th scope="col">Fecha</th>
          <th scope="col">Folio</th>
          <th scope="col">Total</th>
          <th scope="col">Abono</th>
          <th scope="col">Saldo</th>
        <?php 
        }
        else
        {
            if(isset($_GET['envase']))
            {
    ?>
                <th scope="col">Producto</th>
                <th scope="col">Descripción</th>
                <th scope="col">Venta</th>
                <th scope="col">Cant</th>
                <th scope="col">Cant Dev</th>
                <th scope="col">Status</th>
                <th scope="col">Envases Cristal</th>
                <th scope="col">Cajas Plásticas</th>
                <th scope="col">Garrafón</th>
                <th scope="col">Promoción</th>
    <?php 
            }
            else
            {
    ?>
      <th scope="col">Clave</th>
      <th scope="col">Articulo</th>
    <?php 
            }
        }
    if(!isset($_GET['consolidado']) && !isset($_GET['envase']))
    {
     ?>
      <th scope="col">Cajas</th>
      <th scope="col">Piezas</th>
      <th scope="col">Importe</th>
      <th scope="col">IVA</th>
      <th scope="col">Descuento</th>
      <th scope="col">Total</th>
      <th scope="col">PromC</th>
      <th scope="col">PromP</th>
      <th scope="col">ObseqC</th>
      <th scope="col">ObseqP</th>
    <?php 
    }
    ?>
    <?php
    }
    ?>
    </tr>
  </thead>
  <tbody>
<?php 

if(isset($_GET['cobranza']))
    $sql = $sql2;

if (!($res = mysqli_query($conn, $sql))){
    echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ".$sql;
}
$tot_Cajas = 0; $tot_Piezas = 0; $tot_Importe = 0; $tot_IVA = 0; $tot_Descuento = 0; $tot_Total = 0; $tot_promc = 0; $tot_promp = 0;
$tot_obseqc = 0; $tot_obseqp = 0; $tot_Restante = 0; $tot_saldofinal = 0;
while($row = mysqli_fetch_array($res))
{
    extract($row);

    $ObsequioC = 0;
    $ObsequioP = 0;
    if($Tipo == 'Obsequio' && !isset($_GET['cobranza']))
    {
        $ObsequioC = $cajas_total;
        $ObsequioP = $piezas_total;
    }

    if(isset($_GET['cobranza']))
    {
        $tot_Restante += $Saldo;
?>
        <tr>
            <td align="center"><?php echo $Fecha; ?></td>
            <td align="left"><?php echo $Ruta; ?></td>
            <td align="center"><?php echo number_format($SaldoAnt, 2); ?></td>
            <td align="center"><?php echo number_format($Abono, 2); ?></td>
            <td align="center"><?php echo number_format($Saldo, 2); ?></td>
            <!--<td align="center">-->
                <?php //echo number_format($limiteCredito - $Saldo, 2); ?>
            <!--</td>-->
        </tr>
<?php 
    }
    else
    {
?>
    <tr>
    <?php 
    if(isset($_GET['consolidado']))
    {
    ?>
      <td align="center"><?php echo $Fecha; ?></td>
      <td align="left"><?php echo $Folio; ?></td>
      <td align="right"><?php echo number_format($Total, 2); ?></td>
      <td align="right"><?php echo number_format($Abono, 2); ?></td>
      <td align="right"><?php echo number_format($Total-$Abono, 2); ?></td>
    <?php 
    $tot_saldofinal += ($Total-$Abono);
    }
    if(!isset($_GET['consolidado']))
    {
        if(isset($_GET['envase']))
        {
            $env = explode("+++++", $Envase);
            
            $cristal = ""; $plastico = ""; $garrafon = "";

            if($env[0] != '')
            {
                $tipo_env = explode("::::::::::", $env[0]);
                if($tipo_env[1] == 8) $plastico = $tipo_env[0];
                else if($tipo_env[1] == 9) $garrafon = $tipo_env[0];
                else if($tipo_env[1] == 10) $cristal = $tipo_env[0];
            }

            if($env[1] != '')
            {
                $tipo_env = explode("::::::::::", $env[1]);
                if($tipo_env[1] == 8) $plastico = $tipo_env[0];
                else if($tipo_env[1] == 9) $garrafon = $tipo_env[0];
                else if($tipo_env[1] == 10) $cristal = $tipo_env[0];
            }
        ?>
            <td align="left"><?php echo $cve_articulo; ?></td>
            <td align="left"><?php echo $Articulo; ?></td>
            <td align="right"><?php echo $Piezas; ?></td>
            <td align="right"><?php echo $Cantidad_Envase; ?></td>
            <td align="right"><?php echo $Cantidad_Devuelta; ?></td>
            <td align="left"><?php echo $TipoStatus; ?></td>
            <td align="left"><?php echo $cristal; ?></td>
            <td align="left"><?php echo $plastico; ?></td>
            <td align="left"><?php echo $garrafon; ?></td>
            <td align="left"><?php if($PromoC > 0) echo $PromoC. " Caja(s)"; if($PromoP > 0) echo "".$PromoP." Pieza(s)"; ?></td>
    <?php
        }
        else
        {
     ?>
      <td align="left"><?php echo $cve_articulo; ?></td>
      <td align="left"><?php echo $Articulo; ?></td>
      <td align="right"><?php echo $cajas_total-$ObsequioC; ?></td>
      <td align="right"><?php $decimales = 0; if($control_peso == 'S') $decimales = 2; echo (number_format($piezas_total,$decimales)-number_format($ObsequioP,$decimales)); ?></td>
      <td align="right"><?php echo number_format($Importe, 2); ?></td>
      <td align="right"><?php echo number_format($IVA, 2); ?></td>
      <td align="right"><?php echo number_format($Descuento, 2); ?></td>
      <td align="right"><?php /*if($TotalPedidas){ echo number_format($TotalPedidas, 2); $Total = number_format($TotalPedidas, 2);}else*/ {$Total = ($Importe + $IVA - $Descuento); echo number_format($Total, 2);} ?></td>
      <td align="right"><?php echo $PromoC; ?></td>
      <td align="right"><?php echo $PromoP; ?></td>
        <td align="right"><?php echo $ObsequioC; ?></td>
        <td align="right"><?php $decimales = 0; if($control_peso == 'S') $decimales = 2; echo number_format($ObsequioP,$decimales); ?></td>
    <?php 
        }
    }
    ?>
    </tr>
<?php 
$tot_Cajas += $cajas_total; $tot_Piezas += $piezas_total; $tot_Importe += $Importe; $tot_IVA += $IVA; 
$tot_Descuento += $Descuento; $tot_Total += $Total; $tot_promc += $PromoC; $tot_promp += $PromoP;
$tot_obseqc += $ObsequioC;
$tot_obseqp += $ObsequioP;
    }
}
    if(!isset($_GET['cobranza']) && !isset($_GET['envase']))
    {
?>
    <tr>
        <td></td>
        <td align="right"><b>Total:</b></td>
    <?php 
    if(!isset($_GET['consolidado']))
    {
     ?>
        <td align="right"><?php echo $tot_Cajas-$tot_obseqc; ?></td>
        <td align="right"><?php echo $tot_Piezas-$tot_obseqp; ?></td>
        <td align="right"><?php echo number_format($tot_Importe, 2); ?></td>
        <td align="right"><?php echo number_format($tot_IVA, 2); ?></td>
        <td align="right"><?php echo number_format($tot_Descuento, 2); ?></td>
        <td align="right"><?php echo number_format($tot_Total, 2); ?></td>
        <td align="right"><?php echo $tot_promc; ?></td>
        <td align="right"><?php echo $tot_promp; ?></td>
        <td align="right"><?php echo $tot_obseqc; ?></td>
        <td align="right"><?php echo $tot_obseqp; ?></td>
    <?php 
    }
    if(isset($_GET['consolidado']))
    {
     ?>
        <td align="right"></td>
        <td align="right"></td>
        <td align="right"><?php echo number_format($tot_saldofinal, 2); ?></td>
    <?php 
    }
    ?>
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

