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

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $array_lps = $_GET['LP'];
    $almacen = $_GET['almacen'];
    $instancia = $_GET['instancia'];

    $sql = "SELECT nombre FROM c_almacenp WHERE id = $almacen";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $nombre_almacen = $nombre;

    ?>

<?php 
$lps_array = explode(",", $array_lps);
for($i = 0; $i < count($lps_array); $i++)
{
?>
<br>
<div class="compania">
    License Plate Control   AssistPro ADL
</div>

<br>

<div style="text-align: center;position: relative; width: 10.16cm; top: <?php if($instancia == 'foam') echo '300px';else echo '100px'; ?>; left: 50px; font-size: 20px;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $lps_array[$i],
    "type" => "TYPE_CODE_128",
    "widthFactor" => 1.5,
    "height" => 70,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $lps_array[$i]; ?>
</div>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<?php 
if($instancia == 'foam')
{
?>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br>
<?php 
}
?>
<div class="compania almacen">
    <?php echo $nombre_almacen; ?>
</div>
<div class="page-break"></div>
<?php 
}
?>


</div>


</body>
</html>

