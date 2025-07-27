<?php
require_once "../../../../core/autoload.php";
require_once "Report.php";

$report = new Report;
// $report->run()->render();
/*
$width = 76; 
$height = 51; 
$orientation = ($height>$width) ? 'P' : 'L'; 
$pdf = new \TCPDF($orientation, 'mm' , array($width, $height), true, 'UTF-8', false);
*/
//$folio = $_GET['folio'];
$instancia = $_GET['instancia'];


if($instancia == 'foam')
{
    $report->run()->export()
    ->pdf(array(
        /*
        "format"=>"Letter",//A4, Letter
        "orientation"=>"portrait",//portrait, landscape
        */
        //"zoom"=>2
        "width"=>"10.16cm", //4pulgadas
        "height"=>"15.24cm", //6pulgadas
        "margin"=>"0.5cm"
        //"zoom"=>2
    ))
    ->toBrowser("License Plate.pdf", true);
}
else
{
    $report->run()->export()
    ->pdf(array(
        /*
        "format"=>"Letter",//A4, Letter
        "orientation"=>"portrait",//portrait, landscape
        */
        //"zoom"=>2
        "width"=>"10.16cm", //4pulgadas
        "height"=>"7.62cm", //3pulgadas
        "margin"=>"0.5cm"
        //"zoom"=>2
    ))
    ->toBrowser("License Plate.pdf", true);
}

