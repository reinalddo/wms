<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';
    $status           = $_GET['status'];

    

    if($status == "A") $sheet1 = "Listo por asignar";
    if($status == "S") $sheet1 = "Surtiendo";
    if($status == "L") $sheet1 = "Pendiente de auditar";
    if($status == "R") $sheet1 = "Auditando";
    if($status == "P") $sheet1 = "Pendiente de empaque";
    if($status == "M") $sheet1 = "Empacando";
    if($status == "C") $sheet1 = "Pendiente de embarque";
    if($status == "E") $sheet1 = "Embarcando";
    if($status == "T") $sheet1 = "Enviado";
    if($status == "F") $sheet1 = "Entregado";
    if($status == "K") $sheet1 = "Cancelado";

    $sql_status = "";
    if($status)
        $sql_status = " AND th.status = '{$status}' ";



    //$sheet1 = "Listo Por Asignar";
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
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tipo Pedido</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Observaciones</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Status</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Prioridad</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Usuario Surtidor</div>
<?php 



    if(isset($_GET['pedidos_articulos']))
    {
?>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
<?php 
    }

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

    //$status = 'A';

    $sql = "SELECT DISTINCT DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, c.Cve_Clte, c.RazonSocial, th.status, ce.DESCRIPCION as estatus, ce.ORDEN, th.Fol_folio, 
CASE 
WHEN th.TipoPedido = 'T' THEN 'Orden de Trabajo'
WHEN th.TipoPedido = 'R' THEN 'Traslado Externo'
WHEN th.TipoPedido = 'W' THEN 'Ola'
WHEN th.TipoPedido = 'W2' THEN 'Ola de Olas'
WHEN th.TipoPedido = 'X' THEN 'Cross Docking'
WHEN th.TipoPedido = 'RI' THEN 'Traslado Interno'
ELSE 'Pedido General'
END AS TipoPedido, 
th.Observaciones,
ce.DESCRIPCION as status,
p.Descripcion as prioridad,
IF(ths.Fol_Folio IS NOT NULL, CONCAT('(', u.cve_usuario, ') ', u.nombre_completo), '') as usuario_surtidor

FROM th_pedido th
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN cat_estados ce ON ce.ESTADO = th.status
LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = th.ID_Tipoprioridad
LEFT JOIN th_subpedido ths ON ths.Fol_Folio = th.Fol_Folio
LEFT JOIN c_usuario u ON u.cve_usuario = ths.Cve_Usuario
WHERE th.cve_almac = {$almacen}
{$sql_tipo_pedido1} 
{$sql_fecha} 
{$sql_search} 
{$sql_ruta} 
{$sql_status}
AND th.TipoPedido = '{$tipopedido}'

ORDER BY th.Fol_folio
#ORDER BY ce.ORDEN
#ORDER BY th.TipoPedido
";

    if(isset($_GET['pedidos_articulos']))
    $sql = "SELECT DISTINCT DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, c.Cve_Clte, c.RazonSocial, th.status, ce.DESCRIPCION as estatus, ce.ORDEN, th.Fol_folio, 
    td.Cve_Articulo,
    a.des_articulo,
    td.Num_Cantidad,
CASE 
WHEN th.TipoPedido = 'T' THEN 'Orden de Trabajo'
WHEN th.TipoPedido = 'R' THEN 'Traslado Externo'
WHEN th.TipoPedido = 'W' THEN 'Ola'
WHEN th.TipoPedido = 'W2' THEN 'Ola de Olas'
WHEN th.TipoPedido = 'X' THEN 'Cross Docking'
WHEN th.TipoPedido = 'RI' THEN 'Traslado Interno'
ELSE 'Pedido General'
END AS TipoPedido
FROM td_pedido td
LEFT JOIN th_pedido th ON td.Fol_Folio = th.Fol_Folio
LEFT JOIN c_articulo a ON td.Cve_Articulo = a.cve_articulo
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN cat_estados ce ON ce.ESTADO = th.status
WHERE th.cve_almac = {$almacen}
{$sql_tipo_pedido1} 
{$sql_fecha} 
{$sql_search} 
{$sql_ruta} 
{$sql_status}
AND th.TipoPedido = '{$tipopedido}'

ORDER BY th.Fol_folio
#ORDER BY ce.ORDEN
#ORDER BY th.TipoPedido
";


//{$sql_ruta} 

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
        <div cell="G<?php echo $i; ?>"><?php echo $TipoPedido; ?></div>
        <?php 
        if(!isset($_GET['pedidos_articulos']))
        {
        ?>
        <div cell="H<?php echo $i; ?>"><?php echo $Observaciones; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $status; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $prioridad; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $usuario_surtidor; ?></div>
        <?php 
        }
        ?>
        <?php 
        if(isset($_GET['pedidos_articulos']))
        {
?>
        <div cell="H<?php echo $i; ?>"><?php echo $Cve_Articulo; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $Num_Cantidad; ?></div>
<?php 
        }
        $i++;

    }
  ?>
          <?php /* ?><div cell="J<?php echo $i; ?>"><?php echo $sql; ?></div><?php */ ?>

</div>
