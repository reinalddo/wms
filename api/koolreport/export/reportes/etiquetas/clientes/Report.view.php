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
    font-family: 'Arial';
    font-style: normal;
    font-weight: bolder;
    src: local('Arial Regular'), url('ARIAL.woff') format('woff');
    }
    .grayscale {
        -webkit-filter: grayscale(1);
        filter: grayscale(1);
    }
    .compania
    {
        font-size: 14px;
        width: 100%;
        /*background: #000000 !important;
        color: #ffffff !important;*/
        text-align: center;
        position: absolute;
        margin-top: 105px;
    }

    .almacen
    {
        position: relative;
        text-align: left;
        padding-left: 160px;
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

    $cve_clientes = $_GET['cve_clientes'];
    $compania = $_GET['compania'];
    //$almacen = $_GET['almacen'];
/*
    $sql = "SELECT nombre FROM c_almacenp WHERE id = $almacen";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $nombre_almacen = $nombre;
*/

    $sql = "SELECT imagen FROM c_compania WHERE cve_cia = $compania";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    //$nombre_almacen = $nombre;

    ?>


<?php 
$lps_array = explode(",", $cve_clientes);
for($i = 0; $i < count($lps_array); $i++)
{
    $cve_cliente = $lps_array[$i];
    $sql = "SELECT DISTINCT RazonComercial FROM c_cliente WHERE Cve_Clte = '{$cve_cliente}'";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $RazonComercial = mysqli_fetch_array($res)["RazonComercial"];

?>
<div class="compania">
    <?php echo $RazonComercial; ?>
</div>

<?php 
if($imagen[0] != '/')
    $imagen = '/'.$imagen;
if($_SERVER['HTTP_HOST'] == 'yorica.assistpro-adl.com')
    $imagen = '/img/compania/logo_etiqueta.jpeg';

?>
<div style="position: relative;">
<div style="position: absolute; left: 5px; top: 10px;"><img class="" src="<?php echo $imagen; ?>" width="30"></div>
<div style="text-align: center;position: relative; width: 8.08cm; top: 50px; font-size: 9px;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $lps_array[$i],
    "type" => "TYPE_CODE_128",
    "widthFactor" => 2,
    "height" => 35,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $lps_array[$i]; ?>
</div>
</div>
<br><br><br><br><br><br>
<?php /* ?>
<div class="compania almacen">
    <?php echo $nombre_almacen; ?>
</div>

<?php 
*/
}
?>


</div>


</body>
</html>

