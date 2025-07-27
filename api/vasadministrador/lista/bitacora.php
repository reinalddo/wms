<?php
include '../../../config.php';

error_reporting(0);

$accion = $_POST['accion'];

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($accion == 'obtener_reporte_bitacora') {
    $ruta = $_POST['ruta'];
    $diao = $_POST['diao'];
    $almacen = $_POST['almacen'];
    $fecha = $_POST['fecha'];
    if ($ruta == '' || $almacen == '') {
        echo json_encode(array('status' => 'error', 'data' => 'Faltan datos'));
        exit;
    }
    if ($fecha != '' && $diao != '') {
        echo json_encode(array('status' => 'error', 'data' => 'No se puede seleccionar fecha y diao'));
        exit;
    }
    if ($fecha != '' && $diao == '') {
        $fecha = explode('-', $fecha);
        $fecha = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
    }
    if ($fecha == '' && $diao != '') {
        $sqlDiaOperativo = "SELECT Fecha FROM DiasO
             inner join t_ruta on DiasO.RutaId = t_ruta.ID_Ruta
             WHERE DiasO.DiaO = '$diao'
               AND t_ruta.cve_ruta = '$ruta'";
        if (!$result = mysqli_query($conn, $sqlDiaOperativo)) {
            echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los dias operativos'));
            exit;
        }
        $diaso = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $diaso[] = $row;
        }
        if (count($diaso) == 0) {
            echo json_encode(array('status' => 'error', 'data' => 'No se encontraron dias operativos'));
            exit;
        }
        $fecha = $diaso[0]['Fecha'];
    }
    $fechaInicio = $fecha . ' 00:00:00';
    $fechaFin = $fecha . ' 23:59:59';
/*
    $sql = "";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $var = $row[''];
*/
    $sql = "SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '$ruta'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $ID_Ruta = $row['ID_Ruta'];

    $sql = "SELECT DISTINCT 
            v.Nombre AS Vendedor
            FROM BitacoraTiempos b
            LEFT JOIN t_vendedores v ON v.Id_Vendedor = b.IdVendedor
            WHERE b.DiaO = $diao AND b.RutaId = $ID_Ruta";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $Vendedor = $row['Vendedor'];

    $sql = "SELECT COUNT(Codigo) AS n_visitas FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $visitas_realizadas = $row['n_visitas'];

    $sql = "SELECT COUNT(DISTINCT Codigo) AS n_visitas FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE' AND  Programado=1";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $visitas_clientes_programados = $row['n_visitas'];

    $sql = "SELECT COUNT(DISTINCT CodCli) AS n_visitas FROM  TH_SecVisitas WHERE RutaId=$ID_Ruta AND Fecha='$fecha'";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $visitas_programadas = $row['n_visitas'];


    $sql = "SELECT COUNT(c.id_cliente) AS n_visitas FROM th_pedido t LEFT JOIN c_cliente c ON c.Cve_Clte = t.Cve_clte WHERE t.Cve_clte = c.Cve_Clte AND t.DiaO = $diao AND t.ruta = $ID_Ruta";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $clientes_pedido = $row['n_visitas'];

    $sql = "SELECT COUNT(Codigo) AS n_visitas FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE' AND Codigo IN (SELECT CodCliente FROM Venta WHERE DiaO = $diao AND RutaId = $ID_Ruta)";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $clientes_venta = $row['n_visitas'];

    $clientes_pedidos_o_venta = $clientes_venta + $clientes_pedido;

    $sql = "SELECT COUNT(DISTINCT t.Codigo) AS n_visitas FROM BitacoraTiempos t
            INNER JOIN Noventas n ON t.Codigo = n.Cliente AND t.DiaO = $diao AND t.RutaId = $ID_Ruta
            WHERE n.DiaO = $diao AND n.RutaId = $ID_Ruta";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $ClientesVisitaNoVenta = $row['n_visitas'];

    $sql = "SELECT COUNT(DISTINCT t.Codigo) AS n_promocion FROM BitacoraTiempos t
            INNER JOIN PRegalado n ON t.Codigo = n.Cliente AND t.DiaO = $diao AND t.RutaId = $ID_Ruta
            WHERE n.DiaO = $diao AND n.RutaId = $ID_Ruta";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $ClientesPromocion = $row['n_promocion'];

    $sql = "SELECT  COUNT(DISTINCT CodCli) AS n_visitas FROM  TH_SecVisitas WHERE RutaId=$ID_Ruta AND Fecha='$fecha' AND CodCli NOT IN (SELECT Codigo FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE')";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $ClientesSinVisita = $row['n_visitas'];

    $sql = "SELECT COUNT(DISTINCT Codigo) AS n_visitas FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE' AND  Programado=1 AND (Codigo IN (SELECT COUNT(DISTINCT c.id_cliente) AS n_visitas FROM th_pedido t LEFT JOIN c_cliente c ON c.Cve_Clte = t.Cve_clte WHERE t.Cve_clte = c.Cve_Clte AND t.DiaO = $diao AND t.ruta = $ID_Ruta) OR Codigo IN (SELECT CodCliente FROM Venta WHERE DiaO = $diao AND RutaId = $ID_Ruta))";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $efectividadxventa_visitas = $row['n_visitas'];

//    $sql = "";
//    $res = mysqli_query($conn, $sql);
//    $row = mysqli_fetch_assoc($res);
//    $var = $row[''];

    $sql = "SELECT SUM(efectivo.Efectivo) AS efectivo FROM (
            SELECT SUM(v.TOTAL) AS Efectivo 
            FROM Venta v
            LEFT JOIN FormasPag f ON f.IdFpag = v.FormaPag
            WHERE v.DiaO = $diao AND v.RutaId = $ID_Ruta AND f.Forma = 'Efectivo' AND v.TipoVta = 'Contado' AND v.Cancelada = 0 AND v.TOTAL > 0
            UNION 
            SELECT SUM(TotPedidas) AS Efectivo FROM V_Cabecera_Pedido WHERE DiaO = $diao AND Ruta = $ID_Ruta AND FormaPag = 'Efectivo' AND Cancelada = 0
            ) AS efectivo";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $efectivo = $row['efectivo'];

    $sql = "SELECT SUM(credito.Credito) AS credito FROM (
            SELECT SUM(v.TOTAL) AS Credito
            FROM Venta v
            LEFT JOIN FormasPag f ON f.IdFpag = v.FormaPag
            WHERE v.DiaO = $diao AND v.RutaId = $ID_Ruta AND v.TipoVta = 'Credito' AND v.Cancelada = 0
            UNION 
            SELECT SUM(TotPedidas) AS Credito FROM V_Cabecera_Pedido WHERE DiaO = $diao AND Ruta = $ID_Ruta AND TipoPedido = 'Credito' AND Cancelada = 0
            ) AS credito";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $credito = $row['credito'];

    $sql = "SELECT SUM(Abono) AS Cobranza
            FROM DetalleCob 
            WHERE RutaId = $ID_Ruta AND DiaO = $diao AND Cancelada = 0";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $cobranza = $row['Cobranza'];

    $sql = "SELECT SUM(des.descuento) AS descuento FROM (
            SELECT SUM(d.DescMon) AS descuento
            FROM DetalleVet d
            LEFT JOIN Venta v ON v.Documento = d.Docto AND v.RutaId = d.RutaId
            WHERE v.DiaO = $diao AND v.RutaId = $ID_Ruta AND v.Cancelada = 0 AND v.TOTAL > 0
            UNION 
            SELECT SUM(dp.DescuentoPedidas) AS descuento 
            FROM V_Detalle_Pedido dp 
            LEFT JOIN V_Cabecera_Pedido cp ON cp.Pedido = dp.Pedido
            WHERE cp.DiaO = $diao AND cp.Ruta = $ID_Ruta AND cp.Cancelada = 0
            ) AS des";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $descuento = $row['descuento'];
//    $sql = "";
//    $res = mysqli_query($conn, $sql);
//    $row = mysqli_fetch_assoc($res);
//    $var = $row[''];

/*Inicio Operativo*/
$sql = "SELECT  DATE_FORMAT(HI, '%H:%i:%s') as HI FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Codigo='A18253'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$InicioOperativo = $row['HI'];

/*Inicio Primer Cliente*/
$sql = "SELECT MIN(DATE_FORMAT(HI, '%H:%i:%s')) as HI FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$InicioPrimerCliente = $row['HI'];

/*Tiempo de traslado*/
$sql = "SELECT MIN(DATE_FORMAT(HT, '%H:%i:%s')) as HT FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$TiempoTranscurrido = $row['HT'];

/*Cierre ultimo Cliente*/
$sql = "SELECT MAX(DATE_FORMAT(HI, '%H:%i:%s')) as HI FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$UltimoCliente = $row['HI'];

/*Cierre Operativo*/
$sql = "SELECT  DATE_FORMAT(HI, '%H:%i:%s') as HI FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Codigo='A18254'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$CierreOperativo = $row['HI'];

/*Último Tiempo de Traslado*/
$sql = "SELECT DATE_FORMAT(HT, '%H:%i:%s') as HT FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Codigo='A18254'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$UltimoTiempoTraslado = $row['HT'];

$sql = "SELECT DISTINCT SEC_TO_TIME((TIMESTAMPDIFF(MINUTE , (SELECT  HI FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Codigo='A18253'), (SELECT  HI FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Codigo='A18254') ))*60) AS diferencia FROM BitacoraTiempos WHERE  DiaO=$diao AND RutaId=$ID_Ruta AND Descripcion='VISITA A CLIENTE'";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$diferencia = $row['diferencia'];


$sql = "SELECT GROUP_CONCAT(productos.descripcion SEPARATOR ', ') AS TresMasVendidos FROM (
SELECT art.Articulo, art.descripcion ,COUNT(art.Articulo) AS Vendidos FROM (
SELECT d.Articulo, a.des_articulo AS descripcion, d.ID
FROM DetalleVet d
INNER JOIN Venta v ON v.Documento = d.Docto AND d.RutaId = v.RutaId
LEFT JOIN c_articulo a ON a.cve_articulo = d.Articulo
WHERE d.RutaId = $ID_Ruta AND v.DiaO = $diao

UNION

SELECT d.Cve_articulo AS Articulo, a.des_articulo AS descripcion ,d.id AS ID
FROM td_pedido d
INNER JOIN th_pedido t ON t.Fol_folio = d.fol_folio 
LEFT JOIN c_articulo a ON a.cve_articulo = d.Cve_articulo
WHERE t.ruta = $ID_Ruta AND t.DiaO = $diao
) AS art 
GROUP BY art.Articulo 
ORDER BY Vendidos DESC
LIMIT 3
) AS productos";

$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);
$TresMasVendidos = $row['TresMasVendidos'];

    $semaforo = array(
        'cve_ruta' => $ruta,
        'nombre_completo' => $Vendedor,
        'visitas_programadas' => $visitas_programadas,
        'visitas_realizadas' => $visitas_realizadas,
        'visitas_clientes_programados' => $visitas_clientes_programados,
        'ClientesVisitaVenta' => $clientes_pedidos_o_venta,
        'ClientesVisitaNoVenta' => $ClientesVisitaNoVenta,
        'ClientesPromocion' => $ClientesPromocion,
        'ClientesSinVisita' => $ClientesSinVisita,
        'efectividadxventa_visitas' => $efectividadxventa_visitas,
        'Cajas' => '',
        'efectivo' => $efectivo,
        'credito' => $credito,
        'cobranza' => $cobranza,
        'descuentos' => $descuento,
        'CajasPromocion' => '',
        'CajasVentaProm' => '',
        '3MasVendidos' => $TresMasVendidos,
        '3SaboresMasVendidos' => '',
        'InicioOperativo' => $InicioOperativo,
        'InicioPrimerCliente' => $InicioPrimerCliente,
        'TiempoTranscurrido' => $TiempoTranscurrido,
        'UltimoCliente' => $UltimoCliente,
        'CierreOperativo' => $CierreOperativo,
        'UltimoTiempoTraslado' => $UltimoTiempoTraslado,
        'DiferenciaHoras' => $diferencia

    );


    echo json_encode(array('status' => 'success', 'data' => $semaforo));
    exit();

} else {
    echo json_encode(array('status' => 'error', 'data' => 'Accion no encontrada'));
    exit;
}
?>
