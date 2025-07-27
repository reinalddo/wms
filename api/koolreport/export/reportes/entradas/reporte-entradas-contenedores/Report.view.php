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
        margin: 1cm 5cm;
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
    IFNULL(ch.CveLP, IFNULL(ch.Clave_Contenedor, '')) AS ClaveEtiqueta
FROM td_entalmacenxtarima td
    LEFT JOIN c_charolas ch ON ch.Clave_Contenedor = td.ClaveEtiqueta
WHERE td.Fol_Folio = '$folio'
ORDER BY ClaveEtiqueta";


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
<br><br><br><br><br>
<div class="compania">
     Pallet/Contenedor
</div>
<div style="text-align: center; position: relative; width: 9.9cm; left: 5cm;">
  <?php
  BarCode::create(array(
    "format" => "svg", //"svg", "png", "jpg", "html"
    "value" => $ClaveEtiqueta,
    "type" => "TYPE_CODE_128",
    "widthFactor" => 4,
    "height" => 90,
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

<table class="qrs">
    <tr>
        <td class="qrs_td1">
        </td>
        <td class="qrs_td2">
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

