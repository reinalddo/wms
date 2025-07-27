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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Pedido</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha Entrega</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pedido</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Cliente</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Razón Social</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tipo Venta</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Producto</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pares</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Disponible</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pendientes</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>FillRate</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen      = $_GET['almacen'];
    $search       = $_GET['criterio'];
    $tipopedido   = $_GET['tipopedido'];
    $ruta_pedido  = $_GET['ruta_pedido_list'];
    $fecha_inicio = $_GET['fechaInicio'];
    $fecha_fin    = $_GET['fechaFin'];
    $status       = $_GET['status'];

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
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) <= STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
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

    $sql = "SELECT DISTINCT DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fec_Pedido, 
        DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') AS Fec_Entrega, 
        r.cve_ruta, td.Fol_folio, c.Cve_Clte, c.RazonSocial, 
        IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
        a.des_articulo, 
        td.Num_cantidad AS Pares,
        IF(th.status = 'A' , (SELECT SUM(Existencia) FROM VS_ExistenciaParaSurtido WHERE cve_articulo = a.cve_articulo AND Cve_Almac = th.cve_almac AND IFNULL(td.cve_lote, '') = (IF(IFNULL(td.cve_lote, '') = '', '', cve_lote))),IFNULL(sp.Cantidad, 0)) AS Disponibles,
        (IFNULL(td.Num_cantidad, 0) - IFNULL(sp.Cantidad, 0)) AS Pendientes,
        TRUNCATE(((IFNULL(sp.Cantidad, 0)/td.Num_cantidad)*100), 2) AS Fill_Rate
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN td_subpedido tds ON tds.fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio AND pr.SKU = td.cve_articulo
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
LEFT JOIN td_surtidopiezas sp ON td.Fol_folio = sp.fol_folio AND tds.Cve_articulo = sp.Cve_articulo AND IFNULL(tds.Cve_Lote, '') = IFNULL(sp.LOTE, '') 
WHERE th.cve_almac = {$almacen} AND th.status = '{$status}'
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Fec_Pedido; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Fec_Entrega; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $cve_ruta; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Fol_folio; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Cve_Clte; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $RazonSocial; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $tipo_negociacion; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $Pares; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $Disponibles; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $Pendientes; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo $Fill_Rate; ?>%</div>
        <?php 
        $i++;

    }
  ?>

        <?php /* ?><div cell="B<?php echo $i+2; ?>"><?php echo $sql; ?></div><?php */ ?>
</div>