<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Visitados";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>DiaO</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha DiaO</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Código</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>H.Inicial</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>H.Final</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tiempo Traslado</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Tiempo Servicio</div>
    <div cell="K1" excelStyle='<?php echo json_encode($styleArray); ?>'>Visita</div>
    <div cell="L1" excelStyle='<?php echo json_encode($styleArray); ?>'>Programado</div>
    <div cell="M1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cerrado</div>
    <div cell="N1" excelStyle='<?php echo json_encode($styleArray); ?>'>Vendedor</div>
    <div cell="O1" excelStyle='<?php echo json_encode($styleArray); ?>'>Latitud</div>
    <div cell="P1" excelStyle='<?php echo json_encode($styleArray); ?>'>Longitud</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    //if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$charset = mysqli_fetch_array($res_charset)['charset'];
    //mysqli_set_charset($conn , $charset);

    $almacen= $_GET['almacen'];
    $ruta= $_GET['ruta'];
    $diao = $_GET['diao'];

    $SQLDiaO = ""; 
    if($diao)
    {
        $SQLDiaO = " AND BitacoraTiempos.DiaO = '{$diao}' ";
    }

    $SQLRuta = ""; 
    if($ruta)
    {
        $SQLRuta = " AND t_ruta.cve_ruta = '{$ruta}' ";
    }

    $sql = "
    SELECT DISTINCT
        BitacoraTiempos.Codigo as codigo,
      BitacoraTiempos.DiaO as diaOpB,
      BitacoraTiempos.Descripcion as descripcion,
      CONVERT(IFNULL(c_cliente.RazonComercial, '') USING utf8) as Responsable,
      #'' as Responsable,
      CONVERT(IFNULL(c_destinatarios.razonsocial, '') USING utf8) as nombreComercial,
      #'' as nombreComercial,
      DATE_FORMAT(DiasO.Fecha, '%d-%m-%Y') as fechaDO,
      BitacoraTiempos.HI AS HI,
      IF(BitacoraTiempos.Visita = 1, BitacoraTiempos.HF, BitacoraTiempos.HI) AS HF,
      DATE_FORMAT(BitacoraTiempos.HI, '%d-%m-%Y %H:%i:%S') as horaIni,
      DATE_FORMAT(IF(BitacoraTiempos.Visita = 1, BitacoraTiempos.HF, BitacoraTiempos.HI), '%d-%m-%Y %H:%i:%S') as horaFin,
      IFNULL(REPLACE(BitacoraTiempos.HT, '-', ''), '00:00:00') as tiempoTraslado,

      #REPLACE(BitacoraTiempos.TS, '-', '') as tiempoServicio,
      DATE_FORMAT(IF(BitacoraTiempos.Visita = 1, SEC_TO_TIME((TIMESTAMPDIFF(SECOND, BitacoraTiempos.HI, BitacoraTiempos.HF))), '00:00:00'), '%H:%i:%S') as tiempoServicio,
      IF(BitacoraTiempos.Visita = 1, 1, 0) AS visita,
      IF(BitacoraTiempos.Programado = 1, 1, 0) AS programado,
      t_ruta.cve_ruta as rutaName,
      IF(BitacoraTiempos.Cerrado = 1, 1, 0) AS cerrado,
      BitacoraTiempos.IdVendedor as vendedorID,
      t_vendedores.Nombre as Vendedor,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      BitacoraTiempos.Tip as tip,
      c_cliente.Cve_Clte,
      BitacoraTiempos.latitude as latitud,
      BitacoraTiempos.longitude as longitud,
      BitacoraTiempos.pila as pila
      
      FROM BitacoraTiempos
      
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = BitacoraTiempos.RutaId
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = BitacoraTiempos.IdVendedor
      LEFT JOIN c_almacenp al ON al.id= '{$almacen}' 
      INNER JOIN DiasO on DiasO.DiaO = BitacoraTiempos.DiaO AND DiasO.IdEmpresa = al.clave AND DiasO.RutaId = t_ruta.ID_Ruta
      LEFT JOIN c_destinatarios on c_destinatarios.id_destinatario = BitacoraTiempos.Codigo
      LEFT JOIN c_cliente on c_destinatarios.Cve_Clte = c_cliente.Cve_Clte
      
      WHERE 1 {$SQLDiaO} {$SQLRuta} 
      ORDER BY HI
    ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $rutaName; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $diaOpB; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $fechaDO; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo utf8_decode($descripcion); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Cve_Clte; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo utf8_decode($Responsable); ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo $horaIni; ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo $horaFin; ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $tiempoTraslado; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $tiempoServicio; ?></div>
        <div cell="K<?php echo $i; ?>"><?php echo $visita; ?></div>
        <div cell="L<?php echo $i; ?>"><?php echo $programado; ?></div>
        <div cell="M<?php echo $i; ?>"><?php echo $cerrado; ?></div>
        <div cell="N<?php echo $i; ?>"><?php echo $Vendedor; ?></div>
        <div cell="O<?php echo $i; ?>"><?php echo $latitud; ?></div>
        <div cell="P<?php echo $i; ?>"><?php echo $longitud; ?></div>
        <?php 
        $i++;

    }
  ?>

    <?php /* ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>
</div>

<?php 
$sheet2 = "No Visitados";
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
?>

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Codigo</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Dirección</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Colonia</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Postal</div>
    <div cell="G1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ciudad</div>
    <div cell="H1" excelStyle='<?php echo json_encode($styleArray); ?>'>Estado</div>
    <div cell="I1" excelStyle='<?php echo json_encode($styleArray); ?>'>Latitud</div>
    <div cell="J1" excelStyle='<?php echo json_encode($styleArray); ?>'>Longitud</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $almacen= $_GET['almacen'];
    $ruta   = $_GET['ruta'];
    $diao   = $_GET['diao'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
/*
    $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo, r.cve_ruta as Ruta,
                        d.Cve_Clte AS Cliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida, pr.Docto AS Folio
                FROM PRegalado pr 
                LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
                LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
                LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
                LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
                WHERE  al.id = '{$almacen}' AND IFNULL(d.razonsocial, '') != '' AND r.cve_ruta = '{$ruta}' AND pr.DiaO = '{$diao}'
                ORDER BY des_articulo
            ";
*/
/*
    $sql = "SELECT  r.cve_ruta, d.Cve_Clte, d.id_destinatario, d.razonsocial, d.direccion, d.colonia, d.postal, d.ciudad, d.estado, d.latitud, d.longitud
FROM RelDayCli rdc
LEFT JOIN c_destinatarios d ON d.id_destinatario = rdc.Id_Destinatario
LEFT JOIN t_ruta r ON r.ID_Ruta = rdc.Cve_Ruta
WHERE r.cve_ruta = '{$ruta}' AND rdc.Id_Destinatario NOT IN (SELECT Codigo FROM BitacoraTiempos WHERE Visita = 1 AND DiaO = '{$diao}')";
*/

$sql = "SELECT  r.cve_ruta, d.Cve_Clte, d.id_destinatario, d.razonsocial, d.direccion, d.colonia, d.postal, d.ciudad, d.estado, d.latitud, d.longitud
FROM TH_SecVisitas rdc
LEFT JOIN c_destinatarios d ON d.id_destinatario = rdc.CodCli
LEFT JOIN t_ruta r ON r.ID_Ruta = rdc.RutaId
INNER JOIN DiasO di ON rdc.Fecha = di.Fecha AND r.ID_Ruta = di.RutaId AND di.DiaO = '{$diao}' 
WHERE r.cve_ruta = '{$ruta}' AND rdc.CodCli NOT IN (SELECT Codigo FROM BitacoraTiempos WHERE Visita = 1 AND DiaO = '{$diao}')";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
    ?>
        <div cell="A<?php echo $i; ?>"><?php echo $cve_ruta; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Cve_Clte; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo utf8_decode($razonsocial); ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo utf8_decode($direccion); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo utf8_decode($colonia); ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $postal; ?></div>
        <div cell="G<?php echo $i; ?>"><?php echo utf8_decode($ciudad); ?></div>
        <div cell="H<?php echo $i; ?>"><?php echo utf8_decode($estado); ?></div>
        <div cell="I<?php echo $i; ?>"><?php echo $latitud; ?></div>
        <div cell="J<?php echo $i; ?>"><?php echo $longitud; ?></div>
        <?php 
        $i++;

    }
  ?>


</div>