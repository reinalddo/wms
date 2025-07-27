<?php
require_once "../../../../core/autoload.php";
require_once "Report.php";

$report = new Report;
// $report->run()->render();
$folio = $_GET['folio'];
$report->run()->export()
->pdf(array(
    "format"=>"Letter",//A4, Letter
    "orientation"=>"portrait",//portrait, landscape
    //"zoom"=>2
))
->toBrowser("Reporte de Venta - {$folio}.pdf", true);