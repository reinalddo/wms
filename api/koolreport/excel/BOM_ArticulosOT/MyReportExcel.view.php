<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';

$articulo = "";
if(isset($_GET['txtArticuloParte']))
    $articulo = $_GET['txtArticuloParte'];

    $sheet1 = "Comp {$articulo}";
?>
<meta charset="UTF-8">
<meta name="description" content="Free Web tutorials">
<meta name="keywords" content="Excel,HTML,CSS,XML,JavaScript">
<meta name="creator" content="John Doe">
<meta name="subject" content="subject1">
<meta name="title" content="title1">
<meta name="category" content="category1">

<div sheet-name="<?php echo substr($sheet1, 0, 15); ?>">

                                  


    <?php
    $styleArray = [
        'font' => [
            'name' => 'Calibri', //'Verdana', 'Arial'
            'size' => 14,
            'bold' => true
        ]
    ];
?>

    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'>Producto Compuesto</div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Producto</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Cantidad Requerida</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Unidad de Medida</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sqlArticulo = "";
    if($articulo)
        $sqlArticulo = " WHERE t_artcompuesto.Cve_ArtComponente='{$articulo}' ";

    $sql = "SELECT
                    t_artcompuesto.Cve_Articulo,
                    t_artcompuesto.Cve_ArtComponente,
                    t_artcompuesto.Cantidad,
                    t_artcompuesto.Status,
                    t_artcompuesto.Activo,
                    t_artcompuesto.cve_umed,
                    CONVERT(CAST(c_articulo.des_articulo AS BINARY) USING utf8) AS des_articulo,
                    c_articulo.control_peso,
                    c_unimed.des_umed
                    FROM
                    t_artcompuesto
                    INNER JOIN c_articulo ON t_artcompuesto.Cve_Articulo = c_articulo.cve_articulo
                    LEFT  JOIN c_unimed ON t_artcompuesto.cve_umed = c_unimed.cve_umed
                    {$sqlArticulo} 
                    ORDER BY Cve_ArtComponente
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);
        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cve_ArtComponente; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $Cve_Articulo; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $Cantidad; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $cve_umed; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>