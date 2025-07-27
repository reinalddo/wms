<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Sabores POR GRUPO";
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
      if (!empty($search) && $search != 'SQL0000WMS') 
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


$columnas = array('A', 'B','C' ,'D' ,'E' ,'F' ,'G' ,'H' ,'I' ,'J' ,'K' ,'L' ,'M' ,'N' ,'O' ,'P' ,'Q' ,'R' ,'S' ,'T' ,'U' ,'V' ,'W' ,'X' ,'Y' ,'Z', 'AA', 'AB','AC' ,'AD' ,'AE' ,'AF' ,'AG' ,'AH' ,'AI' ,'AJ' ,'AK' ,'AL' ,'AM' ,'AN' ,'AO' ,'AP' ,'AQ' ,'AR' ,'AS' ,'AT' ,'AU' ,'AV' ,'AW' ,'AX' ,'AY' ,'AZ', 'BA', 'BB','BC' ,'BD' ,'BE' ,'BF' ,'BG' ,'BH' ,'BI' ,'BJ' ,'BK' ,'BL' ,'BM' ,'BN' ,'BO' ,'BP' ,'BQ' ,'BR' ,'BS' ,'BT' ,'BU' ,'BV' ,'BW' ,'BX' ,'BY' ,'BZ');

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql = "
SELECT DISTINCT 
    GROUP_CONCAT(DISTINCT IFNULL(ccar.Id_Carac, '_SS_') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
    GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion IN ('Contado', 'Obsequio', 'Credito')
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
ORDER BY Des_Carac
";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $i = 5;
?>
    

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pedido</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Grupo/Artículo</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Desc Grupo/Producto</div>
    <?php 
    $col = mysqli_fetch_array($res);
    $sabores_arr = explode(';;;;;', $col["Des_Carac"]);

    $j = 5;
    for($c = 0; $c < count($sabores_arr); $c++)
    {
?>
        <div cell="<?php echo $columnas[$c+$i]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'><?php echo $sabores_arr[$c]; ?></div>
<?php 
        $j++;
    }
    ?>
    <div cell="<?php echo $columnas[$j]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="<?php echo $columnas[$j+1]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="<?php echo $columnas[$j+2]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrCj</div>
    <div cell="<?php echo $columnas[$j+3]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrPz</div>
    <div cell="<?php echo $columnas[$j+4]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>ObCj</div>
    <div cell="<?php echo $columnas[$j+5]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>ObPz</div>
    <div cell="<?php echo $columnas[$j+6]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Cajas</div>
    <div cell="<?php echo $columnas[$j+7]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Piezas</div>
    <?php 

    ?>

<?php 

    $sql = "
SELECT 
grupo_sabores.cve_articulo,
grupo_sabores.des_articulo,
#SUM(grupo_sabores.cajas_total) AS cajas_total,
#SUM(grupo_sabores.piezas_total) AS piezas_total,
((SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0)) AS cajas_total, 
(IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))) AS piezas_total,

0 AS ObCaja,
0 AS ObPz,
0 AS PrCaja,
0 AS PrPz,
grupo_sabores.ruta,
GROUP_CONCAT(DISTINCT grupo_sabores.pedido) AS pedido,
GROUP_CONCAT(DISTINCT grupo_sabores.Cve_Clte) AS Cve_Clte,
GROUP_CONCAT(DISTINCT IFNULL(grupo_sabores.Id_Carac, '_SS_') ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
GROUP_CONCAT(DISTINCT CONCAT(IFNULL(SUBSTRING(grupo_sabores.Des_Carac, 1, 5), 'SurtR'), ':::::', (((grupo_sabores.cajas_total)*grupo_sabores.num_multiplo)+(grupo_sabores.piezas_total))) ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Des_Carac,
#GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac

#SUM(grupo_sabores.cajas_total) AS tot_cajas,
#SUM(grupo_sabores.piezas_total) AS tot_piezas,
#SUM(grupo_sabores.piezas_total) - (IF(grupo_sabores.PrCaja > 0 AND SUM(grupo_sabores.piezas_total) > 0, grupo_sabores.PrCaja*grupo_sabores.num_multiplo, 0)) AS piezas_total,

((SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0)) AS tot_cajas, 
(IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))) AS tot_piezas,


grupo_sabores.num_multiplo
FROM (
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, pedido.num_multiplo,
    ((pedido.cajas_total)+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0)-pedido.PrCaja) AS cajas_total, 
    (IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))-pedido.PrPz) AS piezas_total,
    GROUP_CONCAT(DISTINCT pedido.cve_ruta) AS ruta,
    GROUP_CONCAT(DISTINCT pedido.Fol_folio) AS pedido,
    GROUP_CONCAT(DISTINCT IFNULL(pedido.Cve_Clte, '')) AS Cve_Clte,
    pedido.PrCaja AS PrCaja, 
    pedido.PrPz AS PrPz , 
    pedido.mav_cveunimed,
    pedido.Id_Carac, 
    pedido.Des_Carac, 
    ((pedido.cajas_total))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, td.Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
    IFNULL(ccar.Id_Carac, '_SS_') AS Id_Carac,
    IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') AS Des_Carac,
SUM(IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))) AS cajas_total,
SUM(IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0))) AS piezas_total,
SUM(IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS PrCaja,
SUM(IF(pr.Tipmed != 'Caja', pr.Cant, 0)) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio AND pr.SKU = td.cve_articulo
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion IN ('Contado', 'Credito') 
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
GROUP BY cve_articulo #, Id_Carac
) AS pedido
GROUP BY pedido.cve_articulo, pedido.Id_Carac, pedido.Fol_Folio
ORDER BY pedido.cve_articulo, pedido.Des_Carac
) AS grupo_sabores
GROUP BY grupo_sabores.cve_articulo

UNION

SELECT * FROM (
SELECT 
grupo_sabores.cve_articulo,
grupo_sabores.des_articulo,
0 AS cajas_total,
0 AS piezas_total,
0 AS ObCaja,
0 AS ObPz,
SUM(grupo_sabores.PrCaja) AS PrCaja,
SUM(grupo_sabores.PrPz) AS PrPz,
grupo_sabores.ruta,
GROUP_CONCAT(DISTINCT grupo_sabores.pedido) AS pedido,
GROUP_CONCAT(DISTINCT grupo_sabores.Cve_Clte) AS Cve_Clte,
GROUP_CONCAT(DISTINCT IFNULL(grupo_sabores.Id_Carac, '_SS_') ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
GROUP_CONCAT(DISTINCT CONCAT(IFNULL(SUBSTRING(grupo_sabores.Des_Carac, 1, 5), 'SurtR'), ':::::', ((grupo_sabores.PrCaja*grupo_sabores.num_multiplo)+grupo_sabores.PrPz)) ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Des_Carac,
#GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac
0 AS tot_cajas,
0 AS tot_piezas,
grupo_sabores.num_multiplo
FROM (
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, pedido.num_multiplo,
    0 AS cajas_total, 
    0 AS piezas_total,
    GROUP_CONCAT(DISTINCT pedido.cve_ruta) AS ruta,
    GROUP_CONCAT(DISTINCT pedido.Fol_folio) AS pedido,
    GROUP_CONCAT(DISTINCT IFNULL(pedido.Cve_Clte, '')) AS Cve_Clte,
    SUM(pedido.PrCaja) AS PrCaja, 
    SUM(pedido.PrPz) AS PrPz , 
    pedido.Id_Carac, 
    pedido.Des_Carac, 
    (SUM(pedido.cajas_total)+SUM(pedido.PrCaja))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') AS Fec_Entrega, r.cve_ruta, pr.Docto as Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
    IFNULL(ccar.Id_Carac, '_SS_') AS Id_Carac,
    IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') AS Des_Carac,
(IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))) AS cajas_total,
(IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0))) AS piezas_total,
(IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS PrCaja,
(IF(pr.Tipmed != 'Caja', pr.Cant, 0)) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN PRegalado pr ON td.Fol_folio = pr.Docto AND pr.SKU = td.cve_articulo #CONCAT(pr.RutaId,'_',pr.Docto)
LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
LEFT JOIN c_cliente c ON c.Cve_Clte = d.Cve_Clte
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion IN ('Contado', 'Obsequio', 'Credito') 
AND IFNULL(pr.Docto, '') != '' 
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
GROUP BY cve_articulo, Fol_folio
) AS pedido
GROUP BY pedido.cve_articulo, pedido.Id_Carac, pedido.Fol_Folio
ORDER BY pedido.cve_articulo, pedido.Des_Carac
) AS grupo_sabores 
GROUP BY grupo_sabores.cve_articulo
) AS sabores_promo WHERE sabores_promo.PrCaja > 0 OR sabores_promo.PrPz > 0

UNION

SELECT 
grupo_sabores.cve_articulo,
grupo_sabores.des_articulo,
0 AS cajas_total,
0 AS piezas_total,
#SUM(grupo_sabores.cajas_total) AS ObCaja,
#SUM(grupo_sabores.piezas_total) AS ObPz,
(SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0) AS ObCaja,
IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))+SUM(grupo_sabores.PrPz) AS ObPz,
0 AS PrCaja,
0 AS PrPz,
grupo_sabores.ruta,
GROUP_CONCAT(DISTINCT grupo_sabores.pedido) AS pedido,
GROUP_CONCAT(DISTINCT grupo_sabores.Cve_Clte) AS Cve_Clte,
GROUP_CONCAT(DISTINCT IFNULL(grupo_sabores.Id_Carac, '_SS_') ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
GROUP_CONCAT(DISTINCT CONCAT(IFNULL(SUBSTRING(grupo_sabores.Des_Carac, 1, 5), 'SurtR'), ':::::', ((grupo_sabores.cajas_total*grupo_sabores.num_multiplo)+grupo_sabores.piezas_total)) ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Des_Carac,
#GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac

#SUM(grupo_sabores.cajas_total) AS tot_cajas,
#SUM(grupo_sabores.piezas_total) AS tot_piezas,
(SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0) AS tot_cajas,
IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))+SUM(grupo_sabores.PrPz) AS tot_piezas,
grupo_sabores.num_multiplo
FROM (
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, pedido.num_multiplo,
    ((pedido.cajas_total)+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0)-pedido.PrCaja) AS cajas_total, 
    (IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))-pedido.PrPz) AS piezas_total,
    GROUP_CONCAT(DISTINCT pedido.cve_ruta) AS ruta,
    GROUP_CONCAT(DISTINCT pedido.Fol_folio) AS pedido,
    GROUP_CONCAT(DISTINCT IFNULL(pedido.Cve_Clte, '')) AS Cve_Clte,
    pedido.PrCaja AS PrCaja, 
    pedido.PrPz AS PrPz , 
    pedido.Id_Carac, 
    pedido.Des_Carac, 
    pedido.mav_cveunimed,
    0 AS tot_cajas,
    0 AS tot_piezas
    #((pedido.cajas_total))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    #IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, td.Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
    IFNULL(ccar.Id_Carac, '_SS_') AS Id_Carac,
    IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') AS Des_Carac,
SUM(IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))) AS cajas_total,
SUM(IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0))) AS piezas_total,
SUM(IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS PrCaja,
SUM(IF(pr.Tipmed != 'Caja', pr.Cant, 0)) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio AND pr.SKU = td.cve_articulo
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion = 'Obsequio' 
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
GROUP BY cve_articulo, Id_Carac
) AS pedido
GROUP BY pedido.cve_articulo, pedido.Id_Carac, pedido.Fol_Folio
ORDER BY pedido.cve_articulo, pedido.Des_Carac
) AS grupo_sabores
GROUP BY grupo_sabores.cve_articulo

ORDER BY cve_articulo
";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $ruta; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $pedido; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Cve_Clte; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $des_articulo; ?></div>

    <?php 

    $j = 5; $k = 5;
    //LIMON:::::500;;;;;TORONJA:::::500
    $des_carac_arr = explode(";;;;;", $Des_Carac);
    for($dc = 0; $dc < count($des_carac_arr); $dc++)
    {
        $des_carac_val = explode(":::::", $des_carac_arr[$dc]);
        for($c = 0; $c < count($sabores_arr); $c++)
        {
            if($des_carac_val[0] == $sabores_arr[$c])
            {
        ?>
        <div cell="<?php echo $columnas[$c+$k].$i; ?>"><?php echo $des_carac_val[1]; ?></div>
        <?php 
            }
        }
        $des_carac_val = array();
        //$j++;
    }
    
    $des_carac_arr = array();
    $j = count($sabores_arr)+5;
    ?>
        <div cell="<?php echo $columnas[$j].$i; ?>"><?php echo ($cajas_total); ?></div>
        <div cell="<?php echo $columnas[$j+1].$i; ?>"><?php echo ($piezas_total); ?></div>
        <div cell="<?php echo $columnas[$j+2].$i; ?>"><?php echo $PrCaja; ?></div>
        <div cell="<?php echo $columnas[$j+3].$i; ?>"><?php echo $PrPz; ?></div>
        <div cell="<?php echo $columnas[$j+4].$i; ?>"><?php echo $ObCaja; ?></div>
        <div cell="<?php echo $columnas[$j+5].$i; ?>"><?php echo $ObPz; ?></div>
        <div cell="<?php echo $columnas[$j+6].$i; ?>"><?php 
        //echo $tot_cajas+$PrCaja+$ObCaja; 
        //echo $tot_cajas+$PrCaja+$ObCaja; 
        if($ObCaja == 0)
            echo $tot_cajas+$PrCaja; 
        else
            echo $ObCaja; 
        ?></div>
        <div cell="<?php echo $columnas[$j+7].$i; ?>"><?php 
        //echo $tot_piezas+$PrPz+$ObPz; 
        //echo $tot_piezas+$PrPz+$ObPz; 
        if($ObPz == 0)
            echo $tot_piezas+$PrPz; 
        else
            echo $ObPz; 
        ?></div>
        <?php 
        $i++;

    }
  ?>
        <?php /* ?><div cell="E<?php echo $i; ?>"><?php echo $sql; ?></div><?php */ ?>
<?php if($search == 'SQL0000WMS'){ ?><div cell="E<?php echo $i; ?>"><?php echo $sql; ?></div><?php } ?>

</div>


<?php 
$sheet2 = "Sabores por PEDIDO";
 ?>

 <div sheet-name="<?php echo $sheet2; ?>">

                                  


    <?php
    $styleArray = [
        'font' => [
            'name' => 'Calibri', //'Verdana', 'Arial'
            'size' => 14,
            'bold' => true
        ]
    ];


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
      if (!empty($search) && $search != 'SQL0000WMS') 
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


$columnas = array('A', 'B','C' ,'D' ,'E' ,'F' ,'G' ,'H' ,'I' ,'J' ,'K' ,'L' ,'M' ,'N' ,'O' ,'P' ,'Q' ,'R' ,'S' ,'T' ,'U' ,'V' ,'W' ,'X' ,'Y' ,'Z', 'AA', 'AB','AC' ,'AD' ,'AE' ,'AF' ,'AG' ,'AH' ,'AI' ,'AJ' ,'AK' ,'AL' ,'AM' ,'AN' ,'AO' ,'AP' ,'AQ' ,'AR' ,'AS' ,'AT' ,'AU' ,'AV' ,'AW' ,'AX' ,'AY' ,'AZ', 'BA', 'BB','BC' ,'BD' ,'BE' ,'BF' ,'BG' ,'BH' ,'BI' ,'BJ' ,'BK' ,'BL' ,'BM' ,'BN' ,'BO' ,'BP' ,'BQ' ,'BR' ,'BS' ,'BT' ,'BU' ,'BV' ,'BW' ,'BX' ,'BY' ,'BZ');

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql = "
SELECT DISTINCT 
    GROUP_CONCAT(DISTINCT IFNULL(ccar.Id_Carac, '_SS_') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
    GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion IN ('Contado', 'Obsequio', 'Credito')
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
ORDER BY Des_Carac
";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $i = 5;
?>
    

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Pedido</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Grupo/Artículo</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Desc Grupo/Producto</div>
    <?php 
    $col = mysqli_fetch_array($res);
    $sabores_arr = explode(';;;;;', $col["Des_Carac"]);

    $j = 5;
    for($c = 0; $c < count($sabores_arr); $c++)
    {
?>
        <div cell="<?php echo $columnas[$c+$i]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'><?php echo $sabores_arr[$c]; ?></div>
<?php 
        $j++;
    }
    ?>
    <div cell="<?php echo $columnas[$j]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cajas</div>
    <div cell="<?php echo $columnas[$j+1]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Piezas</div>
    <div cell="<?php echo $columnas[$j+2]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrCj</div>
    <div cell="<?php echo $columnas[$j+3]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>PrPz</div>
    <div cell="<?php echo $columnas[$j+4]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>ObCj</div>
    <div cell="<?php echo $columnas[$j+5]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>ObPz</div>
    <div cell="<?php echo $columnas[$j+6]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Cajas</div>
    <div cell="<?php echo $columnas[$j+7]; ?>1" excelStyle='<?php echo json_encode($styleArray); ?>'>Total Piezas</div>
    <?php 

    ?>

<?php 

    $sql = "
SELECT 
GROUP_CONCAT(DISTINCT grupo_sabores.cve_articulo) AS cve_articulo,
GROUP_CONCAT(DISTINCT grupo_sabores.des_articulo) AS des_articulo,
#SUM(grupo_sabores.cajas_total) AS cajas_total,
#SUM(grupo_sabores.piezas_total) AS piezas_total,
((SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0)-grupo_sabores.PrCaja) AS cajas_total, 
(IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))-grupo_sabores.PrPz) AS piezas_total,
0 AS ObCaja,
0 AS ObPz,
0 AS PrCaja,
0 AS PrPz,
grupo_sabores.ruta,
GROUP_CONCAT(DISTINCT grupo_sabores.pedido) AS pedido,
GROUP_CONCAT(DISTINCT grupo_sabores.Cve_Clte) AS Cve_Clte,
GROUP_CONCAT(DISTINCT IFNULL(grupo_sabores.Id_Carac, '_SS_') ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
GROUP_CONCAT(DISTINCT CONCAT(IFNULL(SUBSTRING(grupo_sabores.Des_Carac, 1, 5), 'SurtR'), ':::::', ((grupo_sabores.cajas_total*grupo_sabores.num_multiplo)+grupo_sabores.piezas_total)) ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Des_Carac,
#GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac
#SUM(grupo_sabores.cajas_total) AS tot_cajas,
#SUM(grupo_sabores.piezas_total) AS tot_piezas,
((SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0)-grupo_sabores.PrCaja) AS tot_cajas, 
(IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))-grupo_sabores.PrPz) AS tot_piezas,
grupo_sabores.num_multiplo
FROM (
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, pedido.num_multiplo,
    ((pedido.cajas_total)+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0)-pedido.PrCaja) AS cajas_total, 
    (IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))-pedido.PrPz) AS piezas_total,
    pedido.cve_ruta AS ruta,
    pedido.Fol_folio AS pedido,
    IFNULL(pedido.Cve_Clte, '') AS Cve_Clte,
    0 AS PrCaja, 
    0 AS PrPz , 
    pedido.Id_Carac, 
    pedido.Des_Carac, 
    pedido.mav_cveunimed,
    (SUM(pedido.cajas_total))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, td.Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
    IFNULL(ccar.Id_Carac, '_SS_') AS Id_Carac,
    IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') AS Des_Carac,
IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0)) AS cajas_total,
IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0)) AS piezas_total,
IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCaja,
IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio AND pr.SKU = td.cve_articulo
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion IN ('Contado', 'Credito')
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
) AS pedido
GROUP BY pedido, cve_articulo, Des_Carac
ORDER BY pedido.cve_articulo, pedido.Des_Carac
) AS grupo_sabores
GROUP BY grupo_sabores.pedido, grupo_sabores.cve_articulo

UNION

SELECT * FROM (
SELECT 
GROUP_CONCAT(DISTINCT grupo_sabores.cve_articulo) AS cve_articulo,
GROUP_CONCAT(DISTINCT grupo_sabores.des_articulo) AS des_articulo,
0 AS cajas_total,
0 AS piezas_total,
0 AS ObCaja,
0 AS ObPz,
SUM(grupo_sabores.PrCaja) AS PrCaja,
SUM(grupo_sabores.PrPz) AS PrPz,
grupo_sabores.ruta,
GROUP_CONCAT(DISTINCT grupo_sabores.pedido) AS pedido,
GROUP_CONCAT(DISTINCT grupo_sabores.Cve_Clte) AS Cve_Clte,
GROUP_CONCAT(DISTINCT IFNULL(grupo_sabores.Id_Carac, '_SS_') ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
GROUP_CONCAT(DISTINCT CONCAT(IFNULL(SUBSTRING(grupo_sabores.Des_Carac, 1, 5), 'SurtR'), ':::::', ((grupo_sabores.PrCaja*grupo_sabores.num_multiplo)+grupo_sabores.PrPz)) ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Des_Carac,
0 AS tot_cajas,
0 AS tot_piezas,
grupo_sabores.num_multiplo
FROM (
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, pedido.num_multiplo,
    0 AS cajas_total, 
    0 AS piezas_total,
    pedido.cve_ruta AS ruta,
    pedido.Fol_folio AS pedido,
    IFNULL(pedido.Cve_Clte, '') AS Cve_Clte,
    SUM(pedido.PrCaja) AS PrCaja, 
    SUM(pedido.PrPz) AS PrPz , 
    pedido.Id_Carac, 
    pedido.Des_Carac, 
    (SUM(pedido.cajas_total)+SUM(pedido.PrCaja))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') AS Fec_Entrega, r.cve_ruta, td.Fol_folio as Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
    IFNULL(ccar.Id_Carac, '_SS_') AS Id_Carac,
    IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') AS Des_Carac,
IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0)) AS cajas_total,
IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0)) AS piezas_total,
IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCaja,
IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN PRegalado pr ON td.Fol_folio = pr.Docto AND pr.SKU = td.cve_articulo #CONCAT(pr.RutaId,'_',pr.Docto)
LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
LEFT JOIN c_cliente c ON c.Cve_Clte = d.Cve_Clte
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion IN ('Contado', 'Obsequio', 'Credito')
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
) AS pedido
GROUP BY pedido, cve_articulo , Des_Carac
ORDER BY pedido.cve_articulo, pedido.Des_Carac
) AS grupo_sabores 
GROUP BY grupo_sabores.pedido, grupo_sabores.cve_articulo
) AS sabores_promo WHERE sabores_promo.PrCaja > 0 OR sabores_promo.PrPz > 0

UNION 

SELECT 
GROUP_CONCAT(DISTINCT grupo_sabores.cve_articulo) AS cve_articulo,
GROUP_CONCAT(DISTINCT grupo_sabores.des_articulo) AS des_articulo,
0 AS cajas_total,
0 AS piezas_total,
#SUM(grupo_sabores.cajas_total) AS ObCaja,
#SUM(grupo_sabores.piezas_total) AS ObPz,
(SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0) AS ObCaja,
IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))+SUM(grupo_sabores.PrPz) AS ObPz,
0 AS PrCaja,
0 AS PrPz,
grupo_sabores.ruta,
GROUP_CONCAT(DISTINCT grupo_sabores.pedido) AS pedido,
GROUP_CONCAT(DISTINCT grupo_sabores.Cve_Clte) AS Cve_Clte,
GROUP_CONCAT(DISTINCT IFNULL(grupo_sabores.Id_Carac, '_SS_') ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Id_Carac,
GROUP_CONCAT(DISTINCT CONCAT(IFNULL(SUBSTRING(grupo_sabores.Des_Carac, 1, 5), 'SurtR'), ':::::', ((grupo_sabores.cajas_total*grupo_sabores.num_multiplo)+grupo_sabores.piezas_total)) ORDER BY grupo_sabores.Des_Carac SEPARATOR ';;;;;') AS Des_Carac,
#GROUP_CONCAT(DISTINCT IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') ORDER BY ccar.Des_Carac SEPARATOR ';;;;;') AS Des_Carac
#SUM(grupo_sabores.cajas_total) AS tot_cajas,
#SUM(grupo_sabores.piezas_total) AS tot_piezas,
(SUM(grupo_sabores.cajas_total))+TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0) AS tot_cajas,
IF(grupo_sabores.mav_cveunimed != 'XBX', (SUM(grupo_sabores.piezas_total) - (grupo_sabores.num_multiplo*TRUNCATE((SUM(grupo_sabores.piezas_total)/grupo_sabores.num_multiplo), 0))), IF(grupo_sabores.num_multiplo = 1, SUM(grupo_sabores.piezas_total), 0))+SUM(grupo_sabores.PrPz) AS tot_piezas,
grupo_sabores.num_multiplo
FROM (
SELECT  pedido.cve_articulo AS cve_articulo, pedido.des_articulo AS des_articulo, pedido.num_multiplo,
    pedido.cajas_total,
    pedido.piezas_total,
    #((pedido.cajas_total)+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0)-pedido.PrCaja) AS cajas_total, 
    #(IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))-pedido.PrPz) AS piezas_total,
    pedido.cve_ruta AS ruta,
    pedido.Fol_folio AS pedido,
    IFNULL(pedido.Cve_Clte, '') AS Cve_Clte,
    0 AS PrCaja, 
    0 AS PrPz , 
    pedido.Id_Carac, 
    pedido.Des_Carac, 
    pedido.mav_cveunimed,
    0 AS tot_cajas,
    0 AS tot_piezas
    #(SUM(pedido.cajas_total))+TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0) AS tot_cajas,
    #IF(pedido.mav_cveunimed != 'XBX', (SUM(pedido.piezas_total) - (pedido.num_multiplo*TRUNCATE((SUM(pedido.piezas_total)/pedido.num_multiplo), 0))), IF(pedido.num_multiplo = 1, SUM(pedido.piezas_total), 0))+SUM(pedido.PrPz) AS tot_piezas
     FROM (
    SELECT DISTINCT 
    IFNULL(g.cve_gpoart ,a.cve_articulo) AS cve_articulo,
    DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, td.Fol_folio, c.Cve_Clte, c.RazonSocial, IFNULL(th.tipo_negociacion, 'Contado') AS tipo_negociacion, 
    IFNULL(g.des_gpoart, a.des_articulo) AS des_articulo, 
    a.num_multiplo,
    um.mav_cveunimed,
    IFNULL(ccar.Id_Carac, '_SS_') AS Id_Carac,
    IFNULL(SUBSTRING(ccar.Des_Carac, 1, 5), 'SurtR') AS Des_Carac,
    0 AS cajas_total,
    td.Num_cantidad AS piezas_total,
#IF(um.mav_cveunimed = 'XBX', IF(a.num_multiplo = 1, 0, td.Num_cantidad),TRUNCATE((td.Num_cantidad/a.num_multiplo), 0)) AS cajas_total,
#IF(um.mav_cveunimed != 'XBX', (td.Num_cantidad - (a.num_multiplo*TRUNCATE((td.Num_cantidad/a.num_multiplo), 0))), IF(a.num_multiplo = 1, td.Num_cantidad, 0)) AS piezas_total,
IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS PrCaja,
IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS PrPz
FROM td_pedido td
LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
LEFT JOIN Rel_Art_Carac rcar ON rcar.Cve_Articulo = a.cve_articulo
LEFT JOIN c_caracteristicas ccar ON ccar.Id_Carac = rcar.Id_Carac
LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
LEFT JOIN th_pedido th ON th.Fol_folio = td.Fol_folio
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN PRegalado pr ON pr.Docto = td.Fol_folio AND pr.SKU = td.cve_articulo
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
WHERE th.status = 'A' AND th.tipo_negociacion = 'Obsequio'
{$sql_tipo_pedido1} 
{$sql_ruta} 
{$sql_fecha} 
{$sql_search} 
AND th.cve_almac = {$almacen} 
) AS pedido
GROUP BY pedido, cve_articulo , Des_Carac
ORDER BY pedido.cve_articulo, pedido.Des_Carac
) AS grupo_sabores
GROUP BY grupo_sabores.pedido, grupo_sabores.cve_articulo

ORDER BY cve_articulo
";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;

    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $ruta; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $pedido; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Cve_Clte; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $des_articulo; ?></div>

    <?php 

    $j = 5; $k = 5;
    //LIMON:::::500;;;;;TORONJA:::::500
    $des_carac_arr = explode(";;;;;", $Des_Carac);
    for($dc = 0; $dc < count($des_carac_arr); $dc++)
    {
        $des_carac_val = explode(":::::", $des_carac_arr[$dc]);
        for($c = 0; $c < count($sabores_arr); $c++)
        {
            if($des_carac_val[0] == $sabores_arr[$c])
            {
        ?>
        <div cell="<?php echo $columnas[$c+$k].$i; ?>"><?php echo $des_carac_val[1]; ?></div>
        <?php 
            }
        }
        $des_carac_val = array();
        //$j++;
    }
    $des_carac_arr = array();
    $j = count($sabores_arr)+5;
    ?>
        <div cell="<?php echo $columnas[$j].$i; ?>"><?php echo ($cajas_total); ?></div>
        <div cell="<?php echo $columnas[$j+1].$i; ?>"><?php echo ($piezas_total); ?></div>
        <div cell="<?php echo $columnas[$j+2].$i; ?>"><?php echo $PrCaja; ?></div>
        <div cell="<?php echo $columnas[$j+3].$i; ?>"><?php echo $PrPz; ?></div>
        <div cell="<?php echo $columnas[$j+4].$i; ?>"><?php echo $ObCaja; ?></div>
        <div cell="<?php echo $columnas[$j+5].$i; ?>"><?php echo $ObPz; ?></div>
        <div cell="<?php echo $columnas[$j+6].$i; ?>"><?php 
        //echo $tot_cajas+$PrCaja+$ObCaja; 
        if($ObCaja == 0)
            echo $tot_cajas+$PrCaja; 
        else
            echo $ObCaja; 
        ?></div>
        <div cell="<?php echo $columnas[$j+7].$i; ?>"><?php 
        //echo $tot_piezas+$PrPz+$ObPz; 
        if($ObPz == 0)
            echo $tot_piezas+$PrPz; 
        else
            echo $ObPz; 
        ?></div>
        <?php 
        $i++;

    }
  ?>

        <?php /* ?><div cell="E<?php echo $i; ?>"><?php echo $sql; ?></div><?php */ ?>
        <?php if($search == 'SQL0000WMS'){ ?><div cell="E<?php echo $i; ?>"><?php echo $sql; ?></div><?php } ?>


</div>
