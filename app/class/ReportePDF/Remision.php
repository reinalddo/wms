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
class Remision {
    private $articulos;
    private $empresa;
    private $destinatario;
    private $folio;
    private $surtidor;
    private $piezas;
    private $transporte;
    private $placa;
    private $chofer;
    private $logo;

    public function __construct($folio){
       $this->setData($folio);
    }
  
    private function encabezado()
    {
      $base_url = $_SERVER['DOCUMENT_ROOT'];
      $imgInstagram = $base_url."img/instagram.jpg";
      $imgFacebook = $base_url."img/facebook.jpg";
      $imgYoutube = $base_url."img/youtube.png";
      $imgLogo = $base_url."img/compania/".$this->logo;
      ?>
      <table style="width: 95%;">
        <tr>
            <td colspan="2" style="border: 1px solid black">
              <strong style="font-size:25px; border: 0px solid"><?php echo $this->folio ?></strong>
              <br>
              <br>
            </td>
            <td colspan="2" style="border: 1px solid black"></td>
        </tr>
        <tr>
            <td colspan="2" align="center">Datos</td>
            <td colspan="2" align="center">Datos del Destinatario</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid black; font-size:8px;">
                <?php if(strpos($_SERVER['SERVER_NAME'], 'nikken')){ ?>
                    <b>Síguenos en:</b>
                    <div style="position: relative;">
                        <img src="<?=$imgInstagram?>" width="13">
                        <span style="vertical-align: top; top: -10px"><?php if( isNikken() ){ echo '/nikkenlatam'; }?></span>
                    </div>
                    <div style="position: relative;">
                        <img src="<?=$imgFacebook?>" width="13">    
                        <span style="vertical-align: top; margin-top: -30px;"><?php if( isNikken() ){ echo '/Nikkenlatinoamerica'; }?></span>
                    </div>
                    <div style="position: relative;">
                        <img src="<?=$imgYoutube?>" height="10">   
                        <span style="vertical-align: top; top: -10px"><?php if( isNikken() ){ echo '/Nikkenlatinoamerica'; }?></span>
                    </div>
                <?php }else if(strpos($_SERVER['SERVER_NAME'], 'sp')){?>
                    <div style="position: relative; align-items: center;">
                        <img src="<?php echo $imgLogo;?>" width="100" height="100" style="">
                    </div>
                <?php }else
                {
                    ?>



                    <br/>
                    <div><b>Transporte: </b><?php echo $this->transporte ." | "; ?><b>Placa: </b><?php echo $this->placa ; ?></div>
                    <div><b>Chofer: </b><?php echo $this->chofer; ?></div>
                    <?php 
                } 
                ?>
            </td>
            <td colspan="2" style="border: 1px solid black; font-size:8px;">Nombre: <?php echo ( $this->destinatario['razonsocial'] ) ?>
                <br/>
                <?php echo ( $this->destinatario['direccion'] ) ?>
              
            </td>
        </tr>
      <?
    }
  
    private function productos($pagina)
    {
      ?>
        <tr>                
          <td colspan="4"> 
              <br/><br/>                 
              <table style="font-size: 7px; width: 102%; border: 1px solid black">
                  <thead>
                      <tr>
                          <th style="border: 1px solid black; white-space:nowrap; width: 7%">#</th>
                          <?php /* ?><th style="border: 1px solid black; width: 15%">Artículo</th>
                          <th style="border: 1px solid black; width: 40%">Descripción</th><?php */ ?>
                          <th style="border: 1px solid black; width: 55%">Artículo | Descripción</th>
                          <th style="border: 1px solid black; width: 8%;">Cant.</th>
                          <th style="border: 1px solid black; width: 16%">Commentario</th>
                          <th style="border: 1px solid black; width: 14%">Proyecto</th>
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
                              <td style="border-right: 1px solid black; white-space:nowrap; text-align: right;"><?php echo $i ?></td>
                              <td style="border-right: 1px solid black; text-align: center;">
                                <?php //echo $articulo['articulo']; 
                                $imgBarCode = "https://".$_SERVER['HTTP_HOST']."/api/articulos/lista/barcode.php?codetype=Code128&size=20&text=".$articulo['articulo']."&print=true"; 
                                ?><img style="position:  relative; left: 100px;" src="<?=$imgBarCode?>" ><br>
                                <?php echo $articulo['descripcion']; ?></td>
                              <?php /* ?><td style="border-right: 1px solid black"><?php echo $articulo['descripcion']; ?></td><?php */ ?>
                              <td style="border-right: 1px solid black; text-align: right;"><?php echo $articulo['cantidad']; ?></td>
                              <td style="border-right: 1px solid black; text-align: right;white-space:nowrap;"><?php if($articulo['cantidad']<1) {echo 'Pendiente por entrega';} else {echo 'Entregado';} ?></td>
                              <td style="border-right: 1px solid black; text-align: left;"><?php echo $articulo['Proyecto'] ?></td>
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
        </table>
            <table style="width: 95%; margin-left:15%">                
                <tr>
                    <td colspan="3">
                        <br/><br/>
                        <span style="font-size:7px">
                          <?php if(strpos($_SERVER['SERVER_NAME'], 'nikken')){ ?>
                            Basado en la orden de venta <?php echo $this->oc ?>
                          <?php }else{?>
                            Entregado satisfactoriamente
                          <?php }?>
                        </span>
                    </td>
                    <td>
                        <br/><br/>
                        <span style="font-size:7px">
                            Total Pzas. <?php echo $this->piezas ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: left">
                        <strong style="font-size:7px">Pedido certificado completo por sistema de video</strong>
                    </td>
                    <td style="text-align: right;">
                        <img src="<?=$imgCertificado?>" width="30">
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
        $pdf->SetTitle('Hoja de Remisión');
        $pdf->SetSubject('Remisión de surtido');
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
        
        $filename = "Hoja de remision orden #{$folio}.pdf";
        $codigobarra = $data['clave'].$data['lote'].$data['ordenp'].$data['cantidad'];
        $width = '30';
        $height = '10';
        
        $num_art = count($this->articulos);
        $pag_tot = ceil($num_art / 21);
        
        $this->piezas = 0;
        
        for($npag = 1; $npag <= $pag_tot ; $npag++ )
        {
          ob_start();
          $this->encabezado();
          $pdf->write1DBarcode($this->folio, 'C128', 60, 6, $width, $height, 0.2, $style, 'N');
          $this->productos($npag);
          $this->pie();
          $desProducto = ob_get_clean();
          
          $pdf->AddPage();
        
          $pdf->write1DBarcode($this->folio, 'C128', 60, 6, $width, $height, 0.2, $style, 'N');
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
        //mysqli_set_charset(\db2(), 'utf8');
        $utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
        $utf8Sql->execute();

        $this->folio = $folio;
        $this->oc = '';

        $sql = "SELECT 
                    pick_num oc
                FROM th_pedido 
                WHERE Fol_folio = '{$folio}'";
        $query = mysqli_query(\db2(), $sql);

        if($query->num_rows > 0){
            $this->oc = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['oc'];
        }
/*
        $sql = "SELECT  p.Cve_articulo AS articulo,  
                        a.des_articulo AS descripcion,
                        p.Num_cantidad AS cantidad,
                        IFNULL(p.Proyecto, '') as Proyecto
                FROM td_pedido p 
                    LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
                WHERE Fol_folio = '$folio';";
*/
        $sql = "SELECT DISTINCT td.Cve_articulo AS articulo, a.des_articulo AS descripcion,
                       #IF(IFNULL(td.cve_lote, '') = '', IFNULL(s.LOTE, ''),IFNULL(td.cve_lote, '')) AS lote, 
                       #IF(IFNULL(td.cve_lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                       #IFNULL(tdt.Num_cantidad, td.Num_cantidad) AS cantidad,
                       #IFNULL(tdt.Num_cantidad, s.Cantidad) AS cantidad,
                       SUM(IFNULL(IF(th.TipoPedido = 'RI' OR th.TipoPedido = 'RE' OR th.TipoPedido = 'T', k.cantidad, k.ajuste), 0)) AS cantidad,
                       IFNULL(td.Proyecto, '') AS Proyecto
                       #a_destino.clave AS Almacen_Destino
                FROM th_pedido th
                LEFT JOIN td_pedido td ON th.Fol_folio = td.Fol_folio
                LEFT JOIN td_pedidoxtarima tdt ON td.Fol_folio = tdt.Fol_folio
                LEFT JOIN td_surtidopiezas s ON s.Cve_articulo = td.Cve_articulo AND td.Fol_folio = s.fol_folio
                LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_articulo AND l.Lote = IF(IFNULL(td.cve_lote, '') = '', IFNULL(s.LOTE, ''),IFNULL(td.cve_lote, ''))
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tdt.nTarima
                LEFT JOIN c_almacenp a_origen ON a_origen.id = th.statusaurora
                LEFT JOIN c_almacenp a_destino ON a_destino.id = th.cve_almac
                LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                LEFT JOIN t_cardex k ON k.cve_articulo = a.cve_articulo AND k.destino = '$folio' AND IFNULL(k.cve_lote, '') = IFNULL(s.LOTE, '')
                WHERE td.Fol_folio = '$folio' AND k.cve_articulo = a.cve_articulo
                GROUP BY articulo;
                ";

        $query = mysqli_query(\db2(), $sql);

        if($query->num_rows > 0){
            $this->articulos = mysqli_fetch_all($query, MYSQLI_ASSOC);
        }

        $usuario = $_SESSION['id_user'];
        $sql = "SELECT 
                    des_cia, 
                    des_direcc 
                FROM c_compania 
                WHERE cve_cia = (SELECT cve_cia FROM c_usuario WHERE id_user = {$usuario});";
        $query = mysqli_query(\db2(), $sql);

        if($query->num_rows > 0){
            $this->empresa = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
        }

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT 
                    d.razonsocial, 
                    CONCAT(direccion, '<br/> ', colonia, '<br/> ', ciudad, '<br/> ', estado,  '<br/> ', postal) AS direccion 
                FROM Rel_PedidoDest p
                    LEFT JOIN c_destinatarios d ON p.Id_Destinatario = d.id_destinatario
                WHERE p.Fol_Folio = '{$folio}'

                UNION 

                SELECT 
                    d.razonsocial, 
                    CONCAT(d.direccion, '<br/> ', d.colonia, '<br/> ', d.ciudad, '<br/> ', d.estado,  '<br/> ', d.postal) AS direccion 
                FROM th_pedido pd
                  LEFT JOIN c_destinatarios d ON pd.Cve_clte = d.Cve_Clte
                WHERE pd.Fol_Folio = '{$folio}'
                ;";
        $query = mysqli_query($conn, $sql);

        if($query->num_rows > 0){
            $this->destinatario = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
        }

        $sql = "SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = '$folio' LIMIT 1);";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $this->surtidor = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['nombre_completo'];
        }

        $sql = "SELECT IFNULL(o.chofer, '') AS chofer, IFNULL(t.Nombre, '') AS transporte, IFNULL(t.Placas, '') AS placa
                FROM th_ordenembarque o
                LEFT JOIN t_transporte t ON t.id = o.ID_Transporte
                WHERE ID_OEmbarque IN (SELECT ID_OEmbarque FROM td_ordenembarque WHERE fol_folio = '$folio')";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $row = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
            $this->chofer = $row['chofer'];
            $this->transporte = $row['transporte'];
            $this->placa = $row['placa'];
        }


      
        $sql = "SELECT * FROM c_compania WHERE cve_cia = (select cve_cia from c_usuario where id_user = '".$_SESSION["id_user"]."');";
        $query = mysqli_query($conn, $sql);
        if($query->num_rows > 0){
            $empresa = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
            $img = explode("/",$empresa["imagen"]);
            $this->logo = $img[sizeof($img)-1];
        }
    }
}

?>