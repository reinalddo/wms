<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Visitas Sin Ventas";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Ruta</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cliente</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Nombre Cliente</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Motivo</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    //if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$charset = mysqli_fetch_array($res_charset)['charset'];
    //mysqli_set_charset($conn , $charset);

    $almacen= $_GET['almacen'];
    $ruta= $_GET['ruta'];
    $diao = $_GET['diao'];

    $sql = "SELECT DATE_FORMAT(n.Fecha, '%d-%m-%Y') AS Fecha, r.cve_ruta, n.Cliente, d.razonsocial, m.Motivo, d.Cve_Clte
            FROM Noventas n
            LEFT JOIN t_ruta r ON r.ID_Ruta = n.RutaId
            LEFT JOIN c_destinatarios d ON d.id_destinatario = n.Cliente 
            LEFT JOIN MotivosNoVenta m ON m.IdMot = n.MotivoId
            LEFT JOIN c_almacenp a ON a.clave = n.IdEmpresa
            WHERE a.id = {$almacen} AND r.cve_ruta = '{$ruta}' AND n.DiaO = {$diao} 
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Fecha; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $cve_ruta; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $Cliente; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo utf8_decode($razonsocial); ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Motivo; ?></div>
        <?php 
        $i++;

    }
  ?>

    <?php /* ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>
</div>

