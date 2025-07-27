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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Artículo</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Lote|Serie</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Caducidad</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>UM</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>LP</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Hora Recepción</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Status</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Zona Recepción</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $pedido  = $_GET['pedido'];
  $id_ruta = $_GET['id_ruta'];
  $almacen = $_GET['almacen'];
  $diao    = $_GET['diao'];
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_fin = $_GET['fecha_fin'];

      $sql_fecha = "";
      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha = " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') >= '{$fecha_inicio}' ";

      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') <= '{$fecha_fin}'";
      }

      $sql_diao = "";
      if($diao)
      {
            $sql_diao = " AND d.Diao = '$diao' ";
      }

      $sql_pedido = "";
      if($pedido)
      {
            $sql_pedido = " AND d.Folio = '$pedido' ";
      }
        $sql = "SELECT DISTINCT 
                        ub.desc_ubicacion AS zona_recepcion, r.cve_ruta AS cliente_ruta,
                        a.cve_articulo AS clave, a.des_articulo AS descripcion,
                        IF(a.control_lotes = 'S' OR a.control_numero_series = 'S' , IFNULL(tde.cve_lote, ''), '') AS lote_serie, 
                        IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(tde.cve_lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        IFNULL(tdt.Cantidad, tde.CantidadRecibida) AS Cantidad,
                        um.cve_umed AS UM,
                        d.Folio,
                        IFNULL(tdt.ClaveEtiqueta, '') AS LP,
                        IF(tde.status = 'M', 'Enviado a Merma',IF(IFNULL(tde.cve_lote, '') = '', 'Pendiente de Lote', IF(IFNULL(tde.CantidadUbicada, 0) > 0, 'Ubicado', 'Pendiente Acomodo'))) AS STATUS,
                        tde.id AS id_item,
                        d.Diao,
                        IFNULL(a.control_lotes, 'N') AS control_lotes,
                        IFNULL(a.Caduca, 'N') AS Caduca,
                        IFNULL(a.control_numero_series, 'N') AS control_numero_series,
                        DATE_FORMAT(e.HoraInicio, '%H:%m:%i') AS Hora,
                        e.Cve_Usuario
                FROM Descarga d
                LEFT JOIN c_almacenp alm ON alm.clave = d.IdEmpresa
                LEFT JOIN c_articulo a ON a.cve_articulo = d.Articulo
                INNER JOIN th_entalmacen e ON e.Fact_Prov = d.Folio
                INNER JOIN td_entalmacen tde ON tde.cve_articulo = d.Articulo AND tde.fol_folio = e.Fol_Folio
                LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = d.Articulo AND tdt.fol_folio = e.Fol_Folio AND tdt.cve_lote = tde.cve_lote
                LEFT JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
                LEFT JOIN tubicacionesretencion ub ON ub.cve_ubicacion = e.cve_ubicacion
                LEFT JOIN c_lotes l ON l.cve_articulo = d.Articulo AND tde.cve_lote = l.Lote
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE d.IdRuta = '$id_ruta' AND d.IdEmpresa = '$almacen' AND d.Cantidad > 0 AND (IFNULL(a.control_lotes, 'N') = 'S' OR IFNULL(a.control_numero_series, 'N') = 'S')
                #AND tde.status = 'Q' 
                {$sql_pedido} {$sql_diao} {$sql_fecha} 
                ORDER BY d.Diao DESC
";
    //echo var_dump($sql);
    //die();
      // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo 'D'.$Folio; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $cliente_ruta; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $clave; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $descripcion; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $lote_serie; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $Caducidad; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $Cantidad; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $UM; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $LP; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $Hora; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $STATUS; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo $zona_recepcion; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>