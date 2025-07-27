<?php
include '../../../app/load.php';

/*Asegurarse de que se reciben las variables*/
if(!isset($_POST) || empty($_POST)){
    exit;
}
$content.='<table>'; 
$content.='<tr><th style="border: 1px solid #ccc">Inventario</th>'+
                                       '<th style="border: 1px solid #ccc">Conteo</th>'+
                                       '<th style="border: 1px solid #ccc">Fecha de Inventario</th>    '+
                                       '<th style="border: 1px solid #ccc">Proveedor</th>'+
                                       '<th style="border: 1px solid #ccc">Clave</th>   '+
                                       '<th style="border: 1px solid #ccc">Descripcion</th>  '+
                                       '<th style="border: 1px solid #ccc">Cantidad</th>  '+
										'</tr>';
$content.='</table>';
$cia = $_POST['cia'];//ID de la compañía
$content = $content;//Contenido para el reporte (Enviar tabla)
$titleReport = "Informe de Embarque";//Título para el Reporte
$orientation = isset($_POST['orientation']) && !empty($_POST['orientation']) ? $_POST['orientation'] : 'L';

/*Librería pdf, insertar en el constructor Nombre, Dirección y Logo de la compañía*/
$pdf = new \ReportePDF\PDF($cia, $titleReport, $orientation);
/*Contenido o body del reporte pdf*/
$pdf->setContent($content);
/*Salida del PDF al browser*/
$pdf->stream();

?>