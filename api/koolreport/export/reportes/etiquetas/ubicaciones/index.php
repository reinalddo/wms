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
$report->run()->export()
->pdf(array(
    /*
    "format"=>"Letter",//A4, Letter
    "orientation"=>"portrait",//portrait, landscape
    */
    //"zoom"=>2
    "width"=>"4in",//"10.16cm", //4pulgadas
    "height"=>"2in",//"7.62cm", //3pulgadas
    "margin"=>"0.1cm"
    //"zoom"=>2
))
->toBrowser("Codigo BL.pdf", true);

