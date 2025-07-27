<?php
    use \koolreport\excel\Table;
    use \koolreport\excel\PivotTable;
    use \koolreport\excel\BarChart;
    use \koolreport\excel\LineChart;

    include '../../../../config.php';
    $id_lista = $_GET['id_lista'];

    $sheet1 = "Lista De Precios ".$id_lista;
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
    <div cell="A1" excelStyle='<?php echo json_encode($styleArray); ?>'><?php if(isset($_GET['tipo_servicio'])) echo "Servicio";else echo "Artículo"; ?></div>
    <div cell="B1" excelStyle='<?php echo json_encode($styleArray); ?>'>Precio Mínimo</div>
    <div cell="C1" excelStyle='<?php echo json_encode($styleArray); ?>'>Precio Máximo</div>
    <div cell="D1" excelStyle='<?php echo json_encode($styleArray); ?>'>Comisión %</div>
    <div cell="E1" excelStyle='<?php echo json_encode($styleArray); ?>'>Comisión $</div>
    <div cell="F1" excelStyle='<?php echo json_encode($styleArray); ?>'>Descripción</div>

<?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql = "SELECT l.Cve_Articulo, a.des_articulo, l.PrecioMin, l.PrecioMax, l.ComisionPor, l.ComisionMon 
            FROM detallelp l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Cve_Articulo
            WHERE l.ListaId = $id_lista
    ";
    if(isset($_GET['tipo_servicio']))
      $sql = "SELECT l.Cve_Articulo, a.Des_Servicio AS des_articulo, l.PrecioMin, l.PrecioMax, l.ComisionPor, l.ComisionMon
              FROM detallelp l 
              LEFT JOIN c_servicios a ON a.Cve_Servicio= l.Cve_Articulo
              WHERE l.ListaId = $id_lista
    ";


    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    $i = 2;


    while ($row = mysqli_fetch_array($res)) {

        extract($row);

        ?>
        <div cell="A<?php echo $i; ?>"><?php echo $Cve_Articulo; ?></div>
        <div cell="B<?php echo $i; ?>"><?php echo $PrecioMin; ?></div>
        <div cell="C<?php echo $i; ?>"><?php echo $PrecioMax; ?></div>
        <div cell="D<?php echo $i; ?>"><?php echo $ComisionPor; ?></div>
        <div cell="E<?php echo $i; ?>"><?php echo $ComisionMon; ?></div>
        <div cell="F<?php echo $i; ?>"><?php echo $des_articulo; ?></div>
        <?php 
        $i++;

    }
  ?>

    
</div>