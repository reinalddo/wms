<?php

namespace ReportePDF;

require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';

include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include_once  $_SERVER['DOCUMENT_ROOT']."/config.php";

class Monitoreo{
  
  
  public function __construct()
  {
    
  }


  public function getDataPDFMonitoreo($id,$cia, $folio_pedidos)
  {
    $sqlHeader = "
        SELECT DISTINCT tt.Fol_Folio, cd.razonsocial
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_destinatarios cd ON cd.id_destinatario = tp.destinatario
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN ('$folio_pedidos') AND tt.ntarima = ch.IDContenedor 
        #LIMIT 2
        ;
    ";

    $queryHeader = mysqli_query(\db2(), $sqlHeader);


    $sql_tarimas = "
        SELECT COUNT(DISTINCT tt.ntarima) AS num_tarimas
        FROM t_tarima tt
        LEFT JOIN th_cajamixta tc ON tc.fol_folio = tt.Fol_Folio AND tc.Cve_CajaMix = tt.Caja_ref
        LEFT JOIN th_pedido tp ON tp.Fol_folio = tt.Fol_Folio
        LEFT JOIN c_destinatarios cd ON cd.id_destinatario = tp.destinatario
        LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
        WHERE  tt.Fol_Folio IN ('$folio_pedidos') AND tt.ntarima = ch.IDContenedor 
        #LIMIT 2
        ;
    ";

    $query_tarimas = mysqli_query(\db2(), $sql_tarimas);
    $num_tarimas = mysqli_fetch_assoc($query_tarimas)['num_tarimas'];

    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->set_charset('utf8');
    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != ''){
      $data = $query->fetch_object();
      $data->logo = str_replace('../img', 'img', $data->logo);
      $this->companyName = $data->nombre;
      $this->companyAddress = $data->direccion;
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";
      $this->companyLogo = $url.$data->logo;

      //$query->free_result();
      //$db->close();
    }

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle('Monitoreo de Pedidos');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $filename = "Monitoreo de Pedidos.pdf";
    //$reporte = "Reporte de Empaque #{$folio}";img/compania/asl.png
    $reporte = "MONITOREO DE PEDIDOS";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    //ob_start();
    //for($ipdf = 0; $ipdf < $num_tarimas; $ipdf++)

        if($num_tarimas == 0)
        {
            ob_start();
        ?>
            <div class="row"></div>
            <table style="width:100%;">
                <tr style="width:100%;">
                    <td style="width: 10px;"></td>
                    <td style="width:340px;">
                        <table>
                            <tr>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                              <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>

                            <tr>
                                <td colspan="5" style=" text-align: center;">
                                    <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                    
                                </td>
                                <td colspan="19"></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="text-align: center;">
                                    <?php echo $this->companyName; ?>
                                </td>
                                <td colspan="19"></td>
                            </tr>
                            <tr>
                                <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                    <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                                <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>

                            <tr>
                                <td colspan="22" style="text-align: center;"># DE TARIMA</td>
                                <td colspan="2" style=""><?php echo $ipdf; ?></td>
                            </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                                <tr>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                    <td colspan="8" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">PIEZAS PEDIDAS</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS PEDIDAS</td>
                                    <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CAJAS EMBARCADAS</td>
                                </tr>

                                <tr>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="8" style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                    <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;"></td>
                                </tr>
                            <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="12"></td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                                <td colspan="4" style="text-align: right; font-size: 6px;">0</td>
                            </tr>
                           <tr>
                                <td colspan="24" style="">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10px;"></td>
                </tr>
            </table>
        <?php 
            $desProducto = ob_get_clean(); 
            $pdf->AddPage(); 
            $pdf->SetAutoPageBreak(TRUE, 5); 
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
            $pdf->setMargins(0, 5, 0, 0); 
            $pdf->SetXY(5, 5); 
            $pdf->SetFont('helvetica', '', '8px', '', 'default', true); 
            $pdf->WriteHTML($desProducto, true, false, true, '');
            ob_end_clean();
            $pdf->Output($filename, 'I');
            return;
        }

    $ipdf = 0;
    
    while($row_pdf = mysqli_fetch_assoc($queryHeader))
    {
        ob_start();
        $ipdf++;
?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>

                        <tr>
                            <td colspan="5" style=" text-align: center;">
                                <img src="<?php echo $this->companyLogo; ?>" alt="<?php echo $this->companyName; ?>" height="100">
                                
                            </td>
                            <td colspan="19"></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: center;">
                                <?php echo $this->companyName; ?>
                            </td>
                            <td colspan="19"></td>
                        </tr>
                        <tr>
                            <td class="mt-6" colspan="24" style="white-space:nowrap; text-align: center; font-size:9px;">
                                <p style="text-align: center; font-size:8px"><?php echo $reporte; ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span>CLIENTE DESTINO</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $row_pdf['razonsocial']; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE OC</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $folio; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE FACTURA</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $row_pdf['Fol_Folio']; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="font-size: 6px;"><br><br><span># DE TARIMAS TOTALES</span></td>
                            <td colspan="18" style="font-size: 6px; border-bottom: 1px solid black;"><br><br><span><?php echo $num_tarimas; ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <?php 
                        //****************************************************************************************************
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                            <tr>
                                <td colspan="4"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CODIGO</td>
                                <td colspan="12" style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">DESCRIPCIÓN</td>
                                <td colspan="2"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">CANTIDAD DE PIEZAS</td>
                                <td colspan="6"  style="background-color: black; color: white; font-size: 5px; text-align: center;border: 0.3px solid white;">COMENTARIOS</td>
                            </tr>
                        <?php 
                        //foreach($datos_Body as $rows){ 

                        $folio_pdf = $row_pdf['Fol_Folio'];
                        //$guia_pdf = $row_pdf['Guia'];

$sqlBody = "
    SELECT DISTINCT tt.Fol_Folio AS Fol_Folio, tt.cve_articulo AS codigo, ca.des_articulo AS des_articulo, 
        (SUM(tt.cantidad) - ts.Cantidad) AS Discrepancia,
        '' AS Comentarios
    FROM t_tarima tt 
    LEFT JOIN c_articulo ca ON ca.cve_articulo = tt.cve_articulo 
    LEFT JOIN td_surtidopiezas ts ON ts.Cve_articulo = tt.cve_articulo AND ts.fol_folio = tt.Fol_Folio AND ts.Sufijo = tt.Sufijo 
    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
    WHERE tt.Fol_Folio IN ('$folio_pedidos') AND tt.ntarima = ch.IDContenedor AND tt.lote = ts.LOTE
    GROUP BY tt.cve_articulo, tt.lote;
";
                            
                        $queryBody = mysqli_query(\db2(), $sqlBody);
                        $discrepancia = 0;
                        while($row_td = mysqli_fetch_array($queryBody))
                        {
                        ?>
                            <tr>
                                <td colspan="4"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['codigo']; ?></td>
                                <td colspan="12" style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo utf8_decode($row_td['des_articulo']); ?></td>
                                <td colspan="2"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['Discrepancia']; ?></td>
                                <td colspan="6"  style="font-size: 6px; border: 0.3px solid black;text-align: center;"><?php echo $row_td['Comentarios']; ?></td>
                            </tr>
                        <?php 
                            $discrepancia += $row_td['Discrepancia'];
                        } 
                        ?>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="12" style="text-align:center; font-size: 6px; font-weight: bold;">TOTAL: </td>
                            <td colspan="2" style="text-align: center; font-size: 6px;"><?php echo $discrepancia; ?></td>
                            <td colspan="6" style="text-align: center; font-size: 6px;"></td>
                        </tr>
                       <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>


                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
        <?php
          $desProducto = ob_get_clean();
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($desProducto, true, false, true, '');
          ob_end_clean();
    }
    

//error_reporting(0); //Don't show errors in the PDF
//ob_clean(); //Clear any previous output
//ob_start(); //Start new output buffer

    $pdf->Output($filename, 'I');
    }

} 
?>


