<?php 

/*
    ** Created by kemdmq on 05/07/2017 **
*/
namespace ReportePDF;

/*Libreria mPDF*/
require dirname(dirname(__DIR__)).'/vendor/autoload.php';
/*Clase PDF con la plantilla personalizada*/
class PDF {
    private $mpdf;
    private $titleReport;
    private $companyName;
    private $companyAddress; 
    private $companyLogo;
    private $title_con_chofer;

    public function __construct($cia, $titleReport, $orientation = 'P', $title_con_chofer)
    {
        $this->mpdf =  new \mPDF('', "A4-{$orientation}", 0, 'dejavusans');
        $this->mpdf->setAutoTopMargin = true;
        $titulo = $titleReport;
        if($title_con_chofer != '') {$titulo = $title_con_chofer; $this->title_con_chofer = $title_con_chofer;}
        $this->mpdf->SetTitle($titulo);
        //$this->mpdf->allow_charset_conversion = true;
        //$this->mpdf->charset_in = 'UTF-8';
        $this->titleReport = $titleReport;
        $this->setData($cia);
    }

    
    private function setData($cia)
    {
        /*Obteniendo la data de la compañía para generar la plantilla*/
        $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $db->set_charset('utf8');
        $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
        $query = $db->query($sql);
        if($cia != ''){
          $data = $query->fetch_object();
          $data->logo = str_replace('../img', 'img', $data->logo);
          $this->companyName = $data->nombre;
          $this->companyAddress = $data->direccion;
          $url = $_SERVER['DOCUMENT_ROOT']."/";
          $this->companyLogo = $url.$data->logo;
          $query->free_result();
          $db->close();
          /*Configurando Footer y Header*/
          $this->mpdf->SetHTMLHeader($this->getHeader());
          $this->mpdf->SetHTMLFooter($this->getFooter());
        }
    }


    public function setContent($content)
    {
        $this->mpdf->WriteHTML($content);
    }


    public function stream($typeDownload = "i")
    {
        $this->mpdf->Output($this->titleReport.".pdf", $typeDownload);
    }


    private function getHeader()
    {
        ob_start(); ?>
            <header>
                <table style="width: 100%">
                    <tr>
                        <td style="width: 10%; text-align: right;">
                            <img src="<?php echo $this->companyLogo?>" alt="<?php echo $this->companyName?>" height="100px">
                        </td>
                        <td style="width: 80%; text-align: center; font-size:14px">
                            <h1><?php echo $this->companyName?></h1>
                        </td>
                        <td style="width: 10%; text-align: right;">
                            
                        </td>
                    </tr>
                </table>
                <div style=" text-align: center; font-weight: bold"><?php echo $this->titleReport ?></div>
                <div style="border-bottom: 1px solid black; text-align: center; font-weight: bold">&nbsp;</div>
                <div style="text-align: right; margin-top: -16pt">{DATE d/m/Y}</div>
            </header>
        <?php 
        return ob_get_clean();
    }


    private function getFooter()
    {
        ob_start(); ?>

        <footer>
            <?php 
            if($this->title_con_chofer != '')
            {
            ?>
            <p style="text-align:center; font-size:12px;">_____________________________________</p>
            <br>
            <div style="text-align:center; font-size:12px;">Firma del Chofer</div>
            <?php 
            }else
            {
            ?>
            <p style="text-align:center; font-size:12px ">Dirección: <?php echo $this->companyAddress?> <em>(Página {PAGENO})</em></p>
            <?php 
            }
            ?>
        </footer>

        <?php 
        return ob_get_clean();
    }
}

?>