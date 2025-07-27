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
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Reporte de Producto Surtido</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $status = $_GET['status'];

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">
                        <?php if($status == 'S') echo "Lista de Surtido";else echo "Reporte de Producto Surtido"; ?>
                        </span></h1>
                    <p class="lead">
                        Pedido: <span lang="th"><?php echo $_GET['folio']; ?></span>
                    </p>
                    <p class="lead">
                        Fecha Pedido: <span lang="th"><?php echo $_GET['fecha_pedido']; ?></span>
                    </p>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>
      <th scope="col">LP</th>
      <th scope="col">Clave</th>
      <th scope="col">Descripción</th>
      <th scope="col">Lote</th>
      <th scope="col">Caducidad</th>
      <th scope="col" width="150" align="center">BL</th>
      <th scope="col">Cantidad Solicitada</th>
      <th scope="col">Cantidad Surtida</th>
      <th scope="col">Usuario</th>
    </tr>
  </thead>
  <tbody>
  <?php 

    $folio  = $_GET['folio'];
    $sufijo = $_GET['sufijo'];
/*
    $sql = "SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo) AS cr WHERE cr.con_recorrido != -1";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $con_recorrido = mysqli_fetch_array($res)['con_recorrido'];
*/
    $sql = "SELECT COUNT(*) as con_recorrido FROM t_registro_surtido WHERE fol_folio = '$folio' AND Sufijo = '$sufijo'";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $con_recorrido = mysqli_fetch_array($res)['con_recorrido'];


    $sql = "";
    
    //if($status != 'S')
    //if($con_recorrido == 0)
        $sql = "
            SELECT DISTINCT IFNULL(ch.CveLP, '') AS LP, IFNULL(tds.Cve_articulo, ts.cve_articulo) AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                            IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                            #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                            u.CodigoCSD AS BL, 
                            TRUNCATE(tds.Num_cantidad, 3) AS Cantidad_Solicitada, 
                            #IF(th.TipoPedido != 'T' AND th.TipoPedido != 'R' AND th.TipoPedido != 'RI', IFNULL((TRUNCATE(tc.ajuste, 3)), 0), IFNULL((TRUNCATE(tc.cantidad, 3)), 0)) AS Cantidad_Surtida, 
                            IFNULL((TRUNCATE(IF(IFNULL(tc.cantidad, 0) = 0, tc.ajuste, tc.cantidad), 3)), 0) AS Cantidad_Surtida, 
                            c.nombre_completo AS Usuario
            FROM td_surtidopiezas ts
            #LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo
            LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo AND ts.cve_articulo = tds.Cve_articulo AND ts.LOTE = tds.Cve_Lote
            LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
            LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
            LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
            LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN c_serie S ON S.cve_articulo = ts.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%' AND tc.cve_almac = ths.cve_almac AND tc.id_TipoMovimiento = 8
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
            LEFT JOIN t_MovCharolas kc ON kc.id_kardex = tc.id AND kc.Id_TipoMovimiento = 8
            LEFT JOIN c_charolas ch ON ch.IDContenedor = kc.ID_Contenedor
            LEFT JOIN th_pedido th ON th.Fol_folio = ts.fol_folio
            WHERE ts.fol_folio = '{$folio}' 
            #AND tds.Cve_articulo = ts.Cve_articulo 
            AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(ts.LOTE, '') #AND IFNULL(ts.LOTE, '') = IFNULL(tds.Cve_Lote, '') 
            AND tc.destino = ts.fol_folio AND u.AreaProduccion = 'N'
            GROUP BY LP, Clave, Lote, BL
            #GROUP BY Clave, Lote, Usuario
        ";


    if($status == 'S')
    {
    if($con_recorrido == 0)
    {
        /*
        $sql = "
            SELECT DISTINCT IFNULL(ch.CveLP, '') AS LP, tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                            IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                            #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                            u.CodigoCSD AS BL, 
                            TRUNCATE(tds.Num_cantidad, 3) AS Cantidad_Solicitada, SUM(TRUNCATE(tc.Cantidad, 3)) AS Cantidad_Surtida, 
                            c.nombre_completo AS Usuario
            FROM td_surtidopiezas ts
            #LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo
            LEFT JOIN td_subpedido tds ON tds.fol_folio = ts.Fol_folio AND tds.Sufijo = ts.Sufijo AND ts.cve_articulo = tds.Cve_articulo AND ts.LOTE = tds.Cve_Lote
            LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
            LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
            LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
            LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN c_serie S ON S.cve_articulo = ts.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(ts.LOTE, '')
            LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%' AND tc.cve_almac = ths.cve_almac AND tc.id_TipoMovimiento = 8
            LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
            LEFT JOIN t_MovCharolas kc ON kc.id_kardex = tc.id AND kc.Id_TipoMovimiento = 8
            LEFT JOIN c_charolas ch ON ch.IDContenedor = kc.ID_Contenedor
            WHERE ts.fol_folio = '{$folio}' AND tds.Cve_articulo = ts.Cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(ts.LOTE, '') #AND IFNULL(ts.LOTE, '') = IFNULL(tds.Cve_Lote, '') 
            AND tc.destino = ts.fol_folio AND u.AreaProduccion = 'N'
            GROUP BY LP, Clave, Lote, BL
            #GROUP BY Clave, Lote, Usuario
        ";
        */
        $sql = "
SELECT * FROM (
                    SELECT 
                          tsb.Fol_folio AS folio,
                          tsb.Sufijo AS sufijo,
                          c.RazonSocial AS cliente,
                          tsb.Cve_articulo AS Clave,
                          a.des_articulo AS Descripcion,
                          (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = $sufijo) AS Usuario,
                          (SELECT CodigoCSD FROM c_ubicacion WHERE idy_ubica = t.cve_ubicacion) AS ubicacion,
                          t.cve_ubicacion AS idy_ubica,
                          '' AS secuencia,
                          IFNULL(a.control_peso, 'N') AS control_peso,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                            u.CodigoCSD as BL,
                            IFNULL(ch.IDContenedor, '') AS LPNtarima,
                            #IFNULL(IFNULL(ch.CveLP, tarima.CveLP), '') AS LP,
                            IFNULL(ch.CveLP, '') AS LP,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, #IFNULL(t.cve_usuario, t_sin_lote.cve_usuario)
                          (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = $sufijo) AND idy_ubica = t.cve_ubicacion AND Cve_Almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = t.cve_ubicacion)) AS ruta,
                          IF(t.cve_lote != '', IF(a.control_lotes = 'S',  t.cve_lote, ''), '') AS Lote,
                          IF(a.Caduca = 'S', IF(t.cve_lote != '', IF(((SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = t.cve_lote AND tsb.Cve_articulo = cve_articulo) != DATE_FORMAT('00-00-0000', '%d-%m-%Y')) AND (a.control_lotes = 'S'),  (SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = t.cve_lote AND tsb.Cve_articulo = cve_articulo), '' ), ''), '') AS Caducidad,
                          IF(t.cve_lote != '', IF(a.control_numero_series = 'S',  t.cve_lote, ''), '') AS serie,
                          IF((op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0), IF(('OT' LIKE (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = tsb.fol_folio LIMIT 1)), SUM(ROUND((a.peso * tsb.Num_cantidad),4)), tsb.Num_cantidad), IFNULL(a.peso, 0) * tsb.Num_cantidad) AS peso,
                          SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                          IF(a.control_peso = 'S', TRUNCATE(tsb.Num_cantidad, 4), TRUNCATE(tsb.Num_cantidad,0) ) AS Cantidad_Solicitada,
                          IFNULL(s.Cantidad, 0) AS Cantidad_Surtida,
                          umed.mav_cveunimed AS id_medida,
                          umed.des_umed AS unidad_medida,
                          IFNULL(t.Existencia, tsb.Num_cantidad) AS existencia, 
                          '' as proyecto
                    FROM td_subpedido tsb
                      LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                      LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                      LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                      LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                      LEFT JOIN V_ExistenciaGral t ON t.cve_articulo = tsb.Cve_articulo AND t.cve_lote = tsb.cve_lote AND tsb.Sufijo = $sufijo AND t.Cve_Almac = th_pedido.cve_almac AND t.tipo = 'ubicacion'
                      LEFT JOIN c_ubicacion u ON u.idy_ubica = t.cve_ubicacion
                      LEFT JOIN c_charolas ch ON ch.clave_contenedor = t.Cve_Contenedor
                      LEFT JOIN t_ordenprod op ON op.Folio_Pro = tsb.fol_folio
                    WHERE tsb.Fol_folio = '{$folio}' AND tsb.Sufijo = $sufijo AND tsb.Cve_articulo = t.cve_articulo  
                    #AND th_pedido.Cve_Almac = X.cve_almac
                    #AND th_pedido.Cve_Almac in (t_sin_lote.Cve_Almac, t.Cve_Almac)
                    GROUP BY ubicacion, serie, Lote, existencia, Clave
                    ORDER BY Clave
            ) AS sin_recorrido
            ";
    }
    else
        $sql = "SELECT DISTINCT tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(tds.cve_lote, '') AS Lote, 
                        IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                        u.CodigoCSD AS BL, 
                        IFNULL(ch.CveLP, '') AS LP,
                        TRUNCATE(tds.Cantidad, 3) AS Cantidad_Solicitada, 
                        IFNULL(ts.Cantidad, 0) AS Cantidad_Surtida, 
                        c.nombre_completo AS Usuario
        FROM t_registro_surtido tds
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tds.ClaveEtiqueta
        LEFT JOIN th_subpedido ths ON ths.fol_folio = tds.fol_folio AND ths.Sufijo = tds.Sufijo
        LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
        LEFT JOIN c_articulo a ON a.cve_articulo = tds.Cve_articulo
        LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = tds.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tds.cve_lote, '') AND tds.fol_folio = ts.fol_folio
        LEFT JOIN c_lotes L ON L.cve_articulo = tds.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN c_serie S ON S.cve_articulo = tds.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN c_ubicacion u ON u.idy_ubica = tds.idy_ubica
        WHERE tds.fol_folio = '{$folio}' AND tds.Sufijo = '$sufijo' AND tds.Cve_articulo = a.cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(tds.cve_lote, '') #AND IFNULL(tds.cve_lote, '') = IFNULL(tds.Cve_Lote, '') 
        AND u.AreaProduccion = 'N';";
        }

/*
    else
        $sql = "SELECT DISTINCT tds.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(tds.cve_lote, '') AS Lote, 
                        IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        #GROUP_CONCAT(DISTINCT u.CodigoCSD) AS BL, 
                        u.CodigoCSD AS BL, 
                        IFNULL(ch.CveLP, '') AS LP,
                        #TRUNCATE(tds.Cantidad, 3) AS Cantidad_Solicitada, 
                        TRUNCATE(tsb.Num_Cantidad, 3) AS Cantidad_Solicitada, 
                        #IFNULL(ts.Cantidad, 0) AS Cantidad_Surtida, 
                        IFNULL(tds.Cantidad, 0) AS Cantidad_Surtida, 
                        c.nombre_completo AS Usuario
        FROM t_registro_surtido tds
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tds.ClaveEtiqueta
        LEFT JOIN th_subpedido ths ON ths.fol_folio = tds.fol_folio AND ths.Sufijo = tds.Sufijo
        LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
        LEFT JOIN c_articulo a ON a.cve_articulo = tds.Cve_articulo
        LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = tds.Cve_articulo AND tds.fol_folio = ts.fol_folio #AND IFNULL(ts.LOTE, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN td_subpedido tsb ON tsb.fol_folio = ts.fol_folio AND tsb.Cve_articulo = ts.Cve_articulo AND IFNULL(tsb.Cve_Lote, '') = IFNULL(ts.LOTE, '')
        LEFT JOIN c_lotes L ON L.cve_articulo = tds.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN c_serie S ON S.cve_articulo = tds.Cve_articulo AND IFNULL(S.numero_serie, '') = IFNULL(tds.cve_lote, '')
        LEFT JOIN c_ubicacion u ON u.idy_ubica = tds.idy_ubica
        WHERE tds.fol_folio = '{$folio}' AND tds.Sufijo = '$sufijo' AND tds.Cve_articulo = a.cve_articulo AND IFNULL(IFNULL(L.Lote, S.numero_serie), '') = IFNULL(tds.cve_lote, '') #AND IFNULL(tds.cve_lote, '') = IFNULL(tds.Cve_Lote, '') 
        AND u.AreaProduccion = 'N';";
*/
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
      <th scope="row"><?php echo $LP; ?></th>
      <th scope="row"><?php echo $Clave; ?></th>
      <td><?php echo $Descripcion; ?></td>
      <td><?php echo $Lote; ?></td>
      <td><?php echo $Caducidad; ?></td>
      <td width="150" align="center"><?php echo $BL; ?></td>
      <td align="right"><?php echo $Cantidad_Solicitada; ?></td>
      <td align="right"><?php echo $Cantidad_Surtida; ?></td>
      <td><?php echo $Usuario; ?></td>
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

