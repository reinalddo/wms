<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

    $sheet1 = "Descargas";
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

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Dia O</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Fecha</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Clave Artículo</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Folio</div>
<?php     

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    //if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$charset = mysqli_fetch_array($res_charset)['charset'];
    //mysqli_set_charset($conn , $charset);

    $criterio = $_GET['criterio'];
    $ruta = $_GET['ruta'];
    $diao = $_GET['diao'];
    $fecha_inicio = $_GET['fechaini'];
    $fecha_fin = $_GET['fechafin'];
    $almacen = $_GET['almacen'];

    $SQLFecha = "";

    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        $SQLFecha = " AND DATE(d.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(d.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if ($fecha_inicio == $fecha_fin)
            $SQLFecha = " AND DATE(d.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_inicio)) {
        $SQLFecha = " AND d.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_fin)) {
        $SQLFecha = " AND d.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
    }

    $SQLRuta = "";
    if ($ruta) {
        $SQLRuta = " AND r.cve_ruta = '" . $ruta . "' ";

        if ($ruta == 'todas') {
            $SQLRuta = " AND r.cve_ruta != '' ";
        }
    }

    $SQLDiaO = "";
    if ($diao) {
        $SQLDiaO = " AND d.Diao = '" . $diao . "' ";
    }

    $SQLCriterio = ""; 
    if ($criterio) {
        $SQLCriterio = " AND (a.cve_articulo LIKE '%" . $criterio . "%' OR a.des_articulo LIKE '%" . $criterio . "%' OR d.Folio LIKE '%" . $criterio . "%' OR r.cve_ruta LIKE '%" . $criterio . "%' OR r.descripcion LIKE '%" . $criterio . "%' ) ";
    }


    $sql = "SELECT d.Diao, DATE_FORMAT(d.Fecha, '%d-%m-%Y') AS Fecha, a.cve_articulo, a.des_articulo, d.Cantidad, d.Folio
            FROM Descarga d
            INNER JOIN c_articulo a ON a.cve_articulo = d.Articulo
            INNER JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo 
            INNER JOIN c_almacenp alm ON alm.id = ra.Cve_Almac AND alm.clave = d.IdEmpresa
            INNER JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
            WHERE ra.Cve_Almac = {$almacen} 
            {$SQLFecha} 
            {$SQLRuta} 
            {$SQLDiaO} 
            {$SQLCriterio} 
            ORDER BY Diao DESC
            ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Diao; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Fecha; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $cve_articulo; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $Cantidad; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $Folio; ?></div>
        <?php 
        $i++;

    }
  ?>
    </div>

    <?php /* ?> <div cell="B<?php echo $i; ?>"><?php echo $sql; ?></div> <?php */ ?>
</div>

