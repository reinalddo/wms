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
        font-size: 12px;
        width: 100%;
        background: #000000 !important;
        color: #ffffff !important;
        text-align: left;
        padding-left: 80px;
    }

    .info
    {
        position: relative;
        font-size: 10px;
        padding-left: 15px;
        display: inline-block;
        font-family: 'Arial Regular';
    }

    .info5
    {
        width: 6cm;
        left: -60px;
        display: inline-block;
        text-align: left;
        position: relative;
        font-size: 8px;
    }

    .info4
    {
        width: 3.5cm;
        display: inline-block;
    }

    .info_folio
    {
        position: relative;
        left: -25px;
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

    .lote_font_tit
    {
        font-size: 11pt;
    }
    .lote_font_desc, .font_text_cantidad
    {
        font-size: 18pt;
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

    $cve_cia = 1;//$_GET['cve_cia'];
    $folio = $_GET['folio'];
    $unidad = $_GET['unidad'];
    $proveedor = utf8_encode($_GET['proveedor']);
/*
    $sql = "SELECT imagen, des_cia, des_direcc, distrito, des_telef, des_email FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $compania = $des_cia;
*/

          $sql2 = "
              SELECT IFNULL(Fol_OEP, Fol_Folio) AS folio_factura FROM th_entalmacen WHERE Fol_Folio = '$folio'
          ";
          $res2 = mysqli_query($conn, $sql2);
          if(!$res2)
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          $row2_fact = mysqli_fetch_array($res2);
          extract($row2_fact);


          $sql2 = "
              SELECT DISTINCT
                    tdtar.ClaveEtiqueta AS contenedor,
                    DATE_FORMAT(MAX(tde.fecha_fin), '%d/%m/%Y') AS fecha_ingreso,
                    IF(cch.CveLP != '', cch.CveLP,CONCAT('LP', LPAD(CONCAT(cch.IDContenedor,tde.fol_folio), 9, 0))) AS LP
              FROM td_entalmacen tde
                    LEFT JOIN td_entalmacenxtarima tdtar ON tdtar.fol_folio = tde.fol_folio
                    LEFT JOIN c_charolas cch ON cch.clave_contenedor = tdtar.ClaveEtiqueta
              WHERE tde.fol_folio = '$folio' #AND cch.CveLP != '' 
              GROUP BY contenedor
          ";
          //$sql_lp_charolas = $sql2;

          // hace una llamada previa al procedimiento almacenado Lis_Facturas
          //$res  = mysqli_query($conn, $sql1);
          $res2 = mysqli_query($conn, $sql2);
          //if(!$res || !$res2)
          if(!$res2)
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          $row2 = mysqli_fetch_array($res2);
          extract($row2);

$sql_distinct = "DISTINCT"; $sql_union = ""; if($unidad == 1) {$sql_distinct = "";$sql_union = " ALL ";}

          $sql1 = "
              SELECT {$sql_distinct}
    td.cve_articulo AS clave,
    a.des_articulo AS descripcion,
    IFNULL(a.cve_codprov, '') AS codigobarras,
    IFNULL(a.control_lotes, 'N') AS band_lote,
    IFNULL(a.Caduca, 'N') AS band_caducidad,
    IFNULL(a.control_numero_series, 'N') AS band_serie,
    IF(IFNULL(cl.Lote_Alterno, '') = '', td.cve_lote, cl.Lote_Alterno) AS lote,
    #td.cve_lote AS lote,
    DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
    td.Cantidad AS cantidad_recibida,
    td.ClaveEtiqueta
FROM td_entalmacenxtarima td
    LEFT JOIN td_entalmacen tde ON tde.cve_articulo = td.cve_articulo AND td.cve_lote = tde.cve_lote
    LEFT JOIN c_lotes cl ON cl.LOTE = td.cve_lote AND cl.Activo=1 AND cl.cve_articulo = td.cve_articulo
    LEFT JOIN c_articulo a ON a.cve_articulo = td.cve_articulo 
WHERE  td.fol_folio = '$folio' AND td.Cantidad > 0 AND (td.ClaveEtiqueta = '' OR td.ClaveEtiqueta = (SELECT clave_contenedor FROM c_charolas WHERE CveLP = ''))

UNION {$sql_union}

SELECT {$sql_distinct}
    td.cve_articulo AS clave,
    a.des_articulo AS descripcion,
    IFNULL(a.cve_codprov, '') AS codigobarras,
    IFNULL(a.control_lotes, 'N') AS band_lote,
    IFNULL(a.Caduca, 'N') AS band_caducidad,
    IFNULL(a.control_numero_series, 'N') AS band_serie,
    IF(IFNULL(cl.Lote_Alterno, '') = '', td.cve_lote, cl.Lote_Alterno) AS lote,
    #td.cve_lote AS lote,
    DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
    td.CantidadRecibida AS cantidad_recibida,
    '' AS ClaveEtiqueta
FROM td_entalmacen td
    LEFT JOIN td_entalmacenxtarima tde ON tde.cve_articulo = td.cve_articulo AND td.cve_lote = tde.cve_lote
    LEFT JOIN c_lotes cl ON cl.LOTE = td.cve_lote AND cl.Activo=1 AND cl.cve_articulo = td.cve_articulo
    LEFT JOIN c_articulo a ON a.cve_articulo = td.cve_articulo 
WHERE td.fol_folio = '$folio' AND td.CantidadRecibida > 0  #AND td.CantidadPedida > 0 
AND CONCAT(td.cve_articulo, td.cve_lote) NOT IN (SELECT CONCAT(tde.cve_articulo, tde.cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$folio')
ORDER BY ClaveEtiqueta
";//LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo


    $folio = $folio_factura;
            $res  = mysqli_query($conn, $sql1);
            while(($row = mysqli_fetch_array($res, MYSQLI_ASSOC))) 
            {
                $rows[] = $row;
                $datos = $row;

                $clave          = $row['clave'];
                $descripcion    = $row['descripcion'];
                $band_serie     = $row['band_serie'];
                $band_lote      = $row['band_lote'];
                $band_caducidad = $row['band_caducidad'];
                $lote = $datos["lote"];
                //$serie = $datos["serie"];
                $caducidad = $datos["caducidad"];
                $cantidad_recibida = $datos["cantidad_recibida"];

                if($band_serie == 'S')
                {
                    //$lote = $serie;
                    //$lote = "";
                    $caducidad = "";
                }
                else if($band_lote == 'S')
                {
                    $serie = "";
                    if($band_caducidad == 'N') $caducidad = "";
                }
                else
                {
                  $lote = "";
                  $serie = "";
                  $caducidad = "";
                }



if($unidad == 1)
{
for($i = 0; $i < $cantidad_recibida; $i++)
{
    ?>

<div class="compania">
    License Plate Control   AssistPro ADL
</div>

<br>
<div class="row">
    <div class="info info4 bold">Fecha Ingreso: </div>
    <div class="info info4"><?php echo $fecha_ingreso; ?></div>
    <div class="info_folio info info2 bold">Folio:</div>
    <div class="info5"><?php echo substr($folio, 0, 20); ?></div>
</div>
<div class="row">
    <div class="info info4 bold">Proveedor: </div>
    <div class="info info8"><?php echo $proveedor; ?></div>
</div>
<br>

<div class="row">
<?php /* ?>    <div class="info info4 bold">Clave Artículo: </div><?php */ ?>
<div class="info info4 bold"></div>
    <div class="info info8_2 bold">Descripción:</div>
</div>

<div class="row">
    <?php /* ?><div class="info info4"><?php echo $clave; ?></div><?php */ ?>
    <div class="info info4"></div>
    <div class="info info8_21"><?php echo $descripcion; ?></div>
</div>

<div class="row">
    <div class="info info8_23 lote_font_tit bold">Lote | Serie: </div>
    <div class="info info2 bold">Caducidad: </div>
    <div class="info info2 bold">Cantidad: </div>
</div>

<div class="row">
    <div class="info info8_23 lote_font_desc"><?php echo $lote; ?></div>
    <div class="info info2"><?php echo $caducidad; ?></div>
    <div class="info info2 font_text_cantidad"><?php echo 1;//echo $cantidad_recibida; ?></div>
</div>


<div style="text-align: center; position: relative; width: 9.9cm; font-size: 25px;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $clave,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 1.1,
    "height" => 25,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $clave; ?>
</div>
<?php 
}
}
else 
{
?>
<div class="compania">
    License Plate Control   AssistPro ADL
</div>

<br>
<div class="row">
    <div class="info info4 bold">Fecha Ingreso: </div>
    <div class="info info4"><?php echo $fecha_ingreso; ?></div>
    <div class="info_folio info info2 bold">Folio:</div>
    <div class="info5"><?php echo $folio; ?></div>
</div>
<div class="row">
    <div class="info info4 bold">Proveedor: </div>
    <div class="info info8"><?php echo $proveedor; ?></div>
</div>
<br>

<div class="row">
<?php /* ?>    <div class="info info4 bold">Clave Artículo: </div><?php */ ?>
<div class="info info4 bold"></div>
    <div class="info info8_2 bold">Descripción:</div>
</div>

<div class="row">
    <?php /* ?><div class="info info4"><?php echo $clave; ?></div><?php */ ?>
    <div class="info info4"></div>
    <div class="info info8_21"><?php echo $descripcion; ?></div>
</div>

<div class="row">
    <div class="info info8_23 lote_font_tit bold">Lote | Serie: </div>
    <div class="info info2 bold">Caducidad: </div>
    <div class="info info2 bold">Cantidad: </div>
</div>

<div class="row">
    <div class="info info8_23 lote_font_desc"><?php echo $lote; ?></div>
    <div class="info info2"><?php echo $caducidad; ?></div>
    <div class="info info2 font_text_cantidad"><?php echo $cantidad_recibida; ?></div>
</div>


<div style="text-align: center; position: relative; width: 9.9cm; font-size: 25px;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $clave,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 1.1,
    "height" => 25,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<?php echo $clave; ?>
</div>

<?php 
}
}
?>


</div>


</body>
</html>

