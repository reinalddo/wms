<?php
include '../../../config.php';

error_reporting(0);

$accion = $_POST['accion'];

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($accion == 'obtener_reporte_semaforo') {
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
    $sqlSemaforo = "SELECT tr.cve_ruta,
       if(cu.nombre_completo is null, 'Sin Asignar', cu.nombre_completo)                                                                           as nombre_completo,
       (select count(*)
        from BitacoraTiempos bt
        where bt.RutaId = tr.ID_Ruta
          and bt.HI between ('$fechaInicio') and ('$fechaFin')
          and bt.Programado = true
          and bt.Descripcion not like '%DIA OPERATIVO%')                                                                                           as visitas_realizadas,
       (SELECT COUNT(DISTINCT CodCli)
        from TH_SecVisitas tsv
        where tsv.RutaId = tr.ID_Ruta
          and tsv.Fecha between ('$fecha') and ('$fecha'))                                                                                 as visitas_programadas,
       (SELECT COUNT(DISTINCT bt.Codigo)
        FROM TH_SecVisitas tsv
                 INNER JOIN BitacoraTiempos bt ON tsv.CodCli = bt.Codigo
            AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
        WHERE tsv.RutaId = tr.ID_Ruta
          AND tsv.Fecha BETWEEN ('$fecha') and ('$fecha')
          AND bt.Programado = true
          AND bt.Visita = true)                                                                                                                    AS visitas_clientes_programados,
       (SELECT COUNT(*)
        FROM TH_SecVisitas tsv
                 LEFT JOIN BitacoraTiempos bt ON tsv.CodCli = bt.Codigo
            AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
        WHERE tsv.RutaId = tr.ID_Ruta
          AND tsv.Fecha BETWEEN ('$fecha') and ('$fecha')
          AND (bt.RutaId IS NULL OR (bt.Programado = false AND bt.Visita = true)))                                                                 AS visitas_no_programadas,
       (SELECT COUNT(DISTINCT v.CodCliente)
        FROM Venta v
        WHERE v.RutaId = tr.ID_Ruta
          AND v.Fecha BETWEEN ('$fechaInicio') and ('$fechaFin')) +
       (SELECT COUNT(DISTINCT vcp.Cod_Cliente)
        FROM V_Cabecera_Pedido vcp
        WHERE vcp.Ruta = tr.ID_Ruta
          AND vcp.Fec_Pedido BETWEEN ('$fecha') and ('$fecha'))                                                                            AS ClientesVisitaVenta,
       ((SELECT COUNT(*)
         FROM TH_SecVisitas tsv
         WHERE tsv.RutaId = tr.ID_Ruta
           AND tsv.Fecha BETWEEN ('$fecha') AND ('$fecha')) - (SELECT COUNT(DISTINCT v.CodCliente)
                                                                       FROM Venta v
                                                                       WHERE v.RutaId = tr.ID_Ruta
                                                                         AND v.Fecha BETWEEN ('$fechaInicio') and ('$fechaFin'))) AS ClientesVisitaNoVenta,
       (SELECT SUM(total_count) AS total_sum
FROM (SELECT COUNT(DISTINCT PR.Cliente) AS total_count
      FROM PRegalado PR
               INNER JOIN Venta V ON PR.Cliente = V.CodCliente AND PR.DiaO = V.DiaO
      WHERE V.RutaId = tr.ID_Ruta
        AND V.Documento = PR.Docto
        AND V.Fecha BETWEEN ('$fechaInicio') and ('$fechaFin')
      UNION
      SELECT COUNT(DISTINCT PR.Cliente) AS total_count
      FROM PRegalado PR
               INNER JOIN V_Cabecera_Pedido V ON PR.Cliente = V.Cod_Cliente AND PR.DiaO = V.DiaO
      WHERE V.Ruta = tr.ID_Ruta
        AND V.Pedido = PR.Docto
        AND V.Fec_Pedido BETWEEN ('$fecha') AND ('$fecha')) AS subquery)                                                                 AS ClientesPromocion,
    COALESCE((SELECT bt.HI
                 FROM BitacoraTiempos bt
                 WHERE bt.RutaId = tr.ID_Ruta
                   AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
        order by bt.HI asc 
                 LIMIT 1), 'S/R')              as InicioOperativo,
       COALESCE((SELECT bt.HI
                 FROM BitacoraTiempos bt
                 WHERE bt.RutaId = tr.ID_Ruta
                   AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
                 ORDER BY bt.HI
                 LIMIT 1, 1), 'S/R')           as InicioPrimerCliente,
       COALESCE(TIMEDIFF((SELECT bt.HI
                          FROM BitacoraTiempos bt
                          WHERE bt.RutaId = tr.ID_Ruta
                            AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
                          ORDER BY bt.HI
                          LIMIT 1, 1),(SELECT bt.HI
                 FROM BitacoraTiempos bt
                 WHERE bt.RutaId = tr.ID_Ruta
                   AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
        order by bt.HI asc 
                 LIMIT 1)), 'S/R') as TiempoTranscurrido,
       COALESCE((SELECT bt.HI
                 FROM BitacoraTiempos bt
                 WHERE bt.RutaId = tr.ID_Ruta
                   AND bt.Codigo NOT LIKE '%A%'
                   AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
                 ORDER BY bt.HI DESC
                 LIMIT 1), 'S/R')              as UltimoCliente,
       COALESCE((SELECT bt.HI
                 FROM BitacoraTiempos bt
                 WHERE bt.RutaId = tr.ID_Ruta
                   AND bt.Descripcion LIKE '%FIN%'
                   AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
                 ORDER BY bt.HI DESC
                 LIMIT 1), 'S/R')              as CierreOperativo,
       COALESCE(TIMEDIFF((SELECT bt.HI
                          FROM BitacoraTiempos bt
                          WHERE bt.RutaId = tr.ID_Ruta
                            AND bt.Codigo NOT LIKE '%A%'
                            AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
                          ORDER BY bt.HI DESC
                          LIMIT 1),
                         (SELECT bt.HI
                          FROM BitacoraTiempos bt
                          WHERE bt.RutaId = tr.ID_Ruta
                            AND bt.Codigo NOT LIKE '%A%'
                            AND bt.HI BETWEEN ('$fechaInicio') and ('$fechaFin')
                          ORDER BY bt.HI DESC
                          LIMIT 1, 1)), 'S/R') as UltimoTiempoTraslado
from t_ruta tr
         left join Rel_Ruta_Agentes RRA on tr.ID_Ruta = RRA.cve_ruta
         left join c_usuario cu on RRA.cve_vendedor = cu.id_user
where tr.cve_almacenp = '$almacen'";
    if ($ruta != 'todas') {
        $sqlSemaforo .= " and tr.cve_ruta = '$ruta'";
    }
    $sqlSemaforo .= " order by tr.ID_Ruta";
    if (!$result = mysqli_query($conn, $sqlSemaforo)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los datos'));
        exit;
    }
    $semaforo = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $semaforo[] = $row;
    }
    if (count($semaforo) == 0) {
        echo json_encode(array('status' => 'error', 'data' => 'No se encontraron datos'));
        exit;
    }
    $rutasCve = array();
    foreach ($semaforo as &$rutas) {
        $rutasCve[] = $rutas['cve_ruta'];
    }
    $rutasCve = "'" . implode("','", $rutasCve) . "'";
    $sqlCajas = "SELECT Ruta                         as cve_ruta,
       SUM(Cajas)                   AS Cajas,
       SUM(Piezas)                  AS Piezas,
       ROUND(SUM(Porcentaje) / 100) AS CajasExtra
FROM
(SELECT FLOOR(SUM(vdp.Pedidas) / ca.num_multiplo)                    AS Cajas,
             SUM(vdp.Pedidas) % ca.num_multiplo                           AS Piezas,
             tr.cve_ruta                                                  AS Ruta,
             cve_articulo,
             vcp.Cve_Clte,
             vcp.Pedido,
             (SUM(vdp.Pedidas) % ca.num_multiplo) / ca.num_multiplo * 100 as Porcentaje
      FROM V_Cabecera_Pedido vcp
               INNER JOIN V_DetallePedidoSinPromo vdp ON vcp.Pedido = vdp.Pedido AND vdp.Cancelada = 0
               INNER JOIN c_articulo ca ON vdp.Articulo = ca.cve_articulo
               INNER JOIN t_ruta tr ON vcp.Ruta = tr.ID_Ruta
  WHERE
    tr.cve_ruta IN ($rutasCve)
    AND vcp.Fec_Pedido = '$fecha'
   AND vdp.Precio > 0
        AND vcp.Cancelada = 0
  and vcp.TipoPedido != 'Obsequio'
        and vcp.Cve_Clte
      GROUP BY vdp.Articulo, tr.cve_ruta, vcp.Cve_Clte, vcp.Pedido

  UNION

  SELECT (IF(DV.Tipo = 0, DV.Pza,
                 IF(um.mav_cveunimed = 'XBX', IF(ca.num_multiplo = 1, 0, DV.Pza),
                    TRUNCATE((DV.Pza / ca.num_multiplo), 0))))       as Cajas,
             SUM(DV.Pza) % ca.num_multiplo                           AS Piezas,
             tr.cve_ruta                                             AS Ruta,
             cve_articulo,
             cc.Cve_Clte,
             V.Documento                                             as Pedido,
             (SUM(DV.Pza) % ca.num_multiplo) / ca.num_multiplo * 100 as Porcentaje
      FROM Venta V
               inner JOIN DetalleVet DV ON V.Documento = DV.Docto
               INNER JOIN c_articulo ca ON DV.Articulo = ca.cve_articulo
               INNER JOIN t_ruta tr ON V.RutaId = tr.ID_Ruta
               left join c_cliente cc on V.CodCliente = cc.id_cliente
               LEFT JOIN c_unimed um ON um.id_umed = ca.unidadMedida
  WHERE tr.cve_ruta IN ($rutasCve)
    AND V.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'
    AND V.TipoVta != 'Obsequio'
        AND V.Cancelada = 0
        and cc.Cve_Clte
      GROUP BY DV.Articulo, tr.cve_ruta, cc.Cve_Clte, V.Documento) AS Subquery
GROUP BY Ruta";
    //union
    //      SELECT
    //if(pr.Tipmed = 'Caja', pr.Cant, 0)  as Caja,
    //if(pr.Tipmed = 'Pieza', pr.Cant, 0) as Pieza,
    //tr.cve_ruta                         AS Ruta,
    //cve_articulo
    //FROM PRegalado pr
    //         INNER JOIN c_articulo ca ON pr.SKU = ca.cve_articulo
    //         INNER JOIN t_ruta tr ON pr.RutaId = tr.ID_Ruta
    //         inner join DiasO D on tr.ID_Ruta = D.RutaId
    //WHERE tr.cve_ruta IN ($rutasCve)
    //  AND D.Fecha = '$fecha'
    //  and D.DiaO = pr.DiaO
    //group by pr.SKU, pr.Tipmed, pr.Cant, tr.cve_ruta
    if (!$result = mysqli_query($conn, $sqlCajas)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los datos'));
        exit;
    }
    $cajas = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cajas[] = $row;
    }
    $sqlCajasPromocion = "select sum(TotalCajasPromocion) as TotalCajasPromocion, cve_ruta
from (SELECT SUM(PRegalado.Cant) as TotalCajasPromocion, t_ruta.cve_ruta
      FROM V_Cabecera_Pedido vcp
               INNER JOIN PRegalado ON vcp.Pedido = PRegalado.Docto
               INNER JOIN t_ruta ON vcp.Ruta = t_ruta.ID_Ruta
      WHERE t_ruta.cve_ruta IN ($rutasCve)
        AND vcp.Fec_Pedido = '$fecha'
        AND vcp.Cancelada = 0
      GROUP BY t_ruta.cve_ruta
      union
      SELECT SUM(PRegalado.Cant) as TotalCajasPromocion, t_ruta.cve_ruta
      FROM Venta V
               INNER JOIN PRegalado ON V.Documento = PRegalado.Docto
               INNER JOIN t_ruta ON V.RutaId = t_ruta.ID_Ruta
      WHERE t_ruta.cve_ruta IN ($rutasCve)
        AND V.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'
        AND V.Cancelada = 0
      GROUP BY t_ruta.cve_ruta
      union
      SELECT FLOOR(SUM(vdp.Pedidas) / ca.num_multiplo) AS TotalCajasPromocion,
             tr.cve_ruta                               AS cve_ruta
      FROM V_Cabecera_Pedido vcp
               INNER JOIN V_DetallePedidoSinPromo vdp ON vcp.Pedido = vdp.Pedido AND vdp.Cancelada = 0
               INNER JOIN c_articulo ca ON vdp.Articulo = ca.cve_articulo
               INNER JOIN t_ruta tr ON vcp.Ruta = tr.ID_Ruta
      WHERE tr.cve_ruta IN ($rutasCve)
        AND vcp.Fec_Pedido = '$fecha'
        AND vcp.Cancelada = 0
        and vcp.TipoPedido = 'Obsequio'
      GROUP BY tr.cve_ruta
      union
      SELECT FLOOR(SUM(DV.Pza) / ca.num_multiplo) AS TotalCajasPromocion,
             tr.cve_ruta
      FROM Venta V
               INNER JOIN DetalleVet DV ON V.Documento = DV.Docto
               INNER JOIN c_articulo ca ON DV.Articulo = ca.cve_articulo
               INNER JOIN t_ruta tr ON V.RutaId = tr.ID_Ruta
               inner join c_cliente cc on V.CodCliente = cc.id_cliente
               inner join PRegalado pr on pr.Docto = V.Documento and pr.SKU = DV.Articulo
      WHERE tr.cve_ruta IN ($rutasCve)
        AND V.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'
        AND V.TipoVta = 'Obsequio'
        AND V.Cancelada = 0
      GROUP BY tr.cve_ruta) as subquery
      group by cve_ruta";
    if (!$result = mysqli_query($conn, $sqlCajasPromocion)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los datos'));
        exit;
    }
    $cajasPromocion = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cajasPromocion[] = $row;
    }
    $sqlVentasPromocion = "SELECT sum(cajas) as Cajas, rutaName as cve_ruta
from (SELECT ventas.Cliente,
             ventas.Responsable       AS Responsable,
             SUM(ventas.PromoCajas)   AS PromoCajas,
             SUM(ventas.PromoPiezas)  AS PromoPiezas,
             ventas.num_multiplo,
             SUM(ventas.cajas)        AS cajas,
             SUM(ventas.piezas)       AS piezas,
             SUM(ventas.obseq_cajas)  AS obseq_cajas,
             SUM(ventas.obseq_piezas) AS obseq_piezas,
             SUM(ventas.desc_cajas)   AS desc_cajas,
             SUM(ventas.desc_piezas)  AS desc_piezas,
             ventas.rutaName
      FROM (SELECT ventas1.Cliente,
                   ventas1.Responsable                                                                    AS Responsable,
                   SUM(ventas1.PromoC)                                                                    AS PromoCajas,
                   SUM(ventas1.PromoP)                                                                    AS PromoPiezas,
                   ventas1.num_multiplo,
                   ((SUM(ventas1.cajas_total)) + TRUNCATE((SUM(ventas1.piezas_total) / ventas1.num_multiplo), 0) -
                    SUM(ventas1.PromoC))                                                                  AS cajas,
                   (IF(ventas1.mav_cveunimed != 'XBX', (SUM(ventas1.piezas_total) - (ventas1.num_multiplo *
                                                                                     TRUNCATE((SUM(ventas1.piezas_total) / ventas1.num_multiplo), 0))),
                       IF(ventas1.num_multiplo = 1, SUM(ventas1.piezas_total), 0)) - SUM(ventas1.PromoP)) AS piezas,
                   ((SUM(ventas1.obseq_cajas)) +
                    TRUNCATE((SUM(ventas1.obseq_piezas) / ventas1.num_multiplo), 0))                      AS obseq_cajas,
                   (IF(ventas1.mav_cveunimed != 'XBX', (SUM(ventas1.obseq_piezas) - (ventas1.num_multiplo *
                                                                                     TRUNCATE((SUM(ventas1.obseq_piezas) / ventas1.num_multiplo), 0))),
                       IF(ventas1.num_multiplo = 1, SUM(ventas1.obseq_piezas), 0)))                       AS obseq_piezas,
                   ((SUM(ventas1.desc_cajas)) +
                    TRUNCATE((SUM(ventas1.desc_piezas) / ventas1.num_multiplo), 0))                       AS desc_cajas,
                   (IF(ventas1.mav_cveunimed != 'XBX', (SUM(ventas1.desc_piezas) - (ventas1.num_multiplo *
                                                                                    TRUNCATE((SUM(ventas1.desc_piezas) / ventas1.num_multiplo), 0))),
                       IF(ventas1.num_multiplo = 1, SUM(ventas1.desc_piezas), 0)))                        AS desc_piezas,
                   ventas1.rutaName
            FROM (SELECT DISTINCT Venta.Fecha                             as FechaBusq,
                                  DATE_FORMAT(Venta.Fecha, '%d-%m-%Y')    AS Fecha,
                                  Venta.RutaId                            AS Ruta,
                                  t_ruta.cve_ruta                         AS rutaName,
                                  c_cliente.Cve_Clte                      AS Cliente,
                                  Venta.CodCliente                        AS CodCliente,
                                  c_cliente.RazonSocial                   AS Responsable,
                                  c_destinatarios.razonsocial             AS nombreComercial,
                                  Venta.Documento                         AS Folio,
                                  Venta.TipoVta                           AS Tipo,
                                  Venta.DiaO                              as DiaO,
                                  um.des_umed                             as unidadMedida,
                                  DetalleVet.Precio                       AS Precio,
                                  DetalleVet.Importe                      AS Importe,
                                  DetalleVet.IVA                          AS IVA,
                                  DetalleVet.DescMon                      AS Descuento,
                                  'Venta'                                 AS Operacion,
                                  IF(Venta.Cancelada = 0, 'No', 'Si')     as Cancelada,
                                  c_articulo.cve_articulo                 AS cve_articulo,
                                  DetalleVet.Descripcion                  AS Articulo,
                                  um.mav_cveunimed,
                                  c_articulo.num_multiplo,
                                  (IF(DetalleVet.Tipo = 0, DetalleVet.Pza,
                                      IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, DetalleVet.Pza),
                                         TRUNCATE((DetalleVet.Pza / c_articulo.num_multiplo), 0))) +
                                   IF(pr.Tipmed = 'Caja', (pr.Cant), 0))  AS cajas_total,
                                  (IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (DetalleVet.Pza -
                                                                                             (c_articulo.num_multiplo *
                                                                                              TRUNCATE((DetalleVet.Pza / c_articulo.num_multiplo), 0))),
                                                                 IF(c_articulo.num_multiplo = 1, DetalleVet.Pza, 0))) +
                                   IF(pr.Tipmed != 'Caja', (pr.Cant), 0)) AS piezas_total,
                                  IF(pr.Tipmed = 'Caja', (pr.Cant), 0)    AS PromoC,
                                  IF(pr.Tipmed != 'Caja', (pr.Cant), 0)   AS PromoP,
                                  0                                       AS obseq_cajas,
                                  0                                       AS obseq_piezas,
                                  0                                       AS desc_cajas,
                                  0                                       AS desc_piezas
                  FROM Venta
                           LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
                           LEFT JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId
                           LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
                           INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa
                           LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO
                           LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Venta.CodCliente
                           LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
                           LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
                           LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND
                                                     pr.IdEmpresa = DetalleVet.IdEmpresa AND
                                                     pr.Cliente = Venta.CodCliente
                  WHERE t_ruta.cve_ruta in ($rutasCve)
                    AND Venta.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'
                    AND Venta.Cancelada = 0

                  UNION

                  SELECT DISTINCT RelOperaciones.Fecha                                           as FechaBusq,
                                  DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y')                         AS Fecha,
                                  RelOperaciones.RutaId                                          AS Ruta,
                                  t_ruta.cve_ruta                                                AS rutaName,
                                  th.Cve_Clte                                                    AS Cliente,
                                  th.Cod_Cliente                                                 AS CodCliente,
                                  c_cliente.RazonSocial                                          AS Responsable,
                                  c_destinatarios.razonsocial                                    AS nombreComercial,
                                  th.Pedido                                                      AS Folio,
                                  th.TipoPedido                                                  AS Tipo,
                                  RelOperaciones.DiaO                                            as DiaO,
                                  um.des_umed                                                    as unidadMedida,
                                  td.Precio                                                      AS Precio,
                                  td.SubTotalPedidas                                             AS Importe,
                                  td.IVAPedidas                                                  AS IVA,
                                  td.DescuentoPedidas                                            AS Descuento,
                                  IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta')     AS Operacion,
                                  IF(th.Cancelada = 0, 'No', 'Si')                               as Cancelada,
                                  c_articulo.cve_articulo                                        AS cve_articulo,
                                  td.Descripcion                                                 AS Articulo,
                                  um.mav_cveunimed,
                                  c_articulo.num_multiplo,
                                  IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0,
                                     IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),
                                     IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0,
                                        TRUNCATE((td.Pedidas / c_articulo.num_multiplo), 0), 0)) AS cajas_total,
                                  IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0, (td.Pedidas -
                                                                                                         (c_articulo.num_multiplo *
                                                                                                          TRUNCATE((td.Pedidas / c_articulo.num_multiplo), 0))),
                                     IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0,
                                        IF(c_articulo.num_multiplo = 1, td.Pedidas, 0), 0))      AS piezas_total,
                                  IF(pr.Tipmed = 'Caja', (pr.Cant), 0)                           AS PromoC,
                                  IF(pr.Tipmed != 'Caja', (pr.Cant), 0)                          AS PromoP,
                                  0                                                              AS obseq_cajas,
                                  0                                                              AS obseq_piezas,
                                  IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0,
                                     IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),
                                     IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0,
                                        TRUNCATE((td.Pedidas / c_articulo.num_multiplo), 0), 0)) AS desc_cajas,
                                  IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0, (td.Pedidas -
                                                                                                         (c_articulo.num_multiplo *
                                                                                                          TRUNCATE((td.Pedidas / c_articulo.num_multiplo), 0))),
                                     IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0,
                                        IF(c_articulo.num_multiplo = 1, td.Pedidas, 0), 0))      AS desc_piezas
                  FROM V_Cabecera_Pedido th
                           LEFT JOIN V_Detalle_Pedido td
                                     ON td.Pedido = th.Pedido
                           INNER JOIN th_pedido p ON p.Fol_Folio = th.Pedido
                           LEFT JOIN RelOperaciones
                                     ON CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th.Pedido OR
                                        RelOperaciones.Folio = th.Pedido
                           LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta)
                           LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
                           INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa
                           LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
                           LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = th.Cod_Cliente
                           LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
                           LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
                           LEFT JOIN PRegalado pr
                                     ON td.Pedido = pr.Docto AND td.Articulo = pr.SKU AND th.Ruta = pr.RutaId AND
                                        td.IdEmpresa = pr.IdEmpresa AND th.Cod_Cliente = pr.Cliente
                  WHERE t_ruta.cve_ruta in ($rutasCve)
                    AND th.Fec_Pedido = '$fecha'
                    AND th.Cancelada = 0
                    and th.TipoPedido != 'Obsequio'
                    AND th.Cancelada = 0
                    AND p.tipo_negociacion != 'Obsequio'


                  UNION

                  SELECT DISTINCT RelOperaciones.Fecha                                         AS FechaBusq,
                                  DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y')                       AS Fecha,
                                  RelOperaciones.RutaId                                        AS Ruta,
                                  t_ruta.cve_ruta                                              AS rutaName,
                                  th.Cve_Clte                                                  AS Cliente,
                                  c_destinatarios.id_destinatario                              AS CodCliente,
                                  c_cliente.RazonSocial                                        AS Responsable,
                                  c_destinatarios.razonsocial                                  AS nombreComercial,
                                  th.Fol_Folio                                                 AS Folio,
                                  th.TipoPedido                                                AS Tipo,
                                  RelOperaciones.DiaO                                          AS DiaO,
                                  um.des_umed                                                  AS unidadMedida,
                                  td.Precio_unitario                                           AS Precio,
                                  td.Precio_unitario                                           AS Importe,
                                  td.IVA                                                       AS IVA,
                                  td.Precio_unitario                                           AS Descuento,
                                  IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta')   AS Operacion,
                                  IF(th.Activo = 1, 'No', 'Si')                                AS Cancelada,
                                  c_articulo.cve_articulo                                      AS cve_articulo,
                                  c_articulo.des_articulo                                      AS Articulo,
                                  um.mav_cveunimed,
                                  c_articulo.num_multiplo,
                                  0                                                            AS cajas_total,
                                  0                                                            AS piezas_total,
                                  0                                                            AS PromoC,
                                  0                                                            AS PromoP,
                                  IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Num_Cantidad),
                                     TRUNCATE((td.Num_Cantidad / c_articulo.num_multiplo), 0)) AS obseq_cajas,
                                  IF(um.mav_cveunimed != 'XBX', (td.Num_Cantidad - (c_articulo.num_multiplo *
                                                                                    TRUNCATE((td.Num_Cantidad / c_articulo.num_multiplo), 0))),
                                     IF(c_articulo.num_multiplo = 1, td.Num_Cantidad, 0))      AS obseq_piezas,
                                  0                                                            AS desc_cajas,
                                  0                                                            AS desc_piezas
                  FROM th_pedido th
                           LEFT JOIN td_pedido td
                                     ON td.Fol_Folio = th.Fol_Folio
                           LEFT JOIN RelOperaciones
                                     ON CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th.Fol_Folio OR
                                        RelOperaciones.Folio = th.Fol_Folio
                           LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.ruta)
                           LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Cve_Articulo
                           INNER JOIN c_almacenp ON c_almacenp.id = th.cve_almac
                           LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
                           LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th.Cve_Clte
                           LEFT JOIN c_destinatarios ON c_destinatarios.Cve_Clte = th.Cve_Clte
                           LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
                           LEFT JOIN PRegalado pr
                                     ON td.Fol_Folio = pr.Docto AND td.Cve_Articulo = pr.SKU AND th.ruta = pr.RutaId AND
                                        c_almacenp.clave = pr.IdEmpresa AND c_destinatarios.id_destinatario = pr.Cliente
                  WHERE 1
                    AND th.tipo_negociacion = 'Obsequio'
                    AND th.Activo = 1
                    and t_ruta.cve_ruta in ($rutasCve)
                    and RelOperaciones.Fecha BETWEEN '$fechaInicio' AND '$fechaFin') AS ventas1
            WHERE ventas1.Responsable IS NOT NULL
            GROUP BY Cliente, num_multiplo) AS ventas
      WHERE ventas.Responsable IS NOT NULL
        and PromoCajas > 0
      GROUP BY Cliente
      ORDER BY Responsable) as subquery
group by rutaName";
    if (!$result = mysqli_query($conn, $sqlVentasPromocion)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los datos'));
        exit;
    }

    $ventasPromocion = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $ventasPromocion[] = $row;
    }
    $sqlEfectivoCreditoCobranza = "SELECT *
FROM (SELECT DISTINCT c_almacenp.nombre                                                                       AS sucursalNombre,
                      Venta.Id                                                                                AS idVenta,
                      Venta.IdEmpresa                                                                         AS Sucursal,
                      Venta.Fecha                                                                             AS FechaBusq,
                      DATE_FORMAT(Venta.Fecha, '%d-%m-%Y')                                                    AS Fecha,
                      DATE_FORMAT(Venta.Fvence, '%d-%m-%Y')                                                   AS FechaCompromiso,
                      Venta.RutaId                                                                            AS Ruta,
                      t_ruta.cve_ruta                                                                         AS rutaName,
                      Venta.CodCliente                                                                        AS Cliente,
                      c_cliente.Cve_Clte                                                                      as CveCliente,
                      c_cliente.RazonSocial                                                                   AS Responsable,
                      IFNULL(c_cliente.limite_credito, 0)                                                     AS limite_credito,
                      c_destinatarios.razonsocial                                                             AS nombreComercial,
                      Venta.Documento                                                                         AS Folio,
                      Venta.TipoVta                                                                           AS Tipo,
                      FormasPag.Forma                                                                         AS metodoPago,
                      (SELECT SUM(dv.Importe)
                       FROM DetalleVet dv
                       WHERE dv.Docto = Venta.Documento)                                                      AS Importe,
                      (SELECT SUM(IFNULL(dv.IVA, 0)) FROM DetalleVet dv WHERE dv.Docto = Venta.Documento)     AS IVA,
                      (SELECT SUM(IFNULL(dv.DescMon, 0))
                       FROM DetalleVet dv
                       WHERE dv.Docto = Venta.Documento)                                                      AS Descuento,
                      0                                                                                       AS Total,
                      Venta.Cancelada                                                                         AS Cancelada,
                      'Venta'                                                                                 AS Operacion,
                      Venta.VendedorId                                                                        AS vendedorID,
                      t_vendedores.Cve_Vendedor                                                               as cveVendedor,
                      t_vendedores.Nombre                                                                     AS Vendedor,
                      Venta.ID_Ayudante1                                                                      AS Ayudante1,
                      Venta.ID_Ayudante2                                                                      AS Ayudante2,
                      Venta.DiaO                                                                              AS DiaOperativo,
                      Cobranza.DiaO                                                                           AS DiaOperativoCobranza,
                      IFNULL(Cobranza.Status, 1)                                                              AS StatusCobranza,
                      Cobranza.Documento                                                                      AS Documento,
                      (IFNULL(Cobranza.Saldo, 0) -
                       IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = Venta.Documento),
                              0))                                                                             AS saldoFinal,
                      IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = Venta.Documento), 0)        AS Abono,
                      IFNULL(Venta.Saldo, '0.00')                                                             AS saldoActual,
                      Cobranza.FechaReg                                                                       AS fechaRegistro,
                      Cobranza.FechaVence                                                                     AS fechaVence,
                      Venta.DocSalida                                                                         AS tipoDoc,
                      Noventas.MotivoId                                                                       AS idMotivo,
                      MotivosNoVenta.Motivo                                                                   AS Motivo,
                      COUNT(pr.Docto)                                                                         AS tienepromo
      FROM Venta
               LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%', th_pedido.Fol_folio)
               LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
               INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId
               LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag
               LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
               LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
               INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '39'


               LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
               LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
               LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
               LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND
                                     Noventas.Cliente = Venta.CodCliente
               LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
               LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Venta.CodCliente
               LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
               LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
               LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND
                                         pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
      WHERE 1
        AND t_ruta.cve_ruta IN ($rutasCve)
        and Venta.Fecha = '$fecha' AND Venta.Cancelada = 0 
      GROUP BY idVenta,rutaName
      UNION
      SELECT DISTINCT c_almacenp.nombre                                                                   AS sucursalNombre,
                      ''                                                                                  AS idVenta,
                      c_almacenp.clave                                                                    AS Sucursal,
                      RelOperaciones.Fecha                                                                AS FechaBusq,
                      DATE_FORMAT(RelOperaciones.Fecha, '%d-%m-%Y')                                       AS Fecha,
                      DATE_FORMAT(th_pedido.Fec_Entrega, '%d-%m-%Y')                                      AS FechaCompromiso,
                      IFNULL(RelOperaciones.RutaId, th_pedido.Ruta)                                       AS Ruta,
                      t_ruta.cve_ruta                                                                     AS rutaName,
                      th_pedido.Cod_Cliente                                                               AS Cliente,
                      c_cliente.Cve_Clte                                                                  as CveCliente,
                      c_cliente.RazonSocial                                                               AS Responsable,
                      IFNULL(c_cliente.limite_credito, 0)                                                 AS limite_credito,
                      c_destinatarios.razonsocial                                                         AS nombreComercial,
                      th_pedido.Pedido                                                                    AS Folio,
                      th_pedido.TipoPedido                                                                AS Tipo,
                      IFNULL(th_pedido.FormaPag, '')                                                      AS metodoPago,
                      th_pedido.SubTotPedidas                                                             AS Importe,
                      th_pedido.TotIVAPedidas                                                             AS IVA,
                      IFNULL(th_pedido.TotDescPedidas, 0.00)                                              AS Descuento,
                      th_pedido.TotPedidas                                                                AS Total,
                      th_pedido.Cancelada                                                                 AS Cancelada,
                      IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta')                          AS Operacion,
                      th_pedido.cve_Vendedor                                                              AS vendedorID,
                      t_vendedores.Cve_Vendedor                                                           as cveVendedor,
                      t_vendedores.Nombre                                                                 AS Vendedor,
                      ''                                                                                  AS Ayudante1,
                      ''                                                                                  AS Ayudante2,
                      RelOperaciones.DiaO                                                                 AS DiaOperativo,
                      ''                                                                                  AS DiaOperativoCobranza,
                      IFNULL(Cobranza.Status, 1)                                                          AS StatusCobranza,
                      Cobranza.Documento                                                                  AS Documento,
                      (IFNULL(Cobranza.Saldo, IFNULL(th_pedido.TotPedidas, 0)) -
                       IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = th_pedido.Pedido), 0)) AS saldoFinal,
                      IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = th_pedido.Pedido), 0)   AS Abono,
                      ''                                                                                  AS saldoActual,
                      Cobranza.FechaReg                                                                   AS fechaRegistro,
                      Cobranza.FechaVence                                                                 AS fechaVence,
                      ''                                                                                  AS tipoDoc,
                      ''                                                                                  AS idMotivo,
                      ''                                                                                  AS Motivo,
                      COUNT(pr.Docto)                                                                     AS tienepromo
      FROM V_Cabecera_Pedido th_pedido
               LEFT JOIN V_Detalle_Pedido td_pedido ON td_pedido.Pedido = th_pedido.Pedido
               LEFT JOIN RelOperaciones
                         ON CONCAT(RelOperaciones.RutaId, '_', RelOperaciones.Folio) = th_pedido.Pedido OR
                            RelOperaciones.Folio = th_pedido.Pedido
               LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th_pedido.Ruta)
               LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th_pedido.cve_Vendedor
               LEFT JOIN FormasPag ON FormasPag.IdFpag = th_pedido.FormaPag
               LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo
               LEFT JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '39'
               LEFT JOIN Cobranza ON CONCAT(Cobranza.RutaId, '_', Cobranza.Documento) = th_pedido.Pedido
               LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
               LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
               LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = th_pedido.Cod_Cliente
               LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
               LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
               LEFT JOIN PRegalado pr ON td_pedido.Pedido LIKE CONCAT('%', pr.Docto)
      WHERE 1
        AND t_ruta.cve_ruta IN ($rutasCve)
        and th_pedido.Fec_Pedido = '$fecha' AND th_pedido.Cancelada = 0
      GROUP BY Folio,rutaName) AS ventas
WHERE 1
  AND IFNULL(ventas.rutaName, '') != ''
  AND ventas.rutaName != '' AND ventas.Cancelada = 0 
ORDER BY STR_TO_DATE(ventas.Fecha, '%d-%m-%Y') DESC, ventas.DiaOperativo DESC";
    if (!$result = mysqli_query($conn, $sqlEfectivoCreditoCobranza)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los datos'));
        exit;
    }
    $efectivoCreditoCobranza = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $efectivoCreditoCobranza[] = $row;
    }
    $sql3masvendidos = "WITH TotalCajasPorArticulo AS (
    -- Consulta que obtiene el total de cajas por artículo y ruta
    SELECT Ruta, cve_articulo, des_articulo, SUM(Caja) AS TotalCajas
    FROM (SELECT IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS Caja,
                 tr.cve_ruta                        AS Ruta,
                 cve_articulo,
                 ca.des_articulo
          FROM PRegalado pr
                   INNER JOIN c_articulo ca ON pr.SKU = ca.cve_articulo
                   INNER JOIN t_ruta tr ON pr.RutaId = tr.ID_Ruta
                   INNER JOIN DiasO D ON tr.ID_Ruta = D.RutaId
          WHERE tr.cve_ruta IN ($rutasCve)
            AND D.Fecha = '$fecha'
            AND D.DiaO = pr.DiaO
          GROUP BY pr.RutaId, pr.SKU

          UNION ALL

          SELECT FLOOR(SUM(DV.Pza) / ca.num_multiplo) AS Caja,
                 tr.cve_ruta                          AS Ruta,
                 cve_articulo,
                 ca.des_articulo
          FROM Venta V
                   INNER JOIN DetalleVet DV ON V.Documento = DV.Docto
                   INNER JOIN c_articulo ca ON DV.Articulo = ca.cve_articulo
                   INNER JOIN t_ruta tr ON V.RutaId = tr.ID_Ruta
          WHERE tr.cve_ruta IN ($rutasCve)
            AND V.Fecha BETWEEN '$fechaInicio' AND '$fechaFin'
            AND V.TipoVta != 'Obsequio'
            AND V.Cancelada = 0
          GROUP BY V.RutaId, DV.Articulo

          UNION ALL

          SELECT FLOOR(SUM(vdp.Pedidas) / ca.num_multiplo) AS Caja,
                 tr.cve_ruta                               AS Ruta,
                 cve_articulo,
                 ca.des_articulo
          FROM V_Cabecera_Pedido vcp
                   INNER JOIN V_Detalle_Pedido vdp ON vcp.Pedido = vdp.Pedido AND vdp.Cancelada = 0
                   INNER JOIN c_articulo ca ON vdp.Articulo = ca.cve_articulo
                   INNER JOIN t_ruta tr ON vcp.Ruta = tr.ID_Ruta
          WHERE tr.cve_ruta IN ($rutasCve)
            AND vcp.Fec_Pedido = '$fecha'
            AND vdp.Precio > 0
            AND vcp.Cancelada = 0
          GROUP BY vcp.Ruta, vdp.Articulo) AS Subconsulta
    GROUP BY Ruta, cve_articulo, des_articulo)
-- Consulta que obtiene los 3 artículos más vendidos por ruta
SELECT Ruta, cve_articulo, des_articulo, TotalCajas
FROM (SELECT Ruta,
             cve_articulo,
             des_articulo,
             TotalCajas,
             ROW_NUMBER() OVER (PARTITION BY Ruta ORDER BY TotalCajas DESC) AS Ranking
      FROM TotalCajasPorArticulo) AS SubconsultaFiltrada
WHERE Ranking <= 3
";
    if (!$result = mysqli_query($conn, $sql3masvendidos)) {
        echo json_encode(array('status' => 'error', 'data' => 'Error al obtener los datos'));
        exit;
    }
    $tresMasVendidos = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $tresMasVendidos[] = $row;
    }
    $rutas1 = array();
    if (count($cajas) > 0) {
        foreach ($cajas as $registro) {
            $numCajas = $registro['Cajas'] + $registro['CajasExtra'];
            $numPiezas = $registro['Piezas'];
            $rutaC = $registro['cve_ruta'];

            // Verificar si la ruta ya existe en el arreglo
            if (isset($rutas1[$rutaC])) {
                $rutas1[$rutaC]['Cajas'] += $numCajas;
                $rutas1[$rutaC]['Piezas'] += $numPiezas;
            } else {
                $rutas1[$rutaC] = array(
                    'Cajas'  => $numCajas,
                    'Piezas' => $numPiezas,
                );
            }
        }
    }
    $rutas2 = array();
    if (count($cajasPromocion) > 0) {
        foreach ($cajasPromocion as $registro) {
            $numCajas = $registro['TotalCajasPromocion'];
            $rutaC = $registro['cve_ruta'];

            if (isset($rutas2[$rutaC])) {
                $rutas2[$rutaC]['Cajas'] += $numCajas;
            } else {
                $rutas2[$rutaC] = array(
                    'Cajas' => $numCajas,
                );
            }
        }
    }


    $rutas3 = array();
    if (count($ventasPromocion) > 0) {
        foreach ($ventasPromocion as $registro) {
            $numCajas = $registro['Cajas'];
//            $numPiezas = $registro['Piezas'];
            $rutaC = $registro['cve_ruta'];

            // Verificar si la ruta ya existe en el arreglo
            if (isset($rutas3[$rutaC])) {
                $rutas3[$rutaC]['CajasVentaProm'] += $numCajas;
//                $rutas3[$rutaC]['PiezasVentaProm'] += $numPiezas;
            } else {
                $rutas3[$rutaC] = array(
                    'CajasVentaProm' => $numCajas,
                    //                    'PiezasVentaProm' => $numPiezas,
                );
            }
        }
    }
    foreach ($semaforo as &$rutaVet) {
        $rutaVet['Cajas'] = 0;
        $rutaVet['Piezas'] = 0;
        $rutaVet['CajasPromocion'] = 0;
        $rutaVet['3MasVendidos'] = '';
        $rutaVet['efectivo'] = 0;
        $rutaVet['credito'] = 0;
        $rutaVet['cobranza'] = 0;
        $rutaVet['descuentos'] = 0;
        foreach ($rutas1 as $rutaa => $valores) {
            if ($rutaVet['cve_ruta'] == $rutaa) {
                $rutaVet['Cajas'] = $valores['Cajas'];
                $rutaVet['Piezas'] = $valores['Piezas'];
            }

        }
        foreach ($rutas2 as $rutaa => $valores) {
            if ($rutaVet['cve_ruta'] == $rutaa) {
                $rutaVet['CajasPromocion'] = $valores['Cajas'];
            }
        }
        $p = 0;
        foreach ($tresMasVendidos as $registro) {
            if ($rutaVet['cve_ruta'] == $registro['Ruta']) {
                if ($p == 0) {
                    $rutaVet['3MasVendidos'] = $registro['des_articulo'];
                } else {
                    $rutaVet['3MasVendidos'] .= ', ' . $registro['des_articulo'];
                }
                $p++;
            }
        }
        foreach ($efectivoCreditoCobranza as $registro) {
            if ($rutaVet['cve_ruta'] === $registro['rutaName']) {
                if ($registro['Tipo'] == 'Contado' && $registro['metodoPago'] == 'Efectivo' and $registro['Cancelada'] == 0) {
                    $rutaVet['efectivo'] += $registro['Total'];
                } else if ($registro['Tipo'] == 'Credito' and $registro['Cancelada'] == 0) {
                    $rutaVet['credito'] += $registro['Total'];
                }
                if ($registro['Cancelada'] == 0) {
                    $rutaVet['cobranza'] += $registro['Abono'];
                    $rutaVet['descuentos'] += $registro['Descuento'];
                }
            }
        }
        $rutaVet['CajasVentaProm'] = 0;
        $rutaVet['PiezasVentaProm'] = 0;
        foreach ($rutas3 as $rutaa => $valores) {
            if ($rutaVet['cve_ruta'] == $rutaa) {
                $rutaVet['CajasVentaProm'] = $valores['CajasVentaProm'];
                $rutaVet['PiezasVentaProm'] = $valores['PiezasVentaProm'];
            }
        }

    }
    echo json_encode(array('status' => 'success', 'data' => $semaforo));
    exit();
} else {
    echo json_encode(array('status' => 'error', 'data' => 'Accion no encontrada'));
    exit;
}
?>
