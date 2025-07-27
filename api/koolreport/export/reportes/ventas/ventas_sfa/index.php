<?php
require_once "../../../../core/autoload.php";
require_once "Report.php";

$report = new Report;
// $report->run()->render();

$nombre = "Ventas";
if(isset($_GET['cobranza']))
{
    $folio = $_GET['folio'];
    $nombre = "RC{$folio}";
}

$report->run()->export()
->pdf(array(
    "format"=>"Letter",//A4, Letter
    "orientation"=>"landscape",//portrait, landscape
    //"zoom"=>2
))
->toBrowser("{$nombre}.pdf", true);