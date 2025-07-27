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
        font-size: 15px;
        width: 12cm;
        height: 0.6cm;
        text-align: center;
        vertical-align: middle;
        background: #000000 !important;
        color: #ffffff !important;
        margin: 0.5cm;
        /*border-right: 1px solid #000;*/
        /*border-bottom: 1px solid #000;*/
        /*padding-top: 1cm;*/
    }
    
    .datos .compania
    {
        font-size: 18px;
        width: 5cm;
        height: 0.6cm;
        text-align: center;
        vertical-align: middle;
        background: #000000 !important;
        color: #ffffff !important;
        margin: 1cm;
        position: relative;
        top: 0;
        /*border-right: 1px solid #000;*/
        /*border-bottom: 1px solid #000;*/
        /*padding-top: 1cm;*/
    }

    .row_bold
    {
        font-size: 12px;
        width: 100%;
        height: 0.5cm;
        text-align: left;
        vertical-align: middle;
        background: #000000 !important;
        color: #ffffff !important;
        margin: 1cm;
        position: relative;
        top: 0;
    }

    .datos
    { 
        position:relative; 
    }

    .datos table
    {
        position: absolute;
        left: 7cm;
        top: 0cm;
        width: 17cm;
    }

    .datos_art
    {
        width: 12cm;
        margin: 0.5cm;
        position: relative;
        top: 0;
    }

    .tr_datos
    {
        background: #000000 !important;
        z-index: 0;
    }

    .qr_clave
    {
        position: absolute;
        left: 13cm;
        top: 0cm;
        text-align: center;
    }
    .qr_lote
    {
        margin: 0.5cm;
    }

    .td_datos
    {
        font-size: 12px;
        text-align: left;
        width: 5cm;
        height: 0.3cm;
        color: #ffffff !important;
        /*margin: 1cm;*/
        padding: 0.1cm;
        position: relative;
        top: 0;
        z-index: 1;
    }

    .qrs
    {
        margin: 0.5cm;
    }
    .qrs .qrs_td1
    {
        width: 10cm;
    }

    .qrs .qrs_td2
    {
        width: 4cm;
    }

    .td_datos1
    {
        width: 3cm;
    }

    .td_datos2
    {
        width: 9cm;
    }
    .info_td
    {
        font-size: 17px !important;
    }
    .info
    {
        position: relative;
        font-size: 18px;
        padding-left: 15px;
        display: inline-block;
        font-family: 'Arial Regular';
    }

    .info4
    {
        width: 5cm;
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


    $cve_cia = $_GET['cve_cia'];
    $folio = $_GET['folio'];
    $oc = $_GET['oc'];
    $proveedor = utf8_encode($_GET['proveedor']);

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    $sql = "SELECT imagen, des_cia, des_direcc, distrito, des_telef, des_email FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

          $sql2 = "
              SELECT DISTINCT
                    tdtar.ClaveEtiqueta AS contenedor,
                    DATE_FORMAT(MAX(tde.fecha_fin), '%d/%m/%Y') AS fecha_ingreso,
                    IFNULL(IFNULL(th.Pedimento_Well, ad.Pedimento), '') as Pedimento,
                    IF(cch.CveLP != '', cch.CveLP,CONCAT('LP', LPAD(CONCAT(cch.IDContenedor,tde.fol_folio), 9, 0))) AS LP
              FROM td_entalmacen tde
                    LEFT JOIN td_entalmacenxtarima tdtar ON tdtar.fol_folio = tde.fol_folio
                    LEFT JOIN th_entalmacen th ON th.Fol_Folio = tde.fol_folio
                    LEFT JOIN th_aduana ad ON ad.num_pedimento = th.id_ocompra
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
          if(@mysqli_num_rows($res2))
          {
          $row2 = mysqli_fetch_array($res2);
          extract($row2);
         }


          $sql1 = "
              SELECT DISTINCT
    td.cve_articulo AS clave,
    a.des_articulo AS descripcion,
    IFNULL(a.cve_codprov, '') AS codigobarras,
    IFNULL(a.control_lotes, 'N') AS band_lote,
    IFNULL(a.Caduca, 'N') AS band_caducidad,
    IFNULL(a.control_numero_series, 'N') AS band_serie,
    td.cve_lote AS lote,
    IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(a.control_lotes, 'N') = 'S', DATE_FORMAT(cl.Caducidad, '%d-%m-%Y'), '') AS caducidad,
    td.Cantidad AS cantidad_recibida,
    IFNULL(th.id_ocompra, '') AS oc,
    IFNULL(ch.CveLP, ch.clave_contenedor) AS ClaveEtiqueta
FROM td_entalmacenxtarima td
    LEFT JOIN td_entalmacen tde ON tde.cve_articulo = td.cve_articulo AND td.cve_lote = tde.cve_lote
    LEFT JOIN th_entalmacen th ON th.Fol_Folio = tde.fol_folio
    LEFT JOIN c_lotes cl ON cl.LOTE = td.cve_lote AND cl.Activo=1 AND cl.cve_articulo = td.cve_articulo
    LEFT JOIN c_articulo a ON a.cve_articulo = td.cve_articulo 
    LEFT JOIN c_charolas ch ON ch.clave_contenedor = td.ClaveEtiqueta
WHERE  th.Fol_Folio = '$folio' AND td.fol_folio = '$folio' AND td.Cantidad > 0 #AND (td.ClaveEtiqueta = '' OR td.ClaveEtiqueta = (SELECT clave_contenedor FROM c_charolas WHERE CveLP = ''))

UNION

SELECT DISTINCT
    td.cve_articulo AS clave,
    a.des_articulo AS descripcion,
    IFNULL(a.cve_codprov, '') AS codigobarras,
    IFNULL(a.control_lotes, 'N') AS band_lote,
    IFNULL(a.Caduca, 'N') AS band_caducidad,
    IFNULL(a.control_numero_series, 'N') AS band_serie,
    td.cve_lote AS lote,
    DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
    td.CantidadRecibida AS cantidad_recibida,
    IFNULL(th.id_ocompra, '') AS oc,
    '' AS ClaveEtiqueta
FROM td_entalmacen td
    LEFT JOIN td_entalmacenxtarima tde ON tde.cve_articulo = td.cve_articulo AND td.cve_lote = tde.cve_lote
    LEFT JOIN th_entalmacen th ON th.Fol_Folio = td.fol_folio
    LEFT JOIN c_lotes cl ON cl.LOTE = td.cve_lote AND cl.Activo=1 AND cl.cve_articulo = td.cve_articulo
    LEFT JOIN c_articulo a ON a.cve_articulo = td.cve_articulo 
WHERE th.Fol_Folio = '$folio' AND td.fol_folio = '$folio' AND td.CantidadRecibida > 0  #AND td.CantidadPedida > 0 
AND CONCAT(td.cve_articulo, td.cve_lote) NOT IN (SELECT CONCAT(tde.cve_articulo, tde.cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$folio')
ORDER BY ClaveEtiqueta";//LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo


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
                $oc = $datos["oc"];
                $ClaveEtiqueta = $datos["ClaveEtiqueta"];

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

    ?>

<br>
<div class="compania">
     License Plate Control AssistPro ADL
</div>

<br>


<div class="row datos">

<div class="compania">
     Pallet/Contenedor
</div>

<div style="text-align: center; position: relative; width: 9.9cm; left: 1cm;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $ClaveEtiqueta,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 3,
    "height" => 50,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<span style="font-size: 18px;letter-spacing: 1.5px;"><?php echo $ClaveEtiqueta; ?></span>
</div>

    <table>
        <tr>
            <td class="info info4 bold">Fecha Ingreso: </td>
            <td class="info info8_2 bold"><?php echo $fecha_ingreso; ?></td>
            <td class="info info4 bold">Folio:</td>
            <td class="info info8_2 bold"><?php echo $folio; ?></td>
        </tr>
        <tr>
            <td class="info info4 bold">Proveedor: </td>
            <td class="info info8_2 bold"><?php echo $proveedor; ?></td>
            <td class="info info4 bold">OC:</td>
            <td class="info info8_2 bold"><?php echo $oc; ?></td>
        </tr>
        <?php 
        if($instancia == 'welldex')
        {
        ?>
        <tr>
            <td class="info info4 bold"></td>
            <td class="info info8_2 bold"></td>
            <td class="info info4 bold">Pedimento: </td>
            <td class="info info8_2 bold"><?php echo $Pedimento; ?></td>
        </tr>
        <?php 
        }
        ?>
    </table>
</div>
<br>
<div class="row datos_art">

    <table>

        <tr class="tr_datos">
            <td class="td_datos td_datos1 info info8_23">Clave</td>
            <td class="td_datos td_datos2 info info8_23">Descripción</td>
        </tr>

        <tr>
            <td class="info info_td td_datos1"><?php echo $clave; ?></td>
            <td class="info info_td td_datos2"><?php echo $descripcion; ?></td>
        </tr>

        <tr>
            <td style="padding: 0.5cm;">
                <div class="row">
                    <div class="info info8_23 bold">Lote | Serie: </div>
                </div>
                <div class="row">
                    <div class="info info_td"><?php echo $lote; ?></div>
                </div>
            </td>
            <td style="padding: 0.5cm;">
                <table>
                <tr>
                    <td class="info info8_23 bold" style="width: 3cm;">Caducidad: </td>
                    <td class="info info8_23 bold" style="width: 3cm;">Cantidad: </td>
                </tr>
                <tr>
                    <td class="info info_td" style="width: 3cm;"><?php echo $caducidad; ?></td>
                    <td class="info info_td" style="width: 3cm;"><?php echo $cantidad_recibida; ?></td>
                </tr>
                </table>
            </td>
        </tr>


<div class="qr_clave">
<div class="info" style="text-align: left !important; left: 0; padding-left: 0 !important;">Clave</div><br>
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $clave,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 3,
    "height" => 50,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<span style="font-size: 18px;letter-spacing: 1.5px;"><?php echo $clave; ?></span>

</div>


    </table>
</div>

<table class="qrs">
    <tr>
        <td class="qrs_td1">
<div class="info" style="text-align: left !important; left: 0; padding-left: 0 !important;">Lote</div><br>
<div>
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $lote,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 3,
    "height" => 50,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<span style="font-size: 18px;letter-spacing: 1.5px;"><?php echo $lote; ?></span>
</div>
        </td>
        <td class="qrs_td2">
<?php 
/*
?>
<div class="info" style="text-align: center; left: 0; padding-left: 0 !important;">Cantidad</div><br>
<div>
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $cantidad_recibida,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 3,
    "height" => 50,
    "color" => "black", //"{{color string}}" for html and svg, array(R, G, B) for jpg and png
  ));
  ?>
  <br>
<span style="font-size: 18px;letter-spacing: 1.5px;"><?php echo $cantidad_recibida; ?></span>
</div>
<?php 
*/
?>
        </td>
        <td style="text-align:right;">
            <img src="<?php echo ''.$imagen; ?>" alt="" height="70" style="float: right;">
        </td>
    </tr>
</table>

<div class="page-break"></div>
<?php 

}
?>

</div>



</body>
</html>

