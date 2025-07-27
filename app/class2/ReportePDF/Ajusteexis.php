<?php

namespace ReportePDF;

require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';

include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include_once  $_SERVER['DOCUMENT_ROOT']."/config.php";

class Ajusteexis{
  
  public function __construct(){}
  
  public function getDataPDF($id, $cia)
  {
    $sqlHeader1 = "
        SELECT  
            td_ajusteexist.fol_folio AS folio,
            c_almacenp.nombre AS almacen,
            c_almacen.des_almac AS zona,
            c_usuario.nombre_completo AS usuario,
            DATE_FORMAT(th_ajusteexist.fec_ajuste,'%d-%m-%Y %H:%i:%s') AS fecha,
            DATE_FORMAT(th_ajusteexist.fec_ajuste,'%d-%m-%Y %H:%i:%s') as fechafin,
            sum(ABS(td_ajusteexist.num_cantant - td_ajusteexist.num_cantnva)) AS diferencia,
            'Cerrado' as statu,
            '_______' as firma
        FROM td_ajusteexist 
            LEFT JOIN c_ubicacion on c_ubicacion.idy_ubica = td_ajusteexist.Idy_ubica
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            LEFT JOIN c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
            LEFT JOIN th_ajusteexist on th_ajusteexist.fol_folio = td_ajusteexist.fol_folio
            LEFT JOIN c_usuario on c_usuario.id_user = th_ajusteexist.cve_usuario
        WHERE td_ajusteexist.fol_folio =  '{$id}';
    ";
    $queryHeader1 = mysqli_query(\db2(), $sqlHeader1);
    $rows = array();
    while(($row = mysqli_fetch_array($queryHeader1, MYSQLI_ASSOC))) 
    {
        $rows[] = $row;
        $datos_header1 = $row;
    }
    
    $sqlBody = "
        SELECT  
            c_ubicacion.CodigoCSD AS bl,
            td_ajusteexist.cve_articulo AS clave,
            c_articulo.des_articulo AS descripcion,
            IF(c_articulo.control_lotes = 'S',td_ajusteexist.cve_lote,'') AS lote,
            COALESCE(IF(c_articulo.control_lotes = 'S',IF(c_lotes.Caducidad = '0000-00-00','',date_format(c_lotes.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            IF(c_articulo.control_numero_series = 'S',td_ajusteexist.cve_lote,'') AS serie,
            td_ajusteexist.num_cantant AS teorica,
            td_ajusteexist.num_cantnva AS fisica,
            ABS(td_ajusteexist.num_cantant - td_ajusteexist.num_cantnva) AS diferencia,
            ifnull(c_unimed.des_umed,'') as unidad,
            '_______' as firma
        FROM td_ajusteexist 
            LEFT JOIN c_lotes on c_lotes.cve_articulo = td_ajusteexist.cve_articulo AND c_lotes.LOTE = td_ajusteexist.cve_lote
            LEFT JOIN c_ubicacion on c_ubicacion.idy_ubica = td_ajusteexist.Idy_ubica
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            LEFT JOIN c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
            LEFT JOIN th_ajusteexist on th_ajusteexist.fol_folio = td_ajusteexist.fol_folio
            LEFT JOIN c_usuario on c_usuario.id_user = th_ajusteexist.cve_usuario
            INNER JOIN c_articulo on c_articulo.cve_articulo = td_ajusteexist.cve_articulo
            LEFT JOIN c_unimed on c_unimed.id_umed = c_articulo.unidadMedida
        WHERE td_ajusteexist.fol_folio = '{$id}';
    ";
    $queryBody = mysqli_query(\db2(), $sqlBody);
    $rows = array();
    while(($row = mysqli_fetch_array($queryBody, MYSQLI_ASSOC))) 
    {
        $rows[] = $row;
    }
    $datos_Body = $rows;
    
    $db = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->set_charset('utf8');
    $sql = sprintf('SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = %s', $cia);
    $query = $db->query($sql);
    if($cia != '')
    {
        $data = $query->fetch_object();
        $data->logo = str_replace('../img', 'img', $data->logo);
        $this->companyName = $data->nombre;
        $this->companyAddress = $data->direccion;
        $url = $_SERVER['DOCUMENT_ROOT']."/";
        $this->companyLogo = $url.$data->logo;
        $query->free_result();
        $db->close();
    }

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
    $pdf->SetCreator('wms');
    $pdf->SetAuthor('wms');
    $pdf->SetTitle('Ajuste de existencia');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $filename = "Ajuste de existencia #{$id}.pdf";
    $reporte = "Ajuste de existencia #{$id}";
    $base_url = $_SERVER['DOCUMENT_ROOT'];
    ob_start();
    ?>
        <div class="row"></div>
        <table style="width:100%;">
            <tr style="width:100%;">
                <td style="width: 10px;"></td>
                <td style="width:340px;">
                    <table>
                        <tr>
                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td colspan="4" style=" text-align: right;">
                                <img src="<?php echo $this->companyLogo?>" alt="<?php echo $this->companyName?>" height="100px">
                            </td>
                            <td class="mt-6" colspan="16" style="white-space:nowrap; text-align: center; font-size:9px">
                                <h1><?php echo $this->companyName?></h1>
                                <p style="text-align: center; font-size:8px"><?php echo $reporte ?></p>
                            </td>
                            <td colspan="4"  style="text-align: right;">
                                <?php echo date('d-m-Y')?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Folio</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Almacén </td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Zona </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Usuario </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Fecha Ajuste </td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Fecha Fin </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Diferencia </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Status </td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Firma </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["folio"]?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["almacen"]?></td>
                            <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["zona"]?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["usuario"]?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["fecha"]?></td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["fechafin"]?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["diferencia"]?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["statu"]?></td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $datos_header1["firma"]?></td>
                        </tr>
                        <tr>
                            <td colspan="24" style="">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">BL</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Clave</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Descripción</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Lote</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Caducidad</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Serie</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Stock Teórico</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Stock Físico</td>
                            <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Diferencia</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Unidad</td>
                            <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; background-color: #DCDCDC; font-size: 4px;">Firma</td>
                        </tr>
                        <?php foreach($datos_Body as $rows){ ?>
                            <tr>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["bl"]?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["clave"]?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo utf8_encode($rows["descripcion"])?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["lote"]?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["caducidad"]?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["serie"]?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["teorica"]?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["fisica"]?></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["diferencia"]?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["unidad"]?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 7px; text-align: center; font-size: 4px;"><?php echo $rows["firma"]?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
                <td style="width: 10px;"></td>
            </tr>
        </table>
    <?php
    $desProducto = ob_get_clean();
    $pdf->AddPage();
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
        'stretchtext'  => 6
      );
      $pdf->SetAutoPageBreak(TRUE, 5);
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
      $pdf->setMargins(0, 5, 0, 0);
      $pdf->SetXY(5, 5);
      $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
      $pdf->WriteHTML($desProducto, true, false, true, '');
      $pdf->Output($filename, 'I');
  }
}
?>