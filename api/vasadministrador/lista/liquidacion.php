<?php
include '../../../config.php';

error_reporting(0);

$accion = $_POST['accion'];

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($accion == 'obtener_reporte_liquidacion') {
    $ruta = $_POST['ruta'];
    $diao = $_POST['diao'];
    $almacen = $_POST['almacen'];
    $cia = $_POST['cve_cia'];

    if (!($res_charset = mysqli_query($conn, "SET NAMES 'utf8mb4';")))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";

    if ($ruta == '' || $diao == '' || $almacen == '' || $cia == '') {
        echo json_encode(array('status' => 'error', 'data' => 'Faltan datos'));
        exit;
    }
    $sql = "SELECT v.Cve_Vendedor, v.Nombre 
            FROM t_vendedores v
            LEFT JOIN Rel_Ruta_Agentes ra ON v.Id_Vendedor = ra.cve_vendedor
            LEFT JOIN t_ruta r ON r.ID_Ruta = ra.cve_ruta
            WHERE r.cve_ruta = '$ruta'";
    if (!$result = mysqli_query($conn, $sql)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los vendedores'));
        exit;
    }
    $vendedores = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $vendedores[] = $row;
    }
    if (count($vendedores) == 0) {
        echo json_encode(array('status' => 'error', 'data' => 'No se encontraron vendedores'));
        exit;
    }
    $sqlAlamcen = 'SELECT clave, nombre FROM c_almacenp WHERE id = ' . $almacen;
    if (!$result = mysqli_query($conn, $sqlAlamcen)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener el almacen'));
        exit;
    }
    $almacen = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $almacen[] = $row;
    }
    if (count($almacen) == 0) {
        echo json_encode(array('status' => 'error', 'data' => 'No se encontraron almacenes'));
        exit;
    }
    $sqlDiaOperativo = "SELECT DATE_FORMAT(Fecha, '%d/%m/%Y') AS fecha FROM DiasO WHERE Diao = $diao AND RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '$ruta')";
    if (!$result = mysqli_query($conn, $sqlDiaOperativo)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener el dia operativo'));
        exit;
    }
    $diaOperativo = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $diaOperativo[] = $row;
    }
    if (count($diaOperativo) == 0) {
        echo json_encode(array('status' => 'error', 'data' => 'No se encontraron dias operativos'));
        exit;
    }
    $sqlCia = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = ' . $cia;
    if (!$result = mysqli_query($conn, $sqlCia)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener la compañia'));
        exit;
    }
    $cia = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cia[] = mb_check_encoding($row, 'UTF-8') ? $row : array_map('utf8_encode', $row);
    }
    if (count($cia) == 0) {
        echo json_encode(array('status' => 'error', 'data' => 'No se encontraron compañias'));
        exit;
    }
    $sqlAnalisisVentas = "SELECT 
row_liq.cve_articulo, row_liq.Articulo, row_liq.InvInicial, row_liq.control_peso,
row_liq.inv_inicial_cajas,
row_liq.inv_inicial_piezas,
GROUP_CONCAT(CONCAT(row_liq.TipoOperacion, ';;;;;', row_liq.Importe, ';;;;;', row_liq.IVA, ';;;;;', row_liq.Descuento, ';;;;;', row_liq.Cajas, ';;;;;', IF(row_liq.control_peso = 'S', TRUNCATE(row_liq.Piezas, 2), TRUNCATE(row_liq.Piezas, 0)), ';;;;;', row_liq.Total, ';;;;;', row_liq.PrCajas, ';;;;;', row_liq.PrPiezas) SEPARATOR ':::::') AS fila
FROM ( 
SELECT liq.TipoOperacion, liq.cve_articulo, liq.Articulo, SUM(liq.Importe) AS Importe, SUM(liq.IVA) AS IVA, 
       sum(liq.Descuento) AS Descuento, SUM(liq.Cajas) AS Cajas, SUM(liq.Piezas) AS Piezas, liq.control_peso, 
       IFNULL(sh.Stock, '0') AS InvInicial,
       liq.DiaOperativo, liq.Ruta,
     sum(liq.inv_inicial_cajas) AS inv_inicial_cajas,
             sum(liq.inv_inicial_piezas)     AS inv_inicial_piezas,   liq.PrCajas, liq.PrPiezas,
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
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
         #0 AS inv_inicial_cajas,
          #IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          0 AS inv_inicial_piezas,
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
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          #0 AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #0 AS inv_inicial_piezas,
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
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          Inner JOIN RelOperaciones
                    ON #CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th.Pedido OR
                       RelOperaciones.Folio = th.Pedido

         LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta)
         LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido AND Cobranza.RutaId = t_ruta.ID_Ruta AND
                               Cobranza.DiaO = $diao
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = t_ruta.ID_Ruta
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND pr.Docto = th.Pedido AND pr.DiaO = RelOperaciones.DiaO                     
                     left join DiasO do on do.DiaO = $diao and do.RutaId = th.RutaEnt
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
) AS liq 
LEFT JOIN StockHistorico sh ON sh.Articulo = liq.cve_articulo AND sh.DiaO = liq.DiaOperativo AND sh.RutaID = liq.Ruta
WHERE liq.Importe >= 0 AND liq.Cajas >= 0 AND liq.Piezas >= 0 AND liq.TipoOperacion != 'Devoluciones' #Mientras se activan las tablas de devoluciones
GROUP BY liq.TipoOperacion, liq.cve_articulo, liq.Folio
) AS row_liq
GROUP BY row_liq.cve_articulo";
    if (!$result = mysqli_query($conn, $sqlAnalisisVentas)) {
        echo json_encode(array('status' => 'error', 'data' => mysqli_error($conn)));
        exit;
    }
    $analisisVentas = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $analisisVentas[] = mb_check_encoding($row, 'UTF-8') ? $row : array_map('utf8_encode', $row);
    }
    if (count($analisisVentas) > 0) {
        foreach ($analisisVentas as &$venta) {
            $detalleVentasTabla = explode(":::::", $venta['fila']);
            $totalDescuento = 0;
            $totalVenta = 0;
            $totalVentaCaja = 0;
            $totalVentaPieza = 0;
            $totalPreventaCaja = 0;
            $totalPreventaPieza = 0;
            $totalEntregaCaja = 0;
            $totalEntregaPieza = 0;
            $totalRecargaCaja = 0;
            $totalRecargaPieza = 0;
            $totalDevolucion = 0;
            $totalDevolucionCaja = 0;
            $totalDevolucionPieza = 0;
            $totalPromocionesCaja = 0;
            $totalPromocionesPieza = 0;
            $totalPedidoCajaPreventa = 0;
            $totalPedidoPiezaPreventa = 0;

            $stockInicialCaja = $venta['inv_inicial_cajas'];
            $stockInicialPieza = $venta['inv_inicial_piezas'];
            $stockFinalCaja = $venta['inv_inicial_cajas'];
            $stockFinalPieza = $venta['inv_inicial_piezas'];
            foreach ($detalleVentasTabla as $detalleVenta) {
                $tipoVentaFila = explode(";;;;;", $detalleVenta);
                $tipoVenta = $tipoVentaFila[0];
                if ($tipoVenta == "Venta") {
                    $totalVentaCaja += $tipoVentaFila[4];
                    $totalDescuento += $tipoVentaFila[3];
                    $totalVentaPieza += $tipoVentaFila[5];
                    $totalVenta += $tipoVentaFila[6];
                    $stockFinalCaja -= $tipoVentaFila[4];
                    $stockFinalPieza -= $tipoVentaFila[5];
                    $totalPromocionesCaja += $tipoVentaFila[7];
                    $totalPromocionesPieza += $tipoVentaFila[8];
                } else if ($tipoVenta == "PreVenta") {
                    $totalPedidoCajaPreventa += $tipoVentaFila[4];
                    $totalPedidoPiezaPreventa += $tipoVentaFila[5];
                    $totalDescuento += $tipoVentaFila[3];
                    $totalPreventaCaja += $tipoVentaFila[4];
                    $totalPreventaPieza += $tipoVentaFila[5];
                    $totalPromocionesCaja += $tipoVentaFila[7];
                    $totalPromocionesPieza += $tipoVentaFila[8];
                } else if ($tipoVenta == "Entrega") {
                    $totalVenta += $tipoVentaFila[6];
                    $totalEntregaCaja += $tipoVentaFila[4];
                    $totalEntregaPieza += $tipoVentaFila[5];
                    $totalDescuento += $tipoVentaFila[3];
                    $stockFinalCaja -= $tipoVentaFila[4];
                    $stockFinalPieza -= $tipoVentaFila[5];
                    $totalPromocionesCaja += $tipoVentaFila[7];
                    $totalPromocionesPieza += $tipoVentaFila[8];

                } else if ($tipoVenta == "Recarga") {
                    $totalRecargaCaja += $tipoVentaFila[4];
                    $totalDescuento += $tipoVentaFila[3];
                    $totalRecargaPieza += $tipoVentaFila[5];
                    $stockFinalCaja += $tipoVentaFila[4];
                    $stockFinalPieza += $tipoVentaFila[5];
                    $totalPromocionesCaja += $tipoVentaFila[7];
                    $totalPromocionesPieza += $tipoVentaFila[8];
                } else if ($tipoVenta == "Devolucion") {
                    $totalVenta += $tipoVentaFila[6];
                    $totalDescuento += $tipoVentaFila[3];
                    $totalDevolucionCaja += $tipoVentaFila[4];
                    $totalDevolucionPieza += $tipoVentaFila[5];
                    $stockFinalCaja -= $tipoVentaFila[4];
                    $stockFinalPieza -= $tipoVentaFila[5];
                    $totalPromocionesCaja += $tipoVentaFila[7];
                    $totalPromocionesPieza += $tipoVentaFila[8];
                }
            }
            //agrega los totales como una fila mas en cada articulo
            $venta['total_articulo'] = $totalVenta;
            $venta['total_venta_caja'] = $totalVentaCaja;
            $venta['total_venta_pieza'] = $totalVentaPieza;
            $venta['total_preventa_caja'] = $totalPreventaCaja;
            $venta['total_preventa_pieza'] = $totalPreventaPieza;
            $venta['total_entrega_caja'] = $totalEntregaCaja;
            $venta['total_entrega_pieza'] = $totalEntregaPieza;
            $venta['total_recarga_caja'] = $totalRecargaCaja;
            $venta['total_recarga_pieza'] = $totalRecargaPieza;
            $venta['total_devolucion_caja'] = $totalDevolucionCaja;
            $venta['total_devolucion_pieza'] = $totalDevolucionPieza;
            $venta['total_promociones_caja'] = $totalPromocionesCaja;
            $venta['total_promociones_pieza'] = $totalPromocionesPieza;
            $venta['total_pedido_caja_preventa'] = $totalPedidoCajaPreventa;
            $venta['total_pedido_pieza_preventa'] = $totalPedidoPiezaPreventa;
            $venta['stock_inicial_caja'] = $stockInicialCaja;
            $venta['stock_inicial_pieza'] = $stockInicialPieza;
            $venta['stock_final_caja'] = $stockFinalCaja;
            $venta['stock_final_pieza'] = $stockFinalPieza;
            $venta['total_descuento'] = $totalDescuento;
            //$venta['sqlAnalisisVentas'] = $sqlAnalisisVentas;
        }
    }
/*
    $sqlDevoluciones = "SELECT liq.TipoOperacion, liq.cve_articulo, liq.Articulo, SUM(liq.Importe) AS Importe, SUM(liq.IVA) AS IVA, 
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
        
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
           Inner JOIN RelOperaciones
                    ON CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th.Pedido OR
                       RelOperaciones.Folio = th.Pedido

         LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta)
         LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido AND Cobranza.RutaId = t_ruta.ID_Ruta AND
                               Cobranza.DiaO = $diao
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND pr.Docto = th.Pedido AND pr.DiaO = RelOperaciones.DiaO
           left join t_ruta tr2 on tr2.ID_Ruta = th.RutaEnt
      left join DiasO do on do.DiaO = $diao and do.RutaId = th.RutaEnt
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
) AS liq WHERE liq.TipoOperacion = 'Devoluciones' #Mientras se activan las tablas de devoluciones
GROUP BY liq.TipoOperacion, liq.cve_articulo";
*/

 $sqlDevoluciones = "SELECT liq.TipoOperacion, liq.cve_articulo, liq.Articulo, SUM(liq.Importe) AS Importe, SUM(liq.IVA) AS IVA, 
       SUM(liq.Descuento) AS Descuento, SUM(liq.Cajas) AS Cajas, SUM(liq.Piezas) AS Piezas, liq.control_peso, 
       IFNULL(sh.Stock, '0') AS InvInicial,
       liq.DiaOperativo, liq.Ruta,
     SUM(liq.inv_inicial_cajas) AS inv_inicial_cajas,
             SUM(liq.inv_inicial_piezas)     AS inv_inicial_piezas,   liq.PrCajas, liq.PrPiezas,
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
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          #0 AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #0 AS inv_inicial_piezas,
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
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          #0 AS inv_inicial_cajas,
          IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          #0 AS inv_inicial_piezas,

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
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          INNER JOIN RelOperaciones
                    ON RelOperaciones.Folio = th.Pedido #CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th.Pedido OR

         LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta)
         LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido AND Cobranza.RutaId = t_ruta.ID_Ruta AND
                               Cobranza.DiaO = $diao
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = t_ruta.ID_Ruta
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND pr.Docto = th.Pedido AND pr.DiaO = RelOperaciones.DiaO                     
                     LEFT JOIN DiasO do ON do.DiaO = $diao AND do.RutaId = th.RutaEnt
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
) AS liq 
LEFT JOIN StockHistorico sh ON sh.Articulo = liq.cve_articulo AND sh.DiaO = liq.DiaOperativo AND sh.RutaID = liq.Ruta
WHERE liq.Importe < 0 AND liq.TipoOperacion = 'Devoluciones' #Mientras se activan las tablas de devoluciones
GROUP BY liq.TipoOperacion, liq.cve_articulo, liq.Folio";

    if (!$result = mysqli_query($conn, $sqlDevoluciones)) {
        echo json_encode(array('status' => 'error', 'data' => mysqli_error($conn)));
        exit;
    }
    $devoluciones = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $devoluciones[] = $row;
    }
    $sqlCredito = "SELECT 
             cob.Documento, cte.Cve_Clte, cte.RazonSocial AS Cliente, (IFNULL(cob.Saldo, 0)) AS limite_credito, 
            SUM(IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = cob.Documento AND RutaId=r.ID_Ruta AND DiaO=$diao),0)) AS saldo
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
            0 AS saldo
            FROM Cobranza cob
            LEFT JOIN c_destinatarios d ON d.id_destinatario = cob.Cliente
            LEFT JOIN c_cliente cte ON cte.Cve_Clte = d.Cve_Clte
            LEFT JOIN t_ruta r ON r.ID_Ruta = cob.RutaId
            WHERE (cob.DiaO = $diao) AND r.cve_ruta = '$ruta'  AND cob.Status = 1
            GROUP BY Cve_Clte, Documento

            ";
    if (!$result = mysqli_query($conn, $sqlCredito)) {
        echo json_encode(array('status' => 'error', 'data' => mysqli_error($conn)));
        exit;
    }
    $creditos = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $creditos[] = $row;
    }
    $totalCobranzaResumen = 0;
    if (count($creditos) > 0) {
        foreach ($creditos as $credito) {
            $totalCobranzaResumen += $credito['saldo'];
        }
    }
    $sqlResumen = "SELECT liq.DiaOperativo, liq.rutaName,
liq.TipoOperacion, 
IF(liq.TipoOperacion = 'Cobranza', TRUNCATE(SUM(liq.Abono), 2), TRUNCATE(SUM((liq.Importe+liq.IVA)), 2)) AS Total, 
TRUNCATE(SUM(liq.Descuento), 2) AS Descuento
FROM (
SELECT DISTINCT 
       IF(Venta.TipoVta = 'Credito', 'Credito', IF(DetalleVet.Importe < 0, 'Devoluciones', IF(Venta.Documento LIKE 'R%', 'Recarga', IF(Venta.Documento IN (SELECT Documento FROM Cobranza) AND IFNULL(DetalleCob.Abono, 0) > 0, 'Cobranza', 'Contado')))) AS TipoOperacion,
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,

        
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, IFNULL(DetalleVet.DescMon, 0) AS Descuento, DetalleVet.Comisiones AS Comisiones,

          '' AS TotalPedidas, c_articulo.control_peso, IFNULL(Cobranza.Saldo, '') AS Abono,

          #IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          0 AS inv_inicial_cajas,

          #IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,

          0 AS inv_inicial_piezas,

          c_articulo.num_multiplo AS Cajas, DetalleVet.Pza AS Piezas, Venta.Cancelada AS Cancelada,
          #IFNULL(sh.Stock, '0') AS InvInicial,
          0 AS InvInicial,
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
UNION

SELECT DISTINCT 
      IF(td.SubTotalPedidas < 0, 'Devoluciones', IF(th.Pedido LIKE 'R%', 'Recarga', IF(th.Pedido IN (SELECT Fol_folio FROM t_pedentregados), 'Entrega', 'PreVenta'))) AS TipoOperacion,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          SUM(td.SubTotalPedidas) AS Importe, SUM(td.IVAPedidas) AS IVA, td.DescuentoPedidas AS Descuento, '' AS Comisiones,
          td.TotalPedidas AS TotalPedidas, c_articulo.control_peso, IFNULL(Cobranza.Saldo, '') AS Abono,

          #IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IFNULL(sh.Stock, '0')),TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0)) AS inv_inicial_cajas,
          0 AS inv_inicial_cajas,

          #IF(um.mav_cveunimed != 'XBX', (IFNULL(sh.Stock, '0') - (c_articulo.num_multiplo*TRUNCATE((IFNULL(sh.Stock, '0')/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IFNULL(sh.Stock, '0'), 0)) AS inv_inicial_piezas,
          0 AS inv_inicial_piezas,

          c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, '' AS Cancelada, 
          #IFNULL(sh.Stock, '0') AS InvInicial,
          0 AS InvInicial,
          '' AS Promociones, RelOperaciones.DiaO AS DiaOperativo, c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
        
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          Inner JOIN RelOperaciones
                    ON #CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th.Pedido OR
                       RelOperaciones.Folio = th.Pedido

         LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta)
         LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido AND Cobranza.RutaId = t_ruta.ID_Ruta AND
                               Cobranza.DiaO = $diao
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO AND DiasO.RutaId = RelOperaciones.RutaId
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND t_ruta.ID_Ruta = Noventas.RutaId
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = RelOperaciones.RutaId
            left join t_ruta tr2 on tr2.ID_Ruta = th.RutaEnt
      left join DiasO do on do.DiaO = $diao and do.RutaId = th.RutaEnt
          WHERE  RelOperaciones.DiaO = $diao AND t_ruta.cve_ruta = '$ruta' AND th.Cancelada = 0 
          GROUP BY TipoOperacion,Articulo
) AS liq 
GROUP BY liq.TipoOperacion";
    if (!$result = mysqli_query($conn, $sqlResumen)) {
        echo json_encode(array('status' => 'error', 'data' => mysqli_error($conn)));
        exit;
    }
    $resumen = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $resumen[] = $row;
    }
    $preventa = 0;
    $venta_contado = 0;
    $venta_credito = 0;
    $devoluciones1 = 0;
    $cobranza = 0;
    $descuentos_vp = 0;
    $descuentos_credito = 0;
    if (count($resumen) > 0) {
        foreach ($resumen as $tipoResumen) {
            if ($tipoResumen['TipoOperacion'] == 'PreVenta') $preventa += $tipoResumen['Total'];// - $tipoResumen['Descuento'];
            if ($tipoResumen['TipoOperacion'] == 'Contado') $venta_contado += $tipoResumen['Total'];// - $tipoResumen['Descuento'];
            if ($tipoResumen['TipoOperacion'] == 'Credito') $venta_credito += $tipoResumen['Total'];// - $tipoResumen['Descuento'];
            if ($tipoResumen['TipoOperacion'] == 'Devoluciones') $devoluciones1 += $tipoResumen['Total'];// - $tipoResumen['Descuento'];
            if ($tipoResumen['Descuento'] > 0) $descuentos_vp += $tipoResumen['Descuento'];
            if ($tipoResumen['Descuento'] > 0 && $tipoResumen['TipoOperacion'] == 'Credito') $descuentos_credito += $tipoResumen['Descuento'];

        }
    }
    $resumenFinal = array(
        'preventa'      => $preventa,
        'venta_contado' => $venta_contado,
        'venta_credito' => $venta_credito,
        'devoluciones'  => $devoluciones1,
        'cobranza'      => $totalCobranzaResumen,
        'descuentos_vp' => $descuentos_vp,
        'descuentos_credito' => $descuentos_credito,
    );
    echo json_encode(array('status' => 'success', 'data' => [
        "analisis"     => $analisisVentas,
        "resumen"      => $resumenFinal,
        "devoluciones" => $devoluciones,
        "creditos"     => $creditos,
        "sqlResumen" => $sqlResumen
    ]));
    exit();
} else {
    echo json_encode(array('status' => 'error', 'data' => 'Accion no encontrada'));
    exit;
}
?>
