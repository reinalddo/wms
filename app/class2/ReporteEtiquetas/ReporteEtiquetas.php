<?php 

/*
    ** Created by kemdmq on 05/07/2017 **
*/
namespace ReporteEtiquetas;

/*Libreria mPDF*/
require dirname(dirname(__DIR__)).'/vendor/autoload.php';
/*Clase PDF con la plantilla personalizada*/
class ReporteEtiquetas {
    private $mpdf;
    private $titleReport;
    private $companyName;
    private $companyAddress; 
    private $companyLogo;

    public function __construct($cia, $titleReport, $orientation = 'P'){
        $this->mpdf =  new \mPDF('', array(101.6, 38.1), 0, 'dejavusans');
        //$this->mpdf->setAutoTopMargin = true;
        //$this->mpdf->SetTitle($titleReport);
        //$this->titleReport = $titleReport;
        $this->setData($cia);
    }

    private function setData($cia){
        /*Obteniendo la data de la compañía para generar la plantilla*/
        /*$db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $db->set_charset('utf8');
        $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
        $query = $db->query($sql);
        $data = $query->fetch_object();
        $this->companyName = $data->nombre;
        $this->companyAddress = $data->direccion;
        $url = $_SERVER['DOCUMENT_ROOT']."/img/compania/";
        $this->companyLogo = $url.$data->logo;
        $query->free_result();
        $db->close();*/
        /*Configurando Footer y Header
        $this->mpdf->SetHTMLHeader($this->getHeader());
        $this->mpdf->SetHTMLFooter($this->getFooter());*/
    }

    public function setContent($content){
        $contenth = "<style>@page { margin: 0px; }</style> ";
        $content = $contenth . $content;
        $this->mpdf->WriteHTML($content);
    }

    public function stream($typeDownload = "i"){
        $this->mpdf->Output($this->titleReport.".pdf", $typeDownload);
    }

    private function getHeader(){
        ob_start(); ?>
            <header>
                <table style="width: 100%">
                    <tr>
                        <td style="width: 20%; text-align: right;">
                            <img src="<?php echo $this->companyLogo?>" alt="<?php echo $this->companyName?>" height="100px">
                        </td>
                        <td style="width: 80%; text-align: center;">
                            <h1><?php echo $this->companyName?></h1>
                        </td>
                    </tr>
                </table>
                <div style="border-bottom: 1px solid black; text-align: center; font-weight: bold"><?php echo $this->titleReport ?></div>
                <div style="text-align: right; margin-top: -16pt">{DATE d/m/Y}</div>
            </header>
        <?php 
        return ob_get_clean();
    }

    private function getFooter(){
        ob_start(); ?>

        <footer>
            <p style="text-align:center">Dirección: <?php echo $this->companyAddress?> <em>(Página {PAGENO})</em></p>
        </footer>

        <?php 
        return ob_get_clean();
    }
}

?>