<?php
include '../../../app/load.php';

session_start();

/*Asegurarse de que se reciben las variables*/
if(!isset($_SESSION["id_ubicacion"])){
    exit;
}


$db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$db->set_charset('utf8');
$sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $_SESSION['cve_cia']);
$query = $db->query($sql);
$data = $query->fetch_object();
$companyName = $data->nombre;
$companyAddress = $data->direccion;
$url = $_SERVER['DOCUMENT_ROOT']."/img/compania/";
$companyLogo = $url.$data->logo;

$ga = new \Almacen\Almacen();

$data = (array) $ga->loadUbicacionesDeZonas($_SESSION["zona"],$_SESSION["rack"], "", $_SESSION['id_almacen'], "", "", "", "", $_SESSION["nivel"], $_SESSION["seccion"], $_SESSION["posicion"]);

foreach ($data as $d) {   
   

        $contentArr[] = "
        <table>
            <tbody>
                <tr>
                    <td style=\"width: 384px; height: 50px; text-align: center;\"></td>
                </tr>
				<tr>
                    <td style=\"width: 384px; height: 50px; text-align: center;\"></td>
                </tr>
               
                <tr>
                    <td align='center' style=\"width: 384px; height: 60px;\">&nbsp;&nbsp;&nbsp;&nbsp;
                    <div class=\"barcodecell\"><barcode code=\"" . $d["ubicacion"] ."\" type=\"C128A\" class=\"barcode\" /></div>
                    <div style=\"width: 384px; height: 50px; text-align: center;\">" . $d["ubicacion"] ."</div>
					</td>
                </tr>
                <tr>
                    <td style=\"text-align: right;\">&nbsp;</td>
                </tr>
            </tbody>
        </table></div>";
        
}

$content = "<style>
._divimg {
    float:left;
    width:60px;
    height:60px;
    margin:5px;
    padding-top: 5px;
    border:0px;
    position:relative;
}
.barcode {
    padding: 1.0mm;
    margin: 0;
    vertical-align: top;
    color: #000;
	}
.barcodecell {
    text-align: right;
    vertical-align: middle;
	
}</style>";

if(isset($contentArr)){
  $contentE = join("<pagebreak />", $contentArr);
  $content = $content . $contentE;
}
else
{
  $content = $content ."<table><tbody>
    <tr><td style=\"width: 384px; height: 50px; text-align: center;\"></td></tr>
  </tbody></table></div>";
}

/*Librería pdf, insertar en el constructor Nombre, Dirección y Logo de la compañía*/
//$pdf = new \ReportePDF\PDF($cia, $titleReport,'L');
$pdf = new \ReporteEtiquetas\ReporteEtiquetas($cia, $titleReport, 'L');
/*Contenido o body del reporte pdf*/
$pdf->setContent($content);
/*Salida del PDF al browser*/
$pdf->stream();

unset($_SESSION["dataReporte"]);
?>