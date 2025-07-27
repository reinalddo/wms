<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $almacen        = $_GET['almacen'];
    $ruta           = $_GET['ruta'];
    $diao           = $_GET['diao'];
    $fecha_inicio   = $_GET['fechaini'];
    $fecha_fin      = $_GET['fechafin'];
    $criterio       = $_GET['criterio'];
    //$diao = "";
    $sheet1 = $ruta;
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

<?php     

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    //if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$charset = mysqli_fetch_array($res_charset)['charset'];
    //mysqli_set_charset($conn , $charset);


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

    $SQLFecha1 = "";$SQLFecha2 = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
      $SQLFecha1 = " AND DATE(Venta.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(Venta.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      $SQLFecha2 = " AND DATE(RelOperaciones.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(RelOperaciones.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      if($fecha_inicio == $fecha_fin)
      {
            $SQLFecha1 = " AND DATE(Venta.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
            $SQLFecha2 = " AND DATE(RelOperaciones.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
      }


      }
      else if (!empty($fecha_inicio)) 
      {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $SQLFecha1 = " AND Venta.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
        $SQLFecha2 = " AND RelOperaciones.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

      }
      else if (!empty($fecha_fin)) 
      {
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $SQLFecha1 = " AND Venta.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        $SQLFecha2 = " AND RelOperaciones.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
      }

    $SQLDiaO1 = ""; $SQLDiaO2 = ""; 

    if($diao)
    {
        $SQLDiaO1 = " AND Venta.DiaO = '{$diao}' "; 
        $SQLDiaO2 = " AND RelOperaciones.DiaO = '{$diao}' ";
    }

    $sql = "
SELECT 
    ventas.Cliente, ventas.Responsable AS Responsable, 
    SUM(ventas.PromoCajas) AS PromoCajas, 
    SUM(ventas.PromoPiezas) AS PromoPiezas,
    ventas.num_multiplo,
    SUM(ventas.cajas) AS cajas, 
    SUM(ventas.piezas) AS piezas,
    SUM(ventas.obseq_cajas) AS obseq_cajas, 
    SUM(ventas.obseq_piezas) AS obseq_piezas,
    SUM(ventas.desc_cajas) AS desc_cajas, 
    SUM(ventas.desc_piezas) AS desc_piezas
FROM (
SELECT ventas1.Cliente, ventas1.Responsable AS Responsable, 
    #SUM(ventas1.cajas_total) AS cajas, 
    #SUM(ventas1.piezas_total) AS piezas, 
    SUM(ventas1.PromoC) AS PromoCajas, 
    SUM(ventas1.PromoP) AS PromoPiezas,
    ventas1.num_multiplo,
    #group_concat(distinct ventas1.num_multiplo) num_multiplo,
    ((SUM(ventas1.cajas_total))+TRUNCATE((SUM(ventas1.piezas_total)/ventas1.num_multiplo), 0)-SUM(ventas1.PromoC)) AS cajas, 
    (IF(ventas1.mav_cveunimed != 'XBX', (SUM(ventas1.piezas_total) - (ventas1.num_multiplo*TRUNCATE((SUM(ventas1.piezas_total)/ventas1.num_multiplo), 0))), IF(ventas1.num_multiplo = 1, SUM(ventas1.piezas_total), 0))-SUM(ventas1.PromoP)) AS piezas,
    ((SUM(ventas1.obseq_cajas))+TRUNCATE((SUM(ventas1.obseq_piezas)/ventas1.num_multiplo), 0)) AS obseq_cajas, 
    (IF(ventas1.mav_cveunimed != 'XBX', (SUM(ventas1.obseq_piezas) - (ventas1.num_multiplo*TRUNCATE((SUM(ventas1.obseq_piezas)/ventas1.num_multiplo), 0))), IF(ventas1.num_multiplo = 1, SUM(ventas1.obseq_piezas), 0))) AS obseq_piezas,
    ((SUM(ventas1.desc_cajas))+TRUNCATE((SUM(ventas1.desc_piezas)/ventas1.num_multiplo), 0)) AS desc_cajas, 
    (IF(ventas1.mav_cveunimed != 'XBX', (SUM(ventas1.desc_piezas) - (ventas1.num_multiplo*TRUNCATE((SUM(ventas1.desc_piezas)/ventas1.num_multiplo), 0))), IF(ventas1.num_multiplo = 1, SUM(ventas1.desc_piezas), 0))) AS desc_piezas
    FROM (
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
          um.mav_cveunimed,
          c_articulo.num_multiplo,
          (IF(DetalleVet.Tipo = 0, DetalleVet.Pza, IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, DetalleVet.Pza),TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0)))+IF(pr.Tipmed = 'Caja', (pr.Cant), 0)) AS cajas_total,
          (IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (DetalleVet.Pza - (c_articulo.num_multiplo*TRUNCATE((DetalleVet.Pza/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, DetalleVet.Pza, 0)))+IF(pr.Tipmed != 'Caja', (pr.Cant), 0)) AS piezas_total,
          IF(pr.Tipmed = 'Caja', (pr.Cant), 0) AS PromoC,
          IF(pr.Tipmed != 'Caja', (pr.Cant), 0) AS PromoP, 
          0 AS obseq_cajas,
          0 AS obseq_piezas,
          0 AS desc_cajas,
          0 AS desc_piezas
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
          WHERE 1 {$SQLRuta} {$SQLDiaO1} {$SQLFecha1} 
          AND Venta.Cancelada = 0 
          #GROUP BY Folio, cve_articulo

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
          um.mav_cveunimed,
          c_articulo.num_multiplo,
          IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0, IF(c_articulo.num_multiplo = 1, 0, td.Pedidas), IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0, TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0), 0)) AS cajas_total,
          IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0, (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) = 0, IF(c_articulo.num_multiplo = 1, td.Pedidas, 0), 0)) AS piezas_total,
          IF(pr.Tipmed = 'Caja', (pr.Cant), 0) AS PromoC,
          IF(pr.Tipmed != 'Caja', (pr.Cant), 0) AS PromoP, 
          0 AS obseq_cajas,
          0 AS obseq_piezas,
          IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0, IF(c_articulo.num_multiplo = 1, 0, td.Pedidas), IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0, TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0), 0)) AS desc_cajas,
          IF(um.mav_cveunimed != 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0, (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(um.mav_cveunimed = 'XBX' AND IFNULL(td.DescuentoSurtidas, 0) > 0, IF(c_articulo.num_multiplo = 1, td.Pedidas, 0), 0)) AS desc_piezas
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          INNER JOIN th_pedido p ON p.Fol_Folio = th.Pedido
          LEFT JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido OR RelOperaciones.Folio = th.Pedido 
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.Ruta) 
          #LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          #LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          #LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          #LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
          #LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN PRegalado pr ON td.Pedido = pr.Docto AND td.Articulo = pr.SKU AND th.Ruta = pr.RutaId AND td.IdEmpresa = pr.IdEmpresa AND th.Cod_Cliente = pr.Cliente
          #td.Pedido LIKE CONCAT('%',pr.Docto)
          WHERE 1 {$SQLRuta} {$SQLDiaO2} {$SQLFecha2} 
          AND th.Cancelada = 0 
          AND p.tipo_negociacion != 'Obsequio'
          #AND td.DescuentoPedidas = 0
          #GROUP BY Folio, cve_articulo


    UNION 

SELECT DISTINCT
          RelOperaciones.Fecha AS FechaBusq, 
          DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y') AS Fecha, RelOperaciones.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, c_destinatarios.id_destinatario AS CodCliente,
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, th.Fol_Folio AS Folio, th.TipoPedido AS Tipo, 
          #IFNULL(th.FormaPag, '') AS metodoPago,
          RelOperaciones.DiaO AS DiaO,
          um.des_umed AS unidadMedida,
          td.Precio_unitario AS Precio,
          td.Precio_unitario AS Importe, td.IVA AS IVA, td.Precio_unitario AS Descuento, 
          #t_vendedores.Nombre AS Vendedor, 
          IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta') AS Operacion,
          IF(th.Activo = 1, 'No', 'Si') AS Cancelada,
          c_articulo.cve_articulo AS cve_articulo,
          c_articulo.des_articulo AS Articulo, 
          um.mav_cveunimed,
          c_articulo.num_multiplo,
          0 AS cajas_total,
          0 AS piezas_total,
          0 AS PromoC,
          0 AS PromoP,
          IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Num_Cantidad),TRUNCATE((td.Num_Cantidad/c_articulo.num_multiplo), 0)) AS obseq_cajas,
          IF(um.mav_cveunimed != 'XBX', (td.Num_Cantidad - (c_articulo.num_multiplo*TRUNCATE((td.Num_Cantidad/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Num_Cantidad, 0)) AS obseq_piezas,
          0 AS desc_cajas,
          0 AS desc_piezas
          FROM th_pedido th
          LEFT JOIN td_pedido td ON td.Fol_Folio = th.Fol_Folio
          LEFT JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Fol_Folio OR RelOperaciones.Folio = th.Fol_Folio 
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th.ruta) 
          #LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          #LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Cve_Articulo
          INNER JOIN c_almacenp ON c_almacenp.id = th.cve_almac
          #LEFT JOIN Cobranza ON Cobranza.Documento = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          #LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO
          #LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          #LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=th.Cve_Clte
          LEFT JOIN c_destinatarios ON c_destinatarios.Cve_Clte=th.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN PRegalado pr ON td.Fol_Folio = pr.Docto AND td.Cve_Articulo = pr.SKU AND th.ruta = pr.RutaId AND c_almacenp.clave = pr.IdEmpresa AND c_destinatarios.id_destinatario = pr.Cliente
          #td.Pedido LIKE CONCAT('%',pr.Docto)
          WHERE 1 
          AND th.tipo_negociacion = 'Obsequio' 
          AND th.Activo = 1 {$SQLRuta} {$SQLDiaO2} {$SQLFecha2} 
          #GROUP BY Folio, cve_articulo

          ) AS ventas1 WHERE 1 AND ventas1.Responsable IS NOT NULL
            GROUP BY Cliente, num_multiplo

          ) AS ventas WHERE 1 AND ventas.Responsable IS NOT NULL
            GROUP BY Cliente
            ORDER BY Responsable
            ";

    $sql2 = $sql;
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }


    $clientes_con_promocion = 0; $clientes_sin_promocion = 0;
    $cajas_con_promocion = 0; $cajas_sin_promocion = 0;

    $clientes_con_obsequio = 0; 
    $cajas_con_obsequio = 0; 

    $clientes_con_descuento = 0; 
    $cajas_con_descuento = 0; 
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        if(($PromoCajas+$PromoPiezas) == 0)
        {
            if(($PromoCajas+$PromoPiezas+$obseq_cajas+$obseq_piezas) == 0)
            $clientes_sin_promocion++;
            if(($PromoCajas+$PromoPiezas+$obseq_cajas+$obseq_piezas) == 0)
            $cajas_sin_promocion += $cajas;
            //$cajas = 0;
        }
        else 
        {
            if(($PromoCajas+$PromoPiezas+$obseq_cajas+$obseq_piezas) > 0) $clientes_con_promocion++;
            if(($PromoCajas+$PromoPiezas+$obseq_cajas+$obseq_piezas) > 0) $cajas_con_promocion += $cajas;
            //$cajas = 0;
        }

        if($obseq_cajas > 0)
        {
            $clientes_con_obsequio++;
            $cajas_con_obsequio += $cajas;
            $obseq_cajas = 0;
            $obseq_piezas = 0;
        }

        if($desc_cajas > 0)
        {
            $clientes_con_descuento++;
            $cajas_con_descuento += $desc_cajas;
            $desc_cajas = 0;
        }
        $cajas = 0;
        //$cajas;$piezas;$PromoCajas;$PromoPiezas;


    }

?>
    <div cell="A3" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="B3" ><?php echo $ruta; ?></div>

    <div cell="A4" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha</div>
    <div cell="B4" ><?php 
        $sql_fecha = "SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') as Fecha_Actual FROM DUAL";
        if (!($res_fecha = mysqli_query($conn, $sql_fecha))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }
        $fecha_actual = mysqli_fetch_array($res_fecha)["Fecha_Actual"];

        if(($fecha_inicio == $fecha_fin && $fecha_inicio != "") || $fecha_inicio == $fecha_actual) echo $fecha_inicio;
        else if($fecha_inicio != '' && $fecha_fin != '') echo $fecha_inicio." al ". $fecha_fin;
        else if($fecha_inicio != '' && $fecha_fin == '') echo "Desde ".$fecha_inicio. " Hasta ".$fecha_actual;
        else if($fecha_inicio == '' && $fecha_fin != '') echo "Hasta ".$fecha_fin;
        else echo "Todas";
     ?></div>


    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>CLIENTES</div>
    <div cell="E2" ><?php echo $clientes_con_promocion; ?></div>
    <div cell="E3" ><?php echo $clientes_sin_promocion; ?></div>
    <div cell="E4" ><?php echo $clientes_con_obsequio; ?></div>
    <div cell="E5" ><?php echo $clientes_con_descuento; ?></div>
    <div cell="D6" excelStyle='<?php echo json_encode($styleArray); ?>'>TOTAL CLIENTES</div>
    <div cell="E6" ><?php echo ($clientes_con_promocion+$clientes_sin_promocion+$clientes_con_obsequio); ?></div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>CAJAS</div>
    <div cell="G2" ><?php echo $cajas_con_promocion; ?></div>
    <div cell="G3" ><?php echo $cajas_sin_promocion; ?></div>
    <div cell="G4" ><?php echo $cajas_con_obsequio; ?></div>
    <div cell="G5" ><?php echo $cajas_con_descuento; ?></div>
    <div cell="H6" excelStyle='<?php echo json_encode($styleArray); ?>'>TOTAL CAJAS</div>
    <div cell="G6"><?php echo ($cajas_sin_promocion+$cajas_con_promocion+$cajas_con_obsequio/*+$cajas_con_descuento*/); ?></div>

    <div cell="F2" excelStyle='<?php echo json_encode($styleArray); ?>'>CON PROMOCION</div>
    <div cell="F3" excelStyle='<?php echo json_encode($styleArray); ?>'>SIN PROMOCION</div>
    <div cell="F4" excelStyle='<?php echo json_encode($styleArray); ?>'>CON OBSEQUIO</div>
    <div cell="F5" excelStyle='<?php echo json_encode($styleArray); ?>'>CON DESCUENTO</div>



    <div cell="A8" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Clte</div>
    <div cell="B8" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="C8" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="D8" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="E8" excelStyle='<?php echo json_encode($styleArray); ?>'>Promo Cajas</div>
    <div cell="F8" excelStyle='<?php echo json_encode($styleArray); ?>'>Promo Piezas</div>
    <div cell="G8" excelStyle='<?php echo json_encode($styleArray); ?>'>Obseq Cajas</div>
    <div cell="H8" excelStyle='<?php echo json_encode($styleArray); ?>'>Obseq Piezas</div>
    <div cell="I8" excelStyle='<?php echo json_encode($styleArray); ?>'>Descuento Cajas</div>
    <div cell="J8" excelStyle='<?php echo json_encode($styleArray); ?>'>Descuento Piezas</div>

<?php
    $i = 9;

    if (!($res2 = mysqli_query($conn, $sql2))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $total_cajas = 0;
    $total_piezas = 0;
    $total_promoc = 0;
    $total_promop = 0;
    $total_obseqc = 0;
    $total_obseqp = 0;
    $total_descc = 0;
    $total_descp = 0;


    while ($row2 = mysqli_fetch_array($res2)) {
        extract($row2);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Responsable; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo ($cajas); ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo ($piezas); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo ($PromoCajas); ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo ($PromoPiezas); ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo ($obseq_cajas); ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo ($obseq_piezas); ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo ($desc_cajas); ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo ($desc_piezas); ?></div>
        <?php 
        $total_cajas += ($cajas);
        $total_piezas += ($piezas);
        $total_promoc += ($PromoCajas);
        $total_promop += ($PromoPiezas);
        $total_obseqc += ($obseq_cajas);
        $total_obseqp += ($obseq_piezas);
        $total_descc += ($desc_cajas);
        $total_descp += ($desc_piezas);
        $i++;
    }
  ?>

    <div cell="B<?php echo $i; ?>" excelStyle='<?php echo json_encode($styleArray); ?>'>Total General</div>
    <div cell="C<?php echo $i; ?>"><?php echo $total_cajas; ?></div>
    <div cell="D<?php echo $i; ?>"><?php echo $total_piezas; ?></div>
    <div cell="E<?php echo $i; ?>"><?php echo $total_promoc; ?></div>
    <div cell="F<?php echo $i; ?>"><?php echo $total_promop; ?></div>
    <div cell="G<?php echo $i; ?>"><?php echo $total_obseqc; ?></div>
    <div cell="H<?php echo $i; ?>"><?php echo $total_obseqp; ?></div>
    <div cell="I<?php echo $i; ?>"><?php echo $total_descc; ?></div>
    <div cell="J<?php echo $i; ?>"><?php echo $total_descp; ?></div>

<?php if($criterio == 'SQL0000WMS'){ ?> <div cell="B<?php echo $i+3; ?>"><?php echo $sql; ?></div> <?php } ?>

    </div>

    


