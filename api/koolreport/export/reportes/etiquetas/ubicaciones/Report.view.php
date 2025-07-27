<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;
    use \koolreport\barcode\BarCode;

include '../../../../../../config.php';
?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Reporte de Entrada</title>
</head>
<body style="margin: 10px;">
<style>
    @font-face {
    font-family: 'Arial Regular';
    font-style: normal;
    font-weight: normal;
    src: local('Arial Regular'), url('ARIAL.woff') format('woff');
    }
    .compania
    {
        font-size: 18px;
        width: 100%;
        background: #000000 !important;
        color: #ffffff !important;
        text-align: center;
        position: absolute;
    }

    .almacen
    {
        position: relative;
        text-align: left;
        padding-left: 160px;
    }

    .info
    {
        position: relative;
        font-size: 10px;
        padding-left: 15px;
        display: inline-block;
        font-family: 'Arial Regular';
    }

    .info4
    {
        width: 3.5cm;
        display: inline-block;
    }

    .info2
    {
        width: 2cm;
        display: inline-block;
    }
    .info8
    {
        width: 8.5cm;
        display: inline-block;
    }
    .info8_2
    {
        width: 8.5cm;
        display: inline-block;
    }

    .info8_23
    {
        width: 6cm;
        display: inline-block;
    }

    .info8_21
    {
        width: 8.5cm;
        display: inline-block;
    }

    .bold
    {
        font-weight: bold;
    }
</style>

<?php 
function cortar_string ($string, $largo) { 
   $marca = "..."; 
 
   if (strlen($string) > $largo) { 
        
       $string = wordwrap($string, $largo, $marca); 
       $string = explode($marca, $string); 
       $string = $string[0]; 
   } 
   return $string; 
} 
?>

<div class="report-content" >
  <?php 

if(isset($_GET['BL']))
{
    $BL = $_GET['BL'];

?>

<div style="text-align: center;position: relative; width: 4in; top: 1in; font-size: 20px; margin-left: 0.5in;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $BL,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 2.7,
    "height" => 70,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $BL; ?>
</div>
<?php  
}
else if(isset($_GET['ubicacion']))
{
    $ubicacion = json_decode($_GET['ubicacion'], true);

    foreach($ubicacion as $codigocsd)
    {
        
?>
<div style="text-align: center;position: relative; width: 4in; top: 1in; font-size: 20px; margin-left: 0.5in;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $codigocsd["bl"],
    "type" => "TYPE_CODE_128",
    "widthFactor" => 2.7,
    "height" => 70,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $codigocsd["bl"]; ?>
</div>
<div class="page-break"></div>
<?php
    }

}
else if(isset($_GET['zona']) && isset($_GET['rack']))
{
    $zona = $_GET['zona'];
    $rack = $_GET['rack'];
    $nivel = $_GET['nivel'];
    $seccion = $_GET['seccion'];
    $posicion = $_GET['posicion'];

    //AND cve_pasillo = 'F'

    $SQL_rack = "";
    if($rack != '')
        $SQL_rack = " AND cve_rack = '{$rack}' ";

    $SQL_nivel = "";
    if($nivel != '')
        $SQL_nivel = " AND cve_nivel = '{$nivel}' ";

    $SQL_seccion = "";
    if($seccion != '')
        $SQL_seccion = " AND Seccion = '{$seccion}' ";

    $SQL_posicion = "";
    if($posicion != '')
        $SQL_posicion = " AND Ubicacion = '{$posicion}' ";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT CodigoCSD as bl FROM c_ubicacion WHERE cve_almac = {$zona} {$SQL_rack} {$SQL_nivel} {$SQL_seccion} {$SQL_posicion} ORDER BY CodigoCSD ASC;";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;}

    while($codigocsd = mysqli_fetch_assoc($res))
    {
?>
<div style="text-align: center;position: relative; width: 4in; top: 1in; font-size: 20px; margin-left: 0.5in;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $codigocsd["bl"],
    "type" => "TYPE_CODE_128",
    "widthFactor" => 2.7,
    "height" => 70,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $codigocsd["bl"]; ?>
</div>
<div class="page-break"></div>
<?php
    }

}

?>



</div>


</body>
</html>

