<?php 

/*
    ** Created by kemdmq on 24/11/2017 **
*/
namespace ReportePDF;
/*Libreria TCPDF*/
require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';

include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include_once  $_SERVER['DOCUMENT_ROOT']."/config.php";


/*Clase PDF con la plantilla personalizada*/
class Activos
{
  private $claveActivo;
  private $almacen;

  public function __construct($data){
     $this->setData($data);
  }
  
  private function setData($data)
  {
    $this->claveActivo = $data;
    
    mysqli_set_charset(\db2(), 'utf8');
    $sql = "
        select c_almacenp.nombre from t_activo_fijo
        inner join c_articulo on c_articulo.id = t_activo_fijo.id_articulo
        inner join c_almacenp on c_almacenp.id = c_articulo.cve_almac
        where clave_activo =  '".$this->claveActivo."';
    ";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        $this->almacen =  mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['nombre'];
    }
    
  }
  
  public function toPrint()
  {
    
    $style = array(
        'position'     => '',
        'align'        => 'N',
        'stretch'      => false,
        'fitwidth'     => false,
        'cellfitalign' => '',
        'border'       => false,
        'hpadding'     => 'auto',
        'vpadding'     => 'auto',
        'fgcolor'      => array(0, 0, 0),
        'bgcolor'      => false,
        'text'         => false,
        'font'         => 'helvetica',
        'fontsize'     => 6,
        'stretchtext'  => 4
    );
    
    //$style = array('text'=>true);
    
    $pdf = new \TCPDF("L", "mm", array(50.8, 25.4), true, 'UTF-8', false);
    $pdf->SetMargins(4, 4, 4, true);
    
    
    // remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    ob_start();
    $this->encabezado();
    $desProducto = ob_get_clean();

    $pdf->write1DBarcode($this->claveActivo, 'C128', 20, 10, 40, 13, 0.4, $style, 'N');
    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

    $pdf->setMargins(0, 5, 0, 0);
    $pdf->SetXY(15, 5);
    $pdf->SetFont('helvetica', '', '12px', '', 'default', true);
    $pdf->WriteHTML($desProducto, true, false, true, '');

    //$pdf->setFontSubsetting(true);
    //$pdf->SetFont('freeserif', '', 12);
    //$pdf->write1DBarcode("123456", 'C128', '5', '6', '60', 14, 0.4, $style, 'N');
    //$pdf->write1DBarcode($this->claveActivo, 'C128', 20, 5, 40, 20, 0.4, $style, 'N');
    $pdf->Output($this->claveActivo.".pdf", 'I');
  }
  
  
  private function encabezado()
    {
      ?>
      <table style="width: 100%; padding: -15px;">
        <tr><td><div><strong style="font-size:12px; border: 0px solid;"><?php echo $this->almacen ?></strong></div></td></tr>
        <tr><td><div><strong style="font-size:11px; border: 0px solid;padding:5px"><br><br><br><br><?php echo $this->claveActivo ?></strong></div></td></tr>
      </table>
      <?php
    }
}
?>