<?php
    include '../../../app/load.php';

    /*Asegurarse de que se reciben las variables*/

    //echo var_dump($_POST);
    //die();

    if(!isset($_POST) || empty($_POST))
    {
      exit;
    }
    $cia = $_POST['cia'];// ID de la compañía
    $content = $_POST['content'];// Contenido para el reporte (Enviar tabla)
    $titleReport = $_POST['title'];// Título para el Reporte
    $title_con_chofer = $_POST['title_con_chofer'];// Título para el Reporte cuando el titulo trae el chofer
    $orientation = isset($_POST['orientation']) && !empty($_POST['orientation']) ? $_POST['orientation'] : 'L';

    /* Librería pdf, insertar en el constructor Nombre, Dirección y Logo de la compañía */

    @$pdf = new \ReportePDF\PDF($cia, $titleReport, $orientation, $title_con_chofer);
    /* Contenido o body del reporte pdf */
    @$pdf->setContent($content);

    /* Salida del PDF al browser */
    @$pdf->stream();

?>