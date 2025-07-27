<?php
include '../../../app/load.php';

session_start();

/*Asegurarse de que se reciben las variables*/
if(!isset($_SESSION["FolPedidoCon"]) || empty($_SESSION["FolPedidoCon"])){
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

$r = new \Reportes\Reportes();

$data = (array) $r->guiasembarque($_SESSION["FolPedidoCon"], $_SESSION["FacturaMadre"]);

foreach ($data as $d) {

    $cajas = (int) $d["cajas"];

    $c = 1;

    for ($i=0;$i<$cajas;$i++) {

        $contentArr[] = "
        <div id=\"container\"><div class='_divimg'><img src='".$companyLogo."' width='60' height='60'/></div>
        <table>
            <tbody>
                <tr>
                    <td style=\"width: 384px; height: 50px; text-align: center;\">" . $d["Nom_CteCon"] . "</td>
                </tr>
                <tr>
                    <td style=\"width: 384px; height: 80px; text-align: center;\"><h3><strong>" . $d["RazonComercial"] . "</strong></h3></td>
                </tr>
                <tr>
                    <td style=\"width: 384px; height: 30px;\"><h5>" . $d["CalleNumero"] . "</h5></td>
                </tr>
                <tr>
                    <td><h5>" . $d["Ciudad"] . "</h5></td>
                </tr>
                <tr>
                    <td align='right' style=\"width: 384px; height: 60px;\">&nbsp;Orden de Compra:&nbsp;" . $d["No_OrdComp"] . "&nbsp;&nbsp;&nbsp;
                    <div class=\"barcodecell\"><barcode code=\"" . $_SESSION["FacturaMadre"] . "\" type=\"C128A\" class=\"barcode\" /></div>
                    <div style='font-size: 8px; width: 384px; height: 40px;'>" . $_SESSION["FacturaMadre"] . "-".$c."
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div></td>
                </tr>
                <tr>
                    <td style=\"text-align: right;\">&nbsp;Caja: $c de $cajas</td>
                </tr>
            </tbody>
        </table></div>";
        $c++;
    }
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

$contentE = join("<pagebreak />", $contentArr);

$content = $content . $contentE;


/*Librería pdf, insertar en el constructor Nombre, Dirección y Logo de la compañía*/
$pdf = new \ReporteEtiquetas\ReporteEtiquetas($cia, $titleReport, 'L');
/*Contenido o body del reporte pdf*/
$pdf->setContent($content);
/*Salida del PDF al browser*/
$pdf->stream();

unset($_SESSION["dataReporte"]);
?>