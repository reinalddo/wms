<?php

use \koolreport\excel\Table;
use \koolreport\excel\PivotTable;
use \koolreport\excel\BarChart;
use \koolreport\excel\LineChart;

include '../../../../config.php';

$sheet1 = "Reporte de Ventas";
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
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Operación</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>DO</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cve Artículo</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad de Medida</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Precio</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Importe</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>IVA</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descuento</div>
    <div cell="Q1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total</div>
    <div cell="R1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cancelada</div>

    <?php
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn, $charset);

    $almacen = $_GET['almacen'];
    $ruta = $_GET['ruta'];
    $diao = $_GET['diao'];
    $operacion = $_GET['operacion'];
    $fecha_inicio = $_GET['fechaini'];
    $fecha_fin = $_GET['fechafin'];
    $cliente = $_GET['clientes'];
    $tipoV = $_GET['tipoV'];
    $articulos = $_GET['articulos'];
    $articulos_obsq = $_GET['articulos_obsq'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////

    $SQLRuta = "";
    $SQLRutaPr = "";

    if (!empty($ruta)) {
        $SQLRuta = " AND t_ruta.cve_ruta = '{$ruta}' ";
        $SQLRutaPr = " AND r.cve_ruta = '{$ruta}' ";
        if ($ruta == 'todas') {
            $SQLRuta = " AND t_ruta.cve_ruta != '' ";
            $SQLRutaPr = " AND r.cve_ruta != '' ";
        }

    }

    $SQLArticulo1 = "";
    $SQLArticulo2 = "";
    $SQLArticulo_Obseq = "";

    if (!empty($articulos)) {
        $SQLArticulo1 = " AND DetalleVet.Articulo = '$articulos' ";
        $SQLArticulo2 = " AND td.Articulo = '$articulos' ";
    }

    if (!empty($articulos_obsq)) {
        $SQLArticulo_Obseq = " AND pr.SKU = '$articulos_obsq' ";
    }

    $SQLFecha = "";

    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        $SQLFecha = " AND DATE(ventas.FechaBusq) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(ventas.FechaBusq) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if ($fecha_inicio == $fecha_fin)
            $SQLFecha = " AND DATE(ventas.FechaBusq) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_inicio)) {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $SQLFecha = " AND ventas.FechaBusq >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_fin)) {
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $SQLFecha = " AND ventas.FechaBusq <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
    }

    $SQLOperacion = ""; //$SQLOperacionVenta = ""; $SQLOperacionPreVenta = "";
    $InnerJoinOperacion = "";
    if ($operacion) {
        //$SQLOperacionVenta = " AND 0 "; 
        //$SQLOperacionPreVenta = " AND 1 ";
        $SQLOperacion = " AND ventas.Operacion = 'PreVenta' ";

        if ($operacion == 'F') {
            $InnerJoinOperacion = "INNER JOIN t_pedentregados tpe ON tpe.Fol_folio = th.Pedido";
            $SQLOperacion = " AND ventas.Operacion = 'Entrega' ";
        }

        if ($operacion == 'venta') {
            $SQLOperacion = " AND ventas.Operacion = 'Venta' AND IFNULL(ventas.Importe, 0) > 0 ";
            $InnerJoinOperacion = "";
            //$SQLOperacionVenta = " AND 1 "; 
            //$SQLOperacionPreVenta = " AND 0 ";
        }

        if ($operacion == 'Devoluciones')
            $SQLOperacion = " AND IFNULL(ventas.Importe, 0) < 0 ";

    }

    $SQLTipoV = "";
    if ($tipoV) {
        $SQLTipoV = " AND ventas.Tipo = '" . $tipoV . "' ";
    }

    $SQLCliente = "";
    if ($cliente) {
        $SQLCliente = " AND ventas.CodCliente = '" . $cliente . "' ";
    }

    $SQLDiaO1 = "";
    $SQLDiaO2 = "";
    $SQLDiaOPr = "";

    if ($diao) {
        $SQLDiaO1 = " AND Venta.DiaO = '{$diao}' ";
        $SQLDiaO2 = " AND RelOperaciones.DiaO = '{$diao}' ";
        $SQLDiaOPr = " AND pr.DiaO = '{$diao}' ";
    }

    $sql = "SELECT * FROM (
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
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento,
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
          GROUP BY Folio, cve_articulo

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
          td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, 
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
          GROUP BY Folio, cve_articulo
          ) as ventas WHERE 1 {$SQLOperacion} {$SQLFecha} {$SQLCliente} {$SQLTipoV} 
            ORDER BY Folio
            ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        if (($cajas_total - $PromoC) + ($piezas_total - $PromoP) == 0) continue;
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Fecha; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Responsable; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Operacion; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $rutaName; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $DiaO; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo utf8_decode($Articulo); ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $unidadMedida; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo($cajas_total);//-$PromoC ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo($piezas_total);//-$PromoP ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo $Precio; ?></div>
        <div cell="N<?php echo $i; ?>"><?php echo $Importe; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo $IVA ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $Descuento ?></div>
        <div cell="Q<?php echo $i; ?>"><?php echo $Importe + $IVA - $Descuento; ?></div>
        <div cell="R<?php echo $i; ?>"><?php echo $Cancelada; ?></div>
        <?php
        $i++;

    }
    ?>

    <?php /* ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>
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
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tipo Promocion</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Estatus</div>

    <?php
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen = $_GET['almacen'];
    $ruta = $_GET['ruta'];
    $diao = $_GET['diao'];

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

    $SQLFecha = "";

    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        $SQLFecha = " AND dia.Fecha BETWEEN STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";

    } else if (!empty($fecha_inicio)) {
        $SQLFecha = " AND dia.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_fin)) {
        $SQLFecha = " AND dia.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
    }

    $SQLOperacion = "";
    if ($operacion) {
        $SQLOperacion = " AND pr.Tipo = 'P' ";
        if ($operacion == 'venta') {
            $SQLOperacion = " AND pr.Tipo = 'V' ";
        }
    }

    $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo, r.cve_ruta AS Ruta, r.ID_Ruta, 
    d.Cve_Clte AS Cliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida, pr.Docto AS Folio, d.id_destinatario AS CodCliente,
                IF(v.TipoVta != 'Obsequio', 'Promocion', 'Obsequio') AS TipoPromocion,
                IF(v.Cancelada = 1, 'Cancelado', 'Activo') AS Estatus
FROM PRegalado pr 
LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
LEFT JOIN DiasO dia ON dia.DiaO = pr.DiaO AND dia.RutaId = pr.RutaId AND dia.IdEmpresa = pr.IdEmpresa
left join Venta v on v.Documento = pr.Docto
WHERE  al.id = '{$almacen}' {$SQLArticulo_Obseq} {$SQLRutaPr} {$SQLDiaOPr} {$SQLOperacion} {$SQLFecha} 
";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Ruta; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $diao; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo utf8_decode($des_articulo); ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $Cant; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $unidad_medida; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $TipoPromocion; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $Estatus; ?></div>
        <?php
        $i++;

    }
    ?>
    <?php /* ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>

</div>