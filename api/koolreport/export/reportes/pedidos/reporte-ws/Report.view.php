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
<title>Reporte Consolidado</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $folio = $_GET['id'];
    $folios_ws = $_GET['folio_ws'];
    $ruta_pedido = $_GET['ruta_pedido'];

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(1): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    ?>
    <div class="row">
        <div class="col-4 text-center">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>

<?php 
    $sql = "SELECT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '{$folio}';";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $sufijo = 0;

    while($row = mysqli_fetch_array($res))
    {
        $folio_arr = explode('-', $row['Fol_Folio']);
        $sufijo = $folio_arr[count($folio_arr)];
        if($sufijo != 0)
        {
            break;
        }
    }


    $sql = "SELECT cve_usuario, DATE_FORMAT(Fec_Pedido, '%d-%m-%Y') as fecha_pedido FROM th_pedido WHERE Fol_folio = '{$folio}'";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(3): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);
?>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">Reporte Consolidado <?php echo $folio; ?><br>(<?php echo $folios_ws; ?>)</span></h1>
                    <p style="font-size: 18px;">
                        Usuario: <span><?php echo $cve_usuario; ?></span><br>
                        Ruta: <span><?php echo $ruta_pedido; ?></span><br>
                        Fecha: <span><?php echo $fecha_pedido; ?></span>
                    </p>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>
      <th scope="col">Clave</th>
      <th scope="col">Artículo</th>
      <th scope="col">Cajas</th>
      <th scope="col">Piezas</th>
      <th scope="col">PrCJ</th>
      <th scope="col">PrPz</th>
      <th scope="col">Total Cajas</th>
      <th scope="col">Total Piezas</th>
    </tr>
  </thead>
  <tbody>
  <?php 


    $tot_cajas = 0; $tot_piezas = 0; $tot_prcj = 0; $tot_prpz = 0; $tot_total_cajas_promo = 0; $tot_total_piezas_promo = 0;

    $sql = "
        SELECT DISTINCT
                ep.Cve_articulo, ep.des_articulo, SUM(ep.total_cajas) AS total_cajas, 
                SUM(IFNULL((ep.total_piezas%ep.total_cajas), IF(ep.promocion_cajas = '', ep.Num_cantidad, ''))) AS total_piezas, 
                ep.ruta, 
               (SUM(ep.total_cajas)+SUM(ep.promocion_cajas)) AS total_cajas_promo,
               (SUM(IFNULL((ep.total_piezas%ep.total_cajas), IF(ep.promocion_cajas = '', ep.Num_cantidad, '')))+SUM(ep.promocion_piezas)) AS total_piezas_promo, 
               ep.promocion_piezas, ep.promocion_cajas
        FROM (
            SELECT DISTINCT
            IFNULL(CONCAT('(', d.id_destinatario,') ' , d.razonsocial), '') AS cliente,
            IFNULL(CONCAT(d.ciudad, ', ', d.colonia, ', ', d.direccion), '') AS direccion,
            td.Cve_articulo,
            a.des_articulo,
            td.Num_cantidad,

            IFNULL(((SELECT DISTINCT IFNULL(SUM(TRUNCATE(tdp.Num_cantidad/a.num_multiplo, 0)), 0)
            FROM td_pedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = th.Fol_folio AND um.mav_cveunimed != 'XBX' AND tdp.cve_articulo = a.cve_articulo)
            +
            (SELECT DISTINCT IFNULL(SUM(tdc.Num_cantidad), 0)
            FROM td_pedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = th.Fol_folio AND um.mav_cveunimed = 'XBX' AND tdc.cve_articulo = a.cve_articulo)), 0) AS total_cajas,

            IFNULL(((SELECT IFNULL(SUM(tdp.Num_cantidad), 0)
            FROM td_pedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = th.Fol_folio AND um.mav_cveunimed != 'XBX')
            +
            (SELECT IFNULL(SUM(tdc.Num_cantidad*a.num_multiplo), 0)
            FROM td_pedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = th.Fol_folio AND um.mav_cveunimed = 'XBX')), 0) AS total_piezas,
            IFNULL(t_ruta.cve_ruta, 0) AS ruta, 
            0 AS promocion_cajas,
            0 AS promocion_piezas
          FROM th_pedido th 
                LEFT JOIN td_pedido td ON td.Fol_folio = th.Fol_folio
                LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = th.Fol_folio 
                LEFT JOIN c_destinatarios d ON d.id_destinatario = rel.Id_Destinatario
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta AND t_ruta.cve_ruta = th.cve_ubicacion AND th.STATUS = 'T'
                LEFT JOIN th_cajamixta tc ON tc.fol_folio = th.Fol_folio
                LEFT JOIN PRegalado prc ON th.Fol_folio = prc.Docto AND prc.Tipmed = 'Caja'
                LEFT JOIN PRegalado prp ON th.Fol_folio = prp.Docto AND prp.Tipmed != 'Caja'
          WHERE th.Fol_folio IN (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '{$folio}')
          GROUP BY td.Cve_articulo

    UNION
    

            SELECT DISTINCT
            IFNULL(CONCAT('(', d.id_destinatario,') ' , d.razonsocial), '') AS cliente,
            IFNULL(CONCAT(d.ciudad, ', ', d.colonia, ', ', d.direccion), '') AS direccion,
            IFNULL(prc.SKU, prc.SKU) AS Cve_articulo,
            a.des_articulo AS des_articulo,
            IFNULL(prc.Cant, prp.Cant) AS Num_cantidad,

            0 AS total_cajas,

            0 AS total_piezas,
            '' AS ruta, 
            IFNULL(SUM(prc.Cant), 0) AS promocion_cajas,
            IFNULL(SUM(prp.Cant), 0) AS promocion_piezas
          FROM c_articulo a
                LEFT JOIN PRegalado prc ON a.cve_articulo = prc.SKU AND prc.Tipmed = 'Caja'
                LEFT JOIN PRegalado prp ON a.cve_articulo = prp.SKU AND prp.Tipmed != 'Caja'
                LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = CONCAT(IFNULL(prc.RutaId, prp.RutaId), '_', IFNULL(prc.Docto, prp.Docto))
                LEFT JOIN c_destinatarios d ON d.id_destinatario = rel.Id_Destinatario
          WHERE CONCAT(IFNULL(prc.RutaId, prp.RutaId), '_', IFNULL(prc.Docto, prp.Docto)) IN (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '{$folio}')
     GROUP BY Cve_articulo
    ) AS ep GROUP BY ep.Cve_articulo";

    if($sufijo > 0)
    $sql = "
        SELECT DISTINCT
                ep.Cve_articulo, ep.des_articulo, SUM(ep.total_cajas) AS total_cajas, 
                SUM(IFNULL((ep.total_piezas%ep.total_cajas), IF(ep.promocion_cajas = '', ep.Num_cantidad, ''))) AS total_piezas, 
                ep.ruta, 
               (SUM(ep.total_cajas)+SUM(ep.promocion_cajas)) AS total_cajas_promo,
               (SUM(IFNULL((ep.total_piezas%ep.total_cajas), IF(ep.promocion_cajas = '', ep.Num_cantidad, '')))+SUM(ep.promocion_piezas)) AS total_piezas_promo, 
               ep.promocion_piezas, ep.promocion_cajas
        FROM (
            SELECT DISTINCT
            IFNULL(CONCAT('(', d.id_destinatario,') ' , d.razonsocial), '') AS cliente,
            IFNULL(CONCAT(d.ciudad, ', ', d.colonia, ', ', d.direccion), '') AS direccion,
            td.Cve_articulo,
            a.des_articulo,
            td.Num_cantidad,

            IFNULL(((SELECT DISTINCT IFNULL(SUM(TRUNCATE(tdp.Num_cantidad/a.num_multiplo, 0)), 0)
            FROM td_subpedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = th.Fol_folio AND um.mav_cveunimed != 'XBX' AND tdp.cve_articulo = a.cve_articulo AND tdp.Sufijo = $sufijo)
            +
            (SELECT DISTINCT IFNULL(SUM(tdc.Num_cantidad), 0)
            FROM td_subpedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = th.Fol_folio AND um.mav_cveunimed = 'XBX' AND tdc.cve_articulo = a.cve_articulo AND tdc.Sufijo = $sufijo)), 0) AS total_cajas,

            IFNULL(((SELECT IFNULL(SUM(tdp.Num_cantidad), 0)
            FROM td_subpedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = th.Fol_folio AND um.mav_cveunimed != 'XBX' AND tdp.Sufijo = $sufijo)
            +
            (SELECT IFNULL(SUM(tdc.Num_cantidad*a.num_multiplo), 0)
            FROM td_subpedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = th.Fol_folio AND um.mav_cveunimed = 'XBX' AND tdc.Sufijo = $sufijo)), 0) AS total_piezas,
            IFNULL(t_ruta.cve_ruta, 0) AS ruta, 
            0 AS promocion_cajas,
            0 AS promocion_piezas
          FROM th_pedido th 
                LEFT JOIN td_subpedido td ON td.Fol_folio = th.Fol_folio AND td.Sufijo = $sufijo
                LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = th.Fol_folio 
                LEFT JOIN c_destinatarios d ON d.id_destinatario = rel.Id_Destinatario
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta AND t_ruta.cve_ruta = th.cve_ubicacion AND th.STATUS = 'T'
                LEFT JOIN th_cajamixta tc ON tc.fol_folio = th.Fol_folio
                LEFT JOIN PRegalado prc ON th.Fol_folio = prc.Docto AND prc.Tipmed = 'Caja'
                LEFT JOIN PRegalado prp ON th.Fol_folio = prp.Docto AND prp.Tipmed != 'Caja'
          WHERE CONCAT(th.Fol_folio, '-', td.Sufijo) IN (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '{$folio}') AND td.Sufijo = $sufijo
          GROUP BY td.Cve_articulo

    UNION
    

            SELECT DISTINCT
            IFNULL(CONCAT('(', d.id_destinatario,') ' , d.razonsocial), '') AS cliente,
            IFNULL(CONCAT(d.ciudad, ', ', d.colonia, ', ', d.direccion), '') AS direccion,
            IFNULL(prc.SKU, prc.SKU) AS Cve_articulo,
            a.des_articulo AS des_articulo,
            IFNULL(prc.Cant, prp.Cant) AS Num_cantidad,

            0 AS total_cajas,

            0 AS total_piezas,
            '' AS ruta, 
            IFNULL(SUM(prc.Cant), 0) AS promocion_cajas,
            IFNULL(SUM(prp.Cant), 0) AS promocion_piezas
          FROM c_articulo a
                LEFT JOIN PRegalado prc ON a.cve_articulo = prc.SKU AND prc.Tipmed = 'Caja'
                LEFT JOIN PRegalado prp ON a.cve_articulo = prp.SKU AND prp.Tipmed != 'Caja'
                LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = CONCAT(IFNULL(prc.RutaId, prp.RutaId), '_', IFNULL(prc.Docto, prp.Docto))
                LEFT JOIN c_destinatarios d ON d.id_destinatario = rel.Id_Destinatario
          WHERE CONCAT(IFNULL(prc.RutaId, prp.RutaId), '_', IFNULL(prc.Docto, prp.Docto)) IN (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '{$folio}')
     GROUP BY Cve_articulo
    ) AS ep GROUP BY ep.Cve_articulo";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ".$sql;
    }

    $tot_cajas = 0; $tot_piezas = 0; $tot_prcj = 0; $tot_prpz = 0; $tot_total_cajas_promo = 0; $tot_total_piezas_promo = 0;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
      <td><?php echo $Cve_articulo; ?></td>
      <td><?php echo $des_articulo; ?></td>
      <td align="center"><?php echo $total_cajas; ?></td>
      <td align="center"><?php echo $total_piezas; ?></td>
      <td align="center"><?php if(!$promocion_cajas) echo 0; else echo $promocion_cajas; ?></td>
      <td align="center"><?php if(!$promocion_piezas) echo 0; else echo $promocion_piezas; ?></td>
      <td align="center"><?php echo $total_cajas_promo; ?></td>
      <td align="center"><?php echo $total_piezas_promo; ?></td>


    </tr>
    <?php 
    $tot_cajas += $total_cajas;
    $tot_piezas += $total_piezas;
    $tot_prcj += $promocion_cajas;
    $tot_prpz += $promocion_piezas;
    $tot_total_cajas_promo += $total_cajas_promo;
    $tot_total_piezas_promo += $total_piezas_promo;
    }
    ?>
    <tr>
        <td></td>
        <td align="right"><b>Total:</b></td>
        <td align="center"><?php echo $tot_cajas; ?></td>
        <td align="center"><?php echo $tot_piezas; ?></td>
        <td align="center"><?php echo $tot_prcj; ?></td>
        <td align="center"><?php echo $tot_prpz; ?></td>
        <td align="center"><?php echo $tot_total_cajas_promo; ?></td>
        <td align="center"><?php echo $tot_total_piezas_promo; ?></td>
    </tr>
  </tbody>
</table>
</div>

</div>
</body>
</html>

