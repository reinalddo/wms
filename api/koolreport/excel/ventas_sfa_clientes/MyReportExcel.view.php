<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Consolidado de Clientes";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Comercial</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>

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

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////

    $SQLRuta = ""; $SQLRutaPr = ""; 

    if(!empty($ruta))
    {
        $SQLRuta = " AND t_ruta.cve_ruta = '{$ruta}' ";
        $SQLRutaPr = " AND r.cve_ruta = '{$ruta}' ";
        if($ruta == 'todas')
        {
            $SQLRuta = " AND t_ruta.cve_ruta != '' ";
            $SQLRutaPr = " AND r.cve_ruta != '' ";
        }

    }

    $SQLArticulo1 = ""; $SQLArticulo2 = ""; $SQLArticulo_Obseq = "";

    if (!empty($articulos))
    {
        $SQLArticulo1 = " AND DetalleVet.Articulo = '$articulos' "; 
        $SQLArticulo2 = " AND td.Articulo = '$articulos' "; 
    }

    if (!empty($articulos_obsq))
    {
        $SQLArticulo_Obseq = " AND pr.SKU = '$articulos_obsq' "; 
    }

    $SQLFecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
      $SQLFecha = " AND DATE(ventas.FechaBusq) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(ventas.FechaBusq) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      if($fecha_inicio == $fecha_fin)
      $SQLFecha = " AND DATE(ventas.FechaBusq) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_inicio)) 
      {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $SQLFecha = " AND ventas.FechaBusq >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_fin)) 
      {
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $SQLFecha = " AND ventas.FechaBusq <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      }

    $SQLOperacion = ""; //$SQLOperacionVenta = ""; $SQLOperacionPreVenta = "";
    $InnerJoinOperacion = "";
    if($operacion)
    {
        //$SQLOperacionVenta = " AND 0 "; 
        //$SQLOperacionPreVenta = " AND 1 ";
        $SQLOperacion = " AND ventas.Operacion = 'PreVenta' ";

        if($operacion == 'F')
        {
            $InnerJoinOperacion = "INNER JOIN t_pedentregados tpe ON tpe.Fol_folio = th.Pedido";
            $SQLOperacion = " AND ventas.Operacion = 'Entrega' ";
        }

        if($operacion == 'venta')
        {
            $SQLOperacion = " AND ventas.Operacion = 'Venta' AND IFNULL(ventas.Importe, 0) > 0 ";
            $InnerJoinOperacion = "";
            //$SQLOperacionVenta = " AND 1 "; 
            //$SQLOperacionPreVenta = " AND 0 ";
        }

        if($operacion == 'Devoluciones')
            $SQLOperacion = " AND IFNULL(ventas.Importe, 0) < 0 ";

    }

    $SQLTipoV = "";
    if($tipoV)
    {
        $SQLTipoV = " AND ventas.Tipo = '".$tipoV."' ";
    }

    $SQLCliente = "";
    if($cliente)
    {
        $SQLCliente = " AND ventas.CodCliente = '".$cliente."' ";
    }

    $SQLDiaO1 = ""; $SQLDiaO2 = ""; $SQLDiaOPr = "";

    if($diao)
    {
        $SQLDiaO1 = " AND Venta.DiaO = '{$diao}' "; 
        $SQLDiaO2 = " AND RelOperaciones.DiaO = '{$diao}' ";
        $SQLDiaOPr = " AND pr.DiaO = '{$diao}' ";
    }

    $sql = "SELECT ventas.Cliente, ventas.Responsable, SUM(ventas.Importe) AS Importe, SUM(ventas.IVA) AS IVA, SUM(ventas.Descuento) AS Descuento FROM (
            SELECT DISTINCT
          Venta.Fecha as FechaBusq,
          DATE_FORMAT(Venta.Fecha, '%d-%m-%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, 
          c_cliente.Cve_Clte AS Cliente, 
          Venta.CodCliente AS CodCliente,
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, 
          #FormasPag.Forma AS metodoPago,
          Venta.DiaO as DiaO,
          um.des_umed as unidadMedida,
          DetalleVet.Precio AS Precio,
          SUM(DetalleVet.Importe) AS Importe, SUM(DetalleVet.IVA) AS IVA, SUM(DetalleVet.DescMon) AS Descuento,
          #t_vendedores.Nombre AS Vendedor, 
          'Venta' AS Operacion,
          IF(Venta.Cancelada = 0, 'No', 'Si') as Cancelada,
          c_articulo.cve_articulo AS cve_articulo,
          DetalleVet.Descripcion AS Articulo, 
          IF(DetalleVet.Tipo = 0, DetalleVet.Pza, IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, DetalleVet.Pza),TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))) AS cajas_total,
          IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (DetalleVet.Pza - (c_articulo.num_multiplo*TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, DetalleVet.Pza, 0))) AS piezas_total,
          IF(pr.Tipmed = 'Caja', (pr.Cant), 0) AS PromoC,
          IF(pr.Tipmed != 'Caja', (pr.Cant), 0) AS PromoP 
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId  
          #LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          #LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          #LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
          #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          #LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
          #LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
          WHERE 1 {$SQLRuta} {$SQLDiaO1} 
          #AND Venta.Cancelada = 0 
          {$SQLArticulo1} {$SQLArticulo_Obseq}
          GROUP BY CodCliente, Folio

UNION

SELECT DISTINCT
          RelOperaciones.Fecha as FechaBusq, 
          DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y') AS Fecha, RelOperaciones.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, th.Cod_Cliente AS CodCliente,
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, 
          #IFNULL(th.FormaPag, '') AS metodoPago,
          RelOperaciones.DiaO as DiaO,
          um.des_umed as unidadMedida,
          td.Precio AS Precio,
          SUM(td.SubTotalPedidas) AS Importe, SUM(td.IVAPedidas) AS IVA, SUM(td.DescuentoPedidas) AS Descuento, 
          #t_vendedores.Nombre AS Vendedor, 
          IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta') AS Operacion,
          IF(th.Cancelada = 0, 'No', 'Si') as Cancelada,
           c_articulo.cve_articulo AS cve_articulo,
          td.Descripcion AS Articulo, 
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0)) AS cajas_total,
          IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0)) AS piezas_total,
          IF(pr.Tipmed = 'Caja', (pr.Cant), 0) AS PromoC,
          IF(pr.Tipmed != 'Caja', (pr.Cant), 0) AS PromoP 
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido OR RelOperaciones.Folio = th.Pedido 
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta) 
          #LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          #LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          #LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          {$InnerJoinOperacion}
          #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          #LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
          #LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN PRegalado pr ON td.Pedido = pr.Docto AND td.Articulo = pr.SKU AND th.Ruta = pr.RutaId AND td.IdEmpresa = pr.IdEmpresa AND th.Cod_Cliente = pr.Cliente
          #td.Pedido LIKE CONCAT('%',pr.Docto)
          WHERE 1 {$SQLRuta} {$SQLDiaO2} 
          #AND th.Cancelada = 0 
          {$SQLArticulo2} {$SQLArticulo_Obseq}
          GROUP BY CodCliente, Folio
          ) as ventas WHERE 1 {$SQLOperacion} {$SQLFecha} {$SQLCliente} {$SQLTipoV} 
            GROUP BY Cliente
            ORDER BY Cliente";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        //if(($cajas_total-$PromoC) + ($piezas_total-$PromoP) == 0) continue;
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Responsable; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo number_format($Importe+$IVA-$Descuento, 2); ?></div>
        <?php 
        $i++;

    }
  ?>

    <?php /* ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>
</div>

