<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Resumen Pedidos por Ruta";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Grupo/Artículo</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Desc Grupo/Producto</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrCj</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrPz</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Cajas</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Piezas</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen          = $_GET['almacen'];
    $search           = $_GET['criterio'];
    $tipopedido       = $_GET['tipopedido'];
    $ruta_pedido      = $_GET['ruta_pedido_list'];
    $fecha_inicio     = $_GET['fechaInicio'];
    $fecha_fin        = $_GET['fechaFin'];

      $sql_ruta = "";
      if($ruta_pedido != '')
      {
          $ruta_arr = explode(";;;;;", $ruta_pedido);
          $ruta1 = $ruta_arr[0];
          $ruta2 = "";
          if(count($ruta_arr) > 1)
            $ruta2 = $ruta_arr[1];
          if($ruta1 != "")
          {
              $sql_ruta = " AND (th.ruta = '$ruta1' OR th.cve_ubicacion = '$ruta1') ";
          }

          if($ruta2 != "")
          {
              $sql_ruta = " AND (th.ruta IN ('$ruta1', '$ruta2') OR th.cve_ubicacion IN ('$ruta1', '$ruta2')) ";
          }
      }


      $sql_fecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha = " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //STR_TO_DATE('$fecha', '%d-%m-%Y')
        //$sql_fecha = " AND DATE_FORMAT(IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada), '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        $sql_fecha = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $sql_fecha = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $sql_fecha = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) <= STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
      }

      $sql_search = "";
      if (!empty($search) ) 
      {
            $sql_search = " AND (th.Fol_folio like '%$search%' OR th.Pick_Num like '%$search%' OR th.Fol_folio IN (SELECT Fol_folio FROM td_pedido WHERE Cve_articulo LIKE '%$search%')) ";
      }

      $sql_tipo_pedido1 = "";
      if($tipopedido != "")
      {
          //$sql_tipo_pedido1 = " AND IF(LEFT(th.Fol_folio, 2) = 'OT', 1, IF(LEFT(th.Fol_folio, 2) = 'TR', 2, IF(LEFT(th.Fol_folio, 2) = 'WS', 4, IF(LEFT(th.Fol_folio, 1) = 'S' AND IFNULL(th.cve_ubicacion,'') != '', 5, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) != 'R' AND LEFT(th.Fol_folio, 1) != 'P', 3, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'R', 6, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'P', 7, 0))))))) = '{$tipopedido}' ";
            $sql_tipo_pedido1 = " AND th.TipoPedido = '{$tipopedido}' ";
      }
      else
      {
          $sql_tipo_pedido1 = " AND IFNULL(o.ruta, '') = '' AND IFNULL(o.cve_ubicacion, '') = '' ";  //AND o.Fol_folio LIKE 'S%'
      }

    $sql = "
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, 
    SUM(pedido.cajas_total)+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS cajas_total, 
    IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0)) AS piezas_total,
    SUM(pedido.PrCaja) AS PrCaja, 
    SUM(pedido.PrPz) AS PrPz , 
    (SUM(pedido.cajas_total)+SUM(pedido.PrCaja))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, td.Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
#IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0)) AS cajas_total,
#IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0)) AS piezas_total,
IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, SUM(td.Num_cantidad)),TRUNCATE((SUM(td.Num_cantidad)/a.num_multiplo), 0)) AS cajas_total,
IF(um.mav_cveunimed != 'XBX', (SUM(td.Num_cantidad) - (a.num_multiplo*TRUNCATE((SUM(td.Num_cantidad)/a.num_multiplo), 0))), IF(a.num_multiplo = 1, SUM(td.Num_cantidad), 0)) AS piezas_total,
IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCaja,
IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio AND pr.SKU = td.cve_articulo
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND um.Activo = 1
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen}
GROUP BY Fol_folio, cve_articulo, pr.SKU
) AS pedido
GROUP BY pedido.cve_articulo";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo ($cajas_total-$PrCaja); ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo ($piezas_total-$PrPz); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $PrCaja; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $PrPz; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $tot_cajas-$PrCaja; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $tot_piezas-$PrPz; ?></div>
        <?php 
        $i++;

    }
  ?>
    
       <?php if($search == 'SQL0000WMS'){ ?> <div cell="B<?php echo $i+4; ?>"><?php echo ($sql); ?></div> <?php } ?>

</div>