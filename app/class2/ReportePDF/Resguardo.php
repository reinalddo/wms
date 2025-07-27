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
class Resguardo {
    
    private $empresa;
    private $surtidor;
    private $piezas;
  
  
    private $folio;
    private $direccion;
    private $fecha;
    private $cliente;
    private $rfc;
    private $clave;
    private $articulos;

    public function __construct($folio){
       $this->setData($folio);
    }
  
    private function encabezado()
    {
      $base_url = $_SERVER['DOCUMENT_ROOT'];
      $imgLogosSP = $base_url."img/logos_sp.jpg";
      ?>

      <div class="row">
          <div class="col-md-12" style="padding: 20px; height: 90%">
              &nbsp;&nbsp;<img style="display: block; margin-left: auto; margin-right: auto; padding: 20px; width: 3360%" src="<?=$imgLogosSP?>" >
          </div>
      </div>
      <br>
      <table style="width: 95%;">
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="5"style="border: 1px solid black; font-size: 8px; font-weight: bold;"align="center">DIRECCIÓN DE ADMINISTRACIÓN Y FINANZAS</td>
        </tr>
        <tr>
            <td colspan="5"style="border: 1px solid black; font-size: 8px; font-weight: bold;"align="center"> SUBDIRECCIÓN DE ADMINISTRACIÓN</td>
        </tr>
        <tr>
            <td colspan="5"style="border: 1px solid black; font-size: 8px; font-weight: bold;"align="center"> DEPARTAMENTO DE SERVICIOS GENERALES</td>
        </tr>
        <tr>
            <td colspan="5"style="border: 1px solid black; font-size: 8px; background-color: #DCDCDC;"align="center"> FORMATO DE RESGUARDO DE ACTIVO FIJO</td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px; font-weight: bold;" align="center">BIENES DEL REPSS</td>
            <td colspan="1" style="border: 1px solid black; font-size: 7px;" align="center">FOLIO:</td>
            <td colspan="1" style="border: 1px solid black; font-size: 7px;" align="center"><?php echo $this->folio;?></td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px;" >DIRECCIÓN, SUBDIRECCIÓN O DEPARTAMENTO:</td>
            <td rowspan="2" style="border: 1px solid black; font-size: 7px;" align="center">FECHA:</td>
            <td rowspan="2" style="border: 1px solid black; font-size: 7px;" align="center"><?php echo $this->fecha;?></td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px;" align="center"><?php echo $this->direccion;?></td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px;" >SERVIDOR PÚBLICO RESPONSABLE:</td>
            <td rowspan="2" style="border: 1px solid black; font-size: 7px;" align="center">RFC:</td>
            <td rowspan="2" style="border: 1px solid black; font-size: 7px;" align="center"><?php echo $this->rfc;?></td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px;" align="center"><?php echo $this->cliente;?></td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px;" >CARGO:</td>
            <td rowspan="2" style="border: 1px solid black; font-size: 6px;" align="center">No. DE EMPLEADO:</td>
            <td rowspan="2" style="border: 1px solid black; font-size: 7px;" align="center"><?php echo $this->clave;?></td>
        </tr>
        <tr>
            <td colspan="3" style="border: 1px solid black; font-size: 7px;" align="center"></td>
        </tr>
      <?
    }
  
    private function productos($pagina)
    {
      ?>
        <tr>                
          <td colspan="6"> 
              <br/><br/>                 
              <table style="font-size: 7px; width: 100%; border: 1px solid black">
                  <thead>
                      <tr>
                          <th style="background-color: #DCDCDC; border: 1px solid black; width: 7%">No.</th>
                          <th style="background-color: #DCDCDC; border: 1px solid black; width: 25%">No. INVENTARIO</th>
                          <th style="background-color: #DCDCDC; border: 1px solid black; width: 7%">NIC</th>
                          <th style="background-color: #DCDCDC; border: 1px solid black; width: 46%;">DESCRIPCIÓN DEL ACTIVO</th>
                          <th style="background-color: #DCDCDC; border: 1px solid black; width: 15%">COSTO</th>
                      </tr>
                  </thead>

                  <tbody>
                  <?php
                      $i = 1;
                      $piezas = 0;
      
                      $art_inicial = ($pagina * 21)-20;
                      $art_final = $pagina * 21;
      
                      foreach($this->articulos as $articulo)
                      { 
                        if($i >= $art_inicial && $i <= $art_final)
                        {
                          ?>
                            <tr>
                              <td style="border-right: 1px solid black; white-space:nowrap; text-align: right;"><?php echo $articulo["id"] ?></td>
                              <td style="border-right: 1px solid black"><?php echo $articulo['clave_activo'] ?></td>
                              <td style="border-right: 1px solid black"><?php echo $articulo['nic'] ?></td>
                              <td style="border-right: 1px solid black;"><?php echo $articulo['des_articulo'] ?></td>
                              <td style="border-right: 1px solid black; white-space:nowrap;"><?php echo $articulo['costoPromedio']; ?></td>
                            </tr>
                         <?php
                        }
                        $i++;
                        $this->piezas += $articulo['cantidad'];
                      }
                      ?>
                  </tbody>
              </table> 
          </td>                
      </tr>
      <?
    }
  
    private function pie()
    {
      $base_url = $_SERVER['DOCUMENT_ROOT'];
      $imgCertificado = $base_url."img/certificado.nikken.png";
      ?>
          <tr>
              <td colspan="5"> 
                  <br/><br/>             
                  <table style="width: 100%; font-size: 7px;">                
                      <tr>
                          <td style="width: 34%; height: 50px; border: 1px solid black;"></td>
                          <td style="width: 34%; height: 50px; border: 1px solid black;"></td>
                          <td style="width: 32%; height: 50px; border: 1px solid black;"></td>
                      </tr>
                      <tr>
                          <td style="font-size: 6px; text-align:center; width: 34%; border: 1px solid black;">RESPONSABLE DE LA ELABORACIÓN Y ACTUALIZACIÓN</td>
                          <td style="font-size: 6px; text-align:center; width: 34%; border: 1px solid black;">RESPONSABLE DEL RESGUARDO DEL ACTIVO FIJO</td>
                          <td style="font-size: 6px; text-align:center; width: 32%; border: 1px solid black;">DIRECTOR DE ADMINISTRACIÓN Y FINANZAS</td>
                      </tr>
                    <tr>
                          <br>
                          <td colspan="3" style="font-size: 5px; text-align:justify; ">NOTA : EN CASO DE BAJA, CAMBIO, EXTRAVIO O ROBO, DEBERÁ INFORMAR AL DEPARTAMENTO DE SERVICIOS GENERALES, DE LO CONTRARIO, EL RESPONSABLE DEL RESGUARDO DEL ACTIVO FIJO QUIEN FIRMÓ ESTE RESGUARDO ES RESPONSABLE DE LOS BIENES Y NO SERÁ LIBERADO DE LA RESPONSABILIDAD PARA EL CASO DE SU RENUNCIA O BAJA, LO CULA SERÁ INFORMADO A LA CONTRALORÍA INTERNA DEL REPSS.</td>
                      </tr>
                  </table>
              </td>                
          </tr>
      </table>
      <? 
    }

    public function generarHojaSurtido()
    {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Formato de resguardo');
        $pdf->SetSubject('Formato de resguardo');
        $pdf->SetKeywords('producto, surtido');
        $pdf->SetDisplayMode('real', 'default');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 027', PDF_HEADER_STRING);
      
        
        $style = array(
            'position'     => '',
            'align'        => 'C',
            'stretch'      => false,
            'fitwidth'     => false,
            'cellfitalign' => '',
            'border'       => false,
            'hpadding'     => 'auto',
            'vpadding'     => 'auto',
            'fgcolor'      => array(0, 0, 0),
            'bgcolor'      => false,
            'text'         => true,
            'font'         => 'helvetica',
            'fontsize'     => 6,
            'stretchtext'  => 4
        );
        
        $filename = "Formato de resguardo #{$folio}.pdf";
        //$codigobarra = $data['clave'].$data['lote'].$data['ordenp'].$data['cantidad'];
        $width = '30';
        $height = '10';
        
        $num_art = count($this->articulos);
        $pag_tot = ceil($num_art / 21);
        
        $this->piezas = 0;
        
        for($npag = 1; $npag <= $pag_tot ; $npag++ )
        {
          ob_start();
          $this->encabezado();
          //$pdf->write1DBarcode($this->folio, 'C128', 60, 6, $width, $height, 0.2, $style, 'N');
          $this->productos($npag);
          $this->pie();
          $desProducto = ob_get_clean();
          
          $pdf->AddPage();
        
          //$pdf->write1DBarcode($this->folio, 'C128', 60, 6, $width, $height, 0.2, $style, 'N');
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($desProducto, true, false, true, '');
        }
        $pdf->Output($filename, 'I');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    private function setData($folio)
    {
        mysqli_set_charset(\db2(), 'utf8');
        
        $sql = "select fol_folio from th_pedido where id_pedido = (select id_pedido from t_activo_fijo where id = ".$folio.");";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $this->folio = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["fol_folio"];
        }
        
        /*$sql = "
            select RazonSocial,RFC from c_cliente 
            inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte 
            where Fol_folio = '".$this->folio."'
        ";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
          $cliente = mysqli_fetch_all($query, MYSQLI_ASSOC);
          $this->cliente = $cliente[0]["RazonSocial"];
          $this->rfc = $cliente[0]["RFC"];
        }*/
      
        $sql = "
            select nombre_empleado,rfc_empleado, clave_empleado from t_activo_fijo
            where id = '".$folio."'
        ";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
          $empleado = mysqli_fetch_all($query, MYSQLI_ASSOC);
          $this->cliente = $empleado[0]["nombre_empleado"];
          $this->rfc = $empleado[0]["rfc_empleado"];
          $this->clave = $empleado[0]["clave_empleado"];
        }
      
        $sql = "
            select 
              concat(CalleNumero,', ',Colonia,', ',Ciudad,', ',Estado,', ',Pais) as direccion 
            from c_cliente 
            inner join th_pedido on th_pedido.Cve_clte = c_cliente.Cve_Clte 
            where Fol_folio = '".$this->folio."';
        ";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $this->direccion = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["direccion"];
        }
        
        $this->fecha = date('d/m/Y');
      
        $sql = "
            select 
            t_activo_fijo.id,
            t_activo_fijo.clave_activo,
            '' as nic,
            c_articulo.des_articulo,
            c_articulo.costoPromedio
            from t_activo_fijo
            inner join c_articulo on c_articulo.id = t_activo_fijo.id_articulo
            where t_activo_fijo.id = ".$folio;
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $this->articulos = mysqli_fetch_all($query, MYSQLI_ASSOC);
        }
    }
}
?>