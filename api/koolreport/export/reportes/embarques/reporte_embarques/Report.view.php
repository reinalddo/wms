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
<title>Entrega Programada</title>
</head>
<body style="margin: 30px 0;">
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $folio = $_GET['id'];

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

<?php 
    $sql = "SELECT cve_usuario FROM th_ordenembarque WHERE ID_OEmbarque = {$folio}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);
?>
        <div class="col">
                <div class="text-center">
                  
                    <h1><span lang="th">Entrega Programada #<?php echo $folio; ?></span></h1>
                    <p style="font-size: 18px;">
                        Usuario: <span><?php echo $cve_usuario; ?></span>
                    </p>
                </div>
        </div>
    </div>

<div style="padding: 10px 100px;">
<table class="table">
  <thead>
    <tr>
      <th scope="col">Stop</th>
      <th scope="col">Ruta</th>
      <th scope="col">Pedido</th>
      <th scope="col">Cliente</th>
      <th scope="col">Dirección</th>
      <th scope="col">Cajas</th>
      <th scope="col">Piezas|Kg</th>
      <th scope="col">PrCJ</th>
      <th scope="col">PrPz</th>
      <th scope="col">ObseqCJ</th>
      <th scope="col">ObseqPz</th>
    </tr>
  </thead>
  <tbody>
  <?php 


    $sql = "
        SELECT ep.orden_stop, ep.pedido, ep.cliente, ep.direccion, 
               IF(ep.total_cajas = ep.total_piezas, 0, ep.total_cajas) AS total_cajas, 
               IFNULL(IF(ep.total_cajas = ep.total_piezas, ep.total_piezas, (ep.total_piezas%ep.total_cajas)), ep.total_piezas) AS total_piezas, 
               IF(ep.total_cajas_obseq = ep.total_piezas_obseq, 0, ep.total_cajas_obseq) AS total_cajas_obseq, 
               IF(ep.total_cajas_obseq = ep.total_piezas_obseq, ep.total_piezas_obseq, (ep.total_piezas_obseq%ep.total_cajas_obseq)) AS total_piezas_obseq, 
               ep.ruta, ep.promocion_cajas, ep.promocion_piezas
        FROM (
            SELECT
                tdo.orden_stop,
            tdo.Fol_folio AS pedido,
            IFNULL(CONCAT('(', d.id_destinatario,') ' , d.razonsocial), '') AS cliente,
            IFNULL(CONCAT(d.ciudad, ', ', d.colonia, ', ', d.direccion), '') AS direccion,
            #IFNULL(COUNT(DISTINCT tc.Cve_CajaMix), 0) AS total_cajas,

            IFNULL(((SELECT DISTINCT IFNULL(SUM(TRUNCATE(tdp.Num_cantidad/a.num_multiplo, 0)), 0)
            FROM td_subpedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = tdo.Fol_folio AND um.mav_cveunimed != 'XBX' AND tdp.cve_articulo = a.cve_articulo)
            +
            (SELECT DISTINCT IFNULL(SUM(tdc.Num_cantidad), 0)
            FROM td_subpedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = tdo.Fol_folio AND um.mav_cveunimed = 'XBX' AND tdc.cve_articulo = a.cve_articulo)), 0) AS total_cajas,

            IFNULL(((SELECT IFNULL(SUM(tdp.Num_cantidad), 0)
            FROM td_subpedido tdp 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = tdo.Fol_folio AND um.mav_cveunimed != 'XBX')
            +
            (SELECT IFNULL(SUM(tdc.Num_cantidad*a.num_multiplo), 0)
            FROM td_subpedido tdc 
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = tdo.Fol_folio AND um.mav_cveunimed = 'XBX')), 0) AS total_piezas,

            IFNULL(((SELECT DISTINCT IFNULL(SUM(TRUNCATE(tdp.Num_cantidad/a.num_multiplo, 0)), 0)
            FROM td_subpedido tdp 
            INNER JOIN th_pedido p ON p.Fol_Folio = tdp.fol_folio AND p.tipo_negociacion = 'Obsequio'
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = tdo.Fol_folio AND um.mav_cveunimed != 'XBX' AND tdp.cve_articulo = a.cve_articulo AND p.tipo_negociacion = 'Obsequio')
            +
            (SELECT DISTINCT IFNULL(SUM(tdc.Num_cantidad), 0)
            FROM td_subpedido tdc 
            INNER JOIN th_pedido p ON p.Fol_Folio = tdc.fol_folio AND p.tipo_negociacion = 'Obsequio'
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = tdo.Fol_folio AND um.mav_cveunimed = 'XBX' AND tdc.cve_articulo = a.cve_articulo AND p.tipo_negociacion = 'Obsequio')), 0) AS total_cajas_obseq,

            IFNULL(((SELECT IFNULL(SUM(tdp.Num_cantidad), 0)
            FROM td_subpedido tdp 
            INNER JOIN th_pedido p ON p.Fol_Folio = tdp.fol_folio AND p.tipo_negociacion = 'Obsequio'
            LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdp.fol_folio = tdo.Fol_folio AND um.mav_cveunimed != 'XBX' AND p.tipo_negociacion = 'Obsequio')
            +
            (SELECT IFNULL(SUM(tdc.Num_cantidad*a.num_multiplo), 0)
            FROM td_subpedido tdc 
            INNER JOIN th_pedido p ON p.Fol_Folio = tdc.fol_folio AND p.tipo_negociacion = 'Obsequio'
            LEFT JOIN c_articulo a ON a.cve_articulo = tdc.Cve_articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            WHERE tdc.fol_folio = tdo.Fol_folio AND um.mav_cveunimed = 'XBX' AND p.tipo_negociacion = 'Obsequio')), 0) AS total_piezas_obseq,

            IFNULL(t_ruta.cve_ruta, 0) AS ruta, 
            #IFNULL(SUM(prc.Cant), 0) AS promocion_cajas,
            #IFNULL(SUM(prp.Cant), 0) AS promocion_piezas
            IFNULL((SELECT SUM(Cant) FROM PRegalado WHERE tdo.Fol_folio LIKE CONCAT('%',Docto) AND Tipmed = 'Caja'), 0) AS promocion_cajas,
            IFNULL((SELECT SUM(Cant) FROM PRegalado WHERE tdo.Fol_folio LIKE CONCAT('%',Docto) AND Tipmed != 'Caja'), 0) AS promocion_piezas
            #GROUP_CONCAT(CONCAT(pr.Cant, ' ', pr.Tipmed, ' ', pr.SKU ) SEPARATOR '<br>') AS promocion
          FROM td_ordenembarque tdo
                LEFT JOIN th_ordenembarque ON tdo.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN Rel_PedidoDest rel ON rel.Fol_Folio = tdo.Fol_folio 
                LEFT JOIN c_destinatarios d ON d.id_destinatario = rel.Id_Destinatario
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta 
                LEFT JOIN th_pedido th ON t_ruta.cve_ruta = th.cve_ubicacion AND th.STATUS = 'T'
                LEFT JOIN th_cajamixta tc ON tc.fol_folio = tdo.Fol_folio
                LEFT JOIN PRegalado prc ON tdo.Fol_folio LIKE CONCAT('%',prc.Docto) AND prc.Tipmed = 'Caja'
                LEFT JOIN PRegalado prp ON tdo.Fol_folio LIKE CONCAT('%',prp.Docto) AND prp.Tipmed != 'Caja'
          WHERE tdo.ID_OEmbarque = {$folio}
          GROUP BY tdo.Fol_folio
          ORDER BY tdo.orden_stop
    ) AS ep
    ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $tot_cajas = 0; $tot_piezas = 0; $tot_prcj = 0; $tot_prpz = 0; $tot_obseq_c = 0; $tot_obseq_p = 0;
    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
      <th><?php echo $orden_stop; ?></th>
      <td><?php echo $ruta; ?></td>
      <td><?php echo $pedido; ?></td>
      <td><?php echo $cliente; ?></td>
      <td><?php echo $direccion; ?></td>
      <td align="center"><?php echo ($total_cajas-$promocion_cajas-$total_cajas_obseq); ?></td>
      <td align="center"><?php echo ($total_piezas-$promocion_piezas-$total_piezas_obseq); ?></td>
      <td align="center"><?php echo $promocion_cajas; ?></td>
      <td align="center"><?php echo $promocion_piezas; ?></td>
      <td align="center"><?php echo $total_cajas_obseq; ?></td>
      <td align="center"><?php echo $total_piezas_obseq; ?></td>
    </tr>
    <?php 
    $tot_cajas += ($total_cajas-$promocion_cajas-$total_cajas_obseq);
    $tot_piezas += ($total_piezas-$promocion_piezas-$total_piezas_obseq);
    $tot_prcj += $promocion_cajas;
    $tot_prpz += $promocion_piezas;
    $tot_obseq_c += $total_cajas_obseq;
    $tot_obseq_p += $total_piezas_obseq;
    }
    ?>
    <tr>
        <th></th>
        <td></td>
        <td></td>
        <td></td>
        <td align="right"><b>Total:</b></td>
        <td align="center"><?php echo $tot_cajas; ?></td>
        <td align="center"><?php echo $tot_piezas; ?></td>
        <td align="center"><?php echo $tot_prcj; ?></td>
        <td align="center"><?php echo $tot_prpz; ?></td>
        <td align="center"><?php echo $tot_obseq_c; ?></td>
        <td align="center"><?php echo $tot_obseq_p; ?></td>
    </tr>
  </tbody>
</table>
</div>

</div>
</body>
</html>

